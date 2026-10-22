# TypeScript rewrite design

Decision: 2026-10-22.

## Stack and structure

Port the existing rewrite to TypeScript on Node.js, using Discord.js for the bot. The bot and game engine belong in the same codebase and can run together in one Docker deployment.

Keep game rules and simulation independent of Discord. Organize the code into game engine, storage, and Discord interface modules; commands call the engine directly. Enable strict TypeScript checking.

## First rewrite pass

Deliver the minimal engine already represented by the Rust prototype:

- Restaurants with names, cash, menus and ingredient inventories.
- Ingredients and recipes, including ingredient-type validation.
- Ramen menu items and prices, order queues, customer generation and cooking.
- Elapsed-time simulation that consumes stock, serves orders, updates cash and reports earnings and bowls served.

Port the bot skeleton with only `rping` enabled, replying `pong`. All other commands, including `rhome` and `rhelp`, remain silent, per the implementation request. Provide command-handler tests and a Discord smoke check. The target is parity with the current skeleton; persistence remains limited to the existing restaurant name/balance adapter and the original game's full feature set is beyond this first pass.

## Required integration tests

Catalog-based integration tests simulating gameplay use cases are mandatory for the first pass. Port the shared fixtures in Rust's `game/tests/catalog/mod.rs` and the scenarios in `game/tests/simulation_tests.rs`, `game/tests/restaurants_tests.rs`,  and the existing SQLite header scenarios in `game/tests/persistence_tests.rs`.

The catalog supplies reusable ingredients, a valid recipe, ramen, stock, menu and restaurant. Its basic restaurant starts with $1,000 (100,000 cents), ten units of each recipe ingredient and ramen priced at $10 (1,000 cents).

Required scenarios:

- Empty restaurant initialization, valid recipe construction, and a stocked restaurant with correct menu prices.
- Simulated service increases cash; cash gained equals reported earnings.
- Ingredient consumption equals served bowls plus any bowl currently cooking.
- Stock exhaustion serves exactly ten bowls and leaves the required ingredients at zero.

Use a controlled clock and explicit elapsed durations so tests are deterministic and require neither Discord access nor real waiting. Run these scenarios against the actual engine modules together. First-pass acceptance requires passing type checks, catalog integration tests and command tests, plus the Discord smoke check.

## Money representation

Most balances are expected below $1 million; use approximately $1 trillion as the conservative planning maximum, rather than introducing a gameplay cap.

Represent money as integer cents using TypeScript's `number` type. $1 trillion is 100,000,000,000,000 cents, comfortably within JavaScript's maximum safe integer of 9,007,199,254,740,991. Use this unit consistently for balances, prices, earnings and catalog expectations; convert to dollars for display. Validate monetary values as safe integers and keep persisted/serialized amounts in cents. BigInt and arbitrary-precision currency libraries are unnecessary for this range.

## First-pass priorities

Prioritize behavioral parity, correctness, clear code and the required tests. Preserve the straightforward simulation approach; performance optimization, worker processes and load testing are outside the first-pass scope. Revisit performance when actual usage warrants it.
