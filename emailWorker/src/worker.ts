import "dotenv/config";
import amqp from "amqplib";
import sendEmailWithRetry from "./sendEmail";
import axios from "axios";
import fs from "fs";
import { EmailDataType } from "./sendEmail";

const RABBITMQ_URL = process.env.RABBITMQ_URL;
const queueName = "email_queue";

export let smtpConfig: any = null;

async function loadSMTP() {
  const { data } = await axios.get(
    `${process.env.API_URL}/api/broker/smtp-config`,
    { headers: { "X-Broker-Api-Key": process.env.BROKER_API_KEY } },
  );
  console.log("SMTP recebido:", data);
  await fs.promises.writeFile("./smtp-config.conf", JSON.stringify(data));

  smtpConfig = data;
}

export async function startWorker() {
  try {
    await loadSMTP();
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

        const msgParser: EmailDataType = JSON.parse(msg.content.toString());

        console.log(msgParser);

        if (msgParser.event === "SMTP_CONFIG_UPDATED") {
          loadSMTP();
        } else {
          await sendEmailWithRetry(msg);
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
