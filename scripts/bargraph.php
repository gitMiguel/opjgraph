<?php // content="image/png""

try {

require_once ('src/jpgraph.php');
require_once ('src/jpgraph_bar.php');
require_once ('opjgraph.inc');
 
$opjgraph = new OPJGraph('../config/bar.opjgraph.ini');

$chart = $opjgraph->getChartConf();
$items = $opjgraph->getItems();

$starttime = date("Y-m-d H:i:s", 0, 0, 0, date("m")  , date("d")-8, date("Y"));
$endtime = date("Y-m-d H:i:s", 23, 59, 59, date("m")  , date("d")-1, date("Y"));

// New graph
$graph = new Graph($chart['sizev'], $chart['sizeh']);
$graph->SetScale('textlin');
 
$graph->SetMargin(50,50,50,50);
 
// Create a bar pot
$bplot = new BarPlot($datay);
 
// Title
$graph->title->Set($chart['title']);
$graph->title->SetFont(FF_DV_SERIF, FS_BOLD, 14);
 
// MySQL query and graph creation
foreach ($items as $item) {
	
	$data = $opjgraph->getItemData($item, $starttime, $endtime);

	foreach ($data as $time => $value) {
		$datax[] = $time;
		$datay[] = $value;
	}
	
	if ($data) {
		$p = new LinePlot($datay , $datax);
		$p->SetColor($item['color']);
		if ($item['type'] == 'state') {
			$p->SetFillColor($item['color']);
			$p->SetFillFromYMin(TRUE);
			$p->SetStepStyle();		
		}
		$p->SetLegend($opjgraph->getLegend($item, $istoday, $datay, $data));
		$graph->Add($p);		
	}
	unset($data, $datax, $datay);
}

$bplot->SetFillColor('orange');

$plotgroup;


$gbarplot = new  GroupBarPlot($plotarray);
$graph->Add($gbarplot);
$graph->Stroke();

} catch (JpGraphException $jge) {
	$jge->Stroke();
} catch (Exception $ex) {
	throw new JpGraphException($ex);
} catch (Error $er) {
	throw new JpGraphException($er);
}

?>