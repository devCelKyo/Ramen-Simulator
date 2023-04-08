import discord.ext.commands as commands

import utils.permissions

class Admin(commands.Cog):
    def __init__(self, bot):
        self.bot = bot
    
    @commands.command()
    @utils.permissions.admin()
    async def broadcast(self, ctx, message):
        for guild in self.bot.guilds:
            guild.system_channel.send(content=f"@everyone : System announcement\n {message}")