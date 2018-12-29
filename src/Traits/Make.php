<?php

declare(strict_types = 1);
namespace Xutengx\Container\Traits;

use Closure;
use ReflectionClass;
use ReflectionParameter;
use Xutengx\Container\Exception\BindingResolutionException;

/**
 * Trait Make
 * @method ReflectionClass getReflectionClass
 * @package Xutengx\Container\Traits
 */
trait Make {

	/**
	 * 构建抽象
	 * @param string $abstract
	 * @param array $parameters
	 * @return mixed
	 */
	public function make(string $abstract, array $parameters = []) {
		return $this->resolve($abstract, $parameters);
	}

	/**
	 * 构建抽象, 总是缓存的对象
	 * @param string $abstract
	 * @return mixed
	 */
	public function makeSingleton(string $abstract) {
		// 存在临时绑定
		if ($this->hasOnceConcrete($abstract))
			throw new BindingResolutionException("abstract[$abstract] which has been bindOnce, can not be properly makeSingleton.");

		return ($this->hasInstance($abstract) || $this->setInstance($abstract, $this->resolve($abstract))) ?
			$this->getInstance($abstract) : null;
	}

	/**
	 * 容器中分析给定的抽象(接口/类)
	 * @param string $abstract
	 * @param array $parameters
	 * @return mixed
	 */
	protected function resolve(string $abstract, array $parameters = []) {
		// 别名解析
		$abstract = $this->getAlias($abstract);

		// 存在接口的实现
		$concrete = $this->getConcrete($abstract, $once);

		// 存在接口的实现的结果, 则直接返回
		if (!$once && $singleton = $this->isSingleton($abstract) && $this->hasInstance($abstract)) {
			return $this->getInstance($abstract);
		}

		// 记录参数
		$this->with[] = $parameters;

		// 建立对象
		$results = $this->build($concrete);

		// 需要缓存的对象 (非单次且单例, 则缓存)
		if (!$once && $this->isSingleton($abstract)) {
			// 缓存抽象的实现
			$this->setInstance($abstract, $results);
		}

		// 移除参数
		array_pop($this->with);

		return $results;
	}

	/**
	 * 实例化给定抽象的具体实例
	 * @param string|Closure $concrete
	 * @return mixed
	 */
	protected function build($concrete) {
		// 是闭包, 则直接执行
		if ($concrete instanceof Closure) {
			return $this->executeClosure($concrete, $this->getLastParameterOverride());
		}

		// 反射
		$reflector = $this->getReflectionClass($concrete);

		// 不可实例化
		if (!$reflector->isInstantiable()) {
			$this->notInstantiable($concrete);
		}

		$this->buildStack[] = $concrete;

		// 获取类的构造函数
		$constructor = $reflector->getConstructor();

		// 如果没有构造函数, 也就是没有依赖的存在, 则马上返回实例化
		if (is_null($constructor)) {
			array_pop($this->buildStack);
			return new $concrete;
		}
		// 获取类的构造函数的需求参数
		$dependencies = $constructor->getParameters();

		// 解决构造函数的依赖
		$constructorDependentParameters = $this->resolveDependencies($dependencies);

		array_pop($this->buildStack);

		// 返回实例化
		return $reflector->newInstanceArgs($constructorDependentParameters);
	}

	/**
	 * 优先返回已绑定的抽象
	 * @param string $abstract
	 * @param bool $once
	 * @return string|Closure
	 */
	protected function getConcrete(string $abstract, &$once = false) {
		if ($concrete = ($this->OnceBindings[$abstract]['concrete'] ?? false)) {
			$once = true;
			unset($this->OnceBindings[$abstract]);
			return $concrete;
		}
		else
			return $this->bindings[$abstract]['concrete'] ?? $abstract;
	}

	/**
	 * 发出不可实例化的异常
	 * @param string $concrete
	 * @return void
	 */
	protected function notInstantiable(string $concrete): void {
		if (!empty($this->buildStack)) {
			$previous = implode(', ', $this->buildStack);
			$message  = "Target [$concrete] is not instantiable while building [$previous].";
		}
		else {
			$message = "Target [$concrete] is not instantiable.";
		}
		throw new BindingResolutionException($message);
	}

	/**
	 * 解决依赖
	 * @param array $dependencies 依赖( ReflectionParameter )组成的数组
	 * @return array 构造函数的依赖参数
	 */
	protected function resolveDependencies(array $dependencies): array {
		$results = [];
		foreach ($dependencies as $dependency) {
			// 如果依赖被手动传递, 则立即使用
			if ($this->hasParameterOverride($dependency)) {
				// 获得手动传入的实参
				$results[] = $this->getParameterOverride($dependency);
				continue;
			}
			// 分别解决`基本类型的依赖`与`对象类型依赖`
			$results[] = is_null($dependency->getClass()) ? $this->resolvePrimitive($dependency) :
				$this->resolveClass($dependency);
		}
		return $results;
	}

	/**
	 * 基本类型的依赖解决
	 * @param ReflectionParameter $parameter
	 * @return mixed
	 */
	protected function resolvePrimitive(ReflectionParameter $parameter) {
		// 优先使用默认值, 否则给null
		return $parameter->isDefaultValueAvailable() ? $parameter->getDefaultValue() : null;
	}

	/**
	 * 对象类型的依赖解决
	 * @param ReflectionParameter $parameter
	 * @return mixed
	 * @throws BindingResolutionException
	 */
	protected function resolveClass(ReflectionParameter $parameter) {
		try {
			return $this->make($parameter->getClass()->name);
		} catch (BindingResolutionException $e) {
			// 使用默认值
			if ($parameter->isOptional()) {
				return $parameter->getDefaultValue();
			}
			throw $e;
		}
	}

	/**
	 * 是否存在依赖的参数被手动传入
	 * @param ReflectionParameter $dependency
	 * @return bool
	 */
	protected function hasParameterOverride(ReflectionParameter $dependency): bool {
		return array_key_exists($dependency->name, $this->getLastParameterOverride());
	}

	/**
	 * 获得手动传入的且依赖的参数
	 * @param ReflectionParameter $dependency
	 * @return mixed
	 */
	protected function getParameterOverride(ReflectionParameter $dependency) {
		return $this->getLastParameterOverride()[$dependency->name];
	}

	/**
	 * 获得手传入的所有参数
	 * @return array
	 */
	protected function getLastParameterOverride(): array {
		return count($this->with) ? end($this->with) : [];
	}

}
