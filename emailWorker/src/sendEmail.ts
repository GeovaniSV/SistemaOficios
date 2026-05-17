import nodemailer from "nodemailer";
import amqp from "amqplib";
import fs from "fs";
import { transporter } from "./nodemailer";

type DataType = {
  oficioDestinatario: string;
  oficioAssunto: string;
  oficio: string;
};

async function sendEmail(msg: amqp.Message): Promise<void> {
  const data: DataType = JSON.parse(msg.content.toString());
  await transporter.sendMail({
    from: "seu_nome@test-xkjn41mw9w04z781.mlsender.net", // sender address
    to: data.oficioDestinatario, // list of recipients
    subject: data.oficioAssunto, // subject line
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
    try {
      await sendEmail(msg);
      console.log("Email sent successfully");
      return; // Exit the function if email is sent successfully
    } catch (error: any) {
      console.error(`Attempt ${attempt} failed:`, error);

      const mustRetry =
        attempt < retries && ["ESOCKET", "ETIMEDOUT"].includes(error.code);
      console.log(mustRetry);

      if (mustRetry) {
        console.log(`Retrying in ${delay / 1000} seconds...`);
        await new Promise((res) => setTimeout(res, delay)); // Wait before retrying
      }

      if (!mustRetry) {
        console.error("All retry attempts failed. Email could not be sent.");
        throw error;
        attempt = retries;
      }
    }
  }
}
export default sendEmailWithRetry;
