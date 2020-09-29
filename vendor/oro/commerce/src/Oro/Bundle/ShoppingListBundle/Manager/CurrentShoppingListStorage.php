<?php

namespace Oro\Bundle\ShoppingListBundle\Manager;

use Doctrine\Common\Cache\Cache;

/**
 * Provides a storage for the current shopping list identifier.
 */
class CurrentShoppingListStorage
{
    /** @var Cache */
    private $cache;

    /**
     * @param Cache $cache
     */
    public function __construct(Cache $cache)
    {
        $this->cache = $cache;
    }

    /**
     * @param int $customerUserId
     *
     * @return int|null
     */
    public function get(int $customerUserId): ?int
    {
        $shoppingListId = $this->cache->fetch($customerUserId);
        if (false === $shoppingListId) {
            return null;
        }

        return $shoppingListId;
    }

    /**
     * @param int      $customerUserId
     * @param int|null $shoppingListId
     */
    public function set(int $customerUserId, ?int $shoppingListId): void
    {
        if (null === $shoppingListId) {
            $this->cache->delete($customerUserId);
        } else {
            $this->cache->save($customerUserId, $shoppingListId);
        }
    }
}
