#!/bin/bash

#=== Configuration ===

SPLUNK_HEC_URL="https://localhost:8088/services/collector/event"
SPLUNK_HEC_TOKEN="a2225844-b461-444b-9b19-b3761ffd166c"
ALERT_FILE="/var/log/snort/alert"
SOURCETYPE="snort_alerts"

#=== Format and send to Splunk ===
tail -n0 -F "$ALERT_FILE" | while read -r EVENT; do
        curl -k "$SPLUNK_HEC_URL" -H "Authorization: Splunk $SPLUNK_HEC_TOKEN" -H "Content-Type: application/json" -d "{\"event\": \"$EVENT\", \"sourcetype\": \"$SOURCETYPE\"}"

  # If curl succeeded, delete the first line (the one we just sent)
  if [ $? -eq 0 ]; then
    sed -i '1d' "$ALERT_FILE"
  else
    echo "[$(date +'%Y-%m-%dT%H:%M:%S')] ERROR sending alert: $EVENT" >&2
  fi
done