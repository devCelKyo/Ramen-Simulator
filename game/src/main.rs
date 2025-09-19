use std::collections::HashMap;

fn main() {
    println!("Welcome to Ramen Simulator!");
}

struct Restaurant {
    name: String,
    cash: f64,
}

enum IngredientType {
    Broth,
    Noodles,
    Protein,
    Vegetable,
}

struct Ingredient {
    name: String,
    ing_type: IngredientType,
}

struct Inventory {
    stocks: HashMap<Ingredient, i32>,
}

struct Receipe {
    broth: Ingredient,
    noodles: Ingredient,
    proteins: Vec<Ingredient>,
    vegetables: Vec<Ingredient>,
}
