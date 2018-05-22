<?php

namespace Fousky\TreeBundle\Controller;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManager;
use Fousky\TreeBundle\Admin\AbstractTreeAdmin;
use Gedmo\Tree\Entity\Repository\NestedTreeRepository;
use Sonata\AdminBundle\Controller\CRUDController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * @author Lukáš Brzák <lukas.brzak@fousky.cz>
 */
class TreeAdminController extends CRUDController
{
    /** @var AbstractTreeAdmin $admin */
    protected $admin;

    /**
     * @param Request|null $request
     *
     * @return null|\Symfony\Component\HttpFoundation\Response
     * @throws \Symfony\Component\Security\Core\Exception\AccessDeniedException
     */
    public function listAction(Request $request = null)
    {
        $this->admin->checkAccess('list');

        if (null === $request) {
            $request = $this->get('request_stack')->getMasterRequest();
        }

        if ($listMode = $request->get('_list_mode')) {
            $this->admin->setListMode($listMode);
        }

        $listMode = $this->admin->getListMode();

        if ($listMode === 'tree') {
            $this->admin->checkAccess('list');

            $preResponse = $this->preList($request);
            if ($preResponse !== null) {
                return $preResponse;
            }

            return $this->renderWithExtraParams('FouskyTreeBundle:CRUD:tree.html.twig', [
                    'action' => 'list',
                    'csrf_token' => $this->getCsrfToken('sonata.batch'),
                    '_sonata_admin' => $request->get('_sonata_admin'),
                ]
            );
        }

        return parent::listAction();
    }

    /**
     * @param Request|null $request
     *
     * @return JsonResponse
     * @throws \Symfony\Component\HttpKernel\Exception\BadRequestHttpException
     * @throws \RuntimeException
     * @throws \Sonata\AdminBundle\Exception\ModelManagerException
     */
    public function treeDataAction(Request $request = null): JsonResponse
    {
        $this->admin->checkAccess('list');

        if (null === $request) {
            $request = $this->getRequest();
        }

        $doctrine = $this->get('doctrine');

        /** @var EntityManager $em */
        $em = $doctrine->getManagerForClass($this->admin->getClass());

        if (!$em) {
            throw new \RuntimeException(sprintf('EntityManager for class %s does not exists.', $this->admin->getClass()));
        }

        /** @var NestedTreeRepository $repository */
        $repository = $em->getRepository($this->admin->getClass());

        $operation = $request->get('operation');

        switch ($operation) {
            case 'get_node':
                $nodeId = $request->get('id');
                if ($nodeId) {
                    $parentNode = $repository->find($nodeId);
                    $nodes = $repository->getChildren($parentNode, true);
                } else {
                    $nodes = $repository->getRootNodes();
                }

                $nodes = array_map(
                    function ($node) {

                        $hasChildren = true;

                        if (\is_object($node) && method_exists($node, 'getChildren')) {
                            $children = $node->getChildren();
                            if ($children instanceof Collection && $children->count() === 0) {
                                $hasChildren = false;
                            }
                        }

                        return [
                            'id' => $node->getId(),
                            'text' => (string) $node,
                            'children' => $hasChildren,
                        ];
                    },
                    $nodes
                );

                return new JsonResponse($nodes);
            case 'move_node':

                $this->admin->checkAccess('edit');

                $nodeId = $request->get('id');
                $parentNodeId = $request->get('parent_id');

                $parentNode = $repository->find($parentNodeId);
                $node = $repository->find($nodeId);
                $node->setParent($parentNode);

                $this->admin->getModelManager()->update($node);

                $siblings = $repository->getChildren($parentNode, true);
                $position = $request->get('position');
                $i = 0;

                foreach ($siblings as $sibling) {
                    if ($sibling->getId() === $node->getId()) {
                        break;
                    }

                    $i++;
                }

                $diff = $position - $i;

                if ($diff > 0) {
                    $repository->moveDown($node, $diff);
                } else {
                    $repository->moveUp($node, abs($diff));
                }

                return new JsonResponse(
                    [
                        'id' => $node->getId(),
                        'text' => $node->{'get'.ucfirst($this->admin->getTreeTextField())}(),
                    ]
                );
            case 'rename_node':

                $this->admin->checkAccess('edit');

                $nodeId = $request->get('id');
                $nodeText = $request->get('text');
                $node = $repository->find($nodeId);

                $node->{'set'.ucfirst($this->admin->getTreeTextField())}($nodeText);
                $this->admin->getModelManager()->update($node);

                return new JsonResponse(
                    [
                        'id' => $node->getId(),
                        'text' => $node->{'get'.ucfirst($this->admin->getTreeTextField())}(),
                    ]
                );
            case 'create_node':

                $this->admin->checkAccess('edit');

                $parentNodeId = $request->get('parent_id');
                $parentNode = $repository->find($parentNodeId);
                $nodeText = $request->get('text');
                $node = $this->admin->getNewInstance();
                $node->{'set'.ucfirst($this->admin->getTreeTextField())}($nodeText);
                $node->setParent($parentNode);
                $this->admin->getModelManager()->create($node);

                return new JsonResponse(
                    [
                        'id' => $node->getId(),
                        'text' => $node->{'get'.ucfirst($this->admin->getTreeTextField())}(),
                    ]
                );

            case 'delete_node':

                $this->admin->checkAccess('delete');

                $nodeId = $request->get('id');
                $node = $repository->find($nodeId);
                $this->admin->getModelManager()->delete($node);

                return new JsonResponse();
        }

        throw new BadRequestHttpException('Unknown action for tree');
    }
}
