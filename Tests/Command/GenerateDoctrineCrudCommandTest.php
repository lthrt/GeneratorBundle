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

use Symfony\Component\Console\Tester\CommandTester;

class GenerateDoctrineCrudCommandTest extends GenerateCommandTest
{
    /**
     * @dataProvider getInteractiveCommandData
     */
    public function testInteractiveCommand(
        $options,
        $input,
        $expected
    ) {
        list($entity, $format, $prefix, $withWrite) = $expected;

        $generator = $this->getGenerator();
        $generator
            ->expects($this->once())
            ->method('generate')
            ->with($this->getBundle(), $entity, $this->getDoctrineMetadata(), $format, $prefix, $withWrite)
        ;

        $tester = new CommandTester($command = $this->getCommand($generator));
        $this->setInputs($tester, $command, $input);
        $tester->execute($options);
    }

    public function getInteractiveCommandData()
    {
        return [
            [[], "AcmeBlogBundle:Blog/Post\n", ['Blog\\Post', 'annotation', 'blog_post', false]],
            [[], "AcmeBlogBundle:Blog/Post\ny\nyml\nfoobar\n", ['Blog\\Post', 'yml', 'foobar', true]],
            [[], "AcmeBlogBundle:Blog/Post\ny\nyml\n/foobar\n", ['Blog\\Post', 'yml', 'foobar', true]],
            [['entity' => 'AcmeBlogBundle:Blog/Post'], "\ny\nyml\nfoobar\n", ['Blog\\Post', 'yml', 'foobar', true]],
            [['entity' => 'AcmeBlogBundle:Blog/Post'], '', ['Blog\\Post', 'annotation', 'blog_post', false]],
            [['entity' => 'AcmeBlogBundle:Blog/Post', '--format' => 'yml', '--route-prefix' => 'foo', '--with-write' => true], '', ['Blog\\Post', 'yml', 'foo', true]],
            // Deprecated, to be removed in 4.0
            [['--entity' => 'AcmeBlogBundle:Blog/Post'], '', ['Blog\\Post', 'annotation', 'blog_post', false]],
            [['--entity' => 'AcmeBlogBundle:Blog/Post', '--format' => 'yml', '--route-prefix' => 'foo', '--with-write' => true], '', ['Blog\\Post', 'yml', 'foo', true]],

        ];
    }

    /**
     * @dataProvider getNonInteractiveCommandData
     */
    public function testNonInteractiveCommand(
        $options,
        $expected
    ) {
        list($entity, $format, $prefix, $withWrite) = $expected;

        $generator = $this->getGenerator();
        $generator
            ->expects($this->once())
            ->method('generate')
            ->with($this->getBundle(), $entity, $this->getDoctrineMetadata(), $format, $prefix, $withWrite)
        ;

        $tester = new CommandTester($this->getCommand($generator));
        $tester->execute($options, ['interactive' => false]);
    }

    public function getNonInteractiveCommandData()
    {
        return [
            [['entity' => 'AcmeBlogBundle:Blog/Post'], ['Blog\\Post', 'annotation', 'blog_post', false]],
            [['entity' => 'AcmeBlogBundle:Blog/Post', '--format' => 'yml', '--route-prefix' => 'foo', '--with-write' => true], ['Blog\\Post', 'yml', 'foo', true]],
            // Deprecated, to be removed in 4.0
            [['--entity' => 'AcmeBlogBundle:Blog/Post'], ['Blog\\Post', 'annotation', 'blog_post', false]],
            [['--entity' => 'AcmeBlogBundle:Blog/Post', '--format' => 'yml', '--route-prefix' => 'foo', '--with-write' => true], ['Blog\\Post', 'yml', 'foo', true]],
        ];
    }

    public function testCreateCrudWithAnnotationInNonAnnotationBundle()
    {
        $rootDir = $this->getContainer()->getParameter('kernel.root_dir');

        $routing = <<<DATA
acme_blog:
    resource: "@AcmeBlogBundle/Resources/config/routing.xml"
    prefix:   /
DATA;

        @mkdir($rootDir . '/config', 0777, true);
        file_put_contents($rootDir . '/config/routing.yml', $routing);

        $options  = [];
        $input    = "AcmeBlogBundle:Blog/Post\ny\nannotation\n/foobar\n";
        $expected = ['Blog\\Post', 'annotation', 'foobar', true];

        list($entity, $format, $prefix, $withWrite) = $expected;

        $generator = $this->getGenerator();
        $generator
            ->expects($this->once())
            ->method('generate')
            ->with($this->getBundle(), $entity, $this->getDoctrineMetadata(), $format, $prefix, $withWrite)
        ;

        $tester = new CommandTester($command = $this->getCommand($generator));
        $this->setInputs($tester, $command, $input);
        $tester->execute($options);

        $this->assertContains('acme_blog_post:', file_get_contents($rootDir . '/config/routing.yml'));
    }

    public function testCreateCrudWithAnnotationInAnnotationBundle()
    {
        $rootDir = $this->getContainer()->getParameter('kernel.root_dir');

        $routing = <<<DATA
acme_blog:
    resource: "@AcmeBlogBundle/Controller/"
    type:     annotation
DATA;

        @mkdir($rootDir . '/config', 0777, true);
        file_put_contents($rootDir . '/config/routing.yml', $routing);

        $options  = [];
        $input    = "AcmeBlogBundle:Blog/Post\ny\nyml\n/foobar\n";
        $expected = ['Blog\\Post', 'yml', 'foobar', true];

        list($entity, $format, $prefix, $withWrite) = $expected;

        $generator = $this->getGenerator();
        $generator
            ->expects($this->once())
            ->method('generate')
            ->with($this->getBundle(), $entity, $this->getDoctrineMetadata(), $format, $prefix, $withWrite)
        ;

        $tester = new CommandTester($command = $this->getCommand($generator));
        $this->setInputs($tester, $command, $input);
        $tester->execute($options);

        $this->assertEquals($routing, file_get_contents($rootDir . '/config/routing.yml'));
    }

    public function testAddACrudWithOneAlreadyDefined()
    {
        $rootDir = $this->getContainer()->getParameter('kernel.root_dir');

        $routing = <<<DATA
acme_blog:
    resource: "@AcmeBlogBundle/Controller/OtherController.php"
    type:     annotation
DATA;

        @mkdir($rootDir . '/config', 0777, true);
        file_put_contents($rootDir . '/config/routing.yml', $routing);

        $options  = [];
        $input    = "AcmeBlogBundle:Blog/Post\ny\nannotation\n/foobar\n";
        $expected = ['Blog\\Post', 'annotation', 'foobar', true];

        list($entity, $format, $prefix, $withWrite) = $expected;

        $generator = $this->getGenerator();
        $generator
            ->expects($this->once())
            ->method('generate')
            ->with($this->getBundle(), $entity, $this->getDoctrineMetadata(), $format, $prefix, $withWrite)
        ;

        $tester = new CommandTester($command = $this->getCommand($generator));
        $this->setInputs($tester, $command, $input);
        $tester->execute($options);

        $expected = '@AcmeBlogBundle/Controller/PostController.php';

        $this->assertContains($expected, file_get_contents($rootDir . '/config/routing.yml'));
    }

    protected function getCommand($generator)
    {
        $command = $this
            ->getMockBuilder('Lthrt\GeneratorBundle\Command\GenerateDoctrineCrudCommand')
            ->setMethods(['getEntityMetadata'])
            ->getMock()
        ;

        $command
            ->expects($this->any())
            ->method('getEntityMetadata')
            ->will($this->returnValue([$this->getDoctrineMetadata()]))
        ;

        $command->setContainer($this->getContainer());
        $command->setHelperSet($this->getHelperSet());
        $command->setGenerator($generator);
        $command->setFormGenerator($this->getFormGenerator());

        return $command;
    }

    protected function getDoctrineMetadata()
    {
        return $this
            ->getMockBuilder('Doctrine\ORM\Mapping\ClassMetadataInfo')
            ->disableOriginalConstructor()
            ->getMock()
        ;
    }

    protected function getGenerator()
    {
        // get a noop generator
        return $this
            ->getMockBuilder('Lthrt\GeneratorBundle\Generator\DoctrineCrudGenerator')
            ->disableOriginalConstructor()
            ->setMethods(['generate'])
            ->getMock()
        ;
    }

    protected function getFormGenerator()
    {
        return $this
            ->getMockBuilder('Lthrt\GeneratorBundle\Generator\DoctrineFormGenerator')
            ->disableOriginalConstructor()
            ->setMethods(['generate'])
            ->getMock()
        ;
    }

    protected function getBundle()
    {
        $bundle = parent::getBundle();
        $bundle
            ->expects($this->any())
            ->method('getName')
            ->will($this->returnValue('AcmeBlogBundle'))
        ;

        return $bundle;
    }

    protected function getContainer()
    {
        $container = parent::getContainer();

        $container->set('doctrine', $this->getDoctrine());

        return $container;
    }

    protected function getDoctrine()
    {
        $cache = $this->getMockBuilder('Doctrine\Common\Persistence\Mapping\Driver\MappingDriver')->getMock();
        $cache
            ->expects($this->any())
            ->method('getAllClassNames')
            ->will($this->returnValue(['Acme\Bundle\BlogBundle\Entity\Post']))
        ;

        $configuration = $this->getMockBuilder('Doctrine\ORM\Configuration')->getMock();
        $configuration
            ->expects($this->any())
            ->method('getMetadataDriverImpl')
            ->will($this->returnValue($cache))
        ;

        $configuration
            ->expects($this->any())
            ->method('getEntityNamespaces')
            ->will($this->returnValue(['AcmeBlogBundle' => 'Acme\Bundle\BlogBundle\Entity']))
        ;

        $manager = $this->getMockBuilder('Doctrine\ORM\EntityManagerInterface')->getMock();
        $manager
            ->expects($this->any())
            ->method('getConfiguration')
            ->will($this->returnValue($configuration))
        ;

        $registry = $this->getMockBuilder('Symfony\Bridge\Doctrine\RegistryInterface')->getMock();
        $registry
            ->expects($this->any())
            ->method('getAliasNamespace')
            ->will($this->returnValue('Acme\Bundle\BlogBundle\Entity\Blog\Post'))
        ;

        $registry
            ->expects($this->any())
            ->method('getManager')
            ->will($this->returnValue($manager))
        ;

        return $registry;
    }
}
