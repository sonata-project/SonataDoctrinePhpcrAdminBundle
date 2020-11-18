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

namespace Sonata\DoctrinePHPCRAdminBundle\Tests\Unit\Guesser;

use Doctrine\Bundle\PHPCRBundle\ManagerRegistry;
use Doctrine\ODM\PHPCR\DocumentRepository;
use Doctrine\Persistence\Mapping\ClassMetadata;
use PHPUnit\Framework\TestCase;
use Sonata\DoctrinePHPCRAdminBundle\Filter\StringFilter;
use Sonata\DoctrinePHPCRAdminBundle\Guesser\FilterTypeGuesser;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Guess\Guess;
use Symfony\Component\Form\Guess\TypeGuess;

class FilterTypeGuesserTest extends TestCase
{
    public function testGuessType(): void
    {
        $managerRegistry = $this->createMock(ManagerRegistry::class);

        $documentRepository = $this->createMock(DocumentRepository::class);

        $documentRepository->expects($this->once())
            ->method('getClassMetadata')
            ->with($this->equalTo($class = 'Whatever'))
            ->willReturn($this->createMock(
                ClassMetadata::class
            ));

        $managerRegistry->expects($this->once())
            ->method('getManagers')
            ->willReturn([$documentRepository]);

        $guesser = new FilterTypeGuesser(
            $managerRegistry
        );

        $typeGuess = $guesser->guessType($class, $fieldname = 'whatever', $this->createMock(
            'Sonata\AdminBundle\Model\ModelManagerInterface'
        ));

        $this->assertInstanceOf(
            TypeGuess::class,
            $typeGuess
        );
        $this->assertSame(
            StringFilter::class,
            $typeGuess->getType()
        );
        $this->assertSame(
            [
                'field_type' => TextType::class,
                'field_options' => [],
                'options' => [],
                'field_name' => $fieldname,
            ],
            $typeGuess->getOptions()
        );

        $this->assertSame(
            Guess::LOW_CONFIDENCE,
            $typeGuess->getConfidence()
        );
    }
}
