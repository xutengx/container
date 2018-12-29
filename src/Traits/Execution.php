<?php

declare(strict_types = 1);
namespace Xutengx\Container\Traits;

use Closure;
use ReflectionClass;
use ReflectionFunction;

/**
 * 函数执行
 * Trait Execution
 * @method ReflectionClass getReflectionClass
 * @method ReflectionFunction getReflectionFunction
 * @package Xutengx\Container\Traits
 */
trait Execution {

	/**
	 * 执行对象的某个方法, 自动解决依赖
	 * @param string|object 对象/类/抽象
	 * @param string $method
	 * @param array $parameters
	 * @return mixed
	 */
	public function execute($object, string $method, array $parameters = []) {
		// 统一转化为对象
		$obj = is_object($object) ? $object : $this->make($object);

		// 加入参数
		$this->with[] = $parameters;

		// 反射目标
		$reflector = $this->getReflectionClass($obj);

		// 获取类的`执行函数`
		$methodReflector = $reflector->getMethod($method);

		// 获取类的`执行函数`的需求参数
		$dependencies = $methodReflector->getParameters();

		// 解决`执行函数`的依赖
		$methodDependentParameters = $this->resolveDependencies($dependencies);

		// 移除参数
		array_pop($this->with);

		// 将`执行函数`转化为闭包
		$closure = $methodReflector->getClosure($obj);

		// `执行函数`的执行结果
		return $closure(...$methodDependentParameters);
	}

	/**
	 * 执行某个闭包, 自动解决依赖
	 * @param Closure $Closure
	 * @param array $parameters
	 * @return mixed
	 */
	public function executeClosure(Closure $Closure, array $parameters = []) {
		// 加入参数
		$this->with[] = $parameters;

		// 反射目标
		$reflectionFunction = $this->getReflectionFunction($Closure);

		// 获取需求参数
		$dependencies = $reflectionFunction->getParameters();

		// 解决依赖
		$methodDependentParameters = $this->resolveDependencies($dependencies);

		// 移除参数
		array_pop($this->with);

		return call_user_func_array($Closure, $methodDependentParameters);
	}

}
