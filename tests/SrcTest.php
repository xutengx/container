<?php
declare(strict_types = 1);

use PHPUnit\Framework\TestCase;
use Xutengx\Container\Container;

interface human {

}

interface boy {

}

interface girl {

}

interface Student {

}

final class SrcTest extends TestCase {

	/**
	 * 实例化容器
	 * @return Container
	 */
	public function testCreateContainer(): Container {
		$this->assertInstanceOf(Container::class, $Container = new Container);
		return $Container;
	}

	/**
	 * @throws ReflectionException
	 * @throws \Xutengx\Container\Exception\BindingResolutionException
	 */
	public function testBind() {
		$Container = new Container;
		$Container->bind(boy::class, liu::class);
		$Container->bind(girl::class, chen::class);
		$Container->bind(Student::class, li::class);

		$this->assertInstanceOf(li::class, $student1 = $Container->make(Student::class));
		$this->assertInstanceOf(Student::class, $student2 = $Container->make(Student::class));

		$this->assertTrue($student1 == $student2);
		$this->assertFalse($student1 === $student2);
	}

	/**
	 * @throws ReflectionException
	 * @throws \Xutengx\Container\Exception\BindingResolutionException
	 */
	public function testSingleton() {
		$Container = new Container;
		$Container->bind(boy::class, liu::class);
		$Container->bind(girl::class, chen::class);
		$Container->singleton(Student::class, li::class);

		$this->assertInstanceOf(li::class, $student1 = $Container->make(Student::class));
		$this->assertInstanceOf(Student::class, $student2 = $Container->make(Student::class));

		$this->assertTrue($student1 == $student2);
		$this->assertTrue($student1 === $student2);
	}

	/**
	 * @throws ReflectionException
	 * @throws \Xutengx\Container\Exception\BindingResolutionException
	 */
	public function testBindOnce() {
		$Container = new Container;
		$Container->bind(boy::class, liu::class);
		$Container->bind(girl::class, chen::class);
		$Container->singleton(Student::class, li::class);
		$Container->bindOnce(Student::class, zhang::class);

		$this->assertInstanceOf(zhang::class, $student1 = $Container->make(Student::class));
		$this->assertInstanceOf(li::class, $student2 = $Container->make(Student::class));
		$this->assertInstanceOf(Student::class, $student3 = $Container->make(Student::class));

		$Container->bindOnce(Student::class, zhang::class);
		$this->assertInstanceOf(Student::class, $student4 = $Container->make(Student::class));

		$this->assertFalse($student1 == $student2);
		$this->assertFalse($student1 === $student2);
		$this->assertTrue($student2 === $student3);
		$this->assertFalse($student2 === $student4);
		$this->assertFalse($student1 === $student4);
	}

	/**
	 * @throws ReflectionException
	 * @throws \Xutengx\Container\Exception\BindingResolutionException
	 */
	public function testBindOtherType() {
		$Container = new Container;
		$Container->bind('array', function() {
			return ['key' => 'value'];
		});
		$this->assertEquals(['key' => 'value'], $Container->make('array'));

		$Container->bind('array', function(liu $boy) {
			return ['name' => get_class($boy)];
		});
		$this->assertEquals(['name' => 'liu'], $Container->make('array'));

		$Container->bind(boy::class, liu::class);
		$Container->bind(girl::class, chen::class);
		$Container->bind(liu::class, li::class);

		$this->assertEquals(['name' => 'li'], $Container->make('array'));

	}

	/**
	 * @throws ReflectionException
	 */
	public function testExecution() {
		$Container = new Container;
		$function  = function() {
			return 1 + 2;
		};
		$this->assertEquals(3, $Container->executeClosure($function));

		$function2 = function(liu $boy) {
			return get_class($boy);
		};
		$this->assertEquals('liu', $Container->executeClosure($function2));
	}

	public function testAlias() {
		$Container = new Container;
		$Container->bind(boy::class, liu::class);
		$Container->bind(girl::class, chen::class);
		$Container->singleton(Student::class, li::class);
		$Container->bindOnce(Student::class, zhang::class);

		$Container->alias(Student::class, 'a');
		$Container->alias('a', 'b');
		$Container->alias('b', 'c');

		$this->assertInstanceOf(zhang::class, $student1 = $Container->make('c'));
		$this->assertInstanceOf(li::class, $student2 = $Container->make('b'));
		$this->assertInstanceOf(li::class, $student3 = $Container->make('a'));

		$this->assertFalse($student1 === $student2);
		$this->assertFalse($student1 === $student3);
		$this->assertTrue($student2 === $student3);

		$Container->singleton(Student::class, zhang::class);
		$this->assertInstanceOf(zhang::class, $student4 = $Container->make('a'));
		$this->assertFalse($student4 === $student3);


	}

	public function testMakeSingleton() {
		$Container = new Container;
		$Container->bind(boy::class, liu::class);
		$Container->bind(girl::class, chen::class);
		$Container->bind(Student::class, zhang::class);
		$Container->bindOnce(Student::class, zhang::class);

		try {
			$this->assertInstanceOf(Student::class, $student0 = $Container->makeSingleton(Student::class));
		} catch (RuntimeException $exception) {
			$RuntimeException = true;
			$this->assertEquals('abstract[Student] which has been bindOnce, can not be properly makeSingleton.',
				$exception->getMessage());
		} finally {
			$this->assertTrue($RuntimeException);
		}

		$this->assertInstanceOf(Student::class, $student0 = $Container->make(Student::class), '消耗bindOnce');
		$this->assertInstanceOf(Student::class, $student1 = $Container->makeSingleton(Student::class));
		$this->assertInstanceOf(Student::class, $student2 = $Container->makeSingleton(Student::class));
		$this->assertInstanceOf(Student::class, $student4 = $Container->make(Student::class));

		$this->assertFalse($student0 === $student1);
		$this->assertTrue($student1 === $student2);
		$this->assertFalse($student1 === $student4);
		$this->assertFalse($student0 === $student4);

		$Container->bind(Student::class, zhang::class);
		$this->assertInstanceOf(Student::class, $student5 = $Container->makeSingleton(Student::class));
		$this->assertTrue($student1 !== $student5, '重复绑定后 makeSingleton 将发生变化');

	}

}

class person implements human {
	public $father;
	public $mather;

	public function __construct(boy $father, girl $mother) {
		$this->father = $father;
		$this->mother = $mother;
	}
}

class father extends person {

}

class mather extends person {

}

class liu implements boy {
}

class chen implements girl {

}

class li extends liu implements boy, Student {
	public function __construct(boy $father, girl $mother) {
		$this->father = $father;
		$this->mother = $mother;
	}
}

class zhang implements girl, Student {
	public function __construct(boy $father, girl $mother) {
		$this->father = $father;
		$this->mother = $mother;
	}

	public function ko(human $Enemy) {
		return $Enemy->father;
	}
}