<?php
/*
 * OPJGraph - Easy charts for openHAB and MySQL
 *
 * @license        The MIT License (MIT). See LICENSE.txt
 * @author         Miika Jukka <miikajukka@gmail.com>
 * 
 */

try {

require_once 'src/jpgraph.php';
require_once 'src/jpgraph_line.php';
require_once 'src/jpgraph_date.php';
require_once 'src/jpgraph_mgraph.php';
require_once 'core/opjgraph.inc';

// Defaults
$chartconf = "config/line.ini";
$starttime = date("Y-m-d") . " 00:00:00";
$endtime = date("Y-m-d") . " 23:59:59";
$period = "today";
$istoday= true;
$is_cli = false;

if (!http_response_code()) {
	$is_cli = true;
	JpGraphError::SetImageFlag(false);
} else {
	if (isset($_GET["period"])) $period = htmlspecialchars($_GET["period"]);
}

$opjgraph = new OPJGraph($chartconf);
$charts = $opjgraph->getChartConfs();

if ($period == "last24h") {
	$starttime = date("Y-m-d H:i:s", mktime(date("H")+1, 0, 0, date("m")  , date("d")-1, date("Y")));
	$endtime = date("Y-m-d H:i:s", mktime(date("H")+1, 0, 0, date("m")  , date("d"), date("Y")));
	$istoday = true;

} elseif (DateTime::createFromFormat("Y-m-d", $period)) { 
	$time = explode("-", $period, 3);
	$starttime = date("Y-m-d H:i:s", mktime(0, 0, 0, $time[1], $time[2], $time[0]));
	$endtime = date("Y-m-d H:i:s", mktime(23, 59, 59, $time[1], $time[2], $time[0]));
	$istoday = false;
}

$graphs = array();

foreach ($charts as $chart) {
	
	//New graph
	$graph = new Graph($chart['sizev'], $chart['sizeh']);
	$graph->clearTheme();
	$graph->SetScale('datlin',$chart['valuemin'], $chart['valuemax']);
	$graph->SetMargin(50,50,50,($chart['showlegend'] ? 130 : 50));
	$graph->img->SetAntiAliasing(false);

	// Legend
	if (!$chart['showlegend']) {
		$graph->legend->Hide();
	}
	$graph->legend->SetPos(0.05,0.84,'left','top');
	$graph->legend->SetColumns($chart['legendcols']);

	// Title 
	$graph->title->Set($chart['title']);
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
	$graph->xaxis->SetPos('min');
	$graph->xaxis->HideFirstTicklabel();

	// Y-axis
	$graph->yaxis->title->Set($chart['yaxistitle']);
	$graph->ygrid->Show(true, true);

	// MySQL query and graph creation
	foreach ($chart['items'] as $item => $params) {

		$data = $opjgraph->database->getItemData($item, $starttime, $endtime);

		foreach ($data as $time => $value) {
			$datax[] = $time;
			if ($params['type'] == 'state' && $value > 0) {
				$value += ($chart['valuemin'] + 1);
			}
			if ($params['type'] == 'state' && $value == 0) {
				$value += $chart['valuemin'];
			}
			$datay[] = $value;			
		}	
		if ($data) {
			$p = new LinePlot($datay , $datax);
			$p->SetColor($params['color']);
			if ($params['type'] == 'state') {
				$p->SetFillColor($params['color']);
				$p->SetFillFromYMin(TRUE);
				$p->SetStepStyle();		
			}
			$p->SetLegend($opjgraph->createLegend($params, $istoday, $data));
			$graph->Add($p);		
		}
		unset($data, $datax, $datay);
	}
	if ($chart['drawtofile']) {
		$gdImgHandler = $graph->Stroke(_IMG_HANDLER);
		$filepath = $chart['drawtofile'];
		$graph->img->Stream($filepath);
	} else {
		$graphs[] = $graph;
	}
}
if (count($graphs) == 1) {
	$graphs[0]->Stroke();
} elseif (count($graphs) > 1) {
	$mgraph = new MGraph();
	$yoffset = 0;
	$i = 0;
	for($i = 0; $i < count($graphs); ++$i) {
		$mgraph->AddMix($graphs[$i],0,$yoffset);
		$yoffset += $chart['sizeh'] + 10;
	}
	$mgraph->Stroke();
} elseif (count($graphs) == 0) {
	if ($chart['drawtofile']) {
		throw new JpGraphException("Image drawn to file: " . $chart['drawtofile']);
	
	} elseif (!$is_cli) {
		throw new JpGraphException("No charts drawn. Check configuration");
	}
}


} catch (JpGraphException $jge) {
	$jge->Stroke();
} catch (Exception $ex) {
	throw new JpGraphException($ex->getMessage() . "\n");
} catch (Error $er) {
	throw new JpGraphException($er->getMessage() . "\n");
}
?>
