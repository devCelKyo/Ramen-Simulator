import discord
import os

from asyncio import run
from cogs.bases import Bases
from utils.db_connect import connect

intents = discord.Intents.default()
intents.message_content = True

Bot = discord.ext.commands.Bot(command_prefix='!', intents=intents)
async def add_cogs():
    await Bot.add_cog(Bases(Bot))
    # add all others cogs

run(connect())
run(add_cogs())
Bot.run(os.environ['hashirama_bot_token'])