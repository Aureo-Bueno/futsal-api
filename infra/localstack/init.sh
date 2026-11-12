#!/usr/bin/env bash
set -euo pipefail

TOPIC_TEAM=$(awslocal sns create-topic --name futsal-team-events --query TopicArn --output text)
TOPIC_MATCH=$(awslocal sns create-topic --name futsal-match-events.fifo --attributes FifoTopic=true --query TopicArn --output text)

queue_url() {
  local name="$1"
  local fifo="${2:-false}"
  local attrs=""
  if [ "$fifo" = "true" ]; then
    attrs="--attributes FifoQueue=true"
  fi
  awslocal sqs create-queue --queue-name "$name" $attrs --query QueueUrl --output text
}

queue_arn() {
  awslocal sqs get-queue-attributes \
    --queue-url "$1" \
    --attribute-names QueueArn \
    --query Attributes.QueueArn \
    --output text
}

DLQ_STANDINGS=$(queue_url standings-match-events-dlq.fifo true)
DLQ_MATCH=$(queue_url match-team-events-dlq false)
QUEUE_STANDINGS=$(queue_url standings-match-events.fifo true)
QUEUE_MATCH=$(queue_url match-team-events false)

DLQ_STANDINGS_ARN=$(queue_arn "$DLQ_STANDINGS")
DLQ_MATCH_ARN=$(queue_arn "$DLQ_MATCH")

awslocal sqs set-queue-attributes --queue-url "$QUEUE_STANDINGS" \
  --attributes "{\"RedrivePolicy\":\"{\\\"deadLetterTargetArn\\\":\\\"$DLQ_STANDINGS_ARN\\\",\\\"maxReceiveCount\\\":\\\"5\\\"}\"}"

awslocal sqs set-queue-attributes --queue-url "$QUEUE_MATCH" \
  --attributes "{\"RedrivePolicy\":\"{\\\"deadLetterTargetArn\\\":\\\"$DLQ_MATCH_ARN\\\",\\\"maxReceiveCount\\\":\\\"5\\\"}\"}"

STANDINGS_ARN=$(queue_arn "$QUEUE_STANDINGS")
MATCH_ARN=$(queue_arn "$QUEUE_MATCH")

awslocal sns subscribe \
  --topic-arn "$TOPIC_MATCH" \
  --protocol sqs \
  --notification-endpoint "$STANDINGS_ARN" \
  --attributes '{"RawMessageDelivery":"true","FilterPolicy":"{\"eventType\":[\"match.finished\",\"match.cancelled\"]}"}'

awslocal sns subscribe \
  --topic-arn "$TOPIC_TEAM" \
  --protocol sqs \
  --notification-endpoint "$MATCH_ARN" \
  --attributes '{"RawMessageDelivery":"true","FilterPolicy":"{\"eventType\":[\"team.created\",\"team.updated\",\"team.deactivated\",\"player.registered\",\"player.transferred\",\"player.removed\"]}"}'
