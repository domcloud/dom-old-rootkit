<?php

require_once "../vendor/autoload.php";
require "validator.php";

$dotenv = Dotenv\Dotenv::createImmutable(realpath(__DIR__.'/../../'));
$dotenv->load();

// validate request

if (!isset($_GET['secret'], $_GET['domain'])) exit;
if ($_GET['secret'] !== $_SERVER['SECRET_TOKEN']) exit;