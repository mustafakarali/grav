<?php

use Codeception\Util\Fixtures;
use Grav\Common\Utils;

class AssetsTest extends \Codeception\TestCase\Test
{
    /**
     * @var \UnitTester
     */
    protected $tester;

    protected function _before()
    {
    }

    protected function _after()
    {
    }

    public function grav()
    {
        $grav = Fixtures::get('grav');
        return $grav;
    }

    public function assets()
    {
        return $this->grav()['assets'];
    }

    public function testAddingAssets()
    {
        $assets = $this->assets();

        //test add()
        $assets->add('test.css');

        $css = $assets->css();
        $this->assertSame($css, '<link href="test.css" type="text/css" rel="stylesheet" />' . PHP_EOL);

        $assets->add('test.js');
        $js = $assets->js();
        $this->assertSame($js, '<script src="test.js" type="text/javascript" ></script>' . PHP_EOL);

        //test addCss(). Test adding asset to a separate group
        $assets->reset();
        $assets->addCSS('test.css');
        $css = $assets->css();
        $this->assertSame($css, '<link href="test.css" type="text/css" rel="stylesheet" />' . PHP_EOL);

        //test addJs()
        $assets->reset();
        $assets->addJs('test.js');
        $js = $assets->js();
        $this->assertSame($js, '<script src="test.js" type="text/javascript" ></script>' . PHP_EOL);

        //Test CSS Groups
        $assets->reset();
        $assets->addCSS('test.css', null, true, 'footer');
        $css = $assets->css();
        $this->assertEmpty($css);
        $css = $assets->css('footer');
        $this->assertSame($css, '<link href="test.css" type="text/css" rel="stylesheet" />' . PHP_EOL);

        //Test JS Groups
        $assets->reset();
        $assets->addJs('test.js', null, true, null, 'footer');
        $js = $assets->js();
        $this->assertEmpty($js);
        $js = $assets->js('footer');
        $this->assertSame($js, '<script src="test.js" type="text/javascript" ></script>' . PHP_EOL);

        //Test async / defer
        $assets->reset();
        $assets->addJs('test.js', null, true, 'async', null);
        $js = $assets->js();
        $this->assertSame($js, '<script src="test.js" type="text/javascript" async></script>' . PHP_EOL);
        $assets->reset();
        $assets->addJs('test.js', null, true, 'defer', null);
        $js = $assets->js();
        $this->assertSame($js, '<script src="test.js" type="text/javascript" defer></script>' . PHP_EOL);


    }

    public function testPriorityOfAssets()
    {
        $assets = $this->assets();

        $assets->reset();
        $assets->add('test.css');
        $assets->add('test-after.css');

        $css = $assets->css();
        $this->assertSame($css, '<link href="test.css" type="text/css" rel="stylesheet" />' . PHP_EOL .
            '<link href="test-after.css" type="text/css" rel="stylesheet" />' . PHP_EOL);

        //----------------
        $assets->reset();
        $assets->add('test-after.css', 1);
        $assets->add('test.css', 2);

        $css = $assets->css();
        $this->assertSame($css, '<link href="test.css" type="text/css" rel="stylesheet" />' . PHP_EOL .
            '<link href="test-after.css" type="text/css" rel="stylesheet" />' . PHP_EOL);

        //----------------
        $assets->reset();
        $assets->add('test-after.css', 1);
        $assets->add('test.css', 2);
        $assets->add('test-before.css', 3);

        $css = $assets->css();
        $this->assertSame($css, '<link href="test-before.css" type="text/css" rel="stylesheet" />' . PHP_EOL .
            '<link href="test.css" type="text/css" rel="stylesheet" />' . PHP_EOL .
            '<link href="test-after.css" type="text/css" rel="stylesheet" />' . PHP_EOL);
    }

    public function testPipeline()
    {
        $assets = $this->assets();

        $assets->reset();

        //File not existing. Pipeline searches for that file without reaching it. Output is empty.
        $assets->add('test.css', null, true);
        $assets->setCssPipeline(true);
        $css = $assets->css();
        $this->assertSame($css, '');

        //Add a core Grav CSS file, which is found. Pipeline will now return a file
        $assets->add('/system/assets/debugger.css', null, true);
        $css = $assets->css();
        $this->assertContains('<link href=', $css);
        $this->assertContains('type="text/css" rel="stylesheet" />', $css);
    }

}