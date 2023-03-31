import discord
import discord.ext.commands as commands

import assets.restaurants
import utils.api.restaurants

from utils.views.shops import ShopsView, SeeShopView
from utils.views.home import HomeView

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
        if len(restaurants) != 0:
            for restaurant in restaurants:
                text = f"Ramen Stored : **{restaurant['ramen_stored']}** :ramen: /**{restaurant['max_storage']}**\n"
                text += f"Workers : **{restaurant['workers']}** :cook:"
                embed.add_field(name=f"#`{restaurant['public_id']}` ({restaurant['capacity']} || {restaurant['quality']}) " + "★"*restaurant['stars'], value=text)
            
            embed.set_footer(text="Type rss [id] to get more details and access specific actions")
            view = ShopsView(self.bot, ctx)
        else:
            embed.description = "Looks like you don't have any restaurant... The first one is free, buy one!"
            view = HomeView(self.bot, ctx)

        await ctx.reply(embed=embed, view=view)
    
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
        title = f"Restaurant #`{restaurant['public_id']}`"
        colour = discord.Colour.blurple()
        embed = discord.Embed(title=title, colour=colour)

        if restaurant['stars'] == 0:
            stars = "None"
        else:
            stars = "★"*restaurant['stars']
        text = f"Stars : {stars}\n"
        text += f"Capacity : {restaurant['capacity']} / 10\n"
        text += f"Quality : {restaurant['quality']} / 10\n"
        text += f"Workers : {restaurant['workers']}\n"
        embed.add_field(name=":star2: Levels", value=text, inline=False)

        text = f"Ramen stored : {restaurant['ramen_stored']} / {restaurant['max_storage']}\n"
        text += f"Money available : {restaurant['money_cached']}\n"
        embed.add_field(name=":office: State", value=text, inline=False)
        
        upgrade_costs = f"Capacity : {restaurant['capacity_upgrade_price']}両\n"
        upgrade_costs += f"Quality : {restaurant['quality_upgrade_price']}両"
        embed.add_field(name=":moneybag: Upgrade costs", value=upgrade_costs, inline=False)
        embed.set_thumbnail(url=assets.restaurants.RAMEN)

        await ctx.reply(embed=embed, view=SeeShopView(restaurant['public_id'], ctx.author))
    
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
    
    @commands.command(aliases=["fs"])
    async def refill_shops(self, ctx):
        '''
        Refill shops if any
        '''
        discord_id = ctx.author.id
        title, description, colour, img_url = utils.api.restaurants.refill_all(discord_id)
        embed = discord.Embed(title=title, description=description, colour=colour)
        embed.set_image(url=img_url)

        await ctx.reply(embed=embed)
    
    @commands.command(aliases=["aw"])
    async def add_workers(self, ctx, restaurant_public_id, workers_amount):
        discord_id = ctx.author.id
        title, description, colour, img_url = utils.api.restaurants.add_workers(discord_id, restaurant_public_id, workers_amount)
        embed = discord.Embed(title=title, description=description, colour=colour)
        embed.set_image(url=img_url)

        await ctx.reply(embed=embed)