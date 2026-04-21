import "dotenv/config";
import amqp from "amqplib";

const RABBITMQ_URL = process.env.RABBITMQ_URL;
const queueName = "oficios_queue";
async function publishToqueue(message: object[]) {
  console.log(`Publishing message to queue ${queueName}...`);
  console.log(`Message content: ${JSON.stringify(message)}`);

  const conn = await amqp.connect(RABBITMQ_URL!);

  const channel = await conn.createChannel();

  for (const msg of message) {
    await channel.assertQueue(queueName, { durable: true });
    channel.sendToQueue(queueName, Buffer.from(JSON.stringify(msg)), {
      persistent: true,
    });
  }

  console.log(`Message sent to queue ${queueName}:`, message);
  await channel.close();
  await conn.close();
}

export default publishToqueue;
