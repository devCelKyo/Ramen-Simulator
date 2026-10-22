/** Every monetary value in the engine and storage is an integer number of cents. */
export type Cents = number;

export function cents(value: number): Cents {
  if (!Number.isSafeInteger(value) || value < 0) {
    throw new RangeError("Money must be a non-negative safe integer in cents.");
  }
  return value;
}

export function addMoney(left: Cents, right: Cents): Cents {
  return cents(cents(left) + cents(right));
}
