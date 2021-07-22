<?php

// validate request

if (!isset($_GET['secret'], $_GET['action'])) exit;
if ($_GET['secret'] !== $_SERVER['SECRET_TOKEN']) exit;
if ($_GET['action'] === 'refresh') {
    exec($_SERVER['IPTABLESV6_RELOAD']);
    echo "Updated for IPv6\n";
} else if ($_GET['action'] === 'add_user') {
    $iptables_file = file_get_contents($_SERVER['IPTABLESV6_PATH']);
    if (!$iptables_file) {
        die("ERROR: config not found\n");
    }
    $theword = "-A OUTPUT -m owner --uid-owner $_GET[user] -j REJECT\n";
    $replaced_file = str_replace($theword, "", $iptables_file);
    $replaced_file = str_replace("# Limiter goes down here\n", "# Limiter goes down here\n" . $theword, $replaced_file);
    if ($iptables_file === $replaced_file) {
        die("Updated, nothing changed\n");
    }
    if (file_put_contents($_SERVER['IPTABLESV6_PATH'], $replaced_file, LOCK_EX) === false) {
        die("ERROR: unable to write config\n");
    }
    exec($_SERVER['IPTABLESV6_RELOAD']);
    echo "Updated for IPv6\n";
} else if ($_GET['action'] === 'del_user') {
    $iptables_file = file_get_contents($_SERVER['IPTABLESV6_PATH']);
    if (!$iptables_file) {
        die("ERROR: config not found\n");
    }
    $replaced_file = str_replace("-A OUTPUT -m owner --uid-owner $_GET[user] -j REJECT\n", "", $iptables_file);
    if ($iptables_file === $replaced_file) {
        die("Updated, Nothing changed\n");
    }
    if (file_put_contents($_SERVER['IPTABLESV6_PATH'], $replaced_file, LOCK_EX) === false) {
        die("ERROR: unable to write config\n");
    }
    exec($_SERVER['IPTABLESV6_RELOAD']);
    echo "Updated for IPv6\n";
}
