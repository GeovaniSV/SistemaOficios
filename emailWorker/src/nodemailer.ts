import "dotenv/config";
import nodemailer from "nodemailer";

const mailer_server = process.env.mailer_server;
const mailer_port = Number(process.env.mailer_port);
const mailer_user = process.env.mailer_user;
const mailer_password = process.env.mailer_password;

// Create a transporter using SMTP
export const transporter = nodemailer.createTransport({
  host: "",
  port: mailer_port,
  secure: false, // use STARTTLS (upgrade connection to TLS after connecting)
  auth: {
    user: mailer_user,
    pass: mailer_password,
  },
});
