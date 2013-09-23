<?php
/*
 * This file is part of the Sonata package.
 *
 * (c) 2010-2011 Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Sonata\DoctrinePHPCRAdminBundle\Admin;

use PHPCR\Util\PathHelper;
use Sonata\AdminBundle\Admin\Admin as BaseAdmin;
use Sonata\AdminBundle\Route\RouteCollection;

/**
 * Extend the Admin class to incorporate phpcr changes.
 *
 * Especially make sure that there are no duplicated slashes in the generated urls
 *
 * @author Uwe Jäger <uwej711@googlemail.com>
 */
class Admin extends BaseAdmin
{
    /**
     * Path to the root node of documents for this admin.
     *
     * @var string
     */
    private $rootPath;

    /**
     * Set the root path in the repository. To be able to create new items,
     * this path must already exist.
     *
     * @param string $rootPath
     */
    public function setRootPath($rootPath)
    {
        $this->rootPath = $rootPath;
    }

    /**
     * @return string
     */
    public function getRootPath()
    {
        return $this->rootPath;
    }

    /**
     * {@inheritdoc}
     */
    public function createQuery($context = 'list')
    {
        $query = $this->getModelManager()->createQuery($this->getClass(), 'a', $this->getRootPath());

        foreach ($this->extensions as $extension) {
            $extension->configureQuery($this, $query, $context);
        }

        return $query;
    }

    /**
     * {@inheritdoc}
     */
    public function generateObjectUrl($name, $object, array $parameters = array(), $absolute = false)
    {
        $parameters['id'] = $this->getUrlsafeIdentifier($object);
        return $this->generateUrl($name, $parameters, $absolute);
    }

    /**
     * {@inheritdoc}
     */
    public function getUrlsafeIdentifier($object)
    {
        return $this->modelManager->getUrlsafeIdentifier($object);
    }

    /**
     * {@inheritdoc}
     */
    public function id($object)
    {
        return $this->getUrlsafeIdentifier($object);
    }

    protected function configureRoutes(RouteCollection $collection)
    {
        foreach (array('edit', 'create', 'delete') as $name) {
            if ($collection->has($name)) {
                $collection->get($name)->addOptions(array('expose' => true));
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function toString($object)
    {
        if (!is_object($object)) {
            return parent::toString($object);
        }

        if (method_exists($object, '__toString') && null !== $object->__toString()) {
            return (string) $object;
        }

        $dm = $this->getModelManager()->getDocumentManager();
        if ($dm->contains($object)) {
            return PathHelper::getNodeName($dm->getUnitOfWork()->getDocumentId($object));
        }

        return parent::toString($object);
    }
}

