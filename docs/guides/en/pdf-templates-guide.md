# Building PDF Templates in Cubrel

In this article we cover what a PDF template is, how to build one out of sections and fields, and how to actually generate a PDF from a record once a template exists.

## What a PDF template is

A PDF template belongs to one module (for example, Quotes or Invoices) and defines a layout Cubrel uses to turn any record in that module into a PDF, think of it as a document design you build once and reuse for every record. A module can have more than one template (say, one for Quotes and one for a shorter Order Confirmation), with one marked as the **default**.

## Building a template

From **Settings > PDF Templates**, create a new template and pick the module it belongs to. The editor is drag-and-drop: every template has a locked **Header** and **Footer**, and you build the body by dropping in sections:

- **Fields** — a labeled group of fields, laid out half or full width.
- **Text** — a free text block for things that aren't tied to a field.
- **Divider** — a horizontal rule to separate sections visually.
- **Line items** — a table of the record's line items. Name, Position, and Total are always shown; other columns are optional (keep it to around 8 columns or fewer so it doesn't overflow the page).
- **Relationship** — data pulled in from a related record (for example, the linked Company's address on a Quote).

The Header and Footer support their own building blocks: your company logo, company details, the document title, page numbers, the date, and a one-line company info string.

## Adding fields

Drag a field from the **Available Fields** panel on the left onto a section, related-module fields are listed there too and can be expanded. Once placed, a field can have its label shown or hidden, and a display style — title, subtitle, bold, small, label, status, address, or muted — to control how it looks on the page.

## Previewing before you save

Click **Preview** at any point to see roughly how the current layout will render, using placeholder sample data instead of a real record, so you can check the design without needing an actual record to test against.

## Generating a PDF from a record

Open any record and click the PDF icon. If its module only has one template, Cubrel generates it immediately. If there's more than one, you're shown a picker (the default template is marked) to choose which one to use.

## Branding

Your company name, address, phone, email, website, and logo come from your global company settings and are pulled automatically into every template's header/footer, there's no per-template logo or color override today.

## Known limitations

- No image upload beyond the shared company logo.
- No manual page-break control inside a section.
- PDF generation is synchronous (you wait while it's built), so very large batches of PDFs at once aren't recommended.

## In short

| Question | Answer |
| --- | --- |
| Where do I manage templates? | **Settings > PDF Templates** |
| Can a module have more than one template? | Yes, with one marked as default |
| How do I insert a field's value? | Drag it from **Available Fields** onto a section |
| Can I preview before saving? | Yes, with placeholder sample data |
| How do I generate a PDF from a record? | Open the record and click the PDF icon |
| What if a module has multiple templates? | You're shown a picker; the default is marked |
| Can I set a different logo per template? | No, branding comes from company settings globally |
