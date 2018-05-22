<?php

namespace Fousky\AppBundle\Controller\Frontend\Menu;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author Lukáš Brzák <lukas.brzak@aquadigital.cz>
 */
class SimpleMenuController extends Controller
{
    /**
     * @Route(name="_menu_test", path="/menu/{anchor}")
     *
     * @param string $anchor
     *
     * @return Response
     * @throws \Fousky\AppBundle\Model\Menu\Exception\MenuMultipleAnchorException
     * @throws \Fousky\AppBundle\Model\Menu\Exception\MenuNotExistsException
     * @throws \Symfony\Component\OptionsResolver\Exception\ExceptionInterface
     */
    public function renderAction(string $anchor): Response
    {
        return $this->render('FouskyAppBundle:Frontend/Navigation:_main_menu_renderer.html.twig', [
            'menu' => $this
                ->get('fousky.menu.factory')
                ->buildMenu($anchor),
        ]);
    }
}
