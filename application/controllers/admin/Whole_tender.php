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
class Whole_tender extends Admin_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->model('tickets_model');
    }

   


    public function index($action = NULL, $id = NULL)
    {
        // get permission user by menu id
        $data['permission_user'] = $this->tickets_model->all_permission_user('6');

        $data['title'] = "Whole Tender Details"; //Page title      
        if (!empty($id)) {
            if (is_numeric($id)) {
                $can_edit = $this->tickets_model->can_action('tbl_tickets', 'edit', array('tickets_id' => $id));
                $edited = can_action(6, 'edited');
                if (!empty($can_edit) && !empty($edited)) {
                    $data['tickets_info'] = $this->tickets_model->check_by(array('tickets_id' => $id), 'tbl_tickets');
                }
            }

        }
        $data['dropzone'] = true;
        if ($action == 'edit_tickets' || $action == 'project_tickets') {
            $project_id = $this->uri->segment(6);
            if (!empty($project_id)) {
                $project_info = get_row('tbl_project', array('project_id' => $project_id));
                if ($project_info->permission == 'all') {
                    $data['permission_user'] = $this->tickets_model->allowed_user('57');
                } else {
                    $data['permission_user'] = $this->tickets_model->permitted_allowed_user($project_info->permission);
                }
            }
            $data['active'] = 2;
        } else {
            $data['active'] = 1;
        }
        $data['page'] = lang('tickets');
        $data['sub_active'] = lang('all_tickets');
        if ($action == 'tickets_details') {
            $data['tickets_info'] = $this->tickets_model->check_by(array('tickets_id' => $id), 'tbl_tickets');
            $subview = 'tickets_details';
        } elseif ($action == 'download_file') {
            $this->load->helper('download');
            $file = $this->uri->segment(6);
            if ($id) {
                $down_data = file_get_contents('uploads/' . $file); // Read the file's contents
                force_download($file, $down_data);
            } else {
                $type = "error";
                $message = 'Operation Fieled !';
                set_message($type, $message);
                if (empty($_SERVER['HTTP_REFERER'])) {
                    redirect('admin/tickets');
                } else {
                    redirect($_SERVER['HTTP_REFERER']);
                }
            }
        } elseif ($action == 'changed_ticket_status') {
            $date = date('Y-m-d H:i:s');
            $status = $this->uri->segment(6);
            if (!empty($status)) {
                if ($status == 'closed' && config_item('notify_ticket_reopened') == 'TRUE') {
                    $this->notify_ticket_reopened($id);
                }
                $this->tickets_model->set_action(array('tickets_id' => $id), array('status' => $status), 'tbl_tickets');

            }
            $this->tickets_model->set_action(array('tickets_id' => $id), array('last_reply' => $date), 'tbl_tickets');

            $rdata['body'] = $this->input->post('body', TRUE);

            $rdata['tickets_id'] = $id;
            $rdata['replierid'] = $this->session->userdata('user_id');


            $this->tickets_model->_table_name = 'tbl_tickets_replies';
            $this->tickets_model->_primary_key = 'tickets_replies_id';
            $this->tickets_model->save($rdata);

            $user_info = $this->db->where(array('user_id' => $rdata['replierid']))->get('tbl_users')->row();

            if ($user_info->role_id == '2') {
                $this->get_notify_ticket_reply('admin', $rdata); // Send email to admins
            } else {
                $this->get_notify_ticket_reply('client', $rdata); // Send email to client
            }
            // save into activities
            $activities = array(
                'user' => $this->session->userdata('user_id'),
                'module' => 'tickets',
                'module_field_id' => $id,
                'activity' => 'activity_reply_tickets',
                'icon' => 'fa-ticket',
                'link' => 'admin/tickets/index/tickets_details/' . $id,
                'value1' => $rdata['body'],
            );
            // Update into tbl_project
            $this->tickets_model->_table_name = "tbl_activities"; //table name
            $this->tickets_model->_primary_key = "activities_id";
            $this->tickets_model->save($activities);
            if (empty($_SERVER['HTTP_REFERER'])) {
                redirect('admin/tickets');
            } else {
                redirect($_SERVER['HTTP_REFERER']);
            }

        } else {
            $subview = 'whole_tender';
        }

        $data['all_tickets_info'] = $this->tickets_model->get_permission('tbl_tickets');

        $data['subview'] = $this->load->view('admin/whole_tender/' . $subview, $data, TRUE);
        $this->load->view('admin/_layout_main', $data); //page load
    }
    
    
    public function create_whole_tender(){
        // echo "<pre>";
        // print_r($_POST);
        // exit();
        $data = $_POST;
        // echo "<pre>";
        // print_r($data);
        // exit();
        
        $this->db->insert('tbl_whole_tender',$data);
        
        $type = "success";
        $message = lang('Tender Created');
        set_message($type, $message);
        redirect('admin/whole_tender');
    }
    
    public function whole_tender_source()
    {
        $data['title'] = lang('lead_source');
        $data['subview'] = $this->load->view('admin/whole_tender/whole_tender_source', $data, FALSE);
        $this->load->view('admin/_layout_modal', $data);
    }
    
    public function update_whole_tender_source(){
        // print_r($_POST);
        // exit();
        
        $data['agency_name'] = $_POST['agency_name'];
        $this->db->insert('tbl_agency_source',$data);
        
        $type = "success";
        $msg = lang('Agency added');
        set_message($type, $message);
        redirect('admin/whole_tender');
        
    }
    
    public function edit_whole_tender($id){
        $data['tender_info'] = $this->db->select('*')->from('tbl_whole_tender')->where(array('whole_tender_id'=>$id))->get()->row();
        $subview = 'whole_tender';
        $data['active'] = 2;
        $data['subview'] = $this->load->view('admin/whole_tender/' . $subview, $data, TRUE);
        $this->load->view('admin/_layout_main', $data); //page load
        
    }
    
    public function update_whole_tender($id){
        // echo "<pre>";
        // print_r($_POST);
        // exit();
        
        $data = $_POST;
        
        // echo "<pre>";
        // print_r($data);
        // exit();
        
        
        $this->db->where('whole_tender_id',$id);
        $this->db->update('tbl_whole_tender',$data);
        
        $type = "success";
        $msg = lang('Tender Updated');
        set_message($type, $message);
        redirect('admin/whole_tender');
    }
    
    public function delete_whole_tender($id)
    {

        $data['deletion_indicator'] = 1;
        $this->db->where('whole_tender_id',$id);
        $this->db->update('tbl_whole_tender',$data);
        $type = 'success';
        $message = lang('tender deleted');
        set_message($type, $message);
        redirect('admin/whole_tender');
    }

}
