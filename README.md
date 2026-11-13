# Futsal API

Monorepo event-sourced com BFF fino e três serviços de domínio.

## Arquitetura

- `bff/`: autenticação Passport, rotas públicas e agregação de leitura (somente API).
- `team-service/`: times e jogadores (event store + projeção) + `team-worker` de outbox.
- `match-service/`: partidas e regras de jogo + `match-worker` de outbox e consumo de times.
- `standings-service/`: classificação como projeção somente leitura + `standings-worker` de consumo de partidas.
- `packages/contracts/`: envelope de eventos e assinatura interna.
- `packages/event-sourcing/`: agregados, event store, outbox e consumo SQS.

Cada serviço de domínio usa a mesma imagem para dois containers:

- **API**: expõe HTTP e roda migrations.
- **Worker**: não expõe porta; roda apenas comandos de outbox/consumo de fila.

Fluxo de escrita:

1. BFF valida o token e assina a chamada interna.
2. Serviço de domínio valida a assinatura, carrega o agregado, grava eventos e outbox na mesma transação.
3. Worker de outbox publica no SNS.
4. SNS entrega no SQS.
5. `standings-worker` consome `match.finished` e atualiza `team_standings`.

## Diagramas

### Comunicação entre serviços

```mermaid
flowchart LR
    subgraph Clientes
        C[Cliente]
    end

    subgraph Borda
        BFF[BFF\nPassport\n:8000]
    end

    subgraph Dominio[Serviços de domínio]
        T[team-service\nAPI :8001]
        M[match-service\nAPI :8002]
        S[standings-service\nAPI :8003]
    end

    subgraph Dados[(Bancos)]
        BDB[(bff_db)]
        TDB[(team_db)]
        MDB[(match_db)]
        SDB[(standings_db)]
    end

    C -->|HTTP + JWT| BFF
    BFF --> BDB
    BFF -->|HTTP interno assinado HMAC| T
    BFF -->|HTTP interno assinado HMAC| M
    BFF -->|HTTP interno assinado HMAC| S

    T --> TDB
    M --> MDB
    S --> SDB
```

### Publicação de eventos (Transactional Outbox → SNS)

```mermaid
flowchart LR
    subgraph Producer[Serviço produtor]
        API[API / Handler]
        ES[(domain_events)]
        OB[(outbox_events)]
        REL[Worker\noutbox:relay --daemon]
    end

    SNS[(SNS Topic)]

    API -->|mesma transação| ES
    API -->|mesma transação| OB
    REL -->|lê pendentes| OB
    REL -->|publish envelope| SNS
    REL -->|marca publicado| OB
```

### Consumo de eventos (SNS → SQS → Worker → Projeção)

```mermaid
flowchart LR
    SNS[(SNS Topic)] -->|assinatura + filtro| SQS[(SQS Queue)]
    SQS -->|receive / long-poll| CW[Worker consumidor]
    CW -->|idempotência\nprocessed_events| PDB[(Read Model / DB)]
    CW -->|aplica projeção| PDB
    CW -->|delete message| SQS
```

### Cadeia: Team → Match (sincronização de times/jogadores)

```mermaid
flowchart LR
    subgraph Team[team-service]
        TAPI[API] -->|team/player events| TDB[(team_db\nevent_store + outbox)]
        TW[team-worker\noutbox:relay] --> TDB
    end

    TW -->|publish| TT[(SNS\nfutsal-team-events)]
    TT -->|team.created/updated/deactivated\nplayer.registered/transferred/removed| TQ[(SQS\nmatch-team-events)]

    subgraph Match[match-service]
        MW[match-worker\nevents:consume-team] -->|SyncKnownTeams| MDB[(match_db\nknown_teams\nknown_players)]
        MAPI[API] --> MDB
    end

    TQ --> MW
```

### Cadeia: Match → Standings (classificação)

```mermaid
flowchart LR
    subgraph Match[match-service]
        MAPI[API] -->|match events| MDB[(match_db\nevent_store + outbox)]
        MW[match-worker\noutbox:relay] --> MDB
    end

    MW -->|publish| MT[(SNS\nfutsal-match-events.fifo)]
    MT -->|match.finished| SQ[(SQS\nstandings-match-events.fifo)]

    subgraph Standings[standings-service]
        SW[standings-worker\nevents:consume-match] -->|SyncMatchResults\nStandingsProjector| SDB[(standings_db\nteam_standings\nmatch_results)]
        SAPI[API /standings] --> SDB
    end

    SQ --> SW
```

## Requisitos

- Docker e Docker Compose
- PHP 8.5 e Composer (para rodar testes fora do container)

## Subir a stack

```bash
docker compose up -d --build
```

Serviços:

- BFF: `http://localhost:8000`
- team-service: `http://localhost:8001`
- match-service: `http://localhost:8002`
- standings-service: `http://localhost:8003`
- LocalStack: `http://localhost:4566`

Workers `team-worker`, `match-worker` e `standings-worker` não expõem portas.

## Bancos

`bff_db`, `team_db`, `match_db`, `standings_db`, `futsal_test_db` no PostgreSQL local.

## Endpoints públicos

- `POST /api/login`, `POST /api/logout`, `POST /api/user`
- `GET/POST /api/teams`, `GET/PUT/DELETE /api/teams/{id}`
- `POST /api/teams/{teamId}/players`, `PUT/DELETE /api/players/{id}`
- `GET/POST /api/matches`, `GET /api/matches/{id}`
- `POST /api/matches/{id}/start`, `/goals`, `/finish`, `/cancel`
- `GET /api/standings`
- Aliases somente leitura: `/api/team`, `/api/teamMatch`, `/api/teamClassification`

## Testes

```bash
docker compose exec bff php artisan test
docker compose exec team-service php artisan test
docker compose exec match-service php artisan test
docker compose exec standings-service php artisan test
```

E2E:

```bash
./tests/e2e/full-flow.sh
```

## Operação

- Containers de worker (sem porta HTTP):
  - `team-worker`: `php artisan outbox:relay --daemon`.
  - `match-worker`: `outbox:relay --daemon` + `events:consume-team`.
  - `standings-worker`: `events:consume-match`.
- Reprojeção: `php artisan standings:rebuild`.
