<?php

namespace Fousky\AppBundle\Model\Menu;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use Fousky\AppBundle\Entity\Menu\MenuItem;
use Fousky\AppBundle\Model\Menu\Cache\MenuCacheInterface;
use Fousky\AppBundle\Model\Menu\Details\MenuDetails;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RouterInterface;

/**
 * @author Lukáš Brzák <lukas.brzak@aquadigital.cz>
 */
class MenuItemFactory
{
    /** @var EntityManager $manager */
    protected $manager;

    /** @var null|MenuCacheInterface $cache */
    protected $cache;

    /** @var null|Request $request */
    protected $request;

    /** @var string $currentRoute */
    protected $currentRoute;

    protected $router;

    /**
     * @param EntityManager $manager
     * @param RequestStack $stack
     * @param RouterInterface $router
     * @param null|MenuCacheInterface $cache
     */
    public function __construct(
        EntityManager $manager,
        RequestStack $stack,
        RouterInterface $router,
        MenuCacheInterface $cache = null
    ) {
        $this->manager = $manager;
        $this->request = $stack->getMasterRequest();
        $this->router = $router;
        $this->cache = $cache;

        if (null !== $this->request) {
            $this->currentRoute = $this->request->attributes->get('_route');
        }
    }

    /**
     * @param string $anchor
     * @param bool $resolveCurrent
     *
     * @return MenuDetails
     *
     * @throws \Fousky\AppBundle\Model\Menu\Exception\MenuNotExistsException
     * @throws \Fousky\AppBundle\Model\Menu\Exception\MenuMultipleAnchorException
     * @throws \Symfony\Component\OptionsResolver\Exception\ExceptionInterface
     */
    public function buildMenu(string $anchor, $resolveCurrent = true): MenuDetails
    {
        /**
         * Kešování menu nefunguje, takže je lepší to nechat bez něho.
         */
//        if ($this->cache->has($anchor)) {
//            $menu = $this->cache->get($anchor);
//        } else {

            $menu = new MenuDetails();
            $menu->setChildren(
                $items = $this->findMenu($anchor, $level = 1)
            );

//            $this->cache->save($anchor, $menu);
//        }

        if (true === $resolveCurrent) {
            $this->resolveCurrent($menu);
        }

        $this->resolveUri($menu);

        return $menu;
    }

    protected function resolveCurrent(MenuDetails $menu)
    {
        foreach ($menu->getChildren() as $child) {
            $this->resolveCurrentMenuItem($child);
        }
    }

    protected function resolveCurrentMenuItem(MenuItem $item)
    {
        foreach ($item->getChildren() as $child) {
            $this->resolveCurrentMenuItem($child);
        }

        if (null !== $item->getRoute() &&
            $item->getType() === MenuItem::TYPE_ROUTE &&
            $item->getRoute() === $this->currentRoute &&
            !empty($this->currentRoute)
        ) {
            $item->markAsCurrent();
        }
    }

    /**
     * @param MenuDetails $menu
     */
    protected function resolveUri(MenuDetails $menu)
    {
        $menu->resolveUri($this->router);
    }

    /**
     * @param string $anchor
     * @param bool $maxLevel
     *
     * @return array|MenuItem[]|ArrayCollection
     * @throws Exception\MenuMultipleAnchorException
     * @throws Exception\MenuNotExistsException
     */
    protected function findMenu(string $anchor, $maxLevel = false): array
    {
        return $this
            ->manager
            ->getRepository('FouskyAppBundle:Menu\MenuItem')
            ->findByAnchor($anchor, $maxLevel)
        ;
    }
}
