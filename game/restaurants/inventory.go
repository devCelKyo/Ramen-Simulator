package restaurants

import "errors"

type Inventory struct {
	stocks map[Ingredient]int
}

func createInventory() Inventory {
	inv := Inventory{}
	inv.stocks = make(map[Ingredient]int)
	return inv
}

func (inv Inventory) getQuantity(ingredient Ingredient) int {
	return inv.stocks[ingredient]
}

func (inv *Inventory) addQuantity(ingredient Ingredient, quantity int) {
	current := inv.getQuantity(ingredient)
	inv.stocks[ingredient] = current + quantity
}

func (inv Inventory) canCook(receipe map[Ingredient]int) bool {
	for ingredient, quantity := range receipe {
		if inv.getQuantity(ingredient) < quantity {
			return false
		}
	}
	return true
}

func (inv *Inventory) withdraw(receipe map[Ingredient]int) error {
	if !inv.canCook(receipe) {
		return errors.New("Not enough ingredients")
	}

	for ingredient, quantity := range receipe {
		inv.addQuantity(ingredient, -quantity)
	}
	return nil
}
