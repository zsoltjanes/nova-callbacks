<?php

namespace Ganyicz\NovaCallbacks;

use Illuminate\Database\Eloquent\Model;
use Laravel\Nova\Http\Requests\NovaRequest;

/**
 * @method static beforeSave(NovaRequest $request, Model $model): void
 * @method static afterSave(NovaRequest $request, Model $model): void
 * @method static beforeCreate(NovaRequest $request, Model $model): void
 * @method static afterCreate(NovaRequest $request, Model $model): void
 * @method static beforeUpdate(NovaRequest $request, Model $model): void
 * @method static afterUpdate(NovaRequest $request, Model $model): void
 */
trait HasCallbacks
{
    public static function fill(NovaRequest $request, $model): array
    {
        static::executeCallbacks('beforeSave', $request, $model);
        static::executeCallbacks('beforeCreate', $request, $model);
        static::registerSavedCallbacks('afterSave', $request, $model);
        static::registerCreatedCallbacks('afterCreate', $request, $model);

        return static::fillFields(
            $request, $model,
            (new static())
                ->creationFields($request)
                ->applyDependsOn($request)
                ->withoutReadonly($request)
                ->withoutUnfillable()
        );
    }

    public static function fillForUpdate(NovaRequest $request, $model): array
    {
        static::executeCallbacks('beforeSave', $request, $model);
        static::executeCallbacks('beforeUpdate', $request, $model);
        static::registerSavedCallbacks('afterSave', $request, $model);
        static::registerSavedCallbacks('afterUpdate', $request, $model);

        return static::fillFields(
            $request, $model,
            (new static())
                ->updateFields($request)
                ->applyDependsOn($request)
                ->withoutReadonly($request)
                ->withoutUnfillable()
        );
    }

    public static function fillPivot(NovaRequest $request, $model, $pivot): array
    {
        static::executeCallbacks('beforeSave', $request, $pivot);
        static::executeCallbacks('beforeCreate', $request, $pivot);
        static::registerSavedCallbacks('afterSave', $request, $pivot);
        static::registerCreatedCallbacks('afterCreate', $request, $pivot);

        return static::fillFields(
            $request, $pivot,
            (new static())
                ->creationPivotFields($request, $request->relatedResource)
                ->applyDependsOn($request)
                ->withoutReadonly($request)
                ->withoutUnfillable()
        );
    }

    public static function fillPivotForUpdate(NovaRequest $request, $model, $pivot): array
    {
        static::executeCallbacks('beforeSave', $request, $pivot);
        static::executeCallbacks('beforeUpdate', $request, $pivot);
        static::registerSavedCallbacks('afterSave', $request, $pivot);
        static::registerSavedCallbacks('afterUpdate', $request, $pivot);

        return static::fillFields(
            $request, $pivot,
            (new static())
                ->updatePivotFields($request, $request->relatedResource)
                ->applyDependsOn($request)
                ->withoutReadonly($request)
                ->withoutUnfillable()
        );
    }

    protected static function executeCallbacks(string $method, NovaRequest $request, $model): void
    {
        if (method_exists(static::class, $method)) {
            static::{$method}($request, $model);
        }
    }

    protected static function registerSavedCallbacks(string $method, NovaRequest $request, $model): void
    {
        if (method_exists(static::class, $method)) {
            $model::saved(function ($model) use ($request, $method) {
                static::{$method}($request, $model);
            });
        }
    }

    protected static function registerCreatedCallbacks(string $method, NovaRequest $request, $model): void
    {
        if (method_exists(static::class, $method)) {
            $model::created(function ($model) use ($request, $method) {
                static::{$method}($request, $model);
            });
        }
    }
}
