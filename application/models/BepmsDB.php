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

		return ($this->db->affected_rows() != 1) ? false : true;

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
	
	public function systemLoginAuthentication($email, $password){
		$this->db->select('*');
		$this->db->from('bepms_users');
		$this->db->join('bepms_project_positions', 'bepms_users.user_id = bepms_project_positions.user_id');
		$this->db->where(
			array(
				'bepms_users.user_email' => $email,
				'bepms_users.user_password' => $password
			)
		);
		//echo $this->db->get_compiled_select();
		$query = $this->db->get()-> result();

		return (sizeof($query) === 1) ? $query[0] : false;
	}

	public function userPositions(){
		//user position according to the systems
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

	public function homepage($user_id = 2, $userEmail = 'leader@gmail.com'){
		
	}

}
