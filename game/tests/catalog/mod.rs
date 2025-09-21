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

fn basic_receipe() -> Result<Receipe, RecipeError> {
    Ok(Receipe::new()
    .with_broth(miso())?
    .with_noodles(chinese_noodles())?
    .with_protein(ground_beef())?
    .with_vegetable(seaweed())
    .expect("Incomplete receipe"))
}

pub fn basic_ramen() -> Ramen {
    Ramen::new("miso", basic_receipe().unwrap())
}

fn inventory() -> Inventory {
    Inventory::new()
    .add(&miso(), 10)
    .add(&chinese_noodles(), 10)
    .add(&ground_beef(), 10)
    .add(&seaweed(), 10)
}

pub fn basic_restaurant() -> Restaurant {
    Restaurant::new("Test Restaurant", 1000., Menu::default(), inventory())
}