import discord.ext.commands as commands
import utils.api.users

ADMINS = [275260209771970560]

def admin():
    def predicate(ctx):
        allowed = ctx.author.id in ADMINS
        return allowed
    
    return commands.check(predicate)

def player():
    def predicate(ctx):
        allowed = utils.api.users.user_exists(ctx.author.id)
        return allowed

    return commands.check(predicate)