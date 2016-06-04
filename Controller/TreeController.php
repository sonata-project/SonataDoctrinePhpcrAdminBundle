<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\DoctrinePHPCRAdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * A controller to render the tree block.
 */
class TreeController extends Controller
{
    /**
     * @var string
     */
    private $repositoryName;

    /**
     * @var string
     */
    private $template = 'SonataDoctrinePHPCRAdminBundle:Tree:tree.html.twig';

    /**
     * @var array
     */
    private $defaults;

    /**
     * @var bool
     */
    private $confirmMove = false;

    /**
     * @param string $repositoryName
     * @param string $template
     * @param array  $defaults
     * @param bool   $confirmMove
     */
    public function __construct($repositoryName = 'default', $template = null, array $defaults = array(), $confirmMove = false)
    {
        $this->repositoryName = $repositoryName;
        if ($template) {
            $this->template = $template;
        }
        $this->defaults = $defaults;

        $this->confirmMove = $confirmMove;
    }

    /**
     * Renders a tree, passing the routes for each of the admin types (document types)
     * to the view.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function treeAction(Request $request)
    {
        $createInOverlay = $request->attributes->get('create_in_overlay');
        $editInOverlay = $request->attributes->get('edit_in_overlay');
        $deleteInOverlay = $request->attributes->get('delete_in_overlay');

        $root = $request->attributes->get('root');
        $selected = $request->attributes->get('selected') ?: $root;

        return $this->render($this->template, array(
            'repository_name' => $this->repositoryName,
            'root_node' => $root,
            'routing_defaults' => $this->defaults,
            //'confirm_move' => $this->confirmMove,
            //'create_in_overlay' => $createInOverlay ? $createInOverlay : false,
            //'edit_in_overlay' => $editInOverlay ? $editInOverlay : false,
            //'delete_in_overlay' => $deleteInOverlay ? $deleteInOverlay : false,
        ));
    }
}
