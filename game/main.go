package main

import (
	"fmt"
	"game/restaurants"
)

func main() {
	fmt.Println("Welcome to Ramen-Simulator II")
	ramen := restaurants.MISO_RAMEN
	cost := restaurants.GetProductionCost(ramen)
	fmt.Println(cost)
}

