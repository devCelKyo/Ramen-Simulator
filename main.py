import discord
import os

client = discord.Client()
client.run(os.environ['hashirama_bot_token'])