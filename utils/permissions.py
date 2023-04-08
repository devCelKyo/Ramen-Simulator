import discord.ext.commands as commands


ADMINS = ["275260209771970560"]

def admin():
    def predicate(ctx):
        allowed = ctx.author.id in ADMINS
        return allowed
    
    return commands.check(predicate)