import discord
import discord.ext.commands as commands

from utils.embed import send_embed
from utils.api.restaurants import get_restaurants, buy_restaurant, update_user_restaurants, claim_shops

class ShopsView(discord.ui.View):
    @discord.ui.button(label="Claim all", style=discord.ButtonStyle.success)
    async def claim_all_callback(self, interaction, button):
        '''
        Claim shops if any
        '''
        button.disabled = True
        button.label = "Claimed!"
        await interaction.response.edit_message(view=self)

        discord_id = interaction.user.id
        response = claim_shops(discord_id)

        if response["error"] == "True":
            title = "Error !"
            description = response["message"]
            colour = discord.Colour.brand_red()
        else:
            title = "Revenue Redeemed!"
            description = f"You redeemed what your restaurants earned and got {response['given_money']}両!"
            colour = discord.Colour.brand_green()
        
        await send_embed(title, description, interaction.followup, colour, followup=True)