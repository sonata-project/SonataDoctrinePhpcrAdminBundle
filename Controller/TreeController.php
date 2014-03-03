<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\DoctrinePHPCRAdminBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Cmf\Bundle\TreeBrowserBundle\Tree\TreeInterface;

/**
 * A controller to render the tree block
 */
class TreeController extends Controller
{
    /**
     * @var TreeInterface
     */
    private $tree;

    /**
     * @var string
     */
    private $template = 'SonataDoctrinePHPCRAdminBundle:Tree:tree.html.twig';

    /**
     * @var array
     */
    private $defaults;

    /**
     * @var boolean
     */
    private $confirmMove = false;

    /**
     * @param TreeInterface $tree
     * @param string $template
     * @param array $defaults
     * @param boolean $confirmMove
     */
    public function __construct(TreeInterface $tree, $template = null, array $defaults = array(), $confirmMove = false)
    {
        $this->tree = $tree;
        if ($template) {
            $this->template = $template;
        }
        $this->defaults = $defaults;

        $this->confirmMove = $confirmMove;
    }

    /**
     * Renders a tree, passing the routes for each of the admin types (document types)
     * to the view
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
            'tree' => $this->tree,
            'root_node' => $root,
            'selected_node' => $selected,
            'routing_defaults' => $this->defaults,
            'confirm_move' => $this->confirmMove,
            'create_in_overlay' => $createInOverlay ? $createInOverlay : false,
            'edit_in_overlay' => $editInOverlay ? $editInOverlay : false,
            'delete_in_overlay' => $deleteInOverlay ? $deleteInOverlay : false,
        ));
    }
}
