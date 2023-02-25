from tortoise import Tortoise
import os
from asyncio import run

async def init():
    # Here we create a SQLite DB using file "db.sqlite3"
    #  also specify the app name of "models"
    #  which contain models from "app.models"
    db_url = "mysql://{}/game_db".format(os.environ['db_credentials'])
    await Tortoise.init(
        db_url=db_url,
        modules={'models': ['model']}
    )
    # Generate the schema
    await Tortoise.generate_schemas()

run(init())
