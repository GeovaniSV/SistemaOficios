import nodemailer from "nodemailer";

export let transporter = nodemailer.createTransport({
  host: process.env.mailer_server,
  port: Number(process.env.mailer_port),
  secure: false,
  auth: {
    user: process.env.mailer_user,
    pass: process.env.mailer_password,
  },
});

export let fromAddress =
  process.env.mailer_from_email ?? process.env.mailer_user ?? "";
export let fromName = process.env.mailer_from_name ?? "Sistema de Ofícios";

export function updateTransporter(config: {
  host: string;
  port: number;
  user: string;
  password: string;
  from_email?: string;
  from_name?: string;
}) {
  if (!config.host || !config.user || !config.password) {
    console.warn(
      "[SMTP] Configuração incompleta recebida da API — mantendo transporter atual. host:",
      config.host,
      "| user:",
      config.user,
      "| password:",
      config.password ? "***" : "(vazio)",
    );
    return;
  }
  transporter = nodemailer.createTransport({
    host: config.host,
    port: config.port,
    secure: false,
    auth: {
      user: config.user,
      pass: config.password,
    },
  });
  if (config.from_email) fromAddress = config.from_email;
  if (config.from_name) fromName = config.from_name;
  console.log(
    "[SMTP] Transporter atualizado — host:",
    config.host,
    "| port:",
    config.port,
    "| user:",
    config.user,
    "| from:",
    fromAddress,
  );
}
