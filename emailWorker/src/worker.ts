import "dotenv/config";
import amqp from "amqplib";
import nodemailer from "nodemailer";
import { transporter } from "./nodemailer";

type DataType = {
  oficioDestinatario: string;
  oficioAssunto: string;
  oficio: string;
};

const RABBITMQ_URL = process.env.RABBITMQ_URL;
const queueName = "email_queue";
async function startWorker() {
  try {
    const connection = await amqp.connect(RABBITMQ_URL!);
    const channel = await connection.createChannel();
    await channel.assertQueue(queueName, { durable: true });
    channel.prefetch(1);
    console.log(`Worker is waiting for messages in queue: ${queueName}`);
    channel.consume(
      queueName,
      async (msg) => {
        console.log(" [x] Received %s", msg!.content.toString());
        if (!msg) {
          return;
        }
        const data: DataType = JSON.parse(msg.content.toString());

        try {
          const info = await transporter.sendMail({
            from: "seu_nome@test-xkjn41mw9w04z781.mlsender.net", // sender address
            to: data.oficioDestinatario, // list of recipients
            subject: data.oficioAssunto, // subject line
            text: "Um novo email ai", // plain text body
          });

          console.log("Message sent: %s", info.messageId);
          // Preview URL is only available when using an Ethereal test account
          console.log("Preview URL: %s", nodemailer.getTestMessageUrl(info));
        } catch (err) {
          console.error("Error while sending mail:", err);
        }
      },
      {
        noAck: true,
      },
    );

    connection.on("close", () => {
      console.warn("Conexão perdida, reconectando em 5s...");
      setTimeout(startWorker, 5000);
    });
  } catch (error) {
    console.error("Error in worker:", error);
    setTimeout(startWorker, 5000);
  }
}

startWorker();
