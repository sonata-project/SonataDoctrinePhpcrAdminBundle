<?php

namespace Sonata\DoctrinePHPCRAdminBundle\Tree;

use Doctrine\ODM\PHPCR\Document\Generic;
use PHPCR\Util\NodeHelper;

use PHPCR\Util\PathHelper;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Templating\Helper\CoreAssetsHelper;
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
 * @author Uwe Jäger <uwej711@googlemail.com>
 */
class PhpcrOdmTree implements TreeInterface
{
    const VALID_CLASS_ALL = 'all';

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
     * @param DocumentManager $dm
     * @param ModelManager $defaultModelManager to use with documents that
     *      have no manager
     * @param Pool $pool to get admin classes for documents from
     * @param TranslatorInterface $translator
     * @param $assetHelper
     * @param array $validClasses list of the valid class names that may be
     *      used as tree "ref" fields
     */
    public function __construct(DocumentManager $dm, ModelManager $defaultModelManager, Pool $pool, TranslatorInterface $translator, CoreAssetsHelper $assetHelper, array $validClasses)
    {
        $this->dm = $dm;
        $this->defaultModelManager = $defaultModelManager;
        $this->pool = $pool;
        $this->translator = $translator;
        $this->assetHelper = $assetHelper;
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

        if ($root) {
            foreach ($this->getDocumentChildren($root) as $document) {
                if ($document instanceof Generic &&
                    (NodeHelper::isSystemItem($document->getNode())
                        || !strncmp('phpcr_locale:', $document->getNode()->getName(), 13)
                    )
                ) {
                    continue;
                }

                $child = $this->documentToArray($document);

                foreach ($this->getDocumentChildren($document) as $grandchild) {
                    $child['children'][] = $this->documentToArray($grandchild);
                }

                $children[] = $child;
            }
        }

        return $children;
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

        $rel = (in_array($className, array_keys($this->validClasses))) ? $className : 'undefined';
        $rel = $this->normalizeClassname($rel);

        $admin = $this->getAdmin($document);
        if (null !== $admin) {
            $label = $admin->toString($document);
            $id = $admin->getNormalizedIdentifier($document);
            $urlSafeId = $admin->getUrlsafeIdentifier($document);
        } else {
            $label = method_exists($document, '__toString') ? (string) $document : ClassUtils::getClass($document);
            $id = $this->defaultModelManager->getNormalizedIdentifier($document);
            $urlSafeId = $this->defaultModelManager->getUrlsafeIdentifier($document);
        }

        if (substr($label, 0, 1) === '/') {
            $label = PathHelper::getNodeName($label);
        }

        // TODO: this is really the responsibility of the UI
        if (strlen($label) > 18) {
            $label = substr($label, 0, 17) . '...';
        }

        // TODO: ideally the tree should simply not make the node clickable
        $label .= $admin ? '' : ' (not editable)';

        // TODO: this is not an efficient way to determine if there are children. should ask the phpcr node
        $hasChildren = (bool)count($this->getDocumentChildren($document));

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
     * @return \Sonata\AdminBundle\Admin\AdminInterface
     */
    private function getAdmin($document)
    {
        $className = ClassUtils::getClass($document);
        return $this->getAdminByClass($className);
    }

    /**
     * @param string $className
     *
     * @return \Sonata\AdminBundle\Admin\AdminInterface
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
     * @param object $document the PHPCR-ODM document to get the children of
     *
     * @return array of children indexed by child nodename pointing to the child documents
     */
    private function getDocumentChildren($document)
    {
        $admin = $this->getAdmin($document);
        $manager = null !== $admin ? $admin->getModelManager() : $this->defaultModelManager;

        /** @var $meta \Doctrine\ODM\PHPCR\Mapping\ClassMetadata */
        $meta = $manager->getMetadata(ClassUtils::getClass($document));

        $children = array();
        foreach ($meta->childrenMappings as $fieldName) {
            $prop = $meta->getReflectionProperty($fieldName)->getValue($document);
            if (null === $prop) {
                continue;
            }
            if (!is_array($prop)) {
                $prop = $prop->toArray();
            }
            $children = array_merge($children, $this->filterDocumentChildren($document, $prop));
        }

        foreach ($meta->childMappings as $fieldName) {
            $prop = $meta->getReflectionProperty($fieldName)->getValue($document);
            if (null !== $prop && $this->isValidDocumentChild($document, $prop)) {
                $children[$fieldName] = $prop;
            }
        }

        return $children;
    }

    /**
     * @param $document
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
     * @param $document
     * @param $child
     *
     * @return bool TRUE if valid, FALSE if not vaild
     */
    public function isValidDocumentChild($document, $child)
    {
        $className = ClassUtils::getClass($document);
        $childClassName = ClassUtils::getClass($child);

        if (!isset($this->validClasses[$className])) {
            // no mapping means no valid children
            return false;
        }

        if (isset($this->validClasses[$className]['valid_children'][0])
            && $this->validClasses[$className]['valid_children'][0] === self::VALID_CLASS_ALL
        ) {
            return true;
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
        $result = array('undefined' => array(
            'icon' => array('image' => $this->assetHelper->getUrl('bundles/cmftreebrowser/images/folder.png')),
            'valid_children' => 'all',
            'routes' => array()
        ));

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
}
