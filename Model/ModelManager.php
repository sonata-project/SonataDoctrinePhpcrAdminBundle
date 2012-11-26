<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\DoctrinePHPCRAdminBundle\Model;

use Sonata\DoctrinePHPCRAdminBundle\Admin\FieldDescription;
use Sonata\DoctrinePHPCRAdminBundle\Datagrid\ProxyQuery;
use Sonata\AdminBundle\Model\ModelManagerInterface;
use Sonata\AdminBundle\Admin\FieldDescriptionInterface;
use Sonata\AdminBundle\Datagrid\DatagridInterface;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\AdminBundle\Exception\ModelManagerException;

use Doctrine\ODM\PHPCR\DocumentManager;
use Doctrine\Common\Collections\ArrayCollection;

use Symfony\Component\Form\Exception\PropertyAccessDeniedException;

class ModelManager implements ModelManagerInterface
{
    protected $documentManager;

    /**
     * @param \Doctrine\ODM\PHPCR\DocumentManager $documentManager
     */
    public function __construct(DocumentManager $documentManager)
    {
        $this->documentManager = $documentManager;
    }

    /**
     * Returns the related model's metadata
     *
     * @abstract
     * @param string $class
     *
     * @return \Doctrine\ODM\PHPCR\Mapping\ClassMetadata
     */
    public function getMetadata($class)
    {
        return $this->documentManager->getMetadataFactory()->getMetadataFor($class);
    }

    /**
     * Returns true is the model has some metadata
     *
     * @param $class
     * @return boolean
     */
    public function hasMetadata($class)
    {
        return $this->documentManager->getMetadataFactory()->hasMetadataFor($class);
    }

    /**
     * Returns a new FieldDescription
     *
     * @throws \RunTimeException
     * @param $class
     * @param $name
     * @param array $options
     * @return \Sonata\AdminBundle\Admin\ODM\PHPCR\FieldDescription
     */
    public function getNewFieldDescriptionInstance($class, $name, array $options = array())
    {
        if (!is_string($name)) {
            throw new \RunTimeException('The name argument must be a string');
        }

        $metadata = $this->getMetadata($class);

        $fieldDescription = new FieldDescription;
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
     * @param mixed $object
     * @throws \Sonata\AdminBundle\Exception\ModelManagerException
     */
    public function create($object)
    {
        try {
            $this->documentManager->persist($object);
            $this->documentManager->flush();
        } catch (\Exception $e) {
            throw new ModelManagerException('', 0, $e);
        }
    }

    /**
     * @param mixed $object
     * @throws \Sonata\AdminBundle\Exception\ModelManagerException
     */
    public function update($object)
    {
        try {
            $this->documentManager->persist($object);
            $this->documentManager->flush();
        } catch (\Exception $e) {
            throw new ModelManagerException('', 0, $e);
        }
    }

    /**
     * @param object $object
     * @throws \Sonata\AdminBundle\Exception\ModelManagerException
     */
    public function delete($object)
    {
        try {
            $this->documentManager->remove($object);
            $this->documentManager->flush();
        } catch (\Exception $e) {
            throw new ModelManagerException('', 0, $e);
        }
    }

    /**
     * Find one object from the given class repository.
     *
     * @param string $class Class name
     * @param string|int $id Identifier. Can be a string with several IDs concatenated, separated by '-'.
     * @return Object
     */
    public function find($class, $id)
    {
        if (null === $class) {
            return $this->documentManager->find(null, $id);
        }

        return $this->documentManager->getRepository($class)->find($id);
    }

    /**
     * @param $class
     * @param array $criteria
     * @return array
     */
    public function findBy($class, array $criteria = array())
    {
        return $this->documentManager->getRepository($class)->findBy($criteria);
    }

    /**
     * @param $class
     * @param array $criteria
     * @return array
     */
    public function findOneBy($class, array $criteria = array())
    {
        return $this->documentManager->getRepository($class)->findOneBy($criteria);
    }

    /**
     * @return \Doctrine\ODM\PHPCR\DocumentManager
     */
    public function getDocumentManager()
    {
        return $this->documentManager;
    }

    /**
     * @param string $parentAssociationMapping
     * @param string $class
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
     * @param $class
     * @param string $alias (provided only for compatibility with the interface TODO: remove)
     * @return \PHPCR\Query\QueryManagerInterface
     */
    public function createQuery($class, $alias = 'o', $root = null)
    {
        $queryBuilder = $this->getDocumentManager()->createQueryBuilder();
        $qomFactory = $queryBuilder->getQOMFactory();
        $query = new ProxyQuery($qomFactory, $queryBuilder);
        $query->setDocumentName($class);
        $query->setDocumentManager($this->getDocumentManager());
        if ($root) {
            $query->where($qomFactory->descendantNode($root));
        }
        return $query;
    }

    /**
     * @param $query
     * @return mixed
     */
    public function executeQuery($query)
    {
        return $query->execute();
    }

    /**
     * {@inheritDoc}
     */
    public function getModelIdentifier($class)
    {
        return $this->getMetadata($class)->identifier;
    }

    /**
     * Transforms the document into the PHPCR path.
     *
     * Note: This is returning an array because Doctrine ORM for example can
     * have multiple identifiers, e.g. if the primary key is composed of
     * several columns. We only ever have one, but return that wrapped into an
     * array to adhere to the interface.
     *
     * {@inheritDoc}
     */
    public function getIdentifierValues($document)
    {
        $class = $this->getMetadata(get_class($document));
        $path = $class->reflFields[$class->identifier]->getValue($document);
        return array($path);
    }

    /**
     * @param $class
     * @return mixed
     */
    public function getIdentifierFieldNames($class)
    {
        return array($this->getModelIdentifier($class));
    }

    /**
     * {@inheritDoc}
     *
     * This is just taking the id out of the array again.
     */
    public function getNormalizedIdentifier($document)
    {
        if (is_scalar($document)) {
            throw new \RunTimeException('Invalid argument, object or null required');
        }

        // the document is not managed
        if (!$document || !$this->getDocumentManager()->contains($document)) {
            return null;
        }

        $values = $this->getIdentifierValues($document);

        return $values[0];
    }

    /**
     * {@inheritDoc}
     *
     * Currently only the leading slash is removed.
     * TODO: do we also have to encode certain characters like spaces or does
     * that happen automatically?
     */
    public function getUrlsafeIdentifier($document)
    {
        $id = $this->getNormalizedIdentifier($document);
        if (null !== $id) {
            return substr($id, 1);
        }
        return null;
    }

    /**
     * @param $class
     * @param \Sonata\AdminBundle\Datagrid\ProxyQueryInterface $queryProxy
     * @param array $idx
     * @return void
     */
    public function addIdentifiersToQuery($class, ProxyQueryInterface $queryProxy, array $idx)
    {

        /** @var \PHPCR\Util\QOM\QueryBuilder $qb  */
        $qb = $queryProxy->getQueryBuilder();
        $qmf = $qb->getQOMFactory();

        $constraint = null;
        foreach ($idx as $id) {
            $path = $this->getBackendId($id);
            $condition = $qmf->sameNode($path);
            if ($constraint) {
                $constraint = $qmf->orConstraint($constraint, $condition);
            } else {
                $constraint = $condition;
            }
        }
        $qb->andWhere($constraint);
    }

    /**
     * Add leading slash to construct valid phpcr document id.
     *
     * The phpcr-odm QueryBuilder uses absolute paths and expects id´s to start with a forward slash
     * because SonataAdmin uses object id´s for constructing URL´s it has to use id´s without the
     * leading slash.
     *
     * @param $id
     * @return string
     */
    public function getBackendId($id)
    {
        return substr($id, 0, 1) === '/' ? $id : '/'.$id;
    }

    /**
     * @param string $class
     * @param \Sonata\AdminBundle\Datagrid\ProxyQueryInterface $queryProxy
     * @throws \Sonata\AdminBundle\Exception\ModelManagerException
     */
    public function batchDelete($class, ProxyQueryInterface $queryProxy)
    {
        try {
            $i = 0;
            foreach ($queryProxy->getQuery()->execute()->getNodes() as $object) {
                $object->remove();

                if ((++$i % 20) == 0) {
                    $this->documentManager->flush();
                    $this->documentManager->clear();
                }
            }

            $this->documentManager->flush();
            $this->documentManager->clear();
        } catch (\Exception $e) {
            throw new ModelManagerException('', 0, $e);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getModelInstance($class)
    {
        return new $class;
    }

    /**
     * Returns the parameters used in the columns header
     *
     * @param \Sonata\AdminBundle\Admin\FieldDescriptionInterface $fieldDescription
     * @param \Sonata\AdminBundle\Datagrid\DatagridInterface $datagrid
     * @return array
     */
    public function getSortParameters(FieldDescriptionInterface $fieldDescription, DatagridInterface $datagrid)
    {
        $values = $datagrid->getValues();

        if ($fieldDescription->getOption('sortable') == $values['_sort_by']) {
            if ($values['_sort_order'] == 'ASC') {
                $values['_sort_order'] = 'DESC';
            } else {
                $values['_sort_order'] = 'ASC';
            }
        } else {
            $values['_sort_order'] = 'ASC';
            $values['_sort_by'] = $fieldDescription->getName();
        }

        return array('filter' => $values);
    }

    /**
     * @param \Sonata\AdminBundle\Datagrid\DatagridInterface $datagrid
     * @param $page
     * @return array
     */
    public function getPaginationParameters(DatagridInterface $datagrid, $page)
    {
        $values = $datagrid->getValues();

        $values['_page'] = $page;

        return array('filter' => $values);
    }

    /**
     * @param sring $class
     * @return array
     */
    public function getDefaultSortValues($class)
    {
        return array(
            '_sort_order' => 'ASC',
            '_sort_by' => $this->getModelIdentifier($class),
            '_page' => 1
        );
    }

    /**
     * @param string $class
     * @param object $instance
     * @return mixed
     */
    public function modelTransform($class, $instance)
    {
        return $instance;
    }

    /**
     * @param string $class
     * @param array $array
     * @return mixed|void
     * @throws \Symfony\Component\Form\Exception\PropertyAccessDeniedException
     */
    public function modelReverseTransform($class, array $array = array())
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

            } else if (array_key_exists($name, $metadata->associationMappings)) {
                $property = $metadata->associationMappings[$name]['fieldName'];
            } else {
                $property = $name;
            }

            $setter = 'set' . $this->camelize($name);

            if ($reflClass->hasMethod($setter)) {
                if (!$reflClass->getMethod($setter)->isPublic()) {
                    throw new PropertyAccessDeniedException(sprintf('Method "%s()" is not public in class "%s"', $setter, $reflClass->getName()));
                }

                $instance->$setter($value);
            } else if ($reflClass->hasMethod('__set')) {
                // needed to support magic method __set
                $instance->$property = $value;
            } else if ($reflClass->hasProperty($property)) {
                if (!$reflClass->getProperty($property)->isPublic()) {
                    throw new PropertyAccessDeniedException(sprintf('Property "%s" is not public in class "%s". Maybe you should create the method "set%s()"?', $property, $reflClass->getName(), ucfirst($property)));
                }

                $instance->$property = $value;
            } else if ($reflection_property) {
                $reflection_property->setValue($instance, $value);
            }
        }

        return $instance;
    }

    /**
     * method taken from PropertyPath
     *
     * @param  $property
     * @return mixed
     */
    protected function camelize($property)
    {
        return preg_replace(array('/(^|_)+(.)/e', '/\.(.)/e'), array("strtoupper('\\2')", "'_'.strtoupper('\\1')"), $property);
    }

    /**
     * @param string $class
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getModelCollectionInstance($class)
    {
        return new ArrayCollection();
    }

    /**
     * @param mixed $collection
     * @return mixed
     */
    public function collectionClear(&$collection)
    {
        return $collection->clear();
    }

    /**
     * @param mixed $collection
     * @param mixed $element
     * @return mixed
     */
    public function collectionHasElement(&$collection, &$element)
    {
        return $collection->contains($element);
    }

    /**
     * @param mixed $collection
     * @param mixed $element
     * @return mixed
     */
    public function collectionAddElement(&$collection, &$element)
    {
        return $collection->add($element);
    }

    /**
     * @param mixed $collection
     * @param mixed $element
     * @return mixed
     */
    public function collectionRemoveElement(&$collection, &$element)
    {
        return $collection->removeElement($element);
    }

    /**
     * @param \Sonata\AdminBundle\Datagrid\DatagridInterface $datagrid
     * @param array $fields
     * @param null $firstResult
     * @param null $maxResult
     * @return null
     */
    public function getDataSourceIterator(DatagridInterface $datagrid, array $fields, $firstResult = null, $maxResult = null)
    {
        return null;
    }

    /**
     * @param string $class
     * @return null
     */
    public function getExportFields($class)
    {
        return null;
    }
}
