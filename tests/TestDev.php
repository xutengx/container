<?php
declare(strict_types = 1);

use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__) . '/dev/test.php';

final class TestDev extends TestCase {

	public function testTest() {

		$obj   = new \test;

		$this->assertInstanceOf(\redis::class, $obj->index());
	}

}


