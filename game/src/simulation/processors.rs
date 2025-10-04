use crate::restaurants::*;
use std::time::{Duration, SystemTime};

use super::SimulationOutput;

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
            demand_calculator: DemandCalculator{time_before_next_customer: Duration::ZERO},
            order_processor: OrderProcessor{current_order: None, time_before_done: Duration::from_secs(30)},
        }
    }
}

pub struct DemandCalculator {
    time_before_next_customer: Duration,
}

impl DemandCalculator {
    // "tick" API must be respected, implementation should be transparent to caller (simulation)
    pub fn tick(&mut self, restaurant: &Restaurant, duration: Duration) -> Option<Order> {
        let time_between_customers = Duration::from_secs(60);
        
        self.time_before_next_customer = self.time_before_next_customer.saturating_sub(duration);
        if self.time_before_next_customer == Duration::ZERO {
            self.time_before_next_customer = time_between_customers;

            let picked: Option<(&Ramen, f64)> = restaurant.menu.get_one();
            match picked {
                Some(ramen_and_price) => {
                    let order = Order{ramen: (ramen_and_price.0).clone(), price:ramen_and_price.1};
                    return Some(order);
                },
                None => return None
            }
        }
        None
    }
}

pub struct OrderProcessor {
    current_order: Option<Order>,
    time_before_done: Duration,
}

impl OrderProcessor {
    pub fn receive_order(&mut self, restaurant: &mut Restaurant, order: Order) {
        restaurant.placed_orders.place_order(order);
    }

    pub fn tick(&mut self, restaurant: &mut Restaurant, output: &mut SimulationOutput, duration: Duration) {
        let time_to_cook = Duration::from_secs(30);

        if self.current_order.is_some() {
            self.time_before_done = self.time_before_done.saturating_sub(duration);
        }
        else {
            self.current_order = restaurant.placed_orders.pop_first();
        }

        let finished = if self.time_before_done == Duration::ZERO {
            self.time_before_done = time_to_cook;
            self.current_order.take()
        }
        else {
            None
        };

        if let Some(finished) = finished {
            restaurant.cash += finished.price;
            output.earnings += finished.price;
            output.ramen_served += 1;
        }
    }
}
