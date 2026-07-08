# Understanding Relationships in Cubrel

In this article we cover what a relationship actually is, the different shapes one can take, how to create and delete your own, and how linking records to each other works day to day.

## What is a relationship

A relationship connects two modules together, for example "Accounts have many Contacts," or "Every Deal belongs to one Account." Once a relationship exists between two modules, you'll see it as a panel on the Related tab of any record in either module, showing whichever records from the other module are currently linked.

Cubrel ships with a set of relationships already built in between the standard modules (Accounts, Contacts, Deals, Quotes, and so on) — these are called **system relationships**. You can also create your own **custom relationships** between any two modules, including custom modules you've built yourself.

## The three shapes a relationship can take

When you create a relationship, you're describing how many records on one side can connect to how many on the other:

- **One to One** — each record on either side can be linked to at most one record on the other side. Useful for a strict pairing, like one Order to one Shipping Label.
- **One to Many / Many to One** — one record on one side can have many linked records on the other, but each of those "many" records only ever belongs to one of them. "One Account has many Deals" and "Many Deals belong to one Account" are the exact same relationship, just described from opposite sides — Cubrel lets you create it from whichever module makes more sense to you, and figures out the direction automatically.
- **Many to Many** — either side can be linked to any number of records on the other, with no limit on either end. "Contacts to Cases" is a typical example: one contact can be involved in several cases, and one case can involve several contacts.

## How a relationship shows up depends on its shape

- If your module is on the "many" side (or it's a Many to Many relationship), the panel shows a **list** of every linked record, with a count and pagination if there are a lot of them, plus a button to add more.
- If your module is on the "one" side of a One to Many relationship, or it's a One to One relationship, the panel shows just the **single** linked record (if any), with a quick way to unlink it or swap it for a different one.

## Creating a custom relationship

Go to **Settings > Modules > [pick a module] > Relationships > Create new relationship**, and fill in:

- **Name** — an internal identifier, not shown to end users.
- **Label** — what the panel is actually titled on the record page.
- **Related Module** — the other module this relationship connects to.
- **Type** — one of the four shapes above.

It doesn't matter which of the two modules you create it from — pick whichever one you happen to be looking at. If you want "many Deals to one Account" and you're on Deals' Relationships page, choose "Many To One" and pick Accounts as the related module; Cubrel stores it correctly either way.

## What can't be changed after creation

Once a relationship is created, its shape and the two modules it connects can't be edited afterward — there's no "edit" option, only create and delete. If you need something different (a different type, or a different pair of modules), delete the old one and create a new one in its place.

## Deleting a relationship

- **System relationships** (the ones Cubrel ships with) **can't be deleted at all** — the delete button is disabled for these.
- **Custom relationships** you've created can be deleted at any time. If there are records currently linked through it, you'll be shown exactly how many before you confirm — deleting the relationship removes all of those links permanently, along with the relationship itself. This can't be undone.

## Linking and unlinking records

From any record's Related tab, open a relationship's panel and:

- **To link a record**, click the add/link button, search for the record you want, and select it.
- **To unlink a record**, use the unlink action on that record (a single click for a "one" relationship, or a remove action next to each entry in a list for a "many" relationship).

If you link a record to a new "one" on a One to Many relationship — for example, moving a Deal from one Account to another — the old link is automatically replaced; a "many"-side record only ever belongs to one "one"-side record at a time, so re-linking it elsewhere is just how you move it, not an error.

Every link and unlink is recorded in the Audit Trail on both records involved — see [the Audit Trail guide](audit-trail-guide.md).

## Customizing what shows up, and what columns you search by

Two separate things are configurable per module, under **Settings > Modules > [module] > Layouts**:

- **Which relationships appear as panels**, and how they're arranged on the Related tab — the Related Panels layout editor lets you add, remove, and reorder them.
- **Which extra columns appear** in the search window you get when linking a new record — beyond just the record's name, you can show a couple of relevant fields (like status or owner) to help pick the right one, configured via the Linking Panel layout editor.

## In short

| Question | Answer |
| --- | --- |
| Can I create my own relationships? | Yes, between any two modules, from either module's settings page |
| Can I edit a relationship after creating it? | No — delete and recreate instead |
| Can I delete a built-in (system) relationship? | No, never |
| Can I delete a custom relationship? | Yes, any time — you'll see how many links it has first |
| What happens to links when I delete a relationship? | They're all removed permanently, along with the relationship |
| What happens if I re-link a "many" record to a new "one"? | It moves — the old link is replaced automatically |
| Is linking/unlinking tracked? | Yes, on both records, in the Audit Trail |
