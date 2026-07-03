import "dotenv/config";
import axios from "axios";

export type LogStatus = "success" | "error" | "warn";

export type LogEntry = {
  correlationId?: string;
  code?: string;
  message?: string;
  status: LogStatus;
  worker: string;
  queueName?: string;
  eventType?: string;
  metadata?: Record<string, any>;
  userId?: string;
};

const BROKER_API_KEY = process.env.BROKER_API_KEY;

async function boxMessageLogger(entry: LogEntry): Promise<void> {
  const logEntry = {
    correlationId: entry.correlationId,
    code:          entry.code,
    message:       entry.message,
    status:        entry.status,
    worker:        entry.worker,
    queueName:     entry.queueName,
    eventType:     entry.eventType,
    metadata:      entry.metadata,
    userId:        entry.userId,
  };

  console.log(`[${entry.status.toUpperCase()}] ${entry.eventType ?? entry.code ?? entry.message}`);

  await axios
    .post(`${process.env.API_URL}/api/worker-logs`, logEntry, {
      headers: { "X-Broker-Api-Key": BROKER_API_KEY },
    })
    .catch((err) => {
      console.warn("[LOG] Falha ao registrar log na API:", err.message);
    });
}

export default boxMessageLogger;
