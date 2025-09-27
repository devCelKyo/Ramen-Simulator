use crate::restaurants::*;

use std::collections::HashMap;
use std::time::{Duration, SystemTime};

pub struct SimulationEngine {
    restaurants: HashMap<RestaurantKey, Restaurant>,
    update_states: HashMap<RestaurantKey, SystemTime>,
    increment: Duration
}

pub struct SimulationOutput {
    pub restaurant: RestaurantKey,
    pub earnings: f64,
    pub ramen_served: i32,
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

    pub fn seek_restaurant(&self, key: RestaurantKey) -> Option<&Restaurant> {
        return self.restaurants.get(&key)
    }

    pub fn simulate(&mut self, key: RestaurantKey, time: SystemTime) -> Option<SimulationOutput> {
        let rest = match self.restaurants.get_mut(&key) {
            Some(r) => r,
            None => return None
        };

        let last_updated = match self.update_states.get(&key) {
            Some(u) => u,
            None => return None
        };

        let duration = match time.duration_since(*last_updated) {
            Ok(d) => d,
            Err(_) => return None
        };
        
        if duration < self.increment {
            return None;
        }
        
        let mut output = SimulationOutput{restaurant: key, earnings: 0., ramen_served: 0};
        let steps = duration.as_secs() / self.increment.as_secs();
        
        // hard coded shit
        // Eventually, customer entering the restaurant should be on a certain probability at each increment
        // This should be based on demand and capacity (to be calculated later, depending on restaurant)
        let time_between_customers = Duration::from_secs(60);
        let order_processing_time = Duration::from_secs(30);

        let mut time_before_next_customer = Duration::ZERO;
        let mut time_before_order_is_done = Duration::ZERO;
        let mut order_being_processed: Option<Order> = None;

        for _ in 0..steps {
            // Customer generation
            if time_before_next_customer == Duration::ZERO {
                time_before_next_customer = time_between_customers;
                let picked: Option<(&Ramen, f64)> = rest.menu.get_one();
                match picked {
                    Some(t) => {
                        let order = Order{ramen: (t.0).clone(), price:t.1};
                        rest.placed_orders.place_order(order);
                    },
                    None => continue
                }
            }

            // Order processing
            match order_being_processed {
                Some(_) => time_before_order_is_done = time_before_order_is_done.saturating_sub(self.increment),
                None => {
                    order_being_processed = rest.placed_orders.pop_first();
                    time_before_order_is_done = order_processing_time;
                },
            }

            // If order is done
            if time_before_order_is_done == Duration::ZERO {
                if let Some(order) = order_being_processed.as_ref() {
                    rest.cash += order.price;
                    output.earnings += order.price;
                    output.ramen_served += 1;
                }
            }
            time_before_next_customer = time_before_next_customer.saturating_sub(self.increment);
        }
        Some(output)
    }
}