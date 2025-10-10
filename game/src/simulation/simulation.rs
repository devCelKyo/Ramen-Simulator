use crate::restaurants::*;

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

#[derive(Debug)]
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

        for _ in 0..steps {
            // Customer generation
            let option_order = restaurant_engine.demand_calculator.tick(&restaurant_engine.restaurant, self.increment);
            if let Some(order) = option_order {
                restaurant_engine.order_processor.receive_order(&mut restaurant_engine.restaurant, order);
            }

            // Order processing
            restaurant_engine.order_processor.tick(&mut restaurant_engine.restaurant, &mut output, self.increment);
        }

        let simulated_duration = Duration::from_secs(steps * self.increment.as_secs());
        restaurant_engine.update_state = *last_updated + simulated_duration;
        Ok(output)
    }
}