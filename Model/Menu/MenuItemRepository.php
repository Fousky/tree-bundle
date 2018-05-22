<?php

namespace Fousky\AppBundle\EntityRepository\Menu;

use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Fousky\AppBundle\Entity\Menu\MenuItem;
use Fousky\AppBundle\Model\Menu\Exception\MenuMultipleAnchorException;
use Fousky\AppBundle\Model\Menu\Exception\MenuNotExistsException;
use Gedmo\Tree\Entity\Repository\NestedTreeRepository;

/**
 * @author Lukáš Brzák <lukas.brzak@aquadigital.cz>
 */
class MenuItemRepository extends NestedTreeRepository
{
    /**
     * @param string $anchor
     * @param int|bool $maxLevel
     *
     * @return MenuItem[]
     *
     * @throws MenuMultipleAnchorException
     * @throws MenuNotExistsException
     */
    public function findByAnchor(string $anchor, $maxLevel): array
    {
        $rootNode = $this->getRootByAnchor($anchor);

        $builder = $this->createQueryBuilder('menu')
            ->andWhere('menu.left > :lft')
            ->andWhere('menu.right < :rgt')
            ->andWhere('menu.level > :lvl')
            ->andWhere('menu.active = 1')
            ->setParameter('lft', $rootNode->getLeft())
            ->setParameter('rgt', $rootNode->getRight())
            ->setParameter('lvl', $rootNode->getLevel())
            ->addOrderBy('menu.level', 'ASC')
            ->addOrderBy('menu.left', 'ASC')
        ;

        if ($maxLevel !== false && is_int($maxLevel)) {
            $builder
                ->andWhere('menu.level <= :max')
                ->setParameter('max', $maxLevel)
            ;
        }

        return $builder
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @param string $anchor
     *
     * @return MenuItem
     *
     * @throws MenuMultipleAnchorException
     * @throws MenuNotExistsException
     */
    public function getRootByAnchor(string $anchor): MenuItem
    {
        try {
            return $this->createQueryBuilder('menu')
                ->andWhere('menu.active = 1')
                ->andWhere('menu.anchor = :anchor')
                ->setParameter('anchor', $anchor)
                ->addOrderBy('menu.level', 'ASC')
                ->addOrderBy('menu.left', 'ASC')
                ->getQuery()
                ->getSingleResult()
            ;
        } catch (NonUniqueResultException $e) {
            throw new MenuMultipleAnchorException(sprintf(
                'ERR_MENU___MULTIPLE_ANCHOR_EXISTS: [anchor]: %s, [message]: %s',
                $anchor,
                $e->getMessage()
            ));
        } catch (NoResultException $e) {
            throw new MenuNotExistsException(sprintf(
                'ERR_MENU___ANCHOR_NOT_EXISTS: [anchor]: %s, [message]: %s',
                $anchor,
                $e->getMessage()
            ));
        }
    }
}
