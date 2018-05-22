<?php

namespace Fousky\AppBundle\EventListener\Menu;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Fousky\AppBundle\Entity\Menu\MenuItem;
use Fousky\AppBundle\Model\Menu\Cache\MenuCacheInterface;
use Fousky\AppBundle\Model\Menu\MenuItemFactory;

/**
 * @author Lukáš Brzák <lukas.brzak@aquadigital.cz>
 */
class MenuCacheListener implements EventSubscriber
{
    /** @var MenuCacheInterface $cache */
    protected $cache;

    /** @var MenuItemFactory $factory */
    protected $factory;

    public function __construct(MenuCacheInterface $cache, MenuItemFactory $factory)
    {
        $this->cache = $cache;
        $this->factory = $factory;
    }

    /**
     * Returns an array of events this subscriber wants to listen to.
     *
     * @return array
     */
    public function getSubscribedEvents()
    {
        return [
            'preUpdate',
            'postPersist',
            'postUpdate',
            'postRemove',
        ];
    }

    public function preUpdate(PreUpdateEventArgs $args)
    {
        $entity = $args->getEntity();

        if (!$entity instanceof MenuItem) {
            return;
        }

        if ($args->hasChangedField('anchor')) {
            if ($args->getOldValue('anchor') !== null) {
                $this->cache->abandon($args->getOldValue('anchor'));
            }

            $this->cache->abandon($args->getNewValue('anchor'));

        } else {
            $this->resolveRootCache($args->getEntityManager(), $entity);
        }
    }

    public function postRemove(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();

        if (!$entity instanceof MenuItem) {
            return;
        }

        if ($entity->getAnchor() !== null) {
            $this->cache->abandon($entity->getAnchor());
        } else {
            $this->resolveRootCache($args->getEntityManager(), $entity);
        }
    }

    public function postPersist(LifecycleEventArgs $args)
    {
        $this->resolve($args);
    }

    public function postUpdate(LifecycleEventArgs $args)
    {
        $this->resolve($args);
    }

    public function resolve(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();

        if (!$entity instanceof MenuItem) {
            return;
        }

        $this->resolveRootCache($args->getEntityManager(), $entity);
    }

    /**
     * @param EntityManager $manager
     * @param MenuItem $item
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\TransactionRequiredException
     */
    protected function resolveRootCache(EntityManager $manager, MenuItem $item)
    {
        $root = $manager->find(MenuItem::class, (int) $item->getRoot());

        if ($root instanceof MenuItem && $root->getAnchor() !== null) {
            $this->cache->abandon($root->getAnchor());
        } else {
            $this->cache->abandonAll();
        }
    }
}
