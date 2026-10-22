export interface CommandMessage {
  readonly content: string;
  readonly author: { readonly bot: boolean };
  readonly webhookId: string | null;
  reply(options: {
    content: string;
    allowedMentions: { parse: []; repliedUser: false };
  }): Promise<unknown>;
}

/** The first-pass bot deliberately handles only the exact rping command. */
export async function handleMessage(message: CommandMessage): Promise<boolean> {
  if (message.author.bot || message.webhookId !== null || message.content.trim() !== "rping") {
    return false;
  }
  await message.reply({ content: "pong", allowedMentions: { parse: [], repliedUser: false } });
  return true;
}
