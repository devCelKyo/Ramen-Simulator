use std::time::{Duration, SystemTime};
use game::simulation::SimulationEngine;

mod catalog;

#[test]
fn simulation_money() {
    let mut sim = SimulationEngine::new();
    let restaurant = catalog::basic_restaurant();
    
    let cash_start = restaurant.cash;
    let key = restaurant.id;
    
    sim.push_restaurant(restaurant);
    let output = sim.simulate(key, SystemTime::now().checked_add(Duration::from_secs(300)).unwrap()).unwrap(); // We expect to serve at least 2 orders in that interval
    
    let restaurant = sim.seek_restaurant(key).unwrap();
    let cash_after = restaurant.cash;

    assert!(cash_after > cash_start);
    assert_eq!(cash_after - cash_start, output.earnings);
    
    assert!(2 <= output.ramen_served);
}

#[test]
fn simulation_stocks() {
    let mut sim = SimulationEngine::new();
    let restaurant = catalog::basic_restaurant();

    let key = restaurant.id;
    sim.push_restaurant(restaurant);
    let output = sim.simulate(key, SystemTime::now().checked_add(Duration::from_secs(500)).unwrap()).unwrap();

    let stocks_before = catalog::basic_inventory();
    let stocks_after = &sim.seek_restaurant(key).unwrap().stocks;

    let miso = catalog::miso();
    let ramen_being_cooked = match sim.seek_restaurant_engine(key).unwrap().order_processor.current_order.is_some() {
        true => 1,
        false => 0,
    };

    assert_eq!(stocks_before.get(&miso) - stocks_after.get(&miso), output.ramen_served + ramen_being_cooked);
}

#[test]
fn simulation_out_of_stocks() {
    let mut sim = SimulationEngine::new();
    let restaurant = catalog::basic_restaurant();

    let key = restaurant.id;
    sim.push_restaurant(restaurant);
    let output = sim.simulate(key, SystemTime::now().checked_add(Duration::from_secs(1000)).unwrap()).unwrap();

    let stocks_after = &sim.seek_restaurant(key).unwrap().stocks;

    assert_eq!(stocks_after.get(&catalog::miso()), 0);
    assert_eq!(stocks_after.get(&catalog::chinese_noodles()), 0);
    assert_eq!(stocks_after.get(&catalog::ground_beef()), 0);
    assert_eq!(stocks_after.get(&catalog::seaweed()), 0);
    assert_eq!(output.ramen_served, 10);
}