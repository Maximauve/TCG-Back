#!/bin/bash

# Redirect output to stderr.
exec 1>&2

# Block list file location: Update this to point to your own list
blockListFile=".git/hooks/blocked/list.txt"

# Terminal colors and formats
lightRed='\033[1;31m'
green='\033[0;32m'
yellow='\033[1;33m'
clearFormat='\033[0m'
underline='\033[4m'

# Flags
errorFoundFlag=0

confirm () {
  printf '%b\n' "$yellow"
  read -rp "Commit anyway? (y/n) " yn </dev/tty # Enable user input.
  printf '%b' "$clearFormat"

  if echo "$yn" | grep "^[Yy]$"; then
    printf "%bProceeding with commit...%b\n" "$green" "$clearFormat" # The user wants to continue.
  else
    exit 1 # The user does not wish to continue, so rollback.
  fi
}

# Finds the section of the diff that has the blocked phrase
offendingDiff () {
  # Skip this method if there is no parameter passed in
  if [ -z "$1" ]; then
    return 0
  fi

  git diff --cached --color=always | tac | awk "/$1/{flag=1}/diff/{flag=0}flag" | tac
}

checkRegexp () {
  # Skip this method if there is no parameter passed in
  if [ -z "$1" ]; then
    return 0
  fi

  # Here we prepend the regex with [^-] which will ensure we won't be blocked committing changes that remove the blocked phrase
  regex="^[^-].*$1"

  if [ "$(git diff --cached | grep -c "$regex")" != 0 ]; then
    printf "%b\nError: you are attempting to commit the blocked phrase: %b%s:%b\n" "$lightRed" "$underline" "$1" "$clearFormat"
    # Highlights and adds error arrows to the printout
    offendingDiff "$1" | sed "s/$1/$(printf "%b$1" "$underline")/" | sed "/$1/ s/$/ $(printf "%b<<<<<<<%b" "$lightRed" "$clearFormat")/"
    errorFoundFlag=1
  fi
}

checkBlockList () {
  while read -r line
  do
    checkRegexp "$line"
  done < "$blockListFile"
  if [ "$errorFoundFlag" = 1 ]; then
    confirm
  fi
}

checkBlockList