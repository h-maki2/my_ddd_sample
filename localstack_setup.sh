#!/bin/bash
awslocal sns create-topic --name my-sns-topic
awslocal sqs create-queue --queue-name my-sqs-queue