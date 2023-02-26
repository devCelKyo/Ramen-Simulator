import discord
import os


intents = discord.Intents.default()
intents.message_content = True

Bot = discord.ext.commands.Bot(command_prefix='!', intents=intents)
Bot.run(os.environ['hashirama_bot_token'])