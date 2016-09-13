<?php

use Verga\Verga;

class CsvImporterTest extends PHPUnit_Framework_TestCase
{
    public function testSimpleColumnMapping()
    {
        $csv = "giova;giovanni@mail.it;23\n" .
               "skaffo;skaffo@mail.it;26\n" .
               "john;john@mail.it;50";

        $r = Verga::importer([
            'import' => null,
            'cols' => [
                'name'  => 0,
                'email' => [ 'col' => 1 ],
                'age'   => 2,
            ]
        ])
        ->importFromString($csv, false);

        $lines = $r->getLines();
        $this->assertCount(3, $lines);
        $this->assertCount(3, $r->getValidLines());
        $this->assertCount(0, $r->getInvalidLines());

        $this->assertEquals($lines[0]->getParsedData(), [
            'name'  => 'giova',
            'email' => 'giovanni@mail.it',
            'age'   => 23,
        ]);
        $this->assertEquals($lines[1]->getParsedData(), [
            'name'  => 'skaffo',
            'email' => 'skaffo@mail.it',
            'age'   => 26,
        ]);
        $this->assertEquals($lines[2]->getParsedData(), [
            'name'  => 'john',
            'email' => 'john@mail.it',
            'age'   => 50,
        ]);
    }

    public function testCustomDelimiter()
    {
        $csv = "giova~giovanni@mail.it~23\n" .
               "skaffo~skaffo@mail.it~26\n" .
               "john~john@mail.it~50";

        $r = Verga::importer([
            'import' => null,
            'delimiter' => '~',
            'cols' => [
                'name'  => 0,
                'email' => [ 'col' => 1 ],
                'age'   => 2,
            ]
        ])
        ->importFromString($csv, false);

        $lines = $r->getLines();
        $this->assertCount(3, $lines);
        $this->assertCount(3, $r->getValidLines());
        $this->assertCount(0, $r->getInvalidLines());

        $this->assertEquals($lines[0]->getParsedData(), [
            'name'  => 'giova',
            'email' => 'giovanni@mail.it',
            'age'   => 23,
        ]);
        $this->assertEquals($lines[1]->getParsedData(), [
            'name'  => 'skaffo',
            'email' => 'skaffo@mail.it',
            'age'   => 26,
        ]);
        $this->assertEquals($lines[2]->getParsedData(), [
            'name'  => 'john',
            'email' => 'john@mail.it',
            'age'   => 50,
        ]);
    }

    public function testSkip()
    {
        $csv = "name;email;age\n" .
               "Wrong Line\n" .
               "john;john@mail.it;50";

        $r = Verga::importer([
            'import' => null,
            'skip' => 2,
            'cols' => [
                'name'  => 0,
                'email' => [ 'col' => 1 ],
                'age'   => 2,
            ]
        ])
        ->importFromString($csv, false);

        $lines = $r->getLines();
        $this->assertCount(1, $lines);
        $this->assertCount(1, $r->getValidLines());
        $this->assertCount(0, $r->getInvalidLines());

        $this->assertequals($lines[0]->getparseddata(), [
            'name'  => 'john',
            'email' => 'john@mail.it',
            'age'   => 50,
        ]);
    }

    public function testColumnsNotFound()
    {
        $csv = "giova;giovanni@mail.it;23\n" .
               "skaffo;skaffo@mail.it;26\n" .
               "john;john@mail.it";

        $r = Verga::importer([
            'import' => null,
            'cols' => [
                'name'  => 0,
                'email' => [ 'col' => 1 ],
                'age'   => 2,
            ]
        ])->importFromString($csv, false);

        $lines = $r->getLines();
        $this->assertCount(3, $lines);
        $this->assertCount(2, $r->getValidLines());
        $this->assertCount(1, $r->getInvalidLines());
        // TODO: Try a better way to test column not founded...
        $this->assertInstanceOf(\Verga\Result\Line\InvalidLine::class, $lines[2]);
        $errors = $lines[2]->getColumnsErrors();
        $this->assertArrayHasKey('age', $errors);
        $this->assertEquals($errors['age'], "The column at position 2 doesn't exist.");
    }

    //public function testColumnsValidation()
    //{
    //}

    //public function testColumnsMapping()
    //{
    //}

    //public function testColumnsDefault()
    //{
    //}

    //public function testColumnsWithFixedValue()
    //{
    //}

    public function testColumnsCombine()
    {
        $csv = "giova;giovanni@mail.it;23\n" .
               "skaffo;skaffo@mail.it;26\n" .
               "john;john@mail.it";

        $r = Verga::importer([
            'import' => null,
            'cols' => [
                'some' => Verga::combine([
                    'very' => Verga::combine([
                        'very' => Verga::combine([
                            'deep' => Verga::combine([
                                'stuff' => 0
                            ])
                        ])
                    ])
                ]),
                'also' => Verga::combine([
                    'very' => Verga::combine([
                        'very' => Verga::combine([
                            'deep' => Verga::combine([
                                'problem' => [
                                    'col' => 1,
                                    'validate' => function ($value) {
                                        if ($value === 'john@mail.it') {
                                            return Verga::error('I hate john!');
                                        }
                                    }
                                ],
                            ])
                        ])
                    ])
                ])
            ]
        ])->importFromString($csv, false);

        $lines = $r->getLines();
        $this->assertEquals($lines[2]->getParsedData(), [
            'some' => [
                'very' => [
                    'very' => [
                        'deep' => [
                            'stuff' => 'john'
                        ]
                    ]
                ]
            ],
            'also' => [
                'very' => [
                    'very' => [
                        'deep' => [
                            'problem' => 'john@mail.it'
                        ]
                    ]
                ]
            ]
        ]);
        $this->assertEquals($lines[2]->getColumnsErrors(), [
            'also' => [
                'very' => [
                    'very' => [
                        'deep' => [
                            'problem' => 'I hate john!'
                        ]
                    ]
                ]
            ]
        ]);
    }

    public function testLineMapped()
    {
        $csv = "giova;giovanni@mail.it;23\n" .
               "skaffo;skaffo@mail.it;26\n" .
               "john;john@mail.it;30";

        $r = Verga::importer([
            'import' => null,
            'cols' => [
                'name'  => 0,
                'email' => [ 'col' => 1 ],
                'age'   => 2,
            ],
            'map' => function ($data ,$row, $lineNumber, $line) {
                return array_map(function ($value) use ($lineNumber) {
                    return 'Hello ' . $lineNumber . ' ' . $value;
                }, $data);
            }
        ])->importFromString($csv, false);

        $lines = $r->getLines();
        $this->assertEquals($lines[1]->getParsedData(), [
            'name'  => 'Hello 1 skaffo',
            'email' => 'Hello 1 skaffo@mail.it',
            'age'   => 'Hello 1 26',
        ]);
    }

    public function testLineValidate()
    {
        $csv = "giova;giovanni@mail.it;23\n" .
               "skaffo;skaffo@mail.it;26\n" .
               "john;john@mail.it;30";

        $r = Verga::importer([
            'import' => null,
            'cols' => [
                'name'  => 0,
                'email' => [ 'col' => 1 ],
                'age'   => 2,
            ],
            'validate' => function ($data ,$row, $lineNumber, $line) {
                if ($lineNumber === 1) {
                    return Verga::error('I hate line one');
                }
            }
        ])->importFromString($csv, false);

        $lines = $r->getLines();
        $this->assertEquals($lines[1]->getLineError(), 'I hate line one');
    }

    public function testImport()
    {
        $csv = "giova;giovanni@mail.it;23\n" .
               "skaffo;skaffo@mail.it;26\n" .
               "john;john@mail.it;30";

        $r = Verga::importer([
            'import' => function ($data) {
                return $data['age'] * 2;
            },
            'cols' => [
                'name'  => 0,
                'email' => [ 'col' => 1 ],
                'age'   => 2,
            ],
        ])->importFromString($csv);

        $lines = $r->getLines();
        $this->assertEquals($lines[0]->getImportedData(), 46);
    }
}
