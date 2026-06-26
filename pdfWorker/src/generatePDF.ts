var pdfmake = require("pdfmake");
import publishToqueue from "./publisher";
import { uploadPDFWithRetry } from "./publishPDF";
import crypto from "crypto";
import { startWorker } from "./worker";

export type PDFData = {
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
  userId: number;
  oficioHeader: string;
  oficioFooter: string;
  hash: string;
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
    oficioAutor: pdfData.oficioAutor,
    oficioAutorCargo: pdfData.oficioAutorCargo,
    userId: pdfData.userId,
    oficioHeader: pdfData.oficioHeader,
    oficioFooter: pdfData.oficioFooter,
    hash: pdfData.hash,
  };

  const headerLines = configuration.oficioHeader.split("\n");

  const docDefinition: any = {
    pageSize: "A4",
    pageMargins: [72, 180, 72, 160], // left, top, right, bottom

    defaultStyle: {
      font: "Roboto",
      fontSize: 12,
      lineHeight: 1.5,
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
            margin: [0, 0, 0, 10],
          },
          {
            text: `${headerLines[0]}`,
            fontSize: 14,
            bold: true,
            alignment: "center",
            margin: [0, 0, 0, 5],
          },
          {
            text: `${headerLines[1]}`,
            fontSize: 12,
            medium: true,
            alignment: "center",
            margin: [0, 0, 0, 5],
          },
          {
            text: `${headerLines[2]}`,
            fontSize: 11,
            alignment: "center",
            margin: [0, 0, 0, 5],
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
            margin: [0, 0, 0, 8],
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
            text: `${configuration.oficioFooter}`,
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
        text: `${configuration.oficioNumero}`,
        alignment: "right",
        fontSize: 12,
        margin: [0, 10, 0, 20],
      },
      {
        text: `${configuration.oficioDestinatarioTratamento}`,
        alignment: "left",
        fontSize: 12,
        margin: [0, 0, 0, 0],
      },
      {
        text: `${configuration.oficioDestinatarioCargo} ${configuration.oficioDestinatarioNome} (${configuration.oficioDestinatarioInstituicao})`,
        alignment: "left",
        bold: true,
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

      // Assinatura (ainda na página 1)
      {
        margin: [0, 48, 0, 0],
        stack: [
          {
            text: configuration.oficioAutor,
            alignment: "center",
            bold: true,
            fontSize: 12,
          },
          {
            text: configuration.oficioAutorCargo,
            alignment: "center",
            fontSize: 12,
          },
        ],
      },

      // ── Página 2: Protocolo ──
      { text: "", pageBreak: "before" },

      // Cabeçalho azul escuro
      {
        canvas: [
          { type: "rect", x: 0, y: 0, w: 451, h: 52, r: 4, color: "#1a1a2e" },
        ],
        margin: [0, 0, 0, 0],
      },
      {
        stack: [
          {
            text: "PROTOCOLO DE ASSINATURA ELETRÔNICA",
            fontSize: 13,
            bold: true,
            color: "#ffffff",
            alignment: "center",
          },
          {
            text: "Documento assinado digitalmente conforme MP nº 2.200-2/2001",
            fontSize: 9,
            color: "#9fa3b8",
            alignment: "center",
          },
        ],
        relativePosition: { x: 0, y: -42 },
        margin: [0, 0, 0, 28],
      },

      // Informações do Documento
      {
        text: "Informações do Documento",
        bold: true,
        fontSize: 11,
        color: "#1a1a2e",
        margin: [0, 0, 0, 4],
      },
      {
        canvas: [
          {
            type: "line",
            x1: 0,
            y1: 0,
            x2: 451,
            y2: 0,
            lineWidth: 0.5,
            lineColor: "#cccccc",
          },
        ],
        margin: [0, 0, 0, 10],
      },
      {
        columns: [
          {
            stack: [
              { text: "Identificação", fontSize: 9, color: "#888888" },
              {
                text: configuration.oficioNumero,
                fontSize: 13,
                bold: true,
                margin: [0, 2, 0, 0],
              },
            ],
          },
          {
            stack: [
              { text: "Assunto", fontSize: 9, color: "#888888" },
              {
                text: configuration.oficioAssunto,
                fontSize: 13,
                bold: true,
                margin: [0, 2, 0, 0],
              },
            ],
          },
        ],
        margin: [0, 0, 0, 16],
      },
      {
        text: "Código Hash (SHA-256)",
        fontSize: 9,
        color: "#888888",
        margin: [0, 0, 0, 4],
      },
      {
        text: configuration.hash ?? "—",
        fontSize: 9,
        color: "#333333",
        background: "#f5f5f5",
        margin: [0, 0, 0, 24],
      },

      // Signatários
      {
        text: "Signatários",
        bold: true,
        fontSize: 11,
        color: "#1a1a2e",
        margin: [0, 0, 0, 4],
      },
      {
        canvas: [
          {
            type: "line",
            x1: 0,
            y1: 0,
            x2: 451,
            y2: 0,
            lineWidth: 0.5,
            lineColor: "#cccccc",
          },
        ],
        margin: [0, 0, 0, 10],
      },
      {
        table: {
          widths: [38, "*"],
          body: [
            [
              // Avatar com iniciais
              {
                canvas: [
                  {
                    type: "ellipse",
                    x: 19,
                    y: 19,
                    r1: 19,
                    r2: 19,
                    color: "#dbeafe",
                  },
                ],
                border: [false, false, false, false],
                margin: [0, 0, 0, 0],
              },
              {
                stack: [
                  {
                    text: "ABC",
                    fontSize: 13,
                    color: "#1d4ed8",
                    bold: true,
                    relativePosition: { x: -30, y: -24 },
                    alignment: "center",
                  },
                  { text: configuration.oficioAutor, fontSize: 12, bold: true },
                  {
                    text: configuration.oficioAutorCargo,
                    fontSize: 10,
                    color: "#666666",
                    margin: [0, 2, 0, 6],
                  },
                  {
                    text: `Data/Hora: ${"26/06/2026 15:32"}`,
                    fontSize: 9,
                    color: "#666666",
                  },
                  {
                    text: `Autenticação: ${"Senha de Sistema"}`,
                    fontSize: 9,
                    color: "#666666",
                    margin: [0, 2, 0, 8],
                  },
                  {
                    canvas: [
                      {
                        type: "rect",
                        x: 0,
                        y: 0,
                        w: 70,
                        h: 18,
                        r: 9,
                        color: "#dcfce7",
                      },
                    ],
                    margin: [0, 0, 0, 0],
                  },
                  {
                    text: "✓ Assinado",
                    fontSize: 9,
                    bold: true,
                    color: "#16a34a",
                    relativePosition: { x: 8, y: -16 },
                  },
                ],
                border: [false, false, false, false],
              },
            ],
          ],
        },
        layout: "noBorders",
        margin: [0, 0, 0, 24],
      },

      // Verificação
      {
        text: "Verificação de Autenticidade",
        bold: true,
        fontSize: 11,
        color: "#1a1a2e",
        margin: [0, 0, 0, 4],
      },
      {
        canvas: [
          {
            type: "line",
            x1: 0,
            y1: 0,
            x2: 451,
            y2: 0,
            lineWidth: 0.5,
            lineColor: "#cccccc",
          },
        ],
        margin: [0, 0, 0, 10],
      },
      {
        columns: [
          {
            stack: [
              {
                text: "A autenticidade deste documento pode ser verificada acessando o portal de validação:",
                fontSize: 10,
                color: "#444444",
                margin: [0, 0, 0, 8],
              },
              {
                text: "https://oficiopro.com.br/validacao",
                fontSize: 10,
                color: "#1d4ed8",
                margin: [0, 0, 0, 12],
              },
              {
                text: `Código: ${configuration.hash?.slice(0, 4).toUpperCase()}-${configuration.hash?.slice(4, 8).toUpperCase()}-${configuration.hash?.slice(8, 12).toUpperCase()}`,
                fontSize: 10,
                bold: true,
              },
            ],
            width: "*",
          },
          // {
          //   image: "",
          //   width: 80,
          //   height: 80,
          //   margin: [12, 0, 0, 0],
          // },
        ],
      },
    ],

    images: {
      logo: "https://www.oabsinop.com.br/images/logo-oabsinop-40anos.png",
    },
  };

  const pdfPath = `./pdfs/${pdfData.hash}.pdf`;
  pdfmake
    .createPdf(docDefinition)
    .write(pdfPath)
    .then(
      () => {
        // uploadPDFWithRetry(data, pdfPath, `${hash}.pdf`);
        publishToqueue({
          oficioAssunto: pdfData.oficioAssunto,
          oficioDestinatario: pdfData.oficioDestinatario,
          oficio: pdfData.hash + ".pdf",
          userId: pdfData.userId,
        });
      },
      (err: any) => {
        setTimeout(startWorker, 5000);
        console.error(err);
      },
    );
}
