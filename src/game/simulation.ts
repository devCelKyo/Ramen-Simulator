import { addMoney, type Cents } from "./money.js";
import { Order, Restaurant, type RestaurantKey } from "./restaurants.js";

export type Clock = () => number;
export const TICK_MS = 5_000;
const CUSTOMER_INTERVAL_MS = 60_000;
const COOKING_TIME_MS = 30_000;

export interface SimulationOutput {
  readonly restaurant: RestaurantKey;
  earningsCents: Cents;
  ramenServed: number;
}

export class SimulationError extends Error {
  constructor(public readonly code: "RestaurantNotFound" | "InvalidDuration") {
    super(code);
    this.name = "SimulationError";
  }
}

export class DemandCalculator {
  private timeBeforeNextCustomerMs = 0;

  tick(restaurant: Restaurant, durationMs: number): Order | undefined {
    this.timeBeforeNextCustomerMs = Math.max(0, this.timeBeforeNextCustomerMs - durationMs);
    if (this.timeBeforeNextCustomerMs !== 0) return undefined;
    this.timeBeforeNextCustomerMs = CUSTOMER_INTERVAL_MS;
    const item = restaurant.menu.getOne();
    return item ? new Order(item.ramen, item.priceCents) : undefined;
  }
}

export class OrderProcessor {
  public currentOrder: Order | undefined;
  private timeBeforeDoneMs = COOKING_TIME_MS;

  receiveOrder(restaurant: Restaurant, order: Order): void {
    restaurant.placedOrders.placeOrder(order);
  }

  tick(restaurant: Restaurant, output: SimulationOutput, durationMs: number): void {
    if (this.currentOrder) {
      const remaining = Math.max(0, this.timeBeforeDoneMs - durationMs);
      if (remaining > 0) {
        this.timeBeforeDoneMs = remaining;
        return;
      }
      // Validate both additions before mutating the completed order's accounting.
      const earnings = addMoney(output.earningsCents, this.currentOrder.priceCents);
      restaurant.credit(this.currentOrder.priceCents);
      output.earningsCents = earnings;
      output.ramenServed += 1;
      this.currentOrder = undefined;
      this.timeBeforeDoneMs = COOKING_TIME_MS;
      return;
    }
    const next = restaurant.placedOrders.peek();
    if (next && restaurant.stocks.withdrawRecipe(next.recipe)) {
      this.currentOrder = restaurant.placedOrders.popFirst();
    }
  }
}

export class RestaurantEngine {
  public readonly demandCalculator = new DemandCalculator();
  public readonly orderProcessor = new OrderProcessor();

  constructor(public readonly restaurant: Restaurant, public updatedAtMs: number) {}
}

export class SimulationEngine {
  private readonly restaurants = new Map<RestaurantKey, RestaurantEngine>();

  constructor(private readonly clock: Clock = Date.now) {}

  pushRestaurant(restaurant: Restaurant): void {
    const time = this.clock();
    if (!Number.isSafeInteger(time) || time < 0) throw new SimulationError("InvalidDuration");
    this.restaurants.set(restaurant.id, new RestaurantEngine(restaurant, time));
  }

  seekRestaurant(key: RestaurantKey): Restaurant | undefined {
    return this.restaurants.get(key)?.restaurant;
  }

  seekRestaurantEngine(key: RestaurantKey): RestaurantEngine | undefined {
    return this.restaurants.get(key);
  }

  simulate(key: RestaurantKey, timeMs: number = this.clock()): SimulationOutput {
    const engine = this.restaurants.get(key);
    if (!engine) throw new SimulationError("RestaurantNotFound");
    const durationMs = timeMs - engine.updatedAtMs;
    if (!Number.isSafeInteger(timeMs) || !Number.isSafeInteger(durationMs) || durationMs < TICK_MS) {
      throw new SimulationError("InvalidDuration");
    }
    const steps = Math.floor(durationMs / TICK_MS);
    const output: SimulationOutput = { restaurant: key, earningsCents: 0, ramenServed: 0 };
    for (let tick = 0; tick < steps; tick += 1) {
      const order = engine.demandCalculator.tick(engine.restaurant, TICK_MS);
      if (order) engine.orderProcessor.receiveOrder(engine.restaurant, order);
      engine.orderProcessor.tick(engine.restaurant, output, TICK_MS);
    }
    // Retain sub-tick elapsed time for the next simulation call.
    engine.updatedAtMs += steps * TICK_MS;
    return output;
  }
}
