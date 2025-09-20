mod ingredients;
mod ramen;

pub use ramen::*;
pub use ingredients::*;

pub struct Restaurant {
    pub name: String,
    pub cash: f64,
    menu: ramen::Menu,
    stocks: ingredients::Inventory,
}

impl Restaurant {
    pub fn new(name: &str) -> Restaurant {
        Self {
            name: String::from(name),
            cash: 0.,
            menu: Menu::new(),
            stocks: Inventory::new(),
        }
    }
}
