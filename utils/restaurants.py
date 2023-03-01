import requests

def get_restaurants(discord_id):
    response = requests.get(f"http://localhost:8000/restaurants/get_restaurants/{discord_id}").json()
    return response

def buy_restaurant(discord_id):
    response = requests.get(f"http://localhost:8000/restaurants/add_restaurant/{discord_id}").json()
    return response