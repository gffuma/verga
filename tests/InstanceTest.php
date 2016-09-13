<?php

use Verga\Verga;

class InstanceTest extends PHPUnit_Framework_TestCase {

    public function testCsvImporterInstance()
    {
        $i = Verga::importer([
            'import' => null
        ]);

        $this->assertInstanceOf(\Verga\Importer\CsvImporter::class, $i);
    }

    public function testMetaErrorInstance()
    {
        $this->assertInstanceOf(\Verga\Meta\Error::class, Verga::error('Some error'));
    }

    public function testMetaCombineInstance()
    {
        $this->assertInstanceOf(\Verga\Meta\Combine::class, Verga::combine([]));
    }

    public function testResultInstance()
    {
        $r = Verga::importer([
            'import' => null
        ])
        ->importFromString('', false);

        $this->assertInstanceOf(\Verga\Result\Result::class, $r);
    }

    public function testValidLinesInstance()
    {
        $csv = "giova;developer\n" .
               "matteo;mc";

        $r = Verga::importer([
            'import' => null,
            'cols' => [
                'name' => 0,
                'occupation' => 1
            ]
        ])
        ->importFromString($csv, false);

        $this->assertContainsOnlyInstancesOf(
            \Verga\Result\Line\ValidLine::class,
            $r->getLines()
        );

        $this->assertContainsOnlyInstancesOf(
            \Verga\Result\Line\ValidLine::class,
            $r->getValidLines()
        );
    }

    public function testInvalidLinesInstance()
    {
        $csv = "giova\n" .
               "matteo";

        $r = Verga::importer([
            'import' => null,
            'cols' => [
                'name' => 0,
                'occupation' => 1
            ]
        ])
        ->importFromString($csv, false);

        $this->assertContainsOnlyInstancesOf(
            \Verga\Result\Line\InvalidLine::class,
            $r->getLines()
        );

        $this->assertContainsOnlyInstancesOf(
            \Verga\Result\Line\InvalidLine::class,
            $r->getInvalidLines()
        );
    }

    public function testImportedLinesInstance()
    {
        $csv = "giova;dev\n" .
               "matteo;mc";

        $r = Verga::importer([
            'import' => function ($data) { return $data; },
            'cols' => [
                'name' => 0,
                'occupation' => 1
            ]
        ])
        ->importFromString($csv);

        $this->assertContainsOnlyInstancesOf(
            \Verga\Result\Line\ImportedLine::class,
            $r->getLines()
        );

        $this->assertContainsOnlyInstancesOf(
            \Verga\Result\Line\ImportedLine::class,
            $r->getValidLines()
        );
    }
}
