<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Session, Auth;

trait FactoryIdTrait
{

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope('factoryId', function (Builder $builder) {
            /*if (getRole() != 'super-admin') {
                $builder->where('factory_id', \Auth::user()->factory_id);
            }*/
            $table = $builder->getModel()->getTable();
            $builder->where(($table ? $table.'.' : '').'factory_id', factoryId());
                
            /*$builder->where('factory_id', Session::get('factoryId'));*/
        });

        static::saving(function ($model) {
            /* $model->factory_id = \Auth::user()->factory_id; */
            $model->factory_id = Session::get('factoryId');
            if (in_array('created_by', $model->getFillable())) {
                $model->created_by = Auth::user()->id;
            }
            if (Carbon::now()->isFriday()) {
                $model->created_at = Carbon::now()->subDay();
                $model->updated_at = Carbon::now()->subDay();
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
