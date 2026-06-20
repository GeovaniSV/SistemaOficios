import "dotenv/config";
import nodemailer from "nodemailer";
import { smtpConfig } from "./worker";

const mailer = {
  mailer_server: "",
  mailer_port: 0,
  mailer_secure: false,
  mailer_user: "",
  mailer_password: "",
};
if (smtpConfig) {
  mailer.mailer_server = smtpConfig.host;
  mailer.mailer_port = smtpConfig.port;
  mailer.mailer_user = smtpConfig.user;
  mailer.mailer_password = smtpConfig.password;
  mailer.mailer_secure = smtpConfig.secure;
}

// const mailer_from_name = smtpConfig.from_name
// const mailer_from_email = smtpConfig.from_email
// const mailer_server = process.env.mailer_server;
// const mailer_port = Number(process.env.mailer_port);
// const mailer_user = process.env.mailer_user;
// const mailer_password = process.env.mailer_password;

// Create a transporter using SMTP
export const transporter = nodemailer.createTransport({
  host: mailer.mailer_server,
  port: mailer.mailer_port,
  secure: mailer.mailer_secure,
  auth: {
    user: mailer.mailer_user,
    pass: mailer.mailer_password,
  },
});
