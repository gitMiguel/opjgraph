<?php // content="image/png"

try {

require_once ('src/jpgraph.php');
require_once ('src/jpgraph_line.php');
require_once ('src/jpgraph_date.php');
include 'opjgraph.inc';

$opjgraph = new OPJGraph("opjgraph.ini");
$chart = $opjgraph->getChartConf();
$items = $opjgraph->getItems();

// Get parameters
if (!isset($_GET["period"])) {
	$period = "today";
	$istoday = true;
} else {
	$period = $_GET["period"];
}

if ($period == "last24h") {
	$starttime = date("Y-m-d H:i:s", mktime(date("H")+1, 0, 0, date("m")  , date("d")-1, date("Y")));
	$endtime = date("Y-m-d H:i:s", mktime(date("H")+1, 0, 0, date("m")  , date("d"), date("Y")));
	$istoday = true;

} elseif (DateTime::createFromFormat("Y-m-d", $period)) { 
	$time = explode("-", $period, 3);
	$starttime = date("Y-m-d H:i:s", mktime(0, 0, 0, $time[1], $time[2], $time[0]));
	$endtime = date("Y-m-d H:i:s", mktime(23, 59, 59, $time[1], $time[2], $time[0]));
	$istoday = false;

} else {
	$starttime = date("Y-m-d") . " 00:00:00";
	$endtime = date("Y-m-d") . " 23:59:59";
	$istoday= true;
}

// New graph
$graph = new Graph($chart['sizev'], $chart['sizeh']);
$graph->clearTheme();
$graph->SetScale('datlin',0,90);
$graph->SetMargin(50,50,50,($chart['legend'] == 'show' ? 130 : 50));
$graph->img->SetAntiAliasing(false);

// Legend
if ($chart['legend'] == 'hide') {
	$graph->legend->Hide();
}
$graph->legend->SetPos(0.05,0.84,'left','top');
$graph->legend->SetColumns($chart['legendcols']);

// Title 
$graph->title->Set($chart['title']); // . " " . date("Y.m.d", strtotime($endtime)));
$graph->title->SetFont(FF_DV_SERIF, FS_BOLD, 14);
$graph->title->SetMargin(10);

// X-axis
if ($period != "last24h") {
	$graph->xaxis->scale->SetDateAlign( DAYADJ_1 );
} else {
	$graph->xaxis->scale->SetTimeAlign( HOURADJ_1 );
}
$graph->xaxis->scale->SetDateFormat('H:i');
$graph->xaxis->scale->ticks->Set(60*60,30*60);

// Y-axis
$graph->yaxis->title->Set($chart['yaxistitle']);
$graph->yaxis->HideFirstTicklabel();
$graph->ygrid->Show(true, true);

// MySQL query and graph creation
$opjgraph->connect();

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
$opjgraph->close();
$graph->Stroke();

} catch (JpGraphException $jge) {
	$jge->Stroke();
} catch (Exception $ex) {
	throw new JpGraphException($ex);
} catch (Error $er) {
	throw new JpGraphException($er);
}

?>
