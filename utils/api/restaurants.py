import requests
import discord
import assets.restaurants

def get_restaurants(discord_id):
    response = requests.get(f"http://localhost:8000/restaurants/get_restaurants/{discord_id}").json()
    return response

def get_restaurant(restaurant_public_id, discord_id):
    response = requests.post(f"http://localhost:8000/restaurants/get_restaurant/{restaurant_public_id}", data={"discord_id":discord_id}).json()
    return response

def buy_restaurant(discord_id):
    response = requests.get(f"http://localhost:8000/restaurants/add_restaurant/{discord_id}").json()
    if response["error"] == "True":
        title = "Error!"
        description = response["message"]
        colour = discord.Colour.brand_red()
    else:
        title = "Restaurant Purchase"
        description = f"You succesfully purchased a restaurant!"
        colour = discord.Colour.brand_green()
    
    return title, description, colour

def upgrade(restaurant_public_id, upgrade_type):
    response = requests.get(f"http://localhost:8000/restaurants/upgrade/{restaurant_public_id}/{upgrade_type}").json()
    if response["error"] == "True":
        title = "Error!"
        description = response["message"]
        colour = discord.Colour.brand_red()
    else:
        title = "Upgrade successful!"
        description = f"You succesfully upgraded the {response['upgrade_type']} of the restaurant #`{restaurant_public_id}`"
        colour = discord.Colour.brand_green()
    
    return title, description, colour

def update_restaurant(restaurant_id):
    requests.get(f"http://localhost:8000/restaurants/update_restaurant/{restaurant_id}")

def update_user_restaurants(discord_id):
    requests.get(f"http://localhost:8000/restaurants/update_restaurants/{discord_id}")

def claim_shops(discord_id):
    response = requests.get(f"http://localhost:8000/restaurants/claim_restaurants/{discord_id}").json()
    if response["error"] == "True":
        title = "Error !"
        description = response["message"]
        colour = discord.Colour.brand_red()
        img_url = assets.restaurants.FAIL
    else:
        title = "Revenue Redeemed!"
        given_money = response['given_money']
        if given_money == 0:
            description = "There was nothing to claim... Get to work!"
            colour = discord.Colour.dark_red()
            img_url = assets.restaurants.NO_MONEY
        else:
            description = f"You redeemed what your restaurants earned and got {response['given_money']}両!"
            colour = discord.Colour.brand_green()
            img_url = assets.restaurants.RYO
    
    return title, description, colour, img_url

def refill_all(discord_id):
    response = requests.get(f"http://localhost:8000/restaurants/refill_restaurants/{discord_id}").json()
    if response["error"] == "True":
        title = "Error !"
        description = response["message"]
        colour = discord.Colour.brand_red()
        img_url = assets.restaurants.FAIL
    else:
        title = "Restaurants refilled!"
        description = f"You spent {response['total_cost']}両 and ordered a total of {response['total_added_ramen']} bowl(s) for your restaurant(s)."
        colour = discord.Colour.brand_green()
        img_url = assets.restaurants.RAMEN
    
    return title, description, colour, img_url

def add_workers(discord_id, restaurant_public_id, workers_amount):
    params = {"discord_id":discord_id, "workers_to_add":workers_amount}
    response = requests.post(f"http://localhost:8000/restaurants/add_workers/{restaurant_public_id}",
                             data=params).json()
    
    if response["error"] == "True":
        title = "Error !"
        description = response["message"]
        colour = discord.Colour.brand_red()
        img_url = None
    else:
        title = "Workers hired!"
        description = f"You spent {response['total_cost']}両 and hired {response['added_workers']} new workers!"
        colour = discord.Colour.brand_green()
        img_url = None
    
    return title, description, colour, img_url

def buy_slot(discord_id):
    response = requests.get(f"http://localhost:8000/restaurants/buy_slot/{discord_id}").json()

    if response["error"] == "True":
        title = "Error !"
        description = response["message"]
        colour = discord.Colour.brand_red()
        img_url = None
    else:
        title = "Slot bought!"
        description = f"You spent {response['cost']}両 and bougth another slot to build a restaurant on! You now have {response['slots']} slots."
        colour = discord.Colour.brand_green()
        img_url = None
    
    return title, description, colour, img_url

def add_star(discord_id, upgradeRestaurant, fodderRestaurants: list):
    params = {"discord_id": discord_id, "n": len(fodderRestaurants)}
    for i in range(1, len(fodderRestaurants) + 1):
        params[f"restaurant_public_id{i}"] = fodderRestaurants[i - 1]
    
    response = requests.get(f"http://localhost:8000/restaurants/add_star/{upgradeRestaurant}",
                            data=params).json()
    
    if response["error"] == "True":
        title = "Error !"
        description = response["message"]
        colour = discord.Colour.brand_red()
        img_url = None
    else:
        title = "Star upgrade succesful!"
        description = "-- to be written --"
        colour = discord.Colour.gold()
        img_url = None
    
    return title, description, colour, img_url
