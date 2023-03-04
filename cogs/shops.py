import discord
import discord.ext.commands as commands

import assets.restaurants

import utils.api.restaurants
from utils.embed import send_embed
from utils.views.shops import ShopsView

class Shops(commands.Cog):
    def __init__(self, bot):
        self.bot = bot
    
    @commands.command(aliases=["s"])
    async def shops(self, ctx):
        '''
        Lists all the Restaurants owned
        '''
        discord_id = ctx.author.id
        utils.api.restaurants.update_user_restaurants(discord_id)
        response = utils.api.restaurants.get_restaurants(discord_id)
        
        if response["error"] == "True":
            title = "Error !"
            description = response["message"]
            colour = discord.Colour.brand_red()
        else:
            title = "Restaurants Overview"
            description = f"Here are all of {ctx.author.mention}'s restaurants"
            colour = discord.Colour.dark_blue()
        
        embed = discord.Embed(title=title, description=description, colour=colour)
        embed.set_thumbnail(url=assets.restaurants.RAMEN)
        
        restaurants = response["restaurants"]
        for restaurant in restaurants:
            text = f"Ramen Stored : {restaurant['ramen_stored']} bowl(s) /{restaurant['max_storage']}\n"
            text += f"Workers : {restaurant['workers']}"
            embed.add_field(name=f"#```{restaurant['public_id']}```", value=text)
        
        embed.set_footer(text="Type rss [id] to get more details and access specific actions")

        await ctx.reply(embed=embed, view=ShopsView())
    
    @commands.command(aliases=["bs"])
    async def buy_shop(self, ctx):
        '''
        Buys a shop if available
        '''
        discord_id = ctx.author.id
        title, description, colour = utils.api.restaurants.buy_restaurant(discord_id)
        embed = discord.Embed(title=title, description=description, colour=colour)
        
        await ctx.reply(embed=embed)
    
    @commands.command(aliases=["sc"])
    async def shops_claim(self, ctx):
        '''
        Claim shops if any
        '''
        discord_id = ctx.author.id
        title, description, colour, img_url = utils.api.restaurants.claim_shops(discord_id)
        embed = discord.Embed(title=title, description=description, colour=colour)
        embed.set_image(url=img_url)
        
        await ctx.reply(embed=embed)