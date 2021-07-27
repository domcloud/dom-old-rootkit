ipset create whitelist hash:ip
ipset create whitelist-v6 hash:ip family inet6
sh ./whitelist.sh
