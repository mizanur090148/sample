<?php

namespace App\Repository\Api\Provider;

use Illuminate\Http\Request;
use App\Repository\Api\ApiResponseHandler;
use App\Repository\Api\ApiCommonProcessHandler;

use App\Requests\ColorRequest;
use App\Models\Color;

/**
 * Class API provider
 */
class ApiColorProvider
{
    protected $apiResposeHandler;

    protected $apiCommonProcessHandler;

    function __construct()
    {
        /**
         * Load API Response handler class
         */
        $this->apiResposeHandler = new ApiResponseHandler();
        $this->apiCommonProcessHandler = new ApiCommonProcessHandler();
    }

    /**
     * Get course data
     *
     * @param Request $request
     *
     * @return Array
     */
    public function colorList(Request $request)
    {
        $modelData = $this->apiCommonProcessHandler->getModelListByModel(Color::class, $request);

        return $modelData;
    }
    
    /**
     * Save class data
     *
     * @param Request $request
     *
     * @return Array
     */
    public function saveColor(Request $request)
    {       
        /*$validator = \Validator::make($request->all(), ['name' => 'required']);
        
        if ($validator->fails()) {

          return response()->json([
            'status' => 422,
            'errors' => $validator->errors()
        ]);
        }*/

        $rules = array(
          'fname' => 'required|max:255',
          'lname'  => 'required|max:255',
          'email'      => 'required|email|max:255|unique:users',
          'password'   => 'required|min:6|confirmed',
    );
        $this->validate( $request , $rules);

        $modelData = $this->apiCommonProcessHandler->saveModelDataByModel($request, 'colors', Color::class);

        return $modelData;
    }

    /**
     * Delete class data
     *
     * @param Request $request
     *
     * @return Array
     */
    public function deleteClassData(Request $request)
    {
        return $this->apiCommonProcessHandler->deleteModelDataById($request, Classes::class);
    }
    
    /**
     * @param Request $request
     * @return \App\Repository\Api\jSon
     * get quitch usert eacher class
     */
    public function getQuitchUserTeacherClass(Request $request)
    {
        $count_active = DB::table('profile_roles')
            ->join('roles', 'profile_roles.role_id', '=', 'roles.id')
            ->where('roles.slug', TEACHER)
            ->where(['profile_roles.profile_id' => $request->get('user_id'), 'status' => SUSPENDED])
            ->count();

        if ($count_active > 0) {
            $class_list = [];
            return $this->apiResposeHandler->returnResponse(
                $this->apiResposeHandler->responseStatus['success'],
                $class_list
            );
        }
        //Load organization users
        $classes = DB::table('classes')
            ->leftJoin('class_teachers', 'class_teachers.class_id', '=', 'classes.id');
        $classes->whereIn('class_teachers.user_id', [$request->get('user_id')]);
        $classes->where('classes.end_date', '>=', date('Y-m-d'));
        $classes->where('classes.deleted_at', NULL);
        $classes->groupBy('classes.id');

        $class_list = [];

        try {
            $classes->select([
                'classes.*'
            ]);
            $class_list = $classes->get();

            if ($request->get('grab_analytics')) {


                foreach ($class_list as $ind => $class_l) {

                    $request->request->add(['class_id' => $class_l->id]);

                    $modelData = ['content' => Section::where(['class_id' => $class_l->id])->get()];

                    $class_list[$ind]->analytics = app('\App\Repository\Api\Provider\ApiLeaderboardProvider')->getClassAccuracy($request);

                }
            }

        } catch (\Exception $ex) {
            //otherwise return error
            return $this->apiResposeHandler->returnResponse(
                $this->apiResposeHandler->responseStatus['error'],
                $ex->getMessage()
            );
        }
        if ($class_list) {
            //Return class object
            return $this->apiResposeHandler->returnResponse(
                $this->apiResposeHandler->responseStatus['success'],
                $class_list
            );
        } else {
            //otherwise return error
            return $this->apiResposeHandler->returnResponse(
                $this->apiResposeHandler->responseStatus['error'],
                $this->apiResposeHandler->errorListMessage()
            );
        }
    }

    /**
     * Get student's classes
     *
     * @param Request $request
     *
     * @return Array
     */
    public function getQuitchUserStudentClass(Request $request)
    {
        //Load organization users
        $classes = DB::table('classes')
            ->leftJoin('class_student_pivot', 'class_student_pivot.class_id', '=', 'classes.id');

        $classes->whereIn('class_student_pivot.student_id', [$request->get('user_id')]);
        $classes->where('class_student_pivot.status', '=', 'active');
        $classes->where('classes.end_date', '>=', date('Y-m-d'));
        $class_list = [];

        try {
            $classes->select([
                'classes.*'
            ])->orderBy('classes.class_name', "ASC");
            $class_list = $classes->get();

            if ($request->get('grab_analytics')) {

                foreach ($class_list as $ind => $class_l) {

                    $request->request->add(['class_id' => $class_l->id]);

                    $modelData = ['content' => Section::where(['class_id' => $class_l->id])->get()];

                    $class_list[$ind]->analytics = app('\App\Repository\Api\Provider\ApiSectionProvider')->getStatData($request, $modelData);

                }
            }
        } catch (\Exception $ex) {
            //otherwise return error
            return $this->apiResposeHandler->returnResponse(
                $this->apiResposeHandler->responseStatus['error'],
                $ex->getMessage()
            );
        }
        if ($class_list) {
            //Return class object
            return $this->apiResposeHandler->returnResponse(
                $this->apiResposeHandler->responseStatus['success'],
                $class_list
            );
        } else {
            //otherwise return error
            return $this->apiResposeHandler->returnResponse(
                $this->apiResposeHandler->responseStatus['error'],
                $this->apiResposeHandler->errorListMessage()
            );
        }
    }

    /**
     * @param Request $request
     * @return \App\Repository\Api\jSon
     * get org classes
     */
    public function getorgclasses(Request $request)
    {
        if (!$request->has('subdomain')) {

            return $this->apiResposeHandler->returnResponse(
                $this->apiResposeHandler->responseStatus['error'],
                'Subdomain should be provided'
            );
        }

        $profile_id = $this->getprofileId($request);

        if (!$profile_id) {

            return $this->apiResposeHandler->returnResponse(
                $this->apiResposeHandler->responseStatus['error'],
                'User Not Exist On ' . $request->get('subdomain')
            );
        }

        $profile_id = $profile_id->profile_id;
        $request->request->add(['profile_id' => $profile_id]);

        $result = [];

        $roles = DB::table('profile_roles')
            ->join('roles', 'roles.id', '=', 'profile_roles.role_id')
            ->where('profile_roles.profile_id', $profile_id)
            ->select('roles.*')
            ->get()
            ->pluck('slug')
            ->unique()
            ->toArray();

        $result['studentclass'] = $this->getQuitchUserStudentClass($request)['content'];

        $result['taughtclass'] = $this->getQuitchUserTeacherClass($request)['content'];

        $result['roles'] = $roles;

        return $this->apiResposeHandler->returnResponse(
            $this->apiResposeHandler->responseStatus['success'],
            $result
        );

    }


    /**
     * Clear class-student or class-teacherassistant pivot table
     *
     * @param Object $model
     * @param String $id
     */
    private function clearClassStudentsOrClassTeacherAssistant($model, $id)
    {
        $model::where('class_id', $id)->delete();
    }

    /**
     * Add class-student or class-teacherassistant pivot table data
     *
     * @param Object $model
     * @param String $type
     * @param String $typeId
     * @param String $classId
     */
    private function addClassStudentsOrClassTeacherAssistant($model, $type, $typeId, $classId)
    {
        $model::create(['class_id' => $classId, $type => $typeId]);
    }

    /**
     * Get class students, teacher & teching assistant
     *
     * @param String $subdomain
     * @param String $type
     * @param String $classId /$teacherId
     * @param Class $model
     * @param String $field_name
     *
     * @return Array of User Object
     */
    private function getClassUsers($type, $classId)
    {
        if ($type == STUDENT) {
            return \DB::table('class_student_pivot')
                ->select([
                    'class_student_pivot.student_id',
                    'users.email',
                    'users.first_name',
                    'users.last_name',
                    'organization_users.approved'
                ])
                ->leftJoin(\Config::get('database.connections.mysql.database') . '.users', 'users.id', '=', 'class_student_pivot.student_id')
                ->leftJoin(\Config::get('database.connections.mysql.database') . '.organization_users', 'users.id', '=', 'organization_users.id_user')
                ->where('class_student_pivot.class_id', $classId)
                ->get();
        } else {
            $role = Sentinel::findRoleBySlug($type);

            return \DB::table('class_teachers')
                ->leftJoin('profiles', 'class_teachers.user_id', '=', 'profiles.id')
                //->leftJoin('profile_roles', 'profiles.id', '=', 'profile_roles.profile_id')
                ->where('class_teachers.class_id', $classId)
                ->where('class_teachers.teacher_role', $role->id)
                ->get(['profiles.*']);
        }
    }

}