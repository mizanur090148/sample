<?php

namespace App\Traits;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Session, Auth;

trait FactoryIdTrait
{
    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope('factoryId', function (Builder $builder) {
            $builder->where('factory_id', Auth::user()->factory_id);
        });

        static::saving(function ($model) {
             $model->factory_id = Auth::user()->factory_id;            
            if (in_array('created_by', $model->getFillable())) {
                $model->created_by = Auth::user()->id;
            }            
        });

        static::deleting(function ($model) {
            if (in_array('deleted_by', $model->getFillable())) {
                $model->deleted_by = Auth::user()->id;
                $model->save();
            }
        });

        static::updating(function ($model) {
            if (in_array('updated_by', $model->getFillable())) {
                $model->updated_by = Auth::user()->id;
            }
        });
    }
}
