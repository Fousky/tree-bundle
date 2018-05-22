<?php

namespace Fousky\AppBundle\Model\Menu\Details;

use Doctrine\Common\Collections\ArrayCollection;
use Fousky\AppBundle\Entity\Menu\MenuItem;
use Symfony\Component\Routing\RouterInterface;

/**
 * @author Lukáš Brzák <lukas.brzak@aquadigital.cz>
 */
class MenuDetails implements \Serializable
{
    /**
     * @var bool
     */
    protected $active = false;

    /**
     * @var array|MenuItem[]|ArrayCollection
     */
    protected $children;

    /**
     * @var array|MenuItem[]
     */
    protected $activeItems;

    /**
     * @var array|MenuItem[]
     */
    protected $disabledItems;


    public function __construct()
    {
        $this->children = new ArrayCollection();
    }

    /**
     * @return array|ArrayCollection|MenuItem[]
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * @return bool
     */
    public function hasChildren(): bool
    {
        return $this->children->count() > 0;
    }

    /**
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->hasChildren() && $this->active === true;
    }

    /**
     * @param MenuItem $item
     */
    public function addActiveItem(MenuItem $item)
    {
        $this->activeItems[] = $item;
    }

    /**
     * @param array|ArrayCollection|MenuItem[] $children
     *
     * @return $this
     */
    public function setChildren($children)
    {
        $result = new ArrayCollection();

        foreach ($children as $item) {
            if ($item->isActive()) {
                $item->setMenu($this);
                $result->add($item);
            } else {
                $this->disabledItems[] = $item;
            }
        }

        $this->children = $result;

        return $this;
    }

    /**
     * String representation of object
     *
     * @link http://php.net/manual/en/serializable.serialize.php
     * @return string the string representation of the object or null
     * @since 5.1.0
     */
    public function serialize(): string
    {
        return serialize([
            $this->active,
            $this->children,
            $this->activeItems,
        ]);
    }

    /**
     * Constructs the object
     *
     * @link http://php.net/manual/en/serializable.unserialize.php
     *
     * @param string $serialized <p>
     * The string representation of the object.
     * </p>
     *
     * @return void
     * @since 5.1.0
     */
    public function unserialize($serialized)
    {
        list(
            $this->active,
            $this->children,
            $this->activeItems,
        ) = unserialize($serialized);
    }

    public function resolveUri(RouterInterface $router)
    {
        foreach ($this->children as $child) {
            $child->resolveUri($router);
        }
    }
}
