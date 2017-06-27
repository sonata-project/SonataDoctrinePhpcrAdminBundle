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

use Doctrine\Bundle\PHPCRBundle\ManagerRegistry;
use PHPCR\Util\PathHelper;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
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
     * @var \PHPCR\SessionInterface
     */
    private $session;

    /**
     * @param ManagerRegistry $manager
     * @param string $sessionName
     * @param string $repositoryName
     * @param string $template
     * @param array $defaults
     * @param bool $confirmMove
     */
    public function __construct(
        ManagerRegistry $manager,
        $sessionName,
        $repositoryName = 'default',
        $template = null,
        array $defaults = array(),
        $confirmMove = false
    ) {
        $this->repositoryName = $repositoryName;
        if ($template) {
            $this->template = $template;
        }
        $this->defaults = $defaults;

        $this->confirmMove = $confirmMove;

        $this->session = $manager->getConnection($sessionName);
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

    /**
     * Reorder $moved (child of $parent) before or after $target.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function reorderAction(Request $request)
    {
        $parentPath = $request->get('parent');
        $dropedAtPath = $request->get('dropped');
        $targetPath = $request->get('target');
        $position = $request->get('position');

        if (null === $parentPath || null === $dropedAtPath || null === $targetPath) {
            return new JsonResponse(['Parameters parent, dropped and target has to be set to reorder.'], Response::HTTP_BAD_REQUEST);
        }

        $before = 'before' == $position;
        $parentNode = $this->session->getNode($parentPath);
        $targetName = PathHelper::getNodeName($targetPath);
        if (!$before) {
            $nodesIterator = $parentNode->getNodes();
            $nodesIterator->rewind();
            while ($nodesIterator->valid()) {
                if ($nodesIterator->key() == $targetName) {
                    break;
                }
                $nodesIterator->next();
            }
            $targetName = null;
            if ($nodesIterator->valid()) {
                $nodesIterator->next();
                if ($nodesIterator->valid()) {
                    $targetName = $nodesIterator->key();
                }
            }
        }
        $parentNode->orderBefore($targetName, PathHelper::getNodeName($dropedAtPath));
        $this->session->save();

        return new Response('', Response::HTTP_NO_CONTENT);
    }
}
