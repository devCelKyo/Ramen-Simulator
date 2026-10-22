import assert from "node:assert/strict";
import { mkdtempSync, rmSync } from "node:fs";
import { tmpdir } from "node:os";
import { join } from "node:path";
import { test } from "node:test";
import { RestaurantStore } from "../src/storage/restaurants.js";
import { basicRestaurant } from "./catalog/index.js";

test("persistence port: initializes an in-memory database", (t) => {
  const store = new RestaurantStore();
  t.after(() => store.close());
  assert.equal(store.load(999), undefined);
});

test("persistence port: insert and load retain restaurant ID, name and cash", (t) => {
  const store = new RestaurantStore();
  t.after(() => store.close());
  const restaurant = basicRestaurant();
  restaurant.id = store.insert(restaurant);
  const loaded = store.load(restaurant.id);
  assert.ok(loaded);
  assert.equal(loaded.id, restaurant.id);
  assert.equal(loaded.name, restaurant.name);
  assert.equal(loaded.cashCents, restaurant.cashCents);
  // Explicit parity with the Rust adapter's limited persistence contract.
  assert.ok(loaded.menu.isEmpty());
  assert.ok(loaded.stocks.isEmpty());
});

test("saving updates an existing header using bound values", (t) => {
  const store = new RestaurantStore();
  t.after(() => store.close());
  const restaurant = basicRestaurant();
  restaurant.id = store.insert(restaurant);
  restaurant.name = "Chef's ramen; DROP TABLE restaurants;";
  restaurant.credit(1_000);
  store.save(restaurant);
  assert.equal(store.load(restaurant.id)?.name, restaurant.name);
  assert.equal(store.load(restaurant.id)?.cashCents, 101_000);
  assert.ok(store.insert(basicRestaurant()) > restaurant.id);
});

test("saving a missing restaurant reports failure", (t) => {
  const store = new RestaurantStore();
  t.after(() => store.close());
  assert.throws(() => store.save(basicRestaurant()), /not found/);
});

test("file database reopens and retains a trillion-dollar balance exactly", (t) => {
  const directory = mkdtempSync(join(tmpdir(), "ramen-storage-"));
  t.after(() => rmSync(directory, { recursive: true, force: true }));
  const path = join(directory, "test.sqlite");
  const first = new RestaurantStore(path);
  const restaurant = basicRestaurant();
  restaurant.cashCents = 100_000_000_000_000;
  let id: number;
  try { id = first.insert(restaurant); } finally { first.close(); }
  const reopened = new RestaurantStore(path);
  t.after(() => reopened.close());
  assert.equal(reopened.load(id)?.cashCents, 100_000_000_000_000);
});
