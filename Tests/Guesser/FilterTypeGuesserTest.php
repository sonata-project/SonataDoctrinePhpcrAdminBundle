<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\DoctrinePHPCRAdminBundle\Tests\Guesser;

use Sonata\DoctrinePHPCRAdminBundle\Guesser\FilterTypeGuesser;
use Symfony\Component\Form\Guess\Guess;

class FilterTypeGuesserTest extends \PHPUnit_Framework_TestCase
{
    public function testGuessType()
    {
        $managerRegistry = $this->createMock('Doctrine\Bundle\PHPCRBundle\ManagerRegistry');

        $documentRepository = $this->createMock('Doctrine\ODM\PHPCR\DocumentRepository');

        $documentRepository->expects($this->once())
            ->method('getClassMetadata')
            ->with($this->equalTo($class = 'Whatever'))
            ->will($this->returnValue($this->createMock(
                'Doctrine\Common\Persistence\Mapping\ClassMetadata'
            )));

        $managerRegistry->expects($this->once())
            ->method('getManagers')
            ->will($this->returnValue(array($documentRepository)));

        $guesser = new FilterTypeGuesser(
            $managerRegistry
        );

        $typeGuess = $guesser->guessType($class, $fieldname = 'whatever', $this->createMock(
            'Sonata\AdminBundle\Model\ModelManagerInterface'
        ));

        $this->assertInstanceof(
            'Symfony\Component\Form\Guess\TypeGuess',
            $typeGuess
        );
        $this->assertSame(
            'doctrine_phpcr_string',
            $typeGuess->getType()
        );
        $this->assertSame(
            array(
                'field_type' => 'Symfony\Component\Form\Extension\Core\Type\TextType',
                'field_options' => array(),
                'options' => array(),
                'field_name' => $fieldname,
            ),
            $typeGuess->getOptions()
        );

        $this->assertEquals(
            Guess::LOW_CONFIDENCE,
            $typeGuess->getConfidence()
        );
    }
}
