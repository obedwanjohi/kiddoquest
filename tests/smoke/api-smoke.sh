#!/usr/bin/env bash
# End-to-end smoke test for the KiddoQuest app API.
#
# Exercises the whole contract the Flutter app depends on: sign in, children,
# the catalogue, a pack download, a sync with a deliberately forged score, a
# replay of the same events, quarantine, and the parent PIN.
#
#   php artisan serve &
#   php artisan content:build-pack --all
#   API_BASE=http://127.0.0.1:8000/api/v1 bash tests/smoke/api-smoke.sh
set -u
BASE="${API_BASE:-http://127.0.0.1:8000/api/v1}"
DEV="test-device-0001"
PASS=0
FAIL=0

hdr=(-H "Content-Type: application/json" -H "Accept: application/json" -H "X-Device-Id: $DEV" -H "X-App-Version: 1.0.0" -H "X-Platform: android")

check() { # name expected actual
  if [ "$2" = "$3" ]; then PASS=$((PASS+1)); echo "PASS  $1"; else FAIL=$((FAIL+1)); echo "FAIL  $1  expected[$2] got[$3]"; fi
}

jqv() { php -r '$j=json_decode(stream_get_contents(STDIN),true); $p=explode(".",$argv[1]); foreach($p as $k){ if(is_array($j)&&array_key_exists($k,$j)) $j=$j[$k]; elseif(is_array($j)&&ctype_digit($k)&&isset($j[(int)$k])) $j=$j[(int)$k]; else {echo ""; exit;} } echo is_scalar($j)?(is_bool($j)?($j?"true":"false"):$j):json_encode($j);' "$1"; }

echo "=============== 1. config (public) ==============="
CFG=$(curl -s "${hdr[@]}" "$BASE/config")
check "config returns min_app_version" "1.0.0" "$(echo "$CFG" | jqv min_app_version)"
check "config exposes one price list" "200" "$(echo "$CFG" | jqv plans.0.amount)"
check "config exposes PG question cap" "6" "$(echo "$CFG" | jqv session.questions_per_level.PG)"

echo "=============== 2. auth ==============="
EMAIL="apitest$(date +%s)@example.test"
REG=$(curl -s "${hdr[@]}" -X POST "$BASE/auth/parent/register" -d "{\"name\":\"API Test Parent\",\"email\":\"$EMAIL\",\"password\":\"supersecret\"}")
TOKEN=$(echo "$REG" | jqv token.access_token)
check "register returns a token" "yes" "$([ -n "$TOKEN" ] && echo yes || echo no)"

UNAUTH=$(curl -s -o /dev/null -w "%{http_code}" "${hdr[@]}" "$BASE/children")
check "children requires auth" "401" "$UNAUTH"

auth=(-H "Authorization: Bearer $TOKEN")

LOGIN=$(curl -s "${hdr[@]}" -X POST "$BASE/auth/parent/login" -d "{\"email\":\"$EMAIL\",\"password\":\"supersecret\"}")
check "login works" "yes" "$([ -n "$(echo "$LOGIN" | jqv token.access_token)" ] && echo yes || echo no)"
BAD=$(curl -s "${hdr[@]}" -X POST "$BASE/auth/parent/login" -d "{\"email\":\"$EMAIL\",\"password\":\"wrongpass\"}")
check "bad password rejected" "invalid_credentials" "$(echo "$BAD" | jqv error.code)"

echo "=============== 3. children ==============="
KID=$(curl -s "${hdr[@]}" "${auth[@]}" -X POST "$BASE/children" -d '{"name":"Zawadi","avatar":"lion","birthdate":"2023-04-01","favorite_color":"purple"}')
CHILD_ID=$(echo "$KID" | jqv child.id)
check "child created" "yes" "$([ -n "$CHILD_ID" ] && echo yes || echo no)"
check "level recommended from birthdate" "Play Group" "$(echo "$KID" | jqv child.level)"

OTHER=$(curl -s -o /dev/null -w "%{http_code}" "${hdr[@]}" "${auth[@]}" "$BASE/children/999999")
check "other family child is 404" "404" "$OTHER"

kid=(-H "X-Child-Id: $CHILD_ID")

echo "=============== 4. content ==============="
CAT=$(curl -s "${hdr[@]}" "${auth[@]}" "${kid[@]}" "$BASE/content/catalog")
check "catalog scoped to the child level" "PG" "$(echo "$CAT" | jqv level)"
PACK=$(echo "$CAT" | jqv packs.0.pack_id)
PACK_ID="pg-math-whispering-forest"
PACK_VERSION=$(curl -s "${hdr[@]}" "${auth[@]}" "$BASE/content/packs/$PACK_ID/manifest" | jqv pack.version)
check "catalog lists a pack" "yes" "$([ -n "$PACK" ] && echo yes || echo no)"
check "first world of a subject is free" "true" "$(echo "$CAT" | jqv packs.0.is_free)"

ETAG=$(curl -s -D - -o /dev/null "${hdr[@]}" "${auth[@]}" "${kid[@]}" "$BASE/content/catalog" | grep -i '^etag:' | tr -d '\r' | cut -d' ' -f2)
NOTMOD=$(curl -s -o /dev/null -w "%{http_code}" "${hdr[@]}" "${auth[@]}" "${kid[@]}" -H "If-None-Match: $ETAG" "$BASE/content/catalog")
check "catalog honours ETag" "304" "$NOTMOD"

MAN=$(curl -s "${hdr[@]}" "${auth[@]}" "$BASE/content/packs/$PACK_ID/manifest")
check "manifest returns the pack" "$PACK_ID" "$(echo "$MAN" | jqv pack.pack_id)"
DL=$(curl -s "${hdr[@]}" "${auth[@]}" "$BASE/content/packs/$PACK_ID/v$PACK_VERSION/download")
MISSION_ID=$(echo "$DL" | jqv missions.0.id)
check "pack downloads with missions" "yes" "$([ -n "$MISSION_ID" ] && echo yes || echo no)"

echo "=============== 5. sync ==============="
Q1=$(echo "$DL" | jqv missions.0.questions.0.id)
Q2=$(echo "$DL" | jqv missions.0.questions.1.id)
Q3=$(echo "$DL" | jqv missions.0.questions.2.id)
# correct option for each question, and a deliberately wrong one for Q3
C1=$(php -r '$d=json_decode(file_get_contents("php://stdin"),true); $q=$d["missions"][0]["questions"][(int)$argv[1]]; foreach($q["options"] as $o){ if($o["is_correct"]){echo $o["id"]; return;} } echo "0";' 0 <<< "$DL")
C2=$(php -r '$d=json_decode(file_get_contents("php://stdin"),true); $q=$d["missions"][0]["questions"][(int)$argv[1]]; foreach($q["options"] as $o){ if($o["is_correct"]){echo $o["id"]; return;} } echo "0";' 1 <<< "$DL")
W3=$(php -r '$d=json_decode(file_get_contents("php://stdin"),true); $q=$d["missions"][0]["questions"][(int)$argv[1]]; foreach($q["options"] as $o){ if(!$o["is_correct"]){echo $o["id"]; return;} } echo "0";' 2 <<< "$DL")

EV1=$(php -r "printf(\"%04x%04x-%04x-4%03x-8%03x-%04x%04x%04x\", mt_rand(0,0xffff),mt_rand(0,0xffff),mt_rand(0,0xffff),mt_rand(0,0xfff),mt_rand(0,0xfff),mt_rand(0,0xffff),mt_rand(0,0xffff),mt_rand(0,0xffff));")
EV2=$(php -r "printf(\"%04x%04x-%04x-4%03x-8%03x-%04x%04x%04x\", mt_rand(0,0xffff),mt_rand(0,0xffff),mt_rand(0,0xffff),mt_rand(0,0xfff),mt_rand(0,0xfff),mt_rand(0,0xffff),mt_rand(0,0xffff),mt_rand(0,0xffff));")
NOW=$(date -u +%Y-%m-%dT%H:%M:%SZ)

BODY=$(cat <<JSON
{"cursor":null,"events":[
 {"id":"$EV1","seq":1,"type":"mission_started","client_ts":"$NOW","payload":{"mission_id":$MISSION_ID,"pack_version":$PACK_VERSION,"question_ids":[$Q1,$Q2,$Q3]}},
 {"id":"$EV2","seq":2,"type":"mission_completed","client_ts":"$NOW","payload":{"mission_id":$MISSION_ID,"pack_version":$PACK_VERSION,"score":3,"stars":3,"total":3,"time_spent":180,
   "answers":[{"question_id":$Q1,"response":{"option_id":$C1}},{"question_id":$Q2,"response":{"option_id":$C2}},{"question_id":$Q3,"response":{"option_id":$W3}}]}}
]}
JSON
)
S1=$(curl -s "${hdr[@]}" "${auth[@]}" "${kid[@]}" -X POST "$BASE/sync" -d "$BODY")
check "sync accepted both events" "2" "$(echo "$S1" | php -r '$j=json_decode(stream_get_contents(STDIN),true); echo count($j["accepted"] ?? []);')"
check "sync rejected none" "0" "$(echo "$S1" | php -r '$j=json_decode(stream_get_contents(STDIN),true); echo count($j["rejected"] ?? []);')"
check "server recomputed score 2 of 3" "2" "$(echo "$S1" | jqv snapshot.child.total_stars)"
check "coins awarded (10 base + 5 two-star + 5 first day)" "20" "$(echo "$S1" | jqv snapshot.child.star_coins)"
check "streak started" "1" "$(echo "$S1" | jqv snapshot.child.streak_days)"
check "progress recorded" "completed" "$(echo "$S1" | jqv snapshot.progress.0.status)"
check "snapshot carries content versions" "$PACK_VERSION" "$(echo "$S1" | jqv snapshot.content_versions.$PACK_ID)"
check "cursor returned" "yes" "$([ -n "$(echo "$S1" | jqv cursor)" ] && echo yes || echo no)"

echo "=============== 6. idempotency ==============="
S2=$(curl -s "${hdr[@]}" "${auth[@]}" "${kid[@]}" -X POST "$BASE/sync" -d "$BODY")
check "replay accepted again" "2" "$(echo "$S2" | php -r '$j=json_decode(stream_get_contents(STDIN),true); echo count($j["accepted"] ?? []);')"
check "replay did not add stars" "2" "$(echo "$S2" | jqv snapshot.child.total_stars)"
check "replay did not add coins" "20" "$(echo "$S2" | jqv snapshot.child.star_coins)"

echo "=============== 7. forged claims ==============="
EV3=$(php -r "printf(\"%04x%04x-%04x-4%03x-8%03x-%04x%04x%04x\", mt_rand(0,0xffff),mt_rand(0,0xffff),mt_rand(0,0xffff),mt_rand(0,0xfff),mt_rand(0,0xfff),mt_rand(0,0xffff),mt_rand(0,0xffff),mt_rand(0,0xffff));")
FORGE=$(cat <<JSON
{"events":[{"id":"$EV3","seq":3,"type":"mission_completed","client_ts":"$NOW","payload":{"mission_id":$MISSION_ID,"pack_version":$PACK_VERSION,"score":99,"stars":3,"total":99,"time_spent":999999,
 "answers":[{"question_id":$Q1,"response":{"option_id":$W3}}]}}]}
JSON
)
S3=$(curl -s "${hdr[@]}" "${auth[@]}" "${kid[@]}" -X POST "$BASE/sync" -d "$FORGE")
check "forged 3-star claim scored 0" "2" "$(echo "$S3" | jqv snapshot.child.total_stars)"

echo "=============== 8. quarantine ==============="
EV4=$(php -r "printf(\"%04x%04x-%04x-4%03x-8%03x-%04x%04x%04x\", mt_rand(0,0xffff),mt_rand(0,0xffff),mt_rand(0,0xffff),mt_rand(0,0xfff),mt_rand(0,0xfff),mt_rand(0,0xffff),mt_rand(0,0xffff),mt_rand(0,0xffff));")
BAD_EVENTS='{"events":[{"id":"'$EV4'","seq":4,"type":"mission_completed","client_ts":"2031-01-01T00:00:00Z","payload":{"mission_id":1}},{"id":"not-a-uuid","seq":5,"type":"session_ended","client_ts":"'$NOW'","payload":{"seconds":10}}]}'
S4=$(curl -s "${hdr[@]}" "${auth[@]}" "${kid[@]}" -X POST "$BASE/sync" -d "$BAD_EVENTS")
check "future clock quarantined" "clock" "$(echo "$S4" | jqv rejected.0.reason)"
check "non-uuid id rejected" "invalid_id" "$(echo "$S4" | jqv rejected.1.reason)"

echo "=============== 9. screen time + parent pin ==============="
ST=$(curl -s "${hdr[@]}" "${auth[@]}" -X PATCH "$BASE/children/$CHILD_ID/screen-time" -d '{"minutes":30}')
check "screen time set" "30" "$(echo "$ST" | jqv child.daily_time_limit_minutes)"
PIN=$(curl -s "${hdr[@]}" "${auth[@]}" -X POST "$BASE/parent/pin/verify" -d '{"pin":"1234"}')
check "starter pin verifies" "yes" "$([ -n "$(echo "$PIN" | jqv token.access_token)" ] && echo yes || echo no)"
check "pin hash returned for offline gate" "yes" "$(echo "$PIN" | jqv pin_hash | grep -q '^\$2y\$' && echo yes || echo no)"
BADPIN=$(curl -s "${hdr[@]}" "${auth[@]}" -X POST "$BASE/parent/pin/verify" -d '{"pin":"0000"}')
check "wrong pin rejected" "pin_incorrect" "$(echo "$BADPIN" | jqv error.code)"

echo "=============== 10. second-device catch-up ==============="
SNAP=$(curl -s "${hdr[@]}" "${auth[@]}" "$BASE/children/$CHILD_ID/snapshot")
check "snapshot endpoint agrees" "2" "$(echo "$SNAP" | jqv snapshot.child.total_stars)"
check "entitlement present" "none" "$(echo "$SNAP" | jqv snapshot.entitlement.status)"

echo "=============== 11. parent report ==============="
REPORT=$(curl -s "${hdr[@]}" "${auth[@]}" "$BASE/parent/children/$CHILD_ID/report?range=7d")
check "report covers seven days" "7" "$(echo "$REPORT" | jqv range_days)"
check "report counts the questions answered" "4" "$(echo "$REPORT" | jqv overview.questions_answered)"
check "report computes accuracy" "50" "$(echo "$REPORT" | jqv overview.accuracy_percent)"
check "report lists both attempts" "2" "$(echo "$REPORT" | php -r '$j=json_decode(stream_get_contents(STDIN),true); echo count($j["history"] ?? []);')"
check "report names a question that was missed" "true" "$(echo "$REPORT" | jqv support.has_struggle)"
check "another family cannot read it" "child_not_found" "$(curl -s "${hdr[@]}" "${auth[@]}" "$BASE/parent/children/999999/report" | jqv error.code)"

echo "=============== 12. television sign-in ==============="
TVCODE=$(curl -s "${hdr[@]}" -H "X-Device-Id: tv-smoke-0001" -H "X-Platform: android-tv" -X POST "$BASE/auth/device/code")
CODE=$(echo "$TVCODE" | jqv code)
check "tv is given a code" "6" "${#CODE}"
check "tv is told where to approve it" "yes" "$([ -n "$(echo "$TVCODE" | jqv approve_url)" ] && echo yes || echo no)"
check "code is pending before anyone approves" "pending" "$(curl -s "${hdr[@]}" "$BASE/auth/device/poll?code=$CODE" | jqv status)"
check "a stranger cannot approve it" "401" "$(curl -s -o /dev/null -w '%{http_code}' "${hdr[@]}" -X POST "$BASE/auth/device/approve" -d '{"code":'\"$CODE\"'}')"
check "the signed-in phone approves it" "true" "$(curl -s "${hdr[@]}" "${auth[@]}" -X POST "$BASE/auth/device/approve" -d '{"code":'\"$CODE\"'}' | jqv ok)"
TVCLAIM=$(curl -s "${hdr[@]}" -H "X-Device-Id: tv-smoke-0001" "$BASE/auth/device/poll?code=$CODE")
check "tv claims a token" "approved" "$(echo "$TVCLAIM" | jqv status)"
TVTOKEN=$(echo "$TVCLAIM" | jqv token.access_token)
check "the token works" "$EMAIL" "$(curl -s "${hdr[@]}" -H "Authorization: Bearer $TVTOKEN" -H "X-Device-Id: tv-smoke-0001" "$BASE/auth/me" | jqv guardian.email)"
check "the tv sees the family children" "1" "$(curl -s "${hdr[@]}" -H "Authorization: Bearer $TVTOKEN" -H "X-Device-Id: tv-smoke-0001" "$BASE/children" | php -r '$j=json_decode(stream_get_contents(STDIN),true); echo count($j["children"] ?? []);')"
check "a code cannot be claimed twice" "used" "$(curl -s "${hdr[@]}" "$BASE/auth/device/poll?code=$CODE" | jqv status)"
check "an invented code is not approved" "expired" "$(curl -s "${hdr[@]}" "$BASE/auth/device/poll?code=ZZZZZZ" | jqv status)"

echo
echo "$PASS passed, $FAIL failed"
exit $([ "$FAIL" -eq 0 ] && echo 0 || echo 1)
