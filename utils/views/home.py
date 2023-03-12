import discord

import utils.api.users

class HomeView(discord.ui.View):
    def __init__(self, author):
        super().__init__()
        self.author = author
    
    @discord.ui.button(label="Rebirth", style=discord.ButtonStyle.success)
    async def rebirth_callback(self, interaction, button):
        button.disabled = True
        button.label = "---"
        await interaction.response.edit_message(view=self)

        title, description, colour = utils.api.users.rebirth(self.author.id)
        embed = discord.Embed(title=title, description=description, colour=colour)

        await interaction.followup.send(embed=embed)

    async def interaction_check(self, interaction):
        return interaction.user.id == self.author.id