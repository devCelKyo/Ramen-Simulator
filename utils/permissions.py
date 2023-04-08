ADMINS = ["275260209771970560"]

def admin(func):
    def inner(ctx, *args, **kwargs):
        if ctx.author.id not in ADMINS:
            raise Exception("Not allowed")
        else:
            func(*args, **kwargs)
    
    return inner