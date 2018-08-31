<?php

class OPJGraph {

    private $dbconf,
            $chartconf,
            $items,
            $connection;

    public function __construct($filename) {
        $this->parseConfig($filename);
    }

    # Return chart configuration
    public function getChartConf() {
        return $this->chartconf;
    }

    # Return list of items
    public function getItems() {
        return $this->items;
    }

    # Parse configuration file
    private function parseConfig($filename) {
        $config = parse_ini_file($filename, true);
        if (!$config) {
            throw new Exception ("Error reading configuration file");
        }

        foreach ($config['database'] as $key => $value) {
            if (empty($value)){
                throw new InvalidArgumentException ("Error in database settings.");
            }
            $this->dbconf[$key] = $value;       
        }

        foreach ($config['chart'] as $key => $value) {
            if (empty($value)){
                throw new InvalidArgumentException ("Error in chart settings.");
            }
            $this->chartconf[$key] = $value;       
        }
        foreach ($config['items'] as $value) {
            if (empty($value)){
                throw new InvalidArgumentException ("Error in item settings.");
            }
            $i = 0;
            foreach ($value as $val) {
                $item = explode(":", $val);
        	    if (count($item) != 4 || (array_search("", $item) != false)) {
	            	throw new InvalidArgumentException ("Error in item settings.");
                }
                $this->items[$i]['type'] = $item[0];
                $this->items[$i]['name'] = $item[1];
                $this->items[$i]['color'] = $item[2];
                $this->items[$i]['title'] = $item[3];
                $i++;
            }
        }
    }

    # Create MySQL connection
    function connect() {
        $connection = new mysqli($this->dbconf['dbhost'], $this->dbconf['dbuname'],
                                       $this->dbconf['dbpw'], $this->dbconf['dbname']);
        if ($connection->connect_errno) {
            throw new Exception($connection->connect_error);
        }
        $this->connection = $connection;
    }

    # Close MySQL connection
    public function close() {
        $this->connection->close();
    }


    # Construct MySQL query string
    function getItemData($item, $starttime, $endtime) {
        $query = "SELECT * FROM " . $item['name'] . " WHERE (time BETWEEN '" . $starttime . "' AND '" . $endtime . "')";
        $data = array();

	    if ($result = $this->connection->query($query)) {
		    while($row = $result->fetch_assoc()){
                if ($item['type'] == 'state' && $row['value'] > 0) {
			    	$row['value']++;
			    }
			    $data[strtotime($row["time"])] = $row["value"];
		    }
		    $result->free();
	    }
        return $data;
    }

    # Fetch distinct dates from MySQL and return multidimesional array of dates
    function getDatesFromDb() {
        $this->connect();
        $query = "SELECT DISTINCT YEAR(time) AS year, MONTH(time) AS month, DAY(time) AS day FROM " . $this->dbconf['timetable'];
        $calendar = array();
    
        if ($result = $this->connection->query($query)) {
             while($row = $result->fetch_assoc()) {
	    		$calendar[$row["year"]][$row["month"]][$row["day"]] = $row["day"];
	    	}
	    	$result->free();
        }
        $this->close();
        return $calendar;
    }

    # Construct legend string
    function getLegend($item, $istoday, $data) {
        $string = $item['title'] . " ";
        if ($item['type'] == 'line') {
            $string .= sprintf("%01.1f", $istoday ? end($data) : (array_sum($data) / count(array_filter($data))));
            $string .= " &deg;C";
        } elseif ($item['type'] == 'state') { 
            $string .= $istoday ? (end($data) ? "ON" : "OFF") : "Average not counted"; // TODO
        }
        return $string;
    }
}

?>
