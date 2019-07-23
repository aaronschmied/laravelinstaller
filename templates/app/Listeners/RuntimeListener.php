<?php
/**
 * Copyright: © 2019 Pro Sales AG
 * Author: Aaron Schmied <aaron@pro-sales.ch>
 * Date: 2019-05-17
 * Time: 12:44
 */

namespace App\Listeners;

use App\Events\RuntimeEvent;

abstract class RuntimeListener
{
    /**
     * Get the events this listener should listen for.
     *
     * @return array
     */
    abstract public static function events(): array;

    /**
     * Handle an event.
     *
     * @param RuntimeEvent $event
     */
    abstract public function handle(RuntimeEvent $event): void;
}
