<?php

$db_name = 'parking.db';
try {
	$db = new SQLite3('parking.db');
} catch (Exception $e) {
	exit('Can\'t create ' . $db_name . ' ' . $e->getMessage());
}

$sql = "CREATE TABLE zones(zone_id TEXT PRIMARY KEY, price1 FLOAT, price2 FLOAT);";
$db->exec($sql);
$sql = "INSERT INTO zones(zone_id, price1, price2) VALUES('A', 1.99, 2.85);
		INSERT INTO zones(zone_id, price1, price2) VALUES('B', 1.42, 2.28);
		INSERT INTO zones(zone_id, price1, price2) VALUES('C', 1.14, 1.71);
		INSERT INTO zones(zone_id, price1, price2) VALUES('D', 0.85, 1.14);
		INSERT INTO zones(zone_id, price1, price2) VALUES('R', 4.27, 7.11);";
$db->query($sql);
$sql = "CREATE TABLE parking(id INTEGER PRIMARY KEY, car_number TEXT, zone_id INTEGER,
			time INTEGER, price FLOAT, dt INTEGER);";
$db->exec($sql);
exit($db_name . ' created succesfully!');
