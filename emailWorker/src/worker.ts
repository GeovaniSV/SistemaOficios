import "dotenv/config";
import amqp from "amqplib";
import sendEmailWithRetry from "./sendEmail";
import axios from "axios";
import { EmailDataType } from "./sendEmail";

type ConfigType = {
  event: "SMTP_CONFIG_UPDATED";
};

const RABBITMQ_URL = process.env.RABBITMQ_URL;
const queueName = "email_queue";

// export let smtpConfig: any = null;

// async function loadSMTP() {
//   const { data } = await axios.get(
//     `${process.env.API_URL}api/broker/smtp-config`,
//   );

//   smtpConfig = data;
// }

// async function bootstrap() {
//   await loadSMTP();
//   await startWorker();
// }

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

        const msgParser: EmailDataType | ConfigType = JSON.parse(
          msg.content.toString(),
        );
        await sendEmailWithRetry(msg);
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
