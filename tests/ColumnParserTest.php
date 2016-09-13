<?php

use Verga\Verga;
use Verga\Column\ColumnParser;
use Verga\Exception\ColumnNotFoundException;
use Verga\Exception\ColumnValidationException;

class ColumnParserTest extends PHPUnit_Framework_TestCase {

    public function testFixedValue()
    {
        $c = new ColumnParser('fixed~value', null, true, null, null, null);
        $value = $c->parse(33, 'giova;dev', ['giova', 'dev'], [ 'name' => 'giova' ]);
        $this->assertEquals('fixed~value', $value);
    }

    public function testValidColumnIndex()
    {
        $c = new ColumnParser(null, 1, true, null, null, null);
        $value = $c->parse(33, 'giova;dev', ['giova', 'dev'], [ 'name' => 'giova' ]);
        $this->assertEquals('dev', $value);
    }

    public function testInvalidColumnIndex()
    {
        $this->expectException(ColumnNotFoundException::class);
        $c = new ColumnParser(null, 2, true, null, null, null);
        $value = $c->parse(33, 'giova;dev', ['giova', 'dev'], [ 'name' => 'giova' ]);
    }

    public function testDefaultValue()
    {
        $c = new ColumnParser(null, 2, false, 'default~value', null, null);
        $value = $c->parse(33, 'giova;dev', ['giova', 'dev'], [ 'name' => 'giova' ]);
        $this->assertEquals('default~value', $value);
    }

    public function testValidationCallbackArguments()
    {
        $c = new ColumnParser(null, 2, false, 'default~value',
            function ($value, $data, $row, $lineNumber, $line) {
                $this->assertEquals($value, 23);
                $this->assertEquals($data, [
                    'name' => 'giova',
                    'role' => 'dev'
                ]);
                $this->assertEquals($row, ['giova', 'dev', 23]);
                $this->assertEquals($lineNumber, 33);
                $this->assertEquals($line, 'giova;dev;23');
            },
            null
        );
        $value = $c->parse(33, 'giova;dev;23', ['giova', 'dev', 23], [
            'name' => 'giova',
            'role' => 'dev'
        ]);
    }

    public function testValidationOk()
    {
        $c = new ColumnParser(null, 2, false, 'default~value',
            function ($value) {
                return is_int($value);
            },
            null
        );
        $value = $c->parse(33, 'giova;dev;23', ['giova', 'dev', 23], [
            'name' => 'giova',
            'role' => 'dev'
        ]);
        $this->assertEquals(23, $value);
    }

    public function testValidationFail()
    {
        $this->expectException(ColumnValidationException::class);
        $c = new ColumnParser(null, 2, false, 'default~value',
            function ($value) {
                if (! is_int($value)) {
                    return Verga::error('Invalid age!');
                }
            },
            null
        );
        $value = $c->parse(33, 'giova;dev;giovanni@mail.it', ['giova', 'dev', 'giovanni@mail.it'], [
            'name' => 'giova',
            'role' => 'dev'
        ]);
    }

    public function testValidationExceptionValue()
    {
        try {
            $c = new ColumnParser(null, 2, false, 'default~value',
                function ($value) {
                    if (! is_int($value)) {
                        return Verga::error('Invalid age!');
                    }
                },
                null
            );
            $value = $c->parse(33, 'giova;dev;giovanni@mail.it', ['giova', 'dev', 'giovanni@mail.it'], [
                'name' => 'giova',
                'role' => 'dev'
            ]);
        } catch(ColumnValidationException $e) {
            $this->assertEquals('giovanni@mail.it', $e->getValue());
            $this->assertEquals('Invalid age!', $e->getMessage());
        }
    }

    public function testMapCallbackArguments()
    {
        $c = new ColumnParser(null, 2, false, 'default~value', null,
            function ($value, $data, $row, $lineNumber, $line) {
                $this->assertEquals($value, 23);
                $this->assertEquals($data, [
                    'name' => 'giova',
                    'role' => 'dev'
                ]);
                $this->assertEquals($row, ['giova', 'dev', 23]);
                $this->assertEquals($lineNumber, 33);
                $this->assertEquals($line, 'giova;dev;23');
            }
        );
        $value = $c->parse(33, 'giova;dev;23', ['giova', 'dev', 23], [
            'name' => 'giova',
            'role' => 'dev'
        ]);
    }

    public function testMapCallbackMap()
    {
        $c = new ColumnParser(null, 2, false, 'default~value', null,
            function ($value) {
                return 23 + 100;
            }
        );
        $value = $c->parse(33, 'giova;dev;23', ['giova', 'dev', 23], [
            'name' => 'giova',
            'role' => 'dev'
        ]);
        $this->assertEquals(123, $value);
    }
}
