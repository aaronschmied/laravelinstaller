<?php
/**
 * Copyright: © 2019 Pro Sales AG
 * Author: Aaron Schmied <aaron@pro-sales.ch>
 * Date: 2019-05-16
 * Time: 19:04
 */

namespace App\Foundation;

use App\Behaviours\GeneratesUUID;
use Illuminate\Database\Eloquent\Model as BaseModel;

abstract class Model extends BaseModel
{
    use GeneratesUUID;
}
