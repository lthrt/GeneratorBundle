<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Lthrt\GeneratorBundle\Tests\Manipulator;

use Lthrt\GeneratorBundle\Manipulator\RoutingManipulator;

class RoutingManipulatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider getImportedResourceYamlKeys
     */
    public function testGetImportedResourceYamlKey(
        $bundleName,
        $prefix,
        $expectedKey
    ) {
        $manipulator = new RoutingManipulator(__FILE__);
        $key         = $manipulator->getImportedResourceYamlKey($bundleName, $prefix);

        $this->assertEquals($expectedKey, $key);
    }

    public function getImportedResourceYamlKeys()
    {
        return [
            ['AppBundle', '', 'app'],
            ['AppBundle', '/', 'app'],
            ['AppBundle', '//', 'app'],
            ['AppBundle', '/{foo}', 'app'],
            ['AppBundle', '/{_foo}', 'app'],
            ['AppBundle', '/{/foo}', 'app'],
            ['AppBundle', '/{/foo/}', 'app'],
            ['AppBundle', '/{_locale}', 'app'],
            ['AppBundle', '/{_locale}/foo', 'app_foo'],
            ['AppBundle', '/{_locale}/foo/', 'app_foo'],
            ['AppBundle', '/{_locale}/foo/{_format}', 'app_foo'],
            ['AppBundle', '/{_locale}/foo/{_format}/', 'app_foo'],
            ['AppBundle', '/{_locale}/foo/{_format}/bar', 'app_foo_bar'],
            ['AppBundle', '/{_locale}/foo/{_format}/bar/', 'app_foo_bar'],
            ['AppBundle', '/{_locale}/foo/{_format}/bar//', 'app_foo_bar'],
            ['AcmeBlogBundle', '', 'acme_blog'],
            ['AcmeBlogBundle', '/', 'acme_blog'],
            ['AcmeBlogBundle', '//', 'acme_blog'],
            ['AcmeBlogBundle', '/{_locale}', 'acme_blog'],
            ['AcmeBlogBundle', '/{_locale}/foo', 'acme_blog_foo'],
            ['AcmeBlogBundle', '/{_locale}/foo/', 'acme_blog_foo'],
            ['AcmeBlogBundle', '/{_locale}/foo/{_format}', 'acme_blog_foo'],
            ['AcmeBlogBundle', '/{_locale}/foo/{_format}/', 'acme_blog_foo'],
            ['AcmeBlogBundle', '/{_locale}/foo/{_format}/bar', 'acme_blog_foo_bar'],
            ['AcmeBlogBundle', '/{_locale}/foo/{_format}/bar/', 'acme_blog_foo_bar'],
            ['AcmeBlogBundle', '/{_locale}/foo/{_format}/bar//', 'acme_blog_foo_bar'],
        ];
    }

    public function testHasResourceInAnnotation()
    {
        $tmpDir = sys_get_temp_dir() . '/sf';
        @mkdir($tmpDir, 0777, true);
        $file = tempnam($tmpDir, 'routing');

        $routing = <<<DATA
acme_demo:
    resource: "@AcmeDemoBundle/Controller/"
    type:     annotation
DATA;

        file_put_contents($file, $routing);

        $manipulator = new RoutingManipulator($file);
        $this->assertTrue($manipulator->hasResourceInAnnotation('AcmeDemoBundle'));
    }

    public function testHasResourceInAnnotationReturnFalseIfOnlyOneControllerDefined()
    {
        $tmpDir = sys_get_temp_dir() . '/sf';
        @mkdir($tmpDir, 0777, true);
        $file = tempnam($tmpDir, 'routing');

        $routing = <<<DATA
acme_demo_post:
    resource: "@AcmeDemoBundle/Controller/PostController.php"
    type:     annotation
DATA;

        file_put_contents($file, $routing);

        $manipulator = new RoutingManipulator($file);
        $this->assertFalse($manipulator->hasResourceInAnnotation('AcmeDemoBundle'));
    }
}
