import discord.ext.commands as commands

class Bases(commands.Cog):
    def __init__(self, bot):
        self.bot = bot
    
    @commands.Cog.listener()
    async def on_member_join(self, member):
        channel = member.guild.system_channel
        try:
            await channel.send(f'Welcome, {member.mention}')
        except:
            pass
    
    @commands.command()
    async def ping(self, ctx):
        await ctx.send("Pong")
    
    @commands.command()
    async def help_game(self, ctx):
        await ctx.send("Run the !start command to start playing!")

    @commands.command()
    async def start(self, ctx):
        # Check if discord User already has a User registered
        pass
        # If not, create new User with API
