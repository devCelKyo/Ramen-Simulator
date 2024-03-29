import discord
import discord.ext.commands as commands

import assets.restaurants
import utils.api.users

from utils.views.home import HomeView

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
        if utils.api.users.user_exists(discord_id):
            await ctx.reply("You are already registered")
        # If not, create new User with API
        else:
            utils.api.users.create_user(discord_id)
            await ctx.reply("Registration complete!")
    
    @commands.command()
    async def home(self, ctx):
        '''
        To see the dashboard
        '''
        discord_id = ctx.author.id
        user_response = utils.api.users.get_user(discord_id)

        if user_response["error"] == "True":
            embed = discord.Embed(title="Error : Not registered", description="You are not registered! Please run the rstart command to start playing.",
                              colour=discord.Colour.brand_red())
            await ctx.reply(embed=embed)
        else:
            user = user_response['user']
            embed = discord.Embed(
                colour=discord.Colour.dark_blue(),
                description=f"{ctx.author.mention}, here's your profile:"
            )

            embed.set_thumbnail(url=assets.restaurants.SHOP)

            embed.add_field(name="Money", value=f"{user['money']} 両", inline=False)
            embed.add_field(name="Restaurants", value=f"{user['nb_restaurants']}")
            embed.add_field(name="Restaurant slots", value=f"{user['restaurant_slots']}")
            embed.add_field(name="Current slot price", value=f"{user['slot_price']}")

            await ctx.reply(embed=embed, view=HomeView(self.bot, ctx))

    @commands.command(aliases=["dc", "sa"])
    async def daily_claim(self, ctx):
        '''
        You can claim a reward every 12 hours, don't forget it!
        '''
        discord_id = ctx.author.id
        response = utils.api.users.claim_daily_user(discord_id)

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
    
    @commands.command(aliases=["lb"])
    async def leaderboard(self, ctx):
        users = utils.api.users.leaderboard()
        title = "Here are the 10 wealthiest players:"
        embed = discord.Embed(title=title, colour=discord.Colour.blue())
        index = 1
        for user in users:
            user_object = await self.bot.fetch_user(user['discord_id'])
            text = f"{user_object.mention} : {user['money']}両"
            embed.add_field(name=f"#{index}", value=text, inline=False)
            index += 1
        embed.set_thumbnail(url=assets.restaurants.HOKAGE)
        embed.set_footer(text='Can you make it in?')
        
        await ctx.reply(embed=embed)
