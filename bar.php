<?php
/*
 * OPJGraph - Easy charts for openHAB and MySQL
 *
 * @license        The MIT License (MIT). See LICENSE.txt
 * @author         Miika Jukka <miikajukka@gmail.com>
 * 
 */

try {

require_once ('src/jpgraph.php');
require_once ('src/jpgraph_bar.php');
require_once ('core/opjgraph.inc');

// Defaults
$chartconf = "./config/bar.ini";
$starttime = date("Y-m-d H:i:s", mktime(0, 0, 0, date("m")  , date("d")-7, date("Y")));
$endtime = date("Y-m-d H:i:s", mktime(23, 59, 59, date("m")  , date("d")-1, date("Y")));

if (!http_response_code()) JpGraphError::SetImageFlag(false);

$opjgraph = new OPJGraph($chartconf);
$charts = $opjgraph->getChartConfs();

#if (isset($chart))

$plotarray = array();

foreach ($charts as $chart) {

	if (isset($chart['period'])) $starttime = date("Y-m-d H:i:s", mktime(0, 0, 0, date("m"), date("d") - $chart['period'], date("Y")));

	// New graph
	$graph = new Graph($chart['sizev'], $chart['sizeh']);
	$graph->clearTheme();
	$graph->SetScale('textlin');
	$graph->SetMargin(50,50,50,80);
 
	// Legend
	$graph->legend->SetPos(0.33,0.91,'left','top');
	$graph->legend->SetColumns($chart['legendcols']);

	// Title
	$graph->title->Set($chart['title']);
	$graph->title->SetFont(FF_DV_SERIF, FS_BOLD, 14);

	// MySQL query and graph creation
	foreach ($chart['items'] as $item => $params) {
	
		$data = $opjgraph->database->getItemData($item, $starttime, $endtime);

		foreach ($data as $time => $value) {
			$days[date("d.m", $time)][] = $value;
		}
		foreach ($days as $day => $value) {
			$values = array_values($value);
			$average = array_sum($values) / count(array_filter($values));
			$datay[] = $average;
			$datax[] = $day;
		}	
		if ($data) {
			$b = new BarPlot($datay);
			$b->SetFillColor($params['color']);
			$b->SetLegend($params['title']);
			$b->value->Show();
			$b->SetValuePos('max');
			$plotarray[] = $b;		
		}
		unset($data, $datay, $days, $values, $averages);
	}
		
	// Y- and X-axis
	$graph->yaxis->title->Set($chart['yaxistitle']); 
	$graph->xaxis->SetTickLabels($datax);
}

$gbarplot = new GroupBarPlot($plotarray);
$graph->Add($gbarplot);
$graph->Stroke();

} catch (JpGraphException $jge) {
	$jge->Stroke();
} catch (Exception $ex) {
	throw new JpGraphException($ex->getMessage() . "\n");
} catch (Error $er) {
	throw new JpGraphException($er->getMessage() . "\n");
}
?>
