import requests

def get_restaurants(discord_id):
    response = requests.get(f"http://localhost:8000/restaurants/get_restaurants/{discord_id}").json()
    return response

def buy_restaurant(discord_id):
    response = requests.get(f"http://localhost:8000/restaurants/add_restaurant/{discord_id}").json()
    return response

def update_restaurant(restaurant_id):
    requests.get(f"http://localhost:8000/restaurants/update_restaurant/{restaurant_id}")

def update_user_restaurants(discord_id):
    requests.get(f"http://localhost:8000/restaurants/update_restaurants/{discord_id}")

def claim_shops(discord_id):
    response = requests.get(f"http://localhost:8000/restaurants/claim_restaurants/{discord_id}").json()
    return response