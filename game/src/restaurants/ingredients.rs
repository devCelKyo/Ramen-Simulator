use std::collections::HashMap;

#[derive(PartialEq)]
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

pub struct Receipe {
    broth: Ingredient,
    noodles: Ingredient,
    proteins: Vec<Ingredient>,
    vegetables: Vec<Ingredient>,
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
