import discord
import discord.ext.commands as commands

import assets.restaurants

from utils.views.home import HomeView
from utils.api.users import get_user, user_exists, create_user, claim_daily_user

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
        '''
        Pong ?
        '''
        embed = discord.Embed(title="Pong", description="Bah ouai jai trouvé comment on fait siuuuuuu et c'est surement pas grâce à la doc qui pue sa mere",
                              colour=discord.Colour.dark_gold())
        
        await ctx.reply(embed=embed)

    @commands.command()
    async def start(self, ctx):
        '''
        To get started!
        '''
        discord_id = ctx.author.id
        # Check if discord User already has a User registered
        if user_exists(discord_id):
            await ctx.reply("You are already registered")
        # If not, create new User with API
        else:
            create_user(discord_id)
            await ctx.reply("Registration complete!")
    
    @commands.command()
    async def home(self, ctx):
        '''
        To see the dashboard
        '''
        discord_id = ctx.author.id
        user_response = get_user(discord_id)

        if user_response["error"] == "True":
            embed = discord.Embed(title="Error : Not registered", description="You are not registered! Please run the rstart command to start playing.",
                              colour=discord.Colour.brand_red())
            await ctx.reply(embed=embed)
        else:
            money = user_response["user"]["money"]
            embed = discord.Embed(
                title="Profile",
                colour=discord.Colour.dark_blue()
            )

            embed.set_thumbnail(url=assets.restaurants.SHOP)

            embed.add_field(name="Mention", value=ctx.author.mention)
            embed.add_field(name="Money", value=f"{money} 両")
            embed.add_field(name="Rebirths", value=f"{user_response['user']['rebirths']}")

            await ctx.reply(embed=embed, view=HomeView(ctx.author))

    @commands.command(aliases=["dc"])
    async def daily_claim(self, ctx):
        '''
        You can claim a reward every 12 hours, don't forget it!
        '''
        discord_id = ctx.author.id
        response = claim_daily_user(discord_id)

        if response["error"] == "True":
            title = "Bi-daily claim : Failed!"
            description = f"Claim is not ready yet. Time remaining : {response['time_remaining']}"
            colour = discord.Colour.brand_red()
            img_url = assets.restaurants.FAIL
        else:
            title = "Bi-daily claim : Success!"
            description = f"You claimed your daily reward ! +{response['money_given']}両"
            colour = discord.Colour.brand_green()
            img_url = assets.restaurants.RYO
        
        embed = discord.Embed(title=title, description=description, colour=colour)
        embed.set_image(url=img_url)

        await ctx.reply(embed=embed)