import discord

async def send_embed(title, description, ctx, colour=discord.Colour.dark_gold(), interaction=False, followup=False):
    embed = discord.Embed(
        description=description,
        title=title,
        colour=colour
    )

    if interaction:
        await ctx.send_message(embed=embed)
    if followup:
        await ctx.send(embed=embed)
        
    await ctx.reply(embed=embed)
    