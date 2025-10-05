import discord
from discord.ext.commands import Bot

import os

intents = discord.Intents.default()
intents.message_content = True

bot = Bot(command_prefix='r', intents=intents, allowed_mentions=discord.AllowedMentions(everyone = True))

#bot.run(os.getenv('hashirama_bot_token'))
bot.run(os.getenv('tsunade_bot_token'))
