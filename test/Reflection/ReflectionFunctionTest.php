<?php

/**
 * @see       https://github.com/laminas/laminas-server for the canonical source repository
 * @copyright https://github.com/laminas/laminas-server/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-server/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Server\Reflection;

use Laminas\Server\Reflection;
use Laminas\Server\Reflection\AbstractFunction;
use Laminas\Server\Reflection\Prototype;
use PHPUnit\Framework\TestCase;
use ReflectionFunction;

/**
 * @group      Laminas_Server
 */
class ReflectionFunctionTest extends TestCase
{
    public function testConstructor()
    {
        $function = new ReflectionFunction('\LaminasTest\Server\Reflection\function1');
        $r = new Reflection\ReflectionFunction($function);
        $this->assertInstanceOf(\Laminas\Server\Reflection\ReflectionFunction::class, $r);
        $this->assertInstanceOf(AbstractFunction::class, $r);
        $params = $r->getParameters();

        $r = new Reflection\ReflectionFunction($function, 'namespace');
        $this->assertEquals('namespace', $r->getNamespace());

        $argv = ['string1', 'string2'];
        $r = new Reflection\ReflectionFunction($function, 'namespace', $argv);
        $this->assertInternalType('array', $r->getInvokeArguments());
        $this->assertEquals($argv, $r->getInvokeArguments());

        $prototypes = $r->getPrototypes();
        $this->assertInternalType('array', $prototypes);
        $this->assertNotEmpty($prototypes);
    }

    public function testPropertyOverloading()
    {
        $function = new ReflectionFunction('\LaminasTest\Server\Reflection\function1');
        $r = new Reflection\ReflectionFunction($function);

        $r->system = true;
        $this->assertTrue($r->system);
    }


    public function testNamespace()
    {
        $function = new ReflectionFunction('\LaminasTest\Server\Reflection\function1');
        $r = new Reflection\ReflectionFunction($function, 'namespace');
        $this->assertEquals('namespace', $r->getNamespace());
        $r->setNamespace('framework');
        $this->assertEquals('framework', $r->getNamespace());
    }

    public function testDescription()
    {
        $function = new ReflectionFunction('\LaminasTest\Server\Reflection\function1');
        $r = new Reflection\ReflectionFunction($function);
        $this->assertContains('function for reflection', $r->getDescription());
        $r->setDescription('Testing setting descriptions');
        $this->assertEquals('Testing setting descriptions', $r->getDescription());
    }

    public function testGetPrototypes()
    {
        $function = new ReflectionFunction('\LaminasTest\Server\Reflection\function1');
        $r = new Reflection\ReflectionFunction($function);

        $prototypes = $r->getPrototypes();
        $this->assertInternalType('array', $prototypes);
        $this->assertCount(8, $prototypes);

        foreach ($prototypes as $p) {
            $this->assertInstanceOf(Prototype::class, $p);
        }
    }

    public function testGetPrototypes2()
    {
        $function = new ReflectionFunction('\LaminasTest\Server\Reflection\function2');
        $r = new Reflection\ReflectionFunction($function);

        $prototypes = $r->getPrototypes();
        $this->assertInternalType('array', $prototypes);
        $this->assertNotEmpty($prototypes);
        $this->assertCount(1, $prototypes);

        foreach ($prototypes as $p) {
            $this->assertInstanceOf(Prototype::class, $p);
        }
    }


    public function testGetInvokeArguments()
    {
        $function = new ReflectionFunction('\LaminasTest\Server\Reflection\function1');
        $r = new Reflection\ReflectionFunction($function);
        $args = $r->getInvokeArguments();
        $this->assertInternalType('array', $args);
        $this->assertCount(0, $args);

        $argv = ['string1', 'string2'];
        $r = new Reflection\ReflectionFunction($function, null, $argv);
        $args = $r->getInvokeArguments();
        $this->assertEquals($argv, $args);
    }

    public function testClassWakeup()
    {
        $function = new ReflectionFunction('\LaminasTest\Server\Reflection\function1');
        $r = new Reflection\ReflectionFunction($function);
        $s = serialize($r);
        $u = unserialize($s);
        $this->assertInstanceOf(\Laminas\Server\Reflection\ReflectionFunction::class, $u);
        $this->assertEquals('', $u->getNamespace());
    }

    public function testMultipleWhitespaceBetweenDoctagsAndTypes()
    {
        $function = new ReflectionFunction('\LaminasTest\Server\Reflection\function3');
        $r = new Reflection\ReflectionFunction($function);

        $prototypes = $r->getPrototypes();
        $this->assertInternalType('array', $prototypes);
        $this->assertNotEmpty($prototypes);
        $this->assertCount(1, $prototypes);

        $proto = $prototypes[0];
        $params = $proto->getParameters();
        $this->assertInternalType('array', $params);
        $this->assertCount(1, $params);
        $this->assertEquals('string', $params[0]->getType());
    }

    /**
     * @group Laminas-6996
     */
    public function testParameterReflectionShouldReturnTypeAndVarnameAndDescription()
    {
        $function = new ReflectionFunction('\LaminasTest\Server\Reflection\function1');
        $r = new Reflection\ReflectionFunction($function);

        $prototypes = $r->getPrototypes();
        $prototype  = $prototypes[0];
        $params = $prototype->getParameters();
        $param  = $params[0];
        $this->assertContains('Some description', $param->getDescription(), var_export($param, 1));
    }
}

/**
 * \LaminasTest\Server\Reflection\function1
 *
 * Test function for reflection unit tests
 *
 * @param string $var1 Some description
 * @param string|array $var2
 * @param array $var3
 * @return null|array
 */
function function1($var1, $var2, $var3 = null)
{
}

/**
 * \LaminasTest\Server\Reflection\function2
 *
 * Test function for reflection unit tests; test what happens when no return
 * value or params specified in docblock.
 */
function function2()
{
}

/**
 * \LaminasTest\Server\Reflection\function3
 *
 * @param  string $var1
 * @return void
 */
function function3($var1)
{
}
