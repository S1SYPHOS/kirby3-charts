<?php

@include_once __DIR__ . '/vendor/autoload.php';

use Kirby\Cms\Field;
use Kirby\Cms\File;
use Kirby\Cms\Page;
use Kirby\Data\Yaml;
use Kirby\Filesystem\F;
use Kirby\Toolkit\A;


load([
    'fundevogel\\chart' => 'src/Chart.php'
], __DIR__);

use Fundevogel\Chart;


/**
 * @param Kirby\Cms\Page $page
 * @param array $entries Data entries
 * @param array $options Graph settings
 * @param array $options `SVGGraph` options
 * @return Kirby\Cms\File|string
 */
function renderChart(Page $page, array $entries, array $settings = [], ?array $options = [])
{
    # Create chart
    $chart = new Chart($entries, option('fundevogel.charts.precision', 2));

    # Tweak chart setup
    $chart->width = $settings['width'] ?? option('fundevogel.charts.width', 100);
    $chart->height = $settings['height'] ?? option('fundevogel.charts.height', 100);

    # Determine type of chart
    $type = $settings['type'] ?? option('fundevogel.charts.type', 'DonutGraph');

    # Render time!
    $content = $chart->render($type, $options);

    # If specified ..
    if ($settings['inline'] ?? option('fundevogel.charts.inline', false)) {
        # .. provide SVG as string
        return $content;
    }

    $file = new File([
        'filename' => sprintf('chart-%s.svg', hash('md5', $content)),
        'parent' => $page,
        'template' => option('fundevogel.charts.template', 'chart'),
    ]);

    try {

        $file->update([
            'entries' => Yaml::encode($chart->data),
        ]);

        if (!$file->exists()) {
            $file->write($content);
        }

    } catch (Exception $e) {
        throw new Exception(sprintf('Creating chart failed: "%s"', $e));
    }

    return $file;
}


Kirby::plugin('fundevogel/charts', [
    /**
     * Blueprints
     */
    'blueprints' => [
        'fields/chart' => __DIR__ . '/blueprints/field.yml',
        'files/chart'  => __DIR__ . '/blueprints/file.yml',
    ],


    /**
     * Page methods
     */
    'pageMethods' => [
        /**
         * Creates chart
         *
         * @param array $entries Data entries
         * @param array $settings SVG settings
         * @param array $options `SVGGraph` options
         * @return Kirby\Cms\File|string
         */
        'toChart' => function (array $entries, array $settings = [], array $options = [])
        {
            # Render chart
            return renderChart($this, $entries, $settings, $options);
        },
    ],


    /**
     * Field methods
     */
    'fieldMethods' => [
        /**
         * Creates chart
         *
         * @param Kirby\Cms\Field $field
         * @param array $settings SVG settings
         * @param array $options `SVGGraph` options
         * @return Kirby\Cms\File|string
         */
        'toChart' => function (Field $field, array $settings = [], array $options = [])
        {
            # Render chart
            return renderChart($field->model(), $field->toStructure()->toArray(), $settings, $options);
        },
    ],
]);
