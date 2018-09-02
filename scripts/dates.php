<?php
try {

require_once ('opjgraph.inc');

$opjgraph = new OPJGraph('./config/line.opjgraph.ini');
$calendar = $opjgraph->getDatesFromDb();

} catch (Exception $ex) {
	//echo($ex);
} catch (Error $er) {
	//echo($er);
} catch (Warning $warn) {
	echo($warn . "hello");
}
?>