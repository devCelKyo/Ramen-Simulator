import assert from "node:assert/strict";
import { test } from "node:test";
import { addMoney, cents } from "../src/game/index.js";
import { basicRestaurant } from "./catalog/index.js";

test("integer cents preserve a trillion dollars, a single cent, and JSON round trips", () => {
  const amount = addMoney(cents(100_000_000_000_000), 1);
  assert.equal(amount, 100_000_000_000_001);
  const parsed: unknown = JSON.parse(JSON.stringify(amount));
  assert.equal(parsed, amount);
});

test("money rejects fractional, negative, non-finite and unsafe values", () => {
  for (const value of [-1, 0.1, NaN, Infinity, Number.MAX_SAFE_INTEGER + 1]) {
    assert.throws(() => cents(value), RangeError);
  }
});

test("a rejected credit leaves the balance unchanged", () => {
  const restaurant = basicRestaurant();
  restaurant.cashCents = Number.MAX_SAFE_INTEGER;
  assert.throws(() => restaurant.credit(1), RangeError);
  assert.equal(restaurant.cashCents, Number.MAX_SAFE_INTEGER);
});
