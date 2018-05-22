<?php

namespace Fousky\AppBundle\Model\Menu\Cache;

use Fousky\AppBundle\Model\Menu\Details\MenuDetails;

/**
 * @author Lukáš Brzák <lukas.brzak@aquadigital.cz>
 */
interface MenuCacheInterface
{
    /**
     * Save menu to storage.
     *
     * @param string $anchor
     * @param MenuDetails $menu
     */
    public function save(string $anchor, MenuDetails $menu);

    /**
     * Get Menu by anchor.
     *
     * @param string $anchor
     *
     * @return null|MenuDetails
     */
    public function get(string $anchor);

    /**
     * Has storage Menu?
     *
     * @param string $anchor
     *
     * @return bool
     */
    public function has(string $anchor): bool;

    /**
     * Abandon or remove Menu cache.
     *
     * @param string $anchor
     */
    public function abandon(string $anchor);

    /**
     * Abandon or remove all Menus cache.
     */
    public function abandonAll();
}
