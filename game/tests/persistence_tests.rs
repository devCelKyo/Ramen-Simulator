mod catalog;

use game::persistence;
use rusqlite::Connection;

fn get_memory_db() -> Connection
{
    let conn = Connection::open_in_memory();
    conn.expect("Could not open DB")
}

#[test]
fn init_db()
{
    let db = get_memory_db();
    persistence::init(&db);
}

#[test]
fn insert_and_load_restaurant()
{
    let db = get_memory_db();
    persistence::init(&db);

    let mut r = catalog::basic_restaurant();
    let inserted_id = persistence::insert_restaurant(&db, &r).expect("Restaurant should have been inserted.");
    r.id = inserted_id;

    let loaded_r = persistence::load_restaurant(&db, r.id).expect("Restaurant should exist.");
    assert_eq!(r.id, loaded_r.id);
    assert_eq!(r.name, loaded_r.name);
    assert_eq!(r.cash, loaded_r.cash);
}


