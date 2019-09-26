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

namespace Sonata\DoctrinePHPCRAdminBundle\Guesser;

use Doctrine\Bundle\PHPCRBundle\ManagerRegistry;
use Doctrine\ODM\PHPCR\Mapping\ClassMetadata;
use Doctrine\ODM\PHPCR\Mapping\MappingException;
use Sonata\AdminBundle\Guesser\TypeGuesserInterface;
use Sonata\AdminBundle\Model\ModelManagerInterface;
use Sonata\Form\Type\BooleanType;
use Sonata\Form\Type\DatePickerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Guess\Guess;
use Symfony\Component\Form\Guess\TypeGuess;

/**
 * Guesser for displaying fields.
 *
 * Form guesses happen in the FormContractor.
 */
class TypeGuesser implements TypeGuesserInterface
{
    /**
     * @var ManagerRegistry
     */
    protected $registry;

    /**
     * @var array
     */
    private $cache;

    public function __construct(ManagerRegistry $registry)
    {
        $this->registry = $registry;
        $this->cache = [];
    }

    /**
     * {@inheritdoc}
     */
    public function guessType($class, $property, ModelManagerInterface $modelManager)
    {
        if (!$metadata = $this->getMetadata($class)) {
            return new TypeGuess(TextType::class, [], Guess::LOW_CONFIDENCE);
        }

        if ($metadata->hasAssociation($property)) {
            $mapping = $metadata->mappings[$property];

            switch ($mapping['type']) {
                case ClassMetadata::MANY_TO_MANY:
                case 'referrers':
                    return new TypeGuess('doctrine_phpcr_many_to_many', [], Guess::HIGH_CONFIDENCE);

                case ClassMetadata::MANY_TO_ONE:
                case 'parent':
                    return new TypeGuess('doctrine_phpcr_many_to_one', [], Guess::HIGH_CONFIDENCE);

                case 'children':
                    return new TypeGuess('doctrine_phpcr_one_to_many', [], Guess::HIGH_CONFIDENCE);

                case 'child':
                    return new TypeGuess('doctrine_phpcr_one_to_one', [], Guess::HIGH_CONFIDENCE);
            }
        }

        // TODO: missing multivalue support
        switch ($metadata->getTypeOfField($property)) {
            case 'boolean':
                return new TypeGuess(BooleanType::class, [], Guess::HIGH_CONFIDENCE);
            case 'date':
                return new TypeGuess(DatePickerType::class, [], Guess::HIGH_CONFIDENCE);

            case 'decimal':
            case 'double':
                return new TypeGuess(TextType::class, [], Guess::MEDIUM_CONFIDENCE);
            case 'integer':
            case 'long':
                return new TypeGuess(NumberType::class, [], Guess::MEDIUM_CONFIDENCE);
            case 'string':
                return new TypeGuess(TextType::class, [], Guess::HIGH_CONFIDENCE);
            case 'binary':
            case 'uri':
                return new TypeGuess(TextType::class, [], Guess::MEDIUM_CONFIDENCE);
        }

        return new TypeGuess(TextType::class, [], Guess::LOW_CONFIDENCE);
    }

    /**
     * @param string $class
     *
     * @return mixed
     */
    protected function getMetadata($class)
    {
        if (\array_key_exists($class, $this->cache)) {
            return $this->cache[$class];
        }

        $this->cache[$class] = null;
        foreach ($this->registry->getManagers() as $dm) {
            try {
                return $this->cache[$class] = $dm->getClassMetadata($class);
            } catch (MappingException $e) {
                // not an entity or mapped super class
            }
        }
    }
}
