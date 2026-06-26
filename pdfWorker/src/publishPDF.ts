import "dotenv/config";
import amqp from "amqplib";
import fs from "fs";
import {
  S3Client,
  PutObjectCommand,
  S3ServiceException,
} from "@aws-sdk/client-s3";
import { PDFData } from "./generatePDF";
import boxMessageLogger from "./boxMessageLogger";

const bucketName = "pdf-worker";
const ENDPOINT = process.env.cloudflare_endpoint;
const ACCESS_KEY_ID = process.env.cloudflare_access_key_id;
const SECRET_ACCESS_KEY = process.env.cloudflare_secret_access_key;

async function uploadPDFToS3(filePath: string, fileName: string) {
  if (!ENDPOINT || !ACCESS_KEY_ID || !SECRET_ACCESS_KEY) {
    throw new Error("Variáveis de ambiente do S3 não configuradas!");
  }

  const s3 = new S3Client({
    endpoint: ENDPOINT,
    region: "auto",
    credentials: {
      accessKeyId: ACCESS_KEY_ID,
      secretAccessKey: SECRET_ACCESS_KEY,
    },
  });

  const fileContent = fs.readFileSync(filePath);

  const command = new PutObjectCommand({
    Bucket: bucketName,
    Key: `oficios/${fileName}`,
    Body: fileContent,
    ContentType: "application/pdf",
  });
  await s3.send(command);
}

export async function uploadPDFWithRetry(
  msg: string,
  filePath: string,
  fileName: string,
  retries = 3,
  delay = 5000,
) {
  const data: PDFData = JSON.parse(msg);
  for (let attempt = 1; attempt <= retries; attempt++) {
    try {
      await uploadPDFToS3(filePath, fileName);

      console.log("PDF uploaded successfully");

      const outbox = {
        correlationId: crypto.randomUUID(),
        code: "PDF_UPLOADED",
        message: "PDF uploaded successfully",
        status: 1,
        queueName: "email_queue",
        eventType: "PDF uploaded",
        metadata: {
          attempt,
          timestamp: new Date().toISOString(),
        },
        userId: data.userId.toString(),
      };
      boxMessageLogger(outbox);

      return;
    } catch (error: any) {
      console.error(`Attempt ${attempt} failed:`, error);

      const mustRetry =
        attempt < retries &&
        [
          "AuthorizationHeaderMalformed",
          "AuthorizationQueryParametersError",
          "ConnectionClosedByRequester",
          "ExpiredToken",
          "InvalidToken",
          "InternalError",
          "InvalidBucketName",
        ].includes(error.code);

      const errorCodes: Record<string, string> = {
        AuthorizationHeaderMalformed:
          "O cabeçalho de autorização que você forneceu não é válido.",
        AuthorizationQueryParametersError:
          "Os parâmetros de consulta de autorização que você forneceu não são válidos.",
        ConnectionClosedByRequester: "A conexão foi fechada pelo solicitante.",
        ExpiredToken: "O token expirou.",
        InvalidToken: "O token é inválido.",
        InternalError: "Ocorreu um erro interno.",
        InvalidBucketName: "O nome do bucket é inválido.",
      };

      if (!mustRetry) {
        const errorLog = {
          correlationId: crypto.randomUUID(),
          code: error.code,
          message: error.message,
          status: error.status,
          queueName: "email_queue",
          eventType: errorCodes[error.code] || "Unknown error",
          metadata: {
            attempt,
            retries,
            timestamp: new Date().toISOString(),
          },
          userId: data.userId.toString(),
        };
        console.log(errorLog);
        boxMessageLogger(errorLog);
        throw error;
      }

      await new Promise((resolve) => setTimeout(resolve, delay));
    }
  }
}
