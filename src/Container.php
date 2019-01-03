<?php

declare(strict_types = 1);
namespace Xutengx\Container;

use Closure;
use ReflectionClass;
use ReflectionException;
use ReflectionFunction;
use Xutengx\Container\Exception\BindingResolutionException;
use Xutengx\Container\Traits\{Bind, Check, Execution, Instance, Make};

class Container {

	use Bind, Check, Execution, Instance, Make;

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

	/**
	 * 获取类的反射对象
	 * @param string|object $class
	 * @return ReflectionClass
	 */
	protected function getReflectionClass($class): ReflectionClass {
		try {
			return new ReflectionClass($class);
		} catch (ReflectionException $e) {
			throw new BindingResolutionException($e->getMessage(), $e->getCode(), $e);
		}
	}

	/**
	 * 获取闭包的反射对象
	 * @param Closure $Closure
	 * @return ReflectionFunction
	 */
	protected function getReflectionFunction(Closure $Closure): ReflectionFunction {
		try {
			return new ReflectionFunction($Closure);
		} catch (ReflectionException $e) {
			throw new BindingResolutionException($e->getMessage(), $e->getCode(), $e);
		}
	}

}
