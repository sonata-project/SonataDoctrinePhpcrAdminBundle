<?php

namespace Sonata\DoctrinePHPCRAdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\Response;

class TreeController extends Controller
{
    private $template;

    public function __construct($template = 'SonataDoctrinePHPCRAdminBundle:Tree:tree.html.twig')
    {
        $this->template = $template;
    }

    /**
     * Renders a tree, passing the routes for each of the admin types (document types)
     * to the view
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function treeAction($id, $selected = null) {
        //Obtain the routes for each document
        $pool = $this->get('sonata.admin.pool');
        $classes = $pool->getAdminClasses();
        $adminClasses = array();

        foreach ($classes as $class) {
            $instance = $this->get($class);
            $routeCollection = array();
            foreach ($instance->getRoutes()->getElements() as $code => $route) {
                $action = explode('.', $code);
                $routeCollection[end($action)] = sprintf('%s_%s', $instance->getBaseRouteName(), end($action));
            }
            array_push($adminClasses, array(
                'label'     => $instance->getLabel(),
                'className' => $instance->getClass(),
                'baseRoute' => $instance->getBaseRoutePattern(),
                'routes'    => $routeCollection));
        }

        return $this->render($this->template, array(
            'id'            => $id,
            'selected'      => $selected ?: $id,
            'admin_pool'    => $this->container->get('sonata.admin.pool'),
            'handlers'      => $adminClasses
        ));
    }
}
