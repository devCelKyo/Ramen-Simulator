import assert from "node:assert/strict";
import { test } from "node:test";
import { Restaurant, SimulationEngine, SimulationError } from "../src/game/index.js";
import * as catalog from "./catalog/index.js";

const START = 1_700_000_000_000;
function setup(): { simulation: SimulationEngine; restaurant: Restaurant } {
  const simulation = new SimulationEngine(() => START);
  const restaurant = catalog.basicRestaurant();
  simulation.pushRestaurant(restaurant);
  return { simulation, restaurant };
}

test("catalog integration: 300 seconds of service increases cash by reported earnings", () => {
  const { simulation, restaurant } = setup();
  const before = restaurant.cashCents;
  const output = simulation.simulate(restaurant.id, START + 300_000);
  assert.ok(restaurant.cashCents > before);
  assert.equal(restaurant.cashCents - before, output.earningsCents);
  assert.ok(output.ramenServed >= 2);
  assert.equal(output.earningsCents, output.ramenServed * 1_000);
  assert.equal(simulation.seekRestaurant(restaurant.id), restaurant);
});

test("catalog integration: stock consumed includes served ramen and the order being cooked", () => {
  const { simulation, restaurant } = setup();
  const output = simulation.simulate(restaurant.id, START + 500_000);
  const cooking = simulation.seekRestaurantEngine(restaurant.id)?.orderProcessor.currentOrder ? 1 : 0;
  for (const ingredient of catalog.ingredients()) {
    assert.equal(10 - restaurant.stocks.get(ingredient), output.ramenServed + cooking);
  }
});

test("catalog integration: stock exhaustion serves exactly ten bowls without negative stock", () => {
  const { simulation, restaurant } = setup();
  const output = simulation.simulate(restaurant.id, START + 1_000_000);
  for (const ingredient of catalog.ingredients()) assert.equal(restaurant.stocks.get(ingredient), 0);
  assert.equal(output.ramenServed, 10);
  assert.equal(output.earningsCents, 10_000);
  assert.equal(restaurant.cashCents, 110_000);
  const later = simulation.simulate(restaurant.id, START + 2_000_000);
  assert.equal(later.ramenServed, 0);
  assert.equal(later.earningsCents, 0);
});

test("split simulation calls equal one elapsed-time simulation", () => {
  const one = setup();
  const split = setup();
  const expected = one.simulation.simulate(0, START + 500_000);
  const first = split.simulation.simulate(0, START + 300_000);
  const second = split.simulation.simulate(0, START + 500_000);
  assert.equal(first.ramenServed + second.ramenServed, expected.ramenServed);
  assert.equal(first.earningsCents + second.earningsCents, expected.earningsCents);
  assert.equal(one.restaurant.cashCents, split.restaurant.cashCents);
  for (const ingredient of catalog.ingredients()) {
    assert.equal(one.restaurant.stocks.get(ingredient), split.restaurant.stocks.get(ingredient));
  }
});

test("sub-tick remainder is retained and elapsed time cannot be paid twice", () => {
  const { simulation, restaurant } = setup();
  assert.equal(simulation.simulate(0, START + 7_000).ramenServed, 0);
  assert.equal(simulation.seekRestaurantEngine(0)?.updatedAtMs, START + 5_000);
  assert.throws(() => simulation.simulate(0, START + 7_000), SimulationError);
  assert.equal(simulation.simulate(0, START + 35_000).ramenServed, 1);
  const cash = restaurant.cashCents;
  assert.throws(() => simulation.simulate(0, START + 35_000), SimulationError);
  assert.equal(restaurant.cashCents, cash);
});

test("unknown restaurants and invalid times fail before changing state", () => {
  const { simulation, restaurant } = setup();
  assert.throws(() => simulation.simulate(99, START + 5_000), { code: "RestaurantNotFound" });
  for (const time of [START - 1, START, START + 4_999, NaN, Infinity, START + 5_000.5]) {
    assert.throws(() => simulation.simulate(0, time), { code: "InvalidDuration" });
  }
  assert.equal(restaurant.cashCents, 100_000);
  assert.equal(restaurant.stocks.get(catalog.miso()), 10);
  assert.equal(simulation.seekRestaurant(99), undefined);
});

test("empty menu produces no orders or earnings", () => {
  const simulation = new SimulationEngine(() => START);
  const restaurant = Restaurant.empty("Empty");
  simulation.pushRestaurant(restaurant);
  assert.equal(simulation.simulate(0, START + 300_000).ramenServed, 0);
  assert.ok(restaurant.placedOrders.isEmpty());
  assert.equal(restaurant.cashCents, 0);
});

test("restaurants have independent simulation state", () => {
  const simulation = new SimulationEngine(() => START);
  const first = catalog.basicRestaurant(1);
  const second = catalog.basicRestaurant(2);
  simulation.pushRestaurant(first);
  simulation.pushRestaurant(second);
  simulation.simulate(1, START + 300_000);
  assert.equal(second.cashCents, 100_000);
  assert.equal(second.stocks.get(catalog.miso()), 10);
  assert.equal(simulation.seekRestaurantEngine(2)?.updatedAtMs, START);
});

test("simulation can use an injected clock without wall-clock waiting", () => {
  let now = START;
  const simulation = new SimulationEngine(() => now);
  simulation.pushRestaurant(catalog.basicRestaurant());
  now += 300_000;
  assert.ok(simulation.simulate(0).ramenServed >= 2);
});
