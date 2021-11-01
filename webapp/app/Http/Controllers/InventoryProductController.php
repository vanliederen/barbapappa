<?php

namespace App\Http\Controllers;

use App\Models\InventoryItemChange;
use Illuminate\Http\Response;

class InventoryProductController extends Controller {

    const PAGINATE_ITEMS = 50;

    /**
     * Show an inventory product.
     *
     * @return Response
     */
    public function show($communityId, $economyId, $inventoryId, $productId) {
        // Get the community, find the inventory
        $community = \Request::get('community');
        $economy = $community->economies()->findOrFail($economyId);
        $inventory = $economy->inventories()->findOrFail($inventoryId);
        $product = $economy->products()->findOrFail($productId);
        $item = $inventory->getItem($product);

        // Find last balance event
        $lastBalance = $item != null ? $item
            ->changes()
            ->type(InventoryItemChange::TYPE_BALANCE)
            ->first() : null;

        // Build list of quantities by inventory
        $quantities = $economy
            ->inventories
            ->map(function($i) use($product) {
                $item = $i->getItem($product);
                return [
                    'inventory' => $i,
                    'quantity' => $item != null ? $item->quantity : 0,
                ];
            })
            ->sortByDesc('quantity');

        return view('community.economy.inventory.product.show')
            ->with('economy', $economy)
            ->with('inventory', $inventory)
            ->with('product', $product)
            ->with('item', $item)
            ->with('lastBalance', $lastBalance)
            ->with('quantities', $quantities)
            ->with('changes', $item != null ? $item->changes()->limit(10)->get() : collect());
    }

    /**
     * List product changes.
     *
     * @return Response
     */
    public function changes($communityId, $economyId, $inventoryId, $productId) {
        // Get the community, find the inventory
        $community = \Request::get('community');
        $economy = $community->economies()->findOrFail($economyId);
        $inventory = $economy->inventories()->findOrFail($inventoryId);
        $product = $economy->products()->findOrFail($productId);
        $item = $inventory->getItem($product);

        return view('community.economy.inventory.product.changes')
            ->with('economy', $economy)
            ->with('inventory', $inventory)
            ->with('product', $product)
            ->with('item', $item)
            ->with('changes', $item != null ? $item->changes()->paginate(self::PAGINATE_ITEMS) : collect());
    }

    /**
     * Show a product change.
     *
     * @return Response
     */
    public function change($communityId, $economyId, $inventoryId, $productId, $changeId) {
        // Get the community, find the inventory
        $community = \Request::get('community');
        $economy = $community->economies()->findOrFail($economyId);
        $inventory = $economy->inventories()->findOrFail($inventoryId);
        $product = $economy->products()->findOrFail($productId);
        $item = $inventory->getItem($product);
        $change = $item->changes()->findOrFail($changeId);

        return view('community.economy.inventory.product.change')
            ->with('economy', $economy)
            ->with('inventory', $inventory)
            ->with('product', $product)
            ->with('item', $item)
            ->with('change', $change);
    }

    /**
     * The permission required for viewing.
     * @return PermsConfig The permission configuration.
     */
    public static function permsView() {
        return InventoryController::permsView();
    }

    /**
     * The permission required for managing such as editing and deleting.
     * @return PermsConfig The permission configuration.
     */
    public static function permsManage() {
        return InventoryController::permsManage();
    }
}
