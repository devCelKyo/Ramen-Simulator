import { Ingredient, IngredientType, Inventory, Menu, Ramen, Recipe, Restaurant } from "../../src/game/index.js";

// Shared use-case fixtures ported from game/tests/catalog/mod.rs. All prices and cash are cents.
export const miso = (): Ingredient => new Ingredient("Miso", IngredientType.Broth);
export const chineseNoodles = (): Ingredient => new Ingredient("Chinese noodles", IngredientType.Noodles);
export const groundBeef = (): Ingredient => new Ingredient("Ground beef", IngredientType.Protein);
export const seaweed = (): Ingredient => new Ingredient("Seaweed", IngredientType.Vegetable);
export const ingredients = (): Ingredient[] => [miso(), chineseNoodles(), groundBeef(), seaweed()];

export function basicRecipe(): Recipe {
  return new Recipe().withBroth(miso()).withNoodles(chineseNoodles())
    .withProtein(groundBeef()).withVegetable(seaweed());
}

export const basicRamen = (): Ramen => new Ramen("miso", basicRecipe());
export const basicInventory = (): Inventory => new Inventory()
  .add(miso(), 10).add(chineseNoodles(), 10).add(groundBeef(), 10).add(seaweed(), 10);
export const basicMenu = (): Menu => new Menu().pushRamen(basicRamen(), 1_000);
export const basicRestaurant = (id = 0): Restaurant => new Restaurant(
  id, "Test Restaurant", 100_000, basicMenu(), basicInventory(),
);
