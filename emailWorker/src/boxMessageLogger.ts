type ErrorType = {
  correlationId?: string;
  code?: string;
  message?: string;
  status?: number;
  queueName?: string;
  eventType?: string;
  metadata?: Record<string, any>;
  userId?: number | string;
};

function boxMessageLogger(error: ErrorType) {
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
}

export default boxMessageLogger;
