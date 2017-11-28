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

use Lthrt\GeneratorBundle\Command\GenerateDoctrineEntityCommand;
use Lthrt\GeneratorBundle\Model\EntityGeneratorResult;
use Symfony\Component\Console\Tester\CommandTester;

class GenerateDoctrineEntityCommandTest extends GenerateCommandTest
{
    /**
     * @dataProvider getInteractiveCommandData
     */
    public function testInteractiveCommand(
        $options,
        $input,
        $expected
    ) {
        list($entity, $format, $fields) = $expected;

        $generator = $this->getGenerator();
        $generator
            ->expects($this->once())
            ->method('generate')
            ->with($this->getBundle(), $entity, $format, $fields)
            ->willReturn(new EntityGeneratorResult('', '', ''))
        ;

        $tester = new CommandTester($command = $this->getCommand($generator));
        $this->setInputs($tester, $command, $input);
        $tester->execute($options);
    }

    public function getInteractiveCommandData()
    {
        return [
            [[], "Acme2BlogBundle:Blog/Post\n", ['Blog\\Post', 'annotation', []]],
            [['entity' => 'Acme2BlogBundle:Blog/Post'], '', ['Blog\\Post', 'annotation', []]],
            [[], "Acme2BlogBundle:Blog/Post\nyml\n\n", ['Blog\\Post', 'yml', []]],
            [[], "Acme2BlogBundle:Blog/Post\nyml\ncreated_by\n\n255\nfalse\nfalse\ndescription\ntext\nfalse\ntrue\nupdated_at\ndatetime\ntrue\nfalse\nrating\ndecimal\n5\n3\nfalse\nfalse\n\n", ['Blog\\Post', 'yml', [
                ['fieldName' => 'createdBy', 'type' => 'string', 'length' => 255, 'columnName' => 'created_by'],
                ['fieldName' => 'description', 'type' => 'text', 'unique' => true, 'columnName' => 'description'],
                ['fieldName' => 'updatedAt', 'type' => 'datetimetz', 'nullable' => true, 'columnName' => 'updated_at'],
                ['fieldName' => 'rating', 'type' => 'decimal', 'precision' => 5, 'scale' => 3, 'columnName' => 'rating'],
            ]]],
            // Deprecated, to be removed in 4.0
            [['--entity' => 'Acme2BlogBundle:Blog/Post'], '', ['Blog\\Post', 'annotation', []]],
        ];
    }

    /**
     * @dataProvider getNonInteractiveCommandData
     */
    public function testNonInteractiveCommand(
        $options,
        $expected
    ) {
        list($entity, $format, $fields) = $expected;

        $generator = $this->getGenerator();
        $generator
            ->expects($this->once())
            ->method('generate')
            ->with($this->getBundle(), $entity, $format, $fields)
            ->willReturn(new EntityGeneratorResult('', '', ''))
        ;
        $generator
            ->expects($this->any())
            ->method('isReservedKeyword')
            ->will($this->returnValue(false))
        ;

        $tester = new CommandTester($this->getCommand($generator));
        $tester->execute($options, ['interactive' => false]);
    }

    public function getNonInteractiveCommandData()
    {
        return [
            [['entity' => 'Acme2BlogBundle:Blog/Post'], ['Blog\\Post', 'annotation', []]],
            [['entity' => 'Acme2BlogBundle:Blog/Post', '--format' => 'yml', '--fields' => 'created_by:string(255) updated_by:string(length=128 nullable=true) description:text rating:decimal(precision=7 scale=2)'], ['Blog\\Post', 'yml', [
                ['fieldName' => 'created_by', 'type' => 'string', 'length' => 255],
                ['fieldName' => 'updated_by', 'type' => 'string', 'length' => 128, 'nullable' => true],
                ['fieldName' => 'description', 'type' => 'text'],
                ['fieldName' => 'rating', 'type' => 'decimal', 'precision' => 7, 'scale' => 2],
            ]]],
            // Deprecated, to be removed in 4.0
            [['--entity' => 'Acme2BlogBundle:Blog/Post'], ['Blog\\Post', 'annotation', []]],
            [['--entity' => 'Acme2BlogBundle:Blog/Post', '--format' => 'yml', '--fields' => 'created_by:string(255) updated_by:string(length=128 nullable=true) description:text rating:decimal(precision=7 scale=2)'], ['Blog\\Post', 'yml', [
                ['fieldName' => 'created_by', 'type' => 'string', 'length' => 255],
                ['fieldName' => 'updated_by', 'type' => 'string', 'length' => 128, 'nullable' => true],
                ['fieldName' => 'description', 'type' => 'text'],
                ['fieldName' => 'rating', 'type' => 'decimal', 'precision' => 7, 'scale' => 2],
            ]]],
        ];
    }

    protected function getCommand($generator)
    {
        $command = new GenerateDoctrineEntityCommand();
        $command->setContainer($this->getContainer());
        $command->setHelperSet($this->getHelperSet());
        $command->setGenerator($generator);

        return $command;
    }

    protected function getGenerator()
    {
        // get a noop generator
        return $this
            ->getMockBuilder('Lthrt\GeneratorBundle\Generator\DoctrineEntityGenerator')
            ->disableOriginalConstructor()
            ->setMethods(['generate', 'isReservedKeyword'])
            ->getMock()
        ;
    }
}
