import amqp from "amqplib";
import fs from "fs";
import { transporter, fromAddress, fromName } from "./nodemailer";
import boxMessageLogger from "./boxMessageLogger";
import { startWorker } from "./worker";
export type EmailDataType = {
  oficioDestinatario: string;
  oficioAssunto: string;
  oficio: string;
  userId: string;
  event: string;
};

const WORKER = "emailWorker";

async function sendEmail(msg: amqp.Message): Promise<void> {
  const getTransporter = transporter;
  const data: EmailDataType = JSON.parse(msg.content.toString());
  await getTransporter.sendMail({
    from: fromName ? `"${fromName}" <${fromAddress}>` : fromAddress,
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
        path: `${process.env.PDF_PATH ?? "./pdfs"}/${data.oficio}`,
      },
    ],
  });

  fs.rm(`${process.env.PDF_PATH ?? "./pdfs"}/${data.oficio}`, (err) => {
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
      await boxMessageLogger({
        correlationId: String(msg.properties.timestamp ?? ""),
        code: "EMAIL_SENT",
        message: `Email enviado para ${data.oficioDestinatario}`,
        status: "success",
        worker: WORKER,
        queueName: "email_queue",
        eventType: "Email enviado",
        metadata: { attempt, timestamp: new Date().toISOString() },
        userId: data.userId,
      });
      return;
    } catch (error: any) {
      console.error(`Attempt ${attempt} failed:`, error);

      const errorCodes: Record<string, string> = {
        ESOCKET: "Erro de conexão",
        ETIMEDOUT: "Conexão expirou",
        EAUTH: "Falha de autenticação",
        EDNS: "Falha na resolução DNS",
        ETLS: "Falha no handshake TLS",
        ENOAUTH: "Autenticação não fornecida",
        EMESSAGE: "Erro na entrega da mensagem",
        EPROTOCOL: "Resposta inválida do servidor SMTP",
      };

      const mustRetry =
        attempt < retries && Object.keys(errorCodes).includes(error.code);

      if (mustRetry) {
        console.log(`Retrying in ${delay / 1000} seconds...`);
        await new Promise((res) => setTimeout(res, delay));
        continue;
      }

      console.error("All retry attempts failed. Email could not be sent.");
      await boxMessageLogger({
        correlationId: String(msg.properties.correlationId ?? ""),
        code: error.code,
        message: error.message,
        status: "error",
        worker: WORKER,
        queueName: "email_queue",
        eventType: errorCodes[error.code] ?? "Erro desconhecido",
        metadata: { attempt, retries, timestamp: new Date().toISOString() },
        userId: data.userId,
      });
      throw error;
    }
  }
}

export default sendEmailWithRetry;
