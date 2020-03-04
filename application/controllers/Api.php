<?php   defined('BASEPATH') OR exit('No direct script access allowed');
header('Access-Control-Allow-Origin: *');
require APPPATH . 'libraries/REST_Controller.php';

class Api extends REST_Controller {

    function __construct() {
        parent::__construct();
        $this->methods['login_authenticate_post']['limit'] = 50; // 50 requests per hour per user/key
        $this->post = $_REQUEST;
        $this->load->helper(['jwt', 'authorization']);
        $this->load->model('bepms','bepmsdb');
    }

    function response_badRequestWithMessage($message){
        $this->response([
            'status' => 'failed',
            'message' => $message
        ], parent::HTTP_BAD_REQUEST);
    }

    function sendEmail($to_email = "nickpt.0699@gmail.com" ,$message = 'The email send using codeigniter library'){
        $html = '
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <div>
            <div style="margin: 20px; text-align: center;">
                <img src="http://localhost/bepms/assets/images/bepms-logo.png" alt="bepms" width="65">
            </div>
            
            <div style="margin: 10px;font-size:16px;">
                <div>Nikhil, you have received a new report from &lt;&lt;name&gt;&gt;</div>
                <div style="padding-top: 30px;padding-bottom: 20px;margin-top: 80px;">
                    <div style="margin-right: 5px; padding:5px 10px; border:1px solid #4DA1FF; text-align: center; display: inline; background-color: #4DA1FF; border-radius: 5px;color: #fff;">
                        Go to bepms
                    </div>
                    <div style="padding:5px 10px; border:1px solid #4DA1FF; text-align: center; display: inline;border-radius: 5px; background-color: #4DA1FF; border-radius: 5px;color: #fff;"">
                        View notification
                    </div>
                </div>
                <div style="margin-top: 80px;">
                    <p style="font-size:13px;">Was this email:
                        <a style="text-decoration: none;" href="">Userful</a> 
                        | 
                        <a style="text-decoration: none;" href="">Not Useful</a>
                    </p>
                    <hr style="border: 0.5px solid grey;">
                    <p style="font-size:11px; color: #999; font-weight:normal" >This message was sent to &lt;&lt;user_email&gt;&gt;. If you dont\'t want to receive these emails from BEPMS in the future, please disable the notifications in the user profile settings page</p>
                    <p style="font-size:11px; color: #999; font-weight:normal" >To help keep your account secure please don\'t forward this email.</p>
                    <p style="font-size:11px; color: #999; font-weight:normal" >&copy; 2020 ZapyTech</p>
                </div>
            </div>
        </div>
        ';
        $message = $html;
        $tradeMark = 'ZapyTech';
        $from_email = "support@zapy.tech";
        //Load email library
        $this->load->library('email');
        $this->email->from($from_email, $tradeMark);
        $this->email->to($to_email);
        $this->email->subject('[BEPMS] You have 1 new notification');
        $this->email->message($message);
        if($this->email->send()){
            echo('email send successfully');
        } else {
            echo('email send failed!');
        }
    }

    public function updateUserProfile_post(){
        $tokenResult = $this->verifyJWTToken($this->input->request_headers());

        if(!$tokenResult || !isset($tokenResult->data) || !isset($tokenResult->data->user_id) || !isset($tokenResult->data->login_type)){
            $this->response_badRequestWithMessage('Invalid Token!');
        }

        $data = $tokenResult->data;
        $user_id = $data->user_id;

        $update = [];

        if(isset($this->post['user_first_name']) && $this->post['user_first_name'] !== ''){
            $regex_user_first_name = '/^[a-zA-Z]*$/';
            if(preg_match($regex_user_first_name, $this->post['user_first_name'])) { 
                $update['user_first_name'] = $this->post['user_first_name'];
            } else {
                $this->response_badRequestWithMessage('Invalid First Name');
            }
        }
        if(isset($this->post['user_last_name']) && $this->post['user_last_name'] !== ''){
            $regex_user_last_name = '/^[a-zA-Z]*$/';
            if(preg_match($regex_user_last_name, $this->post['user_last_name'])) { 
                $update['user_last_name'] = $this->post['user_last_name'];
            } else {
                $this->response_badRequestWithMessage('Invalid Last Name');
            }
        }
        if(isset($this->post['user_display_name'])   && $this->post['user_display_name'] !== '' ){
            $regex_user_display_name = '/^[a-zA-Z. ]*$/';
            if(preg_match($regex_user_display_name, $this->post['user_display_name'])) { 
                $update['user_display_name'] = $this->post['user_display_name'];
            } else {
                $this->response_badRequestWithMessage('Invalid Display Name');
            }
        }
        if(isset($this->post['user_email']) && isset($this->post['user_confirm_email'])  && $this->post['user_email'] !== ''){
            if($this->post['user_email'] === $this->post['user_confirm_email']){
                $this->load->helper('email');
                if(valid_email($this->post['user_email'])) { 
                    $update['user_email'] = $this->post['user_email'];
                } else {
                    $this->response(['message' => 'Invalid email'], parent::HTTP_BAD_REQUEST);
                }
            } else {
                $this->response_badRequestWithMessage('Email do not match!');
            }
        }
        if(isset($this->post['user_mobile'])  && $this->post['user_mobile'] !== '' ){
            $regex_user_mobile = '/^(0|91)?[7-9][0-9]{9}$/';
            if(preg_match($regex_user_mobile, $this->post['user_mobile'])) { 
                $update['user_mobile'] = $this->post['user_mobile'];
            } else {
                $this->response_badRequestWithMessage('Invalid Mobile number');
            }
        }
        if(isset($this->post['user_password']) && $this->post['user_confirm_password']  && $this->post['user_password'] !== ''){
            if($this->post['user_password'] === $this->post['user_confirm_password']){
                $regex_user_password = '/^(?=.*\d)(?=.*[a-zA-Z#$^+=!*()@%&]).{8,32}$/';
                if(preg_match($regex_user_password, $this->post['user_password'])) { 
                    $update['user_password'] = hash("sha256", $this->post['user_password']);
                } else {
                    $this->response_badRequestWithMessage('Password must be 8 to 32 characters long...must contain atleast one numerica and one character and can contain #$^+=!*()@%&');
                }
            } else {
                $this->response_badRequestWithMessage('Password do not match!');
            }
        }
        if($update){
            $response = $this->bepmsdb->updateUserProfile($user_id, $update);
            if($response){
                $this->response(['message' => 'successfully updated your profile'], parent::HTTP_OK);
            } else {
                $this->response(['message' => 'error while updating your profile'], parent::HTTP_OK);
            }
        } else {
            $this->response_badRequestWithMessage('Didnt receive any input or input fields are invalid!');
        }
    }

    /**
     *      ____.__      _____________ ___________     __                  
     *     |    /  \    /  \__    ___/ \__    ___/___ |  | __ ____   ____  
     *     |    \   \/\/   / |    |      |    | /  _ \|  |/ // __ \ /    \ 
     * /\__|    |\        /  |    |      |    |(  <_> )    <\  ___/|   |  \
     * \________| \__/\  /   |____|      |____| \____/|__|_ \\___  >___|  /
     *                 \/                                  \/    \/     \/ 
     */

    public function createToken($userId, $userEmail, $type, $login_as) {
        $this->load->helper('url');

        $randomPseudoString = base64_encode(openssl_random_pseudo_bytes(32));
        $url_parts = parse_url(current_url());
        $url = $url_parts['scheme'] . '://' . str_replace('www.', '', $url_parts['host']);

        $iss = $url;                            // Issuer
        $iat = now();                           // Issued at: time when the token was generated
        $jti = $randomPseudoString;             // Json Token Id: an unique identifier for the token
        $nbf = $iat + 10;                       // Not before
        $exp = $iat + 604800;                   // Expire after 7 days i.e 604800 sec
        $timestamp = now();                     // Timestamp
        $data = [                              
            'user_id'   => $userId,            
            'userEmail' => $userEmail,          
            'login_type' => $type,
            'login_as' => $login_as
        ];

        $tokenData = [
            'iat'  => $iat,         
            'jti'  => $jti,          
            'timestamp'  => $timestamp,       
            'nbf'  => $nbf,        
            'exp'  => $exp,           
            'data' => $data
        ];

        $token = AUTHORIZATION::generateToken($tokenData);

        return $token;
    }

    function verifyJWTToken($headers){
        try {
            if (array_key_exists('Authorization', $headers) && !empty($headers['Authorization'])) {
                $data = AUTHORIZATION::validateToken($headers['Authorization']);
                $decodedToken = AUTHORIZATION::validateTimestamp($headers['Authorization']);
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
            $status = parent::HTTP_UNAUTHORIZED;
            $response = ['status' => $status, 'message' => 'Unauthorized Access! '];
            $this->response($response, $status);
            exit();
        }
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

    public function userShortDetail_post(){
        return true;
    }

    /**
     *    _________               __                  
     *   /   _____/__.__. _______/  |_  ____   _____  
     *   \_____  <   |  |/  ___/\   __\/ __ \ /     \ 
     *   /        \___  |\___ \  |  | \  ___/|  Y Y  \
     *  /_______  / ____/____  > |__|  \___  >__|_|  /
     *          \/\/         \/            \/      \/ 
     */

    //login
    public function systemLogin_post(){

        $this->load->helper('email');

        if(!isset($this->post['email']) || !isset($this->post['password']) || !isset($this->post['login_as']) || $this->post['email'] === '' || $this->post['password']==='' || $this->post['login_as']===''){
            $this->response_badRequestWithMessage('Credentials not provided!');
        }

        $userEmail = $this->post['email'];
        if(!valid_email($userEmail)) {
            $this->response_badRequestWithMessage('Invalid Email Address');
        }

        $password = hash("sha256", $this->post['password']);
        $login_as = $this->post['login_as']; //student, faculty

        if($login_as === 'faculty'){
            $result = $this->bepmsdb->systemFacultyLoginAuthentication($userEmail, $password);
        } else if ($login_as === 'student'){
            $result = $this->bepmsdb->systemStudentLoginAuthentication($userEmail, $password);
        } else {
            $this->response_badRequestWithMessage('Invalid Email Address');
        }

        if(!$result){
            $this->response_badRequestWithMessage('Invalid credentials');
        } else {
            $userId = $result->user_id;
            $token = $this->createToken($userId, $userEmail, $type = 'system', $login_as);
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

    /*
    *  __. ,      .       , 
    * (__ -+-. . _| _ ._ -+-
    * .__) | (_|(_](/,[ ) | 
    */         

    //student project list
    public function studentProjectList_post(){
        $tokenResult = $this->verifyJWTToken($this->input->request_headers());

        if(!$tokenResult || !isset($tokenResult->data) || !isset($tokenResult->data->user_id) || !isset($tokenResult->data->login_type)){
            $this->response_badRequestWithMessage('Invalid Token!');
        }

        $data = $tokenResult->data;
        $user_id = $data->user_id; 
        $login_type = $data->login_type;
        $login_as = $data->login_as;

        if($login_type !== 'system' || $login_as !== 'student'){
            $this->response_badRequestWithMessage('Invalid Api call...this api is only for student');
        }

        $project_lists = $this->bepmsdb->studentProjectListByUserID($user_id);

        $this->response(['data' => $project_lists], parent::HTTP_OK);

    }

    //student project details
    public function studentProjectDetails_post(){
        $tokenResult = $this->verifyJWTToken($this->input->request_headers());

        if(!$tokenResult || !isset($tokenResult->data) || !isset($tokenResult->data->user_id) || !isset($tokenResult->data->login_type)){
            $this->response_badRequestWithMessage('Invalid Token!');
        }

        $data = $tokenResult->data;
        $user_id = $data->user_id; 
        $login_type = $data->login_type;
        $login_as = $data->login_as;

        if($login_type !== 'system' || $login_as !== 'student'){
            $this->response_badRequestWithMessage('Invalid Api call...this api is only for student');
        }

        $project_id = $this->post['project_id'];

        $projectDetails = $this->bepmsdb->studentProjectDetailsByUserID($user_id, $project_id);

        $this->response(['data' => $projectDetails], parent::HTTP_OK);
    }

    //student report search list
    public function studentProjectReportListBySearchInput_post(){
        $tokenResult = $this->verifyJWTToken($this->input->request_headers());

        if(!$tokenResult || !isset($tokenResult->data) || !isset($tokenResult->data->user_id) || !isset($tokenResult->data->login_type)){
            $this->response_badRequestWithMessage('Invalid Token!');
        }

        $data = $tokenResult->data;
        $user_id = $data->user_id; 
        $login_type = $data->login_type;
        $login_as = $data->login_as;

        if($login_type !== 'system' || $login_as !== 'student'){
            $this->response_badRequestWithMessage('Invalid Api call...this api is only for student');
        }

        if(!isset($this->post['project_id'])){
            $this->response_badRequestWithMessage('Project Id Not Provided!');
        }
        $project_id = $this->post['project_id'];

        if(isset($this->post['search_input'])){
            $search_input = $this->post['search_input'];
        } else {
            $search_input = '';
        }

        $reportList = $this->bepmsdb->studentProjectReportListBySearchInput($user_id, $project_id, $search_input);

        $this->response(['data' => $reportList], parent::HTTP_OK);
    }

    //student report details
    public function studentProjectReportDetailsByReportID_post(){
        $tokenResult = $this->verifyJWTToken($this->input->request_headers());

        if(!$tokenResult || !isset($tokenResult->data) || !isset($tokenResult->data->user_id) || !isset($tokenResult->data->login_type)){
            $this->response_badRequestWithMessage('Invalid Token!');
        }

        $data = $tokenResult->data;
        $user_id = $data->user_id; 
        $login_type = $data->login_type;
        $login_as = $data->login_as;

        if($login_type !== 'system' || $login_as !== 'student'){
            $this->response_badRequestWithMessage('Invalid Api call...this api is only for student');
        }

        if(!isset($this->post['report_id'])){
            $this->response_badRequestWithMessage('Report Id Not Provided!');
        }
        $report_id = $this->post['report_id'];

        $reportDetails = $this->bepmsdb->studentProjectReportDetails($user_id, $report_id);

        $this->response(['data' => $reportDetails], parent::HTTP_OK);
    }

    //leader create report
    public function leaderCreateReport_post(){
        $tokenResult = $this->verifyJWTToken($this->input->request_headers());

        if(!$tokenResult || !isset($tokenResult->data) || !isset($tokenResult->data->user_id) || !isset($tokenResult->data->login_type)){
            $this->response_badRequestWithMessage('Invalid Token!');
        }

        $data = $tokenResult->data;
        $user_id = $data->user_id; 
        $login_type = $data->login_type;
        $login_as = $data->login_as;

        if($login_type !== 'system' || $login_as !== 'student'){
            $this->response_badRequestWithMessage('Invalid Api call...this api is only for student');
        }

        if(!isset($this->post['report_title']) || !isset($this->post['report_description']) || 
            !isset($this->post['report_status_claim']) || !isset($this->post['project_id'])){
            $this->response_badRequestWithMessage('All Fields are important!');
        }

        $report_title = $this->post['report_title'];
        $report_description = $this->post['report_description'];
        $report_status_claim = $this->post['report_status_claim'];
        $project_id = $this->post['project_id'];
        //$report_attachments =
        
        $userPositionInProject = $this->bepmsdb->userPositionInProject($user_id, $project_id);

        $flag = false;
        foreach ($userPositionInProject as $value){ 
            if($value->project_position_name === 'leader'){
                $flag = true;
            }
        }

        if(!$flag) $this->response_badRequestWithMessage('Only the project leader can create reports');

        $createReport = $this->bepmsdb->leaderCreateReport($report_title, $report_description, $report_status_claim, $project_id);

        if($createReport){
            $reportAttachmentPath = $this->doReportAttachmentUpload($createReport['report_id']);
            $this->response(['message' => 'successfully created report'], parent::HTTP_OK);
        } else {
            $this->response(['message' => 'operation failed'], parent::HTTP_OK);
        }
    }

    function doReportAttachmentUpload($report_id){
        $report_attachment = 'report_attachment_'.$report_id;
        $config['upload_path']          = 'uploads/reports/';
        $config['allowed_types']        = 'gif|jpg|png|zip|rar|docx|doc';
        $config['max_size']             = 25000;
        $config['file_name']            = $report_attachment;
        $config['overwrite']            = true;
        $config['file_ext_tolower']     = true;
        $this->load->library('upload', $config);

        if (!$this->upload->do_upload('report_attachments')) {
            $this->response_badRequestWithMessage($this->upload->display_errors());
        } else {
            $fileMetaData = $this->upload->data();
            $this->bepmsdb->updateReportAttachmentPath($report_id, $fileMetaData['file_name'], $fileMetaData['file_size']);
        }
    }

    public function leaderDeleteReport_post(){

        $tokenResult = $this->verifyJWTToken($this->input->request_headers());

        if(!$tokenResult || !isset($tokenResult->data) || !isset($tokenResult->data->user_id) || !isset($tokenResult->data->login_type)){
            $this->response_badRequestWithMessage('Invalid Token!');
        }

        $data = $tokenResult->data;
        $user_id = $data->user_id; 
        $login_type = $data->login_type;
        $login_as = $data->login_as;

        if($login_type !== 'system' || $login_as !== 'student'){
            $this->response_badRequestWithMessage('Invalid Api call...this api is only for student');
        }

        if(!isset($this->post['report_id']) || !isset($this->post['user_password'])){
            $this->response_badRequestWithMessage('All Fields are important/n{/treport_id/tuser_password}!');
        }

        $report_id = $this->post['report_id'];
        $user_password = hash("sha256", $this->post['user_password']);
        
        $userPositionInProject = $this->bepmsdb->userPositionInReport($user_id, $report_id);

        if(!$userPositionInProject) $this->response_badRequestWithMessage('You are not a part of this report or the report dosent exist');

        $flag = false;

        foreach ($userPositionInProject as $value){ 
            if($value->project_position_name === 'leader'){
                $flag = true;
            }
        }

        if(!$flag) $this->response_badRequestWithMessage('Only the project leader can create reports');

        $deleteReport = $this->bepmsdb->leaderDeleteReport($user_id, $report_id, $user_password);

        unlink('uploads/reports/'.$deleteReport->report_attachment);

        $this->response(['message' => 'successfully deleted report'], parent::HTTP_OK);
        
    }

    /**
     *    .___         . ,    
     *    [__  _. _.. .|-+-  .
     *    |   (_](_.(_|| | \_|
     *                     ._|    
     */

    //faculty system list

    public function facultySystemList_post(){
        $tokenResult = $this->verifyJWTToken($this->input->request_headers());

        if(!$tokenResult || !isset($tokenResult->data) || !isset($tokenResult->data->user_id) || !isset($tokenResult->data->login_type)){
            $this->response_badRequestWithMessage('Invalid Token!');
        }

        $data = $tokenResult->data;
        $user_id = $data->user_id; 
        $login_type = $data->login_type;
        $login_as = $data->login_as;

        if($login_type !== 'system' || $login_as !== 'faculty'){
            $this->response_badRequestWithMessage('Invalid Api call...this api is only for faculty');
        }

        $systemLists = $this->bepmsdb->facultySystemListByUserID($user_id);

        $this->response(['data' => $systemLists], parent::HTTP_OK);
    }

    //faculty system search reports

    public function facultyHomeProjectAndReports_post(){
        $tokenResult = $this->verifyJWTToken($this->input->request_headers());

        if(!$tokenResult || !isset($tokenResult->data) || !isset($tokenResult->data->user_id) || !isset($tokenResult->data->login_type)){
            $this->response_badRequestWithMessage('Invalid Token!');
        }

        $data = $tokenResult->data;
        $user_id = $data->user_id; 
        $login_type = $data->login_type;
        $login_as = $data->login_as;

        if($login_type !== 'system' || $login_as !== 'faculty'){
            $this->response_badRequestWithMessage('Invalid Api call...this api is only for faculty');
        }

        if(!isset($this->post['user_position'])){
            $this->response_badRequestWithMessage('User Position is Required');
        }

        $system_id = (!isset($this->post['system_id'])) ? $system_id = '' :$system_id = $this->post['system_id'];
        $search_input = (!isset($this->post['search_input'])) ? $search_input = '' :$search_input = $this->post['search_input'];
        $user_position = $this->post['user_position'];

        $systemLists = $this->bepmsdb->facultyHomeProjectAndReportsByUSerIDPositionSystemId($user_id, $user_position, $system_id, $search_input);

        $this->response(['data' => $systemLists], parent::HTTP_OK);

    }

    public function facultyGuideNotiecsList_post(){
        //for later...
    }

    public function facultyCreateGuideNotice_post(){
        //for later
    }

    public function facultySearchedProjectDetails_post(){
        $tokenResult = $this->verifyJWTToken($this->input->request_headers());

        if(!$tokenResult || !isset($tokenResult->data) || !isset($tokenResult->data->user_id) || !isset($tokenResult->data->login_type)){
            $this->response_badRequestWithMessage('Invalid Token!');
        }

        $data = $tokenResult->data;
        $user_id = $data->user_id;
        $login_type = $data->login_type;
        $login_as = $data->login_as;

        if($login_type !== 'system' || $login_as !== 'faculty'){
            $this->response_badRequestWithMessage('Invalid Api call...this api is only for faculty');
        }

        if(!isset($this->post['project_id']) || !isset($this->post['project_position_name'])){
            $this->response_badRequestWithMessage('All fields are important... system_id, project_id, project_position_name');
        }

        $project_id = $this->post['project_id'];
        $project_position_name = $this->post['project_position_name'];

        if(!in_array($project_position_name, ['guide', 'pc', 'hod'])){
            $this->response_badRequestWithMessage('Invalid position name... only faculty positions are allowed');
        }

        $projectDetails = [];

        if(!$this->checkifPositionValid($user_id, $project_id, $project_position_name)){
            $this->response_badRequestWithMessage('Invalid position name... ');
        }

        $projectDetails = $this->bepmsdb->facultyProjectDetails($user_id, $project_id);

        $this->response(['data' => $projectDetails], parent::HTTP_OK);
    }

    public function checkifPositionValid($user_id, $project_id, $project_position_name){
        $result = $this->bepmsdb->userPositionsInProjectId($user_id, $project_id);
        $flag = false;
        foreach ($result as $value) {
            if($value->project_position_name === $project_position_name){
                $flag = true;
            }
        }
        return $flag;
    }

    public function facultyProjectList_post(){
        $tokenResult = $this->verifyJWTToken($this->input->request_headers());

        if(!$tokenResult || !isset($tokenResult->data) || !isset($tokenResult->data->user_id) || !isset($tokenResult->data->login_type)){
            $this->response_badRequestWithMessage('Invalid Token!');
        }

        $data = $tokenResult->data;
        $user_id = $data->user_id;
        $login_type = $data->login_type;
        $login_as = $data->login_as;

        if($login_type !== 'system' || $login_as !== 'faculty'){
            $this->response_badRequestWithMessage('Invalid Api call...this api is only for faculty');
        }

        if(!isset($this->post['system_id'])){
            $this->response_badRequestWithMessage('All fields are important... system_id, project_id, project_position_name');
        }

        $system_id = $this->post['system_id'];
        
        if(!$this->checkFacultyInSystemID($user_id, $system_id)){
            $this->response_badRequestWithMessage('Invalid System ID...');
        }

        $projectLists = $this->bepmsdb->facultyProjectListBySystemID($system_id);

        $this->response(['data' => $projectLists], parent::HTTP_OK);

    }

    function checkFacultyInSystemID($user_id, $system_id){
        $systemList = $this->bepmsdb->checkFacultyInSystemID($user_id, $system_id);
        if($systemList){
            return true;
        } else {
            return false;
        }
    }

    public function facultyReportSearchListsByPositionInSystem_post(){
        $tokenResult = $this->verifyJWTToken($this->input->request_headers());

        if(!$tokenResult || !isset($tokenResult->data) || !isset($tokenResult->data->user_id) || !isset($tokenResult->data->login_type)){
            $this->response_badRequestWithMessage('Invalid Token!');
        }

        $data = $tokenResult->data;
        $user_id = $data->user_id;
        $login_type = $data->login_type;
        $login_as = $data->login_as;

        if($login_type !== 'system' || $login_as !== 'faculty'){
            $this->response_badRequestWithMessage('Invalid Api call...this api is only for faculty');
        }

        if(!isset($this->post['system_id']) || !isset($this->post['project_position_name']) || !isset($this->post['search_input'])){
            $this->response_badRequestWithMessage('All fields are important... system_id, project_position_name, Search Input');
        }

        $project_position_name = $this->post['project_position_name'];
        $system_id = $this->post['system_id'];
        if(!$this->checkFacultyPositionInSystemID($user_id, $system_id, $project_position_name)){
            $this->response_badRequestWithMessage('Invalid System ID or project position');
        }

        $search_input = $this->post['search_input'];

        $reportsList = $this->bepmsdb->facultyReportSearchListsByPositionInSystem_post($user_id, $system_id, $project_position_name, $search_input);

        $this->response(['data' => $reportsList], parent::HTTP_OK);
    }

    function checkFacultyPositionInSystemID($user_id, $system_id, $project_position_name){
        $systemList = $this->bepmsdb->checkFacultyPositionInSystemID($user_id, $system_id, $project_position_name);
        if($systemList){
            return true;
        } else {
            return false;
        }
    }

    public function searchedReportDetails_post(){
        $tokenResult = $this->verifyJWTToken($this->input->request_headers());

        if(!$tokenResult || !isset($tokenResult->data) || !isset($tokenResult->data->user_id) || !isset($tokenResult->data->login_type)){
            $this->response_badRequestWithMessage('Invalid Token!');
        }

        $data = $tokenResult->data;
        $user_id = $data->user_id;
        $login_type = $data->login_type;
        $login_as = $data->login_as;

        if($login_type !== 'system' || $login_as !== 'faculty'){
            $this->response_badRequestWithMessage('Invalid Api call...this api is only for faculty');
        }

        if(!isset($this->post['system_id']) || !isset($this->post['project_position_name']) || !isset($this->post['report_id'])){
            $this->response_badRequestWithMessage('All fields are important... system_id, project_position_name, Search Input');
        }

        $project_position_name = $this->post['project_position_name'];
        $system_id = $this->post['system_id'];
        $report_id = $this->post['report_id'];

        if(!$this->checkFacultyPositionInSystemID($user_id, $system_id, $project_position_name)){
            $this->response_badRequestWithMessage('Invalid System ID or project position');
        }

        $reportDetails = $this->bepmsdb->studentProjectReportDetails($user_id, $report_id);

        $this->response(['data' => $reportDetails], parent::HTTP_OK);
    }

    public function facultyApproveReport_post(){

        $tokenResult = $this->verifyJWTToken($this->input->request_headers());

        if(!$tokenResult || !isset($tokenResult->data) || !isset($tokenResult->data->user_id) || !isset($tokenResult->data->login_type)){
            $this->response_badRequestWithMessage('Invalid Token!');
        }

        $data = $tokenResult->data;
        $user_id = $data->user_id;
        $login_type = $data->login_type;
        $login_as = $data->login_as;

        if($login_type !== 'system' || $login_as !== 'faculty'){
            $this->response_badRequestWithMessage('Invalid Api call...this api is only for faculty');
        }

        if(!isset($this->post['system_id']) || !isset($this->post['project_position_name']) || !isset($this->post['report_id'])){
            $this->response_badRequestWithMessage('All fields are important... system_id, project_position_name, report id, ');
        }

        $project_position_name = $this->post['project_position_name'];
        $system_id = $this->post['system_id'];
        $report_id = $this->post['report_id'];

        if(!$this->checkFacultyResponseToReportValid($user_id, $report_id, $project_position_name)){
            $this->response_badRequestWithMessage('please make sure if the report is pending for any operatioin or please check your credentials');
        }

        $reportsList = $this->bepmsdb->facultyApproveReportID($user_id, $report_id ,$project_position_name);

        if($reportsList){
            //send notification
            $from_email = "support@zapy.tech";
            $to_email = "nickpt.0699@gmail.com";
            //Load email library
            $this->load->library('email');
            $this->email->from($from_email, 'ZapyTech');
            $this->email->to($to_email);
            $this->email->subject('Send Email Codeigniter');
            $this->email->message('The email send using codeigniter library');
            if($this->email->send()){
                echo 'email send successfully!!';
            } else {
                echo 'email send failed!';
            }
            $this->response(['message' => 'successfully approves the report'], parent::HTTP_OK);
        }
        $this->response(['message' => 'failed to approves the report'], parent::HTTP_OK);
    }

    public function facultyDisapproveReport_post(){

        $tokenResult = $this->verifyJWTToken($this->input->request_headers());

        if(!$tokenResult || !isset($tokenResult->data) || !isset($tokenResult->data->user_id) || !isset($tokenResult->data->login_type)){
            $this->response_badRequestWithMessage('Invalid Token!');
        }

        $data = $tokenResult->data;
        $user_id = $data->user_id;
        $login_type = $data->login_type;
        $login_as = $data->login_as;

        if($login_type !== 'system' || $login_as !== 'faculty'){
            $this->response_badRequestWithMessage('Invalid Api call...this api is only for faculty');
        }

        if(!isset($this->post['system_id']) || !isset($this->post['project_position_name']) || !isset($this->post['report_id']) || !isset($this->post['report_disapproved_reason'])){
            $this->response_badRequestWithMessage('All fields are important... system_id, project_position_name, report id, ');
        }

        $project_position_name = $this->post['project_position_name'];
        $system_id = $this->post['system_id'];
        $report_id = $this->post['report_id'];
        $report_disapproved_reason = $this->post['report_disapproved_reason'];
       

        if(!$this->checkFacultyResponseToReportValid($user_id, $report_id, $project_position_name)){
            $this->response_badRequestWithMessage('please make sure if the report is pending for any operatioin or please check your credentials');
        }

        $reportsList = $this->bepmsdb->facultyDisapproveReportID($user_id, $report_id ,$project_position_name, $report_disapproved_reason);

        if($reportsList){
            //send notification
            $this->sendEmail('21nikhilpatil1998@gmail.com');
            $this->response(['message' => 'successfully approves the report'], parent::HTTP_OK);
        }
        $this->response(['message' => 'failed to approves the report'], parent::HTTP_OK);
    }

    public function facultyAssignChangesReport_post(){

        $tokenResult = $this->verifyJWTToken($this->input->request_headers());

        if(!$tokenResult || !isset($tokenResult->data) || !isset($tokenResult->data->user_id) || !isset($tokenResult->data->login_type)){
            $this->response_badRequestWithMessage('Invalid Token!');
        }

        $data = $tokenResult->data;
        $user_id = $data->user_id;
        $login_type = $data->login_type;
        $login_as = $data->login_as;

        if($login_type !== 'system' || $login_as !== 'faculty'){
            $this->response_badRequestWithMessage('Invalid Api call...this api is only for faculty');
        }

        if(!isset($this->post['system_id']) || !isset($this->post['project_position_name']) || !isset($this->post['report_id']) || !isset($this->post['report_disapproved_reason'])){
            $this->response_badRequestWithMessage('All fields are important... system_id, project_position_name, report id, ');
        }

        $project_position_name = $this->post['project_position_name'];
        $system_id = $this->post['system_id'];
        $report_id = $this->post['report_id'];
        $report_change_assign = $this->post['report_change_assign'];
       

        if(!$this->checkFacultyResponseToReportValid($user_id, $report_id, $project_position_name)){
            $this->response_badRequestWithMessage('please make sure if the report is pending for any operatioin or please check your credentials');
        }

        $reportsList = $this->bepmsdb->facultyAssignChangesReportID($user_id, $report_id ,$project_position_name, $report_change_assign);

        if($reportsList){
            //send notification
            $this->sendEmail('21nikhilpatil1998@gmail.com','Your Report has been approved!', 'your hod has approved you report');
            $this->response(['message' => 'successfully approves the report'], parent::HTTP_OK);
        }
        $this->response(['message' => 'failed to approves the report'], parent::HTTP_OK);
    }

    function checkFacultyResponseToReportValid($user_id, $report_id, $project_position_name){
        $userPositions = $this->bepmsdb->checkFacultyResponseToReportValid($user_id, $report_id, $project_position_name);
        if($userPositions){
            return true;
        } else {
            return false;
        }
    }

    public function getUserProfileDetails_post(){
        $tokenResult = $this->verifyJWTToken($this->input->request_headers());

        if(!$tokenResult || !isset($tokenResult->data) || !isset($tokenResult->data->user_id) || !isset($tokenResult->data->login_type)){
            $this->response_badRequestWithMessage('Invalid Token!');
        }

        $data = $tokenResult->data;
        $user_id = $data->user_id;

        $data = $this->bepmsdb->userProfileDetails($user_id);

        $this->response(['data' => $data], parent::HTTP_OK);
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
