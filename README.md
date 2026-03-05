# GridZero - Cyber-Terminal RPG

GridZero é um RPG de estratégia baseado em texto (text-based) com foco em simulação de hacking e gerenciamento de sistemas. O projeto foi desenhado com foco em **baixa latência, alta performance e custo de infraestrutura otimizado**.

## 🛠️ Stack Tecnológica

* **Backend:** Laravel (PHP 8.3+)
* **Frontend:** React (SPA) + **TypeScript** + Tailwind CSS
* **Banco de Dados:** MySQL (via Docker)
* **Cache/Filas:** Redis (via Docker)
* **Interface:** CLI-Style Terminal (Monospace fonts)

## 🚀 Funcionalidades Principais

* **Motor de Ações Stateless:** A regeneração de energia e recursos é calculada via *Time-Delta*, eliminando a necessidade de processos de background constantes.
* **Sistema de Interação Polimórfico:** Utiliza a interface `Interactable` para que o motor de ações suporte interações com `Nodes`, `NPCs` e outros jogadores de forma genérica.
* **Processamento Assíncrono:** Uso intensivo de *Laravel Queues/Jobs* para garantir que ações complexas não bloqueiem a API.
* **Frontend Tipado:** Desenvolvimento robusto com **TypeScript** para garantir a integridade dos dados trafegados entre o terminal do usuário e a API.
* **Interface Imersiva:** Frontend minimalista focado na experiência "Hacker Terminal".

## 📋 Pré-requisitos

Para rodar este ambiente, você precisará de:
* [Docker](https://www.docker.com/) e [Docker Compose](https://docs.docker.com/)
* PHP 8.3+ (caso deseje rodar o artisan localmente)
* Composer
* Node.js (para o build do frontend)

## 🛠️ Como rodar o projeto

### 1. Clone o repositório:
```bash
git clone [https://github.com/wheelkorner/GridZero.git](https://github.com/wheelkorner/GridZero.git)
cd GridZero

### 2. Configure o ambiente:
```bash
cp .env.example .env
```

### 3. Suba os containers:
```bash
docker-compose up -d
```

### 4. Instale as dependências e rode as migrations:
```bash
docker exec -it <nome_do_container_app> composer install
docker exec -it <nome_do_container_app> php artisan migrate
```

### 5. Inicie o worker de fila (essencial para as ações):
```bash
docker exec -it <nome_do_container_app> php artisan queue:work

php artisan queue:work --verbose
```

## 🏗️ Arquitetura e Design Patterns

O projeto utiliza o **Service Pattern** (`ActionService`) para isolar as regras de negócio dos Controllers. Para a escalabilidade do sistema de ataques/interações, implementamos a interface `Interactable`, permitindo que novos tipos de alvos (como NPCs ou Chefões) sejam adicionados apenas implementando o contrato exigido, sem necessidade de alterações no núcleo do serviço de ações.

## 🤝 Contribuição

Contribuições são bem-vindas! Sinta-se à vontade para abrir Issues para sugestões ou Pull Requests com melhorias.



## Logs

```bash
/vendor/bin/phpstan analyse
``` 