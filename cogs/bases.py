import discord
import discord.ext.commands as commands

from utils.users import user_exists, create_user

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
        embed = discord.Embed(
            colour=discord.Colour.dark_gold,
            description="Bah ouai jai trouvé comment on fait siuuuuuu et c'est surement pas grâce à la doc qui pue sa mere",
            title="Pong"
        )

        await ctx.send(embed=embed)
    
    @commands.command()
    async def help_game(self, ctx):
        await ctx.send("Run the !start command to start playing!")

    @commands.command()
    async def start(self, ctx):
        discord_id = ctx.author.id
        # Check if discord User already has a User registered
        if user_exists(discord_id):
            await ctx.send("You are already registered")
        # If not, create new User with API
        else:
            create_user(discord_id)
            await ctx.send("Registration complete!")