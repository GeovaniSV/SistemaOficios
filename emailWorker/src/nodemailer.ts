import "dotenv/config";
import nodemailer from "nodemailer";
import { smtpConfig } from "./worker";

const mailer_server = smtpConfig.host;
const mailer_port = smtpConfig.port;
const mailer_user = smtpConfig.user;
const mailer_password = smtpConfig.password;
const mailer_secure = smtpConfig.secure;
// const mailer_from_name = smtpConfig.from_name
// const mailer_from_email = smtpConfig.from_email
// const mailer_server = process.env.mailer_server;
// const mailer_port = Number(process.env.mailer_port);
// const mailer_user = process.env.mailer_user;
// const mailer_password = process.env.mailer_password;

// Create a transporter using SMTP
export const transporter = nodemailer.createTransport({
  host: mailer_server,
  port: mailer_port,
  secure: mailer_secure,
  auth: {
    user: mailer_user,
    pass: mailer_password,
  },
});
