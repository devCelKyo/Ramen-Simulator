import requests

def user_exists(discord_id):
    response = requests.get(f"http://localhost:8000/get_user/{discord_id}").json()
    return response['error'] == "False"

def create_user(discord_id):
    params = {"discord_id" : discord_id}
    requests.post("http://localhost:8000/create_user", data=params)