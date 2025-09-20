use game::restaurants::*;

fn miso() -> Ingredient {
    Ingredient::new("Miso", IngredientType::Broth)
}

fn chinese_noodles() -> Ingredient {
    Ingredient::new("Chinese noodles", IngredientType::Noodles)
}

fn ground_beef() -> Ingredient {
    Ingredient::new("Ground beef", IngredientType::Protein)
}

fn seaweed() -> Ingredient {
    Ingredient::new("Seaweed", IngredientType::Vegetable)
}

fn receipe() -> Result<Receipe, RecipeError> {
    Ok(Receipe::new()
    .with_broth(miso())?
    .with_noodles(chinese_noodles())?
    .with_protein(ground_beef())?
    .with_vegetable(seaweed())
    .expect("Incomplete receipe"))
}

pub fn ramen() -> Ramen {
    Ramen::new("miso", receipe().unwrap())
}