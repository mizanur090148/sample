<?php
/**
 * Created by PhpStorm.
 * User: hizbul
 * Date: 1/18/17
 * Time: 5:38 PM
 */

namespace App\Repository\Facades;


use Illuminate\Support\Facades\Facade;

class SampleApi extends Facade
{
    /**
     * Api consumer facade
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'ApiConsumer';
    }

}