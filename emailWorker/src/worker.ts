import "dotenv/config";
import amqp from "amqplib";
import sendEmailWithRetry from "./sendEmail";

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
