export enum IngredientType {
  Broth = "broth",
  Noodles = "noodles",
  Protein = "protein",
  Vegetable = "vegetable",
}

export class Ingredient {
  constructor(public readonly name: string, public readonly type: IngredientType) {}

  /** Match Rust's value-based ingredient identity, including separately built catalog items. */
  get key(): string {
    return JSON.stringify([this.type, this.name]);
  }
}

export class RecipeError extends Error {
  constructor(expected: IngredientType, found: IngredientType) {
    super(`Expected ${expected}, received ${found}.`);
    this.name = "RecipeError";
  }
}

export class Recipe {
  private broth: Ingredient | undefined;
  private noodles: Ingredient | undefined;
  private readonly proteins: Ingredient[] = [];
  private readonly vegetables: Ingredient[] = [];

  private validate(ingredient: Ingredient, expected: IngredientType): void {
    if (ingredient.type !== expected) throw new RecipeError(expected, ingredient.type);
  }

  withBroth(ingredient: Ingredient): this {
    this.validate(ingredient, IngredientType.Broth);
    this.broth = ingredient;
    return this;
  }

  withNoodles(ingredient: Ingredient): this {
    this.validate(ingredient, IngredientType.Noodles);
    this.noodles = ingredient;
    return this;
  }

  withProtein(ingredient: Ingredient): this {
    this.validate(ingredient, IngredientType.Protein);
    this.proteins.push(ingredient);
    return this;
  }

  withVegetable(ingredient: Ingredient): this {
    this.validate(ingredient, IngredientType.Vegetable);
    this.vegetables.push(ingredient);
    return this;
  }

  isValid(): boolean {
    return this.broth !== undefined && this.noodles !== undefined
      && this.proteins.length > 0 && this.vegetables.length > 0;
  }

  ingredients(): readonly Ingredient[] {
    return [
      ...(this.broth ? [this.broth] : []),
      ...(this.noodles ? [this.noodles] : []),
      ...this.proteins,
      ...this.vegetables,
    ];
  }
}

export class Inventory {
  private readonly stocks = new Map<string, number>();

  add(ingredient: Ingredient, quantity: number): this {
    const total = this.get(ingredient) + quantity;
    if (!Number.isSafeInteger(quantity) || quantity < 0 || !Number.isSafeInteger(total)) {
      throw new RangeError("Stock quantities must be non-negative safe integers.");
    }
    this.stocks.set(ingredient.key, total);
    return this;
  }

  get(ingredient: Ingredient): number {
    return this.stocks.get(ingredient.key) ?? 0;
  }

  isEmpty(): boolean {
    return this.stocks.size === 0;
  }

  private required(recipe: Recipe): Map<string, number> {
    const counts = new Map<string, number>();
    for (const ingredient of recipe.ingredients()) {
      counts.set(ingredient.key, (counts.get(ingredient.key) ?? 0) + 1);
    }
    return counts;
  }

  canCook(recipe: Recipe): boolean {
    if (!recipe.isValid()) return false;
    for (const [key, quantity] of this.required(recipe)) {
      if ((this.stocks.get(key) ?? 0) < quantity) return false;
    }
    return true;
  }

  withdrawRecipe(recipe: Recipe): boolean {
    if (!this.canCook(recipe)) return false;
    for (const [key, quantity] of this.required(recipe)) {
      this.stocks.set(key, (this.stocks.get(key) ?? 0) - quantity);
    }
    return true;
  }
}
