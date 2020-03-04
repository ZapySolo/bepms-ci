<?php

defined('BASEPATH') OR exit('No direct script access allowed');
 
/**
 * Implements helper functions for grabbing information from the MyBB install.
 *
 * Can be configured by modifying application/config/mybb.php
 */
class Bepms extends CI_Model {

	/**
	 * Constructor.
	 * Establish database connection, if appropriate.
	 */
	public function __construct() {
        parent::__construct();
		$this->load->database();
    }
    
    // U S E R

    public function userProfileDetails($user_id){
        $this->db->select('user_profile_image, user_first_name, user_last_name, user_display_name, user_email, user_mobile');
        $this->db->from('bepms_users');
        
		$this->db->where(
			array(
                'user_id' => $user_id,
			)
        );
		$query = $this->db->get()-> result();
		return $query;
    }
	

    //----------------- S Y S T E M -------------------------
    
    public function systemFacultyLoginAuthentication($userEmail, $password){
        $this->db->select('*');
		$this->db->from('bepms_users as bu');
        $this->db->join('bepms_project_member_positions as bpmp', "bu.user_id = bpmp.user_id AND
            (bpmp.project_position_name = 'hod' OR bpmp.project_position_name = 'pc' OR bpmp.project_position_name = 'guide')");
		$this->db->where(
			array(
				'bu.user_email' => $userEmail,
				'bu.user_password' => $password
			)
        );
        //echo $this->db->get_compiled_select();
		$query = $this->db->get()-> result();
		return (sizeof($query) > 0) ? $query[0] : false;
    }

    /**
     * S T U D E N T 
     * */

    public function systemStudentLoginAuthentication($userEmail, $password){
        $this->db->select('*');
		$this->db->from('bepms_users as bu');
        $this->db->join('bepms_project_member_positions as bpmp', "bu.user_id = bpmp.user_id AND
            (bpmp.project_position_name = 'leader' OR bpmp.project_position_name = 'member')");
		$this->db->where(
			array(
				'bu.user_email' => $userEmail,
				'bu.user_password' => $password
			)
        );
        //echo $this->db->get_compiled_select();
		$query = $this->db->get()-> result();
		return (sizeof($query) > 0) ? $query[0] : false;
    }

    public function studentProjectListByUserID($user_id){
        $this->db->select('bp.project_name, bpmp.project_position_name, bp.project_id');
		$this->db->from('bepms_projects as bp');
        $this->db->join('bepms_project_member_positions as bpmp', "bp.project_id = bpmp.project_id AND
            (bpmp.project_position_name = 'leader' OR bpmp.project_position_name = 'member')");
        $this->db->join('bepms_users as bu', "bu.user_id = bpmp.user_id");
		$this->db->where(
			array(
				'bpmp.user_id' => $user_id
			)
        );
        $query = $this->db->get()-> result();
		return $query;
    }

    public function studentProjectDetailsByUserID($user_id, $project_id){
        $this->db->select('bp.project_cover_img, bp.project_profile_img, bp.project_name, bp.project_description, bp.project_status, bp.project_attachment,
        bpmp.project_position_name');
		$this->db->from('bepms_projects as bp');
        $this->db->join('bepms_project_member_positions as bpmp', "bp.project_id = bpmp.project_id AND
            (bpmp.project_position_name = 'leader' OR bpmp.project_position_name = 'member')");
        $this->db->join('bepms_users as bu', "bu.user_id = bpmp.user_id");
		$this->db->where(
			array(
                'bpmp.user_id' => $user_id,
                'bp.project_id' => $project_id
			)
        );
        $this->db->limit(1);
        $query = $this->db->get()-> result();
        $query[0]->project_members = $this->projectMemberList($project_id);
		return $query;
    }

    function projectMemberList($project_id){
        $this->db->select('bu.user_display_name, bu.user_profile_image, bpmp.project_position_name');
		$this->db->from('bepms_projects as bp');
        $this->db->join('bepms_project_member_positions as bpmp', "bp.project_id = bpmp.project_id AND
            (bpmp.project_position_name = 'leader' OR bpmp.project_position_name = 'member')");
        $this->db->join('bepms_users as bu', "bu.user_id = bpmp.user_id");
		$this->db->where(
			array(
				'bp.project_id' => $project_id
			)
        );
        $query = $this->db->get()-> result();
		return $query;
    }

    public function studentProjectReportListBySearchInput($user_id, $project_id, $search_input){
        $this->db->select('br.report_title, br.report_creation_date, br.report_status_guide, br.report_status_pc, br.report_status_hod,
            br.report_id');
		$this->db->from('bepms_reports as br');
        $this->db->join('bepms_projects as bp','br.project_id = bp.project_id');
        $this->db->join('bepms_project_member_positions as bpmp', "bp.project_id = bpmp.project_id");
        $this->db->join('bepms_users as bu', "bu.user_id = bpmp.user_id");
		$this->db->where(
			array(
                'bp.project_id' => $project_id,
                'bu.user_id' => $user_id
			)
        );
        if($search_input !== ''){
            $this->db->like('br.report_title', $search_input);
        }
        $query = $this->db->get()-> result();
		return $query;
    }

    function projectNameByProjectId($projectID){
        //
    }

    public function studentProjectReportDetails($user_id, $report_id){
        $this->db->select('br.report_title, br.report_creation_date, br.report_status_guide, br.report_status_pc, br.report_status_hod,
        br.report_id, br.report_description, br.report_status_claim, br.report_disapproved_reason, br.report_change_assign, br.report_attachment');
        $this->db->from('bepms_reports as br');
        $this->db->join('bepms_projects as bp','br.project_id = bp.project_id');
        $this->db->join('bepms_project_member_positions as bpmp', "bp.project_id = bpmp.project_id");
        $this->db->join('bepms_users as bu', "bu.user_id = bpmp.user_id");
        $this->db->where(
            array(
                'br.report_id' => $report_id,
                'bu.user_id' => $user_id
            )
        );
        $this->db->limit(1);
        $query = $this->db->get()-> result();
        return $query;
    }

    public function userPositionInProject($user_id, $project_id){
        $this->db->select('bpmp.project_position_name');
        $this->db->from('bepms_project_member_positions as bpmp');
        $this->db->where(
            array(
                'bpmp.project_id' => $project_id,
                'bpmp.user_id' => $user_id
            )
        );
        $query = $this->db->get()-> result();
        return $query;
    }

    public function leaderCreateReport($report_title, $report_description, $report_status_claim, $project_id){
        $data = array(
            'report_title' => $report_title,
            'report_description' => $report_description,
            'report_status_claim' => $report_status_claim,
            'project_id' => $project_id,
            'report_status_guide' => 'pending',
            'report_status_pc' => '---',
            'report_status_hod' => '---',
            'report_disapproved_reason' => NULL,
            'report_change_assign' => NULL,
            'report_attachment' => NULL,
            'report_creation_date' => date("Y-m-d H:i:s")
		);
    
		$this->db->insert('bepms_reports', $data);

		$last_id = $this->db->insert_id();

		return ($this->db->affected_rows() != 1) ? [] : ['report_id' => $last_id];
    }

    public function updateReportAttachmentPath($report_id, $report_attachment, $report_attachment_size){
        $data = array(
            'report_attachment' => $report_attachment,
            'report_attachment_size' => $report_attachment_size
        );
        $this->db->where('report_id', $report_id);
        $this->db->update('bepms_reports', $data);
    }

    public function userPositionInReport($user_id, $report_id){
        $this->db->select('bpmp.project_position_name');
        $this->db->from('bepms_project_member_positions as bpmp');
        $this->db->join('bepms_reports as br','br.project_id = bpmp.project_id');
        $this->db->where(
            array(
                'br.report_id' => $report_id,
                'bpmp.user_id' => $user_id
            )
        );
        $query = $this->db->get()-> result();
        return $query;
    }

    public function leaderDeleteReport($user_id, $report_id, $user_password){
        $this->db->select('br.report_attachment');
        $this->db->from('bepms_reports as br');
        $this->db->join('bepms_projects as bp','br.project_id = bp.project_id');
        $this->db->join('bepms_project_member_positions as bpmp', "bp.project_id = bpmp.project_id");
        $this->db->join('bepms_users as bu', "bu.user_id = bpmp.user_id");
        $this->db->where(
            array(
                'br.report_id' => $report_id,
                'bu.user_id' => $user_id,
                'bu.user_password' => $user_password
            )
        );
        $this->db->limit(1);
        $query = $this->db->get()-> result();
        
        if( $query ){
            $this->db->where('report_id', $report_id);
            $this->db->delete('bepms_reports');
            return $query[0];
        } else {
            return false;
        }
    }

    //F A C U L T Y 

    public function facultySystemListByUserID($user_id){
        $this->db->select('bs.system_id, bs.system_name, bs.system_creation_date,bp.project_id, bpmp.project_position_name');//bp.project_id, bpmp.project_position_name,
		$this->db->from('bepms_systems as bs');
        $this->db->join('bepms_projects as bp', "bp.system_id = bs.system_id");
        $this->db->join('bepms_project_member_positions as bpmp', "bp.project_id = bpmp.project_id AND 
            (bpmp.project_position_name = 'hod' OR bpmp.project_position_name = 'guide' OR bpmp.project_position_name = 'pc')");
        $this->db->join('bepms_users as bu', "bu.user_id = bpmp.user_id");
		$this->db->where(
			array(
				'bpmp.user_id' => $user_id
			)
        );
        
        $query = $this->db->get()-> result();

        $data = json_decode(json_encode($query), true); //converting stdClass to array<-might have performance issused

        // $data = [
        //     [
        //         'system_id' => '1',
        //         'system_name' => 'Computer Department 2020',
        //         'system_creation_date' => '2020-02-11 00:00:00',
        //         'project_id' => '1',
        //         'project_position_name' => 'guide'
        //     ], [
        //         'system_id' => '1',
        //         'system_name' => 'Computer Department 2020',
        //         'system_creation_date' => '2020-02-11 00:00:00',
        //         'project_id' => '1',
        //         'project_position_name' => 'pc'
        //     ], [
        //         'system_id' => '1',
        //         'system_name' => 'Computer Department 2020',
        //         'system_creation_date' => '2020-02-11 00:00:00',
        //         'project_id' => '1',
        //         'project_position_name' => 'guide'
        //     ], [
        //         'system_id' => '2',
        //         'system_name' => 'Computer Department 2020',
        //         'system_creation_date' => '2020-02-11 00:00:00',
        //         'project_id' => '1',
        //         'project_position_name' => 'guide'
        //     ], [
        //         'system_id' => '2',
        //         'system_name' => 'Computer Department 2020',
        //         'system_creation_date' => '2020-02-11 00:00:00',
        //         'project_id' => '1',
        //         'project_position_name' => 'pc'
        //     ], [
        //         'system_id' => '1',
        //         'system_name' => 'Computer Department 2020',
        //         'system_creation_date' => '2020-02-11 00:00:00',
        //         'project_id' => '1',
        //         'project_position_name' => 'hod'
        //     ]
        // ];
        $result = [];
        for($i = 0; $i < sizeof($data); $i++){
            $flag = false;
            $data_i = $data[$i];
            for($k = 0; $k < sizeof($result); $k++){
                $result_k = $result[$k];
                if($result_k['system_id'] === $data_i['system_id']){
                    if(!in_array($data_i['project_position_name'], $result_k['project_position_name'])){
                        array_push($result_k['project_position_name'], $data_i['project_position_name']);
                    }
                    $flag = true;
                }
                $result[$k] = $result_k;
            }
            if(!$flag){
                $temp = [
                    'system_id' => $data_i['system_id'],
                    'system_name' => $data_i['system_name'],
                    'system_creation_date' => $data_i['system_creation_date'],
                    'project_position_name' => [
                        $data_i['project_position_name']
                    ]
                ];
                array_push($result, $temp);
            }
        }
        //print_r($query);
		return $result;
    }

    public function facultyHomeProjectAndReportsByUSerIDPositionSystemId($user_id, $user_position, $system_id, $search_input){
        //wtf is wrong with this code.... 
        //after 1hr of making changes and finally comming back to where i was an hour ago 
        //it works fine :-(
        $result = new stdClass();
        
        $where = array(
            'user_bu.user_id' => $user_id,
            'bpmp_user.project_position_name' => $user_position
        );
        if($system_id !== '') $where['bp.system_id'] = $system_id;

        if($search_input === ''){
            //projects
            $this->db->select('bs.system_id, bp.project_id, bp.project_cover_img, bp.project_name, leader_bu.user_display_name as leader_display_name, bpmp_user.project_position_name');
            $this->db->from('bepms_projects as bp');
            $this->db->join('bepms_project_member_positions as bpmp_leader', "bp.project_id = bpmp_leader.project_id AND (bpmp_leader.project_position_name = 'leader')");
            $this->db->join('bepms_users as leader_bu', "leader_bu.user_id = bpmp_leader.user_id");
            $this->db->join('bepms_project_member_positions as bpmp_user', "bp.project_id = bpmp_user.project_id");
            $this->db->join('bepms_users as user_bu', "user_bu.user_id = bpmp_user.user_id");
            $this->db->join('bepms_systems as bs', 'bs.system_id = bp.system_id');
            $this->db->where($where);
            $projectQuery = $this->db->get()-> result();

            //reports
            $this->db->select('bp.project_profile_img, br.report_title, br.report_status_'.$user_position.' as report_status_user, leader_bu.user_display_name');
            $this->db->from('bepms_reports as br');
            $this->db->join('bepms_projects as bp', "bp.project_id = br.project_id");
            $this->db->join('bepms_project_member_positions as bpmp_leader', "bp.project_id = bpmp_leader.project_id AND (bpmp_leader.project_position_name = 'leader')");
            $this->db->join('bepms_users as leader_bu', "leader_bu.user_id = bpmp_leader.user_id");
            $this->db->join('bepms_project_member_positions as bpmp_user', "bp.project_id = bpmp_user.project_id");
            $this->db->join('bepms_users as user_bu', "user_bu.user_id = bpmp_user.user_id");
            $this->db->join('bepms_systems as bs', 'bs.system_id = bp.system_id');
            $this->db->where($where);
            $this->db->limit(10);
            $reportQuery = $this->db->get()-> result();

        } else {
            //project like %project_name%
            $this->db->select('bs.system_id, bp.project_id, bp.project_cover_img, bp.project_name, leader_bu.user_display_name as leader_display_name, bpmp_user.project_position_name');
            $this->db->from('bepms_projects as bp');
            $this->db->join('bepms_project_member_positions as bpmp_leader', "bp.project_id = bpmp_leader.project_id AND (bpmp_leader.project_position_name = 'leader')");
            $this->db->join('bepms_users as leader_bu', "leader_bu.user_id = bpmp_leader.user_id");
            $this->db->join('bepms_project_member_positions as bpmp_user', "bp.project_id = bpmp_user.project_id");
            $this->db->join('bepms_users as user_bu', "user_bu.user_id = bpmp_user.user_id");
            $this->db->join('bepms_systems as bs', 'bs.system_id = bp.system_id');
            $this->db->where($where);
            $this->db->like('bp.project_name', $search_input);
            $projectQueryLikeProjectName = $this->db->get()-> result();

            //project like %leader_name%
            $this->db->select('bs.system_id, bp.project_id, bp.project_cover_img, bp.project_name, leader_bu.user_display_name as leader_display_name, bpmp_user.project_position_name');
            $this->db->from('bepms_projects as bp');
            $this->db->join('bepms_project_member_positions as bpmp_leader', "bp.project_id = bpmp_leader.project_id AND (bpmp_leader.project_position_name = 'leader')");
            $this->db->join('bepms_users as leader_bu', "leader_bu.user_id = bpmp_leader.user_id");
            $this->db->join('bepms_project_member_positions as bpmp_user', "bp.project_id = bpmp_user.project_id");
            $this->db->join('bepms_users as user_bu', "user_bu.user_id = bpmp_user.user_id");
            $this->db->join('bepms_systems as bs', 'bs.system_id = bp.system_id');
            $this->db->where($where);
            $this->db->like('leader_bu.user_display_name', $search_input);
            $projectQueryLikeLeaderName = $this->db->get()-> result();

            $projectQuery = array_merge($projectQueryLikeProjectName, $projectQueryLikeLeaderName);

            //reports like %project_name%
            $this->db->select('bp.project_profile_img, br.report_title, br.report_status_'.$user_position.' as report_status_user, leader_bu.user_display_name, bp.project_name');
            $this->db->from('bepms_reports as br');
            $this->db->join('bepms_projects as bp', "bp.project_id = br.project_id");
            $this->db->join('bepms_project_member_positions as bpmp_leader', "bp.project_id = bpmp_leader.project_id AND (bpmp_leader.project_position_name = 'leader')");
            $this->db->join('bepms_users as leader_bu', "leader_bu.user_id = bpmp_leader.user_id");
            $this->db->join('bepms_project_member_positions as bpmp_user', "bp.project_id = bpmp_user.project_id");
            $this->db->join('bepms_users as user_bu', "user_bu.user_id = bpmp_user.user_id");
            $this->db->join('bepms_systems as bs', 'bs.system_id = bp.system_id');
            $this->db->where($where);
            $this->db->like('bp.project_name', $search_input);
            $this->db->limit(10);
            $reportQueryLikeLeaderName = $this->db->get()-> result();

            //reports like %leader_name%
            $this->db->select('bp.project_profile_img, br.report_title, br.report_status_'.$user_position.' as report_status_user, leader_bu.user_display_name, bp.project_name');
            $this->db->from('bepms_reports as br');
            $this->db->join('bepms_projects as bp', "bp.project_id = br.project_id");
            $this->db->join('bepms_project_member_positions as bpmp_leader', "bp.project_id = bpmp_leader.project_id AND (bpmp_leader.project_position_name = 'leader')");
            $this->db->join('bepms_users as leader_bu', "leader_bu.user_id = bpmp_leader.user_id");
            $this->db->join('bepms_project_member_positions as bpmp_user', "bp.project_id = bpmp_user.project_id");
            $this->db->join('bepms_users as user_bu', "user_bu.user_id = bpmp_user.user_id");
            $this->db->join('bepms_systems as bs', 'bs.system_id = bp.system_id');
            $this->db->where($where);
            $this->db->like('leader_bu.user_display_name', $search_input);
            $this->db->limit(10);
            $reportQueryLikeReportTitle = $this->db->get()-> result();
            $reportQuery = array_merge($reportQueryLikeLeaderName, $reportQueryLikeReportTitle);

        }

        $result->project = $projectQuery;
        $result->report = $reportQuery;
   
        return $result;
    }

    public function userPositionsInProjectId($user_id, $project_id){
		$this->db->select('bpmp.project_position_name');
		$this->db->from('bepms_project_member_positions as bpmp');
		$this->db->join('bepms_projects as bp', "bp.project_id = bpmp.project_id");
		$this->db->where(
			array(
				'bpmp.user_id' => $user_id,
				'bp.project_id' => $project_id
			)
		);
		//echo $this->db->get_compiled_select();
		$query = $this->db->get()-> result();
		return $query;
    }
    
    public function facultyProjectDetails($user_id, $project_id){
        $this->db->select('bp.project_id, bp.project_name, bp.project_description, bp.project_status, bp.project_attachment, bp.project_code
		, bpmp.project_position_name, bp.project_cover_img, bp.project_profile_img');
		$this->db->from('bepms_projects as bp');
		$this->db->join('bepms_project_member_positions as bpmp', 'bp.project_id = bpmp.project_id');
		$this->db->where(
			array(
				'bpmp.user_id' => $user_id,
				'bpmp.project_id' => $project_id,
			)
        );
        $query = $this->db->get()-> result();
        $query[0]->members = $this->projectMemberByProjectID($query[0]->project_id);
		return $query;
    }

    function projectMemberByProjectID($project_id){
		$this->db->select('bu.user_display_name, bu.user_profile_image, bpmp.project_position_name');
		$this->db->from('bepms_users as bu');
		$this->db->join('bepms_project_member_positions as bpmp', "bu.user_id = bpmp.user_id AND (bpmp.project_position_name = 'leader' OR bpmp.project_position_name = 'member')"); 
		$this->db->where(
			array(
				'bpmp.project_id' => $project_id
			)
		);
		$query = $this->db->get()-> result();
		return $query;
	}

    public function facultyProjectListBySystemID($system_id){
        $this->db->select('bp.project_id, bp.project_profile_img, bp.project_name, l_bu.user_display_name as leader_display_name');//
        $this->db->from('bepms_projects as bp');
        $this->db->join('bepms_project_member_positions as l_bpmp', "l_bpmp.project_id = bp.project_id AND l_bpmp.project_position_name = 'leader'");
		$this->db->join('bepms_users as l_bu', "l_bu.user_id = l_bpmp.user_id");
		$this->db->where(
			array(
                'bp.system_id' => $system_id
			)
		);
		$query = $this->db->get()-> result();
		return $query;
    }

    public function checkFacultyInSystemID($user_id, $system_id){
        $this->db->select('bpmp.project_position_name');
        $this->db->from('bepms_systems as bs');
        $this->db->join('bepms_projects as bp', "bs.system_id = bp.system_id");
        $this->db->join('bepms_project_member_positions as bpmp', "bpmp.project_id = bp.project_id AND (bpmp.project_position_name = 'hod'
            OR bpmp.project_position_name = 'guide' OR bpmp.project_position_name = 'pc')");
		$this->db->where(
			array(
                'bpmp.user_id' => $user_id,
                'bp.system_id' => $system_id
			)
		);
		$query = $this->db->get()-> result();
		return $query;
    }

    public function checkFacultyPositionInSystemID($user_id, $system_id, $project_position_name){
        $this->db->select('bpmp.project_position_name');
        $this->db->from('bepms_systems as bs');
        $this->db->join('bepms_projects as bp', "bs.system_id = bp.system_id");
        $this->db->join('bepms_project_member_positions as bpmp', "bpmp.project_id = bp.project_id AND (bpmp.project_position_name = 'hod'
            OR bpmp.project_position_name = 'guide' OR bpmp.project_position_name = 'pc')");
		$this->db->where(
			array(
                'bpmp.user_id' => $user_id,
                'bp.system_id' => $system_id,
                'bpmp.project_position_name' => $project_position_name
			)
		);
		$query = $this->db->get()-> result();
		return $query;
    }

    public function facultyReportSearchListsByPositionInSystem_post($user_id, $system_id, $project_position_name, $search_input = ''){
        $this->db->select('bp.project_id, bpmp.project_position_name, br.report_title, br.report_id, br.report_creation_date, br.report_status_'.$project_position_name.' as user_report_status');
        $this->db->from('bepms_reports as br');
        $this->db->join('bepms_projects as bp', "br.project_id = bp.project_id");
        $this->db->join('bepms_project_member_positions as bpmp', "bpmp.project_id = bp.project_id AND (bpmp.project_position_name = 'hod'
            OR bpmp.project_position_name = 'guide' OR bpmp.project_position_name = 'pc')");
		$this->db->where(
			array(
                'bpmp.user_id' => $user_id,
                'bp.system_id' => $system_id,
                'bpmp.project_position_name' => $project_position_name,
                'br.report_status_'.$project_position_name.' !=' => '---'
			)
        );
        if($search_input !== ''){
            $this->db->like('br.report_title', $search_input);
        }
		$query = $this->db->get()-> result();
		return $query;
    }

    public function checkFacultyResponseToReportValid($user_id, $report_id, $project_position_name){
        $this->db->select('*');
        $this->db->from('bepms_reports as br');
        $this->db->join('bepms_projects as bp', "br.project_id = bp.project_id");
        $this->db->join('bepms_project_member_positions as bpmp', "bpmp.project_id = bp.project_id AND (bpmp.project_position_name = 'hod'
            OR bpmp.project_position_name = 'guide' OR bpmp.project_position_name = 'pc')");
		$this->db->where(
			array(
                'bpmp.user_id' => $user_id,
                'br.report_id' => $report_id,
                'bpmp.project_position_name' => $project_position_name,
                'br.report_status_'.$project_position_name.'' => 'pending'
			)
        );
		$query = $this->db->get()-> result();
		return $query;
    }

    public function facultyApproveReportID($user_id, $report_id ,$project_position_name){

        $data = [];

        if($project_position_name === 'hod'){
            $data = [
                'report_status_hod' => 'approved',
            ];
        } else if($project_position_name === 'pc'){
            $data = [
                'report_status_pc' => 'approved',
                'report_status_hod' => 'pending'
            ];
        } else if($project_position_name === 'guide'){
            $data = [
                'report_status_guide' => 'approved',
                'report_status_pc' => 'pending'
            ];
        }

        $this->db->where('report_id', $report_id);
        $this->db->update('bepms_reports', $data);

        if($this->db->affected_rows() >= 0){
            return true; 
        }else{
            return false;
        }

    }

    public function facultyDisapproveReportID($user_id, $report_id ,$project_position_name, $report_disapproved_reason){
        $data = [];

        if($project_position_name === 'hod'){
            $data = [
                'report_status_hod' => 'disapprove',
                'report_disapproved_reason' => $report_disapproved_reason
            ];
        } else if($project_position_name === 'pc'){
            $data = [
                'report_status_pc' => 'disapprove',
                'report_disapproved_reason' => $report_disapproved_reason
            ];
        } else if($project_position_name === 'guide'){
            $data = [
                'report_status_guide' => 'disapprove',
                'report_disapproved_reason' => $report_disapproved_reason
            ];
        }

        $this->db->where('report_id', $report_id);
        $this->db->update('bepms_reports', $data);

        if($this->db->affected_rows() >= 0){
            return true; 
        }else{
            return false;
        }
    }

    public function facultyAssignChangesReportID($user_id, $report_id ,$project_position_name, $report_change_assign){
        $data = [];

        if($project_position_name === 'hod'){
            $data = [
                'report_status_hod' => 'changes',
                'report_change_assign' => $report_change_assign
            ];
        } else if($project_position_name === 'pc'){
            $data = [
                'report_status_pc' => 'changes',
                'report_change_assign' => $report_change_assign
            ];
        } else if($project_position_name === 'guide'){
            $data = [
                'report_status_guide' => 'changes',
                'report_change_assign' => $report_change_assign
            ];
        }

        $this->db->where('report_id', $report_id);
        $this->db->update('bepms_reports', $data);

        if($this->db->affected_rows() >= 0){
            return true;
        }else{
            return false;
        }
    }

    public function updateUserProfile($user_id, $update){
        $this->db->where('user_id', $user_id);
        $this->db->update('bepms_users', $update);
        if($this->db->affected_rows() >= 0){
            return true;
        }else{
            return false;
        }
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


}
