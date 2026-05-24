import nodemailer from "nodemailer";
import amqp from "amqplib";
import fs from "fs";
import { transporter } from "./nodemailer";
import logError from "./sendBoxMessage";

type DataType = {
  oficioDestinatario: string;
  oficioAssunto: string;
  oficio: string;
  userId: string;
};

async function sendEmail(msg: amqp.Message): Promise<void> {
  const data: DataType = JSON.parse(msg.content.toString());
  await transporter.sendMail({
    from: "seu_nome@test-xkjn41mw9w04z781.mlsender.net",
    to: data.oficioDestinatario,
    subject: data.oficioAssunto,
    text: `
Prezados,

Encaminhamos em anexo o ofício referente ao assunto em questão, para conhecimento e providências cabíveis.

Atenciosamente,
`,
    attachments: [
      {
        filename: data.oficio,
        path: `../pdfs/${data.oficio}`,
      },
    ],
  });

  fs.rm(`../pdfs/${data.oficio}`, (err) => {
    if (err) {
      console.error("Error while deleting PDF:", err);
    } else {
      console.log(`PDF ${data.oficio} deleted successfully.`);
    }
  });
}

async function sendEmailWithRetry(
  msg: amqp.Message,
  retries = 3,
  delay = 5000,
) {
  for (let attempt = 1; attempt <= retries; attempt++) {
    const data: DataType = JSON.parse(msg.content.toString());
    try {
      await sendEmail(msg);
      console.log("Email sent successfully");
      return;
    } catch (error: any) {
      console.error(`Attempt ${attempt} failed:`, error);

      const mustRetry =
        attempt < retries &&
        [
          "ESOCKET",
          "ETIMEDOUT",
          "EAUTH",
          "EDNS",
          "ETLS",
          "ENOAUTH",
          "EMESSAGE",
          "EPROTOCOL",
        ].includes(error.code);
      console.log(mustRetry);

      const errorCodes: Record<string, string> = {
        ESOCKET: "Connection error",
        ETIMEDOUT: "Connection timed out",
        EAUTH: "Authentication failed",
        EDNS: "DNS resolution failed",
        ETLS: "TLS handshake or STARTTLS failed",
        ENOAUTH: "Authentication not provided",
        EMESSAGE: "Message delivery error",
        EPROTOCOL: "Invalid SMTP server response",
      };

      const errorLog = {
        correlationId: msg.properties.correlationId,
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
        userId: data.userId,
      };

      if (mustRetry) {
        console.log(`Retrying in ${delay / 1000} seconds...`);
        await new Promise((res) => setTimeout(res, delay));
      }

      if (!mustRetry) {
        console.error("All retry attempts failed. Email could not be sent.");
        logError(errorLog);
        attempt = retries;
        throw error;
      }
    }
  }
}
export default sendEmailWithRetry;
