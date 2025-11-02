use rusqlite::Connection;
use sea_query::{*};

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
        .col(ColumnDef::new(MenuEntry::Price).integer().not_null())
        .to_owned();

    let _ = connection.execute(&query_entry.to_string(SqliteQueryBuilder), ());
}

#[derive(Iden)]
pub enum Restaurant 
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
        .table(Restaurant::Table)
        .if_not_exists()
        .col(ColumnDef::new(Restaurant::Id).integer().not_null().auto_increment().primary_key())
        .col(ColumnDef::new(Restaurant::OwnerId).integer().not_null())
        .col(ColumnDef::new(Restaurant::Name).string().not_null())
        .col(ColumnDef::new(Restaurant::Cash).double().not_null())
        .col(ColumnDef::new(Restaurant::InventoryId).integer().not_null())
        .col(ColumnDef::new(Restaurant::MenuId).integer().not_null())
        .to_owned();

    let _ = connection.execute(&query.to_string(SqliteQueryBuilder), ());
}

pub fn init(connection : &Connection) 
{
    create_restaurant_table(connection);
    create_inventory_tables(connection);
    create_menu_tables(connection);
}