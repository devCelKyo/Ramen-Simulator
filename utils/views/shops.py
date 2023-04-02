import discord

import utils.api.restaurants

class ShopsView(discord.ui.View):
    def __init__(self, bot, ctx):
        super().__init__()
        self.author = ctx.author
        self.ctx = ctx
        self.bot = bot
    
    @discord.ui.button(label="Claim all", style=discord.ButtonStyle.success)
    async def claim_all_callback(self, interaction, button):
        '''
        Claim shops if any
        '''
        button.disabled = True
        button.label = "Claimed!"
        await interaction.response.edit_message(view=self)
        await self.ctx.invoke(self.bot.get_command('shops_claim'))

    @discord.ui.button(label="Refill all", style=discord.ButtonStyle.primary)
    async def refill_all_callback(self, interaction, button):
        button.disabled = True
        button.label = "Refilled!"
        await interaction.response.edit_message(view=self)
        await self.ctx.invoke(self.bot.get_command('refill_shops'))
    
    async def interaction_check(self, interaction: discord.Interaction):
        return interaction.user.id == self.author.id

class SeeShopView(discord.ui.View):
    def __init__(self, restaurant_public_id, bot, ctx):
        super().__init__()
        self.bot = bot
        self.ctx = ctx
        self.author = ctx.author
        self.public_id = restaurant_public_id

    @discord.ui.button(label="Upgrade Capacity", style=discord.ButtonStyle.primary)
    async def uc_callback(self, interaction, button):
        button.disabled = True
        button.label = "---"
        await interaction.response.edit_message(view=self)

        title, description, colour = utils.api.restaurants.upgrade(self.public_id, "capacity")
        embed = discord.Embed(title=title, description=description, colour=colour)

        await interaction.followup.send(embed=embed, view=SeeShopAgainView(self.public_id, self.bot, self.ctx))
    
    @discord.ui.button(label=f"Upgrade Quality", style=discord.ButtonStyle.primary)
    async def uq_callback(self, interaction, button):
        button.disabled = True
        button.label = "---"
        await interaction.response.edit_message(view=self)
    
        title, description, colour = utils.api.restaurants.upgrade(self.public_id, "quality")
        embed = discord.Embed(title=title, description=description, colour=colour)

        await interaction.followup.send(embed=embed, view=SeeShopAgainView(self.public_id, self.bot, self.ctx))
    
    async def interaction_check(self, interaction: discord.Interaction):
        return interaction.user.id == self.author.id

class SeeShopAgainView(discord.ui.View):
    def __init__(self, restaurant_public_id, bot, ctx):
        super().__init__()
        self.bot = bot
        self.ctx = ctx
        self.author = ctx.author
        self.public_id = restaurant_public_id
    
    @discord.ui.button(label="See restaurant again", style=discord.ButtonStyle.success)
    async def see_again_callback(self, interaction, button):
        button.disabled = True
        button.label = "---"
        await interaction.response.edit_message(view=self)
    
        await self.ctx.invoke(self.bot.get_command('see_shop'), self.public_id)
    
    async def interaction_check(self, interaction: discord.Interaction):
        return interaction.user.id == self.author.id


