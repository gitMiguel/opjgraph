<?php
/*
 * OPJGraph - Easy charts for openHAB and MySQL
 *
 * @license        The MIT License (MIT). See LICENSE.txt
 * @author         Miika Jukka <miikajukka@gmail.com>
 * 
 */

require_once 'database.inc';

class OPJGraph {

    public  $database;

    private $dbconf,
            $charts,
            $connection;

    public function __construct($chartsconf) {
        $this->database = new Database("./config/database.ini");
        $this->parseConfig($chartsconf);
    }

    // Return chart configurations
    public function getChartConfs() {
        return $this->charts;
    }

    // Parse configuration file
    private function parseConfig($filename) {
        $config = parse_ini_file($filename, true);
        if (!$config) {
            throw new Exception ("Error reading configuration file");
        }
        foreach ($config as $confsection => $value) {
            if (preg_match("/\bchart.\b/", $confsection)) {
                foreach ($value as $key => $value) {
                    if (is_array($value)) {
                        foreach($value as $name => $value) {
                            $values = explode(":", $value);
                            if (count($values) > 4 || count($values) < 3 || (array_search("", $values) != false)) {
                                throw new InvalidArgumentException ("Error in item settings.");
                            }
                            $this->charts[$confsection][$key][$name]['type'] = $values[0];
                            $this->charts[$confsection][$key][$name]['color'] = $values[1];
                            $this->charts[$confsection][$key][$name]['title'] = $values[2];
                            if (count($values) == 4) {
                                $this->charts[$confsection][$key][$name]['unit'] = $values[3];
                            }
                        }
                    } else {
                        $this->charts[$confsection][$key] = $value;
                    }
                }
            }
        }
    }

    // Construct legend string
    function createLegend($params, $istoday, $data) {
        $values = array_values($data);
        $string = $params['title'] . " ";
        if ($params['type'] == 'line') {
            $string .= sprintf("%01.1f", $istoday ? end($values) : (array_sum($values) / count(array_filter($values))));
            $string .= " " . $params['unit'];
        } elseif ($params['type'] == 'state') {
            $string .= $istoday ? (end($values) ? "ON" : "OFF") : $this->calculateOnTime($data);
        }
        return $string;
    }

    // Calculate ON time for state item
    function calculateOnTime($data) {
        $total = 0;
        $start = 0; 
        foreach ($data as $time => $value) {
            if (($value > 0) && ($start == 0)) {
                $start = $time;
            } elseif (($value < 1) && ($start > 0)) {
                $total += $time - $start;
                $start = 0;
            }
        }
        return gmdate("H:i:s", $total);
    }
}
?>
