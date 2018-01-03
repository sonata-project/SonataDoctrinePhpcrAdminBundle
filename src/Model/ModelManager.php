<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\DoctrinePHPCRAdminBundle\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Util\ClassUtils;
use Doctrine\ODM\PHPCR\DocumentManager;
use Doctrine\ODM\PHPCR\Mapping\ClassMetadata;
use Sonata\AdminBundle\Admin\FieldDescriptionInterface;
use Sonata\AdminBundle\Datagrid\DatagridInterface;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\AdminBundle\Exception\ModelManagerException;
use Sonata\AdminBundle\Model\ModelManagerInterface;
use Sonata\DoctrinePHPCRAdminBundle\Admin\FieldDescription;
use Sonata\DoctrinePHPCRAdminBundle\Datagrid\ProxyQuery;
use Symfony\Component\PropertyAccess\Exception\NoSuchPropertyException;

class ModelManager implements ModelManagerInterface
{
    /**
     * @var DocumentManager
     */
    protected $dm;

    /**
     * @param DocumentManager $dm
     */
    public function __construct(DocumentManager $dm)
    {
        $this->dm = $dm;
    }

    /**
     * Returns the related model's metadata.
     *
     * @param string $class
     *
     * @return ClassMetadata
     */
    public function getMetadata($class)
    {
        return $this->dm->getMetadataFactory()->getMetadataFor($class);
    }

    /**
     * Returns true is the model has some metadata.
     *
     * @param string $class
     *
     * @return bool
     */
    public function hasMetadata($class)
    {
        return $this->dm->getMetadataFactory()->hasMetadataFor($class);
    }

    /**
     * {@inheritdoc}
     *
     * @throws ModelManagerException if the document manager throws any exception
     */
    public function create($object)
    {
        try {
            $this->dm->persist($object);
            $this->dm->flush();
        } catch (\Exception $e) {
            throw new ModelManagerException('', 0, $e);
        }
    }

    /**
     * {@inheritdoc}
     *
     * @throws ModelManagerException if the document manager throws any exception
     */
    public function update($object)
    {
        try {
            $this->dm->persist($object);
            $this->dm->flush();
        } catch (\Exception $e) {
            throw new ModelManagerException('', 0, $e);
        }
    }

    /**
     * {@inheritdoc}
     *
     * @throws ModelManagerException if the document manager throws any exception
     */
    public function delete($object)
    {
        try {
            $this->dm->remove($object);
            $this->dm->flush();
        } catch (\Exception $e) {
            throw new ModelManagerException('', 0, $e);
        }
    }

    /**
     * Find one object from the given class repository.
     *
     * {@inheritdoc}
     */
    public function find($class, $id)
    {
        if (!isset($id)) {
            return;
        }

        if (null === $class) {
            return $this->dm->find(null, $id);
        }

        return $this->dm->getRepository($class)->find($id);
    }

    /**
     * {@inheritdoc}
     *
     * @throws \RunTimeException if $name is not a string
     *
     * @return FieldDescription
     */
    public function getNewFieldDescriptionInstance($class, $name, array $options = [])
    {
        if (!is_string($name)) {
            throw new \RunTimeException('The name argument must be a string');
        }

        $metadata = $this->getMetadata($class);

        $fieldDescription = new FieldDescription();
        $fieldDescription->setName($name);
        $fieldDescription->setOptions($options);

        if (isset($metadata->associationMappings[$name])) {
            $fieldDescription->setAssociationMapping($metadata->associationMappings[$name]);
        }

        if (isset($metadata->fieldMappings[$name])) {
            $fieldDescription->setFieldMapping($metadata->fieldMappings[$name]);
        }

        return $fieldDescription;
    }

    /**
     * {@inheritdoc}
     */
    public function findBy($class, array $criteria = [])
    {
        return $this->dm->getRepository($class)->findBy($criteria);
    }

    /**
     * {@inheritdoc}
     */
    public function findOneBy($class, array $criteria = [])
    {
        return $this->dm->getRepository($class)->findOneBy($criteria);
    }

    /**
     * @return DocumentManager The PHPCR-ODM document manager responsible for
     *                         this model
     */
    public function getDocumentManager()
    {
        return $this->dm;
    }

    /**
     * {@inheritdoc}
     *
     * @return FieldDescriptionInterface
     */
    public function getParentFieldDescription($parentAssociationMapping, $class)
    {
        $fieldName = $parentAssociationMapping['fieldName'];

        $metadata = $this->getMetadata($class);

        $associatingMapping = $metadata->associationMappings[$parentAssociationMapping];

        $fieldDescription = $this->getNewFieldDescriptionInstance($class, $fieldName);
        $fieldDescription->setName($parentAssociationMapping);
        $fieldDescription->setAssociationMapping($associatingMapping);

        return $fieldDescription;
    }

    /**
     * @param string $class the fully qualified class name to search for
     * @param string $alias alias to use for this class when accessing fields,
     *                      defaults to 'a'
     *
     * @throws \InvalidArgumentException if alias is not a string or an empty string
     *
     * @return ProxyQueryInterface
     */
    public function createQuery($class, $alias = 'a')
    {
        $qb = $this->getDocumentManager()->createQueryBuilder();
        $qb->from()->document($class, $alias);

        return new ProxyQuery($qb, $alias);
    }

    /**
     * @param ProxyQuery $query
     *
     * @return mixed
     */
    public function executeQuery($query)
    {
        return $query->execute();
    }

    /**
     * {@inheritdoc}
     */
    public function getModelIdentifier($classname)
    {
        return $this->getMetadata($classname)->identifier;
    }

    /**
     * Transforms the document into the PHPCR path.
     *
     * Note: This is returning an array because Doctrine ORM for example can
     * have multiple identifiers, e.g. if the primary key is composed of
     * several columns. We only ever have one, but return that wrapped into an
     * array to adhere to the interface.
     *
     * {@inheritdoc}
     */
    public function getIdentifierValues($document)
    {
        $class = $this->getMetadata(ClassUtils::getClass($document));
        $path = $class->reflFields[$class->identifier]->getValue($document);

        return [$path];
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentifierFieldNames($class)
    {
        return [$this->getModelIdentifier($class)];
    }

    /**
     * This is just taking the id out of the array again.
     *
     * {@inheritdoc}
     *
     * @throws \InvalidArgumentException if $document is not an object or null
     */
    public function getNormalizedIdentifier($document)
    {
        if (is_scalar($document)) {
            throw new \InvalidArgumentException('Invalid argument, object or null required');
        }

        // the document is not managed
        if (!$document || !$this->getDocumentManager()->contains($document)) {
            return;
        }

        $values = $this->getIdentifierValues($document);

        return $values[0];
    }

    /**
     * Currently only the leading slash is removed.
     *
     * @param object $document
     *
     * @return null|string
     */
    public function getUrlsafeIdentifier($document)
    {
        $id = $this->getNormalizedIdentifier($document);
        if (null !== $id) {
            return substr($id, 1);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function addIdentifiersToQuery($class, ProxyQueryInterface $queryProxy, array $idx)
    {
        /* @var $queryProxy ProxyQuery */
        $qb = $queryProxy->getQueryBuilder();

        $orX = $qb->andWhere()->orX();

        foreach ($idx as $id) {
            $path = $this->getBackendId($id);
            $orX->same($path, $queryProxy->getAlias());
        }
    }

    /**
     * Add leading slash to construct valid phpcr document id.
     *
     * The phpcr-odm QueryBuilder uses absolute paths and expects id´s to start with a forward slash
     * because SonataAdmin uses object id´s for constructing URL´s it has to use id´s without the
     * leading slash.
     *
     * @param string $id
     *
     * @return string
     */
    public function getBackendId($id)
    {
        return '/' === substr($id, 0, 1) ? $id : '/'.$id;
    }

    /**
     * {@inheritdoc}
     *
     * @throws ModelManagerException if anything goes wrong during query execution
     */
    public function batchDelete($class, ProxyQueryInterface $queryProxy)
    {
        try {
            $i = 0;
            $res = $queryProxy->execute();
            foreach ($res as $object) {
                $this->dm->remove($object);

                if (0 == (++$i % 20)) {
                    $this->dm->flush();
                    $this->dm->clear();
                }
            }

            $this->dm->flush();
            $this->dm->clear();
        } catch (\Exception $e) {
            throw new ModelManagerException('', 0, $e);
        }
    }

    /**
     * {@inheritdoc}
     *
     * @return object
     */
    public function getModelInstance($class)
    {
        return new $class();
    }

    /**
     * {@inheritdoc}
     */
    public function getSortParameters(FieldDescriptionInterface $fieldDescription, DatagridInterface $datagrid)
    {
        $values = $datagrid->getValues();

        if ($fieldDescription->getName() == $values['_sort_by']->getName()) {
            if ('ASC' == $values['_sort_order']) {
                $values['_sort_order'] = 'DESC';
            } else {
                $values['_sort_order'] = 'ASC';
            }

            $values['_sort_by'] = $fieldDescription->getName();
        } else {
            $values['_sort_order'] = 'ASC';
            $values['_sort_by'] = $fieldDescription->getName();
        }

        return ['filter' => $values];
    }

    /**
     * {@inheritdoc}
     */
    public function getPaginationParameters(DatagridInterface $datagrid, $page)
    {
        $values = $datagrid->getValues();

        $values['_sort_by'] = $values['_sort_by']->getName();
        $values['_page'] = $page;

        return ['filter' => $values];
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultSortValues($class)
    {
        return [
            '_sort_order' => 'ASC',
            '_sort_by' => $this->getModelIdentifier($class),
            '_page' => 1,
        ];
    }

    /**
     * {@inheritdoc}
     *
     * @return object
     */
    public function modelTransform($class, $instance)
    {
        return $instance;
    }

    /**
     * {@inheritdoc}
     *
     * @throws NoSuchPropertyException if the class has no magic setter and
     *                                 public property for a field in array
     *
     * @return object
     */
    public function modelReverseTransform($class, array $array = [])
    {
        $instance = $this->getModelInstance($class);
        $metadata = $this->getMetadata($class);

        $reflClass = $metadata->reflClass;
        foreach ($array as $name => $value) {
            $reflection_property = false;
            // property or association ?
            if (array_key_exists($name, $metadata->fieldMappings)) {
                $property = $metadata->fieldMappings[$name]['fieldName'];
                $reflection_property = $metadata->reflFields[$name];
            } elseif (array_key_exists($name, $metadata->associationMappings)) {
                $property = $metadata->associationMappings[$name]['fieldName'];
            } else {
                $property = $name;
            }

            // TODO: use PropertyAccess https://github.com/sonata-project/SonataDoctrinePhpcrAdminBundle/issues/187
            $setter = 'set'.$this->camelize($name);

            if ($reflClass->hasMethod($setter)) {
                if (!$reflClass->getMethod($setter)->isPublic()) {
                    throw new NoSuchPropertyException(sprintf('Method "%s()" is not public in class "%s"', $setter, $reflClass->getName()));
                }

                $instance->$setter($value);
            } elseif ($reflClass->hasMethod('__set')) {
                // needed to support magic method __set
                $instance->$property = $value;
            } elseif ($reflClass->hasProperty($property)) {
                if (!$reflClass->getProperty($property)->isPublic()) {
                    throw new NoSuchPropertyException(sprintf('Property "%s" is not public in class "%s". Maybe you should create the method "set%s()"?', $property, $reflClass->getName(), ucfirst($property)));
                }

                $instance->$property = $value;
            } elseif ($reflection_property) {
                $reflection_property->setValue($instance, $value);
            }
        }

        return $instance;
    }

    /**
     * {@inheritdoc}
     */
    public function getModelCollectionInstance($class)
    {
        return new ArrayCollection();
    }

    /**
     * {@inheritdoc}
     */
    public function collectionClear(&$collection)
    {
        return $collection->clear();
    }

    /**
     * {@inheritdoc}
     */
    public function collectionHasElement(&$collection, &$element)
    {
        return $collection->contains($element);
    }

    /**
     * {@inheritdoc}
     */
    public function collectionAddElement(&$collection, &$element)
    {
        return $collection->add($element);
    }

    /**
     * {@inheritdoc}
     */
    public function collectionRemoveElement(&$collection, &$element)
    {
        return $collection->removeElement($element);
    }

    /**
     * {@inheritdoc}
     */
    public function getDataSourceIterator(DatagridInterface $datagrid, array $fields, $firstResult = null, $maxResult = null)
    {
        throw new \RuntimeException('Datasourceiterator not implemented.');
    }

    /**
     * {@inheritdoc}
     *
     * Not really implemented.
     */
    public function getExportFields($class)
    {
        return [];
    }

    /**
     * Method taken from PropertyPath.
     *
     * NEXT_MAJOR: remove when doing https://github.com/sonata-project/SonataDoctrinePhpcrAdminBundle/issues/187
     *
     * @param string $property
     *
     * @return string
     *
     * @deprecated
     */
    protected function camelize($property)
    {
        return preg_replace(['/(^|_)+(.)/e', '/\.(.)/e'], ["strtoupper('\\2')", "'_'.strtoupper('\\1')"], $property);
    }
}
