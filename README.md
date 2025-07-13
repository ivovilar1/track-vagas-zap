# 🤖 Track Vagas Zap

<p align="center">
  <img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="300" alt="Laravel Logo">
</p>

<p align="center">
  Um bot de WhatsApp para gerenciar e acompanhar suas candidaturas de emprego de forma simples e direta.
</p>

<p align="center">
  <img src="https://img.shields.io/badge/PHP-8.2%2B-blueviolet" alt="PHP 8.2+">
  <img src="https://img.shields.io/badge/Laravel-11.x-FF2D20?style=flat-square&logo=laravel" alt="Laravel 11.x">
  <img src="https://img.shields.io/badge/Status-Funcional-brightgreen" alt="Status: Funcional">
</p>

---

## 🚀 Funcionalidades

O Track Vagas Zap permite que você gerencie suas vagas diretamente pelo WhatsApp, oferecendo as seguintes funcionalidades:

- **Listar Candidaturas:** Visualize todas as vagas para as quais você se aplicou.
- **Adicionar Nova Candidatura:** Registre uma nova aplicação em um fluxo de conversa guiado.
- **Atualizar Candidatura:** Modifique informações de uma candidatura existente.
- **Excluir Candidatura:** Remova uma candidatura que não é mais de seu interesse.
- **Sair/Cancelar:** Cancele qualquer operação a qualquer momento e volte ao menu principal.

---

## 🛠️ Tecnologias Utilizadas

- **[Laravel 11](https://laravel.com):** Framework PHP para a estrutura do backend.
- **[Evolution API](https://evolution-api.com/):** API não-oficial para a integração com o WhatsApp.
- **PHP 8.2:** Linguagem de programação base.
- **MySQL/PostgreSQL/SQLite:** Banco de dados para persistir as informações.

---

## ⚙️ Guia de Instalação e Configuração

Siga os passos abaixo para configurar e executar o projeto em seu ambiente local.

### Pré-requisitos

- PHP 8.2 ou superior
- Composer
- Um SGBD (MySQL, PostgreSQL, etc.)
- Uma instância da [Evolution API](https://github.com/EvolutionAPI/evolution-api) funcionando.

### Passo a Passo

1.  **Clone o repositório:**
    ```bash
    git clone https://github.com/seu-usuario/track-vagas-zap.git
    cd track-vagas-zap
    ```

2.  **Instale as dependências:**
    ```bash
    composer install
    ```

3.  **Configure as Variáveis de Ambiente:**
    - Copie o arquivo de exemplo `.env.example` para `.env`.
      ```bash
      cp .env.example .env
      ```
    - Gere a chave da aplicação.
      ```bash
      php artisan key:generate
      ```
    - Configure as variáveis de ambiente no arquivo `.env` conforme a seção abaixo.

4.  **Execute as Migrations:**
    - Este comando irá criar as tabelas necessárias no banco de dados.
      ```bash
      php artisan migrate
      ```

5.  **Configure o Servidor Web:**
    - Aponte a raiz do seu servidor (Nginx, Apache) para o diretório `public/`.
    - Certifique-se de que o `mod_rewrite` (ou equivalente) está ativado.

---

## 🔑 Variáveis de Ambiente

As seguintes variáveis precisam ser configuradas em seu arquivo `.env` para que o bot funcione corretamente.

| Variável                 | Descrição                                                                          | Exemplo                                        |
| ------------------------ | ---------------------------------------------------------------------------------- | ---------------------------------------------- |
| `DB_CONNECTION`          | A conexão de banco de dados a ser usada.                                           | `mysql`                                        |
| `DB_HOST`                | O host do seu banco de dados.                                                      | `127.0.0.1`                                    |
| `DB_PORT`                | A porta do seu banco de dados.                                                     | `3306`                                         |
| `DB_DATABASE`            | O nome do banco de dados.                                                          | `track_vagas_zap`                              |
| `DB_USERNAME`            | O usuário de acesso ao banco.                                                      | `root`                                         |
| `DB_PASSWORD`            | A senha de acesso ao banco.                                                        | `password`                                     |
| `EVOLUTION_API_URL`      | A URL base da sua instância da Evolution API.                                      | `http://localhost:8080`                        |
| `EVOLUTION_API_TOKEN`    | O token de API para autenticar com a sua instância da Evolution API.               | `seu-token-secreto`                            |
| `EVOLUTION_INSTANCE_NAME`| O nome da instância do WhatsApp a ser utilizada na Evolution API.                  | `minha-instancia`                              |

---

## 🔗 Configuração do Webhook

Para que o bot receba as mensagens do WhatsApp, você precisa configurar o webhook na sua instância da Evolution API.

1.  **URL do Webhook:**
    ```
    https://seu-domino.com/api/webhook/evolution
    ```

2.  **Token Secreto:**
    - No campo de token (ou `apikey`) do webhook na Evolution API, insira o mesmo valor que você definiu em `EVOLUTION_API_TOKEN` no seu arquivo `.env`.

O bot irá validar este token em todas as requisições para garantir a segurança da comunicação.
