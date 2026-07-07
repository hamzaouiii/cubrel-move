# Module vs BaseModule: A Developer's Guide

This article is for anyone new to Cubrel's codebase who's trying to figure out where a piece of module-related logic should live: on `Module`, or on `BaseModule`. There's no CRM screen where these names appear, they're purely backend architecture, but the split between them shows up constantly once you start reading or writing module code.

## The one-sentence version

**`Module` describes a module. `BaseModule` is what a module's records inherit from.**

## Two very different kinds of thing

Cubrel ships with a fixed set of module *types*: Deals, Contacts, Accounts, Cases, and so on, plus any custom modules an admin builds. Each of those types needs two completely different things represented in code:

1. **A description of the module itself** — its name, icon, color, which fields it has, what its layouts look like, whether it has line items. This is configuration. There's exactly one of these per module type, and it lives in the `modules` database table.
2. **The actual records that belong to that module** — this specific deal, that specific contact. There are many of these per module type, and each module type stores them in its own table (`deals`, `contacts`, `accounts`, ...).

`Module` is the Eloquent model for (1). `BaseModule` is the shared ancestor class for (2).

## `Module`: the registry entry

`Module` (`app/Models/Module.php`) is a normal Eloquent model backed by the `modules` table. Every module type, built-in or custom, has exactly one row here. That row is what the Module Builder UI edits, and it's what the rest of the app reads to answer questions like:

- What fields does the `deals` module have, and what are their types?
- What does the record layout for `contacts` look like?
- Does `orders` have line items, and if so, where do they source from?
- What Eloquent class actually implements this module?

That last question is answered by the `model_class` column, and `Module::getInstance()` uses it to instantiate the right concrete class:

```php
public function getInstance(): BaseModule
{
    return new ($this->class_name);
}
```

## `BaseModule`: the shared record behavior

`BaseModule` (`app/Models/BaseModule.php`) is an **abstract** class. You never create a `BaseModule` directly, there's no `base_modules` table, and no row anywhere is "a BaseModule." Instead, every concrete module class extends it:

```php
class Deal extends BaseModule
{
    protected $table = 'deals';

    protected $fillable = ['name', 'owner_id', 'amount', 'sales_stage', 'probability', 'expected_close_date', 'type'];
    // ...
}
```

`Deal`, `Contact`, `Account`, `SupportCase`, `Order`, and every other module class inherit from `BaseModule`, which gives them, for free:

- Search indexing (Laravel Scout `Searchable`)
- Custom field support
- Dynamic relationships (`link()` / `unlinkRelation()`)
- Owner defaulting on create
- Audit logging (`AuditObserver`, registered per concrete class)
- A way to find their own `Module` registry row (`moduleDefinition()`, `getModuleSlug()`)

Anything that's true of *every record in every module*, regardless of which module it is, belongs here.

## How they find each other

A `BaseModule` subclass instance can look up its own metadata row:

```php
public function moduleDefinition(): Module
{
    return Module::withoutGlobalScope(\App\Scopes\AdminOnlyModuleScope::class)
        ->where('model_class', static::class)
        ->firstOrFail();
}
```

And a `Module` row can instantiate its corresponding business class via `getInstance()` shown above. This is the only place the two classes reference each other directly, everything else is one-directional: business logic doesn't reach "up" into the registry except to read metadata, and the registry doesn't reach "down" into business records except to instantiate one.

## A mental model

Think of `Module` as a **blueprint** and `BaseModule` subclasses as the **buildings** built from it. One blueprint, many buildings. The blueprint records don't have addresses or tenants; the buildings don't specify their own floor plan, they just point back at the blueprint that defines it. You wouldn't merge "the blueprint" and "a building" into one concept, they answer different questions and have different quantities.

## Where does my new method go?

Ask: **does this describe the module, or does it act on a record?**

| If you're... | Add it to |
| --- | --- |
| Adding a config field (icon, color, category, `has_line_items`) | `Module` |
| Resolving which layout/fields a module uses | `Module` |
| Adding behavior every record type should get (audit, search, linking) | `BaseModule` |
| Adding behavior specific to one module (e.g. `Deal::toSearchResult()`) | The concrete subclass (`Deal`, `Contact`, ...), overriding `BaseModule` |

If you're not sure, a quick test: could this method ever be called without a specific record loaded, just from a module's slug or config? If yes, it's a `Module` concern. If it needs `$this->id`, `$this->owner_id`, or any other record-specific attribute, it's a `BaseModule` (or subclass) concern.

## In short

| Question | `Module` | `BaseModule` |
| --- | --- | --- |
| What is it? | A registry row describing a module type | An abstract base class for module records |
| Backed by a table? | Yes, `modules` | No |
| How many exist per module type? | One | Many (one per record, in that module's own table) |
| Ever instantiated directly? | Yes | No, always via a concrete subclass |
| Example | The row describing "Deals" | `Deal`, `Contact`, `SupportCase`, ... |
