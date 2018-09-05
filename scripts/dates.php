<?php
/*
 * OPJGraph - Easy charts for openHAB and MySQL
 *
 * @lastmodified   Wed Sep 05 2018
 * @license        The MIT License (MIT). See LICENSE.txt
 * @author         Miika Jukka <miikajukka@gmail.com>
 * @version        0.1
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
