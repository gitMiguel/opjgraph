<?php
/*
 * OPJGraph - Easy charts for openHAB and MySQL
 *
 * @license        The MIT License (MIT). See LICENSE.txt
 * @author         Miika Jukka <miikajukka@gmail.com>
 *
 */
require_once 'database.inc';

$database = new Database('../config/database.ini');
$calendar = $database->getDatesFromDb();

header('Content-Type: application/json');
echo json_encode($calendar);

$database->close();
?>
