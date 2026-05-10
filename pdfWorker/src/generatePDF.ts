import puppeteer from "puppeteer";
import fs from "fs";
import path from "path";
import { uploadPDFToS3 } from "./publishPDF";
import publishToqueue from "./publisher";
//"file:///C:/Users/Usuario/OneDrive/Desktop/MeusProjetos/SistemaOficios/pdfWorker/src/templates/index.html"

type PDFData = {
  oficioNumero: string;
  oficioDestinatarioTratamento: string;
  oficioDestinatarioNome: string;
  oficioDestinatarioCargo: string;
  oficioDestinatarioInstituicao: string;
  oficioAssunto: string;
  oficioCorpo: string;
  oficioDestinatario: string;
};

export async function generatePDF(data: string) {
  const pdfData: PDFData = JSON.parse(data);

  let html = fs.readFileSync(
    path.resolve(__dirname, "templates", "index.html"),
    "utf-8",
  );

  const pdfName = `oficio_${Date.now()}.pdf`;

  const QRCode = `https://chart.googleapis.com/chart?cht=qr&chs=150x150&chl=${encodeURIComponent(`https://pub-1649ce4f14c84ed09aaca2bd47e12561.r2.dev/oficios/${pdfName}`)}`;

  html = html
    .replace("{{oficioNumero}}", pdfData.oficioNumero)
    .replace(
      "{{oficioDestinatarioTratamento}}",
      pdfData.oficioDestinatarioTratamento,
    )
    .replace("{{oficioDestinatarioNome}}", pdfData.oficioDestinatarioNome)
    .replace("{{oficioDestinatarioCargo}}", pdfData.oficioDestinatarioCargo)
    .replace(
      "{{oficioDestinatarioInstituicao}}",
      pdfData.oficioDestinatarioInstituicao,
    )
    .replace("{{oficioAssunto}}", pdfData.oficioAssunto)
    .replace("{{oficioCorpo}}", pdfData.oficioCorpo);

  const browser = await puppeteer.launch({
    executablePath:
      "C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe",
    headless: true,
  });
  const page = await browser.newPage();
  await page.setContent(html, { waitUntil: "networkidle0" });

  await page.pdf({
    path: `./pdfs/${pdfName}`,
    format: "A4",
    printBackground: true,
    margin: { top: "0", bottom: "0", left: "0", right: "0" },
  });

  await browser.close();

  // await uploadPDFToS3(`./pdfs/${pdfName}`, pdfName);

  await publishToqueue({
    oficioAssunto: pdfData.oficioAssunto,
    oficioDestinatario: pdfData.oficioDestinatario,
    oficio: pdfName,
  });
}
