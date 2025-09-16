package restaurants

import "fmt"

type Restaurant struct {
	name      string
	cash      float64
	inventory Inventory
}

type Ingredient struct {
	name string
	cost float64
}

type Inventory struct {
	stocks map[Ingredient]int
}

type Ramen struct {
	name    string
	receipe []Ingredient
}

func createRamen(name string, receipe []Ingredient) Ramen {
	return Ramen{name, receipe}
}

func (ramen Ramen) getRawProductionCost() float64 {
	sum := 0.
	for _, ing := range ramen.receipe {
		fmt.Println(ing)
		sum += ing.cost
	}
	return sum
}
