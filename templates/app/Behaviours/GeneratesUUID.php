<?php
/**
 * Copyright: © 2019 Pro Sales AG
 * Author: Aaron Schmied <aaron@pro-sales.ch>
 * Date: 2019-05-16
 * Time: 19:07
 */

namespace App\Behaviours;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

trait GeneratesUUID
{
    /**
     * Hook into the creating event and generate a new UUID.
     *
     * @return void
     */
    protected static function bootGeneratesUUID()
    {
        static::creating(function (Model $model) {
            $model->setAttribute($model->getKeyName(), (string) Str::uuid());
        });
    }

    /**
     * Determine if the primary key is incrementing.
     *
     * @return bool
     */
    public function getIncrementing()
    {
        return false;
    }

    /**
     * Determines the primary key type.
     *
     * @return string
     */
    public function getKeyType()
    {
        return 'string';
    }
}
