<?php

namespace App\Repository\Api\Consumer;

use Vinelab\Http\Client;

use Config;

//use App\Http\Requests\RegistrationFormRequest;

use App\Http\Controllers\ApiProviderController;

use Illuminate\Http\Request;
use App\Repository\Api\Provider\ApiColorProvider;
use DB;

class ApiConsumer
{
    protected $client;

    public function __construct()
    {
        //$this->client = new Client();
    }

    /**
     * Get request to API endpoint
     * @param null $path
     * @param array $data
     * @return mixed
     */
    public function get($path = null, array $data = [])
    {
        $response = $this->client->get([
            'url' => Config::get('app.url') . '/api/' . $path,
            'param' => $this->getData($data)
        ]);

        return json_decode($response->content());
    }

    /**
     * Post request to API endpoint
     * @param null $path
     * @param array $data
     * @return mixed
     */
    public function post($path = null, array $data = [])
    {
        $request = app(Request::class);
        $actname = $this->getActionName($path);
        $objColors = app(ApiProviderController::class)->$actname($request);

        return $objColors;
    } 

    /**
     * Get data with API access data.
     * @param array $data
     * @return array
     */
    public function getData(array $data = [])
    {
        $data = array_merge($data, [

            'user_id' => 1,//currentUser()->id,
            'quitch_user_id' => 1,//currentUser()->getoriginal()['id'],
            'api_token' => 12345678765432,
            'web_request' => true,
        ]);

        return $data;
    }

    /**
     * @param $key
     * @return mixed
     */
    function getActionName($key)
    {

        $arr = [

            'login' => 'login',
            'register' => 'register',
            'password-reset' => 'passwordReset',
            'password-update' => 'passwordUpdate',

            'colors' => 'colorList',
            'faculty/save' => 'saveFacultyData',
            'faculty/delete' => 'deleteFacultyData'           

        ];

        return $arr[$key];

    }
}