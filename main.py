import discord
import os

from cogs.bases import Bases

intents = discord.Intents.default()
intents.message_content = True

Bot = discord.ext.commands.Bot(command_prefix='!', intents=intents)
Bot.add_cog(Bases(Bot))

Bot.run(os.environ['hashirama_bot_token'])