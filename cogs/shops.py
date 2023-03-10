import discord
import discord.ext.commands as commands

import assets.restaurants

import utils.api.restaurants
from utils.embed import send_embed
from utils.views.shops import ShopsView, SeeShopView

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
            embed.add_field(name=f"#```{restaurant['public_id']}``` ({restaurant['capacity']} || {restaurant['quality']})", value=text)
        
        embed.set_footer(text="Type rss [id] to get more details and access specific actions")

        await ctx.reply(embed=embed, view=ShopsView(ctx.author))
    
    @commands.command(aliases=["ss"])
    async def see_shop(self, ctx, public_id):
        '''
        Lists a particular shop
        '''
        discord_id = ctx.author.id
        response = utils.api.restaurants.get_restaurant(public_id, discord_id)
        if response["error"] == "True":
            title = "Error !"
            description = response["message"]
            colour = discord.Colour.brand_red()

            embed = discord.Embed(title=title, description=description, colour=colour)
            await ctx.reply(embed=embed)
            return
        
        restaurant = response["restaurant"]
        title = f"Restaurant #```{restaurant['public_id']}```"
        description = f"Capacity : {restaurant['capacity']} / 10\n"
        description += f"Quality : {restaurant['quality']} / 10\n"
        description += f"Workers : {restaurant['workers']}\n"
        description += f"Ramen stored : {restaurant['ramen_stored']} / {restaurant['max_storage']}\n"
        description += f"Money available : {restaurant['money_cached']}\n"
        colour = discord.Colour.blurple()

        embed = discord.Embed(title=title, description=description, colour=colour)
        embed.set_thumbnail(url=assets.restaurants.RAMEN)
        await ctx.reply(embed=embed, view=SeeShopView(restaurant['public_id']))
    
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