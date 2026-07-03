import "dotenv/config";
import amqp from "amqplib";
import fs from "fs/promises";
import path from "path";
import { generatePDF } from "./generatePDF";

const RABBITMQ_URL = process.env.RABBITMQ_URL;
const queueName = "oficios_queue";
const DEBUG_MULTI_PDFS = process.env.DEBUG_MULTI_PDFS === "true";

async function saveDebugJson(raw: string): Promise<void> {
  const payload = JSON.parse(raw);
  const recipient: string = payload.oficioDestinatario ?? "unknown";
  const safeName = recipient.replace(/[^a-zA-Z0-9@._-]/g, "_");
  const filePath = path.resolve(`./${safeName}.json`);
  await fs.writeFile(filePath, JSON.stringify(payload, null, 2), "utf-8");
  console.log(`[DEBUG] JSON salvo: ${filePath}`);
}

async function startWorker() {
  try {
    const connection = await amqp.connect(RABBITMQ_URL!);
    const channel = await connection.createChannel();
    await channel.assertQueue(queueName, { durable: true });
    channel.prefetch(1);
    console.log(`Worker is waiting for messages in queue: ${queueName}`);
    if (DEBUG_MULTI_PDFS) {
      console.log("[DEBUG] Modo DEBUG_MULTI_PDFS ativo — PDFs não serão gerados, mensagens salvas como JSON.");
    }
    channel.consume(
      queueName,
      async (msg) => {
        if (!msg) return;
        console.log(" [x] Received %s", msg.content.toString());
        try {
          if (DEBUG_MULTI_PDFS) {
            await saveDebugJson(msg.content.toString());
          } else {
            await generatePDF(msg.content.toString());
          }
        } catch (err) {
          console.error("Erro ao processar mensagem:", err);
        } finally {
          channel.ack(msg);
        }
      },
      {
        noAck: false,
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
