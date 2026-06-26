import nodemailer from "nodemailer";
import amqp from "amqplib";
import fs from "fs";
import { getTransporter } from "./nodemailer";
import logError from "./boxMessageLogger";
import boxMessageLogger from "./boxMessageLogger";
import { smtpConfig, startWorker } from "./worker";

export type EmailDataType = {
  oficioDestinatario: string;
  oficioAssunto: string;
  oficio: string;
  userId: string;
  event: string;
};

async function sendEmail(msg: amqp.Message): Promise<void> {
  const transporter = getTransporter();
  const data: EmailDataType = JSON.parse(msg.content.toString());
  await transporter.sendMail({
    from: smtpConfig.from_email,
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
        path: `./pdfs/${data.oficio}`,
      },
    ],
  });

  fs.rm(`./pdfs/${data.oficio}`, (err) => {
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
    const data: EmailDataType = JSON.parse(msg.content.toString());
    try {
      await sendEmail(msg);
      console.log("Email sent successfully");
      const outbox = {
        correlationId: msg.properties.timestamp,
        code: "EMAIL_SENT",
        message: "Email sent successfully",
        status: 1,
        queueName: "email_queue",
        eventType: "Email sent",
        metadata: {
          attempt,
          timestamp: new Date().toISOString(),
        },
        userId: data.userId.toString(),
      };

      console.log(outbox);
      boxMessageLogger(outbox);
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
        EAUTH: "Credênciais inválidas",
        EDNS: "DNS resolution failed",
        ETLS: "TLS handshake or STARTTLS failed",
        ENOAUTH: "Sem credênciais de autenticação",
        EMESSAGE: "Message delivery error",
        EPROTOCOL: "Invalid SMTP server response",
        ENOTFOUND: "Servidor SMTP inválido",
      };

      if (mustRetry) {
        console.log(`Retrying in ${delay / 1000} seconds...`);
        await new Promise((res) => setTimeout(res, delay));
      }

      if (!mustRetry) {
        const errorLog = {
          correlationId: new Date() + crypto.randomUUID(),
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
        console.error("All retry attempts failed. Email could not be sent.");
        boxMessageLogger(errorLog);
        attempt = retries;
        startWorker();
      }
    }
  }
}
export default sendEmailWithRetry;
