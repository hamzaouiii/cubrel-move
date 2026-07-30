# Understanding Email Capture in Cubrel

In this article we cover what email capture is, how to find and use your own capture address, how admins can set up shared team addresses, and how a captured email connects to the rest of your CRM data.

## What is email capture

Email capture lets you log an email against your CRM records without typing anything in by hand. Every user gets a standing email address — BCC it (or send to it directly) on any email you send from your normal inbox, and Cubrel logs a copy of that email automatically, matching it to any Contact whose address appears in it.

Captured emails land in the **Emails** module, right alongside Tasks, Calls, Meetings, and Notes — they show up the same way in a Contact's or Deal's activity timeline, they can be linked and unlinked like any other activity, and they can trigger a Conversion Rule.

## Finding your personal capture address

Your capture address is `yourusername@` followed by your company's Cubrel domain — it's derived directly from your username, so there's nothing separate to generate or remember. You can find it, and copy it with one click, from your **Profile** page.

If you ever change your username, your capture address changes with it — the new address starts working immediately, and anything already captured through the old one is unaffected.

## Using it

BCC your capture address on any email you send, the same way you'd BCC a colleague. There's nothing to configure per-email — every message sent to that address is logged automatically, whether you're BCC'd on it or it's addressed to you directly.

A captured email keeps its original subject, sender, recipients, and body, and is timestamped with when it was actually sent — not when Cubrel happened to receive it.

## Team / shared capture addresses

Beyond personal addresses, an admin can set up additional capture addresses for a shared purpose — for example `leads@` for new inbound inquiries, or `support@` for a shared support inbox — from **Settings > Email > Inbound Email**.

A team address can optionally have an owner (whoever should be considered responsible for what comes through it), or be left ownerless — captured emails through an ownerless address still get logged normally, just without a specific person attached as the owner. Deleting a team address only removes the address itself; any emails already captured through it stay exactly where they are.

## How a captured email connects to your data

Every recipient and sender address on a captured email is checked against your Contacts — any match links the email to that Contact automatically, the same linking system used everywhere else in Cubrel. That link is what makes a captured email show up in a Contact's (or any other linked record's) activity timeline without you doing anything extra.

## Automating what happens next

Every captured email records which address received it — your personal one, or a team address's name. Because that's a regular field (called **Mailbox**), it can be used as a condition in a [Conversion Rule](/en/conversion-guide) — for example, a rule that automatically creates a Lead from anything captured through the `leads` address, with no one needing to review it first.

## In short

| Question | Answer |
| --- | --- |
| Do I need to set anything up to get a capture address? | No — every user has one automatically, based on their username |
| Where do I find my capture address? | Your Profile page, with a one-click copy button |
| What do I do with it? | BCC it (or send to it directly) on any email |
| Does the captured email keep the original sender/subject/body? | Yes, exactly as sent |
| Can I get a shared address for a team inbox, not tied to one person? | Yes — an admin creates it under Settings > Email > Inbound Email, owner optional |
| How does a captured email end up on a Contact's timeline? | Automatically, by matching participant email addresses |
| Can captured emails trigger automation? | Yes, via Conversion Rules conditioned on the Mailbox field |
| If I change my username, does my old capture address stop working? | Yes, immediately — the new one takes over right away |
