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
