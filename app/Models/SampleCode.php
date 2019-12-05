<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\FactoryIdTrait;

class SampleCode extends Model
{
    use SoftDeletes, FactoryIdTrait;
	
    protected $fillable = [
    	'sample_code_id',
    	'buyer_id',
    	'color_id',
    	'size_id',
    	'status', // 1 = sent, 2 = received 
    	'created_by',
    	'updated_by',
    	'deleted_by',
    	'factory_id'
    ];

    protected $dates = [
    	'deleted_at'
    ];

    public function sample_codes()
    {
    	return $this->hasMany(self::class);
    }

    public function sample_code_parent()
    {
    	return $this->belogsTo(self::class, 'sample_code_id')->withDefault();
    }

    public function buyer()
    {
    	return $this->belongsTo(Buyer::class)->withDefault();
    }

    public function color()
    {
    	return $this->belongsTo(Color::class)->withDefault();
    }

    public function size()
    {
    	return $this->belongsTo(Size::class)->withDefault();
    }
}
