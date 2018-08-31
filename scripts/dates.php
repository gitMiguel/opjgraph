<?php
try {

require_once 'opjgraph.inc';

$myopjgraph = new MyOPJGraph('opjgraph.ini');
$calendar = $myopjgraph->getDatesFromDb();

} catch (Exception $ex) {
	//echo($ex);
} catch (Error $er) {
	//echo($er);
}
?>