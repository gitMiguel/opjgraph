<?php
/*
 * OPJGraph - Easy charts for openHAB and MySQL
 *
 * @license        The MIT License (MIT). See LICENSE.txt
 * @author         Miika Jukka <miikajukka@gmail.com>
 * 
 */

class Database {

    private $db_conf,
            $type,
            $connection;

    public function __construct($db_conf_file) {
        $this->parseConfig($db_conf_file);
        $this->createConnection($this->type);
    }

    // Close connection
    public function close() {
        $this->connection->close();
    }

    // Parse configuration file
    private function parseConfig($db_conf_file) {
        $config = parse_ini_file($db_conf_file, true);
        if (!$config) {
            throw new Exception("Error reading configuration file");
        }
        foreach ($config as $conf_section => $value) {
            if ($conf_section == 'database') {
                foreach ($value as $key => $value) {
                    if (empty($value)){
                        throw new Exception("Error in database settings. Missing value for \"" . $key . "\"");
                    } elseif ($key == "type") {
                        $this->type = $value;
                    } else {
                        $this->db_conf[$key] = $value;
                    }
                }
            }
        }
        if (empty($this->db_conf)) {
            throw new InvalidArgumentException ("Error parsing configuration file");
        }
    }

    // Create connection
    public function createConnection($type) {
        if (!$type) {
            throw new InvalidArgumentException ("Database type not set");
        } elseif ($type == "mysql") {
            $connection = new mysqli($this->db_conf['host'], $this->db_conf['uname'],
                                       $this->db_conf['pw'], $this->db_conf['name']);
            if ($connection->connect_errno) {
                throw new Exception($connection->connect_error);
            }
        } else {
            throw new Exception("Unsupported database \"" . $type . "\"");
        }
        $this->connection = $connection;
    }

    // Get item data from database
    function getItemData($item,  $starttime, $endtime) {
        $data = array();
        switch ($this->type) {
            case "mysql":
                $query = "SELECT * FROM " . $item . " WHERE (time BETWEEN '" . $starttime . "' AND '" . $endtime . "')";
                if ($result = $this->connection->query($query)) {
		            while($row = $result->fetch_assoc()){
                        $data[] = $row;
		            }
                    $result->free();
                }
                break;
            }
        return $data;
    }

    // Fetch distinct dates from db
    function getDatesFromDb() {
        $calendar = array();
        switch ($this->type) {
            case "mysql":
                $query = "SELECT DISTINCT YEAR(time) AS year, MONTH(time) AS month, DAY(time) AS day FROM " . $this->db_conf['timetable'];
                if ($result = $this->connection->query($query)) {
                    while($row = $result->fetch_assoc()) {
	    		        $calendar[$row["year"]][$row["month"]][$row["day"]] = $row["day"];
	    	        }
	    	        $result->free();
                }
                break;
            }
        return $calendar;
    }

    // Fetch year averages
    function getYearAverages() {
        $data1 = array();
        switch ($this->type) {
            case "mysql":
                $query = "SELECT time , AVG(value) as value FROM " . $this->db_conf['timetable'] . " GROUP BY YEAR(`time`), MONTH(`time`)";
                if ($result = $this->connection->query($query)) {
                    while($row = $result->fetch_assoc()) {
	    		        $data1[] = $row;
	    	        }
	    	        $result->free();
                }
                break;
            }
        return $data1;
    }
}
?>