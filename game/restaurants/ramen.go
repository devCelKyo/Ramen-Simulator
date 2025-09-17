package restaurants

type IngredientType int

const (
	Broth IngredientType = iota
	Noodles
	Protein
	Vegetable
)

type Ingredient struct {
	name    string
	cost    float32
	ingType IngredientType
}

// Should be encapsulated in a "receipe" package to maintain invariants
type Receipe struct {
	broth     Ingredient
	noodles   Ingredient
	protein   []Ingredient
	vegetable []Ingredient
}

type Ramen struct {
	name    string
	receipe map[Ingredient]int
}

func createRamen(name string, receipe map[Ingredient]int) Ramen {
	return Ramen{name, receipe}
}

func (ramen Ramen) getRawProductionCost() float32 {
	var sum float32 = 0.
	for ing := range ramen.receipe {
		sum += ing.cost
	}
	return sum
}

type Menu struct {
	ramenPrices map[string]float32 // only ramen name is kept in the map
}

func (menu Menu) getPrice(ramen Ramen) float32 {
	return menu.ramenPrices[ramen.name]
}
