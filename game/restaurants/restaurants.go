package restaurants

type Restaurant struct {
	name      string
	cash      float32
	inventory Inventory
	menu      Menu
}

func (rest *Restaurant) giveMoney(money float32) {
	rest.cash += money
}

func (rest *Restaurant) cook(ramen Ramen) {
	price := rest.menu.getPrice(ramen)
	revenue := price - ramen.getRawProductionCost()
	rest.inventory.withdraw(ramen.receipe)
	rest.giveMoney(revenue)
}
