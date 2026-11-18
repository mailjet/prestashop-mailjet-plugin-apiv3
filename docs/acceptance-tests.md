# Audit acceptance tests (DE-11745)

The 14 minimum acceptance tests from the September 2026 audit (DE-11505), run on the staging in `staging/`.
Every test is checked in **both** systems: PrestaShop (`staging/check.sql`) and Mailjet (`staging/mj-check.sh <email>`).
A message in the PrestaShop back office alone does not count as a pass.

## Setup

1. Use a **Mailjet test sub-account**, with test lists and `@example.test` addresses only.
2. `cp staging/.env.example staging/.env`, then fill in the passwords and the test sub-account keys.
3. `docker compose -f staging/docker-compose.yml --env-file staging/.env up -d`, then wait for the PrestaShop install (about 2 min).
4. Back office → Modules: install **Mailjet**. Enter the test keys, enable contact sync and pick the managed lists (`docs/scope-rules.md` §1).
5. Use a second, unmanaged list in the same account for AT9.
6. Set up the cron URL from the module's Triggers page. Run it by hand during the tests: `curl "<cron URL>"`.
7. Webhook (AT3, AT11): Mailjet must reach the shop. Expose port 8080, for example `cloudflared tunnel --url http://localhost:8080`, and set the event URL in Mailjet.

Record each run in the table at the end: date, module commit, PASS/FAIL, and notes or Jira keys for failures.

## Tests

| AT | Scenario | Steps | Expected: PrestaShop | Expected: Mailjet | Covers |
|---|---|---|---|---|---|
| 1 | newsletter=0 customer matching an auto segment | Create a customer with newsletter off, add a cart and an order so it matches an auto-assigned segment | `newsletter=0` stays | Contact **not** added/subscribed to the segment list | DE-11516 |
| 2 | Subscribe with newsletter=1 | Register with newsletter on, then run the initial import, edit first/last name, run sync | `newsletter=1` | Subscribed to the managed list; firstname/lastname updated after the routine update | DE-11517 |
| 3 | Unsubscribe via Mailjet email, with and without account | Send a test campaign, then click unsubscribe as (a) a customer and (b) a guest from `ps_emailsubscription`. Run the cron | (a) `newsletter=0`, (b) `ps_emailsubscription.active=0` or row removed | Unsubscribed in the managed lists, other lists untouched | DE-11535, DE-11542 |
| 4 | Profile update after unsubscribe | After AT3, edit the customer's name in FO and BO, then rerun the sync and cron | `newsletter` stays 0 | Still unsubscribed: no `addforce` resubscription | DE-11517 |
| 5 | Double opt-in | Subscribe through the FO newsletter block with double opt-in on, and check Mailpit before confirming | No active subscription before the link is clicked | Contact absent or unsubscribed until confirmation, then subscribed | DE-11525 |
| 6 | Email change A→B | Change email in FO "My account" and, separately, in the BO customer form | Customer row has B | B subscribed, A removed from the managed lists (both interfaces) | DE-11567 |
| 7 | Delete/deactivate via every workflow | Customer record delete, list action, bulk delete, deactivate, and delete through the webservice API or a third-party module | Rows deleted or `active=0` | Contact removed from the managed lists in every workflow (and only those) | DE-11568 |
| 8 | Guest reactivate then deactivate | BO → Newsletter subscribers: reactivate a guest, then deactivate it | `ps_emailsubscription.active` follows | Subscribed, then unsubscribed | DE-11535, DE-11574 |
| 9 | Unrelated list on the same API key | Subscribe the test address to the unmanaged list in Mailjet, then repeat AT3 and AT7 | — | Unmanaged list subscription **unchanged** | DE-11542, DE-11736 |
| 10 | Segments | Sync segments that are existing, empty, changed (edit a condition and resync), and over 2,500 contacts (bulk-create with SQL) | — | Mailjet list counts match the segments; the large one completes (async job) | segments |
| 11 | API errors and recovery | Block `api.mailjet.com` in the shop container, or use wrong keys, and repeat AT2 and AT6. Restore access and run the cron. Send a webhook while processing fails | Failures logged; rows in `ps_mj_pending_operation`; webhook answered non-2xx | After the cron, the queued operations are applied and the queue is empty. Mailjet retries the webhook | DE-11715, DE-11722 |
| 12 | Triggered emails to unsubscribed/deactivated/deleted | Enable a marketing trigger, make the matching customers unsubscribed, deactivated or deleted, then run the trigger cron | — | **No** email in Mailpit or Mailjet stats for them | DE-11738 (planned) |
| 13 | Concurrent cron + SMTP failure | Run two trigger cron calls at once. Then stop `mail` and run the cron again | Sent status only set for mails actually sent | No duplicate emails; failed sends retried later | DE-11738 (planned) |
| 14 | Tracking consent (after #28) | Accept, refuse, then withdraw tracking consent in both directions, and inspect the email source | Consent stored | Tracking pixels and links only when consent was given | #28 (not planned yet) |

AT12–AT14 depend on fixes that are not merged yet: DE-11738, and #28 which has no ticket in this month's plan. Run them for the baseline now and expect failures. Rerun them after those fixes.

## Results

| Date | Module commit | AT1 | AT2 | AT3 | AT4 | AT5 | AT6 | AT7 | AT8 | AT9 | AT10 | AT11 | AT12 | AT13 | AT14 | Notes |
|---|---|---|---|---|---|---|---|---|---|---|---|---|---|---|---|---|
| | | | | | | | | | | | | | | | | |
