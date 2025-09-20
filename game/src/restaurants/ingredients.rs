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
    pub broth: Option<Ingredient>,
    pub noodles: Option<Ingredient>,
    pub proteins: Option<Vec<Ingredient>>,
    pub vegetables: Option<Vec<Ingredient>>,
}

impl Receipe {
    fn is_valid(&self) -> bool {
        if !self.broth.as_ref().map_or(false, |b| b.ing_type == IngredientType::Broth) {
            return false;
        }
        if !self.noodles.as_ref().map_or(false, |n| n.ing_type == IngredientType::Noodles) {
            return false;
        }
        if !self.proteins.as_ref().map_or(false, |v| v.iter().all(|i| i.ing_type == IngredientType::Protein)) {
            return false;
        }
        if !self.vegetables.as_ref().map_or(false, |v| v.iter().all(|i| i.ing_type == IngredientType::Vegetable)) {
            return false;
        }
        true
    }
}
