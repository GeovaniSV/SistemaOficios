import nodemailer from "nodemailer";
import { smtpConfig } from "./worker";

export function getTransporter() {
  if (!smtpConfig) {
    throw new Error("SMTP não carregado");
  }

  console.log("Criando transporter:", smtpConfig);

  return nodemailer.createTransport({
    host: smtpConfig.host,
    port: smtpConfig.port,
    secure: smtpConfig.secure,
    auth: {
      user: smtpConfig.username,
      pass: smtpConfig.password,
    },
  });
}
