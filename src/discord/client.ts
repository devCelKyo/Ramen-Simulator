import { Client, Events, GatewayIntentBits, Partials } from "discord.js";
import { handleMessage } from "./commands.js";

export function createBot(): Client {
  const client = new Client({
    intents: [
      GatewayIntentBits.Guilds,
      GatewayIntentBits.GuildMessages,
      GatewayIntentBits.DirectMessages,
      GatewayIntentBits.MessageContent,
    ],
    partials: [Partials.Channel],
    allowedMentions: { parse: [], repliedUser: false },
  });
  client.once(Events.ClientReady, (ready) => {
    console.info(`Discord ready as ${ready.user.tag}; command: rping.`);
  });
  client.on(Events.MessageCreate, async (message) => {
    try {
      if (await handleMessage(message)) {
        console.info("Handled rping: pong sent.", { messageId: message.id, channelId: message.channelId });
      }
    } catch {
      // Do not log raw REST errors or message payloads: they can contain sensitive data.
      console.error("Could not reply to command.");
    }
  });
  client.on(Events.Error, () => console.error("Discord client error."));
  return client;
}
