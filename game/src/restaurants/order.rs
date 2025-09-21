use std::collections::VecDeque;
use super::Ramen;

pub struct Order {
    pub ramen: Ramen,
    pub price: f64
}

pub struct OrderQueue {
    orders: VecDeque<Order>
}

impl OrderQueue {
    pub fn new() -> Self {
        Self {
            orders: VecDeque::new()
        }
    }

    pub fn is_empty(&self) -> bool {
        self.orders.is_empty()
    }

    pub fn size(&self) -> usize {
        self.orders.len()
    }

    pub fn pop_first(&mut self) -> Order {
        assert!(!self.is_empty());
        self.orders.pop_front().unwrap()
    }

    pub fn place_order(&mut self, order: Order) {
        self.orders.push_back(order);
    }
}