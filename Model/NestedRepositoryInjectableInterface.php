<?php

namespace Fousky\TreeBundle\Model;

use Gedmo\Tree\Entity\Repository\NestedTreeRepository;

/**
 * @author Lukáš Brzák <lukas.brzak@fousky.cz>
 */
interface NestedRepositoryInjectableInterface
{
    /**
     * @return NestedTreeRepository|null
     */
    public function getNestedRepository();

    /**
     * @return bool
     */
    public function hasNestedRepository(): bool;

    /**
     * @param NestedTreeRepository $repository
     */
    public function injectNestedRepository(NestedTreeRepository $repository);

    /**
     * @return NestedRepositoryInjectableInterface[]
     */
    public function getPath(): array;

    /**
     * @param string $glue
     * @return string
     */
    public function generatePath($glue = ' > '): string;

    /**
     * @param NestedRepositoryInjectableInterface $item
     *
     * @return string
     */
    public static function generatePathAsString(NestedRepositoryInjectableInterface $item): string;
}
