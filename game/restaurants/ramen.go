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

func MakeReceipe() Receipe {
	return Receipe{}
}

func (receipe *Receipe) withBroth(broth Ingredient) *Receipe {
	if broth.ingType == Broth {
		receipe.broth = broth
	}
	return receipe
}

func (receipe *Receipe) withNoodles(noodles Ingredient) *Receipe {
	if noodles.ingType == Noodles {
		receipe.noodles = noodles
	}
	return receipe
}

func (receipe *Receipe) withProtein(protein Ingredient) *Receipe {
	if protein.ingType == Protein {
		receipe.protein = append(receipe.protein, protein)
	}
	return receipe
}

func (receipe *Receipe) withVegetable(vegetable Ingredient) *Receipe {
	if vegetable.ingType == Vegetable {
		receipe.vegetable = append(receipe.vegetable, vegetable)
	}
	return receipe
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
