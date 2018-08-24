<?php

declare(strict_types = 1);
namespace Xutengx\Container\Traits;

use Closure;
use Xutengx\Container\Container;

trait Bind {

	/**
	 * 手动绑定
	 * @param string $abstract 抽象类/接口/类/自定义的标记
	 * @param Closure|string $concrete 闭包|类名
	 * @param bool $singleton 单例
	 * @return Container
	 */
	public function bind(string $abstract, $concrete = null, bool $singleton = false): Container {
		// 覆盖旧的绑定信息
		$this->dropStaleInstances($abstract);

		// 默认的类实现, 就是其本身
		$concrete = $concrete ?? $abstract;

		// 记录绑定
		$this->bindings[$abstract] = compact('concrete', 'singleton');

		return $this;
	}

	/**
	 * 单例绑定
	 * @param string $abstract
	 * @param Closure|string $concrete
	 * @return Container
	 */
	public function singleton(string $abstract, $concrete = null): Container {
		return $this->bind($abstract, $concrete, true);
	}

	/**
	 * 临时绑定, 同接口实现优先使用一次
	 * @param string $abstract
	 * @param Closure|string $concrete
	 * @return Container
	 */
	public function bindOnce(string $abstract, $concrete = null): Container {
		// 默认的类实现, 就是其本身
		$concrete = $concrete ?? $abstract;

		// 记录绑定
		$this->OnceBindings[$abstract] = compact('concrete');

		return $this;
	}

	/**
	 * 设置别名
	 * @param string $abstract
	 * @param string $alias
	 * @return Container
	 */
	public function alias(string $abstract, string $alias): Container {
		$this->aliases[$alias]              = $abstract;
		$this->abstractAliases[$abstract][] = $alias;
		return $this;
	}

	/**
	 * 解析别名
	 * @param string $abstract
	 * @return string
	 */
	public function getAlias(string $abstract): string {
		return isset($this->aliases[$abstract]) ? $this->getAlias($this->aliases[$abstract]) : $abstract;
	}

	/**
	 * 移除已经绑定的
	 * @param string $abstract
	 * @return Container
	 */
	protected function dropStaleInstances(string $abstract): Container {
		unset($this->instances[$abstract], $this->aliases[$abstract]);
		return $this;
	}

}
