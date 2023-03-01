import discord
import discord.ext.commands as commands

import assets.restaurants

from utils.embed import send_embed
from utils.restaurants import get_restaurants, buy_restaurant, update_user_restaurants, claim_shops

class Shops(commands.Cog):
    def __init__(self, bot):
        self.bot = bot
    
    @commands.command(aliases=["s"])
    async def shops(self, ctx):
        '''
        Lists all the Restaurants owned
        '''
        discord_id = ctx.author.id
        update_user_restaurants(discord_id)
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
        embed.set_thumbnail(url=assets.restaurants.RAMEN)
        
        restaurants = response["restaurants"]
        for restaurant in restaurants:
            text = f"Capacity : level {restaurant['capacity']}/10\nQuality : level {restaurant['quality']}/10"
            text += f"\nRamen Stored : {restaurant['ramen_stored']} bowl(s) /{restaurant['max_storage']}"
            embed.add_field(name=f"#```{restaurant['public_id']}```", value=text)
        
        embed.set_footer(text="Type rss [id] to get more details and access specific actions")
        await ctx.reply(embed=embed)
    
    @commands.command(aliases=["bs"])
    async def buy_shop(self, ctx):
        '''
        Buys a shop if available
        '''
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
    
    @commands.command(aliases=["sc"])
    async def shops_claim(self, ctx):
        '''
        Claim shops if any
        '''
        discord_id = ctx.author.id
        response = claim_shops(discord_id)

        if response["error"] == "True":
            title = "Error !"
            description = response["message"]
            colour = discord.Colour.brand_red()
        else:
            title = "Revenue Redeemed!"
            description = f"You redeemed what your restaurants earned and got {response['given_money']}両!"
            colour = discord.Colour.brand_green()
        
        await send_embed(title, description, ctx, colour)