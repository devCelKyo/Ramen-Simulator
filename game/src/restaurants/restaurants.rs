use super::Menu;
use super::Inventory;

pub struct Restaurant {
    pub name: String,
    pub cash: f64,
    pub menu: Menu,
    pub stocks: Inventory,
}

impl Restaurant {
    pub fn default(name: &str) -> Restaurant {
        Self {
            name: String::from(name),
            cash: 0.,
            menu: Menu::new(),
            stocks: Inventory::new(),
        }
    }

    pub fn new(name: &str, cash: f64, menu: Menu, stocks: Inventory) -> Restaurant {
        Self {
            name: String::from(name),
            cash: cash,
            menu: menu,
            stocks: stocks,
        }
    }
}