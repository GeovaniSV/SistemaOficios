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

      // ── Página 2: Protocolo ──
      { text: "", pageBreak: "before" },

      // Título centralizado (sem fundo, só texto bold + subtítulo)
      {
        stack: [
          {
            text: "PROTOCOLO DE ASSINATURA ELETRONICA",
            fontSize: 14,
            bold: true,
            alignment: "center",
            margin: [0, 0, 0, 4],
          },
          {
            text: "Documento assinado digitalmente conforme MP nº 2.200-2/2001",
            fontSize: 9,
            color: "#666666",
            alignment: "center",
          },
        ],
        margin: [0, 0, 0, 20],
      },
      {
        canvas: [
          {
            type: "line",
            x1: 0,
            y1: 0,
            x2: 451,
            y2: 0,
            lineWidth: 1,
            lineColor: "#cccccc",
          },
        ],
        margin: [0, 0, 0, 20],
      },

      // ── Informações do Documento ──
      {
        text: "INFORMAÇÕES DO DOCUMENTO",
        fontSize: 9,
        bold: true,
        color: "#888888",
        letterSpacing: 1,
        margin: [0, 0, 0, 12],
      },
      {
        columns: [
          {
            stack: [
              { text: "Identificação", fontSize: 9, color: "#4a90d9" },
              {
                text: configuration.oficioNumero,
                fontSize: 13,
                bold: true,
                color: "#1a1a1a",
                margin: [0, 2, 0, 0],
              },
            ],
            width: "50%",
          },
          {
            stack: [
              { text: "Assunto", fontSize: 9, color: "#4a90d9" },
              {
                text: configuration.oficioAssunto,
                fontSize: 13,
                bold: true,
                color: "#1a1a1a",
                margin: [0, 2, 0, 0],
              },
            ],
            width: "50%",
          },
        ],
        margin: [0, 0, 0, 16],
      },
      {
        text: "Código Hash (SHA-256)",
        fontSize: 9,
        color: "#4a90d9",
        margin: [0, 0, 0, 4],
      },
      {
        text: configuration.hash ?? "—",
        fontSize: 9,
        color: "#333333",
        margin: [0, 0, 0, 20],
      },
      {
        canvas: [
          {
            type: "line",
            x1: 0,
            y1: 0,
            x2: 451,
            y2: 0,
            lineWidth: 1,
            lineColor: "#cccccc",
          },
        ],
        margin: [0, 0, 0, 20],
      },

      // ── Signatários ──
      {
        text: "SIGNATÁRIOS",
        fontSize: 9,
        bold: true,
        color: "#888888",
        margin: [0, 0, 0, 12],
      },
      {
        // Card do signatário com borda
        table: {
          widths: ["*", 80],
          body: [
            [
              {
                stack: [
                  {
                    text: configuration.oficioAutor,
                    fontSize: 12,
                    bold: true,
                    color: "#1a1a1a",
                  },
                  {
                    text: configuration.oficioAutorCargo,
                    fontSize: 10,
                    color: "#666666",
                    margin: [0, 2, 0, 8],
                  },
                  {
                    text: `Data/Hora: ${"26/06/2026 15:45"} (Horário de Brasília)`,
                    fontSize: 9,
                    color: "#4a90d9",
                  },
                  {
                    text: `Autenticação: ${"Senha de Sistema"}`,
                    fontSize: 9,
                    color: "#4a90d9",
                    margin: [0, 2, 0, 0],
                  },
                ],
                border: [false, false, false, false],
                margin: [0, 0, 0, 0],
              },
              {
                // Badge ASSINADO à direita
                stack: [
                  {
                    // Ícone escudo (simulado com texto/canvas)
                    canvas: [
                      {
                        type: "rect",
                        x: 16,
                        y: 0,
                        w: 28,
                        h: 28,
                        r: 4,
                        color: "#f0fdf4",
                      },
                      // triângulo/escudo aproximado
                      {
                        type: "polyline",
                        points: [
                          { x: 30, y: 4 },
                          { x: 36, y: 8 },
                          { x: 36, y: 18 },
                          { x: 30, y: 24 },
                          { x: 24, y: 18 },
                          { x: 24, y: 8 },
                        ],
                        closePath: true,
                        color: "#16a34a",
                      },
                    ],
                    margin: [0, 0, 0, 4],
                  },
                  {
                    text: "ASSINADO",
                    fontSize: 8,
                    bold: true,
                    color: "#16a34a",
                    alignment: "center",
                  },
                ],
                border: [false, false, false, false],
                alignment: "center",
                margin: [0, 4, 0, 0],
              },
            ],
          ],
        },
        layout: {
          hLineWidth: () => 1,
          vLineWidth: () => 0,
          hLineColor: () => "#e5e7eb",
          paddingLeft: () => 12,
          paddingRight: () => 12,
          paddingTop: () => 12,
          paddingBottom: () => 12,
        },
        margin: [0, 0, 0, 20],
      },
      {
        canvas: [
          {
            type: "line",
            x1: 0,
            y1: 0,
            x2: 451,
            y2: 0,
            lineWidth: 1,
            lineColor: "#cccccc",
          },
        ],
        margin: [0, 0, 0, 20],
      },

      // ── Verificação de Autenticidade ──
      {
        text: "VERIFICAÇÃO DE AUTENTICIDADE",
        fontSize: 9,
        bold: true,
        color: "#888888",
        margin: [0, 0, 0, 12],
      },
      {
        text: "A autenticidade deste documento e de suas assinaturas pode ser verificada acessando o portal de validação através do link abaixo:",
        fontSize: 10,
        color: "#444444",
        margin: [0, 0, 0, 12],
      },
      {
        // Linha com URL + QR
        columns: [
          {
            stack: [
              // Caixa da URL
              {
                table: {
                  widths: ["*"],
                  body: [
                    [
                      {
                        text: "https://oficiopro.com.br/validacao",
                        fontSize: 10,
                        color: "#1d4ed8",
                        border: [true, true, true, true],
                        margin: [8, 6, 8, 6],
                      },
                    ],
                  ],
                },
                layout: {
                  hLineColor: () => "#bfdbfe",
                  vLineColor: () => "#bfdbfe",
                },
                margin: [0, 0, 0, 8],
              },
              // Linha "Informe o código"
              {
                columns: [
                  {
                    text: "Informe o seguinte código:",
                    fontSize: 9,
                    color: "#444444",
                    width: "auto",
                    margin: [0, 6, 6, 0],
                  },
                  {
                    table: {
                      widths: ["*"],
                      body: [
                        [
                          {
                            text: `${configuration.hash?.slice(0, 4).toUpperCase()}-${configuration.hash?.slice(4, 8).toUpperCase()}-${configuration.hash?.slice(8, 12).toUpperCase()}-${configuration.hash?.slice(12, 16).toUpperCase()}`,
                            fontSize: 9,
                            bold: true,
                            color: "#1a1a1a",
                            border: [true, true, true, true],
                            margin: [8, 4, 8, 4],
                          },
                        ],
                      ],
                    },
                    layout: {
                      hLineColor: () => "#cccccc",
                      vLineColor: () => "#cccccc",
                    },
                  },
                ],
              },
            ],
            width: "*",
          },
          // QR Code placeholder (substitua por imagem gerada com qrcode lib)
          {
            canvas: [
              {
                type: "rect",
                x: 0,
                y: 0,
                w: 52,
                h: 52,
                lineWidth: 1,
                lineColor: "#cccccc",
              },
              // quadradinhos simulando QR
              { type: "rect", x: 4, y: 4, w: 14, h: 14, color: "#1a1a1a" },
              { type: "rect", x: 34, y: 4, w: 14, h: 14, color: "#1a1a1a" },
              { type: "rect", x: 4, y: 34, w: 14, h: 14, color: "#1a1a1a" },
              { type: "rect", x: 20, y: 20, w: 6, h: 6, color: "#1a1a1a" },
              { type: "rect", x: 34, y: 34, w: 6, h: 6, color: "#1a1a1a" },
              { type: "rect", x: 42, y: 34, w: 6, h: 6, color: "#1a1a1a" },
              { type: "rect", x: 34, y: 42, w: 6, h: 6, color: "#1a1a1a" },
            ],
            width: 52,
            margin: [12, 0, 0, 0],
          },
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
