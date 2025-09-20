use super::ingredients::Receipe;
use std::collections::HashMap;

pub struct Ramen {
    name: String,
    receipe: Receipe,
}

pub struct Menu {
    prices: HashMap<String, f64>,
}

impl Menu {
    pub fn new() -> Self {
        Self {
            prices: HashMap::new()
        }
    }
}
