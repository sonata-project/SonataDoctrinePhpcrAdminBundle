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

namespace Sonata\DoctrinePHPCRAdminBundle\Tests\Fixtures\App\Document;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ODM\PHPCR\Mapping\Annotations as PHPCRODM;
use PHPCR\NodeInterface;

/**
 * @PHPCRODM\Document(referenceable=true)
 */
class Content
{
    /**
     * @PHPCRODM\Id
     */
    protected $id;

    /**
     * @PHPCRODM\Node
     */
    protected $node;

    /**
     * @PHPCRODM\ParentDocument
     */
    protected $parentDocument;

    /**
     * @PHPCRODM\Nodename
     */
    protected $name;

    /**
     * @PHPCRODM\Field(type="string")
     */
    protected $title;

    /**
     * @PHPCRODM\Child()
     *
     * @var Content
     */
    protected $child;

    /**
     * @PHPCRODM\Children()
     *
     * @var ArrayCollection|Content[]
     */
    protected $children;

    /**
     * @PHPCRODM\ReferenceOne(targetDocument="Sonata\DoctrinePHPCRAdminBundle\Tests\Fixtures\App\Document\Content")
     *
     * @var Content
     */
    protected $singleRoute;

    /**
     * @PHPCRODM\ReferenceMany(targetDocument="Sonata\DoctrinePHPCRAdminBundle\Tests\Fixtures\App\Document\Content")
     *
     * @var ArrayCollection|Content[]
     */
    protected $routes;

    public function __construct()
    {
        $this->children = new ArrayCollection();
        $this->routes = new ArrayCollection();
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Gets the underlying PHPCR node of this content.
     *
     * @return NodeInterface
     */
    public function getNode()
    {
        return $this->node;
    }

    /**
     * Sets the parent document.
     *
     * @param object $parent the parent document
     */
    public function setParentDocument($parent)
    {
        $this->parentDocument = $parent;

        return $this;
    }

    /**
     * Gets the parent document.
     *
     * @return object
     */
    public function getParentDocument()
    {
        return $this->parentDocument;
    }

    /**
     * Sets the document name.
     *
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Gets the document name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @return string
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * @param string $body
     *
     * @return Content
     */
    public function setBody($body)
    {
        $this->body = $body;

        return $this;
    }

    /**
     * @return Content
     */
    public function getChild()
    {
        return $this->child;
    }

    /**
     * @param mixed $child
     */
    public function setChild($child)
    {
        $this->child = $child;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * @param mixed $children
     */
    public function setChildren($children)
    {
        $this->children = $children;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getSingleRoute()
    {
        return $this->singleRoute;
    }

    /**
     * @param mixed $singleRoute
     */
    public function setSingleRoute($singleRoute)
    {
        $this->singleRoute = $singleRoute;

        return $this;
    }

    /**
     * @return ArrayCollection|Content[]
     */
    public function getRoutes()
    {
        return $this->routes;
    }

    /**
     * @param ArrayCollection|Content[] $routes
     */
    public function setRoutes($routes)
    {
        $this->routes = $routes;

        return $this;
    }

    /**
     * @param $route
     */
    public function addRoute($route): void
    {
        $this->routes->add($route);
    }

    /**
     * @param $route
     */
    public function removeRoute($route): void
    {
        $this->routes->removeElement($route);
    }

    /**
     * @param $child
     */
    public function addChild($child): void
    {
        $this->children->add($child);
    }

    /**
     * @param $child
     */
    public function removeChild($child): void
    {
        $this->children->removeElement($child);
    }
}
