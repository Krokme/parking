<?php
include 'Parking.php';

class ParkingTest extends PHPUnit_Framework_TestCase
{
	public function testParking()
    {
		$parking = new Parking();
        
		$this->assertArrayHasKey('result', $parking->zones());

		$this->assertArrayHasKey('result', $parking->parking(['FK1736', 'A', '10']));
		$this->assertArrayHasKey('parked', $parking->parked(['FK1736', 'A']));

		$this->assertArrayHasKey('error', $parking->parking(['FK173699999999999999999999999999999999', 'A', '70']));
		$this->assertArrayHasKey('error', $parking->parked(['FK173699999999999999999999999999999999', 'A']));

		$this->assertArrayHasKey('error', $parking->parking(['FK1736', 'Z', '70']));
		$this->assertArrayHasKey('error', $parking->parked(['FK1736', 'Z']));

		$this->assertArrayHasKey('error', $parking->parking(['FK1736', 'Z', '0']));
	}

}
