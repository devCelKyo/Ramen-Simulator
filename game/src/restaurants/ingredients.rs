use std::collections::HashMap;

#[derive(PartialEq)]
enum IngredientType {
    Broth,
    Noodles,
    Protein,
    Vegetable,
}

pub struct Ingredient {
    name: String,
    ing_type: IngredientType,
}

pub struct Inventory {
    pub stocks: HashMap<Ingredient, i32>,
}

impl Inventory {
    pub fn new() -> Self {
        Self {
            stocks: HashMap::new()
        }
    }
}

pub struct Receipe {
    pub broth: Ingredient,
    pub noodles: Ingredient,
    pub proteins: Vec<Ingredient>,
    pub vegetables: Vec<Ingredient>,
}

impl Receipe {
    fn is_valid(&self) -> bool {
        if self.broth.ing_type != IngredientType::Broth {
            return false;
        }
        if self.noodles.ing_type != IngredientType::Noodles {
            return false;
        }
        for ing in self.proteins.iter() {
            if ing.ing_type != IngredientType::Protein {
                return false;
            }
        }
        for ing in self.vegetables.iter() {
            if ing.ing_type != IngredientType::Vegetable {
                return false;
            }
        }
        return true;
    }
}
