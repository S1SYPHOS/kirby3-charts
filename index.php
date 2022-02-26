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

    $file = new File([
        'filename' => sprintf('chart-%s.svg', hash('md5', $content)),
        'parent' => $page,
        'template' => option('fundevogel.charts.template', 'chart'),
    ]);

    $file->update([
        'entries' => Yaml::encode($chart->data),
    ]);

    $inline = $settings['inline'] ?? option('fundevogel.charts.inline', false);

    if ($file->exists()) {
        if ($inline) {
            return svg($file);
        }

        return $file;
    }

    if (F::write($file->root(), $content)) {
        if ($inline) {
            return svg($file);
        }

        return $file;
    }

    throw new Exception('Couldn\'t create chart!');
}


Kirby::plugin('fundevogel/charts', [
    'blueprints' => [
        'fields/chart' => __DIR__ . '/blueprints/field.yml',
        'files/chart' => __DIR__ . '/blueprints/file.yml',
    ],
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
