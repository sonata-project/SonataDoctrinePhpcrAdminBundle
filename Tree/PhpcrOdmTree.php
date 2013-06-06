<?php

namespace Symfony\Cmf\Bundle\TreeBrowserBundle\Tree;

use PHPCR\Util\NodeHelper;

use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Templating\Helper\CoreAssetsHelper;
use Symfony\Cmf\Bundle\TreeBrowserBundle\Tree\TreeInterface;

use Doctrine\ODM\PHPCR\DocumentManager;
use Doctrine\Common\Util\ClassUtils;

use Sonata\DoctrinePHPCRAdminBundle\Model\ModelManager;
use Doctrine\ODM\PHPCR\Document\Generic;

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

    protected $icons = array(
        'undefined' => 'bundles/cmftreebrowser/images/folder.png',
        'folder' => 'bundles/cmftreebrowser/images/folder.png',
    );

    /**
     * @var DocumentManager
     */
    private $dm;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var CoreAssetsHelper
     */
    private $assetHelper;

    /**
     * List of the valid class names that may be used as tree "ref" fields
     * @var array
     */
    private $validClasses;

    public function __construct(
        DocumentManager $dm, 
        TranslatorInterface $translator, 
        CoreAssetsHelper $assetHelper, 
        array $validClasses
    )
    {
        $this->dm = $dm;
        $this->translator = $translator;
        $this->assetHelper = $assetHelper;
        $this->validClasses = $validClasses;
    }

    /**
     * {@inheritDoc}
     */
    public function getChildren($path)
    {
        $children = array();
        $root = $this->dm->find(null, $path);

        if (!$root) {
            return $children;
        }

        foreach ($this->dm->getChildren($root) as $child) {

            // ignore system nodes
            if (
                $child instanceof Generic &&
                NodeHelper::isSystemItem($child->getNode())
            ) {
                continue;
            }

            // ignore classes not found in validClasses
            if (false === $this->isValidDocumentChild($root, $child)) {
                continue;
            }

            $child = $this->documentToArray($document);

            // can probably optimize this with fetch depth above
            // somehow.
            $grandChildren = $this->dm->getChildren($document);

            foreach ($grandChildren as $grandChild) {
                $child['children'][] = $this->documentToArray($grandChild);
            }

            $children[] = $child;
        }

        return $children;
    }

    /**
     * {@inheritDoc}
     */
    public function move($sourcePath, $targetPath)
    {
        $targetBasePath = $targetPath.'/'.basename($sourcePath);

        $document = $this->dm->find(null, $sourcePath);

        if (null === $document) {
            return sprintf('No document found at "%s"', $sourcePath);
        }

        $this->dm->move($document, $targetBasePath);
        $this->dm->flush();

        return $targetBasePath;
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
        $node = $this->dm->getNodeForDocument($document);
        $id = $node->getIdentifier();

        throw new \Exception('How to get URL safe id?');
        $urlSafeId = $id;

        $rel = in_array($className, array_keys($this->validClasses)) ? $className : 'undefined';
        $rel = $this->normalizeClassname($rel);

        return array(
            'data'  => $label,
            'attr'  => array(
                'id' => $id,
                'url_safe_id' => $urlSafeId,
                'rel' => $rel
            ),
            'state' => $node->hasNodes() ? 'closed' : null,
        );
    }

    /**
     * @param $document - Parent document
     * @param $child    - Child document
     *
     * @return bool TRUE if valid, FALSE if not vaild
     */
    private function isValidDocumentChild($className, $childClassName)
    {
        if (!isset($this->validClasses[$className])) {
            // no mapping means no valid children
            return false;
        }

        $validClass = $this->validClasses[$className];

        $showAll = false;
        if (isset($validClass['valid_children'][0])) {
            $showAll = $this->validClasses['valid_children'][0] === self::VALID_CLASS_ALL;
        }

        $isValidChild = in_array($childClassName, $validClass['valid_children']);

        return $showAll || $isValidChild;
    }

    /**
     * {@inheritDoc}
     */
    public function reorder($parentPath, $sourcePath, $targetPath, $before)
    {
        $parentDocument = $this->dm->find(null, $parent);

        $this->dm->reorder(
            $parentDocument, 
            basename($sourcePath), 
            basename($targetPath), 
            $before
        );

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
        
        $result['undefined'] = array(
            'icon' => array(
                'image' => $this->assetHelper->getUrl($this->icons['undefined']),
            ),
            'valid_children' => 'all',
            'routes' => array()
        );

        foreach ($this->validClasses as $className => $classConfig) {

            $normalizedClassName = $this->normalizeClassname($className);

            $validChildren = array();

            foreach ($classConfig['valid_children'] as $validChild) {
                $validChildren[] = $this->normalizeClassname($validChild);
            }

            $icon = $this->icons['folder'];;

            if (!empty($children['image'])) {
                $icon = $children['image'];
            }

            $routes = array();

            // todo: sane way to register routes
            $routes['edit'] = null;

            $result[$normalizedClassName] = array(
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
     *
     * @note: what is this for??
     */
    public function getLabels()
    {
        return array(
            'createItem' => $this->translator->trans(
                'create_item', array(), 'CmfTreeBrowserBundle'
            ),
            'deleteItem' => $this->translator->trans(
                'delete_item', array(), 'CmfTreeBrowserBundle'
            ),
        );
    }

    private function normalizeClassname($className)
    {
        return str_replace('\\', '_', $className);
    }
}
