<?php

declare(strict_types = 1);
namespace Xutengx\Container\Traits;

/**
 * Trait Instance
 * @package Xutengx\Container\Traits
 */
trait Instance {

	/**
	 * 是否存在抽象的实现
	 * @param string $abstract
	 * @return bool
	 */
	protected function hasInstance(string $abstract): bool {
		return isset($this->instances[$abstract]);
	}

	/**
	 * 缓存抽象的实现
	 * @param string $abstract
	 * @param $results
	 * @return bool
	 */
	protected function setInstance(string $abstract, $results): bool {
		$this->instances[$abstract] = $results;
		return true;
	}

	/**
	 * 返回抽象的实现
	 * @param string $abstract
	 * @return mixed
	 */
	protected function getInstance(string $abstract) {
		return $this->instances[$abstract];
	}

	/**
	 * 移除一个抽象的实现
	 * @param string $abstract
	 * @return bool
	 */
	protected function delInstance(string $abstract): bool {
		unset($this->instances[$abstract]);
		return true;
	}
}
