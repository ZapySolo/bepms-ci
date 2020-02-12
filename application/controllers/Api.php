<?php   defined('BASEPATH') OR exit('No direct script access allowed');

require APPPATH . 'libraries/REST_Controller.php';

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");

class Api extends REST_Controller {

    function __construct() {
        // Construct the parent class
        parent::__construct();
        // // Configure limits on our controller methods
        // // Ensure you have created the 'limits' table and enabled 'limits' within application/config/rest.php
        $this->methods['login_authenticate_post']['limit'] = 50; // 500 requests per hour per user/key
        $this->post = $_REQUEST;
        $this->load->helper(['jwt', 'authorization']);
        $this->load->model('bepmsdb');
        //$this->verifyJWTToken($this->input->request_headers());
    }

    //JWT Token Operation -----------------------------------------------------------------------------------------------
    //remember to pass the token inside the header as 'Authorization'
    function verifyJWTToken($headers){

        try {
            // Validate the token
            // Successfull validation will return the decoded user data else returns false
            if (array_key_exists('Authorization', $headers) && !empty($headers['Authorization'])) {
                $data = AUTHORIZATION::validateToken($headers['Authorization']);
                //TODO: Change 'token_timeout' in application\config\jwt.php
                $decodedToken = AUTHORIZATION::validateTimestamp($headers['Authorization']);
                // return response if token is valid
                if ($decodedToken != false) {
                    $this->set_response($decodedToken, REST_Controller::HTTP_OK);
                    return $data;
                } else {
                    $status = parent::HTTP_UNAUTHORIZED;
                    $response = ['status' => $status, 'message' => 'Token Expired'];
                    $this->response($response, $status);
                    exit();
                }
            } else {
                $status = parent::HTTP_UNAUTHORIZED;
                $response = ['status' => $status, 'message' => 'Unauthorized Access!'];
                $this->response($response, $status);
                exit();
            }
        } catch (Exception $e) {
            // Token is invalid
            // Send the unathorized access message
            $status = parent::HTTP_UNAUTHORIZED;
            $response = ['status' => $status, 'message' => 'Unauthorized Access! '];
            $this->response($response, $status);
            exit();
        }   
    }

    //create token
    public function createToken($userId, $userEmail, $type) {
        $this->load->helper('url');

        $url_parts = parse_url(current_url());
        $iss = $url_parts['scheme'] . '://' . str_replace('www.', '', $url_parts['host']);// Issuer
        $iat = now();                  // Issued at: time when the token was generated
        $jti = base64_encode(openssl_random_pseudo_bytes(32));// Json Token Id: an unique identifier for the token
        $nbf = $iat + 10;                  // Not before
        $exp = $iat + 604800;                  // Expire after 7 days
        $timestamp = now();
        $data = [                       // Data related to the signer user
            'userId'   => $userId,      // userid from the users table
            'userEmail' => $userEmail,  // User name
            'type' => $type
        ];

        $tokenData = [
        //    'iat'  => $iat,         
            'jti'  => $jti,          
            'timestamp'  => $timestamp,       
            'nbf'  => $nbf,        
            'exp'  => $exp,           
            'data' => $data
        ];

        $token = AUTHORIZATION::generateToken($tokenData);

        return $token;
    }

    public function verifyJWTToken_post(){
        $result = $this->verifyJWTToken($this->input->request_headers());
        if($result)
            $this->response($result, parent::HTTP_OK);
        else
            $this->response('invalid token', parent::HTTP_OK);
    }

    // -------------------------------------------------------------------------------------------------------- / 
    // ******************************************************************************************************** /
    // User Table Operations ---------------------------------------------------------------------------------- /

    /**
	 * Check if user exist
	 *
	 * @param string $userEmail
	 * @return boolean
	 */
    
    public function checkUserEmailExist($userEmail){
        $result = $this->bepmsdb->checkUserEmailExist($userEmail);
        return (!$result) ? false : true;
    }

    /**
	 * Create a new user
	 *
	 * @param string post['email']
	 * @return array
	 */

    public function createUser_post(){

        $this->load->helper('email');
        
        //check if email field exist
        if(!isset($this->post['email']) || $this->post['email'] === '') $this->response(['message' => 'email not provided'], parent::HTTP_BAD_REQUEST);

        $userEmail = $this->post['email'];
        //check if valid email
        if(!valid_email($userEmail)) 
            $this->response(['message' => 'invalid email'], parent::HTTP_BAD_REQUEST);

        //check if user exist
        if($this->checkUserEmailExist($userEmail)) 
            $this->response(['message' => 'user already exist'], parent::HTTP_OK);
    
        $result = $this->bepmsdb->createNewUser($userEmail);

        //if fialed
        (!$result) 
            ? $this->response(['message' => 'Unable to create user'], parent::HTTP_OK)
            : $this->response(['message' => 'Successfully created user'], parent::HTTP_CREATED);
    }

    // -------------------------------------------------------------------------------------------------------- / 
    // ******************************************************************************************************** /
    // System Operation --------------------------------------------------------------------------------------- /

    public function createSystem_post(){
        
        //check if name field exist
        if(!isset($this->post['name']) || $this->post['name'] === '') $this->response(['message' => 'system name not provided'], parent::HTTP_BAD_REQUEST);

        $systemName = $this->post['name'];

        //check if user exist
        if($this->checkSystemExist($systemName)) 
            $this->response(['message' => 'system already exist'], parent::HTTP_OK);
    
        $result = $this->bepmsdb->createNewSystem($systemName);

        //if failed
        (!$result)
            ? $this->response(['message' => 'Unable to create System'], parent::HTTP_OK)
            : $this->response(['message' => 'Successfully created System'], parent::HTTP_CREATED);
    }

    public function checkSystemExist($systemName){
        $result = $this->bepmsdb->checkSystemExist($systemName);
            return (!$result) ? false : true;
    }

    // --------------------------------------------------------------------------------------------------------
    //*********************************************************************************************************/
    // S Y S T E M ----------------------------------------------------------------------------------------------
    
    //after login send user the jwt login credentials and send him to homepage
    // homepage send the details fo the project, profile, notification, notice id and project position.
    public function systemLogin_post(){

        $this->load->helper('email');

        if(!isset($this->post['email']) || !isset($this->post['password']) || $this->post['email'] === '' || $this->post['password']==='') 
            $this->response(['message' => 'credentials not provided'], parent::HTTP_BAD_REQUEST);

        $userEmail = $this->post['email'];
        $password = hash("sha256", $this->post['password']);

        if(!valid_email($userEmail)) {
            $this->response(['message' => 'invalid email'], parent::HTTP_BAD_REQUEST);
        }
        
        $result = $this->bepmsdb->systemLoginAuthentication($userEmail, $password);

        if(!$result){
            $this->response(['message' => 'Invalid credentials'], parent::HTTP_OK);
        } else {
            $userId = $result->user_id;
            $token = $this->createToken($userId, $userEmail, $type = 'system');
            $this->response(['jwt' => $token], parent::HTTP_OK);
        }
    }
    
    //

    // --------------------------------------------------------------------------------------------------------










    // member --------------------------------------------------------------------------------------------------



    //---------------------------------------------------------------------------------------------------------

    //******************************************************************************************************* */

    // faculty ------------------------------------------------------------------------------------------------



    //---------------------------------------------------------------------------------------------------------

    // admin --------------------------------------------------------------------------------------------------

    public function adminLogin_post(){

        $this->load->helper('email');

        if(!isset($this->post['email']) || !isset($this->post['password']) || $this->post['email'] === '' || $this->post['password']==='') 
            $this->response(['message' => 'credentials not provided'], parent::HTTP_BAD_REQUEST);

        $userEmail = $this->post['email'];
        $password = hash("sha256", $this->post['password']);

        if(!valid_email($userEmail)) {
            $this->response(['message' => 'invalid email'], parent::HTTP_BAD_REQUEST);
        }
        
        $result = $this->bepmsdb->adminLoginAuthentication($userEmail, $password);

        if(!$result){
            $this->response(['message' => 'Invalid credentials'], parent::HTTP_OK);
        } else {
            $userId = $result->user_id;
            $token = $this->createToken($userId, $userEmail, $type = 'admin');
            $this->response(['jwt' => $token], parent::HTTP_OK);
        }
    }

    //----------------------------------------------------------------------------------------------------------

}
