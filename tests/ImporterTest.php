<?php

use Verga\Verga;

class CalculatorTest extends PHPUnit_Framework_TestCase
{
    public function testSimpleColumnMapping()
    {
        $csv = "giova;giovanni@mail.it\n" .
               "skaffo;skaffo@mail.it\n" .
               "john;john@mail.it";

        $r = Verga::importer([
            'import' => null,
            'cols' => [
                'name'  => 0,
                'email' => [ 'col' => 1 ],
            ]
        ])
        ->importFromString($csv, false);

        $lines = $r->getLines();
        $this->assertCount(3, $lines);
        $this->assertCount(3, $r->getValidLines());
        $this->assertCount(0, $r->getInvalidLines());

        $this->assertEquals($lines[0]->getParsedData(), [
            'name' => 'giova',
            'email' => 'giovanni@mail.it',
        ]);
        $this->assertEquals($lines[1]->getParsedData(), [
            'name' => 'skaffo',
            'email' => 'skaffo@mail.it',
        ]);
        $this->assertEquals($lines[2]->getParsedData(), [
            'name' => 'john',
            'email' => 'john@mail.it',
        ]);
    }
}
