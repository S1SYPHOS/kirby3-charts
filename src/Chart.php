<?php

namespace Fundevogel;

use Kirby\Toolkit\A;

use Aeq\LargestRemainder\Math\LargestRemainder;


/**
 * Class Chart
 *
 * Wrapper for `SVGGraph` by goat1000
 *
 * See https://github.com/goat1000/SVGGraph
 */
class Chart
{
    /**
     * Processed data
     *
     * @var array
     */
    public $data;


    /**
     * Colors
     *
     * @var array
     */
    public $colors = [];


    /**
     * Canvas width
     *
     * @var int
     */
    public $width = 100;


    /**
     * Canvas height
     *
     * @var int
     */
    public $height = 100;


    /**
     * Constructor
     *
     * @param array $data Source data
     * @param int $precision Rounding precision
     * @return void
     */
    public function __construct(array $data, int $precision = 0)
    {
        # If precision specified ..
        if ($precision >= 0) {
            # .. round values safely, using 'largest remainder' method,
            # see https://en.wikipedia.org/wiki/Largest_remainder_method
            $largestRemainder = new LargestRemainder(A::pluck($data, 'share'));
            $largestRemainder->setPrecision($precision);

            $values = $largestRemainder->round();

            for ($i = 0; $i < count($values); $i++) {
                $data[$i]['share'] = $values[$i];
            }
        }

        $this->data = $data;

        if (!empty($colors)) {
            $this->colors = $colors;
        }
    }


    /**
     * Creates SVG graph
     *
     * @param string $type `SVGGraph` type
     * @param array $options `SVGGraph` options
     * @return string
     */
    public function render(string $type, array $options = []): string
    {
        # Generate chart from language data
        $graph = new \Goat1000\SVGGraph\SVGGraph($this->width, $this->height, A::update([
            # Defaults
            # (1) General options
            'sort' => false,

            # (2) SVG options
            'auto_fit' => true,
            'svg_class' => 'w-6 h-6',

            # (3) Graph options
            # (a) Background
            'back_colour' => null,
            'back_stroke_width' => 0,

            # (b) Padding
            'pad_bottom' => 0,
            'pad_left'   => 0,
            'pad_right'  => 0,
            'pad_top'    => 0,

            # (c) Remove labels
            'show_labels' => false,

            # (d) Remove JavaScript
            'show_tooltips' => false,

            # For lists of types, options & examples,
            # see https://www.goat1000.com/svggraph.php
            ##
        ], $options));

        # Generate graph
        # (1) Pass collected data
        $graph->values(array_combine(A::pluck($this->data, 'title'), A::pluck($this->data, 'share')));
        $graph->colours(A::pluck($this->data, 'color'));

        # (2) Render time!
        return $graph->fetch($type, false);
    }
}
