use game::restaurants::Restaurant;

mod catalog;

#[test]
fn init_empty_restaurant() {
    let rest = Restaurant::default("TEST");
    assert_eq!(rest.name, "TEST");
    assert_eq!(rest.cash, 0.);
    assert!(rest.menu.is_empty());
    assert!(rest.stocks.is_empty());
}

#[test]
fn init_basic_ramen() {
    let ramen = catalog::basic_ramen();
    assert!(ramen.receipe.is_valid());
}

#[test]
fn init_basic_restaurant() {
    let restaurant = catalog::basic_restaurant();
    assert!(!restaurant.stocks.is_empty());

    let ramen = catalog::basic_ramen();
    assert!(restaurant.stocks.can_cook(&ramen.receipe));

    assert!(!restaurant.menu.is_empty());

    let expected_ramen_price = 10.;
    assert_eq!(restaurant.menu.get_price_from_name(&ramen.name).unwrap(), expected_ramen_price);
    assert_eq!(restaurant.menu.get_price_from_ramen(&ramen).unwrap(), expected_ramen_price);
}