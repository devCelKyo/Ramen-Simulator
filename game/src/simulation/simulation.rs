use crate::restaurants::*;

use std::collections::HashMap;
use std::time::SystemTime;

pub struct SimulationEngine {
    restaurants: HashMap<RestaurantKey, Restaurant>,
    update_states: HashMap<RestaurantKey, SystemTime>,
}

impl SimulationEngine {
    pub fn new() -> SimulationEngine {
        Self {
            restaurants: HashMap::new(),
            update_states: HashMap::new(), 
        }
    }

    pub fn push_restaurant(&mut self, restaurant: Restaurant) {
        let id = restaurant.id;
        self.restaurants.insert(id, restaurant);
        self.update_states.insert(id, SystemTime::now());
    }
}