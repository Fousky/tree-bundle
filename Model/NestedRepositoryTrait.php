<?php

namespace Fousky\TreeBundle\Model;

use Gedmo\Tree\Entity\Repository\NestedTreeRepository;

/**
 * @author Lukáš Brzák <lukas.brzak@fousky.cz>
 */
trait NestedRepositoryTrait
{
    /**
     * @var NestedTreeRepository|null
     */
    protected $nestedRepository;

    /**
     * @return NestedTreeRepository|null
     */
    public function getNestedRepository()
    {
        return $this->nestedRepository;
    }

    /**
     * @return bool
     */
    public function hasNestedRepository(): bool
    {
        return $this->nestedRepository !== null;
    }

    /**
     * @param NestedTreeRepository $repository
     */
    public function injectNestedRepository(NestedTreeRepository $repository)
    {
        $this->nestedRepository = $repository;
    }


    /**
     * @return NestedRepositoryInjectableInterface[]
     */
    public function getPath(): array
    {
        if (!$this->hasNestedRepository()) {
            return [$this];
        }

        return $this->nestedRepository->getPath($this);
    }

    /**
     * @param string $glue
     * @return string
     */
    public function generatePath($glue = ' > '): string
    {
        if (!$this->hasNestedRepository()) {
            return (string) $this;
        }

        return implode($glue, $this->getPath());
    }

    /**
     * @param NestedRepositoryInjectableInterface $item
     *
     * @return string
     */
    public static function generatePathAsString(NestedRepositoryInjectableInterface $item): string
    {
        return $item->generatePath();
    }
}
