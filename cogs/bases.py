import discord
import discord.ext.commands as commands

from utils.users import get_user, user_exists, create_user, claim_daily_user
from utils.embed import send_embed
from utils.restaurants import get_restaurants, buy_restaurant

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
        await send_embed("Pong", "Bah ouai jai trouvé comment on fait siuuuuuu et c'est surement pas grâce à la doc qui pue sa mere", ctx)
    
    @commands.command()
    async def help_game(self, ctx):
        await ctx.reply("Run the rstart command to start playing!")

    @commands.command()
    async def start(self, ctx):
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
        discord_id = ctx.author.id
        user_response = get_user(discord_id)

        if user_response["error"] == "True":
            await send_embed("Error : Not registered",
                             "You are not registered! Please run the rstart command to start playing.",
                             ctx,
                             discord.Colour.brand_red())
        else:
            money = user_response["user"]["money"]
            embed = discord.Embed(
                title="Profile",
                colour=discord.Colour.dark_blue()
            )

            embed.add_field(name="Mention", value=ctx.author.mention)
            embed.add_field(name="Money", value=f"{money} 両")

            await ctx.reply(embed=embed)

    @commands.command(aliases=["dc"])
    async def daily_claim(self, ctx):
        discord_id = ctx.author.id
        response = claim_daily_user(discord_id)

        if response["error"] == "True":
            title = "Bi-daily claim : Failed!"
            description = f"Claim is not ready yet. Time remaining : {response['time_remaining']}"
            colour = discord.Colour.brand_red()
        else:
            title = "Bi-daily claim : Success!"
            description = f"You claimed your daily reward ! +{response['money_given']}両"
            colour = discord.Colour.brand_green()
        
        await send_embed(title, description, ctx, colour)
    
    @commands.command(aliases=["s"])
    async def shops(self, ctx):
        discord_id = ctx.author.id
        response = get_restaurants(discord_id)

        if response["error"] == "True":
            title = "Error !"
            description = response["message"]
            colour = discord.Colour.brand_red()
        else:
            title = "Restaurants"
            description = f"Here are {ctx.author.mention}'s restaurants"
            colour = discord.Colour.dark_blue()
        
        embed = discord.Embed(title=title, description=description, colour=colour)
        
        restaurants = response["restaurants"]
        for restaurant in restaurants:
            embed.add_field(name="restaurant", value=f"Capacity : {restaurant['capacity']}")
        
        await ctx.reply(embed=embed)
    
    @commands.command(aliases=["bs"])
    async def buy_shop(self, ctx):
        discord_id = ctx.author.id
        response = buy_restaurant(discord_id)

        if response["error"] == "True":
            title = "Error !"
            description = response["message"]
            colour = discord.Colour.brand_red()
        else:
            title = "Restaurant Purchase"
            description = f"You succesfully purchased a restaurant!"
            colour = discord.Colour.brand_green()
        
        await send_embed(title, description, ctx, colour)