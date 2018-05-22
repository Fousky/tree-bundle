<?php

namespace Fousky\AppBundle\Entity\Menu;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Fousky\AppBundle\Model\Menu\Details\MenuDetails;
use Fousky\Traits\Active\ActivableTrait;
use Fousky\Traits\Id\IdTrait;
use Fousky\Traits\Timestampable\TimestampableTrait;
use Fousky\TreeBundle\Model\NestedRepositoryInjectableInterface;
use Fousky\TreeBundle\Model\NestedRepositoryTrait;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Routing\RouterInterface;

/**
 * @ORM\Entity(repositoryClass="Fousky\AppBundle\EntityRepository\Menu\MenuItemRepository")
 * @ORM\Table(name="fousky_menu")
 * @ORM\HasLifecycleCallbacks()
 *
 * @Gedmo\Tree(type="nested")
 *
 * @author Lukáš Brzák <lukas.brzak@aquadigital.cz>
 */
class MenuItem implements NestedRepositoryInjectableInterface, \Serializable
{
    use IdTrait;
    use ActivableTrait;
    use NestedRepositoryTrait;
    use TimestampableTrait;

    const TYPE_ROUTE = 'route';
    const TYPE_LINK = 'link';
    const TYPE_VOID = 'void';
    const TYPE_SEPARATOR = 'separator';

    const LINK_TARGET_SAME = 'same';
    const LINK_TARGET_NEW = 'new';

    public static $typeChoices = [
        'menu.type.route' => self::TYPE_ROUTE,
        'menu.type.link' => self::TYPE_LINK,
        'menu.type.void' => self::TYPE_VOID,
        'menu.type.separator' => self::TYPE_SEPARATOR,
    ];

    public static $linkTargetChoices = [
        'menu.link_target.same' => self::LINK_TARGET_SAME,
        'menu.link_target.new' => self::LINK_TARGET_NEW,
    ];

    /**
     * @var bool
     */
    protected $current = false;

    /**
     * @var null|MenuDetails
     */
    protected $menu;

    /**
     * @var string|null
     *
     * @ORM\Column(name="anchor", type="string", nullable=true)
     */
    protected $anchor;

    /**
     * @var integer
     *
     * @ORM\Column(name="tree_root", type="integer")
     * @Gedmo\TreeRoot()
     */
    protected $root = 1;

    /**
     * @var integer|null
     *
     * @ORM\Column(name="tree_left", type="integer")
     * @Gedmo\TreeLeft()
     */
    protected $left;

    /**
     * @var integer|null
     *
     * @ORM\Column(name="tree_right", type="integer")
     * @Gedmo\TreeRight()
     */
    protected $right;

    /**
     * @var integer|null
     *
     * @ORM\Column(name="tree_level", type="integer")
     * @Gedmo\TreeLevel()
     */
    protected $level;

    /**
     * @var MenuItem|null
     *
     * @ORM\ManyToOne(targetEntity="Fousky\AppBundle\Entity\Menu\MenuItem", inversedBy="children", cascade={"persist"})
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id", onDelete="CASCADE")
     * @Gedmo\TreeParent()
     */
    protected $parent;

    /**
     * @var MenuItem[]|ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Fousky\AppBundle\Entity\Menu\MenuItem", mappedBy="parent", cascade={"persist"}, orphanRemoval=true)
     * @ORM\OrderBy({"left" = "ASC"})
     */
    protected $children;

    /**
     * @var string|null
     * @ORM\Column(name="title", type="string", nullable=true)
     */
    protected $title;

    /**
     * @var string
     * @ORM\Column(name="type", type="string", nullable=false, options={"default":"route"})
     */
    protected $type = self::TYPE_ROUTE;

    /**
     * @var string|null
     * @ORM\Column(name="route", type="string", nullable=true)
     */
    protected $route;

    /**
     * @var array
     * @ORM\Column(name="route_parameters", type="json_array")
     */
    protected $routeParameters = [];

    /**
     * @var string|null
     * @ORM\Column(name="link", type="string", nullable=true)
     */
    protected $link;

    /**
     * @var string|null
     * @ORM\Column(name="link_target", type="string", nullable=true)
     */
    protected $linkTarget;

    /**
     * @var string|null
     */
    protected $uri;



    public function __construct()
    {
        $this->children = new ArrayCollection();
    }

    /**
     * @return string
     */
    public function __toString()
    {
        if (null === $this->id) {
            return 'Nová položka';
        }

        return (string) $this->getTitle();
    }

    /**
     * @ORM\PrePersist()
     */
    public function prePersist()
    {
        $this->active = false;
    }

    /**
     * @ORM\PrePersist()
     * @ORM\PreUpdate()
     */
    public function update()
    {
        if ($this->getParent() !== null) {
            $this->anchor = null;
        }
    }

    /**
     * @return int
     */
    public function getRoot(): int
    {
        return $this->root;
    }

    /**
     * @param int $root
     *
     * @return $this
     */
    public function setRoot($root)
    {
        $this->root = $root;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getLeft()
    {
        return $this->left;
    }

    /**
     * @param int|null $left
     *
     * @return $this
     */
    public function setLeft($left)
    {
        $this->left = $left;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getRight()
    {
        return $this->right;
    }

    /**
     * @param int|null $right
     *
     * @return $this
     */
    public function setRight($right)
    {
        $this->right = $right;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getLevel()
    {
        return $this->level;
    }

    /**
     * @param int|null $level
     *
     * @return $this
     */
    public function setLevel($level)
    {
        $this->level = $level;

        return $this;
    }

    /**
     * @return MenuItem|null
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * @param MenuItem|null $parent
     *
     * @return $this
     */
    public function setParent($parent)
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * @return ArrayCollection|MenuItem[]
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * @param MenuItem $item
     *
     * @return $this
     */
    public function addChild(MenuItem $item)
    {
        if (!$this->children->contains($item)) {
            $this->children->add($item);
            $item->setParent($this);
        }

        return $this;
    }

    /**
     * @param MenuItem $item
     *
     * @return $this
     */
    public function removeChild(MenuItem $item)
    {
        if ($this->children->contains($item)) {
            $this->children->removeElement($item);
            $item->setParent(null);
        }

        return $this;
    }

    /**
     * @param ArrayCollection|MenuItem[] $children
     *
     * @return $this
     */
    public function setChildren($children)
    {
        $this->children = $children;

        foreach ($children as $item) {
            $item->setParent($this);
        }

        return $this;
    }

    /**
     * @return null|string
     */
    public function getAnchor()
    {
        return $this->anchor;
    }

    /**
     * @param null|string $anchor
     *
     * @return $this
     */
    public function setAnchor($anchor)
    {
        $this->anchor = $anchor;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param null|string $title
     *
     * @return $this
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @return bool
     */
    public function isTypeLink(): bool
    {
        return $this->type === static::TYPE_LINK;
    }

    /**
     * @return bool
     */
    public function isTypeRoute(): bool
    {
        return $this->type === static::TYPE_ROUTE;
    }

    /**
     * @return bool
     */
    public function isTypeVoid(): bool
    {
        return $this->type === static::TYPE_VOID;
    }

    /**
     * @return bool
     */
    public function isTypeSeparator(): bool
    {
        return $this->type === static::TYPE_SEPARATOR;
    }

    /**
     * @param string $type
     *
     * @return $this
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getRoute()
    {
        return $this->route;
    }

    /**
     * @param null|string $route
     *
     * @return $this
     */
    public function setRoute($route)
    {
        $this->route = $route;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getLink()
    {
        return $this->link;
    }

    /**
     * @param null|string $link
     *
     * @return $this
     */
    public function setLink($link)
    {
        $this->link = $link;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getLinkTarget()
    {
        return $this->linkTarget;
    }

    /**
     * @param null|string $linkTarget
     *
     * @return $this
     */
    public function setLinkTarget($linkTarget)
    {
        $this->linkTarget = $linkTarget;

        return $this;
    }

    /**
     * @return array
     */
    public function getRouteParameters(): array
    {
        return $this->routeParameters;
    }

    /**
     * @param array $routeParameters
     *
     * @return $this
     */
    public function setRouteParameters($routeParameters)
    {
        $this->routeParameters = $routeParameters;

        return $this;
    }

    /**
     * @return bool
     */
    public function isCurrent(): bool
    {
        return $this->current;
    }

    public function markAsCurrent()
    {
        $this->current = true;

        if ($this->getParent() !== null) {
            $this->parent->markAsCurrent();
        }
    }

    /**
     * @return MenuDetails|null
     */
    public function getMenu()
    {
        return $this->menu;
    }

    /**
     * @param MenuDetails|null $menu
     *
     * @return $this
     */
    public function setMenu($menu)
    {
        $this->menu = $menu;

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
            $this->id,
            $this->active,
            $this->createdAt,
            $this->updatedAt,
            $this->current,
            $this->menu,
            $this->anchor,
            $this->root,
            $this->left,
            $this->right,
            $this->level,
            $this->parent,
            $this->children,
            $this->title,
            $this->type,
            $this->route,
            $this->routeParameters,
            $this->link,
            $this->linkTarget,
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
            $this->id,
            $this->active,
            $this->createdAt,
            $this->updatedAt,
            $this->current,
            $this->menu,
            $this->anchor,
            $this->root,
            $this->left,
            $this->right,
            $this->level,
            $this->parent,
            $this->children,
            $this->title,
            $this->type,
            $this->route,
            $this->routeParameters,
            $this->link,
            $this->linkTarget,
        ) = unserialize($serialized);
    }

    /**
     * @return null|string
     */
    public function getUri()
    {
        return $this->uri;
    }

    /**
     * @param null|string $uri
     *
     * @return $this
     */
    public function setUri($uri)
    {
        $this->uri = $uri;

        return $this;
    }

    /**
     * @param RouterInterface $router
     */
    public function resolveUri(RouterInterface $router)
    {
        if ($this->type === static::TYPE_ROUTE && $this->route !== null) {
            $this->uri = $router->generate($this->route, $this->routeParameters);
        }

        foreach ($this->children as $child) {
            $child->resolveUri($router);
        }
    }
}
