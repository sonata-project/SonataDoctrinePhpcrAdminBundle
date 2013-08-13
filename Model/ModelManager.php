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

use Doctrine\ODM\PHPCR\Mapping\ClassMetadata;
use Doctrine\ODM\PHPCR\DocumentManager;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Util\ClassUtils;

use Symfony\Component\PropertyAccess\Exception\NoSuchPropertyException;

class ModelManager implements ModelManagerInterface
{
    /**
     * @var DocumentManager
     */
    protected $documentManager;

    /**
     * @param DocumentManager $documentManager
     */
    public function __construct(DocumentManager $documentManager)
    {
        $this->documentManager = $documentManager;
    }

    /**
     * Returns the related model's metadata
     *
     * @param string $class
     *
     * @return ClassMetadata
     */
    public function getMetadata($class)
    {
        return $this->documentManager->getMetadataFactory()->getMetadataFor($class);
    }

    /**
     * Returns true is the model has some metadata
     *
     * @param $class
     *
     * @return boolean
     */
    public function hasMetadata($class)
    {
        return $this->documentManager->getMetadataFactory()->hasMetadataFor($class);
    }

    /**
     * @param mixed $object
     *
     * @throws ModelManagerException
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
     *
     * @throws ModelManagerException
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
     *
     * @throws ModelManagerException
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
     *
     * @return Object
     */
    public function find($class, $id)
    {
        if (!isset($id)) {
            return null;
        }

        if (null === $class) {
            return $this->documentManager->find(null, $id);
        }

        return $this->documentManager->getRepository($class)->find($id);
    }

    /**
     * Returns a new FieldDescription
     *
     * @param $class
     * @param $name
     * @param array $options
     *
     * @return FieldDescription
     * @throws \RunTimeException
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
     * @param $class
     * @param array $criteria
     *
     * @return array
     */
    public function findBy($class, array $criteria = array())
    {
        return $this->documentManager->getRepository($class)->findBy($criteria);
    }

    /**
     * @param $class
     * @param array $criteria
     *
     * @return array
     */
    public function findOneBy($class, array $criteria = array())
    {
        return $this->documentManager->getRepository($class)->findOneBy($criteria);
    }

    /**
     * @return DocumentManager
     */
    public function getDocumentManager()
    {
        return $this->documentManager;
    }

    /**
     * @param string $parentAssociationMapping
     * @param string $class
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
     * @param string $class
     * @param string $alias (provided only for compatibility with the interface TODO: remove)
     * @param null $root
     *
     * @return ProxyQueryInterface
     */
    public function createQuery($class, $alias = 'o', $root = null)
    {
        $qb = $this->getDocumentManager()->createQueryBuilder();
        $qb->from($class);
        $query = new ProxyQuery($qb);

        if ($root) {
            $query->where($qb->expr()->descendant($root));
        }

        return $query;
    }

    /**
     * @param $query
     *
     * @return mixed
     */
    public function executeQuery($query)
    {
        return $query->execute();
    }

    /**
     * @param string $classname
     * @return string
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
     * @param object $document
     *
     * @return array
     */
    public function getIdentifierValues($document)
    {
        $class = $this->getMetadata(ClassUtils::getClass($document));
        $path = $class->reflFields[$class->identifier]->getValue($document);
        return array($path);
    }

    /**
     * @param $class
     *
     * @return array
     */
    public function getIdentifierFieldNames($class)
    {
        return array($this->getModelIdentifier($class));
    }

    /**
     * This is just taking the id out of the array again.
     *
     * @param object $document
     *
     * @return null|string
     * @throws \RunTimeException
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
     * Currently only the leading slash is removed.
     * TODO: do we also have to encode certain characters like spaces or does that happen automatically?
     *
     * @param object $document
     * @return null|string
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
     * @param ProxyQueryInterface $queryProxy
     * @param array $idx
     */
    public function addIdentifiersToQuery($class, ProxyQueryInterface $queryProxy, array $idx)
    {
        $qb = $queryProxy->getQueryBuilder();

        $constraint = null;

        foreach ($idx as $id) {
            $path = $this->getBackendId($id);
            $condition = $qb->expr()->eqPath($path);
            if ($constraint) {
                $constraint = $qb->expr()->orx($constraint, $condition);
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
     * @param string $id
     *
     * @return string
     */
    public function getBackendId($id)
    {
        return substr($id, 0, 1) === '/' ? $id : '/'.$id;
    }

    /**
     * @param string $class
     * @param ProxyQueryInterface $queryProxy
     *
     * @throws ModelManagerException
     */
    public function batchDelete($class, ProxyQueryInterface $queryProxy)
    {
        try {
            $i = 0;
            $res = $queryProxy->getQuery()->execute();
            foreach ($res as $object) {
                $this->documentManager->remove($object);

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
     * @param string $class
     *
     * @return object
     */
    public function getModelInstance($class)
    {
        return new $class;
    }

    /**
     * Returns the parameters used in the columns header
     *
     * @param FieldDescriptionInterface $fieldDescription
     * @param DatagridInterface $datagrid
     *
     * @return array
     */
    public function getSortParameters(FieldDescriptionInterface $fieldDescription, DatagridInterface $datagrid)
    {
        $values = $datagrid->getValues();

        if ($fieldDescription->getName() == $values['_sort_by']->getName()) {
            if ($values['_sort_order'] == 'ASC') {
                $values['_sort_order'] = 'DESC';
            } else {
                $values['_sort_order'] = 'ASC';
            }

            $values['_sort_by']    = $fieldDescription->getName();
        } else {
            $values['_sort_order'] = 'ASC';
            $values['_sort_by'] = $fieldDescription->getName();
        }

        return array('filter' => $values);
    }

    /**
     * @param DatagridInterface $datagrid
     * @param $page
     *
     * @return array
     */
    public function getPaginationParameters(DatagridInterface $datagrid, $page)
    {
        $values = $datagrid->getValues();

        $values['_sort_by'] = $values['_sort_by']->getName();
        $values['_page'] = $page;

        return array('filter' => $values);
    }

    /**
     * @param string $class
     *
     * @return array
     */
    public function getDefaultSortValues($class)
    {
        return array(
            '_sort_order' => 'ASC',
            '_sort_by'    => $this->getModelIdentifier($class),
            '_page'       => 1
        );
    }

    /**
     * @param string $class
     * @param object $instance
     *
     * @return object
     */
    public function modelTransform($class, $instance)
    {
        return $instance;
    }

    /**
     * @param string $class
     * @param array $array
     *
     * @return object
     * @throws NoSuchPropertyException
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
                    throw new NoSuchPropertyException(sprintf('Method "%s()" is not public in class "%s"', $setter, $reflClass->getName()));
                }

                $instance->$setter($value);
            } else if ($reflClass->hasMethod('__set')) {
                // needed to support magic method __set
                $instance->$property = $value;
            } else if ($reflClass->hasProperty($property)) {
                if (!$reflClass->getProperty($property)->isPublic()) {
                    throw new NoSuchPropertyException(sprintf('Property "%s" is not public in class "%s". Maybe you should create the method "set%s()"?', $property, $reflClass->getName(), ucfirst($property)));
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
     *
     * @return string
     */
    protected function camelize($property)
    {
        return preg_replace(array('/(^|_)+(.)/e', '/\.(.)/e'), array("strtoupper('\\2')", "'_'.strtoupper('\\1')"), $property);
    }

    /**
     * @param string $class
     *
     * @return ArrayCollection
     */
    public function getModelCollectionInstance($class)
    {
        return new ArrayCollection();
    }

    /**
     * @param mixed $collection
     *
     * @return mixed
     */
    public function collectionClear(&$collection)
    {
        return $collection->clear();
    }

    /**
     * @param mixed $collection
     * @param mixed $element
     *
     * @return bool
     */
    public function collectionHasElement(&$collection, &$element)
    {
        return $collection->contains($element);
    }

    /**
     * @param mixed $collection
     * @param mixed $element
     *
     * @return mixed
     */
    public function collectionAddElement(&$collection, &$element)
    {
        return $collection->add($element);
    }

    /**
     * @param mixed $collection
     * @param mixed $element
     *
     * @return mixed
     */
    public function collectionRemoveElement(&$collection, &$element)
    {
        return $collection->removeElement($element);
    }

    /**
     * @param DatagridInterface $datagrid
     * @param array $fields
     * @param null $firstResult
     * @param null $maxResult
     *
     * @return null
     */
    public function getDataSourceIterator(DatagridInterface $datagrid, array $fields, $firstResult = null, $maxResult = null)
    {
        return null;
    }

    /**
     * @param string $class
     *
     * @return null
     */
    public function getExportFields($class)
    {
        return null;
    }
}
