use game::restaurants::Restaurant;

mod catalog;

#[test]
fn init_restaurant() {
    let rest = Restaurant::default("TEST");
    assert_eq!(rest.name, "TEST");
    assert_eq!(rest.cash, 0.);
    assert!(rest.menu.prices.is_empty());
    assert!(rest.stocks.stocks.is_empty());
}

#[test]
fn init_basic_ramen() {
    let ramen = catalog::ramen();
    assert!(ramen.receipe.is_valid());
}