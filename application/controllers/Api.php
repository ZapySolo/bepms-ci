<?php

defined('BASEPATH') OR exit('No direct script access allowed');

// This can be removed if you use __autoload() in config.php OR use Modular Extensions
/** @noinspection PhpIncludeInspection */
require APPPATH . 'libraries/REST_Controller.php';

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");

/**
 * This is an example of a few basic user interaction methods you could use
 * all done with a hardcoded array
 *
 * @package         CodeIgniter
 * @subpackage      Rest Server
 * @category        Controller
 * @author          Phil Sturgeon, Chris Kacerguis
 * @license         MIT
 * @link            https://github.com/chriskacerguis/codeigniter-restserver
 */
class Api extends REST_Controller {

    function __construct()
    {
        // Construct the parent class
        parent::__construct();
        // // Configure limits on our controller methods
        // // Ensure you have created the 'limits' table and enabled 'limits' within application/config/rest.php
        $this->methods['login_authenticate_post']['limit'] = 50; // 500 requests per hour per user/key

        $this->post = $_REQUEST;

        $this->load->helper(['jwt', 'authorization']); 
    }

    //Verify JWT Token
    //remember to pass the token inside the header as 'Authorizatioin'
    function verifyJWTToken(){
        $headers = $this->input->request_headers();
        $token = $headers['Authorization'];
        try {
            // Validate the token
            // Successfull validation will return the decoded user data else returns false
            $data = AUTHORIZATION::validateToken($token);

            if ($data === false) {
                $status = parent::HTTP_UNAUTHORIZED;
                $response = ['status' => $status, 'msg' => 'Unauthorized Access!'];
                $this->response($response, $status);
                exit();
            } else {
                return true;
            }
        } catch (Exception $e) {
            // Token is invalid
            // Send the unathorized access message
            $status = parent::HTTP_UNAUTHORIZED;
            $response = ['status' => $status, 'msg' => 'Unauthorized Access! '];
            $this->response($response, $status);
            exit();
        }   
    }

    public function login_authenticate_post(){

        $tokenData = 'Hello World!';

        $token = AUTHORIZATION::generateToken($tokenData);

        $this->set_response([
            'token'=>$token
        ], REST_Controller::HTTP_OK);
    }

    public function verifyJWTToken_post(){
        if($this->verifyJWTToken()){
            $this->response('valid token', parent::HTTP_OK);
        }
    }
}
