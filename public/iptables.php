<?php

require_once "../vendor/autoload.php";

$dotenv = Dotenv\Dotenv::createImmutable(realpath(__DIR__ . '/../'));
$dotenv->load();

include "../src/iptables/main.php";
include "../src/iptables/mainv6.php";
