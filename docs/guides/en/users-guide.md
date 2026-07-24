# Managing Users, Invites, and Passwords in Cubrel

In this article we cover how people get access to your Cubrel organization, the difference between creating a user directly and inviting one, and how passwords get set, reset, or recovered along the way.

## The Users module

**Settings-level access aside, everyone with a Cubrel account shows up under Users** — one record per person, alongside every other module's records: a list view, a record page, and its own fields (name, username, email, phone, title, avatar, status, and so on). It behaves like any core module, with two differences that come from it representing a person rather than a business record:

- Users have no **Owner** field — a user can't own itself the way a Deal or Contact is owned by someone.
- The record page's action menu offers user-specific actions instead of the usual ones: **Send password reset email**, and, for super admins only, **Login as** (impersonation, covered in the [Audit Trail guide](audit-trail-guide.md#impersonation-is-always-transparent-never-hidden)).

Every user has a **Status** (Active or Inactive) and an **Is Admin** flag. Status controls whether the account can be impersonated and shows up as available; Admin controls access to Settings, see [Admin and Super admin](terminology.md#admin) in the terminology guide for the distinction between an admin and a super admin.

## Two ways to add someone: create directly, or invite

Both live under **Users**, and both end up with the same kind of account, they just differ in who sets the person's password.

### Creating a user directly

From **Users > New**, an admin fills in the person's details (name, username, email, title, admin flag, etc.) and saves. There's deliberately no password field on this form; the account is created without a usable password. Right after saving, you're asked whether to email the new user a link to set their own password, this is the same **Send Set Password Email** action described below, just offered automatically at the moment of creation. You can decline and send it later from the user's record instead.

### Inviting someone by email

From the **Users** list, **Invite User** opens a modal where you can queue up one email address at a time (each with its own **Admin** toggle), and send them all together, up to 20 in a single batch. There's no separate "add one" vs. "bulk invite" screen, you just add as many rows as you need before sending.

Each invite sends an email containing a unique sign-up link. Opening it takes the person to a short form (first name, last name, username, password, confirm password), their email is already fixed by the invite and can't be changed there. Submitting it creates their account and signs them straight in.

A few rules govern invites:

- **One pending invite per email at a time.** Inviting an address that already has a pending invite replaces it rather than creating a duplicate. Inviting an address that's already a user is rejected outright.
- **Invite links expire after 7 days.** After that, the link still opens but shows as expired instead of the sign-up form.
- **Links are single-use.** Once accepted, the same link can't be used again.

### Tracking invites: Users > Invites

**Users > Invites** lists every invite that's been sent, with a status of **Pending**, **Accepted**, **Expired**, or **Revoked**. From this list you can:

- **Resend** a pending or expired invite — this issues a brand-new link (and a new 7-day window), replacing the old one. The old link stops working the moment the new one is sent.
- **Revoke** a pending invite, if you invited the wrong address or changed your mind. A revoked invite's link stops working immediately.
- **Delete** an invite once it's no longer pending (accepted, expired, or revoked), to clear it from the list. Pending invites must be revoked first.

## Password management

Cubrel never shows or emails anyone's actual password, every path below works by emailing a secure, time-limited link instead.

### Setting a password for the first time

New users set their own password, they're never assigned one by an admin:

- **Invited users** set it as part of accepting their invite (the sign-up form described above).
- **Directly-created users** set it the first time they follow the **Send Set Password Email** link, which an admin can trigger from the user's record at any point (not just right after creation) via the action menu's **Send Set Password Email** option.

### Resetting a forgotten password

Anyone can request their own reset from the **Forgot password?** link on the login page, entering their email sends them a reset link the same way a normal "forgot password" flow works anywhere else.

An admin can also trigger this on someone's behalf: open the user's record, open the action menu, and choose **Send Password Reset Email**. This is the same underlying reset-link flow, just started by an admin instead of the user, useful if someone's locked out and can't reach the forgot-password page themselves.

Both the reset link and the set-password link expire **1 hour** after being sent and can only be used once. If a user has no email address on file, these actions can't be sent, and Cubrel will tell you why instead of silently failing.

## In short

| Question | Answer |
| --- | --- |
| Where do I manage users? | **Users** (list, create, and each person's own record) |
| How do I add someone without emailing them? | **Users > New** — creates the account, no password set yet |
| How do I add someone by email invite? | **Users** list > **Invite User**, up to 20 emails per batch |
| Where do I see invite status? | **Users > Invites** — Pending / Accepted / Expired / Revoked |
| Can I resend an expired invite? | Yes, **Resend** issues a fresh link and a new 7-day window |
| How long do invite/reset/set-password links last? | Invites: 7 days. Password reset / set-password links: 1 hour. All are single-use |
| Does an admin ever see or set someone's password? | No, every path emails the user (or invitee) a link to set it themselves |
| What if a user is locked out? | An admin sends them a **Password Reset Email** from their record |
