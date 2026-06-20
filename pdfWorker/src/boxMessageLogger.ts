import "dotenv";
import axios from "axios";

const BROKER_API_KEY = process.env.BROKER_API_KEY;

type ErrorType = {
  correlationId?: number | string;
  code?: string;
  message?: string;
  status?: number;
  queueName?: string;
  eventType?: string;
  metadata?: Record<string, any>;
  userId?: string;
};

async function boxMessageLogger(error: ErrorType) {
  const logEntry = {
    correlationId: error.correlationId,
    code: error.code,
    message: error.message,
    status: error.status,
    queueName: error.queueName,
    eventType: error.eventType,
    metadata: error.metadata,
    userId: error.userId,
  };
  console.error(JSON.stringify(logEntry));
  await axios.post(`${process.env.API_URL}/api/worker-logs`, logEntry, {
    headers: { BROKER_API_KEY },
  });
}

export default boxMessageLogger;
