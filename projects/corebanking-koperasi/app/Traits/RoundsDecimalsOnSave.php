<?php

namespace App\Traits;

/**
 * RoundsDecimalsOnSave
 *
 * Automatically rounds all fields declared with 'decimal:X' or 'decimal:2' casts
 * to exactly 2 decimal places before the model is persisted to the database.
 * Apply this trait to any Eloquent model that holds financial / numeric data.
 */
trait RoundsDecimalsOnSave
{
    /**
     * Boot the trait — hook into the 'saving' model event.
     */
    public static function bootRoundsDecimalsOnSave(): void
    {
        static::saving(function ($model) {
            foreach ($model->getCasts() as $field => $castType) {
                // Handle decimal:N casts
                if (str_starts_with($castType, 'decimal')) {
                    if (isset($model->attributes[$field]) && $model->attributes[$field] !== null) {
                        $model->attributes[$field] = round((float) $model->attributes[$field], 2);
                    }
                }
                // Handle plain float / double casts
                if (in_array($castType, ['float', 'double', 'real'])) {
                    if (isset($model->attributes[$field]) && $model->attributes[$field] !== null) {
                        $model->attributes[$field] = round((float) $model->attributes[$field], 2);
                    }
                }
            }
        });
    }
}
