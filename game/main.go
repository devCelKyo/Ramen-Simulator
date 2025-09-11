package main

import (
	"fmt"
	"game/restaurants"
)

func main() {
	fmt.Println("Ramen-Simulator II")
	var ramen = restaurants.CreateRamen("hello")
	restaurants.GetProductionCost(ramen)
}