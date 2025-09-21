use super::Menu;
use super::Inventory;

use super::Order;
use super::OrderQueue;

pub type RestaurantKey = i32;
pub struct Restaurant {
    pub id: RestaurantKey,
    pub name: String,
    pub cash: f64,
    pub menu: Menu,
    pub stocks: Inventory,
    placed_orders: OrderQueue,
}

impl Restaurant {
    pub fn default(name: &str) -> Restaurant {
        Self {
            id: 0,
            name: String::from(name),
            cash: 0.,
            menu: Menu::default(),
            stocks: Inventory::new(),
            placed_orders: OrderQueue::new(),
        }
    }

    pub fn new(name: &str, cash: f64, menu: Menu, stocks: Inventory) -> Restaurant {
        Self {
            id: 0,
            name: String::from(name),
            cash: cash,
            menu: menu,
            stocks: stocks,
            placed_orders: OrderQueue::new(),
        }
    }

    pub fn place_order(&mut self, order: Order) {
        self.placed_orders.place_order(order);
    }
}