use crate::restaurants::*;

use std::alloc::System;
use std::collections::HashMap;
use std::time::{Duration, SystemTime};

use super::RestaurantEngine;

pub struct SimulationEngine {
    restaurants: HashMap<RestaurantKey, RestaurantEngine>,
    increment: Duration
}

pub struct SimulationOutput {
    pub restaurant: RestaurantKey,
    pub earnings: f64,
    pub ramen_served: i32,
}

pub enum SimulationError {
    RestaurantNotFound,
    InvalidDuration,
} 

impl SimulationEngine {
    pub fn new() -> SimulationEngine {
        Self {
            restaurants: HashMap::new(),
            increment: Duration::from_secs(5),
        }
    }

    pub fn push_restaurant(&mut self, restaurant: Restaurant) {
        let id = restaurant.id;
        self.restaurants.insert(id, RestaurantEngine::new(restaurant, SystemTime::now()));
    }

    pub fn seek_restaurant(&self, key: RestaurantKey) -> Option<&Restaurant> {
        match self.restaurants.get(&key) {
            Some(engine) => Some(&engine.restaurant),
            None => None,
        }
    }

    pub fn simulate(&mut self, key: RestaurantKey, time: SystemTime) -> Result<SimulationOutput, SimulationError> {
        let restaurant_engine = match self.restaurants.get_mut(&key) {
            Some(r) => r,
            None => return Err(SimulationError::RestaurantNotFound)
        };

        let last_updated = &restaurant_engine.update_state;
        let duration = match time.duration_since(*last_updated) {
            Ok(d) => d,
            Err(_) => return Err(SimulationError::InvalidDuration)
        };
        
        if duration < self.increment {
            return Err(SimulationError::InvalidDuration);
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
            // target
            // let option_order = demand_calculator.tick(duration)
            // if there is one --> transfer it to OrderProcessor
            // order_processor.tick(duration) --> returns the order if done

            // Customer generation

            // Order processing
            // TODO MOVE ME IN ORDERPROCESSOR
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