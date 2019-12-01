<?php

namespace App\Repository\Api;

use Illuminate\Http\Request;
use Webpatser\Uuid\Uuid;
use DB;
use App\Forms\DynamicFormHandler;
use App\Model\FormConfiguration;
use App\Repository\Api\ApiResponseHandler;

/**
 * API Response Handler class.
 */
class ApiCommonProcessHandler
{   
    protected $apiResposeHandler;

    public function __construct()
    {
        $this->apiResposeHandler = new ApiResponseHandler();
    }

    /**
     * Get model data
     *
     * @param String $modelClassName
     * @param Request $request
     *
     * @return Array
     */
    private function getData($modelClassName, Request $request)
    {
        //Load model class object
        $model = new $modelClassName();

        //Load fields, where clause items, sort field & direction if available.
        /*$arrFields = ($request->has('fields')) ? $request->get('fields') : [];
        $arrWhere = ($request->has('where')) ? $request->get('where') : [];
        $sortField = ($request->has('sortField')) ? $request->get('sortField') : 'created_at';
        $sortDirection = ($request->has('sortDirection')) ? $request->get('sortDirection') : 'DESC';*/
        //$multiSort = ($request->has('multiSort')) ? $request->get('multiSort') : null;
        //Select all or given fields.
        /*if (sizeof($arrFields) == 0) {
            $listItems = $model::select(DB::raw('*'));
        } else {
            $listItems = $model::select($arrFields);
            //$model::withTrashed()->select($arrFields);
        }*/

        $listItems = $model::select(DB::raw('*'));

        if ($request->has('name')) {
            $listItems->where('name', 'like', '%' . $request->name . '%');
        }

        //Add where clause items
        /*if (sizeof($arrWhere) > 0) {
            foreach ($arrWhere as $column => $value) {
                if ($value == "null") {
                    $listItems = $listItems->whereNull($column);
                } else {
                    $listItems = $listItems->where($column, 'like', '%' . $value . '%');
                }
            }
        }*/
        /**
         * Adding the sorting array to add multiple sorting
         */
        /*if (empty($multiSort)) {
            //Add order by items
            if ($sortField != 'created_at') {
                $listItems = $listItems->orderBy($sortField, $sortDirection);
            } else {
                $listItems = $listItems->orderBy($sortField, $sortDirection)->orderBy('created_at', 'ASC');
            }
        }*/

        //Return model object
        return $listItems->orderBy('id', 'desc')->paginate(3);
    }

    /**
     * Add/Update model data
     *
     * @param Request $request
     * @param String $moduleName
     * @param String $modelClassName
     * @param Array $arrFieldsToSave
     *
     * @return Array
     */
    public function saveModelData(Request $request, $moduleName, $modelClassName)
    {
        //Load model class object
        $model = new $modelClassName();
        //If ID then update, else create
        if ($request->has('id')) {
            $modelId = $request->get('id');
        } else {
            $modelId = '';
        }
        if ($modelId != '') {            
            $model::where('id', $id)->update($request->except(['_token', '_method']));
            $modelData = $model::where('id', $id)->first();
        } else {
            $modelData = $model::create($request->except(['_token', '_method']));
        }

        return $modelData;
    }

    /**
     * Delete model data
     *
     * @param String $modelClassName
     * @param String $id
     *
     * @return Boolean
     */
    private function deleteData($modelClassName, $id)
    {
        //Load model class object
        $model = new $modelClassName();
        //If found object then delete
        if ($model::find($id)) {
            $delete = $model::where('id', $id)->delete();
            return true;
        } else {
            return false;
        }
    }


    /**
     * Get Model data by Model
     *
     * @param String $modelClassName
     * @param Request $request
     *
     * @return Array
     */
    public function getModelListByModel($modelClassName, Request $request)
    {        
        try {
            $modelData = $this->getData($modelClassName, $request);
        } catch (\Exception $ex) {
            //otherwise return error
            return $this->apiResposeHandler->returnResponse(
                $this->apiResposeHandler->responseStatus['error'],
                $ex->getMessage()
            );
        }
        if ($modelData) { 
            return $modelData;
            //Return faculty object
            return $this->apiResposeHandler->returnResponse(
                $this->apiResposeHandler->responseStatus['success'],
                $modelData
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
     * Save Model data by Model
     *
     * @param Request $request
     * @param String $moduleName
     * @param String $modelClassName
     * @param Array $arrFieldsToSave
     *
     * @return Array
     */
    public function saveModelDataByModel(Request $request, $moduleName, $modelClassName)
    {
        if (sizeof($request->all()) == 0) {
            return $this->returnResponse(
                $this->apiResposeHandler->responseStatus['BadRequest'],
                $this->apiResposeHandler->errorFieldMissingMessage()
            );
        }

        try {
            $modelData = $this->saveModelData($request, $moduleName, $modelClassName);
        } catch (\Exception $ex) {
            //otherwise return error
            return $this->apiResposeHandler->returnResponse(
                $this->apiResposeHandler->responseStatus['error'],
                $ex->getMessage()
            );
        }
        //return $modelData;
        if (isset($modelData->id) || isset($modelData->pivot_id)) {
            //Return success object
            return $this->apiResposeHandler->returnResponse(
                $this->apiResposeHandler->responseStatus['success'],
                $modelData
            );
        } else {
            //otherwise return error
            return $this->apiResposeHandler->returnResponse(
                $this->apiResposeHandler->responseStatus['error'],
                $modelData
            );
        }
    }

    /**
     * Delete Model data by Id
     *
     * @param Request $request
     * @param String $modelClassName
     *
     * @return Array
     */
    public function deleteModelDataById(Request $request, $modelClassName, $switchDomain = false)
    {

        //Check id, if not then send bad request response
        if (!$request->has('id')) {
            return $this->apiResposeHandler->returnResponse(
                $this->apiResposeHandler->responseStatus['BadRequest'],
                $this->apiResposeHandler->missingIdMessage()
            );
        } else {
            try {
                $modelData = $this->deleteData($modelClassName, $request->get('id'));
            } catch (\Exception $ex) {
                //otherwise return error
                return $this->apiResposeHandler->returnResponse(
                    $this->apiResposeHandler->responseStatus['error'],
                    $ex->getMessage()
                );
            }
            if ($modelData === true) {
                //Return success object
                return $this->apiResposeHandler->returnResponse(
                    $this->apiResposeHandler->responseStatus['success'],
                    $this->apiResposeHandler->successDeleteMessage()
                );
            } else {
                //otherwise return error
                return $this->apiResposeHandler->returnResponse(
                    $this->apiResposeHandler->responseStatus['error'],
                    $this->apiResposeHandler->errorDeleteMessage()
                );
            }
        }
    }

    /**
     * Compare date with model
     *
     * @param Request $request
     * @param Class $model
     * @param String $model_id
     * @param String $model_name
     *
     * @return Array
     */
    public function compareDateWithModel(Request $request, $model, $model_id, $model_name)
    {
        //Check term dates between model dates
        if ($request->has($model_id)) {

            //Load selected model
            $modelData = $model::find($request->get($model_id));
            if (!$modelData) {
                //If no model data loaded return bad request
                return $this->apiResposeHandler->returnResponse(
                    $this->apiResposeHandler->responseStatus['BadRequest'],
                    $this->apiResposeHandler->makeMessage('Please select correct ' . $model_name . '.')
                );
            }
            if ($request->has('start_date') && $modelData->start_date != null && $modelData->end_date != null) {
                //Compare given start date with model's start date.
                if (!compareDateBetween($modelData->start_date, $modelData->end_date, $request->get('start_date'))) {
                    return $this->apiResposeHandler->returnResponse(
                        $this->apiResposeHandler->responseStatus['BadRequest'],
                        $this->apiResposeHandler->makeMessage('Please provide valid start date.')
                    );
                }
            }
            if ($request->has('end_date') && $modelData->start_date != null && $modelData->end_date != null) {
                //Compare given end date with model's end date.
                if (!compareDateBetween($modelData->start_date, $modelData->end_date, $request->get('end_date'))) {
                    return $this->apiResposeHandler->returnResponse(
                        $this->apiResposeHandler->responseStatus['BadRequest'],
                        $this->apiResposeHandler->makeMessage('Please provide valid end date.')
                    );
                }
            }

            //Switch back to main DB
            //switchToMainDatabase();
        }

        return [];
    }

    /**
     * Merge where clause in request with new items
     *
     * @param Request $request
     * @param Array $arrNewItem
     *
     * @return Request $request
     */
    public function requestWhereMerge(Request $request, $arrNewItem)
    {
        if ($request->has('where')) {
            $request->merge(['where' => array_merge($request->get('where'), $arrNewItem)]);
        } else {
            $request->merge(['where' => $arrNewItem]);
        }

        return $request;
    }

    public function getAnyModelDataById(Request $request)
    {
        if ($request->has('module')) {
            try {
                $formConfig = FormConfiguration::where('module', '=', $request->get('module'))->value('module');

                if ($formConfig != null) {
                    //Load fields, where clause items, sort field & direction if available.
                    $arrFields = ($request->has('fields')) ? $request->get('fields') : [];
                    $arrWhere = ($request->has('where')) ? $request->get('where') : [];
                    $sortField = ($request->has('sortField')) ? $request->get('sortField') : 'created_at';
                    $sortDirection = ($request->has('sortDirection')) ? $request->get('sortDirection') : 'DESC';

                    //Select all or given fields.
                    if (sizeof($arrFields) == 0) {
                        $listItems = DB::table($formConfig)->select(DB::raw('*'));
                    } else {
                        $listItems = DB::table($formConfig)->select($arrFields);
                    }

                    //Add where clause items
                    if (sizeof($arrWhere) > 0) {
                        foreach ($arrWhere as $column => $value) {
                            if ($value == "null") {
                                $listItems = $listItems->whereNull($column);
                            } else {
                                $listItems = $listItems->where($column, 'like', '%' . $value . '%');
                            }
                        }
                    }

                    //Add order by items
                    $listItems = $listItems->orderBy($sortField, $sortDirection);
                    //Return model object

                    return $this->apiResposeHandler->returnResponse(
                        $this->apiResposeHandler->responseStatus['success'],
                        $listItems->get()
                    );
                } else {
                    return $this->apiResposeHandler->returnResponse(
                        $this->apiResposeHandler->responseStatus['error'],
                        $this->apiResposeHandler->makeMessage('Please provide valid module name.')
                    );
                }
            } catch (\Exception $ex) {
                //otherwise return error
                return $this->apiResposeHandler->returnResponse(
                    $this->apiResposeHandler->responseStatus['error'],
                    $ex->getMessage()
                );
            }
        } else {
            return $this->apiResposeHandler->returnResponse(
                $this->apiResposeHandler->responseStatus['error'],
                $this->apiResposeHandler->makeMessage('Please provide module name.')
            );
        }
    }

    /**
     * Delete model data by any field
     *
     * @param String $modelClassName
     * @param String $id
     *
     * @return Boolean
     */
    public function deleteByField($modelClassName, $fieldName, $fieldValue)
    {
        //Load model class object
        $model = new $modelClassName();
        //If found object then delete
        if ($model::where($fieldName, $fieldValue)) {
            $delete = $model::where($fieldName, $fieldValue)->delete();
            return true;
        } else {
            return false;
        }
    }


    /***
     * Delete model data by any field
     *
     * @param String $modelClassName
     * @param String $id
     *
     * @return Boolean
     */
    public function deleteByFieldValues($modelClassName, $fieldName, $fieldValues)
    {

        //Load model class object
        $model = new $modelClassName();
        $delete = $model::whereIn($fieldName, $fieldValues)->delete();
    }


    /***
     * Delete model data by any field
     *
     * @param String $modelClassName
     * @param String $id
     *
     * @return Boolean
     */
    public function deleteByFields($modelClassName, $cond)
    {

        //Load model class object
        $model = new $modelClassName();
        $delete = $model::where($cond)->delete();
    }

    /*
             update by values

    */

    public function updateByfield($modelClassName, $fieldName, $fieldValue, $items)
    {
        $model = new $modelClassName();
        $model::where($fieldName, $fieldValue)->update($items);
    }

    /*
     * multiple insert row
     *
     * it does not check validation
     *
     */

    public function multipleInsert($modelClassName, $items)
    {
        $now = \Carbon\Carbon::now();
        $items = collect($items)->map(function (array $data) use ($now) {
            return array_merge([

                'id' => Uuid::generate()->string
            ], $data);
        })->all();

        $tab_name = (new $modelClassName)->getTable();

        \DB::table($tab_name)->insert($items);
    }
}
