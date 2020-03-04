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

    /**
     *      ____.__      _____________ ___________     __                  
     *     |    /  \    /  \__    ___/ \__    ___/___ |  | __ ____   ____  
     *     |    \   \/\/   / |    |      |    | /  _ \|  |/ // __ \ /    \ 
     * /\__|    |\        /  |    |      |    |(  <_> )    <\  ___/|   |  \
     * \________| \__/\  /   |____|      |____| \____/|__|_ \\___  >___|  /
     *                 \/                                  \/    \/     \/ 
     */

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
        $exp = $iat + 604800;               // Expire after 7 days
        $timestamp = now();
        $data = [                       // Data related to the signer user
            'user_id'   => $userId,      // userid from the users table
            'userEmail' => $userEmail,  // User name
            'login_type' => $type
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
        $tokenResult = $this->verifyJWTToken($this->input->request_headers());
        if($tokenResult)
            $this->response([
                'status' => 'success',
                "message" => "Token is verified and valid",
                'data' => [
                    $tokenResult
                ]
            ], parent::HTTP_OK);
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
    
    public function checkUserEmailExist($userEmail){ //not used
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

    /**
     *    _________               __                  
     *   /   _____/__.__. _______/  |_  ____   _____  
     *   \_____  <   |  |/  ___/\   __\/ __ \ /     \ 
     *   /        \___  |\___ \  |  | \  ___/|  Y Y  \
     *  /_______  / ____/____  > |__|  \___  >__|_|  /
     *          \/\/         \/            \/      \/ 
     */

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
    //********************************************************************************************************/
    // S Y S T E M --------------------------------------------------------------------------------------------
    
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
            $this->response([
                'status' => 'success',
                "message" => "Successfully logged in",
                'data' => [
                    'access_token' => $token,
                    'token_type' => 'jwt',
                    'expiry' => date('Y/m/d H:i:s', now() + 604800),
                    'login_type' => 'system'
                ]
            ], parent::HTTP_OK);
        }
    }
    
    // public function systemListByUserID_post(){ //depricated
    //     $result = $this->bepmsdb->systemListByUserID($this->post['id']);
    //     $this->response(['data' => $result], parent::HTTP_OK);
    // }

    public function systemList_post(){
        $tokenResult = $this->verifyJWTToken($this->input->request_headers());

        if(!$tokenResult || !isset($tokenResult->data) || !isset($tokenResult->data->user_id) || !isset($tokenResult->data->login_type)){
            $this->response(['message' => 'invalid token'], parent::HTTP_BAD_REQUEST);
        }

        $data = $tokenResult->data;
        $user_id = $data->user_id; 
        $login_type = $data->login_type;

        if($login_type === 'system'){
            $result = $this->bepmsdb->systemListByUserID($user_id);
            $this->response(['data' => $result], parent::HTTP_OK);
        } else {
            $this->response(['message' => 'your dont have access to this api'], parent::HTTP_BAD_REQUEST);
        }    

    }
    
    public function homeDetails_post(){
        $tokenResult = $this->verifyJWTToken($this->input->request_headers());
        $system_id = $this->post['system_id'];
        $project_position_name = $this->post['project_position_name'];
        $project_id = $this->post['project_id'];

        if(!$tokenResult || !isset($tokenResult->data) || !isset($tokenResult->data->user_id) || !isset($tokenResult->data->login_type)){
            $this->response(['message' => 'invalid token'], parent::HTTP_BAD_REQUEST);
        }

        $student = ['leader', 'member'];
        $faculty = ['hod', 'guide', 'pc'];

        $data = $tokenResult->data;
        $user_id = $data->user_id;
        $login_type = $data->login_type;

        if($login_type === 'system'){
            $x = $this->checkifPositionValid($user_id, $system_id, $project_position_name, $project_id);
            //if member or leader 
            if($x && in_array($project_position_name, $student)){
                $project_details = $this->bepmsdb->projectDetailsByPositionIDandName($user_id, $system_id, $project_position_name,$project_id);
                $this->response([
                        'status' => 'success',
                        'data' => $project_details
                    ]
                    , parent::HTTP_OK
                );
            }
            if ($x && in_array($project_position_name, $faculty)){
                $project_report_list = $this->bepmsdb->facultyHomeDetailsBySystemIDPositionName($user_id, $system_id, $project_position_name, $project_id);

                //facultyHomeDetailsBySystemIDPositionName
                $this->response([
                        'status' => 'success',
                        'data' => $project_report_list
                    ]
                    , parent::HTTP_OK
                );
            }
            $this->response(['message' => 'invalid position name'], parent::HTTP_OK);
        } else {
            $this->response(['message' => 'your dont have access to this api'], parent::HTTP_BAD_REQUEST);
        }
    }

    public function checkifPositionValid($user_id, $system_id, $project_position_name, $project_id){
        $result = $this->bepmsdb->userPositionsInSystemWithProjectId($user_id, $system_id, $project_id);
        $flag = false;
        foreach ($result as $value) {
            if($value->project_position_name === $project_position_name){
                $flag = true;
            }
        }
        return $flag;
    }

    /**
     * Reports
     */

     public function reportsUserProject(){
        $tokenResult = $this->verifyJWTToken($this->input->request_headers());
        $system_id = $this->post['system_id'];
        $project_position_name = $this->post['project_position_name'];
        $project_id = $this->post['project_id'];

        if(!$tokenResult || !isset($tokenResult->data) || !isset($tokenResult->data->user_id) || !isset($tokenResult->data->login_type)){
            $this->response(['message' => 'invalid token'], parent::HTTP_BAD_REQUEST);
        }

        $student = ['leader']; //member dont have acces to the reports
        $faculty = ['hod', 'guide', 'pc'];

        $data = $tokenResult->data;
        $user_id = $data->user_id;
        $login_type = $data->login_type;

        if($login_type === 'system'){
            $x = $this->checkifPositionValid($user_id, $system_id, $project_position_name, $project_id);

            if($x && (in_array($project_position_name, $faculty) || in_array($project_position_name, $student) )){
                $userProjectReports = $this->bepmsdb->userProjectReports($user_id, $system_id, $project_position_name,$project_id);
                $this->response([
                        'status' => 'success',
                        'data' => $userProjectReports
                    ]
                    , parent::HTTP_OK
                );
            }
            $this->response(['message' => 'invalid position name'], parent::HTTP_OK);
        } else {
            $this->response(['message' => 'inavlid token access right'], parent::HTTP_BAD_REQUEST);
        }

    }

    //current system reports lists...
    public function searchReportInSystemID_post(){
        $tokenResult = $this->verifyJWTToken($this->input->request_headers());
        $system_id = $this->post['system_id'];
        $project_position_name = $this->post['project_position_name'];
        $search_input = $this->post['search_input'];

        if(!$tokenResult || !isset($tokenResult->data) || !isset($tokenResult->data->user_id) || !isset($tokenResult->data->login_type)){
            $this->response(['message' => 'invalid token'], parent::HTTP_BAD_REQUEST);
        }//search_input

        $student = ['leader']; //member dont have acces to the reports
        $faculty = ['hod', 'guide', 'pc'];

        $data = $tokenResult->data;
        $user_id = $data->user_id;
        $login_type = $data->login_type;

        if($login_type === 'system'){
            $x = $this->checkifPositionValid($user_id, $system_id, $project_position_name, $search_input);

            if($x && (in_array($project_position_name, $faculty) || in_array($project_position_name, $student) )){
                $userProjectReports = $this->bepmsdb->userProjectReports($user_id, $system_id, $project_position_name,$search_input);
                $this->response([
                        'status' => 'success',
                        'data' => $userProjectReports
                    ]
                    , parent::HTTP_OK
                );
            }
            $this->response(['message' => 'invalid position name'], parent::HTTP_OK);
        } else {
            $this->response(['message' => 'inavlid token access right'], parent::HTTP_BAD_REQUEST);
        }
    }
    

    /**
     *    _____       .___      .__        
     *    /  _  \    __| _/_____ |__| ____  
     *   /  /_\  \  / __ |/     \|  |/    \ 
     *  /    |    \/ /_/ |  Y Y  \  |   |  \
     *  \____|__  /\____ |__|_|  /__|___|  /
     *          \/      \/     \/        \/ 
     */

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

            $this->response([
                'status' => 'success',
                "message" => "Successfully logged in",
                'data' => [
                    'access_token' => $token,
                    'token_type' => 'jwt',
                    'expiry' => date('Y/m/d H:i:s', now() + 604800),
                    'login_type' => 'admin'
                ]
            ], parent::HTTP_OK);
        }
    }

    //----------------------------------------------------------------------------------------------------------

}
