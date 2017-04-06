<?php
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/include/Parking.php';

$parking = new Parking();

if (isset($_SERVER['argv'])) {
    $parking->runCLI($_SERVER['argv']);
}
