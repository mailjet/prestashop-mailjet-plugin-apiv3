#!/bin/bash
# Show what Mailjet knows about a contact: subscription status in every list + contact properties.
# Usage: staging/mj-check.sh someone@example.test     (reads MJ_APIKEY_PUBLIC / MJ_APIKEY_PRIVATE from staging/.env)
set -euo pipefail
cd "$(dirname "$0")"; [ -f .env ] && set -a && . ./.env && set +a
: "${MJ_APIKEY_PUBLIC:?set in staging/.env}" "${MJ_APIKEY_PRIVATE:?set in staging/.env}"
EMAIL="${1:?email}"
api() { curl -sS -u "$MJ_APIKEY_PUBLIC:$MJ_APIKEY_PRIVATE" "https://api.mailjet.com/v3/REST/$1"; }
echo "== Lists (IsUnsubscribed true = unsubscribed from that list)"
api "contact/$EMAIL/getcontactslists" | python3 -c 'import json,sys; [print(f"  list {l[\"ListID\"]}: unsubscribed={l[\"IsUnsub\"]}") for l in json.load(sys.stdin).get("Data",[])]' || true
echo "== Properties"
api "contactdata/$EMAIL" | python3 -c 'import json,sys; d=json.load(sys.stdin).get("Data",[]); [print(f"  {p[\"Name\"]} = {p[\"Value\"]}") for p in (d[0]["Data"] if d else [])]' || true
