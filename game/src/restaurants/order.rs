use std::collections::VecDeque;
use super::Ramen;
use super::Receipe;

pub struct Order {
    pub ramen: Ramen,
    pub price: f64
}

impl Order {
    pub fn receipe(&self) -> &Receipe {
        &self.ramen.receipe
    }
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

    pub fn pop_first(&mut self) -> Option<Order> {
        self.orders.pop_front()
    }

    pub fn peek(&self) -> Option<&Order> {
        self.orders.front()
    }

    pub fn place_order(&mut self, order: Order) {
        self.orders.push_back(order);
    }
}