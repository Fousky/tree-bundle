<?php

namespace Fousky\AppBundle\Model\Menu\Cache;

use Fousky\AppBundle\Model\Menu\Details\MenuDetails;
use Nette\Caching\Cache;
use Nette\Caching\Storages\FileStorage;
use Nette\Caching\Storages\SQLiteJournal;
use Symfony\Component\Filesystem\Filesystem;

/**
 * @author Lukáš Brzák <lukas.brzak@aquadigital.cz>
 */
class MenuNetteCache implements MenuCacheInterface
{
    const MENU_CACHE_TAG = '_fousky/menu';

    /** @var Cache $cache */
    protected $cache;

    /** @var array|MenuDetails[] $results */
    protected $results = [];

    /**
     * @param string $cacheDir
     * @param string $journalFile
     *
     * @throws \InvalidArgumentException
     */
    public function __construct(string $cacheDir, string $journalFile)
    {
        $this->assertDir($cacheDir);

        $this->cache = new Cache(
            new FileStorage(
                $cacheDir,
                new SQLiteJournal($journalFile)
            )
        );
    }

    /**
     * Save menu to storage.
     *
     * @param string $anchor
     * @param MenuDetails $menu
     *
     * @throws \Nette\InvalidArgumentException
     */
    public function save(string $anchor, MenuDetails $menu)
    {
        $this->cache->save($this->createMenuKey($anchor), $menu, [
            Cache::TAGS => $this->createCacheTags($anchor),
        ]);
    }

    /**
     * Get Menu by anchor.
     *
     * @param string $anchor
     *
     * @return null|MenuDetails
     */
    public function get(string $anchor)
    {
        if (array_key_exists($anchor, $this->results)) {
            return $this->results[$anchor];
        }

        return $this->results[$anchor] = $this->cache->load($this->createMenuKey($anchor));
    }

    /**
     * Has storage Menu?
     *
     * @param string $anchor
     *
     * @return bool
     */
    public function has(string $anchor): bool
    {
        return $this->get($anchor) instanceof MenuDetails;
    }

    /**
     * Abandon or remove Menu cache.
     *
     * @param string $anchor
     */
    public function abandon(string $anchor)
    {
        $this->cache->remove($this->createMenuKey($anchor));
    }

    public function abandonAll()
    {
        $this->cache->clean([
            Cache::TAGS => [
                static::MENU_CACHE_TAG,
            ],
        ]);
    }

    /**
     * @param string $anchor
     *
     * @return string
     */
    protected function createMenuKey(string $anchor): string
    {
        return sprintf('%s/%s', static::MENU_CACHE_TAG, $anchor);
    }

    /**
     * @param string $anchor
     *
     * @return array
     */
    protected function createCacheTags(string $anchor): array
    {
        return [
            static::MENU_CACHE_TAG,
            sprintf('%s/%s', static::MENU_CACHE_TAG, $anchor),
        ];
    }

    /**
     * @param string $dir
     *
     * @throws \InvalidArgumentException
     */
    protected function assertDir(string $dir)
    {
        try {
            (new Filesystem())->mkdir($dir);
        } catch (\Exception $e) {
            throw new \InvalidArgumentException(sprintf(
                'ERR_MENU_NETTE_CACHE___CANNOT_CREATE_DIRECTORY [dir]: %s [message]: %s [stack]: %s',
                $dir,
                $e->getMessage(),
                $e->getTraceAsString()
            ));
        }
    }
}
