<?php

namespace Verga;

class Verga {

    /**
     * Build importer.
     *
     * @param  array $config
     * @return \Verga\Importer\CsvImporter
     */
    public static function importer(array $config)
    {
        $c = array_merge([
            'delimiter' => ';',
            'skip'      => 0,
            'cols'      => [],
            'validate'  => null,
            'map'       => null,
        ], $config);

        return new Importer\CsvImporter(
            $c['delimiter'],
            $c['skip'],
            $c['cols'],
            $c['validate'],
            $c['map'],
            $c['import']
        );
    }

    /**
     * Combine meta used for configuration.
     *
     * @param  array $columns
     * @return \Verga\Meta\Combine
     */
    public static function combine(array $columns)
    {
        return new Meta\Combine($columns);
    }

    /**
     * Combine meta used for error managment.
     *
     * @param  string $error
     * @return \Verga\Meta\Error
     */
    public static function error($error)
    {
        return new Meta\Error($error);
    }
}
