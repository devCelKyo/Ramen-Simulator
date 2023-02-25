import discord
import os


intents = discord.Intents.default()
intents.message_content = True

client = discord.Client(intents)
client.run(os.environ['hashirama_bot_token'])