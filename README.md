[![Build Status](https://travis-ci.org/gffuma/verga.svg?branch=master)](https://travis-ci.org/gffuma/verga)

# Verga
Verga, a battle-tested CSV importer for PHP

![](https://raw.githubusercontent.com/gffuma/verga/master/verga.gif)

## Installation
```
composer require gffuma/verga
```

## Usage

This code is the same of [example](examples/import.php).

```php
use Verga\Verga;

/**
 * Make the csv importer.
 *
 */
$importer = Verga::importer([

    /**
     * Number of rows to skip.
     * Default: 0
     *
     */
    'skip' => 1,

    /**
     * String used to parse csv lines.
     * Default ';'
     *
     */
    'delimiter' => ';',

    /**
     * Columns configuration.
     *
     */
    'cols' => [

        /**
         * Configure each column.
         *
         */
        'email' => [

            /**
             * Map the csv column at index 1 with the `email` field.
             *
             */
            'col' => 1,

            /**
             * When set to true and there is no column index in current csv
             * line. The line fail and the error is reported.
             * Default: true
             *
             */
            'required' => false,

            /**
             * The default value of column when is not required.
             * Default: null
             *
             */
            'default' => 'nobody@mail.it',

            /**
             * You can map every column value with a callback.
             *
             */
            'map' => function (

                /**
                 * The value of column.
                 *
                 */
                $value,

                /**
                 * The data parsed up to here.
                 *
                 */
                $data,

                /**
                 * All the csv row parsed by delimiter.
                 *
                 */
                $row,

                /**
                 * The csv line number.
                 *
                 */
                $lineNumber,

                /**
                 * Original csv line.
                 *
                 */
                $line
            ) {
                return strtolower($value);
            },

            /**
             * You can also validate each column value.
             *
             */
            'validate' => function ($value /*, $data, $row, $lineNumber, $line*/ ) {
                if (! filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    return Verga::error("The email {$value} is invalid!");
                }
            }
        ],

        /**
         * This is a shortcut for:
         *
         * 'name' => [
         *     'col' => 0
         * ],
         */
        'name' => 0,

        /**
         * Map column to nested array:
         *
         */
        'info' => Verga::combine([

            'role' => Verga::combine([

                'name' => 2,

                'level' => [

                    'col' => 3,

                    'required' => false,

                    'default' => 'newbie',
                ],
            ]),
        ]),

        /**
         * You can also provide a fixed value for a column.
         *
         */
        'message' => [
            'value' => 'Imported from csv at ' . date('Y-m-d H:i:s')
        ],
    ],

    /**
     * Validate the entire line when provided.
     *
     */
    'validate' => function ($data, $row, $lineNumber, $line) {
        if ($lineNumber === 2) {
            return Verga::error('Sorry but i hate the second line :)');
        }
    },

    /**
     * Map the entire line when provided.
     *
     */
    'map' => function ($data /*,$row, $lineNumber, $line*/ ) {
        return array_map(function ($value) {
            if (is_array($value)) {
                return $value;
            }
            return '~~~' . strtoupper($value) . '~~~';
        }, $data);
    },


    /**
     * The import callback.
     *
     */
    'import' => function ($data /*, $row, $lineNumber, $line*/ ) {
        return 'Just import my friend ' . $data['name'];
    }
]);

/**
 * Now the imported is built, you can import the csv from differente sources:
 * file, url or direct from a string.
 *
 * The $result is an instance of Verga\Result\Result
 *
 * This class contains all the information and util methods
 * to inspect the result of current import.
 */
$result = $importer->importFromFile(
    /**
     * Source value.
     *
     */
    'users.csv',

    /**
     * Should import runned?
     * When is set to false the csv lines are processed and parsed
     * but the import callback is not runned.
     * Useful if you want only show the possible results before run
     * the real import...
     * Default: true
     */
    true
);
//$result = $importer->importFromUrl('https://somewhere.com/users.csv');
//$result = $importer->importFromString($_POST['csv_to_parse']);

// You can also get the lines alredy filtered:
// $result->getValidLines()
// $result->getInvalidLines()
foreach ($result->getLines() as $line) {

    // Is the line valid?
    $line->isValid();

    // Is the line invalid?
    $line->isInvalid();

    // Is the line imported?
    // true when is a valid line the import callback was runned
    $line->isImported();

    // Line original line number of csv from 0 to N
    $line->getLineNumber();

    // Original csv line
    $line->getLine();

    // The row parsed
    $line->getRow();

    // The parsed data
    $line->getParsedData();

    // The imported data returned from import callback
    $line->getImportedLine();

    // The columns errors when line is invalid
    $line->getColumnsErrors();
    $line->hasColumnsErrors();

    // The line error when line is invalid
    $line->getLineError();
    $line->hasLineError();
}
```
