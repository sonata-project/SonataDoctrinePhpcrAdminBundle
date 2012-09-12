<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\DoctrinePHPCRAdminBundle\Tests\Builder;

use Sonata\DoctrinePHPCRAdminBundle\Builder\ListBuilder;

class ListBuilderTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->guesser = $this->getMock('\Sonata\AdminBundle\Guesser\TypeGuesserInterface', array(), array());
        $this->templates = array();
    }

    public function testGetBaseList()
    {
        $lb = new ListBuilder($this->guesser, $this->templates);
        $this->assertInstanceOf('Sonata\AdminBundle\Admin\FieldDescriptionCollection', $lb->getBaseList());
    }

    private function setupAddField()
    {
        $this->lb = new ListBuilder($this->guesser, $this->templates);
        $this->metaData = $this->getMock('\Doctrine\ODM\PHPCR\Mapping\ClassMetadata', array(), array(), '', false);
        $this->modelManager = $this->getMockBuilder('\Sonata\DoctrinePHPCRAdminBundle\Model\ModelManager')->disableOriginalConstructor()->getMock();
        $this->modelManager->expects($this->any())
            ->method('getMetadata')
            ->will($this->returnValue($this->metaData));
        $this->modelManager->expects($this->any())
            ->method('hasMetadata')
            ->with($this->anything())
            ->will($this->returnValue(true));

        $this->fieldDescription = $this->getMock('\Sonata\AdminBundle\Admin\FieldDescriptionInterface');
        $this->fieldDescription->expects($this->any())
            ->method('getModelManager')
            ->will($this->returnValue($this->modelManager));
        $this->fieldDescription->expects($this->any())
            ->method('getType')
            ->will($this->returnValue('string'));
        $this->fieldDescription->expects($this->once())
            ->method('setType')
            ->with($this->anything());

        //AdminInterface doesn't implement methods called in addField,
        //so we mock Admin
        $this->admin = $this->getMock('\Sonata\AdminBundle\Admin\Admin', array(), array(), '', false);
        $this->admin->expects($this->any())
            ->method('getModelManager')
            ->will($this->returnValue($this->modelManager));
        $this->admin->expects($this->once())
            ->method('addListFieldDescription')
            ->with($this->anything(), $this->fieldDescription);

        $this->fieldDescriptionCollection = $this->getMock('\Sonata\AdminBundle\Admin\FieldDescriptionCollection', array(), array());
        $this->fieldDescriptionCollection->expects($this->once())
            ->method('add')
            ->with($this->fieldDescription);
    }

    public function testAddField()
    {
        $this->setupAddField();
        $this->lb->addField($this->fieldDescriptionCollection, 'string', $this->fieldDescription, $this->admin);
    }

    public function testAddFieldNullType()
    {
        $typeguess = $this->getMock('Symfony\Component\Form\Guess\TypeGuess', array(), array(), '', false);
        $this->guesser->expects($this->once())
            ->method('guessType')
            ->with($this->anything())
            ->will($this->returnValue($typeguess));
        $this->setupAddField();
        $this->lb->addField($this->fieldDescriptionCollection, null, $this->fieldDescription, $this->admin);
    }

    //public function testAddField()
    //{
    //    $fieldDescriptionCollection = $this->getMock('\Sonata\AdminBundle\Admin\FieldDescriptionCollection', array(), array());
    //    $fieldDescription = $this->getMock('\Sonata\AdminBundle\Admin\FieldDescriptionInterface', array(), array());
    //    $admin = $this->getMock('\Sonata\AdminBundle\Admin\AdminInterface', array(), array());
    //    $lb = new ListBuilder($this->guesser, $this->templates);

    //    $lb->addField($fieldDescriptionCollection, 'sometype', $fieldDescription, $admin);

    //}
}
