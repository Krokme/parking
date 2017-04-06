<?php

class Parking
{
    private        $db;
    private static $db_name = 'parking.db';

    public function __construct()
    {
		if (!file_exists(self::$db_name)) {
			exit('Please setup db, use command "composer run-script setup"');
		}
		try {
			$this->db = new SQLite3(self::$db_name);
		} catch (Exception $e) {
			exit('Can\'t access ' . self::$db_name . ' ' . $e->getMessage());
		}
    }

	public function runCLI($argv)
	{
		array_shift($argv);

        // setup db
        if (isset($argv[0]) && $argv[0] == 'setup') {
            $this->response($this->setup());
        }

        // get info about the tarifs
        if (sizeof($argv) == 0) {
            $this->response($this->zones());
        }

        // get info about parked car
        if (sizeof($argv) == 2) {
            $this->response($this->parked($argv));
        }

        // park the car
        if (sizeof($argv) == 3) {
            $this->response($this->parking($argv));
        }
	}

    public function parking($argv)
    {
        $argv[0] = trim($argv[0]);
        if (strlen($argv[0]) == 0 || strlen($argv[0]) > 20) {
			return ['error' => ['code' => 3001, 'message' => 'Incorrect car number', 'data' => null]];
        }

        $argv[1] = trim($argv[1]);
        $sql = "SELECT * from zones where zone_id = '" . ucfirst($argv[1]) . "'";
        if (strlen($argv[1]) > 1 || !$result = $this->db->querySingle($sql, true)) {
			return ['error' => ['code' => 3002, 'message' => 'Incorrect zone', 'data' => null]];
        }

        $argv[2] = trim($argv[2]);
        if (!filter_var($argv[2], FILTER_VALIDATE_INT, array("options" => array("min_range" => 10)))) {
			return ['error' => ['code' => 3003, 'message' => 'Incorrect parking time', 'data' => null]];
        }

        $argv[2] = (int) $argv[2];
        if ($argv[2] > 60) {
            $price = $result['price1'];
            $price += round(($result['price2'] * ($argv[2] - 60)) / 60, 2);
        } else {
            $price = round(($result['price1'] * $argv[2]) / 60, 2);
        }

        $start_date = time();
        $end_date = $start_date + ($argv[2] * 60);
        $sql = "INSERT INTO parking(car_number, zone_id, time, price, dt)
                VALUES('" . $this->db->escapeString($argv[0]) . "',
                '" . $argv[1][0] . "', " . $argv[2] . ", " .
                $price . ", " . $end_date . ")";
        if (!$this->db->query($sql)) {
			return ['error' => ['code' => 3004, 'message' => 'Parking error ' . $db->lastErrorMsg(), 'data' => null]];
        }

        return ['result' => 2, 'id' => null, 'start_date' => date('d.m.Y H:i:s', $start_date),
                'end date' => date('d.m.Y H:i:s', $end_date),
                'price' => $price];
    }

    public function parked($argv)
    {
        $argv[0] = trim($argv[0]);
        if (strlen($argv[0]) == 0 || strlen($argv[0]) > 20) {
            return ['error' => ['code' => 3001, 'message' => 'Incorrect car number', 'data' => null]];
        }

        $argv[1] = trim($argv[1]);
        $sql = "SELECT * from zones where zone_id = '" . ucfirst($argv[1]) . "'";
        if (strlen($argv[1]) > 1 || !$result = $this->db->querySingle($sql, true)) {
            return ['error' => ['code' => 3002, 'message' => 'Incorrect zone', 'data' => null]];
        }

        $sql = "SELECT dt from parking WHERE car_number = 
               '" . $this->db->escapeString($argv[0]) . "' AND zone_id = 
               '" . $this->db->escapeString($argv[1]) . "'
               ORDER BY dt desc";
        if (!$result = $this->db->querySingle($sql, true)) {
            return ['error' => ['code' => 3005, 'message' => 'Parked error ' . $db->lastErrorMsg(), 'data' => null]];
        }

        $parked = false;
        if (time() <= $result['dt']) {
            $parked = true;
        }
        
        return ['result' => 3, 'id' => null, 'parked' => $parked, 'end date' => date('d.m.Y H:i:s', $result['dt'])];
    }

    public function zones()
    {
        $sql = "SELECT * from zones ORDER BY zone_id";
        if (!$result = $this->db->query($sql)) {
            $this->error(3006, 'Parking error ' . $db->lastErrorMsg());
        }

        $data = ['result' => 1, 'id' => null];
        while($row = $result->fetchArray(SQLITE3_ASSOC)) {
            $data[] = $row;
        }
        return $data;
    }

    private function response($data)
    {
        echo json_encode(['jsonrpc' => 2.0] + $data);
    }
}
