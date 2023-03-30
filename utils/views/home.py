import discord

import utils.api.restaurants

class HomeView(discord.ui.View):
    def __init__(self, bot, ctx):
        super().__init__()
        self.bot = bot
        self.ctx = ctx
        self.author = ctx.author
    
    @discord.ui.button(label="Buy slot", style=discord.ButtonStyle.danger)
    async def buy_slot_callback(self, interaction, button):
        button.disabled = True
        button.label = "---"
        await interaction.response.edit_message(view=self)

        title, description, colour, img_url = utils.api.restaurants.buy_slot(self.author.id)
        await self.ctx.reply(embed=discord.Embed(title=title, description=description, colour=colour, url=img_url))
    
    @discord.ui.button(label="Buy restaurant", style=discord.ButtonStyle.success)
    async def buy_shop_callback(self, interaction, button):
        button.disabled = True
        button.label = "---"
        await interaction.response.edit_message(view=self)
        await self.ctx.invoke(self.bot.get_command('buy_shop'))
    
    async def interaction_check(self, interaction: discord.Interaction):
        return interaction.user.id == self.author.id