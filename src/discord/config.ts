import { readFileSync } from "node:fs";

/** Tokens are supplied only at runtime, preferably via a mounted Docker secret. */
export function readDiscordToken(env: NodeJS.ProcessEnv = process.env): string {
  const token = env.DISCORD_TOKEN_FILE
    ? readFileSync(env.DISCORD_TOKEN_FILE, "utf8").trim()
    : env.DISCORD_TOKEN?.trim();
  if (!token) throw new Error("Provide DISCORD_TOKEN_FILE or DISCORD_TOKEN at runtime.");
  return token;
}
