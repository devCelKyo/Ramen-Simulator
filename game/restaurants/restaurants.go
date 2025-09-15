package restaurants

import "fmt"

type Restaurant struct {
	name string
	cash float64
}

type Ramen struct {
	name    string
	receipe []Ingredient
}

func CreateRamen(name string, receipe []Ingredient) Ramen {
	return Ramen{name, receipe}
}

type Ingredient struct {
	name string
	cost float64
}

func GetProductionCost(ramen Ramen) float64 {
	sum := 0.
	for _, ing := range ramen.receipe {
		fmt.Println(ing)
		sum += ing.cost
	}
	return sum
}
