<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\DoctrinePHPCRAdminBundle\Tree;

use Doctrine\ODM\PHPCR\Document\Generic;
use PHPCR\Util\NodeHelper;

use PHPCR\Util\PathHelper;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\Exception\NoSuchPropertyException;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Templating\Helper\CoreAssetsHelper;
use Symfony\Cmf\Bundle\TreeBrowserBundle\Tree\TreeInterface;

use Doctrine\ODM\PHPCR\DocumentManager;
use Doctrine\Common\Util\ClassUtils;

use Sonata\AdminBundle\Admin\Pool;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\DoctrinePHPCRAdminBundle\Model\ModelManager;

/**
 * A tree implementation to work with Doctrine PHPCR-ODM
 *
 * Your documents need to map all children with an Children mapping for the
 * tree to see its children. Not having the Children annotation is a
 * possibility to not show children you do not want to show.
 *
 * @author David Buchmann <david@liip.ch>
 * @author Uwe Jäger <uwej711@googlemail.com>
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
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var CoreAssetsHelper
     */
    private $assetHelper;

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
     * Depth to which grand children should be fetched, currently the maximum depth is one
     * @var integer
     */
    private $depth;

    /**
     * Fetch children lazy - enabling this will allow the tree to fetch a larger amount of  children in the tree but less accurate
     * @var bool
     */
    private $preciseChildren;

    /**
     * The options are
     *
     * - depth: Down to what level children should be fetched, currently the
     *      maximum supported depth is one.
     * - precise_children: To determine if a tree element has children, check if
     *      the document has valid children. If false, simply check if the node
     *      has any child nodes. Less accurate but better performance.
     *
     * @param DocumentManager $dm
     * @param ModelManager $defaultModelManager to use with documents that have no manager
     * @param Pool $pool to get admin classes for documents from
     * @param TranslatorInterface $translator
     * @param CoreAssetsHelper $assetHelper
     * @param array $validClasses list of the valid class names that may be
     *      used as tree "ref" fields
     * $param integer $depth depth to which grand children should be fetched,
     *      currently the maximum depth is one
     * @param array $options
     */
    public function __construct(
        DocumentManager $dm,
        ModelManager $defaultModelManager,
        Pool $pool,
        TranslatorInterface $translator,
        CoreAssetsHelper $assetHelper,
        array $validClasses,
        array $options
    ) {
        $this->dm = $dm;
        $this->defaultModelManager = $defaultModelManager;
        $this->pool = $pool;
        $this->translator = $translator;
        $this->assetHelper = $assetHelper;
        $this->validClasses = $validClasses;

        $this->depth = $options['depth'];
        $this->preciseChildren = $options['precise_children'];
    }

    /**
     * Get the children of the document at this path by looking at the Child and Children mappings.
     *
     * {@inheritDoc}
     */
    public function getChildren($path)
    {
        $root = $this->dm->find(null, $path);

        $children = array();

        if ($root) {
            $rootManager = $this->getModelManager($root);
            foreach ($this->getDocumentChildren($rootManager, $root) as $document) {
                if ($document instanceof Generic &&
                    (NodeHelper::isSystemItem($document->getNode())
                        || !strncmp('phpcr_locale:', $document->getNode()->getName(), 13)
                    )
                ) {
                    continue;
                }
                $manager = $this->getModelManager($document);

                $child = $this->documentToArray($manager, $document);

                if ($this->depth > 0) {
                    foreach ($this->getDocumentChildren($manager, $document) as $grandchild) {
                        $child['children'][] = $this->documentToArray($manager, $grandchild);
                    }
                }

                $children[] = $child;
            }
        }

        return $children;
    }

    /**
     * {@inheritDoc}
     */
    public function move($movedPath, $targetPath)
    {
        $resultingPath = $targetPath.'/'.basename($movedPath);

        $document = $this->dm->find(null, $movedPath);
        if (null === $document) {
            return "No document found at $movedPath";
        }

        $this->dm->move($document, $resultingPath);
        $this->dm->flush();

        $admin = $this->getAdmin($document);
        if (null !== $admin) {
            $id = $admin->getNormalizedIdentifier($document);
            $urlSafeId = $admin->getUrlsafeIdentifier($document);
        } else {
            $id = $this->defaultModelManager->getNormalizedIdentifier($document);
            $urlSafeId = $this->defaultModelManager->getUrlsafeIdentifier($document);
        }

        return array('id' => $id, 'url_safe_id' => $urlSafeId);
    }

    /**
     * Returns an array representation of the document
     *
     * @param ModelManager $manager the manager to use with this document
     * @param object       $document
     *
     * @return array
     */
    protected function documentToArray(ModelManager $manager, $document)
    {
        $className = ClassUtils::getClass($document);

        $rel = (in_array($className, array_keys($this->validClasses))) ? $className : 'undefined';
        $rel = $this->normalizeClassname($rel);

        $admin = $this->getAdmin($document);
        if (null !== $admin) {
            $label = $admin->toString($document);
            $id = $admin->getNormalizedIdentifier($document);
            $urlSafeId = $admin->getUrlsafeIdentifier($document);
        } else {
            $label = method_exists($document, '__toString') ? (string) $document : ClassUtils::getClass($document);
            $id = $manager->getNormalizedIdentifier($document);
            $urlSafeId = $manager->getUrlsafeIdentifier($document);
        }

        if (substr($label, 0, 1) === '/') {
            $label = PathHelper::getNodeName($label);
        }

        // TODO: ideally the tree should simply not make the node clickable
        $label .= $admin ? '' : ' '.$this->translator->trans('not_editable', array(), 'SonataDoctrinePHPCRAdmin');

        $hasChildren = false;
        if (isset($this->validClasses[$className]['valid_children'])
            && count($this->validClasses[$className]['valid_children'])
        ) {
            if ($this->preciseChildren) {
                // determine if a node has children the accurate way. we need to
                // loop over all documents, as a PHPCR node might have children but
                // only invalid ones. this is quite costly.
                $hasChildren = (bool) count($this->getDocumentChildren($manager, $document));
            } else {
                // just check if there is any child node
                $hasChildren = $manager->getDocumentManager()->getNodeForDocument($document)->hasNodes();
            }
        }

        return array(
            'data'  => $label,
            'attr'  => array(
                'id' => $id,
                'url_safe_id' => $urlSafeId,
                'rel' => $rel
            ),
            'state' => $hasChildren ? 'closed' : null,
        );
    }

    /**
     * @param object $document the PHPCR-ODM document to get the sonata admin for
     *
     * @return AdminInterface
     */
    private function getAdmin($document)
    {
        $className = ClassUtils::getClass($document);
        return $this->getAdminByClass($className);
    }

    /**
     * @param string $className
     *
     * @return AdminInterface
     */
    private function getAdminByClass($className)
    {
        if (!isset($this->admins[$className])) {
            // will return null if not defined
            $this->admins[$className] = $this->pool->getAdminByClass($className);
        }

        return $this->admins[$className];
    }

    /**
     * @param ModelManager $manager the manager to use with this document
     * @param object $document      the PHPCR-ODM document to get the children of
     *
     * @return array of children indexed by child nodename pointing to the child documents
     */
    private function getDocumentChildren(ModelManager $manager, $document)
    {
        $accessor = PropertyAccess::getPropertyAccessor(); // use deprecated BC method to support symfony 2.2

        /** @var $meta \Doctrine\ODM\PHPCR\Mapping\ClassMetadata */
        $meta = $manager->getMetadata(ClassUtils::getClass($document));

        $children = array();
        foreach ($meta->childrenMappings as $fieldName) {
            try {
                $prop = $accessor->getValue($document, $fieldName);
            } catch (NoSuchPropertyException $e) {
                $prop = $meta->getReflectionProperty($fieldName)->getValue($document);
            }
            if (null === $prop) {
                continue;
            }
            if (!is_array($prop)) {
                $prop = $prop->toArray();
            }

            $children = array_merge($children, $this->filterDocumentChildren($document, $prop));
        }

        foreach ($meta->childMappings as $fieldName) {
            try {
                $prop = $accessor->getValue($document, $fieldName);
            } catch (NoSuchPropertyException $e) {
                $prop = $meta->getReflectionProperty($fieldName)->getValue($document);
            }
            if (null !== $prop && $this->isValidDocumentChild($document, $prop)) {
                $children[$fieldName] = $prop;
            }
        }

        return $children;
    }

    /**
     * @param object $document
     * @param array $children
     *
     * @return array of valid children for the document
     */
    protected function filterDocumentChildren($document, array $children)
    {
        $me = $this;

        return array_filter($children, function ($child) use ($me, $document) {
            return $me->isValidDocumentChild($document, $child);
        });
    }

    /**
     * @param object $document
     * @param object $child
     *
     * @return boolean TRUE if valid, FALSE if not valid
     */
    public function isValidDocumentChild($document, $child)
    {
        $className = ClassUtils::getClass($document);
        $childClassName = ClassUtils::getClass($child);

        if (!isset($this->validClasses[$className])) {
            // no mapping means no valid children
            return false;
        }

        return in_array($childClassName, $this->validClasses[$className]['valid_children']);
    }

    /**
     * {@inheritDoc}
     */
    public function reorder($parent, $moved, $target, $before)
    {
        $parentDocument = $this->dm->find(null, $parent);
        $this->dm->reorder($parentDocument, basename($moved), basename($target), $before);
        $this->dm->flush();
    }

    /**
     * {@inheritDoc}
     */
    public function getAlias()
    {
        return 'phpcr_odm_tree';
    }

    /**
     * {@inheritDoc}
     */
    public function getNodeTypes()
    {
        $result = array();

        foreach ($this->validClasses as $className => $children) {
            $rel = $this->normalizeClassname($className);
            $admin = $this->getAdminByClass($className);
            $validChildren = array();

            foreach ($children['valid_children'] as $child) {
                $validChildren[] = $this->normalizeClassname($child);
            }

            $icon = 'bundles/cmftreebrowser/images/folder.png';
            if (!empty($children['image'])) {
                $icon = $children['image'];
            }

            $routes = array();
            if (null !== $admin) {
                foreach ($admin->getRoutes()->getElements() as $code => $route) {
                    $action = explode('.', $code);
                    $key = $this->mapAction(end($action));

                    if (null !== $key) {
                        $routes[$key] = sprintf('%s_%s', $admin->getBaseRouteName(), end($action));
                    }
                }
            }

            $result[$rel] = array(
                'icon' => array('image' => $this->assetHelper->getUrl($icon)),
                'label' => (null !== $admin) ? $admin->trans($admin->getLabel()) : $className,
                'valid_children' => $validChildren,
                'routes' => $routes
            );
        }

        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function getLabels()
    {
        return array(
            'createItem' => $this->translator->trans('create_item', array(), 'SonataDoctrinePHPCRAdmin'),
            'deleteItem' => $this->translator->trans('delete_item', array(), 'SonataDoctrinePHPCRAdmin'),
        );
    }

    /**
     * @param string $className
     *
     * @return string
     */
    private function normalizeClassname($className)
    {
        return str_replace('\\', '_', $className);
    }

    /**
     * @param string $action
     *
     * @return string|null
     */
    private function mapAction($action)
    {
        switch ($action) {
            case 'edit': return 'select_route';
            case 'create': return 'create_route';
            case 'delete': return 'delete_route';
        }

        return null;
    }

    /**
     * @param object $document
     *
     * @return ModelManager the modelmanager for $document or the default manager
     */
    protected function getModelManager($document = NULL)
    {
        $admin = $document ? $this->getAdmin($document) : NULL;

        return $admin ? $admin->getModelManager() : $this->defaultModelManager;
    }
}
