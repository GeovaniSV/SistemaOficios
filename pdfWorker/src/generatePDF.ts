var pdfmake = require("pdfmake");
import publishToqueue from "./publisher";
import { uploadPDFToS3 } from "./publishPDF";
import crypto from "crypto";
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
  oficioAutor: string;
  oficioAutorCargo: string;
};

const fonts = {
  Roboto: {
    normal: "fonts/Roboto/static/Roboto-Regular.ttf",
    bold: "fonts/Roboto/static/Roboto-Bold.ttf",
    medium: "fonts/Roboto/static/Roboto-Medium.ttf",
    italics: "fonts/Roboto/static/Roboto-Italic.ttf",
    bolditalics: "fonts/Roboto/static/Roboto-MediumItalic.ttf",
  },
};
pdfmake.addFonts(fonts);

pdfmake.setUrlAccessPolicy((url: string) => {
  return url.startsWith(
    "https://www.oabsinop.com.br/images/logo-oabsinop-40anos.png",
  );
});

pdfmake.setLocalAccessPolicy((path: string) => {
  return true;
});

export async function generatePDF(data: string) {
  const pdfData: PDFData = JSON.parse(data);

  const configuration = {
    oficioNumero: pdfData.oficioNumero,
    oficioDestinatarioTratamento: pdfData.oficioDestinatarioTratamento,
    oficioDestinatarioNome: pdfData.oficioDestinatarioNome,
    oficioDestinatarioCargo: pdfData.oficioDestinatarioCargo,
    oficioDestinatarioInstituicao: pdfData.oficioDestinatarioInstituicao,
    oficioAssunto: pdfData.oficioAssunto,
    oficioCorpo: pdfData.oficioCorpo,
    oficioDestinatario: pdfData.oficioDestinatario,
    oficioAutor: "João Silva",
    oficioAutorCargo: "Diretoria",
  };

  const hash = `${Date.now()}${crypto.randomUUID()}`;
  // header
  const docDefinition: any = {
    pageSize: "A4",
    pageMargins: [72, 180, 72, 120], // left, top, right, bottom

    defaultStyle: {
      font: "Roboto",
      fontSize: 12,
    },

    patterns: [],

    header: function (page: number, pages: number) {
      return {
        stack: [
          {
            image: "logo",
            width: 50,
            height: 25,
            alignment: "center",
            margin: [0, 0, 0, 20],
          },
          {
            text: "ORDEM DOS ADVOGADOS DO BRASIL",
            fontSize: 16,
            bold: true,
            alignment: "center",
            margin: [0, 0, 0, 10],
          },
          {
            text: "SECCIONAL MATO GROSSO",
            fontSize: 14,
            medium: true,
            alignment: "center",
            margin: [0, 0, 0, 10],
          },
          {
            text: "6ª SUBSEÇÃO — SINOP",
            fontSize: 12,
            alignment: "center",
            margin: [0, 0, 0, 20],
          },
          {
            canvas: [
              {
                type: "line",
                x1: 72,
                y1: 0,
                x2: 523,
                y2: 0,
                lineWidth: 1.5,
                lineColor: "#000000", // ← vermelho pra testar
              },
            ],
          },
        ],
        margin: [0, 20, 0, 0],
      };
    },

    footer: function (page: number, pages: number) {
      return {
        stack: [
          {
            canvas: [
              {
                type: "line",
                x1: 72,
                y1: 0,
                x2: 523,
                y2: 0,
                lineWidth: 1.5,
                lineColor: "#000000", // ← vermelho pra testar
              },
            ],
            margin: [0, 0, 0, 20],
          },
          {
            image: "logo",
            width: 50,
            height: 25,
            alignment: "center",
            margin: [0, 0, 0, 20],
          },
          {
            text: "OAB Mato Grosso 6ª subseção - Sinop",
            bold: true,
            alignment: "center",
            fontSize: 12,
          },
        ],
        margin: [0, 10, 0, 0],
      };
    },

    content: [
      {
        text: `Ofício nº ${configuration.oficioNumero}`,
        alignment: "right",
        fontSize: 12,
        margin: [0, 0, 0, 20],
      },
      {
        text: `${configuration.oficioDestinatarioTratamento}\n${configuration.oficioDestinatarioCargo} ${configuration.oficioDestinatarioNome} (${configuration.oficioDestinatarioInstituicao}`,
        alignment: "left",
        fontSize: 12,
        margin: [0, 0, 0, 20],
      },
      {
        text: `Assunto: ${configuration.oficioAssunto}`,
        bold: true,
        fontSize: 12,
        margin: [0, 0, 0, 20],
      },

      ...configuration.oficioCorpo
        .split("\n\n")
        .filter((p) => p.trim())
        .map((p) => ({
          text: p.trim().replace(/-/g, "\u2011"),
          alignment: "justify" as const,
          fontSize: 12,
          margin: [0, 0, 0, 12],
          noWrap: false,
          preserveLeadingSpaces: true,
          characterSpacing: 0,
        })),

      {
        stack: [
          {
            text: `${configuration.oficioAutor}`,
            alignment: "center",
            bold: true,
            fontSize: 12,
          },
          {
            text: `${configuration.oficioAutorCargo}`,
            alignment: "center",
            fontSize: 12,
          },
        ],
        absolutePosition: { x: 72, y: 680 }, // ← y fixo, ajusta conforme necessário
      },
    ],

    images: {
      logo: "https://www.oabsinop.com.br/images/logo-oabsinop-40anos.png",
    },
  };

  const pdfPath = `./pdfs/${hash}.pdf`;

  pdfmake
    .createPdf(docDefinition)
    .write(pdfPath)
    .then(
      () => {
        // uploadPDFToS3(pdfPath, `${hash}.pdf`);
        publishToqueue({
          oficioAssunto: pdfData.oficioAssunto,
          oficioDestinatario: pdfData.oficioDestinatario,
          oficio: hash,
        });
      },
      (err: any) => {
        console.error(err);
      },
    );
}
