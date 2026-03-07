# Regras de Negócio - GridZero

O GridZero é um RPG de hacking onde a eficiência e o gerenciamento de recursos são fundamentais. Abaixo estão detalhadas as regras de negócio que regem o comportamento do simulador.

## 1. Sistema de Energia e Regeneração

O jogo utiliza um motor de ações *stateless* onde a energia é calculada sob demanda (*Time-Delta*).

*   **Capacidade Máxima:** 100 pontos de energia.
*   **Custo de Ação:** Cada ação (hack, scan, etc.) custa **10 pontos**.
*   **Taxa de Regeneração:** 1 ponto a cada **5 minutos**.
*   **Cálculo:** A regeneração ocorre sempre que o perfil do usuário é carregado ou uma ação é iniciada, comparando o timestamp atual com o `last_energy_update`.

## 2. Motor de Ações (Hack/Interação)

As ações são executadas contra alvos que implementam a interface `Interactable` (Nós, NPCs ou Jogadores).

### 2.1. Duração da Ação
O tempo para conclusão de uma ação é dinâmico:
`Tempo (segundos) = Dificuldade do Alvo * 120`

### 2.2. Probabilidade de Sucesso
O sucesso não é garantido e depende do nível do jogador e da dificuldade do alvo:
`Chance = 85% - (Dificuldade * 5%) + (Nível do Usuário * 2%)`
*   **Intervalo Seguro:** A chance de sucesso é limitada entre **10% (mínimo)** e **95% (máximo)**.

### 2.3. NPCs (Elite Hackers)
- **Status Online:** NPCs estão sempre online e visíveis no comando `scan -net`.
- **Vulnerabilidade Variável:** NPCs nem sempre estão hackáveis. Eles alternam entre estados:
  - **Ativo (Hacking):** Portas abertas por um longo período (vulnerável).
  - **Escaneando:** Portas abertas por um curto período.
  - **Ocioso:** Host online, mas portas fechadas.
- **Progressão:** NPCs podem subir de nível dinamicamente durante a simulação de atividades.
- **Recompensas de Elite:** NPCs de alto nível (Elite) possuem um arquivo oculto em `/root/.sys/prize_hash.db`. O hash contido neste arquivo pode ser validado pela administração para conceder itens exclusivos ao inventário do jogador que realizou a intrusão.

### 2.4. Vizinhança e Vulnerabilidade (Counter-Hacking)
Ao iniciar qualquer ação ofensiva, o jogador expõe seu sistema:
*   **Janela de Vulnerabilidade:** O atacante fica vulnerável por **60 segundos** após o início de uma ação. Durante este tempo, ele pode ser alvo de contra-ataques ou scans de outros sistemas.

## 3. Recompensas e Progressão

### 3.1. Recompensas de Sucesso
Ao completar uma ação com sucesso, o jogador recebe:
*   **XP:** 100 pontos * Multiplicador do Alvo.
*   **Créditos (GZC):** 50 créditos * Multiplicador do Alvo.

### 3.2. Penalidade de Falha
Falhar em um hack resulta em danos físicos ao hardware:
*   **Integridade do SSD:** Redução de **10%** na saúde do SSD.

### 3.3. Evolução de Nível (Level Up)
O nível é calculado com base no XP total:
`Nível = floor(XP / 1000) + 1`
Ao subir de nível, o hardware é escalonado automaticamente:
*   **CPU:** +200MHz por nível.
*   **RAM:** +256MB por nível.

## 4. Mercado Negro (Shop) e Programas

Os jogadores podem adquirir ferramentas para facilitar suas operações.

| Programa | Preço | Efeito Principal |
| :--- | :--- | :--- |
| **Impersonator v2** | 500 CR | Drena entre 10% e 30% dos créditos de uma vítima. |
| **KeyLogger.so** | 300 CR | Próximos 3 hacks concedem +50% de XP. |
| **Stealth.bin** | 400 CR | Reduz a janela de vulnerabilidade ao atacar. |
| **CrackForce.py** | 600 CR | Próximo hack não consome energia. |
| **FireWall-X** | 700 CR | Bloqueia o próximo ataque de NPC. |
| **AntiVirus Pro** | 350 CR | Remove infecções e fecha janela de vulnerabilidade. |
