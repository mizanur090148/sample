<?php

namespace App\Repository\Traits;

use App\Http\ViewComposer\NavigationComposer;
use Illuminate\Support\Facades\Session;
use App\Model\Sentinel\User;

use Uuid;

trait Impersonate
{

    /**
     * Make impersonate
     * @param $id
     * @return bool
     */
    protected function makeImpersonate($id = null)
    {
        $navigation = new NavigationComposer();

        if (!is_null($id)) {
            $objUser = User::findOrFail($id);
        } else {
            $objUser = User::where('email', ORGANIZATION_ADMIN_EMAIL)->first();
        }

        $objUser->api_token = (trim($objUser->api_token) != '') ? $objUser->api_token : Uuid::generate()->__toString();
        $objUser->save();

        if (!empty($objUser) && currentUser()) {
            Session::put('originalUser', currentUser());
            Session::put('currentUser', $objUser);

            Session::put('navigation', $navigation->createNavigation());

            return true;
        }

        return false;
    }
}