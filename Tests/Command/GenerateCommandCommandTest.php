<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Lthrt\GeneratorBundle\Tests\Command;

use Lthrt\GeneratorBundle\Command\GenerateCommandCommand;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class GenerateCommandCommandTest extends GenerateCommandTest
{
    protected $generator;

    /**
     * @dataProvider getInteractiveCommandData
     */
    public function testInteractiveCommand(
        $options,
        $input,
        $expected
    ) {
        list($bundle, $name) = $expected;

        $generator = $this->getGenerator();
        $generator
            ->expects($this->once())
            ->method('generate')
            ->with($this->getBundle(), $name)
        ;

        $tester = new CommandTester($command = $this->getCommand($generator));
        $this->setInputs($tester, $command, $input);
        $tester->execute($options);
    }

    public function getInteractiveCommandData()
    {
        return [
            [
                [],
                "FooBarBundle\napp:foo-bar\n",
                ['FooBarBundle', 'app:foo-bar'],
            ],

            [
                ['bundle' => 'FooBarBundle'],
                "app:foo-bar\n",
                ['FooBarBundle', 'app:foo-bar'],
            ],

            [
                ['name' => 'app:foo-bar'],
                "FooBarBundle\n",
                ['FooBarBundle', 'app:foo-bar'],
            ],

            [
                ['bundle' => 'FooBarBundle', 'name' => 'app:foo-bar'],
                '',
                ['FooBarBundle', 'app:foo-bar'],
            ],
        ];
    }

    /**
     * @dataProvider getNonInteractiveCommandData
     */
    public function testNonInteractiveCommand(
        $options,
        $expected
    ) {
        list($bundle, $name) = $expected;

        $generator = $this->getGenerator();
        $generator
            ->expects($this->once())
            ->method('generate')
            ->with($this->getBundle(), $name)
        ;

        $tester = new CommandTester($command = $this->getCommand($generator));
        $tester->execute($options, ['interactive' => false]);
    }

    public function getNonInteractiveCommandData()
    {
        return [
            [
                ['bundle' => 'FooBarBundle', 'name' => 'app:my-command'],
                ['FooBarBundle', 'app:my-command'],
            ],
        ];
    }

    protected function getCommand($generator)
    {
        $command = new GenerateCommandCommand();

        $command->setContainer($this->getContainer());
        $command->setHelperSet($this->getHelperSet());
        $command->setGenerator($generator);

        return $command;
    }

    protected function getApplication($input = '')
    {
        $application = new Application();

        $command = new GenerateCommandCommand();
        $command->setContainer($this->getContainer());
        $command->setHelperSet($this->getHelperSet($input));
        $command->setGenerator($this->getGenerator());

        $application->add($command);

        return $application;
    }

    protected function getGenerator()
    {
        if (null === $this->generator) {
            $this->setGenerator();
        }

        return $this->generator;
    }

    protected function setGenerator()
    {
        // get a noop generator
        $this->generator = $this
            ->getMockBuilder('Lthrt\GeneratorBundle\Generator\CommandGenerator')
            ->disableOriginalConstructor()
            ->setMethods(['generate'])
            ->getMock()
        ;
    }

    protected function getBundle()
    {
        if (null == $this->bundle) {
            $this->setBundle();
        }

        return $this->bundle;
    }

    protected function setBundle()
    {
        $bundle = $this->getMockBuilder('Symfony\Component\HttpKernel\Bundle\BundleInterface')->getMock();
        $bundle->expects($this->any())->method('getPath')->will($this->returnValue(''));
        $bundle->expects($this->any())->method('getName')->will($this->returnValue('FooBarBundle'));
        $bundle->expects($this->any())->method('getNamespace')->will($this->returnValue('Foo\BarBundle'));

        $this->bundle = $bundle;
    }
}
