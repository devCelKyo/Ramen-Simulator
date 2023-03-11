import discord
import discord.ext.commands as commands

import utils.api.restaurants
from utils.embed import send_embed

class ShopsView(discord.ui.View):
    def __init__(self, author):
        super().__init__()
        self.author = author
    
    @discord.ui.button(label="Claim all", style=discord.ButtonStyle.success)
    async def claim_all_callback(self, interaction, button):
        '''
        Claim shops if any
        '''
        button.disabled = True
        button.label = "Claimed!"
        await interaction.response.edit_message(view=self)

        discord_id = interaction.user.id
        title, description, colour, img_url = utils.api.restaurants.claim_shops(discord_id)

        embed = discord.Embed(title=title, description=description, colour=colour)
        embed.set_image(url=img_url)

        await interaction.followup.send(embed=embed)

    @discord.ui.button(label="Refill all", style=discord.ButtonStyle.primary)
    async def refill_all_callback(self, interaction, button):
        button.disabled = True
        button.label = "Refilled!"
        await interaction.response.edit_message(view=self)

        discord_id = interaction.user.id
        title, description, colour, img_url = utils.api.restaurants.refill_all(discord_id)

        embed = discord.Embed(title=title, description=description, colour=colour)
        embed.set_image(url=img_url)

        await interaction.followup.send(embed=embed)
    
    async def interaction_check(self, interaction: discord.Interaction):
        return interaction.user.id == self.author.id

class SeeShopView(discord.ui.View):
    def __init__(self, restaurant_public_id, author):
        super().__init__()
        self.author = author
        self.public_id = restaurant_public_id

    @discord.ui.button(label="Upgrade Capacity", style=discord.ButtonStyle.primary)
    async def uc_callback(self, interaction, button):
        button.disabled = True
        button.label = "---"
        await interaction.response.edit_message(view=self)

        title, description, colour = utils.api.restaurants.upgrade(self.public_id, "capacity")
        embed = discord.Embed(title=title, description=description, colour=colour)

        await interaction.followup.send(embed=embed)
    
    @discord.ui.button(label=f"Upgrade Quality", style=discord.ButtonStyle.primary)
    async def uq_callback(self, interaction, button):
        button.disabled = True
        button.label = "---"
        await interaction.response.edit_message(view=self)
    
        title, description, colour = utils.api.restaurants.upgrade(self.public_id, "quality")
        embed = discord.Embed(title=title, description=description, colour=colour)

        await interaction.followup.send(embed=embed)
    
    async def interaction_check(self, interaction: discord.Interaction):
        return interaction.user.id == self.author.id
