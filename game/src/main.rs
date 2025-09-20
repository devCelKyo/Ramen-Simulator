#![allow(dead_code)]

mod restaurants;

fn main() {
    println!("Welcome to Ramen Simulator!");
    let rest = restaurants::Restaurant::new("ichiraku");
    println!("{:?}", rest.name);
}
