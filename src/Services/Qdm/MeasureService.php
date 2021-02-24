<?php


namespace OpenEMR\Services\Qdm;


class MeasureService
{
    public static $measure_sources = [
        'projecttacoma/cqm-execution' =>'/node_modules/cqm-execution/spec/fixtures/json/cqm_measures',
        'contrib' => '/contrib/ecqm/EP-EC-eCQM-2020-05'
    ];

    public static function fetchMeasureSourceOptions()
    {
        return self::$measure_sources;
    }

    public static function fetchMeasureOptions($source)
    {
        $measureSourcePath = self::$measure_sources[$source];
        $measurePath = $GLOBALS['fileroot'] . $measureSourcePath;
        $options = [];
        foreach (glob("$measurePath/*", GLOB_ONLYDIR) as $measureDirectory) {
            $options[basename($measureDirectory)] = $measureDirectory;
        }

        return $options;
    }

    /**
     * Given full path to the measure directory, get the paths to the
     * measure file, and the value sets file.
     *
     * @param $measurePath
     * @return string[]
     */
    public static function fetchMeasureFiles($measurePath)
    {
        return [
            'measure' => $measurePath . '/' . basename($measurePath) . '.json',
            'valueSets' => $measurePath . '/value_sets.json'
        ];
    }

}
