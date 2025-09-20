use game::restaurants::*;

fn miso() -> Ingredient {
    Ingredient::new("Miso", IngredientType::Broth)
}

fn receipe() -> Receipe {
    Receipe::new().with_broth(miso())
}

fn ramen() -> Ramen {
    Ramen::new("miso");
}