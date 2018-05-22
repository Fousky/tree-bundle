<?php

namespace Fousky\TreeBundle\EntityListener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Fousky\TreeBundle\Model\NestedRepositoryInjectableInterface;
use Gedmo\Tree\Entity\Repository\NestedTreeRepository;

/**
 * @author Lukáš Brzák <lukas.brzak@fousky.cz>
 */
class NestedRepositoryInjector implements EventSubscriber
{
    /**
     * @return array
     */
    public function getSubscribedEvents(): array
    {
        return [
            'postLoad',
        ];
    }

    public function postLoad(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();

        if (!$entity instanceof NestedRepositoryInjectableInterface) {
            return;
        }

        $repository = $args->getEntityManager()->getRepository(get_class($entity));

        if (!$repository instanceof NestedTreeRepository) {
            throw new \RuntimeException(sprintf(
                'Cannot inject repository of instance %s into %s',
                get_class($repository),
                get_class($entity)
            ));
        }

        $entity->injectNestedRepository($repository);
    }
}
