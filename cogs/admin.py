import discord.ext.commands as commands

import utils.permissions

class Admin(commands.Cog):
    def __init__(self, bot):
        self.bot = bot
    
    @utils.permissions.admin
    @commands.command()
    async def broadcast(self, ctx, message):
        await ctx.send(message)