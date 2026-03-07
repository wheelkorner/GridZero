# Documentação Técnica - GridZero

Esta documentação descreve a arquitetura técnica, padrões de design e componentes do sistema GridZero.

## 1. Arquitetura Geral

O sistema segue o padrão **Service-Layer Pattern** sobre a estrutura padrão do Laravel 11.

*   **Controllers:** Gerenciam as requisições HTTP e validação básica.
*   **Services:** Contêm a lógica de negócio pesada (ex: `ActionService`).
*   **Jobs:** Processamento assíncrono para finalização de ações (ex: `CompleteActionJob`).
*   **Interfaces:** Contratos para garantir polimorfismo (ex: `Interactable`).

## 2. Padrões de Design de Destaque

### 2.1. Polimorfismo de Alvos (`Interactable`)
O sistema de ações não sabe se o alvo é um Servidor (Node), um NPC ou outro Humano. Ele interage com qualquer classe que implemente `App\Interfaces\Interactable`.
*   **Método:** `getDifficulty(): int` - Define o tempo e a chance de sucesso da interação.

### 2.2. Motor de Energia Stateless
Para evitar sobrecarga de processos em background (`cronjobs` ou `daemons` pesados), a energia dos jogadores é regenerada matematicamente no momento do acesso.
*   **Lógica:** O dado é persistido como `energy_points` e `last_energy_update`. O `ActionService@calculateEnergy` aplica a diferença de tempo (*Time-Delta*) para atualizar os pontos.

## 3. Principais Endpoints da API

| Método | Endpoint | Descrição |
| :--- | :--- | :--- |
| `POST` | `/api/login` | Autenticação de terminal. |
| `GET` | `/api/user` | Status atual do perfil (Energia, Hardware, XP). |
| `GET` | `/api/nodes` | Lista nós disponíveis para hacking. |
| `POST` | `/api/actions` | Inicia uma ação de hacking (Requer `interactable_id`). |
| `GET` | `/api/shop` | Lista programas disponíveis no Mercado Negro. |
| `POST` | `/api/shop/buy` | Adquire um programa para o inventário. |

## 4. Estrutura de Dados (MySQL)

### Tabela `users`
*   `username`, `password`: Credenciais.
*   `level`: Nível atual (1 a N).
*   `cpu`, `ram`, `ssd`: Atributos de performance e saúde.
*   `energy_points`: Recurso limitado para ações.
*   `stats`: JSON contendo XP, Créditos (GZC) e Inventário.
*   `vulnerable_until`: Timestamp que define se o IP está exposto.

### Tabela `actions`
*   `user_id`: Dono da ação.
*   `interactable_type`, `interactable_id`: Referência polimórfica ao alvo.
*   `status`: `pending`, `completed`, `failed`.
*   `ends_at`: Data e hora prevista para o término da tarefa.

## 5. Processamento Assíncrono

As ações utilizam **Laravel Queues** com driver Redis.
1.  O `ActionService` despacha um `CompleteActionJob`.
2.  O Job é agendado com um *delay* igual ao tempo de execução da tarefa.
3.  O Worker processa o resultado (sucesso ou falha) e atualiza o estado do banco.
