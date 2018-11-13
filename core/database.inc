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
                        throw new Exception("Error in database settings. Missing value for " . $key);
                    } elseif ($key == "type") {
                        $this->type = $value;
                    } else {
                        $this->db_conf[$key] = $value;
                    }
                }
            }
        }
    }

    // Create connection
    public function createConnection($type) {
        if (!$type) {
            throw new Exception("Database type not set");
        } elseif ($type == "mysql") {
            $connection = new mysqli($this->db_conf['host'], $this->db_conf['uname'],
                                       $this->db_conf['pw'], $this->db_conf['name']);
            if ($connection->connect_errno) {
                throw new Exception($connection->connect_error);
            }
        } else {
            throw new Exception("Unsupported database");
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
			            $data[strtotime($row["time"])] = $row["value"];
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
}
?>