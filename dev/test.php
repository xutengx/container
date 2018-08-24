<?php

declare(strict_types = 1);

use Xutengx\Container\Container;

require_once dirname(__DIR__) . '/vendor/autoload.php';

class test {

	public function index() {

		$Container = new Container;

		$Container->bind('redisObj', \redis::class);

		$redis = $Container->make('redisObj');

		return $redis;

	}

}
