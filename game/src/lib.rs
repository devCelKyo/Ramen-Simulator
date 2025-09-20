#![allow(dead_code)]

pub mod restaurants;

pub fn run() {
    println!("Welcome to Ramen Simulator!");
    let rest = restaurants::Restaurant::new("ichiraku");
    println!("{:?}", rest.name);
}