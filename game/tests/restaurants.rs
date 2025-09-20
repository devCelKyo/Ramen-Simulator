use game::restaurants::Restaurant;

#[test]
fn init_restaurant() {
    let rest = Restaurant::new("TEST");
    assert_eq!(rest.name, "TEST");
    assert_eq!(rest.cash, 0.);
    assert!(rest.menu.prices.is_empty());
    assert!(rest.stocks.stocks.is_empty());
}