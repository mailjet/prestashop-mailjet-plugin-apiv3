# Subscription scope rules

Decisions for the September 2026 audit (DE-11505). The audit fixes for findings #02, #05, #09, #11, #22, #23, #28 and #29 follow these rules. When a later change has to break one of them, update this file in the same PR.

## 1. Lists the shop manages

The module manages exactly these Mailjet lists, and no others on the same API key:

| List | How it is identified |
|---|---|
| Master list | `Configuration::get('contact_list')`, otherwise the list named `PrestaShop Customers Master List` |
| Segment lists | Lists whose name starts with `<id_filter>idf`, as created by `HooksSynchronizationSegment` |

`HooksSynchronizationSynchronizationAbstract::getManagedListIds()` is the single implementation. Any code that loops over lists must use it, never `getContactsLists()` directly.

Lists created by hand in Mailjet, or by another shop or tool on the same API key, are never subscribed to, unsubscribed from or emptied by the module.

## 2. Unsubscription, list removal, erasure

| Operation | Mailjet action | Scope | When |
|---|---|---|---|
| **Withdrawal** (unsubscription) | `unsub` | Every managed list the contact is on | Newsletter consent withdrawn: `newsletter = 0`, newsletter form "unsubscribe", BO "unsubscribe", Mailjet `unsub` webhook |
| **List removal** | `remove` | One segment list | The customer no longer matches a segment |
| **Erasure** | `remove` | Every managed list, master list included | Customer deleted (hard or soft), account deactivated, old address after an email change |

A withdrawal keeps the contact in the lists with the unsubscribed status. That status is the record that blocks a later routine sync from adding the contact back.

## 3. Withdrawal precedence and `addforce`

- A withdrawal always wins over a routine sync. Routine operations never change an unsubscribed contact back to subscribed.
- Routine operations use `addnoforce`: segment assignment, profile updates, back-office saves and list imports.
- `addforce` is reserved for an explicit, recorded opt-in by the contact:
  - the newsletter form after PrestaShop accepted the registration (`actionNewsletterRegistrationAfter`, which runs after the double opt-in confirmation);
  - account creation with the newsletter box ticked;
  - a customer update where `newsletter` changes from `0` to `1`.
- Without newsletter consent (`newsletter = 0`) a customer is never subscribed to any managed list, including segment lists that match the customer.

## 4. Source of truth per operation

| Data | Source of truth | Direction |
|---|---|---|
| Newsletter consent, opt-in | PrestaShop | PrestaShop → Mailjet |
| Unsubscription made in Mailjet (link in an email, Mailjet UI) | Mailjet | Mailjet → PrestaShop via the `unsub` webhook |
| Segment membership | PrestaShop (`mj_filter` rules) | PrestaShop → Mailjet |
| Contact properties (first name, last name, tracking consent) | PrestaShop | PrestaShop → Mailjet |
| Open/click tracking consent | Last change wins, see `MailjetTrackingConsent` | Both ways |

If an API call fails, the PrestaShop state stays as the target. The operation is queued and retried, and it is never dropped.

## 5. Multistore isolation

- A webhook event only changes data in the shop that owns the managed list from the event's `mj_list_id`. An event for a list the shop does not manage is acknowledged and ignored.
- A customer, or an `emailsubscription` row, is only changed in its own `id_shop`. The same email address in another shop is a different contact for consent purposes.
- Segment rules and cron-triggered emails only select customers in the current shop context.

## 6. Who may receive triggered emails

A cron-triggered email (abandoned cart, reorder, birthday and similar) is only sent when all of these are true at send time:

- the customer is active and not deleted;
- the customer has `newsletter = 1` for marketing triggers. Service triggers such as a payment reminder only need an active account;
- the address has not been withdrawn in Mailjet.
