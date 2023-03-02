import requests

def get_user(discord_id):
    response = requests.get(f"http://localhost:8000/get_user/{discord_id}").json()
    return response

def user_exists(discord_id):
    response = requests.get(f"http://localhost:8000/get_user/{discord_id}").json()
    return response['error'] == "False"

def create_user(discord_id):
    params = {"discord_id" : discord_id}
    requests.post("http://localhost:8000/create_user", data=params)

def claim_daily_user(discord_id):
    params = {"discord_id" : discord_id}
    response = requests.post("http://localhost:8000/claim_daily", data=params).json()
    return response