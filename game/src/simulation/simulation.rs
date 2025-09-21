use crate::restaurants::*;

use std::collections::HashMap;
use std::time::{Duration, SystemTime};

pub struct SimulationEngine {
    restaurants: HashMap<RestaurantKey, Restaurant>,
    update_states: HashMap<RestaurantKey, SystemTime>,
    increment: Duration
}

impl SimulationEngine {
    pub fn new() -> SimulationEngine {
        Self {
            restaurants: HashMap::new(),
            update_states: HashMap::new(),
            increment: Duration::from_secs(5),
        }
    }

    pub fn push_restaurant(&mut self, restaurant: Restaurant) {
        let id = restaurant.id;
        self.restaurants.insert(id, restaurant);
        self.update_states.insert(id, SystemTime::now());
    }

    /// Assumes Restaurant is loaded and cached
    pub fn simulate(&mut self, key: RestaurantKey, time: SystemTime) {
        let rest = self.restaurants.get_mut(&key).unwrap();
        
        let last_updated = self.update_states.get(&key).unwrap();
        let maybe_duration = time.duration_since(*last_updated);
        if maybe_duration.is_err() {
            return;
        }
        let duration = maybe_duration.unwrap();
        if duration < self.increment {
            return;
        }
        
        let steps = duration.as_secs() / self.increment.as_secs();
        
        // hard coded shit
        let time_between_customers = Duration::from_secs(60);
        let order_processing_time = Duration::from_secs(30);
        //

        let mut time_before_next_customer = Duration::ZERO;
        let mut time_before_order_is_done = Duration::ZERO;
        let mut order_being_processed: Option<Order> = None;

        for _ in 0..steps {
            if time_before_next_customer == Duration::ZERO {
                time_before_next_customer = time_between_customers;
                let picked = rest.menu.get_one();
                match picked {
                    Some(t) => {
                        let order = Order{ramen: (t.0).clone(), price:t.1};
                        rest.place_order(order);
                    },
                    None => continue
                }
            }

            // TODO: 
            // - If no order is being processed, pick one from the top and start processing it
            // - If an order is being processed, keep doing it
        }
    }
}