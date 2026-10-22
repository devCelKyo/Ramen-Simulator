import { DatabaseSync } from "node:sqlite";
import { cents, Restaurant, type RestaurantKey } from "../game/index.js";

/** Ports the Rust prototype's name/balance persistence. Menus, stock and simulation state are not saved. */
export class RestaurantStore {
  private readonly db: DatabaseSync;

  constructor(path = ":memory:") {
    this.db = new DatabaseSync(path);
    this.db.exec(`
      CREATE TABLE IF NOT EXISTS restaurants (
        id INTEGER PRIMARY KEY,
        name TEXT NOT NULL,
        cash_cents INTEGER NOT NULL CHECK(cash_cents >= 0 AND cash_cents <= 9007199254740991)
      ) STRICT
    `);
  }

  insert(restaurant: Restaurant): RestaurantKey {
    const result = this.db.prepare("INSERT INTO restaurants (name, cash_cents) VALUES (?, ?)")
      .run(restaurant.name, cents(restaurant.cashCents));
    const id = Number(result.lastInsertRowid);
    if (!Number.isSafeInteger(id)) throw new RangeError("Restaurant ID exceeds the safe integer range.");
    return id;
  }

  save(restaurant: Restaurant): void {
    const result = this.db.prepare("UPDATE restaurants SET name = ?, cash_cents = ? WHERE id = ?")
      .run(restaurant.name, cents(restaurant.cashCents), restaurant.id);
    if (result.changes === 0) throw new Error("Restaurant not found.");
  }

  load(id: RestaurantKey): Restaurant | undefined {
    const row = this.db.prepare("SELECT id, name, cash_cents FROM restaurants WHERE id = ?").get(id);
    if (!row) return undefined;
    if (typeof row.id !== "number" || typeof row.name !== "string" || typeof row.cash_cents !== "number") {
      throw new Error("Invalid persisted restaurant.");
    }
    return new Restaurant(row.id, row.name, cents(row.cash_cents));
  }

  close(): void { this.db.close(); }
}
