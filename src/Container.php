<?php

declare(strict_types = 1);
namespace Xutengx\Container;

use Xutengx\Container\Traits\{Bind, Check, Execution, Make};

class Container {

	use Bind, Check, Execution, Make;

	/**
	 * 绑定信息
	 * @var array
	 */
	protected $bindings = [];
	/**
	 * 仅一次有效的绑定信息
	 * @var array
	 */
	protected $OnceBindings = [];
	/**
	 * 单例对象存储
	 * @var array
	 */
	protected $instances = [];
	/**
	 * 别名 -> 抽象 (一个别名对应一个抽象)
	 * @var array
	 */
	protected $aliases = [];
	/**
	 * 抽象 -> 别名 (一个抽象对应多个别名)
	 * @var array
	 */
	protected $abstractAliases = [];
	/**
	 * 依赖参数
	 * @var array
	 */
	protected $with = [];
	/**
	 * 正在解决的依赖栈
	 * @var array
	 */
	protected $buildStack = [];

	//	public function __get(string $name) {
	//		return $this->$name;
	//	}

}
