#![allow(dead_code)]

use rusqlite::Connection;

pub mod restaurants;
pub mod simulation;

pub mod controller;

pub fn run() {
    println!("Welcome to Ramen Simulator!");
    let _conn = Connection::open("rs.db");
}