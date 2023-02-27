import discord

async def send_embed(title, description, ctx, colour=discord.Colour.dark_gold()):
    embed = discord.Embed(
        description=description,
        title=title,
        colour=colour
    )

    await ctx.send(embed=embed)