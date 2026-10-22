# Ramen Simulator

Minimal TypeScript port of the Rust game prototype and Python Discord bot. Node.js 24.17+ (24.x) and pnpm 11.25.0 are required.

## Development

```sh
pnpm install --frozen-lockfile
pnpm check
pnpm build
```

`pnpm check` runs strict type checks and all tests. Tests use Node's test runner, a shared ingredient/restaurant catalog and a controlled clock; no Discord credentials or network access are needed. See [the design](docs/TS_REWRITE_DESIGN.md) for scope and acceptance requirements.

- `src/game/`: recipes, inventory, menus, queues and the elapsed-time simulation.
- `src/storage/`: SQLite restaurant name/balance persistence, matching the existing prototype's limited adapter. Menus, stock and simulation state remain in memory.
- `src/discord/`: Discord.js interface. **Only `rping` is enabled; it replies `pong`.** Other messages, bots and webhooks are ignored.
- `tests/catalog/`: shared fixtures ported from the Rust integration tests.

Money is always integer cents, including JSON and SQLite values. The catalog's $1,000 starting balance is `100_000` cents and its $10 ramen price is `1_000` cents. A $1 trillion balance fits within JavaScript's safe integer range.

## Run the bot

Provide an existing Discord bot token at runtime using `DISCORD_TOKEN_FILE` (preferred) or `DISCORD_TOKEN`. The Discord application needs Message Content Intent enabled and channel permissions to view messages, read message history and send replies.

After building, `pnpm start` runs the bot. A successful connection logs `Discord ready as ...; command: rping.` A successful reply logs `Handled rping: pong sent.`

For Docker Compose, point `DISCORD_TOKEN_PATH` to the existing private token file **outside the checkout**, then run:

```sh
docker compose up -d --build bot
docker compose logs --tail=20 bot
```

Compose mounts that file as a Docker secret. It does not put its contents into the image or Compose configuration. Git and Docker ignore local secret files, database files and SQL backups. Never commit tokens or `.env` files. Build and CI run without credentials.

First-pass smoke check: from a human Discord account send `rping` and expect `pong`; `rhome` and `rhelp` should receive no response. Logging in alone does not establish command-response behavior. The TypeScript branch is deployed for review separately from `main`.
