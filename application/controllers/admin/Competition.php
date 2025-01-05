<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of admistrator
 *
 * @author pc mart ltd
 */
class Competition extends Admin_Controller
{

    public function __construct()
    {
        parent::__construct();
        // $this->load->model('competition_model');
    }


    public function index()
    {

        // echo "hello";
        // exit();
        // get permission user by menu id
        // $data['permission_user'] = $this->competition_model->all_permission_user('6');

        // $data['title'] = "Competition Data"; //Page title      
        // if (!empty($id)) {
        //     if (is_numeric($id)) {
        //         $can_edit = $this->competition_model->can_action('tbl_competition', 'edit', array('competition_id' => $id));
        //         $edited = can_action(6, 'edited');
        //         if (!empty($can_edit) && !empty($edited)) {
        //             $data['competition_info'] = $this->competition_model->check_by(array('competition_id' => $id), 'tbl_competition');
        //         }
        //     }
        // }
        // $data['dropzone'] = true;
        // if ($action == 'edit_competition' || $action == 'project_competition') {
        //     $project_id = $this->uri->segment(6);
        //     if (!empty($project_id)) {
        //         $project_info = get_row('tbl_project', array('project_id' => $project_id));
        //         if ($project_info->permission == 'all') {
        //             $data['permission_user'] = $this->competition_model->allowed_user('57');
        //         } else {
        //             $data['permission_user'] = $this->competition_model->permitted_allowed_user($project_info->permission);
        //         }
        //     }
        //     $data['active'] = 2;
        // } else {
        //     $data['active'] = 1;
        // }
        // $data['page'] = lang('competition');
        // $data['sub_active'] = lang('all_competition');
        // if ($action == 'competition_details') {
        //     $data['competition_info'] = $this->competition_model->check_by(array('competition_id' => $id), 'tbl_competition');
        //     $subview = 'competition_details';
        // } elseif ($action == 'download_file') {
        //     $this->load->helper('download');
        //     $file = $this->uri->segment(6);
        //     if ($id) {
        //         $down_data = file_get_contents('uploads/' . $file); // Read the file's contents
        //         force_download($file, $down_data);
        //     } else {
        //         $type = "error";
        //         $message = 'Operation Fieled !';
        //         set_message($type, $message);
        //         if (empty($_SERVER['HTTP_REFERER'])) {
        //             redirect('admin/competition');
        //         } else {
        //             redirect($_SERVER['HTTP_REFERER']);
        //         }
        //     }
        // } elseif ($action == 'changed_competition_status') {
        //     $date = date('Y-m-d H:i:s');
        //     $status = $this->uri->segment(6);
        //     if (!empty($status)) {
        //         if ($status == 'closed' && config_item('notify_competition_reopened') == 'TRUE') {
        //             $this->notify_competition_reopened($id);
        //         }
        //         $this->competition_model->set_action(array('competition_id' => $id), array('status' => $status), 'tbl_competition');

        //     }
        //     $this->competition_model->set_action(array('competition_id' => $id), array('last_reply' => $date), 'tbl_competition');

        //     $rdata['body'] = $this->input->post('body', TRUE);

        //     $rdata['competition_id'] = $id;
        //     $rdata['replierid'] = $this->session->userdata('user_id');


        //     $this->competition_model->_table_name = 'tbl_competition_replies';
        //     $this->competition_model->_primary_key = 'competition_replies_id';
        //     $this->competition_model->save($rdata);

        //     $user_info = $this->db->where(array('user_id' => $rdata['replierid']))->get('tbl_users')->row();

        //     if ($user_info->role_id == '2') {
        //         $this->get_notify_ticket_reply('admin', $rdata); // Send email to admins
        //     } else {
        //         $this->get_notify_ticket_reply('client', $rdata); // Send email to client
        //     }
        //     // save into activities
        //     $activities = array(
        //         'user' => $this->session->userdata('user_id'),
        //         'module' => 'competition',
        //         'module_field_id' => $id,
        //         'activity' => 'activity_reply_competition',
        //         'icon' => 'fa-ticket',
        //         'link' => 'admin/competition/index/competition_details/' . $id,
        //         'value1' => $rdata['body'],
        //     );
        //     // Update into tbl_project
        //     $this->competition_model->_table_name = "tbl_activities"; //table name
        //     $this->competition_model->_primary_key = "activities_id";
        //     $this->competition_model->save($activities);
        //     if (empty($_SERVER['HTTP_REFERER'])) {
        //         redirect('admin/competition');
        //     } else {
        //         redirect($_SERVER['HTTP_REFERER']);
        //     }

        // } else {
        //     $subview = 'competition';
        // }

        $data['active'] = 1;
        $data['all_competition_info'] = $this->db->select('*')->from('tbl_competition')->get()->result_array();

        // echo "<pre>";
        // print_r($data);
        // exit();

        $data['subview'] = $this->load->view('admin/competition/competition', $data, TRUE);
        $this->load->view('admin/_layout_main', $data); //page load
    }
    
    
    public function create_competition(){
        // echo "<pre>";
        // print_r($_POST);
        // exit();
        $data = $_POST;
        
        $this->db->insert('tbl_competition',$data);
        
        $type = "success";
        $message = lang('New Competition Created');
        set_message($type, $message);
        redirect('admin/competition');
    }
    
    // public function competition_source()
    // {
    //     $data['title'] = lang('lead_source');
    //     $data['subview'] = $this->load->view('admin/competition/competition_source', $data, FALSE);
    //     $this->load->view('admin/_layout_modal', $data);
    // }
    
    // public function update_competition_source(){
    //     // print_r($_POST);
    //     // exit();
        
    //     $data['agency_name'] = $_POST['agency_name'];
    //     $this->db->insert('tbl_agency_source',$data);
        
    //     $type = "success";
    //     $msg = lang('Agency added');
    //     set_message($type, $message);
    //     redirect('admin/competition');
        
    // }
    
    public function edit_competition($id){
        $data['competition_info'] = $this->db->select('*')->from('tbl_competition')->where(array('competition_id'=>$id))->get()->row();
        $subview = 'competition';
        $data['active'] = 2;
        $data['subview'] = $this->load->view('admin/competition/' . $subview, $data, TRUE);
        $this->load->view('admin/_layout_main', $data); //page load
        
    }
    
    public function update_competition($id){
        
        $data = $_POST;
        
        $this->db->where('competition_id',$id);
        $this->db->update('tbl_competition',$data);
        
        $type = "success";
        $msg = lang('Competition Updated');
        set_message($type, $message);
        redirect('admin/competition');
    }
    
    public function delete_competition($id)
    {

        $data['deletion_indicator'] = 1;
        $this->db->where('competition_id',$id);
        $this->db->update('tbl_competition',$data);
        $type = 'success';
        $message = lang('Competition deleted');
        set_message($type, $message);
        redirect('admin/competition');
    }

}
