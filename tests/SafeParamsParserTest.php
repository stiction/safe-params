<?php

use PHPUnit\Framework\TestCase;
use Stiction\SafeParams\SafeParamsParser;

class SafeParamsParserTest extends TestCase
{
    public function testInt()
    {
        $this->doTest(100, 'int', 100);
        $this->doTest(-29382, 'int', -29382);
        $this->doTest(3.0, 'int', 3);
        $this->doTest(3.9, 'int', 3);
        $this->doTest(true, 'int', 1);
        $this->doTest(false, 'int', 0);
        $this->doTest('', 'int', 0);
        $this->doTest('hello', 'int', 0);
        $this->doTest('100', 'int', 100);
        $this->doTest('38.9', 'int', 38);
        $this->doTest('0026.9', 'int', 26);
        $this->doTest('  65.2', 'int', 65);
        $this->doTest([], 'int', 0);
        $this->doTest([100], 'int', 0);
        $this->doTest(new \stdClass, 'int', 0);
        $this->doTest(null, 'int', 0);
    }

    public function testFloat()
    {
        $this->doTest(100, 'float', 100.0);
        $this->doTest(-29382, 'float', -29382.0);
        $this->doTest(3.0, 'float', 3.0);
        $this->doTest(1.1, 'float', 1.1);
        $this->doTest(true, 'float', 1.0);
        $this->doTest(false, 'float', 0.0);
        $this->doTest('', 'float', 0.0);
        $this->doTest('hello', 'float', 0.0);
        $this->doTest('100', 'float', 100.0);
        $this->doTest('0093', 'float', 93.0);
        $this->doTest('  17', 'float', 17.0);
        $this->doTest('9.5', 'float', 9.5);
        $this->doTest([], 'float', 0.0);
        $this->doTest([100], 'float', 0.0);
        $this->doTest(new \stdClass, 'float', 0.0);
        $this->doTest(null, 'float', 0.0);
    }

    public function testBool()
    {
        $this->doTest(0, 'bool', false);
        $this->doTest(1, 'bool', true);
        $this->doTest(0.0, 'bool', false);
        $this->doTest(1.0, 'bool', true);
        $this->doTest(-1.0, 'bool', true);
        $this->doTest(false, 'bool', false);
        $this->doTest(true, 'bool', true);
        $this->doTest('', 'bool', false);
        $this->doTest('0', 'bool', false);
        $this->doTest('0.0', 'bool', true);
        $this->doTest('0.', 'bool', true);
        $this->doTest('.0', 'bool', true);
        $this->doTest('hello', 'bool', true);
        $this->doTest([], 'bool', false);
        $this->doTest([100], 'bool', true);
        $this->doTest(new \stdClass, 'bool', true);
        $this->doTest(null, 'bool', false);
    }

    public function testString()
    {
        $this->doTest(false, 'string', '');
        $this->doTest(true, 'string', '1');
        $this->doTest([], 'string', 'Array');
        $this->doTest([100], 'string', 'Array');
        $this->doTest(new \stdClass, 'string', '');
        $this->doTest('hello', 'string', 'hello');
        $this->doTest(' hello   ', 'string', ' hello   ');
        $this->doTest(' hello   ', 'string.trim', 'hello');
        $this->doTest(null, 'string', '');
    }

    public function testArray()
    {
        $this->doTest(0, 'array', [0]);
        $this->doTest(1, 'array', [1]);
        $this->doTest(0.0, 'array', [0.0]);
        $this->doTest(false, 'array', [false]);
        $this->doTest(true, 'array', [true]);
        $this->doTest('', 'array', ['']);
        $this->doTest('hello', 'array', ['hello']);
        $this->doTest([], 'array', []);
        $this->doTest([100, 3.5, 'hello', true, null], 'array', [100, 3.5, 'hello', true, null]);
        $this->doTest(new \stdClass, 'array', []);
        $this->doTest(null, 'array', []);
        $this->doTest([100, 3.5, ' hello  ', true, null], 'array.string', ['100', '3.5', ' hello  ', '1', '']);
        $this->doTest([100, 3.5, ' hello  ', true, null], 'array.string.trim', ['100', '3.5', 'hello', '1', '']);
        $this->doTest([100, 3.5, ' hello  ', true, null], 'array.int', [100, 3, 0, 1, 0]);
        $this->doTest([100, 3.5, ' hello  ', true, null], 'array.float', [100.0, 3.5, 0.0, 1.0, 0.0]);
        $this->doTest([100, 3.5, ' hello  ', true, null], 'array.bool', [true, true, true, true, false]);
    }

    public function testUint64()
    {
        $this->doTest(100, 'uint64', '100');
        $this->doTest(-29382, 'uint64', '0');
        $this->doTest(3.0, 'uint64', '3');
        $this->doTest(1.1, 'uint64', '1');
        $this->doTest(true, 'uint64', '1');
        $this->doTest(false, 'uint64', '0');
        $this->doTest('', 'uint64', '0');
        $this->doTest('hello', 'uint64', '0');
        $this->doTest('100', 'uint64', '100');
        $this->doTest('03.99999999999999', 'uint64', '3');
        $this->doTest('0093', 'uint64', '93');
        $this->doTest('  17', 'uint64', '17');
        $this->doTest('   09.5', 'uint64', '9');
        $this->doTest(' 0.5', 'uint64', '0');
        $this->doTest([], 'uint64', '0');
        $this->doTest([100], 'uint64', '0');
        $this->doTest(new \stdClass, 'uint64', '0');
        $this->doTest(null, 'uint64', '0');
        $this->doTest('18446744073709551616', 'uint64', '18446744073709551615');
        $this->doTest(' 18446744073709551617', 'uint64', '18446744073709551615');
        $this->doTest(' 0321342123415218446744073709551616', 'uint64', '18446744073709551615');
        $this->doTest(321342123415218446744073709551616.2938, 'uint64', '18446744073709551615');
        $this->doTest(-321342123415218446744073709551616.2938, 'uint64', '0');
        $this->doTest('-18446744073709551616', 'uint64', '0');
    }

    public function testMulti()
    {
        $data = [
            'name' => '   jack   ',
            'id' => 29,
            'hobbies' => [21, 'baseball', 3.14],
            'address' => 'a beautiful village',
        ];
        $expected = [
            'name' => 'jack',
            'id' => '29',
            'hobbies' => [21, 0, 3],
        ];
        $this->doTest($data, ['name' => 'string.trim', 'id' => 'uint64', 'hobbies' => 'array.int'], $expected);
    }

    public function testExceptions()
    {
        $this->doTestException('', 'abc');
        $this->doTestException('', ' int');
        $this->doTestException('', 'array.array');
        $this->doTestException('', 'int.trim');
        $this->doTestException('', ['name' => 'abc']);
    }

    protected function doTest($data, $spec, $expected)
    {
        $parser = new SafeParamsParser;
        $result = $parser->parse($data, $spec);
        $message = var_export(compact('data', 'spec', 'expected', 'result'), true);
        $this->assertSame($expected, $result, $message);
    }

    protected function doTestException($data, $spec)
    {
        try {
            $parser = new SafeParamsParser;
            $parser->parse($data, $spec);
            $this->fail(var_export($spec, true).' is invalid');
        } catch (\InvalidArgumentException $e) {
            // empty
        }
    }
}
