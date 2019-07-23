<?php
/**
 * Copyright: © 2019 Pro Sales AG
 * Author: Aaron Schmied <aaron@pro-sales.ch>
 * Date: 2019-05-17
 * Time: 11:51
 */

namespace App\Behaviours;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

trait GeneratesSlug
{
    /**
     * Get the slug key.
     *
     * @return string
     */
    protected function getSlugKey()
    {
        return 'slug';
    }

    /**
     * Get the slug source.
     *
     * @return string
     */
    abstract protected function getSlugSource(): string;

    /**
     * Determines if the slug should be unique.
     *
     * @return bool
     */
    protected function slugShouldBeUnique(): bool
    {
        return false;
    }

    /**
     * Set the slug separator.
     *
     * @return string
     */
    protected function slugSeparator(): string
    {
        return '-';
    }

    /**
     * Hook into the creating event and generate a new UUID.
     *
     * @return void
     */
    protected static function bootGeneratesSlug()
    {
        static::creating(function (Model $model) {
            $model->makeSlug();
        });
    }

    /**
     * Sets the slug value.
     *
     * @return void
     */
    public function makeSlug()
    {
        $slug = Str::slug($this->getSlugSource(), $this->slugSeparator(), app()->getLocale());

        if ($this->slugShouldBeUnique()) {
            $rounds = 0;

            do {
                if ($rounds > 0) {
                    $source = implode(' ', [
                        $this->getSlugSource(),
                        Str::random($rounds),
                    ]);

                    $slug = Str::slug($source, $this->slugSeparator(), app()->getLocale());
                }

                $validator = Validator::make(compact('slug'), [
                    'slug' => ['required', "unique:{$this->getTable()},{$this->getSlugKey()}"]
                ]);

                $rounds++;
            } while ($validator->fails());
        }

        $this->setAttribute($this->getSlugKey(), $slug);
    }
}
