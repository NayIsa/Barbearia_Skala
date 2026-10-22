# 💈 Barbearia Skala — Sistema de Agendamento

Sistema web para agendamento de serviços em barbearia, desenvolvido em **Laravel 13** com banco de dados **SQLite**. Permite que clientes escolham serviços e horários disponíveis, e que funcionários visualizem e cancelem agendamentos do dia.

---

## 📋 Índice

- [Como rodar](#-como-rodar)
- [O que foi feito](#-o-que-foi-feito)
- [Decisões e suposições](#-decisões-e-suposições)
- [Planejado x entregue](#-planejado-x-entregue)

---

## 🚀 Como rodar

### Pré-requisitos

| Ferramenta | Versão mínima |
|---|---|
| PHP | 8.3 |
| Composer | 2.x |
| Node.js + npm | 18+ |

> **Windows:** caso não tenha PHP e Composer instalados, execute no PowerShell (como administrador):
> ```powershell
> Set-ExecutionPolicy Bypass -Scope Process -Force; [System.Net.ServicePointManager]::SecurityProtocol = [System.Net.ServicePointManager]::SecurityProtocol -bor 3072; iex ((New-Object System.Net.WebClient).DownloadString('https://php.new/install/windows/8.5'))
> ```
> Após a instalação, **feche e reabra o terminal**.

---

### Passo a passo

**1. Clone o repositório**
```bash
git clone <url-do-repositorio>
cd barbearia-teste
```

**2. Instale as dependências PHP**
```bash
composer install
```

**3. Configure o arquivo de ambiente**
```bash
cp .env.example .env
php artisan key:generate
```

**4. Crie o banco de dados e execute as migrations**
```bash
touch database/database.sqlite   # Linux/macOS
# No Windows PowerShell:
# New-Item database/database.sqlite -ItemType File
php artisan migrate
```

**5. Instale as dependências JavaScript e compile os assets**
```bash
npm install
npm run build
```

**6. Suba o servidor de desenvolvimento**
```bash
php artisan serve
```

O sistema estará disponível em **http://localhost:8000**.

---

### Atalho: setup completo em um comando

O `composer.json` inclui um script que executa todos os passos acima de uma vez (exceto a criação do arquivo `.sqlite`):

```bash
# Crie o arquivo do banco antes:
touch database/database.sqlite   # Linux/macOS
# New-Item database/database.sqlite -ItemType File  # Windows

composer setup
php artisan serve
```

---

### Variáveis de ambiente relevantes

| Variável | Padrão | Descrição |
|---|---|---|
| `APP_NAME` | `Laravel` | Nome exibido na aplicação |
| `APP_ENV` | `local` | Ambiente (`local`, `production`) |
| `APP_DEBUG` | `true` | Exibir erros detalhados |
| `DB_CONNECTION` | `sqlite` | Driver do banco (SQLite por padrão) |

> Para usar MySQL/PostgreSQL, descomente e preencha `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME` e `DB_PASSWORD` no `.env`.

---

### Executar os testes

```bash
php artisan test
```

---

## ✅ O que foi feito

### Área do Cliente

| Funcionalidade | Descrição |
|---|---|
| **Listagem de serviços** | Página inicial (`/`) exibe todos os serviços cadastrados com nome, preço e horários livres para o dia selecionado |
| **Seleção de data** | Cliente pode navegar entre datas; datas passadas são ignoradas e substituídas por hoje |
| **Horários disponíveis** | Grade de horários calculada em tempo real, excluindo horários já ocupados e os já passados no dia atual |
| **Formulário de agendamento** | Ao escolher serviço e horário (`/agendar/{servico}`), cliente informa nome e telefone |
| **Confirmação com feedback** | Após agendamento bem-sucedido, mensagem de confirmação com data/hora/serviço é exibida |
| **Validação de dados** | Telefone aceita DDD + 8 ou 9 dígitos; campos obrigatórios; data deve ser hoje ou futura |

### Área dos Funcionários

| Funcionalidade | Descrição |
|---|---|
| **Agenda do dia** | `/agenda` lista todos os agendamentos de um dia em ordem cronológica, com total de receita |
| **Navegação por data** | Funcionário pode consultar qualquer dia, incluindo passados |
| **Cancelamento** | Cada agendamento pode ser removido diretamente pela agenda |
| **Cadastro de serviços** | `/servicos` permite criar novos serviços (nome, duração em minutos, preço) |
| **Exclusão de serviços** | Serviço só pode ser excluído se não tiver agendamentos vinculados |

### Controle de Conflitos (Concorrência)

Verificação e gravação de agendamentos ocorrem dentro de uma **transação com `lockForUpdate`**, evitando duplo agendamento simultâneo no mesmo horário.

### Configuração do Expediente

O arquivo `config/barbearia.php` centraliza as regras de horário:

```php
'abertura'   => 9 * 60,   // 09:00
'fechamento' => 18 * 60,  // 18:00
'passo'      => 10,        // grade de 10 em 10 minutos
```

---

## 🧠 Decisões e suposições

### Banco de dados: SQLite
Optou-se por **SQLite** como padrão para facilitar o setup (zero configuração de servidor). O sistema é compatível com MySQL/PostgreSQL bastando alterar as variáveis no `.env`.

### Horários em minutos inteiros
Os campos `inicio` e `fim` da tabela `agendamentos` armazenam **minutos desde a meia-noite** (`SMALLINT`). Isso simplifica cálculos de sobreposição e ordenação sem depender de campos `TIME` com comportamento variável entre bancos.

### Grade de horários fixa (passo de 10 min)
A grade de início dos agendamentos avança de 10 em 10 minutos. Isso evita fragmentação de agenda e é facilmente ajustável no arquivo `config/barbearia.php`.

### Ausência de autenticação na área de funcionários
As rotas `/agenda` e `/servicos` estão públicas por enquanto. O código já contém comentários (`// Futuro projeto: proteger com autenticação`) indicando onde o middleware de autenticação deve ser aplicado. Optou-se por não implementar login neste escopo para manter o foco nas regras de negócio de agendamento.

### Formatação do telefone no modelo
O telefone é salvo apenas com dígitos (10 ou 11) e formatado no accessor `telefoneFormatado` do model `Agendamento`, mantendo o dado limpo no banco e a apresentação centralizada no model.

### Normalização do preço no FormRequest
O `StoreServicoRequest` normaliza entradas no formato brasileiro (`1.250,50` → `1250.50`) antes da validação, evitando erros de parsing e permitindo entrada natural para o usuário.

### Sem seeders de dados iniciais
Não foram criados seeders. O banco começa vazio; o próprio sistema permite cadastrar serviços pela interface em `/servicos`.

---

## 📊 Planejado x entregue

### O que foi entregue conforme planejado

| Item | Status |
|---|---|
| CRUD de serviços (criar, listar, excluir) | ✅ Entregue |
| Agendamento com escolha de data e horário | ✅ Entregue |
| Cálculo de horários livres em tempo real | ✅ Entregue |
| Visualização da agenda diária pelo funcionário | ✅ Entregue |
| Cancelamento de agendamentos | ✅ Entregue |
| Proteção contra conflito de horários (concorrência) | ✅ Entregue |
| Validação dos dados de entrada (cliente e serviço) | ✅ Entregue |

### O que ficou de fora e por quê

| Item | Motivo |
|---|---|
| **Autenticação de funcionários** | Fora do escopo do teste. A estrutura está preparada para receber middleware de autenticação nas rotas de `/agenda` e `/servicos` sem refatoração |
| **Edição de serviços** | Não solicitado no escopo; o fluxo de excluir e recriar cobre o caso de uso básico |
| **Edição de agendamentos** | Optou-se por cancelar e novo agendamento em vez de edição, reduzindo complexidade e eliminando ambiguidade de conflito durante a edição |
| **Notificações (SMS/e-mail)** | Fora do escopo. O `MAIL_MAILER=log` no `.env` já está configurado para facilitar adição futura |
| **Testes automatizados de feature** | Apenas o teste de exemplo padrão do Laravel está presente. Os testes de feature específicos (agendamento, conflito, serviços) não foram escritos no prazo |
| **Painel administrativo avançado** | Relatórios, filtros por período e estatísticas não foram incluídos por estarem fora do escopo mínimo |

---

## 🗂️ Estrutura do projeto

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── AgendaController.php        # Agenda do funcionário
│   │   ├── AgendamentoController.php   # Fluxo de agendamento do cliente
│   │   ├── HomeController.php          # Página inicial com serviços
│   │   └── ServicoController.php       # CRUD de serviços
│   └── Requests/
│       ├── StoreAgendamentoRequest.php  # Validação do agendamento
│       └── StoreServicoRequest.php      # Validação do serviço
├── Models/
│   ├── Agendamento.php
│   └── Servico.php
└── Services/
    └── Disponibilidade.php             # Regras de horário e conflito

config/
└── barbearia.php                       # Expediente e grade de horários

database/
└── migrations/
    ├── ...create_servicos_table.php
    └── ...create_agendamentos_table.php

resources/views/
├── home.blade.php                      # Vitrine de serviços e horários
├── agenda/index.blade.php              # Agenda do funcionário
├── agendamentos/create.blade.php       # Formulário de agendamento
└── servicos/index.blade.php            # Gestão de serviços
```

---

## 🔧 Stack

- **Backend:** PHP 8.3 + Laravel 13
- **Banco de dados:** SQLite (padrão) — compatível com MySQL/PostgreSQL
- **Frontend:** Blade + Vite (CSS/JS mínimos, sem frameworks JS)
- **Concorrência:** Transações com `DB::transaction` + `lockForUpdate`

---------------------------------------------------------------------------------------------------------------------------

Principais prompts Usados (Claude.IA):
    Utilizando como base as instruções a seguir, realize a criação de arquivos para a existencia de um site de uma barbearia hipotética.
    1. Cadastro de serviços. O barbeiro cadastra os serviços oferecidos, com nome, duração em
    minutos e preço. Exemplos: Corte (40 min, R$ 50,00) e Barba (20 min, R$ 30,00).
    2. Agendamento pelo cliente. O cliente escolhe um serviço, uma data e um horário disponível,
    e informa nome e telefone.
    3. Horário de atendimento. A barbearia atende das 9h às 18h.
    4. Sem conflito. Não pode haver dois agendamentos no mesmo horário.
    5. Agenda do dia. O barbeiro vê a lista de agendamentos de um dia, em ordem de horário

    ---x---

    Certo, fiz leves alterações para melhor compreensão, vamos para a parte do polimento agora.
    A pagina "Home" deve ser uma pagina inicial onde o cliente vai ver as opções de serviço, preço e horários disponíveis. Ou seja, o cliente não deve poder cadastrar novos serviços.
    A pagina "Agendamento" é onde o cliente será levado após escolher "agendar" nas opções de serviço, ai sim podendo agendar seu horário com a informações vistas anteriormente (devem aparecer aqui novamente).
    A pagina "Visualizar agenda" deve ter um aviso antes do conteúdo escrito "Isto será visto apenas pelos funcionários em um futuro projeto"

    ---x---

    Com as novas alterações e a ideia recaptulada, organize os arquivos existentes para quadrar com o estilo de arquivos para Laravel. Mantendo a estética ao maximo no produto final e realizando corretamente a divisão de pastas.
