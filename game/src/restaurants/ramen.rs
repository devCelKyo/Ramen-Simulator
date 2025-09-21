use super::Receipe;
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
    prices: HashMap<String, f64>,
    ramens: HashMap<String, Ramen>,
}

impl Menu {
    pub fn default() -> Self {
        Self {
            prices: HashMap::new(),
            ramens: HashMap::new(),
        }
    }

    pub fn new(prices: HashMap<String, f64>, ramens: HashMap<String, Ramen>) -> Self {
        Self {
            prices: prices,
            ramens: ramens,
        }
    }

    pub fn is_empty(&self) -> bool {
        self.prices.is_empty()
    }

    /// No-op if Ramen already present
    pub fn push_ramen(&mut self, ramen: Ramen, price: f64) -> &Self {
        let name = &ramen.name;
        if self.prices.contains_key(name) {
            return self
        }
        self.prices.insert(name.clone(), price);
        self.ramens.insert(name.clone(), ramen);
        self
    }
}
