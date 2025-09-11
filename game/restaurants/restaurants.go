package restaurants

import "fmt"

type Restaurant struct {
	name string
}

type Ramen struct {
	name string
	receipe []Ingredient
}

func CreateRamen(name string) Ramen {
	return Ramen{name} // fix
}
type Ingredient struct {
	name string
	cost float64
}

func GetProductionCost(ramen Ramen) {
	var ing1 = Ingredient{"miso", 2.5}
	var ing2 = Ingredient{"udon", 5}
	var receipe = []Ingredient{ing1, ing2}
	fmt.Println(receipe)
	// figure out loops and array ffs
	for (int i = 0; i < receipe.size(); ++i) {
		fmt.Println(ing)
	}
	return 0 // change when less stupid
}

