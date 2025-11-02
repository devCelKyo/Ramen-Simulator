use rusqlite::Connection;
use sea_query::{*};

#[derive(Iden)]
pub enum Restaurant {
    Table,
    Id,
    OwnerId,
    Name,
    Cash,
}

pub fn init(connection : &Connection) {
    let query = Table::create()
        .table(Restaurant::Table)
        .if_not_exists()
        .col(ColumnDef::new(Restaurant::Id).integer().not_null().auto_increment().primary_key())
        .col(ColumnDef::new(Restaurant::OwnerId).integer().not_null())
        .col(ColumnDef::new(Restaurant::Name).string().not_null())
        .col(ColumnDef::new(Restaurant::Cash).double().not_null())
        .to_owned();

    let _ = connection.execute(&query.to_string(SqliteQueryBuilder), ());
}