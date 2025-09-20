use super::ingredients::Receipe;
use std::collections::HashMap;

pub struct Ramen {
    pub name: String,
    pub receipe: Receipe,
}

impl Ramen {
    pub fn new(name: &str, receipe: Receipe) -> Self {
        Self {
            name: String::from(name),
            receipe: receipe,
        }
    }
}

pub struct Menu {
    pub prices: HashMap<String, f64>,
}

impl Menu {
    pub fn new() -> Self {
        Self {
            prices: HashMap::new()
        }
    }
}
