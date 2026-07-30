# Setting Up Cubrel for the First Time

In this article we cover the first-run setup wizard, from the initial setup link through to inviting your team. For ongoing, day-to-day user invites after setup, see [Managing Users, Invites, and Passwords](users-guide.md).

## Getting your setup link

A brand-new Cubrel instance has no users yet, so it can't be accessed through the normal login page. Setting it up starts from a one-time **setup link**, generated on the server and either emailed to the first admin or shared directly. This link is only valid for **24 hours**, and only one is valid at a time, generating a new one invalidates any earlier one.

Opening the link shows a short form to create the very first account: name, username, email, and password. This account becomes the instance's first super admin. The link is single-use, once this form is submitted, it stops working.

## The setup wizard

After your account is created, you're taken through a short wizard:

1. **Organization info** — your company name, address, phone, email, website, and logo. This becomes the default branding used throughout the app, including in PDF templates.
2. **Demo data** — an optional toggle to seed the instance with sample records, useful for exploring Cubrel before your real data is in, skip it if you'd rather start from a clean instance.
3. **Invite your team** — add email addresses (each with its own Admin toggle) to invite people right away.

## Skipping the invite step

The invite step isn't required, use **Skip** on that step, or the **Skip** link available at any point in the wizard, to finish setup without inviting anyone yet. You can always invite people afterwards from **Users**, see [Managing Users, Invites, and Passwords](users-guide.md).

## Before you invite anyone

Invites sent from the wizard are real emails. If outbound email isn't configured yet, those invites won't actually reach anyone, even though the wizard will accept them. If you're not sure email is set up, skip this step and invite your team later once it's confirmed working.

## After setup finishes

Once you finish (or skip) the last step, setup is marked complete and the wizard won't run again, visiting it again just takes you to your Dashboard instead.

## In short

| Question | Answer |
| --- | --- |
| How do I access Cubrel for the first time? | Via a one-time setup link, valid for 24 hours |
| What does the setup link let me do? | Create the first super admin account |
| What are the wizard's steps? | Organization info, optional demo data, invite your team |
| Can I skip inviting people during setup? | Yes, at that step or at any point in the wizard |
| Can I run the wizard again later? | No, it only runs once, before the instance has any completed setup |
| Do invites in the wizard need email configured? | Yes, otherwise they won't be delivered |
