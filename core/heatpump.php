<?php
/*
 * OPJGraph - Easy charts for openHAB and MySQL
 *
 * @license        The MIT License (MIT). See LICENSE.txt
 * @author         Miika Jukka <miikajukka@gmail.com>
 *
 */
try {


require_once 'opjgraph.inc';

$chartconf = "../config/heatpump.ini";
$starttime = date("Y-m-d H:i:s", mktime(date("H")+1, 0, 0, date("m")  , date("d")-1, date("Y")));
$endtime = date("Y-m-d H:i:s", mktime(date("H")+1, 0, 0, date("m")  , date("d"), date("Y")));

$opjgraph = new OPJGraph($chartconf);
$charts = $opjgraph->getChartConfs();

$returnArray = array();

foreach ($charts as $chart) {
    foreach ($chart['items'] as $item => $params) {	
		$data = $opjgraph->database->getItemData($item, $starttime, $endtime);
        //$returnArray[$params['type']][$item][$params['title']][$params['color']] = $data;
        $returnArray[$params['type']]['name'] = $item;
        $returnArray[$params['type']]['title'] = $params['title'];
        $returnArray[$params['type']]['color'] = $params['color'];
        $returnArray[$params['type']]['values'] = $data;
    }
}  

header('Content-Type: application/json');
echo json_encode($returnArray);
    
$opjgraph->database->close();

} catch (Exception $ex) {
	echo($ex);
} catch (Error $er) {
	echo($er);
}
?>