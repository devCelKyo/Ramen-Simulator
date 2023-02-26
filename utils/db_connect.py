from tortoise import Tortoise
import os

async def connect():
    db_url = "mysql://{}/game_db".format(os.environ['db_credentials'])
    await Tortoise.init(
        db_url=db_url,
        modules={'models': ['models.user']}
    )