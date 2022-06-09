<?php

namespace Fundevogel\ChartsPlugin\Tests;

use Kirby\Cms\File;


/**
 * Class ChartTest
 *
 * Adds tests for class 'Chart'
 */
final class ChartTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Properties
     */

    /**
     * Generated file
     *
     * @var \Kirby\Cms\File
     */
    private $file;


    /**
     * Chart data
     *
     * @var array
     */
    private $data = [
        ['title' => 'HTML', 'color' => '#4F5D95', 'share' => 0.6],
        ['title' => 'CSS', 'color' => '#2b7489', 'share' => 0.4],
    ];


    /**
     * SVG content
     *
     * @var string
     */
    private static $content = '';


    /**
     * Setup
     */

    public static function setUpBeforeClass(): void
    {
        # Ask permissions
        kirby()->impersonate('kirby');

        # Remove files
        foreach (page('home')->files() as $file) {
            $file->delete();
        }

        # Create SVG content (for humans)
        self::$content .= '<svg version="1.1" width="100%" height="100%" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 100 100" class="w-6 h-6">';
        self::$content .= '<rect width="100%" height="100%" fill="none" stroke-width="0px"/>';
        self::$content .= '<path fill="#4F5D95" stroke="rgb(0,0,0)" stroke-width="1px" stroke-linejoin="round" d="M29.77 35.31A25 25 0 1 0 75 50L100 50A50 50 0 1 1 9.55 20.61z"/>';
        self::$content .= '<path fill="#2b7489" stroke="rgb(0,0,0)" stroke-width="1px" stroke-linejoin="round" d="M75 50A25 25 0 0 0 29.77 35.31L9.55 20.61A50 50 0 0 1 100 50z"/>';
        self::$content .= "</svg>\n";
    }


    public function setUp(): void
    {
        $this->file = null;
    }


    public function tearDown(): void
    {
        if ($this->file && $this->file->delete()) {
            $this->file = null;
        }
    }


    /**
     * Tests
     */

    public function testPageToChart(): void
    {
        # Setup
        # (1) Ask permissions
        kirby()->impersonate('kirby');

        # Run function
        $this->file = page('home')->toChart($this->data, ['inline' => false]);

        # Assert result
        $this->assertEquals($this->file->read(), self::$content);
    }


    public function testPageToChartInline(): void
    {
        # Run function
        $result = page('home')->toChart($this->data, ['inline' => true]);

        # Assert result
        $this->assertEquals($result, self::$content);
    }


    public function testFieldToChart(): void
    {
        # Setup
        # (1) Ask permissions
        kirby()->impersonate('kirby');

        # Run function
        $this->file = page('home')->entries()->toChart(['inline' => false]);

        # Assert result
        $this->assertEquals($this->file->read(), self::$content);
    }


    public function testFieldToChartInline(): void
    {
        # Run function
        $result = page('home')->entries()->toChart(['inline' => true]);

        # Assert result
        $this->assertEquals($result, self::$content);
    }}
