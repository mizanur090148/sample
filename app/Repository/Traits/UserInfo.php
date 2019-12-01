<?php

namespace App\Repository\Traits;

use App\Model\Classes\ClassStudentsPivot;
use App\Model\Sentinel\Profile;


trait UserInfo
{

    /**
     * Make impersonate
     * @param $id
     * @return bool
     */
    protected function getUserinfo($id = null)
    {

        $user = ['id' => $id, 'first_name' => 'First name', 'last_name' => 'Last name', 'email' => ''];

        // $class_std_pivot = ClassStudentsPivot::where('student_id', $id)->first();

        // $user = Profile::where('invitation_code', $class_std_pivot->pivot_id)->first();

        $user_c = (array)\DB::table(\Config::get('database.connections.tenant.database') . '.class_student_pivot')
            ->select([

                'profiles.first_name as first_name',
                'profiles.last_name as last_name',
                'class_student_pivot.student_id as id'

            ])
            ->leftJoin(\Config::get('database.connections.tenant.database') . '.profiles', 'class_student_pivot.pivot_id', '=', 'profiles.invitation_code')
            ->where('student_id', $id)
            ->first();

        if (sizeof($user_c) > 0) {
            return $user_c;
        }

        return $user;

        // if(sizeof($class_std_pivot)>0) {                
        //     $user = Profile::where('invitation_code', $class_std_pivot->pivot_id)->first();
        //     if(sizeof($user)>0) {
        //         return $user;
        //     }

        // }

        //return $user;

    }
}