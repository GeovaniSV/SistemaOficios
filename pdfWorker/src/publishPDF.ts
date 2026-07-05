import "dotenv/config";
import fs from "fs";
import { S3Client, PutObjectCommand } from "@aws-sdk/client-s3";
import { PDFData } from "./generatePDF";
import boxMessageLogger from "./boxMessageLogger";
import { startWorker } from "./worker";

const WORKER = "pdfWorker";
const bucketName = process.env.cloudflare_bucket_name ?? "fyle-storage-oab";
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

      await boxMessageLogger({
        correlationId: crypto.randomUUID(),
        code: "PDF_UPLOADED",
        message: `PDF ${fileName} enviado ao R2`,
        status: "success",
        worker: WORKER,
        queueName: "oficios_queue",
        eventType: "PDF enviado ao R2",
        metadata: { attempt, fileName, timestamp: new Date().toISOString() },
        userId: data.userId,
      });

      return;
    } catch (error: any) {
      console.error(`Attempt ${attempt} failed:`, error);

      const errorCodes: Record<string, string> = {
        AuthorizationHeaderMalformed: "Cabeçalho de autorização inválido.",
        AuthorizationQueryParametersError:
          "Parâmetros de autorização inválidos.",
        ConnectionClosedByRequester: "Conexão fechada pelo solicitante.",
        ExpiredToken: "Token expirado.",
        InvalidToken: "Token inválido.",
        InternalError: "Erro interno.",
        InvalidBucketName: "Nome do bucket inválido.",
      };

      const mustRetry =
        attempt < retries && Object.keys(errorCodes).includes(error.code);

      if (!mustRetry) {
        await boxMessageLogger({
          correlationId: crypto.randomUUID(),
          code: error.code,
          message: error.message,
          status: "error",
          worker: WORKER,
          queueName: "oficios_queue",
          eventType: errorCodes[error.code] ?? "Erro desconhecido no upload",
          metadata: {
            attempt,
            retries,
            fileName,
            timestamp: new Date().toISOString(),
          },
          userId: data.userId,
        });
        startWorker();
      }

      await new Promise((resolve) => setTimeout(resolve, delay));
    }
  }
}
