<?php

namespace Sonata\DoctrinePHPCRAdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

/**
 * A controller to render the tree block
 */
class TreeController extends Controller
{
    /**
     * array indexed by class names pointing to info records.
     * the info contains the valid_children key pointing to an array of allowed child class names for this document type.
     * @var array
     */
    private $types;

    private $template = 'SonataDoctrinePHPCRAdminBundle:Tree:tree.html.twig';

    private $defaults;

    /**
     * @param array $types array of document class names to valid_children list
     * @param string $template the template to render the tree, defaults to Tree:tree.html.twig
     * @param array $defaults an array of values that should be included in the tree routes
     */
    public function __construct(array $types, $template = null, array $defaults = array())
    {
        $this->types = $types;
        if ($template) {
            $this->template = $template;
        }
        $this->defaults = $defaults;
    }

    /**
     * Renders a tree, passing the routes for each of the admin types (document types)
     * to the view
     *
     * @param string $id path to the tree root
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function treeAction($id, $selected = null)
    {
        //Obtain the routes for each document
        /** @var $pool \Sonata\AdminBundle\Admin\Pool */
        $pool = $this->get('sonata.admin.pool');

        // TODO: this is somewhat inconsistent:
        // for editing new documents, we build the info here
        // but the info about moving documents is configured and injected
        // the two types do not fully map, i.e. you might configure move for
        // documents that have no admin
        $classes = $pool->getAdminClasses();
        $adminClasses = array();

        foreach ($classes as $class) {
            /** @var $instance \Sonata\AdminBundle\Admin\AdminInterface */
            // TODO: the AdminInterface seems to be incomplete, we rely on
            // methods provided only by the abstract Admin base class
            $instance = $this->get($class);
            $routeCollection = array();
            foreach ($instance->getRoutes()->getElements() as $code => $route) {
                $action = explode('.', $code);
                $routeCollection[end($action)] = sprintf('%s_%s', $instance->getBaseRouteName(), end($action));
            }
            array_push($adminClasses, array(
                'label'     => $instance->trans($instance->getLabel()),
                'className' => $instance->getClass(),
                'baseRoute' => $instance->getBaseRoutePattern(),
                'routes'    => $routeCollection));
        }

        return $this->render($this->template, array(
            'id'            => $id,
            'selected'      => $selected ?: $id,
            'admin_pool'    => $this->container->get('sonata.admin.pool'),
            'handlers'      => $adminClasses,
            'types'         => $this->types,
            'routing_defaults' => $this->defaults,
        ));
    }
}
