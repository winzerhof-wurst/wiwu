<?php

/**
 * Winzerhof Wurst – www.winzerhof-wurst.at
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Christoph Wurst <christoph@winzerhof-wurst.at>
 * @copyright Christoph Wurst 2016-2017
 */

namespace App\Shop;

use App\Customer;
use App\Events\OrderEvent;
use App\Item;
use App\Order;
use App\Tidbit;
use App\Wine;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\Facades\DB;

class Shop {

	/** @var Dispatcher */
	private $eventDispatcher;

	/**
	 * @param Dispatcher $eventDispatcher
	 */
	public function __construct(Dispatcher $eventDispatcher) {
		$this->eventDispatcher = $eventDispatcher;
	}

	/**
	 * @param array $customerData
	 * @param array $wines
	 * @param array $tidbits
	 * @param string $comment
	 */
	public function saveOrder($customerData, array $wines, array $tidbits, $comment) {
		$order = null;

		DB::transaction(function () use (&$order, $customerData, $wines, $tidbits, $comment) {
			$customer = Customer::create($customerData);
			$order = new Order();
			$order->comment = $comment;
			$customer->orders()->save($order);

			foreach ($wines as $wineData) {
				if (!isset($wineData['quantity']) ||
					0 === (int) $wineData['quantity']) {
					continue;
				}
				$wine = Wine::find($wineData['id']);

				$item = new Item();
				$item->name = $wine->name . ' - ' . $wine->description . ' - ' . $wine->year;
				$item->price = $wine->price;
				$item->tax_rate = $wine->tax_rate;
				$item->qty = $wineData['quantity'];

				$order->items()->save($item);
				$wine->items()->save($item);
			}
			foreach ($tidbits as $tidbitData) {
				if (!isset($tidbitData['quantity']) ||
					0 === (int) $tidbitData['quantity']) {
					continue;
				}
				$tidbit = Tidbit::find($tidbitData['id']);

				$item = new Item();
				$item->name = $tidbit->name;
				$item->price = $tidbit->price;
				$item->tax_rate = $tidbit->tax_rate;
				$item->qty = $tidbitData['quantity'];

				$order->items()->save($item);
				$tidbit->items()->save($item);
			}
			$this->eventDispatcher->fire(new OrderEvent($customerData, $order->items));
		});

		return $order;
	}

}
