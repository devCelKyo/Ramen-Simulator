use std::time::{SystemTime, Duration};
use game::simulation::SimulationEngine;

mod catalog;

#[test]
fn basic_simulation() {
    let mut sim = SimulationEngine::new();
    let restaurant = catalog::basic_restaurant();
    
    let cash_start = restaurant.cash;
    let key = restaurant.id;
    
    sim.push_restaurant(restaurant);
    let output = sim.simulate(key, SystemTime::now().checked_add(Duration::from_secs(300)).unwrap()).unwrap(); // We expect to serve at least 2 orders in that interval
    let cash_after = sim.seek_restaurant(key).unwrap().cash;

    assert!(cash_after > cash_start);
    assert_eq!(cash_after - cash_start, output.earnings);
    
    assert!(2 <= output.ramen_served);
}