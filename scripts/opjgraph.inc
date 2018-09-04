<?php

class OPJGraph {

    private $dbconf,
            $charts,
            $connection;

    public function __construct($filename) {
        $this->parseConfig($filename);
        $this->connection = $this->connect();
    }

    # Return chart configuration
    public function getChartConfs() {
        return $this->charts;
    }

    # Parse configuration file
    private function parseConfig($filename) {
        $config = parse_ini_file($filename, true);
        if (!$config) {
            throw new Exception ("Error reading configuration file");
        }
        foreach ($config as $confsection => $value) {
            if ($confsection == 'database') {
                foreach ($value as $key => $value) {
                    if (empty($value)){
                        throw new InvalidArgumentException ("Error in database settings. Missing value for " . $key);
                    }
                    $this->dbconf[$key] = $value;
                }
            } elseif (preg_match("/\bchart.\b/", $confsection)) {
                foreach ($value as $key => $value) {
                    if (is_array($value)) {
                        foreach($value as $name => $value) {
                            //var_dump($name);
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
        if (!$this->charts) {
            throw new InvalidArgumentException ("No chart settings found.");
        }
    }

    # Create MySQL connection
    function connect() {
        $connection = new mysqli($this->dbconf['dbhost'], $this->dbconf['dbuname'],
                                       $this->dbconf['dbpw'], $this->dbconf['dbname']);
        if ($connection->connect_errno) {
            throw new Exception ($connection->connect_error);
        }
        return $connection;
    }

    # Close MySQL connection
    public function close() {
        $this->connection->close();
    }

    # Get item data from database
    function getItemData($item,  $starttime, $endtime) {
        $query = "SELECT * FROM " . $item . " WHERE (time BETWEEN '" . $starttime . "' AND '" . $endtime . "')";
        $data = array();

	    if ($result = $this->connection->query($query)) {
		    while($row = $result->fetch_assoc()){
			    $data[strtotime($row["time"])] = $row["value"];
		    }
		    $result->free();
	    }
        return $data;
    }

    # Fetch distinct dates from MySQL and return multidimesional array of dates
    function getDatesFromDb() {
        $query = "SELECT DISTINCT YEAR(time) AS year, MONTH(time) AS month, DAY(time) AS day FROM " . $this->dbconf['timetable'];
        $calendar = array();
    
        if ($result = $this->connection->query($query)) {
             while($row = $result->fetch_assoc()) {
	    		$calendar[$row["year"]][$row["month"]][$row["day"]] = $row["day"];
	    	}
	    	$result->free();
        }
        return $calendar;
    }

    # Construct legend string
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

    # Calculate ON time for state item
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
