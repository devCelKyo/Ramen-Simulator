use std::time::{SystemTime, Duration};
use game::simulation::SimulationEngine;

mod catalog;

#[test]
fn basic_simulation() {
    let mut sim = SimulationEngine::new();
    let restaurant = catalog::basic_restaurant();
    let key = restaurant.id;
    sim.push_restaurant(restaurant);
    let mut rest = sim.seek_restaurant(key).unwrap();
    let cash = rest.cash.clone();    
    sim.simulate(key, SystemTime::now().checked_add(Duration::from_secs(180)).unwrap()); // We expect to serve at least 2 orders in that interval
    let cashafter = rest.cash.clone();
}