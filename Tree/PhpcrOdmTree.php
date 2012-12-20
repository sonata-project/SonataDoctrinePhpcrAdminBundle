<?php

namespace Sonata\DoctrinePHPCRAdminBundle\Tree;

use PHPCR\Util\NodeHelper;

use Symfony\Cmf\Bundle\TreeBrowserBundle\Tree\TreeInterface;

use Doctrine\ODM\PHPCR\DocumentManager;
use Doctrine\Common\Util\ClassUtils;

use Sonata\AdminBundle\Admin\Pool;
use Sonata\DoctrinePHPCRAdminBundle\Model\ModelManager;

/**
 * A tree implementation to work with Doctrine PHPCR-ODM
 *
 * Your documents need to map all children with an Children mapping for the
 * tree to see its children. Not having the Children annotation is a
 * possibility to not show children you do not want to show.
 *
 * @author David Buchmann <david@liip.ch>
 */
class PhpcrOdmTree implements TreeInterface
{
    /**
     * @var ModelManager
     */
    private $defaultModelManager;
    /**
     * @var DocumentManager
     */
    private $dm;

    /**
     * @var Pool
     */
    private $pool;

    /**
     * Array of cached admin services indexed by class name
     * @var array
     */
    private $admins = array();

    /**
     * List of the valid class names that may be used as tree "ref" fields
     * @var array
     */
    private $validClasses;

    /**
     * @param DocumentManager $manager to get documents from
     * @param ModelManager $defaultModelManager to use with documents that
     *      have no manager
     * @param Pool $pool to get admin classes for documents from
     * @param array $validClasses list of the valid class names that may be
     *      used as tree "ref" fields
     */
    public function __construct(DocumentManager $dm, ModelManager $defaultModelManager, Pool $pool, array $validClasses)
    {
        $this->dm = $dm;
        $this->defaultModelManager = $defaultModelManager;
        $this->pool = $pool;
        $this->validClasses = $validClasses;
    }

    /**
     * {@inheritDoc}
     *
     * Get the children of the document at this path by looking at the Child and Children mappings.
     */
    public function getChildren($path)
    {
        $root = $this->dm->find(null, $path);

        $children = array();

        foreach ($this->getDocumentChildren($root) as $document) {
            if ($document instanceof \Doctrine\ODM\PHPCR\Document\Generic &&
                NodeHelper::isSystemItem($document->getNode())) {
                continue;
            }

            $child = $this->documentToArray($document);

            foreach ($this->getDocumentChildren($document) as $grandchild) {
                $child['children'][] = $this->documentToArray($grandchild);
            }

            $children[] = $child;
        }

        return $children;
    }

    /**
     * This method makes no sense in this context
     */
    public function getProperties($path)
    {
        throw new \Exception('not implemented');
    }

    /**
     * {@inheritDoc}
     */
    public function move($moved_path, $target_path)
    {
        $resulting_path = $target_path.'/'.basename($moved_path);

        $document = $this->dm->find(null, $moved_path);
        if (null === $document) {
            return "No document found at $moved_path";
        }

        $this->dm->move($document, $resulting_path);
        $this->dm->flush();

        return $resulting_path;
    }

    /**
     * Returns an array representation of the document
     *
     * @param object $document
     *
     * @return array
     */
    private function documentToArray($document)
    {
        $className = ClassUtils::getClass($document);
        $rel = (in_array($className, $this->validClasses)) ? $className : 'undefined';

        $admin = $this->getAdmin($document);
        if (null !== $admin) {
            $label = $admin->toString($document);
            $id = $admin->getNormalizedIdentifier($document);
            $urlSafeId = $admin->getUrlsafeIdentifier($document);
        } else {
            $className = ''; // empty class name means not editable
            $label = '';
            if (method_exists($document, '__toString')) {
                $label = (string)$document;
            }
            if (strlen($label) > 18) {
                // TODO: tooltip with full name?
                $label = substr($label, 0, 17) . '...';
            }
            $label .= ' <not editable>';
            $id = $this->defaultModelManager->getNormalizedIdentifier($document);
            $urlSafeId = $this->defaultModelManager->getUrlsafeIdentifier($document);
        }

        // TODO: this is not an efficient way to determine if there are children. should ask the phpcr node
        $has_children = (bool)count($this->getDocumentChildren($document));

        return array(
            'data'  => $label,
            'attr'  => array(
                'id' => $id,
                'url_safe_id' => $urlSafeId,
                'rel' => $rel,
                'classname' => $className,
            ),
            'state' => $has_children ? 'closed' : null,
        );
    }

    /**
     * @param object $document the PHPCR-ODM document to get the sonata admin for
     *
     * @return \Sonata\AdminBundle\Admin\AdminInterface
     */
    private function getAdmin($document)
    {
        $class = ClassUtils::getClass($document);
        if (!isset($this->admins[$class])) {
            // will return null if not defined
            $this->admins[$class] = $this->pool->getAdminByClass($class);
        }
        return $this->admins[$class];
    }

    /**
     * @param object $document the PHPCR-ODM document to get the children of
     *
     * @return array of children indexed by child nodename pointing to the child documents
     */
    private function getDocumentChildren($document)
    {
        $admin = $this->getAdmin($document);
        $manager = (null !== $admin) ? $admin->getModelManager() : $this->defaultModelManager;
        $meta = $manager->getMetadata(ClassUtils::getClass($document));
        /** @var $meta \Doctrine\ODM\PHPCR\Mapping\ClassMetadata */
        $children = array();
        foreach ($meta->childrenMappings as $mapping) {
            $prop = $meta->getReflectionProperty($mapping['name'])->getValue($document);
            if (is_null($prop)) {
                continue;
            }
            if (! is_array($prop)) {
                $prop = $prop->toArray();
            }
            $children = array_merge($children, $prop);
        }
        foreach ($meta->childMappings as $mapping) {
            $prop = $meta->getReflectionProperty($mapping['name'])->getValue($document);
            if (! is_null($prop)) {
                $children[$mapping['fieldName']] = $prop;
            }
        }

        return $children;
    }
}
