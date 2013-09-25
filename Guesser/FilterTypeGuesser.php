<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\DoctrinePHPCRAdminBundle\Guesser;

use Sonata\AdminBundle\Guesser\TypeGuesserInterface;
use Sonata\AdminBundle\Model\ModelManagerInterface;

use Doctrine\Bundle\PHPCRBundle\ManagerRegistry;
use Doctrine\ODM\PHPCR\Mapping\MappingException;
use Doctrine\ODM\PHPCR\Mapping\ClassMetadata;

use Symfony\Component\Form\Guess\Guess;
use Symfony\Component\Form\Guess\TypeGuess;

class FilterTypeGuesser implements TypeGuesserInterface
{
    /**
     * @var ManagerRegistry
     */
    protected $registry;

    /**
     * @var array
     */
    private $cache;

    /**
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        $this->registry = $registry;
        $this->cache = array();
    }

    /**
     * {@inheritDoc}
     */
    public function guessType($class, $property, ModelManagerInterface $modelManager)
    {
        if (!$metadata = $this->getMetadata($class)) {
            return false;
        }

        $options = array(
            'field_type'     => false,
            'field_options'  => array(),
            'options'        => array(),
        );

        if ($metadata->hasAssociation($property)) {
            // TODO add support for children, child, referrers and parentDocument associations
            $mapping = $metadata->mappings[$property];

            $options['operator_type'] = 'sonata_type_boolean';
            $options['operator_options'] = array();

            $options['field_type'] = 'document';
            if (!empty($mapping['targetDocument'])) {
                $options['field_options'] = array(
                    'class' => $mapping['targetDocument']
                );
            }
            $options['field_name'] = $mapping['fieldName'];
            $options['mapping_type'] = $mapping['type'];

            switch ($mapping['type']) {
                case ClassMetadata::MANY_TO_MANY:
                    return new TypeGuess('doctrine_phpcr_many_to_many', $options, Guess::HIGH_CONFIDENCE);

                case ClassMetadata::MANY_TO_ONE:
                    return new TypeGuess('doctrine_phpcr_many_to_one', $options, Guess::HIGH_CONFIDENCE);
            }
        }

        // TODO add support for node, nodename, version created, version name

        $options['field_name'] = $property;
        switch ($metadata->getTypeOfField($property)) {
            case 'boolean':
                $options['field_type'] = 'sonata_type_boolean';
                $options['field_options'] = array();

                return new TypeGuess('doctrine_phpcr_boolean', $options, Guess::HIGH_CONFIDENCE);
            case 'date':
                return new TypeGuess('doctrine_phpcr_date', $options, Guess::HIGH_CONFIDENCE);
            case 'decimal':
            case 'float':
                return new TypeGuess('doctrine_phpcr_number', $options, Guess::HIGH_CONFIDENCE);
            case 'integer':
                $options['field_type'] = 'number';
                $options['field_options'] = array(
                    'csrf_protection' => false
                );

                return new TypeGuess('doctrine_phpcr_integer', $options, Guess::HIGH_CONFIDENCE);
            case 'text':
            case 'string':
                $options['field_type'] = 'text';

                return new TypeGuess('doctrine_phpcr_string', $options, Guess::HIGH_CONFIDENCE);
        }

        return new TypeGuess('doctrine_phpcr_string', $options, Guess::LOW_CONFIDENCE);
    }

    /**
     * @param string $class
     *
     * @return mixed
     */
    protected function getMetadata($class)
    {
        if (array_key_exists($class, $this->cache)) {
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
