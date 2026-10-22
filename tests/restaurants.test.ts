import assert from "node:assert/strict";
import { test } from "node:test";
import { IngredientType, Inventory, Menu, Order, OrderQueue, Recipe, RecipeError, Restaurant } from "../src/game/index.js";
import * as catalog from "./catalog/index.js";

test("catalog: empty restaurant starts with zero cash, empty menu and stocks", () => {
  const restaurant = Restaurant.empty("TEST");
  assert.equal(restaurant.name, "TEST");
  assert.equal(restaurant.cashCents, 0);
  assert.ok(restaurant.menu.isEmpty());
  assert.ok(restaurant.stocks.isEmpty());
});

test("catalog: basic ramen recipe is valid", () => {
  assert.ok(catalog.basicRamen().recipe.isValid());
  assert.equal(new Recipe().isValid(), false);
});

test("catalog: stocked restaurant can cook its menu at the expected price", () => {
  const restaurant = catalog.basicRestaurant();
  const ramen = catalog.basicRamen();
  assert.equal(restaurant.cashCents, 100_000);
  assert.equal(restaurant.stocks.isEmpty(), false);
  assert.ok(restaurant.stocks.canCook(ramen.recipe));
  assert.equal(restaurant.menu.isEmpty(), false);
  assert.equal(restaurant.menu.getPriceFromName(ramen.name), 1_000);
  assert.equal(restaurant.menu.getPriceFromRamen(ramen), 1_000);
});

test("recipe rejects ingredients in the wrong category", () => {
  assert.throws(() => new Recipe().withBroth(catalog.chineseNoodles()), RecipeError);
  assert.throws(() => new Recipe().withNoodles(catalog.miso()), RecipeError);
  assert.throws(() => new Recipe().withProtein(catalog.seaweed()), RecipeError);
  assert.throws(() => new Recipe().withVegetable(catalog.groundBeef()), RecipeError);
  assert.equal(catalog.miso().type, IngredientType.Broth);
});

test("inventory uses ingredient values rather than object identity", () => {
  const inventory = new Inventory().add(catalog.miso(), 2);
  assert.equal(inventory.get(catalog.miso()), 2);
  assert.equal(inventory.get(catalog.seaweed()), 0);
});

test("insufficient ingredients leave all stock unchanged, including duplicate ingredients", () => {
  const recipe = catalog.basicRecipe().withProtein(catalog.groundBeef());
  const inventory = new Inventory().add(catalog.miso(), 1).add(catalog.chineseNoodles(), 1)
    .add(catalog.groundBeef(), 1).add(catalog.seaweed(), 1);
  assert.equal(inventory.canCook(recipe), false);
  assert.equal(inventory.withdrawRecipe(recipe), false);
  for (const ingredient of catalog.ingredients()) assert.equal(inventory.get(ingredient), 1);
  inventory.add(catalog.groundBeef(), 1);
  assert.ok(inventory.withdrawRecipe(recipe));
  for (const ingredient of catalog.ingredients()) assert.equal(inventory.get(ingredient), 0);
  assert.equal(inventory.canCook(new Recipe()), false);
});

test("menu preserves original price when the same ramen name is added twice", () => {
  const menu = new Menu().pushRamen(catalog.basicRamen(), 1_000).pushRamen(catalog.basicRamen(), 2_000);
  assert.equal(menu.getPriceFromName("miso"), 1_000);
  assert.equal(menu.getOne()?.priceCents, 1_000);
  assert.equal(menu.getPriceFromName("absent"), undefined);
});

test("orders are served in insertion order", () => {
  const queue = new OrderQueue();
  const first = new Order(catalog.basicRamen(), 1_000);
  const second = new Order(catalog.basicRamen(), 2_000);
  queue.placeOrder(first);
  queue.placeOrder(second);
  assert.equal(queue.size, 2);
  assert.equal(queue.peek(), first);
  assert.equal(queue.popFirst(), first);
  assert.equal(queue.popFirst(), second);
  assert.ok(queue.isEmpty());
  assert.equal(queue.popFirst(), undefined);
});

test("inventory rejects negative, fractional and unsafe quantities", () => {
  const inventory = catalog.basicInventory();
  for (const invalid of [-1, 0.5, NaN, Infinity, Number.MAX_SAFE_INTEGER]) {
    assert.throws(() => inventory.add(catalog.miso(), invalid), RangeError);
    assert.equal(inventory.get(catalog.miso()), 10);
  }
});
