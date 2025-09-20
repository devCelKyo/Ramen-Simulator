use std::collections::HashMap;

#[derive(PartialEq, Debug)]
pub enum IngredientType {
    Broth,
    Noodles,
    Protein,
    Vegetable,
}

pub struct Ingredient {
    pub name: String,
    pub ing_type: IngredientType,
}

impl Ingredient {
    pub fn new(name: &str, ing_type: IngredientType) -> Self {
        Self {
            name: String::from(name),
            ing_type: ing_type,
        }
    }
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

#[derive(Default)]
pub struct Receipe {
    pub broth: Option<Ingredient>,
    pub noodles: Option<Ingredient>,
    pub proteins: Option<Vec<Ingredient>>,
    pub vegetables: Option<Vec<Ingredient>>,
}

#[derive(Debug)]
pub enum RecipeError {
    InvalidIngredient { expected: IngredientType, found: IngredientType },
}

impl Receipe {
    pub fn new() -> Self {
        Receipe::default()
    }

    pub fn is_valid(&self) -> bool {
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

    pub fn with_broth(mut self, ing: Ingredient) -> Result<Self, RecipeError> {
        if ing.ing_type != IngredientType::Broth {
            return Err(RecipeError::InvalidIngredient { expected: IngredientType::Broth, found: ing.ing_type });
        }
        self.broth = Some(ing);
        Ok(self)
    }

    pub fn with_noodles(mut self, ing: Ingredient) -> Result<Self, RecipeError> {
        if ing.ing_type != IngredientType::Noodles {
            return Err(RecipeError::InvalidIngredient { expected: IngredientType::Noodles, found: ing.ing_type });
        }
        self.noodles = Some(ing);
        Ok(self)
    }

    pub fn with_protein(mut self, ing: Ingredient) -> Result<Self, RecipeError> {
        if ing.ing_type != IngredientType::Protein {
            return Err(RecipeError::InvalidIngredient { expected: IngredientType::Protein, found: ing.ing_type });
        }
        match self.proteins {
            Some(ref mut vec) => vec.push(ing),
            None => {
                let mut vec = Vec::new();
                vec.push(ing);
                self.proteins = Some(vec);
            }
        }
        Ok(self)
    }

    pub fn with_vegetable(mut self, ing: Ingredient) -> Result<Self, RecipeError> {
        if ing.ing_type != IngredientType::Vegetable {
            return Err(RecipeError::InvalidIngredient { expected: IngredientType::Vegetable, found: ing.ing_type });
        }
        match self.vegetables {
            Some(ref mut vec) => vec.push(ing),
            None => {
                let mut vec = Vec::new();
                vec.push(ing);
                self.vegetables = Some(vec);
            }
        }
        Ok(self)
    }
}
