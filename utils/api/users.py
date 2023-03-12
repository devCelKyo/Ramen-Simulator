import requests
import discord

def get_user(discord_id):
    response = requests.get(f"http://localhost:8000/users/get_user/{discord_id}").json()
    return response

def user_exists(discord_id):
    response = requests.get(f"http://localhost:8000/users/get_user/{discord_id}").json()
    return response['error'] == "False"

def create_user(discord_id):
    params = {"discord_id" : discord_id}
    requests.post("http://localhost:8000/users/create_user", data=params)

def claim_daily_user(discord_id):
    params = {"discord_id" : discord_id}
    response = requests.post("http://localhost:8000/users/claim_daily", data=params).json()
    return response

def rebirth(discord_id):
    response = requests.get(f"http://localhost:8000/users/rebirth/{discord_id}").json()
    if response["error"] == "True":
        title = "You can't do that"
        description = response["message"]
        colour = discord.Colour.brand_red()
    else:
        title = "Rebirth done!!"
        description = f"Your money multiplier is now x{response['new_multiplier']}"
        colour = discord.Colour.brand_green()
    return title, description, colour