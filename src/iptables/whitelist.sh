#!/bin/bash

SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" &> /dev/null && pwd )"

ipset flush whitelist
while read p; do
  if [[ $p != "#"* ]];
  then
    FFI=`dig +short $(echo $p | xargs) | grep -v '\.$'`
    while read -r q; do
        ipset add whitelist $q
    done < <(echo $FFI| sed 's/ /\n/g')
  fi
done <"$SCRIPT_DIR/whitelist.conf"