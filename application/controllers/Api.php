<?php   defined('BASEPATH') OR exit('No direct script access allowed');

header('Access-Control-Allow-Origin: *');

if($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
	header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
	header('Access-Control-Allow-Headers: Content-Type');
	exit;
}

require APPPATH . 'libraries/REST_Controller.php';

class Api extends REST_Controller {

    function __construct() {
        parent::__construct();
        $this->methods['login_authenticate_post']['limit'] = 50; // 50 requests per hour per user/key
        $this->post = $_REQUEST;
        $this->load->helper(['jwt', 'authorization']);
        $this->load->model('bepms'); 
    }

    function response_badRequestWithMessage($message, $status = parent::HTTP_BAD_REQUEST){
        $this->response([
            'status' => 'failed',
            'message' => $message
        ], $status);
    }

    function createNotificationxxx_post(){
        $this->createNotification($this->post['from_user_id'], $this->post['to_user_id'], $this->post['notification_message'], $this->post['project_id']);
    }

    function createNotification($from_user_id, $to_user_id, $message, $project_id){
        $from_user = $this->userNameEmaiByUserId($from_user_id);
        $notification_message = '';
        $notification_title = '';
        $from_user_name = '';
        if($from_user){
            $from_user = $from_user[0];
            $from_user_name = $from_user->user_display_name;
        }
        switch($message){
            case 'newReport':
                $notification_message = 'You have a new report by '.$from_user_name;
                break;
            case 'approvedReport':
                $notification_message = 'Your report has been approved by '.$from_user_name;
                break;
            case 'disapproveReport':
                $notification_message = 'Your report has been disapproved by '.$from_user_name;
                break;
            case 'assignModification':
                $notification_message = 'Your report has been assigned for modification by'.$from_user_name;
                break;
            case 'modifiedReport':
                $notification_message = 'Your changes assigned report has been send by'.$from_user_name;
            default :
                exit;
        }

        $this->bepms->createNotification($from_user_id, $to_user_id, $notification_message, $project_id);
        $this->sendEmail($this->userNameEmaiByUserId($to_user_id), $notification_message);
    }

    function userNameEmaiByUserId($user_id){
        return $this->bepms->userNameEmaiByUserId($user_id);
    }

    function sendEmail($receiver ,$message){
        $bepms_logo_source = 'http://projects.zapy.tech/bepms/static/media/bepms-logo.34f0fe1e.png';
        $receiver_display_name = '';
        $receiver_email = '';
        if($receiver){
            $receiver = $receiver[0];
            $receiver_display_name = $receiver->user_display_name;
            $receiver_email = $receiver->user_email;
        } else {
            echo 'fuck happen!';
        }
        $html = '
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <div>
            <div style="margin: 20px; text-align: center;">
                <img src="'.$bepms_logo_source.'" alt="bepms" width="65">
            </div>
            
            <div style="margin: 10px;font-size:16px;">
                <div>'.$receiver_display_name.', '.$message.'</div>
                <div style="padding-top: 30px;padding-bottom: 20px;margin-top: 80px;">
                    <a href="http://projects.zapy.tech/bepms/">
                        <div style="margin-right: 5px; padding:5px 10px; border:1px solid #4DA1FF; text-align: center; display: inline; background-color: #4DA1FF; border-radius: 5px;color: #fff;">
                            Go to bepms
                        </div>
                    </a>
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
                    <p style="font-size:11px; color: #999; font-weight:normal" >This message was sent to '.$receiver_email.'. If you dont\'t want to receive these emails from BEPMS in the future, please disable the notifications in the user profile settings page</p>
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
        $this->email->to($receiver_email);
        $this->email->subject('[BEPMS] You have 1 new notification');
        $this->email->message($message);
        if($this->email->send()){
            echo('email send successfully to '.$receiver_email);
        } else {
            echo('email send failed!');
        }
    }

    public function updateUserProfile_post(){
        $tokenResult = $this->verifyJWTToken($this->post['Authorization']);

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
            $response = $this->bepms->updateUserProfile($user_id, $update);
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
        //$url = $url_parts['scheme'] . '://' . str_replace('www.', '', $url_parts['host']);
        $url = 'www.zapy.tech';
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
            if (!empty($headers)) {
                $data = AUTHORIZATION::validateToken($headers);
                $decodedToken = AUTHORIZATION::validateTimestamp($headers);
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

    public function verifyJWTToken_post(){//$this->post['search_input']
        $tokenResult = $this->verifyJWTToken($this->post['Authorization']);
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
            $this->response_badRequestWithMessage('Credentials not provided!', 401);
        }

        $userEmail = $this->post['email'];
        if(!valid_email($userEmail)) {
            $this->response_badRequestWithMessage('Invalid Email Address',401);
        }

        $password = hash("sha256", $this->post['password']);
        $login_as = $this->post['login_as']; //student, faculty

        if($login_as === 'faculty'){
            $result = $this->bepms->systemFacultyLoginAuthentication($userEmail, $password);
        } else if ($login_as === 'student'){
            $result = $this->bepms->systemStudentLoginAuthentication($userEmail, $password);
        } else {
            $this->response_badRequestWithMessage('Invalid Login Type', 401);
        }
        
        if(!$result){
            $this->response_badRequestWithMessage('No User Found', 401);
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
        $tokenResult = $this->verifyJWTToken($this->post['Authorization']);

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

        $project_lists = $this->bepms->studentProjectListByUserID($user_id);

        $this->response(['data' => $project_lists], parent::HTTP_OK);

    }

    //student project details
    public function studentProjectDetails_post(){
        $tokenResult = $this->verifyJWTToken($this->post['Authorization']);

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

        $projectDetails = $this->bepms->studentProjectDetailsByUserID($user_id, $project_id);

        $this->response(['data' => $projectDetails], parent::HTTP_OK);
    }

    //student report search list
    public function studentProjectReportListBySearchInput_post(){
        $tokenResult = $this->verifyJWTToken($this->post['Authorization']);

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

        $reportList = $this->bepms->studentProjectReportListBySearchInput($user_id, $project_id, $search_input);

        $this->response(['data' => $reportList], parent::HTTP_OK);
    }

    //student report details
    public function studentProjectReportDetailsByReportID_post(){
        $tokenResult = $this->verifyJWTToken($this->post['Authorization']);

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

        $reportDetails = $this->bepms->studentProjectReportDetails($user_id, $report_id);

        $this->response(['data' => $reportDetails], parent::HTTP_OK);
    }

    //leader create report
    public function leaderCreateReport_post(){
        $tokenResult = $this->verifyJWTToken($this->post['Authorization']);

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
        
        $userPositionInProject = $this->bepms->userPositionInProject($user_id, $project_id);

        $flag = false;
        foreach ($userPositionInProject as $value){ 
            if($value->project_position_name === 'leader'){
                $flag = true;
            }
        }

        if(!$flag) $this->response_badRequestWithMessage('Only the project leader can create reports');

        $createReport = $this->bepms->leaderCreateReport($report_title, $report_description, $report_status_claim, $project_id);

        if($createReport){
            $reportAttachmentPath = $this->doReportAttachmentUpload($createReport['report_id']);

            $guide_user = $this->getUserIdByProjectIdPositionName($project_id, 'guide');
            $guide_user_id = $guide_user->user_id;
            $notification = $this->createNotification($user_id, $guide_user_id, 'newReport', $project_id);

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
            $this->bepms->updateReportAttachmentPath($report_id, $fileMetaData['file_name'], $fileMetaData['file_size']);
        }
    }

    public function leaderDeleteReport_post(){

        $tokenResult = $this->verifyJWTToken($this->post['Authorization']);

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
        
        $userPositionInProject = $this->bepms->userPositionInReport($user_id, $report_id);

        if(!$userPositionInProject) $this->response_badRequestWithMessage('You are not a part of this report or the report dosent exist');

        $flag = false;

        foreach ($userPositionInProject as $value){ 
            if($value->project_position_name === 'leader'){
                $flag = true;
            }
        }

        if(!$flag) $this->response_badRequestWithMessage('Only the project leader can create reports');

        $deleteReport = $this->bepms->leaderDeleteReport($user_id, $report_id, $user_password);

        unlink('uploads/reports/'.$deleteReport->report_attachment);

        $this->response(['message' => 'successfully deleted report'], parent::HTTP_OK);
        
    }

    public function leaderEditProjectDetails_post(){
        $tokenResult = $this->verifyJWTToken($this->post['Authorization']);

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

        $update = [];
        if(!isset($this->post['project_id'])){
            $this->response_badRequestWithMessage('project_id not set!');
        }
        $project_id = $this->post['project_id'];
        
        if(isset($this->post['project_name'])){
            $update['project_name'] = $this->post['project_name'];
        }
        if($this->post['project_description']){
            $update['project_description'] = $this->post['project_description'];
        }

        $userPositionInProject = $this->bepms->userPositionInProject($user_id, $project_id);

        $flag = false;
        foreach ($userPositionInProject as $value){ 
            if($value->project_position_name === 'leader'){
                $flag = true;
            }
        }

        if(!$flag) $this->response_badRequestWithMessage('Only the project leader can create reports');

        $updateProjectDetails = $this->bepms->leaderEditProjectDetails($project_id, $update);
        if($updateProjectDetails){
            $this->response(['message' => 'successfully updated project', 'data' => $updateProjectDetails], parent::HTTP_OK);
        } else {
            $this->response(['message' => 'operation failed'], parent::HTTP_OK);
        }
    }

    function getUserIdByProjectIdPositionName($project_id, $project_position_name){
        return $this->bepms->getUserIdByProjectIdPositionName($project_id, $project_position_name);
    }

















    /**
     *    .___         . ,    
     *    [__  _. _.. .|-+-  .
     *    |   (_](_.(_|| | \_|
     *                     ._|    
     */

    //faculty system list

    public function facultySystemList_post(){
        $tokenResult = $this->verifyJWTToken($this->post['Authorization']);

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

        $systemLists = $this->bepms->facultySystemListByUserID($user_id);

        $this->response(['data' => $systemLists], parent::HTTP_OK);
    }

    //faculty system search reports

    public function facultyHomeProjectAndReports_post(){
        $tokenResult = $this->verifyJWTToken($this->post['Authorization']);

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

        $systemLists = $this->bepms->facultyHomeProjectAndReportsByUSerIDPositionSystemId($user_id, $user_position, $system_id, $search_input);

        $this->response(['data' => $systemLists], parent::HTTP_OK);

    }

    public function facultyGuideNotiecsList_post(){
        //for later...
    }

    public function facultyCreateGuideNotice_post(){
        //for later...
    }

    public function facultySearchedProjectDetails_post(){
        $tokenResult = $this->verifyJWTToken($this->post['Authorization']);

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

        $projectDetails = $this->bepms->facultyProjectDetails($user_id, $project_id);

        $this->response(['data' => $projectDetails], parent::HTTP_OK);
    }

    public function checkifPositionValid($user_id, $project_id, $project_position_name){
        $result = $this->bepms->userPositionsInProjectId($user_id, $project_id);
        $flag = false;
        foreach ($result as $value) {
            if($value->project_position_name === $project_position_name){
                $flag = true;
            }
        }
        return $flag;
    }

    public function facultyProjectList_post(){
        $tokenResult = $this->verifyJWTToken($this->post['Authorization']);

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

        $projectLists = $this->bepms->facultyProjectListBySystemID($system_id);

        $this->response(['data' => $projectLists], parent::HTTP_OK);

    }

    function checkFacultyInSystemID($user_id, $system_id){
        $systemList = $this->bepms->checkFacultyInSystemID($user_id, $system_id);
        if($systemList){
            return true;
        } else {
            return false;
        }
    }

    public function facultyReportSearchListsByPositionInSystem_post(){
        $tokenResult = $this->verifyJWTToken($this->post['Authorization']);

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

        $reportsList = $this->bepms->facultyReportSearchListsByPositionInSystem_post($user_id, $system_id, $project_position_name, $search_input);

        $this->response(['data' => $reportsList], parent::HTTP_OK);
    }

    function checkFacultyPositionInSystemID($user_id, $system_id, $project_position_name){
        $systemList = $this->bepms->checkFacultyPositionInSystemID($user_id, $system_id, $project_position_name);
        if($systemList){
            return true;
        } else {
            return false;
        }
    }

    public function searchedReportDetails_post(){
        $tokenResult = $this->verifyJWTToken($this->post['Authorization']);

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

        $reportDetails = $this->bepms->studentProjectReportDetails($user_id, $report_id);

        $this->response(['data' => $reportDetails], parent::HTTP_OK);
    }

    public function facultyApproveReport_post(){

        $tokenResult = $this->verifyJWTToken($this->post['Authorization']);

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

        $reportsList = $this->bepms->facultyApproveReportID($user_id, $report_id ,$project_position_name);

        if($reportsList){
            $x = ($project_position_name === 'guide') ? 'pc' : ($project_position_name === 'pc') ? 'hod' : '';
            $nxt_user = $this->getUserIdByProjectIdPositionName($project_id, $x);
            $nxt_user_id = $nxt_user->user_id;
            $notification = $this->createNotification($user_id, $nxt_user_id, 'approveReport', $project_id);
            $this->response(['message' => 'successfully approves the report'], parent::HTTP_OK);
        }
        $this->response(['message' => 'failed to approves the report'], parent::HTTP_OK);
    }

    public function facultyDisapproveReport_post(){

        $tokenResult = $this->verifyJWTToken($this->post['Authorization']);

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

        $reportsList = $this->bepms->facultyDisapproveReportID($user_id, $report_id ,$project_position_name, $report_disapproved_reason);

        if($reportsList){

            $x = ($project_position_name === 'guide') ? 'pc' : ($project_position_name === 'pc') ? 'hod' : '';
            $nxt_user = $this->getUserIdByProjectIdPositionName($project_id, $x);
            $nxt_user_id = $nxt_user->user_id;
            $notification = $this->createNotification($user_id, $nxt_user_id, 'approveReport', $project_id);

            $this->response(['message' => 'successfully approves the report'], parent::HTTP_OK);
        }
        $this->response(['message' => 'failed to approves the report'], parent::HTTP_OK);
    }

    public function facultyAssignChangesReport_post(){

        $tokenResult = $this->verifyJWTToken($this->post['Authorization']);

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

        if(!isset($this->post['system_id']) || !isset($this->post['project_position_name']) || !isset($this->post['report_id']) || !isset($this->post['report_change_assign'])){
            $this->response_badRequestWithMessage('All fields are important... system_id, project_position_name, report id, ');
        }

        $project_position_name = $this->post['project_position_name'];
        $system_id = $this->post['system_id'];
        $report_id = $this->post['report_id'];
        $report_change_assign = $this->post['report_change_assign'];
       

        if(!$this->checkFacultyResponseToReportValid($user_id, $report_id, $project_position_name)){
            $this->response_badRequestWithMessage('please make sure if the report is pending for any operatioin or please check your credentials');
        }

        if($project_position_name !== 'guide'){$this->response_badRequestWithMessage('only guide can assign changes');}

        $reportsList = $this->bepms->facultyAssignChangesReportID($user_id, $report_id ,$project_position_name, $report_change_assign);

        if($reportsList){
            $x = ($project_position_name === 'guide') ? 'pc' : ($project_position_name === 'pc') ? 'hod' : '';
            $nxt_user = $this->getUserIdByProjectIdPositionName($project_id, $x);
            $nxt_user_id = $nxt_user->user_id;
            $notification = $this->createNotification($user_id, $nxt_user_id, 'modifyReport', $project_id);

            $this->response(['message' => 'successfully approves the report'], parent::HTTP_OK);
        }
        $this->response(['message' => 'failed to approves the report'], parent::HTTP_OK);
    }

    function checkFacultyResponseToReportValid($user_id, $report_id, $project_position_name){
        $userPositions = $this->bepms->checkFacultyResponseToReportValid($user_id, $report_id, $project_position_name);
        if($userPositions){
            return true;
        } else {
            return false;
        }
    }

    public function getUserProfileDetails_post(){
        $tokenResult = $this->verifyJWTToken($this->post['Authorization']);

        if(!$tokenResult || !isset($tokenResult->data) || !isset($tokenResult->data->user_id) || !isset($tokenResult->data->login_type)){
            $this->response_badRequestWithMessage('Invalid Token!');
        }

        $data = $tokenResult->data;
        $user_id = $data->user_id;

        $data = $this->bepms->userProfileDetails($user_id);

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
        
        $result = $this->bepms->adminLoginAuthentication($userEmail, $password);

        if(!$result){
            $this->response(['message' => 'Invalid credentials'], parent::HTTP_OK);
        } else {

            //<-code to update admin last login by user_id

            $userId = $result->user_id;
            $token = $this->createToken($userId, $userEmail, $type = 'admin', $login_as = 'admin');

            $this->response([
                'status' => 'success',
                "message" => "Successfully logged in ",
                'data' => [
                    'access_token' => $token,
                    'token_type' => 'jwt',
                    'expiry' => date('Y/m/d H:i:s', now() + 604800),
                    'login_type' => 'admin'
                ]
            ], parent::HTTP_OK);
        }
    }




    public function adminSystemListBySearchInput_post(){

        $tokenResult = $this->verifyJWTToken($this->post['Authorization']);

        if(!$tokenResult || !isset($tokenResult->data) || !isset($tokenResult->data->user_id) || !isset($tokenResult->data->login_type)){
            $this->response_badRequestWithMessage('Invalid Token!');
        }

        $data = $tokenResult->data;
        $user_id = $data->user_id; 
        $login_type = $data->login_type;
        $login_as = $data->login_as;

        echo '';

        if($login_type !== 'admin' || $login_as !== 'admin'){
            $this->response_badRequestWithMessage('Invalid Api call...this api is only for admin');
        }

        if(isset($this->post['search_input'])){
            $search_input = $this->post['search_input'];
        } else {
            $search_input = '';
        }

        $systemList = $this->bepms->adminSystemListBySearchInput($user_id, $search_input);

        $this->response(['data' => $systemList], parent::HTTP_OK);
    }

    public function adminSystemProjects_post() {   
        $tokenResult = $this->verifyJWTToken($this->post['Authorization']);

        if(!$tokenResult || !isset($tokenResult->data) || !isset($tokenResult->data->user_id) || !isset($tokenResult->data->login_type)){
            $this->response_badRequestWithMessage('Invalid Token!');
        }

        $data = $tokenResult->data;
        $user_id = $data->user_id; 
        $login_type = $data->login_type;
        $login_as = $data->login_as;
        if($login_type !== 'admin' || $login_as !== 'admin'){
            $this->response_badRequestWithMessage('Invalid Api call...this api is only for admin');
        }
        
        $system_id = $this->post['system_id'];

        $projectList = $this->bepms->adminProjectListBySystemId($user_id, $system_id);

        $this->response(['data' => $projectList], parent::HTTP_OK);
    }

    public function adminCreateNewSystem_post() {
        $tokenResult = $this->verifyJWTToken($this->post['Authorization']);

        if(!$tokenResult || !isset($tokenResult->data) || !isset($tokenResult->data->user_id) || !isset($tokenResult->data->login_type)){
            $this->response_badRequestWithMessage('Invalid Token!');
        }

        $data = $tokenResult->data;
        $user_id = $data->user_id; 
        $login_type = $data->login_type;
        $login_as = $data->login_as;
        if($login_type !== 'admin' || $login_as !== 'admin'){
            $this->response_badRequestWithMessage('Invalid Api call...this api is only for admin');
        }

        $system_name = $this->post['system_name'];
        $system_description = $this->post['system_description'];
        $getAdminIdByUserId = $this->getAdminIdByUserId($user_id);

        if(!$getAdminIdByUserId){return $this->response_badRequestWithMessage('You are not regestered as admin');}

        $createSystem = $this->bepms->adminCreateNewSystemByDetails($user_id, $system_name, $system_description);

        $this->response(['status' => 'success', 'data' => $createSystem], parent::HTTP_OK);
    }

    function getAdminIdByUserId($user_id){
        $admin_id = $this->bepms->getAdminIdByUserId($user_id);
        return $admin_id;
    }

    public function adminAddProjectToSystem_post() {
        $tokenResult = $this->verifyJWTToken($this->post['Authorization']);
        $this->load->helper('email');
        if(!$tokenResult || !isset($tokenResult->data) || !isset($tokenResult->data->user_id) || !isset($tokenResult->data->login_type)){
            $this->response_badRequestWithMessage('Invalid Token!');
        }

        $data = $tokenResult->data;
        $user_id = $data->user_id; 
        $login_type = $data->login_type;
        $login_as = $data->login_as;
        if($login_type !== 'admin' || $login_as !== 'admin'){
            $this->response_badRequestWithMessage('Invalid Api call...this api is only for admin');
        }
        $admin_id = $this->getAdminIdByUserId($user_id);
        $admin_id = $admin_id[0]->admin_id;

        $system_id = isset($this->post['system_id']) ? $this->post['system_id'] : '';

        if(!$this->bepms->adminSystemListBySystemId($admin_id, $system_id))
             $this->response_badRequestWithMessage('invalid system_id...admin is not assigned to any such system.');
        
        //here the system id && admin_id is valid
        $hod_email = isset($this->post['hod_email']) ? $this->post['hod_email'] : '';
        $pc_email = isset($this->post['pc_email']) ? $this->post['pc_email'] : '';
        $guide_email = isset($this->post['guide_email']) ? $this->post['guide_email'] : '';
        $leader_email = isset($this->post['leader_email']) ? $this->post['leader_email'] : '';
        $member_email = isset($this->post['member_email']) ? $this->post['member_email'] : [];
        $member_email = explode(', ', $member_email);
        $update = [];

        $flag = false;
        if($hod_email && valid_email($hod_email)) $update['hod'] = $hod_email; else $flag = true;
        if($pc_email && valid_email($pc_email)) $update['pc'] = $pc_email; else $flag = true;
        if($guide_email && valid_email($guide_email)) $update['guide'] = $guide_email; else $flag = true;
        if($leader_email && valid_email($leader_email)) $update['leader'] = $leader_email; else $flag = true;
        $update_member = [];
        foreach ($member_email as $email){ 
            if(valid_email($email)){
                array_push($update_member, $email);
            } else {
                $flag = true;
            }
        }
        $update['member'] = $update_member;
        if($flag) $this->response_badRequestWithMessage('Invalid email provided');
        //there the email fields are valid

        //NOW WE NEED TO CREATE A PROJECT (with just passing system_id)
        $project_id = $this->bepms->createNewProject($system_id);
        if(!$project_id) {
            $this->response_badRequestWithMessage('Error While creating new project');
        }
        
        //now that we have project_id we can insert inot bepms_project_member_positions table
        if($update && $system_id){
            foreach ($update as $project_position_name => $user_email) {
                if($project_position_name === 'member'){
                    foreach ($user_email as $member_email){
                        $update_submit = $this->bepms->insertNewProjectPositionByUserEmail($project_id, $member_email, $project_position_name);
                    }
                } else {
                    $update_submit = $this->bepms->insertNewProjectPositionByUserEmail($project_id, $user_email, $project_position_name);
                }
            }
            return $this->response(['status' => 'successfully created list'], parent::HTTP_OK);
        }

        function checkAdminSystemIdValid($admin_id, $system_id){
            return $this->bepms->adminSystemListBySystemId($admin_id, $system_id);
        }


        // if(!$getAdminIdByUserId){return $this->response_badRequestWithMessage('You are not regestered as admin');}

        // $createSystem = $this->bepms->adminCreateNewSystemByDetails($user_id, $system_name, $system_description);

        // $this->response(['status' => 'success', 'data' => $createSystem], parent::HTTP_OK);
    }

}