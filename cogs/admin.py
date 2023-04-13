import discord.ext.commands as commands

import utils.permissions

class Admin(commands.Cog):
    def __init__(self, bot):
        self.bot = bot
    
    @commands.command()
    @utils.permissions.admin()
    async def broadcast(self, ctx, message, everyone):
        text = ""
        if everyone == "1":
            text += "@everyone : "
        text += f"System announcement\n {message}"

        for guild in self.bot.guilds:
            await guild.system_channel.send(content=text)
    
    @commands.command()
    @utils.permissions.admin()
    async def file_broadcast(self, ctx, everyone):
        text = ""
        if everyone == "1":
            text += "@everyone : "
        file = open("broadcast.txt", "r")
        message = file.read()
        file.close()
        
        text += f"System announcement\n {message}"

        for guild in self.bot.guilds:
            await guild.system_channel.send(content=text)