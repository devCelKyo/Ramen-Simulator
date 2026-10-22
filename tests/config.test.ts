import assert from "node:assert/strict";
import { mkdtempSync, rmSync, writeFileSync } from "node:fs";
import { tmpdir } from "node:os";
import { join } from "node:path";
import { test } from "node:test";
import { readDiscordToken } from "../src/discord/config.js";

test("missing or empty runtime credentials fail without exposing values", () => {
  assert.throws(() => readDiscordToken({}), /Provide DISCORD_TOKEN_FILE or DISCORD_TOKEN/);
  assert.throws(() => readDiscordToken({ DISCORD_TOKEN: " " }), /Provide DISCORD_TOKEN_FILE or DISCORD_TOKEN/);
});

test("runtime environment accepts a token without writing it to disk", () => {
  assert.equal(readDiscordToken({ DISCORD_TOKEN: " fixture-not-a-discord-token " }), "fixture-not-a-discord-token");
});

test("mounted secret file takes precedence over the environment", (t) => {
  const directory = mkdtempSync(join(tmpdir(), "ramen-config-"));
  t.after(() => rmSync(directory, { recursive: true, force: true }));
  const path = join(directory, "fixture");
  writeFileSync(path, "fixture-not-a-discord-token\n", { mode: 0o600 });
  assert.equal(readDiscordToken({ DISCORD_TOKEN_FILE: path, DISCORD_TOKEN: "unused-fixture" }), "fixture-not-a-discord-token");
  writeFileSync(path, " ");
  assert.throws(() => readDiscordToken({ DISCORD_TOKEN_FILE: path, DISCORD_TOKEN: "unused-fixture" }));
});
