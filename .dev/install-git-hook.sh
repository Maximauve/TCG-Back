#!/bin/bash

mkdir -p ./.git/hooks/blocked && cp ./.dev/blocked_words.txt "$_"
cp ./.dev/pre-commit.sh ./.git/hooks/pre-commit