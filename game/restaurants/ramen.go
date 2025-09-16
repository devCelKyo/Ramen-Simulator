package restaurants

type Ingredient struct {
	name string
	cost float64
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
		sum += ing.cost
	}
	return sum
}

type Menu struct {
	ramenCosts map[string]float32 // only ramen name is kept in the map
}

func (menu Menu) getCost(ramen Ramen) float32 {
	return menu.ramenCosts[ramen.name]
}
