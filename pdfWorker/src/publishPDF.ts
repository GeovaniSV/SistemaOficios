import "dotenv/config";
import {
  S3Client,
  PutObjectCommand,
  GetObjectCommand,
  ListObjectsV2Command,
} from "@aws-sdk/client-s3";
import fs from "fs";

const bucketName = "pdf-worker";
const ENDPOINT = process.env.cloudflare_endpoint;
const ACCESS_KEY_ID = process.env.cloudflare_access_key_id;
const SECRET_ACCESS_KEY = process.env.cloudflare_secret_access_key;

export async function uploadPDFToS3(filePath: string, fileName: string) {
  try {
    if (!ENDPOINT || !ACCESS_KEY_ID || !SECRET_ACCESS_KEY) {
      throw new Error("Variáveis de ambiente do R2 não configuradas!");
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
    console.log(`Uploaded ${fileName}`);
  } catch (error) {
    console.error("Error uploading PDF to S3:", error);
  }
}
