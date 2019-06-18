<?php
/**
 * Copyright: © 2019 Pro Sales AG
 * Author: Aaron Schmied <aaron@pro-sales.ch>
 * Date: 2019-05-17
 * Time: 12:40
 */

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

abstract class RuntimeEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
}

