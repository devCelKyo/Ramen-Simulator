use crate::restaurants::*;
use std::time::{Duration, SystemTime};

pub struct RestaurantEngine {
    pub restaurant: Restaurant,
    pub update_state: SystemTime,
    pub demand_calculator: DemandCalculator,
    pub order_processor: OrderProcessor,
}

impl RestaurantEngine {
    pub fn new(r: Restaurant, timestamp: SystemTime) -> Self {
        Self {
            restaurant: r,
            update_state: timestamp,
            demand_calculator: DemandCalculator{},
            order_processor: OrderProcessor{},
        }
    }
}

pub struct DemandCalculator {}
pub struct OrderProcessor {}
