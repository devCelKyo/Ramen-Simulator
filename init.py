from tortoise import Tortoise, run_async
from utils.db_connect import connect

async def init():
    await connect()
    # Generate the schema
    await Tortoise.generate_schemas()
    await Tortoise.close_connections()

run_async(init())
