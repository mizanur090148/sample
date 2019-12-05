<?php
/**
 * Define API Response Code constants
 */
define('API_RESPONSE_SUCCESS', '200');
define('API_RESPONSE_ERROR', '500');
define('API_RESPONSE_UNAUTHORIZED', '401');
define('API_RESPONSE_BAD_REQUEST', '400');

define('S_SAVE', 'Successfully created');
define('E_SAVE', 'Not created');
define('S_DELETE', 'Successfully deleted');
define('E_DELETE', 'Not deleted');
define('S_UPDATE', 'Successfully updated');
define('E_UPDATE', 'Not updated');
define('USER', 0);
define('ADMIN', 1);
define('SUPER_ADMIN', 2);

/*
* Get Current user object
*/
function currentUser()
{
    if (Auth::check())
    {
        return Auth::user();
    }
    return null;
}

/*
* return user id of current user
*/
function userId()
{
    if (Auth::check())
    {
        return Auth::user()->id;
    }
    return null;
}

function factoryId()
{
    if (Auth::check())
    {
        return Auth::user()->factory_id;
    }
    return null;
}