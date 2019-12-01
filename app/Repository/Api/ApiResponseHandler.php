<?php

namespace App\Repository\Api;

use Illuminate\Http\Request;

/**
 * API Response Handler class.
 */
class ApiResponseHandler
{    
    /**
     * Set common status codes
     */
    public $responseStatus = [
        'success' => API_RESPONSE_SUCCESS,
        'error' => 'API_RESPONSE_ERROR',
        'Unauthorized' => 'API_RESPONSE_UNAUTHORIZED',
        'BadRequest' =>' API_RESPONSE_BAD_REQUEST'
    ];

    /**
     * Return response in pre-defined format
     *
     * @param String $status
     * @param Array $arrObject
     *
     * @return jSon Object
     */
    public function returnResponse($status = '', $arrObject = [])
    {
        if (trim($status) == '') {
            $status = $this->responseStatus['error'];
        }
        $arrReturn['status'] = $status;
        $arrReturn['content'] = $arrObject;

        return $arrReturn;
    }

    /**
     * Make array of message
     *
     * @param String @message
     *
     * @return Array
     */
    public function makeMessage($message = '')
    {
        return ['message' => $message];
    }

    public function successSaveMessage()
    {
        return ['message' => 'Record saved successfully.'];
    }

    public function successDeleteMessage()
    {
        return ['message' => 'Record deleted successfully.'];
    }

    public function errorListMessage()
    {
        return ['message' => 'Failed to load record.'];
    }

    public function missingIdMessage()
    {
        return ['message' => 'Please provide id to delete record.'];
    }

    public function errorDeleteMessage()
    {
        return ['message' => 'Did not find record to delete.'];
    }

    public function errorFieldMissingMessage()
    {
        return ['message' => 'Missing fields to save record.'];
    }

    /**
     * Global validation method for all Api calls except login, register & forgotpassword.
     *
     * @param Request $request
     *
     * @return array
     *
     * @sample
     * $validateDomain = $this->apiResposeHandler->domainHandler($request);
     * if(sizeof($validateDomain) > 0)
     *    return $validateDomain;
     */
    public function domainHandler(Request $request)
    {

        if ($request->has('no_validate')) {
            return null;
        }
        //Switch to organization DB if subdomain present. Needed for web login
        if ($request->has('subdomain')) {
            $dbName = getDatabaseNameByKey($request->get('subdomain'));
            if (sizeof($dbName)) {
                //Switch to tenant DB connection
                session()->put('TENANT_DB_DATABASE', $dbName);
                session()->put('TENANT_ID', $request->get('subdomain'));

                switchToTenantDatabase();
            } else {
                //Wrong domain
                return $this->returnResponse(
                    $this->responseStatus['Unauthorized'],
                    $this->makeMessage('Please provide correct sub-domain to login.')
                );
            }

        }
        return [];
    }

    
    
    /**
     * Update User Api Token when needed.
     *
     * @param String $userId
     *
     * @return String $apiToken
     */
    public function updateUserApiToken($userId)
    {
        $apiToken = Uuid::generate()->__toString();
        DB::table('users')->where('id', $userId)->update(['api_token' => $apiToken]);

        return $apiToken;
    }
}