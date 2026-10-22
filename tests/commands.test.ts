import assert from "node:assert/strict";
import { test } from "node:test";
import { Events, type ClientEvents } from "discord.js";
import { createBot } from "../src/discord/client.js";
import { handleMessage, type CommandMessage } from "../src/discord/commands.js";

function fixture(content: string): { message: CommandMessage; replies: unknown[] } {
  const replies: unknown[] = [];
  return {
    replies,
    message: {
      content, author: { bot: false }, webhookId: null,
      reply: async (options) => { replies.push(options); },
    },
  };
}

test("rping replies exactly pong and suppresses mentions", async () => {
  const { message, replies } = fixture("rping");
  assert.equal(await handleMessage(message), true);
  assert.deepEqual(replies, [{ content: "pong", allowedMentions: { parse: [], repliedUser: false } }]);
});

test("rhome, rhelp and all other text remain silent", async () => {
  for (const content of ["rhome", "rhelp", "rping extra", "rpingpong", "Rping", "/ping", "ping", "hello", ""]) {
    const { message, replies } = fixture(content);
    assert.equal(await handleMessage(message), false, content);
    assert.equal(replies.length, 0, content);
  }
});

test("surrounding whitespace does not change the rping command", async () => {
  const { message, replies } = fixture(" rping\n");
  assert.equal(await handleMessage(message), true);
  assert.equal(replies.length, 1);
});

test("bot and webhook messages are ignored", async () => {
  const { message, replies } = fixture("rping");
  assert.equal(await handleMessage({ ...message, author: { bot: true } }), false);
  assert.equal(await handleMessage({ ...message, webhookId: "test-webhook" }), false);
  assert.equal(replies.length, 0);
});

test("Discord message event routes through the tested command handler without logging in", async (t) => {
  const client = createBot();
  t.after(() => client.destroy());
  const { message, replies } = fixture("rping");
  client.emit(Events.MessageCreate, message as unknown as ClientEvents[Events.MessageCreate][0]);
  await new Promise<void>((resolve) => setImmediate(resolve));
  assert.equal(replies.length, 1);
  const ignored = fixture("rhome");
  client.emit(Events.MessageCreate, ignored.message as unknown as ClientEvents[Events.MessageCreate][0]);
  await new Promise<void>((resolve) => setImmediate(resolve));
  assert.equal(ignored.replies.length, 0);
});

test("reply failure is propagated rather than counted as a successful response", async () => {
  const { message } = fixture("rping");
  await assert.rejects(handleMessage({ ...message, reply: async () => { throw new Error("test failure"); } }), /test failure/);
});
