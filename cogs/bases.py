import discord.ext.commands as commands
import requests

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
        discord_id = ctx.author.id
        # Check if discord User already has a User registered
        response = requests.get(f"http://localhost:8000/get_user/{discord_id}").json()
        if response.error == "True":
            await ctx.send("You are already registered")
        # If not, create new User with API
        else:
            params = {"discord_id" : discord_id}
            requests.post("http://localhost:8000/create_user", params=params)
            await ctx.send("Registration complete!")