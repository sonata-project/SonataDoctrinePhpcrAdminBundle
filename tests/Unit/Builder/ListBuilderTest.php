<?php

declare(strict_types=1);

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\DoctrinePHPCRAdminBundle\Tests\Unit\Builder;

use Doctrine\ODM\PHPCR\Mapping\ClassMetadata;
use PHPUnit\Framework\TestCase;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Admin\FieldDescriptionCollection;
use Sonata\AdminBundle\Admin\FieldDescriptionInterface;
use Sonata\AdminBundle\Guesser\TypeGuesserInterface;
use Sonata\DoctrinePHPCRAdminBundle\Admin\Admin;
use Sonata\DoctrinePHPCRAdminBundle\Admin\FieldDescription;
use Sonata\DoctrinePHPCRAdminBundle\Builder\ListBuilder;
use Sonata\DoctrinePHPCRAdminBundle\Model\ModelManager;
use Symfony\Component\Form\Guess\TypeGuess;

class ListBuilderTest extends TestCase
{
    /**
     * @var ListBuilder
     */
    private $lb;

    /**
     * @var Admin
     */
    private $admin;

    /**
     * @var ModelManager
     */
    private $modelManager;

    /**
     * @var FieldDescriptionInterface
     */
    private $fieldDescription;

    /**
     * @var FieldDescriptionCollection
     */
    private $fieldDescriptionCollection;

    /**
     * @var TypeGuesserInterface
     */
    private $guesser;

    public function setUp(): void
    {
        $this->guesser = $this->createMock(TypeGuesserInterface::class, [], []);
        $this->templates = [];
    }

    public function testGetBaseList(): void
    {
        $lb = new ListBuilder($this->guesser, $this->templates);
        $this->assertInstanceOf(FieldDescriptionCollection::class, $lb->getBaseList());
    }

    public function testAddField(): void
    {
        $this->setupAddField();
        $this->lb->addField($this->fieldDescriptionCollection, 'string', $this->fieldDescription, $this->admin);
    }

    public function testAddFieldNullType(): void
    {
        $typeguess = $this->createMock(TypeGuess::class, [], [], '', false);
        $this->guesser->expects($this->once())
            ->method('guessType')
            ->with($this->anything())
            ->will($this->returnValue($typeguess));
        $this->setupAddField();
        $this->lb->addField($this->fieldDescriptionCollection, null, $this->fieldDescription, $this->admin);
    }

    public function testAddListActionField(): void
    {
        $this->setUpListActionTests();

        $fieldDescription = new FieldDescription();
        $fieldDescription->setName('foo');
        $list = $this->listBuilder->getBaseList();
        $this->listBuilder
            ->addField($list, 'actions', $fieldDescription, $this->admin);

        $this->assertSame(
            '@SonataAdmin/CRUD/list__action.html.twig',
            $list->get('foo')->getTemplate(),
            'Custom list action field has a default list action template assigned'
        );
    }

    public function testCorrectFixedActionsFieldType(): void
    {
        $this->setUpListActionTests();
        $this->guesser->expects($this->once())->method('guessType')->willReturn(null);

        $fieldDescription = new FieldDescription();
        $fieldDescription->setName('_action');
        $list = $this->listBuilder->getBaseList();
        $this->listBuilder->addField($list, null, $fieldDescription, $this->admin);

        $this->assertSame(
            'actions',
            $list->get('_action')->getType(),
            'Standard list _action field has "actions" type'
        );
    }

    //public function testAddField()
    //{
    //    $fieldDescriptionCollection = $this->createMock('\Sonata\AdminBundle\Admin\FieldDescriptionCollection', [], []);
    //    $fieldDescription = $this->createMock('\Sonata\AdminBundle\Admin\FieldDescriptionInterface', [], []);
    //    $admin = $this->createMock('\Sonata\AdminBundle\Admin\AdminInterface', [], []);
    //    $lb = new ListBuilder($this->guesser, $this->templates);

    //    $lb->addField($fieldDescriptionCollection, 'sometype', $fieldDescription, $admin);

    //}

    protected function setUpListActionTests(): void
    {
        $this->metaData = $this->createMock(ClassMetadata::class);
        $this->modelManager = $this->createMock(ModelManager::class);
        $this->modelManager->expects($this->any())
            ->method('getMetadata')
            ->will($this->returnValue($this->metaData));
        $this->modelManager->expects($this->any())
            ->method('hasMetadata')
            ->with($this->anything())
            ->will($this->returnValue(true));

        $this->admin = $this->createMock(Admin::class, [], [], '', false);
        $this->admin->expects($this->atLeastOnce())->method('getModelManager')
            ->willReturn($this->modelManager);

        $this->listBuilder = new ListBuilder($this->guesser);
    }

    private function setupAddField(): void
    {
        $this->lb = new ListBuilder($this->guesser, $this->templates);
        $this->metaData = $this->createMock(ClassMetadata::class, [], [], '', false);
        $this->modelManager = $this->createMock(ModelManager::class);
        $this->modelManager->expects($this->any())
            ->method('getMetadata')
            ->will($this->returnValue($this->metaData));
        $this->modelManager->expects($this->any())
            ->method('hasMetadata')
            ->with($this->anything())
            ->will($this->returnValue(true));

        $this->fieldDescription = $this->createMock(FieldDescriptionInterface::class);
        $this->fieldDescription->expects($this->any())
            ->method('getType')
            ->will($this->returnValue('string'));
        $this->fieldDescription->expects($this->once())
            ->method('setType')
            ->with($this->anything());

        //AdminInterface doesn't implement methods called in addField,
        //so we mock Admin
        $this->admin = $this->createMock(AbstractAdmin::class, [], [], '', false);
        $this->admin->expects($this->any())
            ->method('getModelManager')
            ->will($this->returnValue($this->modelManager));
        $this->admin->expects($this->once())
            ->method('addListFieldDescription')
            ->with($this->anything(), $this->fieldDescription);

        $this->fieldDescriptionCollection = $this->createMock(FieldDescriptionCollection::class);
        $this->fieldDescriptionCollection->expects($this->once())
            ->method('add')
            ->with($this->fieldDescription);
    }
}
