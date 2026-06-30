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
    pageMargins: [72, 40, 72, 160], // pequeno e fixo pras duas páginas

    defaultStyle: {
      font: "Roboto",
      fontSize: 12,
      lineHeight: 1.5,
    },

    footer: function (page: number, pages: number) {
      if (page > 1) return { text: "", margin: [0, -160, 0, 0] };
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
            margin: [0, 0, 0, 12],
          },
          {
            image: "logo",
            width: 50,
            height: 25,
            alignment: "center",
            margin: [0, 0, 0, 12],
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

    // SEM função header/footer — tudo dentro do content, controlado manualmente
    content: [
      // ════════════════ CABEÇALHO (somente página 1) ════════════════
      {
        image: "logo",
        width: 50,
        height: 25,
        alignment: "center",
        margin: [0, 0, 0, 10],
      },
      {
        text: headerLines[0] ?? "",
        fontSize: 14,
        bold: true,
        alignment: "center",
        margin: [0, 0, 0, 5],
      },
      {
        text: headerLines[1] ?? "",
        fontSize: 12,
        alignment: "center",
        margin: [0, 0, 0, 5],
      },
      {
        text: headerLines[2] ?? "",
        fontSize: 11,
        alignment: "center",
        margin: [0, 0, 0, 5],
      },
      {
        canvas: [
          {
            type: "line",
            x1: 0,
            y1: 0,
            x2: 451,
            y2: 0,
            lineWidth: 1.5,
            lineColor: "#000000",
          },
        ],
        margin: [0, 0, 0, 20],
      },

      // ════════════════ CONTEÚDO DO OFÍCIO ════════════════
      {
        text: configuration.oficioNumero,
        alignment: "right",
        fontSize: 12,
        margin: [0, 10, 0, 20],
      },
      {
        text: configuration.oficioDestinatarioTratamento,
        alignment: "left",
        fontSize: 12,
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
        })),

      // ════════════════ PÁGINA 2: PROTOCOLO (sem header/footer institucional) ════════════════
      { text: "", pageBreak: "before" },

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
        margin: [0, 0, 0, 14],
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
        margin: [0, 0, 0, 14],
      },

      // ── Informações do Documento ──
      {
        text: "INFORMAÇÕES DO DOCUMENTO",
        fontSize: 9,
        bold: true,
        color: "#888888",
        margin: [0, 0, 0, 10],
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
        margin: [0, 0, 0, 12],
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
        margin: [0, 0, 0, 14],
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
        margin: [0, 0, 0, 14],
      },

      // ── Signatários ──
      {
        text: "SIGNATÁRIOS",
        fontSize: 9,
        bold: true,
        color: "#888888",
        margin: [0, 0, 0, 10],
      },
      {
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
                    margin: [0, 2, 0, 6],
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
              },
              {
                // Badge ASSINADO — ícone via canvas (não usa caractere de fonte)
                stack: [
                  {
                    canvas: [
                      {
                        type: "ellipse",
                        x: 14,
                        y: 8,
                        r1: 8,
                        r2: 8,
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
              },
            ],
          ],
        },
        layout: {
          hLineWidth: () => 1,
          vLineWidth: () => 0,
          hLineColor: () => "#e5e7eb",
          paddingLeft: () => 10,
          paddingRight: () => 10,
          paddingTop: () => 8,
          paddingBottom: () => 8,
        },
        margin: [0, 0, 0, 14],
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
        margin: [0, 0, 0, 14],
      },

      // ── Verificação de Autenticidade ──
      {
        text: "VERIFICAÇÃO DE AUTENTICIDADE",
        fontSize: 9,
        bold: true,
        color: "#888888",
        margin: [0, 0, 0, 10],
      },
      {
        text: "A autenticidade deste documento e de suas assinaturas pode ser verificada acessando o portal de validação através do link abaixo:",
        fontSize: 10,
        color: "#444444",
        margin: [0, 0, 0, 10],
      },
      {
        columns: [
          {
            stack: [
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
                            text: "hashCodigo", // ← código real do UUID, não mais "sdsa"
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

      // ════════════════ FIM — SEM RODAPÉ INSTITUCIONAL NA PÁGINA 2 ════════════════
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
