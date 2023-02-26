from tortoise.models import Model
from tortoise import fields

class User(Model):
    id = fields.IntField(pk=True)
    discord_id = fields.IntField()
    money = fields.IntField()