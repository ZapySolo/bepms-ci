<?php

defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Implements helper functions for grabbing information from the MyBB install.
 *
 * Can be configured by modifying application/config/mybb.php
 */
class BepmsDB extends CI_Model {

	/**
	 * Constructor.
	 * Establish database connection, if appropriate.
	 */
	public function __construct() {
        parent::__construct();
		$this->load->database();
	}
	
	//--------------------- U S E R T A B L E -------------------------------
	
    /**
	 * Create a new user
	 *
	 * @param int $limit
	 * @param string $userEmail
	 * @param object $currentTimestamp
	 * @return boolean
	 */

    public function createNewUser($userEmail) {

        $data = array(
            'user_id' => NULL,
            'user_first_name' => NULL,
            'user_last_name' => NULL,
            'user_email' => $userEmail,
            'user_password' => '',
            'user_display_name' => '',
            'user_creation_date' => date("Y-m-d H:i:s"),
            'user_profile_image' => '',
		);
        
		$this->db->insert('bepms_users', $data);

		$last_id = $this->db->insert_id();

		return ($this->db->affected_rows() != 1) ? [] : ['report_id' => $last_id];

		//OR
		//$this->db->trans_start();
		//$this->db->query....<<query>>
		//$this->db->trans_complete();
		//if (!$this->db->trans_status()) {query failed}

        //how to call
        //$this->load->model('bepmsdb');
        //$this->bepmsdb->createNewUser($userEmail);
	}


    /**
	 * Check if the user exist by email
	 * 
	 * @param string $userEmail
	 * @return boolean
	 */

    public function checkUserEmailExist($userEmail) {

        $where = array(
            'user_email' => $userEmail, 
		);
        
		$query = $this->db->get_where('bepms_users', $where);
		$result = $query->result();
		return $result;
	}

	//----------------- S Y S T E M -------------------------

	/**
	 * Create a new System
	 * @param int $limit
	 * @param string $userEmail
	 * @param object $currentTimestamp
	 * @return boolean
	 */

	public function checkSystemExist($systemName){
		$where = array(
            'system_name' => $systemName, 
		);
		$query = $this->db->get_where('bepms_systems', $where);
		$result = $query->result();
		return $result;
	}

	public function createNewSystem($systemName){
        $data = array(
			'admin_id' => 2, // <-provide admin id here
            'system_name' => $systemName,
            'system_creation_date' => date("Y-m-d H:i:s"),
		);
		$this->db->insert('bepms_systems', $data);
		return ($this->db->affected_rows() != 1) ? false : true;
	}

	/**
	 * @param string $email
	 * @param string $password
	 * @return boolean
	 */
					//error prove query but works
	public function systemLoginAuthentication($email, $password){
		$this->db->select('*');
		$this->db->from('bepms_users');
		$this->db->join('bepms_project_member_positions', 'bepms_users.user_id = bepms_project_member_positions.user_id');
		$this->db->where(
			array(
				'bepms_users.user_email' => $email,
				'bepms_users.user_password' => $password
			)
		);
		//echo $this->db->get_compiled_select();
		$query = $this->db->get()-> result();

		return (sizeof($query) > 0) ? $query[0] : false;
	}

	//list of all the system assigned to the user
	//expected output system_id, position_name[], latest_system_id, system_name
	public function systemListByUserID($userId){
		$this->db->select('bs.system_id, bpp.project_position_name, bs.system_creation_date, bp.project_id');
		$this->db->from('bepms_systems as bs');
		$this->db->join('bepms_projects as bp', 'bs.system_id = bp.system_id');
		$this->db->join('bepms_project_member_positions as bpmp', 'bp.project_id = bpmp.project_id');
		$this->db->join('bepms_project_positions as bpp', 'bpp.project_position_id = bpmp.project_position_id');
		$this->db->where(
			array(
				'bpmp.user_id' => $userId
			)
		);
		//echo $this->db->get_compiled_select();
		$query = $this->db->get()-> result();
		return $query;
	}

	/**
	 * Project Details
	 * Used by Student Login for homepage details
	 */

	public function projectNoticeByUserID($user_id, $project_id){
		$this->db->select('from_user_id, notice_message	, bnl.notice_level_text');
		$this->db->from('bepms_notices as bno');
		$this->db->join('bepms_users as bu', 'bu.user_id = bno.to_user_id');
		$this->db->join('bepms_projects as bp', 'bno.project_id	= bp.project_id');
		$this->db->join('bepms_notice_levels as bnl', 'bno.notice_level = bnl.notice_level');
		$this->db->where(
			array(
				'bu.user_id' => $user_id,
				'bno.project_id' => $project_id
			)
		);
		//echo $this->db->get_compiled_select();
		$query = $this->db->get()-> result();
		return $query;
	}

	public function projectNotificationByUserID($user_id, $project_id){
		$this->db->select('bnt.notification_message, bnt.notification_checked_status, bnt.notification_creation_date, bnt.notification_id, bnt.from_user_id, bu.user_display_name as from_user_display_name');
		$this->db->from('bepms_notifications as bnt');
		$this->db->join('bepms_projects as bp', 'bnt.project_id	= bp.project_id');
		$this->db->join('bepms_users as bu', 'bu.user_id = bnt.from_user_id');
		$this->db->where(
			array(
				'bnt.to_user_id' => $user_id,
				'bp.project_id' => $project_id,
				'bnt.notification_checked_status' => 0
			)
		);
		//echo $this->db->get_compiled_select();
		$query = $this->db->get()-> result();

		return $query;
	}

	public function projectMemberByProjectID($project_id){
		$this->db->select('bu.user_display_name, bu.user_profile_image, bpp.project_position_name');
		$this->db->from('bepms_users as bu');
		$this->db->join('bepms_project_member_positions as bpmp', 'bu.user_id = bpmp.user_id'); 
		$this->db->join('bepms_project_positions as bpp', "bpp.project_position_id = bpmp.project_position_id AND (bpp.project_position_name = 'leader' OR bpp.project_position_name = 'member')");
		$this->db->where(
			array(
				'bpmp.project_id' => $project_id
			)
		);
		//echo $this->db->get_compiled_select();
		$query = $this->db->get()-> result();
		return $query;
	}

	public function projectDetailsByPositionIDandName($user_id, $system_id, $project_position_name, $project_id){
		$this->db->select('bp.project_id, bp.project_name, bp.project_description, bp.project_status, bp.project_attachment, bp.project_code
		, bpp.project_position_name');
		$this->db->from('bepms_projects as bp');
		$this->db->join('bepms_systems as bs', 'bs.system_id = bp.system_id');
		$this->db->join('bepms_project_member_positions as bpmp', 'bp.project_id = bpmp.project_id');
		$this->db->join('bepms_project_positions as bpp', "bpp.project_position_id = bpmp.project_position_id AND (bpp.project_position_name = 'leader' OR bpp.project_position_name = 'member')");
		$this->db->where(
			array(
				'bpmp.user_id' => $user_id,
				'bp.system_id' => $system_id,
				'bpmp.project_id' => $project_id,
				'bpp.project_position_name' => $project_position_name
			)
		);
		//echo $this->db->get_compiled_select();
		$query = $this->db->get()-> result();

		$project_id = $query[0]->project_id;

		$query[0]->members = $this->projectMemberByProjectID($query[0]->project_id);
		$query[0]->notices = $this->projectNoticeByUserID($user_id, $project_id);
		$query[0]->notifications = $this->projectNotificationByUserID($user_id, $project_id);
		
		return $query;
	}

	public function userPositionsInSystemWithProjectId($user_id, $system_id, $project_id){
		$this->db->select('bpp.project_position_name');
		$this->db->from('bepms_project_positions as bpp');
		$this->db->join('bepms_project_member_positions as bpmp', 'bpp.project_position_id = bpmp.project_position_id');
		$this->db->join('bepms_projects as bp', "bp.project_id = bpmp.project_id");
		$this->db->join('bepms_systems as bs', "bp.system_id = bs.system_id");
		$this->db->where(
			array(
				'bpmp.user_id' => $user_id,
				'bp.system_id' => $system_id,
				'bp.project_id' => $project_id
			)
		);
		//echo $this->db->get_compiled_select();
		$query = $this->db->get()-> result();
		return $query;
	}

	public function facultyProjects($user_id, $system_id, $project_position_name){
		$this->db->select('bp.project_profile_img, bp.project_name, bp.project_id');//, bul.user_display_name as leader_display_name
		$this->db->from('bepms_projects as bp');
		$this->db->join('bepms_systems as bs', 'bs.system_id = bp.system_id');
		$this->db->join('bepms_project_member_positions as bpmp', 'bp.project_id = bpmp.project_id');
		$this->db->join('bepms_project_positions as bpp', "bpp.project_position_id = bpmp.project_position_id");
		
		//$this->db->join('bepms_users as bu', "bpmp.user_id = bu.user_id AND bpp.project_position_name = 'leader'");
		
		$this->db->where(
			array(
				//'bpmp.user_id' => $user_id,
				'bp.system_id' => $system_id,
				'bpp.project_position_name' => $project_position_name
			)
		);
		//echo $this->db->get_compiled_select();
		$query = $this->db->get()-> result();
		return $query;
	}

	public function facultyHomeDetailsBySystemIDPositionName($user_id, $system_id, $project_position_name, $project_id){

		$query = [];

		$query['projects'] = $this->facultyProjects($user_id, $system_id, $project_position_name, $project_id);

		$query['reports'] = $this->facultyHomeReportsList($system_id, $project_id, $project_position_name);

		if($project_position_name === 'guide'){
			$query['notices'] = $this->facultyNotices($user_id, $system_id);
		}

		//$query[0]->notifications = $this->facultyNotifications($user_id);
		
		return $query;
	}

	public function facultyHomeReportsList($system_id, $project_id, $project_position_name){
		//project profile img, project name, leader name, title, if read by user, date, report id
		$this->db->select(
			'br.report_title, br.report_status_guide, br.report_status_pc, 
			br.report_status_hod, br.report_creation_date, bp.project_name, 
			bp.project_profile_img, bu.user_display_name as leader_display_name'
		);
		$this->db->from('bepms_reports as br');
		$this->db->join('bepms_projects as bp', 'br.project_id = bp.project_id');
		$this->db->join('bepms_project_member_positions as bpmp', 'bp.project_id = bpmp.project_id');
		$this->db->join('bepms_project_positions as bpp', "bpp.project_position_id = bpmp.project_position_id AND bpp.project_position_name = 'leader'");
		$this->db->join('bepms_users as bu', 'bpmp.user_id = bu.user_id');
		$this->db->where(
			array(
				'bp.system_id' => $system_id,
				'bp.project_id' => $project_id,
				'report_status_'.$project_position_name.' !=' => '---',
			)
		);
		//echo $this->db->get_compiled_select();
		$query = $this->db->get()-> result();
		return $query;
	}

	public function facultyNotices($guide_id, $system_id){
		//notice level, notice title
		$this->db->select('*');
		$this->db->from('bepms_notices as bno');
		$this->db->join('bepms_projects as bp', 'bno.project_id = bp.project_id');
		$this->db->join('bepms_systems as bs', 'bs.system_id = bp.system_id');
		$this->db->where(
			array(
				'bno.from_user_id' => $guide_id,
				'bp.system_id' => $system_id
			)
		);
		$query = $this->db->get()-> result();
		return $query;
	}

	public function facultyNotifications($user_id){
		//notice level, notice title
		$this->db->select('*');
		$this->db->from('bepms_notifications');
		$this->db->where(
			array(
				'to_user_id' => $user_id
			)
		);
		$query = $this->db->get()-> result();
		return $query;
	}

	/**
	 * Reports
	 */

	 public function userProjectReports($user_id, $system_id, $project_position_name,$project_id){
		$this->db->select('*');
		$this->db->from('bepms_reports as br');
		$this->db->join('bepms_projects as bp', 'br.project_id = bp.project_id');
		$this->db->join('bepms_project_member_positions as bpmp', 'bp.project_id = bpmp.project_id');
		$this->db->join('bepms_project_positions as bpp', "bpp.project_position_id = bpmp.project_position_id AND (bpp.project_position_name = 'leader' OR bpp.project_position_name = 'hod' OR bpp.project_position_name = 'guide' OR bpp.project_position_name = 'pc')");
		$this->db->join('bepms_users as bu', 'bpmp.user_id = bu.user_id');
		$this->db->where(
			array(
				'bp.system_id' => $system_id,
				'bp.project_id' => $project_id,
				'report_status_'.$project_position_name.' !=' => '---',
			)
		);
	 }


	//---------------- A D M I N -----------------------------

	public function adminLoginAuthentication($email, $password){
		$this->db->select('*');
		$this->db->from('bepms_users');
		$this->db->join('bepms_admins', 'bepms_admins.user_id = bepms_users.user_id');
		$this->db->where(
			array(
				'bepms_users.user_email' => $email,
				'bepms_users.user_password' => $password
			)
		);
		//echo $this->db->get_compiled_select();
		$query = $this->db->get()-> result();
		//var_dump($query[0]);
		return (sizeof($query) === 1) ? $query[0] : false;
	}

	//---------------- H O M E P A G E -----------------------------


}
