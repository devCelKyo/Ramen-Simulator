#![allow(dead_code)]

use rusqlite::Connection;

pub mod restaurants;
pub mod simulation;

pub mod controller;
pub mod persistence;

pub fn run() {
    println!("Welcome to Ramen Simulator!");
    let conn = Connection::open("rs.db");
    persistence::db::init(&conn.unwrap());
}