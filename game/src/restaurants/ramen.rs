use super::ingredients::Receipe;
use std::collections::HashMap;

pub struct Ramen {
    pub name: String,
    pub receipe: Receipe,
}

pub struct Menu {
    pub prices: HashMap<String, f64>,
}
