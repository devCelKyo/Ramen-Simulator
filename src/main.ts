import { createBot } from "./discord/client.js";
import { readDiscordToken } from "./discord/config.js";

async function main(): Promise<void> {
  // Read configuration before creating the client so missing credentials fail promptly.
  const token = readDiscordToken();
  const client = createBot();
  const shutdown = (): void => {
    void client.destroy().then(() => { process.exitCode = 0; });
  };
  process.once("SIGTERM", shutdown);
  process.once("SIGINT", shutdown);
  await client.login(token);
}

main().catch(() => {
  console.error("Bot startup failed; check runtime configuration, credentials and Discord intents.");
  process.exitCode = 1;
});
