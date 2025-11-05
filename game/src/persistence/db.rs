use rusqlite::Connection;
use sea_query::{*};

#[derive(Iden)]
pub enum Ingredient
{
    Table,
    Id,
    Name,
    SavedPrice,
}

pub fn create_ingredient_table(connection : &Connection)
{
    let query = Table::create()
        .table(Ingredient::Table)
        .if_not_exists()
        .col(ColumnDef::new(Ingredient::Id).integer().not_null().auto_increment().primary_key())
        .col(ColumnDef::new(Ingredient::Name).string().not_null())
        .col(ColumnDef::new(Ingredient::SavedPrice).double().not_null())
        .to_owned();

    let _ = connection.execute(&query.to_string(SqliteQueryBuilder), ());
}


#[derive(Iden)]
pub enum InventoryHead
{
    Table,
    Id,
    RestaurantId,
}

#[derive(Iden)]
pub enum InventoryEntry
{
    Table,
    InventoryId,
    IngredientId,
    Quantity,
}

pub fn create_inventory_tables(connection : &Connection)
{
    let query_head = Table::create()
        .table(InventoryHead::Table)
        .if_not_exists()
        .col(ColumnDef::new(InventoryHead::Id).integer().not_null().auto_increment().primary_key())
        .col(ColumnDef::new(InventoryHead::RestaurantId).integer().not_null())
        .to_owned();
    
    let _ = connection.execute(&query_head.to_string(SqliteQueryBuilder), ());
    
    let query_entry = Table::create()
        .table(InventoryEntry::Table)
        .if_not_exists()
        .col(ColumnDef::new(InventoryEntry::InventoryId).integer().not_null())
        .col(ColumnDef::new(InventoryEntry::IngredientId).integer().not_null())
        .col(ColumnDef::new(InventoryEntry::Quantity).integer().not_null())
        .to_owned();

    let _ = connection.execute(&query_entry.to_string(SqliteQueryBuilder), ());
}

#[derive(Iden)]
pub enum RamenHead
{
    Table,
    Id,
    Name,
}

#[derive(Iden)]
pub enum RamenEntry
{
    Table,
    RamenId,
    IngredientId,
}

pub fn create_ramen_tables(connection : &Connection)
{
    let query_head = Table::create()
        .table(RamenHead::Table)
        .if_not_exists()
        .col(ColumnDef::new(RamenHead::Id).integer().not_null().auto_increment().primary_key())
        .col(ColumnDef::new(RamenHead::Name).string().not_null())
        .to_owned();
    
    let _ = connection.execute(&query_head.to_string(SqliteQueryBuilder), ());
    
    let query_entry = Table::create()
        .table(RamenEntry::Table)
        .if_not_exists()
        .col(ColumnDef::new(RamenEntry::RamenId).integer().not_null())
        .col(ColumnDef::new(RamenEntry::IngredientId).integer().not_null())
        .to_owned();

    let _ = connection.execute(&query_entry.to_string(SqliteQueryBuilder), ());
}

#[derive(Iden)]
pub enum MenuHead
{
    Table,
    Id,
    RestaurantId,
}

#[derive(Iden)]
pub enum MenuEntry
{
    Table,
    MenuId,
    RamenId,
    Price,
}

pub fn create_menu_tables(connection : &Connection)
{
    let query_head = Table::create()
        .table(MenuHead::Table)
        .if_not_exists()
        .col(ColumnDef::new(MenuHead::Id).integer().not_null().auto_increment().primary_key())
        .col(ColumnDef::new(MenuHead::RestaurantId).integer().not_null())
        .to_owned();
    
    let _ = connection.execute(&query_head.to_string(SqliteQueryBuilder), ());
    
    let query_entry = Table::create()
        .table(MenuEntry::Table)
        .if_not_exists()
        .col(ColumnDef::new(MenuEntry::MenuId).integer().not_null())
        .col(ColumnDef::new(MenuEntry::RamenId).integer().not_null())
        .col(ColumnDef::new(MenuEntry::Price).double().not_null())
        .to_owned();

    let _ = connection.execute(&query_entry.to_string(SqliteQueryBuilder), ());
}

#[derive(Iden)]
pub enum RestaurantColumn 
{
    Table,
    Id,
    OwnerId,
    Name,
    Cash,
    InventoryId,
    MenuId,
}

pub fn create_restaurant_table(connection : &Connection)
{
    let query = Table::create()
        .table(RestaurantColumn::Table)
        .if_not_exists()
        .col(ColumnDef::new(RestaurantColumn::Id).integer().not_null().auto_increment().primary_key())
        .col(ColumnDef::new(RestaurantColumn::OwnerId).integer().not_null())
        .col(ColumnDef::new(RestaurantColumn::Name).string().not_null())
        .col(ColumnDef::new(RestaurantColumn::Cash).double().not_null())
        .col(ColumnDef::new(RestaurantColumn::InventoryId).integer().not_null())
        .col(ColumnDef::new(RestaurantColumn::MenuId).integer().not_null())
        .to_owned();

    let _ = connection.execute(&query.to_string(SqliteQueryBuilder), ());
}

pub fn init(connection : &Connection) 
{
    create_restaurant_table(connection);
    create_inventory_tables(connection);
    create_menu_tables(connection);
    create_ramen_tables(connection);
    create_ingredient_table(connection);
}

use crate::restaurants::{self, *};

pub fn insert_restaurant(connection: &Connection, restaurant: &Restaurant) 
{
    let query = Query::insert()
        .into_table(RestaurantColumn::Table)
        .columns([RestaurantColumn::Name, RestaurantColumn::Cash])
        .values_panic([restaurant.name.clone().into(), restaurant.cash.into()])
        .to_owned();

    let _ = connection.execute(&query.to_string(SqliteQueryBuilder), ());
}

pub fn save_restaurant(connection: &Connection, restaurant: &Restaurant) 
{
    let query = Query::update()
        .table(RestaurantColumn::Table)
        .values([(RestaurantColumn::Name, restaurant.name.clone().into()),
                 (RestaurantColumn::Cash, restaurant.cash.into())])
        .and_where(Expr::col(RestaurantColumn::Id).eq(restaurant.id))
        .to_owned();

    let _ = connection.execute(&query.to_string(SqliteQueryBuilder), ());
}

pub fn load_restaurant(connection: &Connection, key: RestaurantKey) -> Option<Restaurant>
{
    let query = Query::select()
        .column(RestaurantColumn::Name)
        .column(RestaurantColumn::Cash)
        .from(RestaurantColumn::Table)
        .and_where(Expr::col(RestaurantColumn::Id).eq(key))
        .to_owned();

    //let result = connection.query_one(&query.to_string(SqliteQueryBuilder), (), 
    None // todo make me work
}