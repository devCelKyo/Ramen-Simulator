import { Inventory, Recipe } from "./ingredients.js";
import { addMoney, cents, type Cents } from "./money.js";

export class Ramen {
  constructor(public readonly name: string, public readonly recipe: Recipe) {}
}

export interface MenuItem {
  readonly ramen: Ramen;
  readonly priceCents: Cents;
}

export class Menu {
  private readonly items = new Map<string, MenuItem>();

  isEmpty(): boolean {
    return this.items.size === 0;
  }

  /** A duplicate name leaves the original dish and price unchanged. */
  pushRamen(ramen: Ramen, priceCents: Cents): this {
    const price = cents(priceCents);
    if (!this.items.has(ramen.name)) {
      this.items.set(ramen.name, { ramen, priceCents: price });
    }
    return this;
  }

  getPriceFromName(name: string): Cents | undefined {
    return this.items.get(name)?.priceCents;
  }

  getPriceFromRamen(ramen: Ramen): Cents | undefined {
    return this.getPriceFromName(ramen.name);
  }

  /** Deterministic menu selection for the prototype's simple demand model. */
  getOne(): MenuItem | undefined {
    return this.items.values().next().value;
  }
}

export class Order {
  public readonly priceCents: Cents;

  constructor(public readonly ramen: Ramen, priceCents: Cents) {
    this.priceCents = cents(priceCents);
  }

  get recipe(): Recipe {
    return this.ramen.recipe;
  }
}

export class OrderQueue {
  private readonly orders: Order[] = [];

  isEmpty(): boolean { return this.orders.length === 0; }
  get size(): number { return this.orders.length; }
  peek(): Order | undefined { return this.orders[0]; }
  popFirst(): Order | undefined { return this.orders.shift(); }
  placeOrder(order: Order): void { this.orders.push(order); }
}

export type RestaurantKey = number;

export class Restaurant {
  private balanceCents: Cents;
  public readonly placedOrders = new OrderQueue();

  constructor(
    public id: RestaurantKey,
    public name: string,
    cashCents: Cents,
    public readonly menu: Menu = new Menu(),
    public readonly stocks: Inventory = new Inventory(),
  ) {
    this.balanceCents = cents(cashCents);
  }

  static empty(name: string): Restaurant {
    return new Restaurant(0, name, 0);
  }

  get cashCents(): Cents { return this.balanceCents; }
  set cashCents(value: Cents) { this.balanceCents = cents(value); }

  credit(amountCents: Cents): void {
    this.balanceCents = addMoney(this.balanceCents, amountCents);
  }
}
