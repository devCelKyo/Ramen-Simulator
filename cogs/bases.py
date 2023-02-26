import discord.ext.commands as commands

from models.user import User

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
    async def help(self, ctx):
        await ctx.send("Run the !start command to start playing!")

    @commands.command()
    async def start(self, ctx):
        # Check if discord User already has a User registered
        if User.exists(discord_id=ctx.author.id):
            await ctx.send("You are already registered.")
        else:
            await User.create(discord_id=ctx.author.id, money=50)
            await ctx.send("Registration complete!")
