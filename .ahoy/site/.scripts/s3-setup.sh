#!/usr/bin/env bash

set -e

if [ ! -f ~/.s3curl ]; then
  if [[ -z "$AWS_ID" || -z "$AWS_KEY" ]]; then
    echo "AWS environment variables are not set and there's no .s3curl file available. Aborting."
    exit 1;
  fi
fi

if [ ! -f ~/.s3curl ]; then
  echo "
%awsSecretAccessKeys = (
  local => {
    id => '$AWS_ID',
    key => '$AWS_KEY',
  }
);" > ~/.s3curl
chmod 600 ~/.s3curl
else
  echo ".s3curl file is available. Skipping"
fi
