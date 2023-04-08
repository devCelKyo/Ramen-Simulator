import discord
import os

from asyncio import run

# Cogs classes import
from cogs.bases import Bases
from cogs.shops import Shops
from cogs.admin import Admin


intents = discord.Intents.default()
intents.message_content = True

Bot = discord.ext.commands.Bot(command_prefix='r', intents=intents)
async def add_cogs():
    await Bot.add_cog(Bases(Bot))
    await Bot.add_cog(Shops(Bot))
    await Bot.add_cog(Admin(Bot))
    # add all others cogs

run(add_cogs())
Bot.run(os.environ['hashirama_bot_token'])