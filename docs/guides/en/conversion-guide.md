# Understanding Conversion Rules in Cubrel

In this article we cover what a conversion rule is, how to set one up, the difference between converting a record by hand and letting Cubrel do it automatically, and what happens to the two records once they're connected.

## What is a conversion rule

A conversion rule tells Cubrel how to create a record in one module from a record in another — for example, "create an Invoice from a Quote." It's a reusable recipe, not a one-off action: once you've defined it, every record in the source module can be converted the same way, either by clicking a button yourself or automatically when conditions you choose are met.

Converting a record never changes or removes the original — a Quote stays exactly as it was, a new Invoice is simply created alongside it and linked back to it.

## Creating a conversion rule

Go to **Settings > Automation > Conversion Rules > New Conversion Rule**, then:

1. Give it a **name**, and pick the **Source Module** (what you're converting from) and **Target Module** (what gets created) — these can be any active module except Users and User Invites. Once saved, these two modules can't be changed — delete and recreate the rule if you picked wrong.
2. Fill in the **Setup** tab (below).
3. Fill in the **Mapping** tab (below).
4. Save.

Cubrel checks a few things before letting you save, so you can't create a rule that would fail or crash later:

- Every field the target module actually requires must have a mapping — Cubrel adds an empty row for each one automatically (and a sensible default where it can guess one, see below) so you don't have to remember which fields are required.
- A "from a field" mapping only offers source fields of the same type as the target field (and, for a record-type field, only ones pointing at the same related module) — so you can't accidentally wire a text field into a number field.
- The one-line expression builder is only offered for text-like target fields (text, long text, email, phone, URL) — it wouldn't make sense for a number or date field.

### Setup tab

- **Automatic** — off by default. A conversion rule can *always* be run manually regardless of this toggle; turning it on additionally lets Cubrel run it by itself whenever a record's conditions become true, with no one clicking anything.
- **Conditions** — only shown once Automatic is on, since they only ever gate the automatic run, never the manual one. Add one or more `field / operator / value` rows (the same condition-builder used by List Filters), and choose whether **all** of them or **any one** of them must match. A rule can't be saved with Automatic on and no conditions — an automatic rule with nothing to check would never actually run.
- **Link the two records** — on by default. Links the source record to the one this rule creates, so each shows up in the other's Relationships tab and as its "Created From"/"Converted To" connection. If you turn on both this and Automatic, Cubrel warns you: unlike a manual run, an automatic run has no confirmation step, so if the underlying relationship is a strict one-to-one, every automatic run silently replaces whichever record it was previously linked to.

### Mapping tab

- **Field Mappings** — for each field on the target record, choose where its value comes from: a field on the source record, a fixed static value, or a small expression (literal text combined with a source field and/or a helper like today's date). A target field can only be mapped once. Required target fields are pre-added for you, and the two most common ones get a sensible default automatically: **Owner** defaults to "Current user" and **Name** defaults to the source record's own name — both can still be changed.
  - For a static value on a record-type field (e.g. mapping a fixed Owner or a fixed related record), a picker lets you search and select the actual record instead of typing an id — or choose "Current user" as a shortcut for whoever runs the conversion.
- **Relationships to Copy** — which of the source record's relationships (line items, notes, attachments, and so on) get copied onto the new record too. Use "Select all"/"Unselect all" to toggle every option at once.

## Running a conversion manually

Any enabled conversion rule shows up under **Convert** in a record's action menu, regardless of whether Automatic is on. Picking one:

1. Checks whether running it would replace an existing link (only relevant for a one-to-one relationship). If so, you're asked to confirm before continuing, or you can create the new record without linking it at all.
2. Creates the new record according to the rule's field mappings and copied relationships.
3. Shows a confirmation with a link straight to the new record — you stay on the record you converted from, nothing navigates you away.

## Automatic conversions

With Automatic on, Cubrel checks a record's conditions every time it's saved, and runs the conversion the moment they become true — no button, no confirmation. A few things worth knowing:

- It only fires when a save actually changes one of the condition fields, not on every unrelated save of a record that already qualifies.
- If a condition field is toggled away from matching and then back again, the rule fires again and creates a second record — this is by design for now: it's a simple, predictable trigger, not a full workflow engine that tracks whether it already ran once for a given record.
- Because there's no one in the loop to confirm anything, a one-to-one "Link the two records" setup can silently re-link a record on every automatic run (see the warning under Setup above) — turn off linking, or use a different relationship type, if that's not what you want.

## Enabling, disabling, and deleting

From the Conversion Rules list, each rule can be enabled/disabled with one click (a disabled rule can't be run at all, manually or automatically) or opened to edit. Deleting a rule removes the recipe itself, but never touches records or links it already created — if it has ever actually been used, you'll see a stronger warning before you can delete it, since that history can't be recovered afterward.

## In short

| Question | Answer |
| --- | --- |
| Does converting a record change the original? | No, the source record is left untouched |
| Can I run a conversion rule by hand? | Yes, always, whenever it's enabled — Automatic doesn't affect this |
| Do Conditions affect the manual "Convert" action? | No, only the automatic run |
| Can a rule be Automatic with no conditions? | No, that's blocked when saving |
| What happens if I turn on both Automatic and Link the two records on a one-to-one relationship? | Every automatic run can silently replace the existing link, with no confirmation |
| Can I change the source/target module after creating a rule? | No — delete and recreate instead |
| Can Users or User Invites be a source/target module? | No, they're excluded from the module pickers |
| Can I save a rule that leaves a required target field unmapped? | No, Cubrel blocks the save until every required field has a mapping |
| Does deleting a rule delete records or links it already created? | No, only the rule itself is removed |
