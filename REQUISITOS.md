# Requisitos do Sistema de Ofícios

## Visão Geral

Sistema de gerenciamento de ofícios para a OAB Sinop, permitindo a criação, acompanhamento e envio de documentos oficiais com geração de PDF e notificação por e-mail.

**Stack:** Laravel 13 (API) · React 19 + TypeScript (Frontend) · Node.js (Workers) · RabbitMQ (Mensageria) · Docker

---

## Requisitos Funcionais

### RF01 — Autenticação e Controle de Acesso

- **RF01.1** O sistema deve permitir login com e-mail e senha via autenticação por token (Laravel Sanctum).
- **RF01.2** O sistema deve permitir recuperação de senha via e-mail.
- **RF01.3** O sistema deve exibir o perfil do usuário autenticado na página "Meu Perfil".
- **RF01.4** O sistema deve encerrar a sessão do usuário ao realizar logout.

---

### RF02 — Gerenciamento de Contatos

- **RF02.1** O sistema deve permitir cadastrar, editar e excluir contatos.
- **RF02.2** O sistema deve suportar dois tipos de contato: **Pessoa Física** (CPF) e **Pessoa Jurídica** (CNPJ).
- **RF02.3** O sistema deve validar e formatar automaticamente CPF (11 dígitos) e CNPJ (14 dígitos) no momento do cadastro.
- **RF02.4** Cada contato deve poder ter um endereço associado.
- **RF02.5** Cada contato deve poder ter múltiplos **responsáveis** vinculados.
- **RF02.6** O sistema deve permitir listar os responsáveis de um contato específico.

---

### RF03 — Gerenciamento de Responsáveis

- **RF03.1** O sistema deve permitir cadastrar responsáveis com nome, e-mail, tratamento (Sr./Sra./etc.), cargo e departamento.
- **RF03.2** Os responsáveis podem ser vinculados a múltiplos ofícios.
- **RF03.3** O sistema deve permitir selecionar responsáveis como destinatários de um ofício.

---

### RF04 — Gerenciamento de Ofícios

- **RF04.1** O sistema deve permitir criar novos ofícios com assunto, conteúdo, prioridade e contato de destino.
- **RF04.2** Os ofícios devem suportar três níveis de prioridade: **Baixa**, **Média** e **Alta**.
- **RF04.3** Os ofícios devem ter dois estados: **Pendente** e **Concluído**.
- **RF04.4** O sistema deve permitir associar múltiplos responsáveis a um ofício.
- **RF04.5** O sistema deve permitir visualizar uma prévia do ofício antes do envio.
- **RF04.6** O sistema deve permitir editar ofícios existentes.
- **RF04.7** O sistema deve permitir arquivar ofícios.
- **RF04.8** O sistema deve exibir a lista de ofícios com suporte a filtros avançados (por status, prioridade, data, contato).
- **RF04.9** O sistema deve fornecer um menu de contexto (clique direito) com ações rápidas sobre o ofício.
- **RF04.10** O sistema deve permitir criar ofícios a partir de **templates** pré-definidos.

---

### RF05 — Envio de Ofícios

- **RF05.1** Ao enviar um ofício, o sistema deve criar um registro de **mensagem** para cada responsável destinatário.
- **RF05.2** O sistema deve publicar uma mensagem na fila RabbitMQ para processamento assíncrono.
- **RF05.3** O **worker de PDF** deve consumir a fila, gerar o PDF do ofício com a identidade visual da OAB Sinop (fonte Roboto) e encaminhar para a fila de e-mail.
- **RF05.4** O **worker de e-mail** deve consumir a fila e enviar o e-mail com o PDF anexado para cada destinatário via Nodemailer.
- **RF05.5** O status de cada mensagem deve ser atualizado para **Enviado** ou **Erro** conforme o resultado do processamento.
- **RF05.6** O envio de um ofício deve alterar seu status para **Concluído**.

---

### RF06 — Geração de PDF

- **RF06.1** O sistema deve gerar PDFs dos ofícios utilizando a biblioteca pdfmake.
- **RF06.2** O PDF deve incluir os dados do ofício: assunto, conteúdo, destinatário, responsáveis e data.
- **RF06.3** Os PDFs gerados devem ser armazenados no diretório `/pdfs` e/ou enviados para o AWS S3.

---

### RF07 — Templates de Ofício

- **RF07.1** O sistema deve permitir criar, editar e excluir templates de ofício.
- **RF07.2** Os templates devem servir como base de conteúdo para a criação de novos ofícios.
- **RF07.3** O sistema deve exibir a lista de templates disponíveis para seleção.

---

### RF08 — Rastreamento de Mensagens

- **RF08.1** O sistema deve registrar cada envio de mensagem com status (**Pendente**, **Enviado**, **Erro**) e data/hora de envio.
- **RF08.2** O sistema deve permitir reenviar mensagens com erro via o endpoint `POST /api/messages/{id}/send-broker`.
- **RF08.3** O sistema deve exibir o histórico de mensagens associadas a cada ofício.

---

### RF09 — Configurações do Sistema

- **RF09.1** O sistema deve permitir configurar o **texto de declaração** (statement) padrão utilizado nos ofícios.
- **RF09.2** As configurações devem ser persistidas no banco de dados e acessíveis via API.

---

### RF10 — Logs de Workers

- **RF10.1** O sistema deve registrar todas as operações executadas pelos workers (PDF e e-mail) em uma tabela de auditoria (`worker_logs`).
- **RF10.2** O sistema deve expor os logs de workers via API para consulta e diagnóstico.

---

### RF11 — Validação de Documentos

- **RF11.1** O sistema deve disponibilizar uma página de **validação** para verificação de documentos.

---

## Requisitos Não Funcionais

### RNF01 — Desempenho

- **RNF01.1** O processamento de PDF e envio de e-mail deve ser **assíncrono**, sem bloquear a resposta da API ao usuário.
- **RNF01.2** A geração de PDF pelo worker deve ser concluída em tempo hábil para não comprometer a entrega de e-mails.
- **RNF01.3** O frontend deve utilizar gerenciamento de estado eficiente (Zustand) para evitar renderizações desnecessárias.

---

### RNF02 — Disponibilidade e Resiliência

- **RNF02.1** O sistema deve suportar retry automático no envio de e-mails em caso de falha transiente.
- **RNF02.2** O broker de mensagens (RabbitMQ) deve garantir a entrega das mensagens entre os serviços mesmo em caso de reinicialização dos workers.
- **RNF02.3** Cada serviço (API, pdfworker, emailworker) deve ser executável de forma independente via Docker.

---

### RNF03 — Segurança

- **RNF03.1** Todos os endpoints da API devem exigir autenticação via token Bearer (Laravel Sanctum), exceto login e recuperação de senha.
- **RNF03.2** Dados sensíveis (senhas, credenciais RabbitMQ, chaves AWS) devem ser gerenciados exclusivamente via variáveis de ambiente (`.env`), nunca versionados.
- **RNF03.3** A API deve validar todos os dados de entrada por meio de Form Requests do Laravel antes de processá-los.
- **RNF03.4** O sistema deve proteger contra injeção de SQL utilizando o ORM Eloquent com queries parametrizadas.

---

### RNF04 — Manutenibilidade

- **RNF04.1** A lógica de negócio deve estar isolada em **Services**, separada dos Controllers.
- **RNF04.2** O código deve seguir os padrões do Laravel (PSR-4, Eloquent, Form Requests, Resource Controllers).
- **RNF04.3** O frontend deve utilizar TypeScript com tipagem estrita para reduzir erros em tempo de execução.
- **RNF04.4** O projeto deve ser estruturado em módulos independentes (api, front, pdfworker, emailworker) para facilitar manutenção isolada.

---

### RNF05 — Escalabilidade

- **RNF05.1** A arquitetura baseada em filas (RabbitMQ) deve permitir escalar os workers de PDF e e-mail horizontalmente sem alteração na API.
- **RNF05.2** O banco de dados deve ser configurável (SQLite para desenvolvimento, MySQL/PostgreSQL para produção).

---

### RNF06 — Portabilidade e Implantação

- **RNF06.1** Todos os serviços devem estar containerizados com **Docker** e orquestrados via **Docker Compose**.
- **RNF06.2** O ambiente de desenvolvimento deve ser inicializável com um único comando (`docker-compose up`).
- **RNF06.3** O sistema deve suportar armazenamento de PDFs tanto local (`/pdfs`) quanto em nuvem (AWS S3).

---

### RNF07 — Usabilidade

- **RNF07.1** O frontend deve ser responsivo e utilizar Tailwind CSS para consistência visual.
- **RNF07.2** A interface deve fornecer feedback visual imediato para ações do usuário (carregamentos, erros, confirmações).
- **RNF07.3** O editor de ofícios deve permitir visualização prévia antes do envio.
- **RNF07.4** O sistema deve suportar localização em português brasileiro (locale `pt_BR`).

---

### RNF08 — Testabilidade

- **RNF08.1** A API deve contar com testes automatizados utilizando **PHPUnit**.
- **RNF08.2** O banco de dados de testes deve usar SQLite in-memory para isolamento e velocidade.
- **RNF08.3** Os models devem ter factories associadas para facilitar a criação de dados de teste.

---

### RNF09 — Documentação da API

- **RNF09.1** A API deve ter documentação gerada automaticamente via **Scribe**, acessível em ambiente de desenvolvimento.

---

## Entidades Principais

| Entidade          | Descrição                                              |
|-------------------|--------------------------------------------------------|
| `User`            | Usuário autenticado do sistema                         |
| `Contact`         | Contato destinatário (PF ou PJ)                        |
| `Responsible`     | Pessoa responsável vinculada a um contato              |
| `Oficio`          | Documento oficial gerenciado pelo sistema              |
| `OficioTemplate`  | Template reutilizável para criação de ofícios          |
| `Message`         | Registro de envio de e-mail para um responsável        |
| `OficioSetting`   | Configurações globais do sistema                       |
| `Address`         | Endereço associado a um contato                        |
| `WorkerLog`       | Log de auditoria das operações dos workers             |

---

## Fluxos Principais

### Criação e Envio de Ofício

```
Usuário → Cria Ofício → Seleciona Responsáveis → Envia
  → API cria Messages → Publica na fila RabbitMQ
  → pdfworker: gera PDF → publica na fila de e-mail
  → emailworker: envia e-mail → atualiza status da Message
```

### Validação de Documentos CPF/CNPJ

```
Cadastro de Contato → Model valida e formata CPF/CNPJ automaticamente
```

---

*Gerado em 2026-05-31*
