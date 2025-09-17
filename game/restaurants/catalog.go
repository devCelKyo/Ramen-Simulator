package restaurants

var MISO = Ingredient{"Miso", 0.5, Broth}
var UDON = Ingredient{"Udon", 1, Noodles}

var MISO_RAMEN_RECEIPE = MakeReceipe().withBroth(MISO).withNoodles(UDON)
var MISO_RAMEN = CreateRamen("Miso Ramen", MISO_RAMEN_RECEIPE)
