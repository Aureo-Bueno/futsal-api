#!/usr/bin/env bash
set -euo pipefail

BASE=http://localhost:8000/api
TOKEN=$(curl -s -X POST "$BASE/login" \
  -H 'Content-Type: application/json' -H 'Accept: application/json' \
  -d '{"email":"admin@example.com","password":"password"}' | jq -r '.token // .access_token // empty')

if [ -z "$TOKEN" ]; then
  echo "FAIL: login"
  exit 1
fi

AUTH=(-H "Authorization: Bearer $TOKEN" -H 'Content-Type: application/json' -H 'Accept: application/json')

HOME_ID=$(curl -s -X POST "$BASE/teams" "${AUTH[@]}" -d '{"name":"Home FC"}' | jq -r '.id')
AWAY_ID=$(curl -s -X POST "$BASE/teams" "${AUTH[@]}" -d '{"name":"Away FC"}' | jq -r '.id')

HOME_PLAYER=$(curl -s -X POST "$BASE/teams/$HOME_ID/players" "${AUTH[@]}" \
  -d "{\"name\":\"Home Player\",\"jersey_number\":10}" | jq -r '.id')

# aguarda projeção de known_teams/known_players no match-service
sleep 5

MATCH_ID=$(curl -s -X POST "$BASE/matches" "${AUTH[@]}" \
  -d "{\"home_team_id\":\"$HOME_ID\",\"away_team_id\":\"$AWAY_ID\",\"scheduled_at\":\"2026-11-20T20:00:00+00:00\"}" | jq -r '.id')

curl -s -X POST "$BASE/matches/$MATCH_ID/start" "${AUTH[@]}" -d '{}' > /dev/null

for g in 5 12; do
  for i in $(seq 1 15); do
    code=$(curl -s -o /dev/null -w '%{http_code}' -X POST "$BASE/matches/$MATCH_ID/goals" "${AUTH[@]}" \
      -d "{\"team_id\":\"$HOME_ID\",\"player_id\":\"$HOME_PLAYER\",\"minute\":$g,\"own_goal\":false}")
    [ "$code" = "200" ] && break
    sleep 2
  done
done

curl -s -X POST "$BASE/matches/$MATCH_ID/finish" "${AUTH[@]}" -d '{}' > /dev/null

for i in $(seq 1 30); do
  POINTS=$(curl -s "$BASE/standings" -H "Authorization: Bearer $TOKEN" -H 'Accept: application/json' | jq -r ".[] | select(.team_id==\"$HOME_ID\") | .points // 0")
  if [ "$POINTS" = "3" ]; then
    echo "OK: standings updated"
    exit 0
  fi
  sleep 2
done

echo "FAIL: standings not updated"
exit 1
