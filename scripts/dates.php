<?php
/*
 * OPJGraph - Easy charts for openHAB and MySQL
 *
 * @license        The MIT License (MIT). See LICENSE.txt
 * @author         Miika Jukka <miikajukka@gmail.com>
 *
 */

try {

require_once ('opjgraph.inc');

$opjgraph = new OPJGraph('./config/database.ini', false);
$calendar = $opjgraph->getDatesFromDb();

} catch (Exception $ex) {
	//echo($ex);
} catch (Error $er) {
	//echo($er);
}
?>
