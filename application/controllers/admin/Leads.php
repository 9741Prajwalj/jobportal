<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Leads extends Admin_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->model('items_model');

        $this->load->helper('ckeditor');
        $this->data['ckeditor'] = array(
            'id' => 'ck_editor',
            'path' => 'asset/js/ckeditor',
            'config' => array(
                'toolbar' => "Full",
                'width' => "99.8%",
                'height' => "400px"
            )
        );
    }

    public function create_from_lead()
    {
        $leads_id = $this->input->post('leads_id');

        // Debug: Check if $leads_id is being passed correctly
        if (empty($leads_id)) {
            show_error("Lead ID is missing or invalid.");
            return;
        }

        // Retrieve the lead details from the database using the lead ID
        $this->db->where('leads_id', $leads_id);
        $leads_details = $this->db->get('tbl_leads')->row();

        // Debug: Check if $leads_details is retrieved
        if (empty($leads_details)) {
            show_error("No lead found with ID: $leads_id");
            return;
        }

        // Pass the lead details to the 'create' view
        $data['leads_details'] = $leads_details; 
        $this->load->view('admin/opportunities/create', $data); 
    }

    public function index($id = NULL)
    {
        $data['title'] = lang('all_leads');
        if (!empty($id)) {
            if ($id == 'kanban') {
                $data['active'] = 1;
                $k_session['leads_kanban'] = $id;
                $this->session->set_userdata($k_session);
            } elseif ($id == 'list') {
                $data['active'] = 1;
                $this->session->unset_userdata('leads_kanban');
            } elseif ($id == 'by_status') {
                $data['active'] = 1;    
                $lead_status_id = $this->uri->segment(5);
                if ($lead_status_id == 'all') {
                    redirect('admin/leads');
                } else {
                    $search = true;
                    $by_status = $this->items_model->get_leads('by_status', $lead_status_id);
                }
                $this->session->unset_userdata('leads_kanban');
            } elseif ($id == 'by_source') {
                $data['active'] = 1;
                $lead_source_id = $this->uri->segment(5);
                if ($lead_source_id == 'all') {
                    redirect('admin/leads');
                } else {
                    $search = true;
                    $by_status = $this->items_model->get_leads('by_source', $lead_source_id);
                }
                $this->session->unset_userdata('leads_kanban');
            } else {
                $data['active'] = 2;
                $can_edit = $this->items_model->can_action('tbl_leads', 'edit', array('leads_id' => $id));
                if (!empty($can_edit)) {
                    $data['leads_info'] = $this->items_model->check_by(array('leads_id' => $id), 'tbl_leads');
                }
                $this->session->unset_userdata('leads_kanban');
            }
        } else {
            $data['active'] = 1;
        }
        // get all leads status
        $status_info = $this->db->order_by('order_no', 'ASC')->get('tbl_lead_status')->result();
        if (!empty($status_info)) {
            foreach ($status_info as $v_status) {
                $data['status_info'][$v_status->lead_type][] = $v_status;
            }
        }
        $data['languages'] = $this->db->where('active', 1)->order_by('name', 'ASC')->get('tbl_languages')->result();

        $data['assign_user'] = $this->items_model->allowed_user('55');

        if (!empty(staff())) {
            $data['showing_created'] = true;
        }

        if (!empty($search)) {
            $data['all_leads'] = $by_status;
        } else {
            $data['all_leads'] = $this->items_model->get_permission('tbl_leads');
        }


        $data['subview'] = $this->load->view('admin/leads/all_leads', $data, TRUE);
        $this->load->view('admin/_layout_main', $data); //page load
    }

    public function leadList($filterBy = null, $search_by = null)
    {
        if ($this->input->is_ajax_request()) {
            $this->load->model('datatables');
            $this->datatables->table = 'tbl_leads';
            $this->datatables->join_table = array('tbl_lead_status', 'tbl_lead_source');
            $this->datatables->join_where = array('tbl_lead_status.lead_status_id=tbl_leads.lead_status_id', 'tbl_lead_source.lead_source_id=tbl_leads.lead_source_id');
            $custom_field = custom_form_table_search(5);
            $main_column = array('lead_name', 'contact_name', 'email', 'phone', 'lead_source', 'tags', 'lead_status', 'permission');
            $action_array = array('leads_id');
            $result = array_merge($main_column, $custom_field, $action_array);
            $this->datatables->column_order = $result;
            $this->datatables->column_search = $result;
            $this->datatables->order = array('leads_id' => 'ASC');

            $where = array('converted_client_id' => 0);
            if (!empty($search_by)) {
                if ($search_by == 'by_status') {
                    $where = array('tbl_leads.lead_status_id' => $filterBy);
                }
                if ($search_by == 'by_source') {
                    $where = array('tbl_leads.lead_source_id' => $filterBy);
                }
            } else {
                if ($filterBy == 'assigned_to_me') {
                    $user_id = $this->session->userdata('user_id');
                    $where = $user_id;
                }
                if ($filterBy == 'everyone') {
                    $where = array('permission' => 'all');
                }
                if ($filterBy == 'converted') {
                    $where = array('converted_client_id !=' => 0);
                }
                if ($filterBy == 'all') {
                    $where = array();
                }
            }

            $fetch_data = $this->datatables->get_datatable_permission($where);

            $data = array();

            $edited = can_action('55', 'edited');
            $deleted = can_action('55', 'deleted');
            foreach ($fetch_data as $_key => $v_leads) {
                $action = null;
                $can_edit = $this->items_model->can_action('tbl_leads', 'edit', array('leads_id' => $v_leads->leads_id));
                $can_delete = $this->items_model->can_action('tbl_leads', 'delete', array('leads_id' => $v_leads->leads_id));

                $sub_array = array();
                if (!empty($deleted) || !empty($can_delete)) {
                    $sub_array[] = '<div class="checkbox c-checkbox" ><label class="needsclick"> <input value="' . $v_leads->leads_id . '" type="checkbox"><span class="fa fa-check"></span></label></div>';
                }
                $name = null;
                $name .= '<a class="text-info" href="' . base_url() . 'admin/leads/leads_details/' . $v_leads->leads_id . '">' . $v_leads->lead_name . '</a>';

                $sub_array[] = $name;
                $sub_array[] = $v_leads->contact_name;
                $sub_array[] = $v_leads->email;
                $sub_array[] = $v_leads->phone;
                $sub_array[] = get_tags($v_leads->tags, true);
                $sub_array[] = '<span class="tags label label-info">' . ($v_leads->lead_source) . '</span>';
                $change_status = null;
                $ch_url = base_url() . 'admin/leads/change_status/';
                $astatus_info = $this->db->get('tbl_lead_status')->result();
                $change_status .= '<div class="btn-group">
        <button class="btn btn-xs btn-default dropdown-toggle"
                data-toggle="dropdown">
            <span class="caret"></span></button>
        <ul class="dropdown-menu animated zoomIn">';
                foreach ($astatus_info as $v_status) {
                    $change_status .= '<li><a href="' . $ch_url . $v_leads->leads_id . '/' . $v_status->lead_status_id . '">' . lang($v_status->lead_type) . '-' . $v_status->lead_status . '</a></li>';
                }
                $change_status .= '</ul></div>';

                if (!empty($v_leads->lead_status_id)) {
                    if ($v_leads->lead_type == 'open') {
                        $status = "<span class='label label-success'>" . lang($v_leads->lead_type) . "</span>";
                    } else {
                        $status = "<span class='label label-warning'>" . lang($v_leads->lead_type) . "</span>";
                    }
                    $status = "<span class='tags'>" . $v_leads->lead_status . "</span>" . ' ' . $status;
                } else {
                    $status = "<span class='label label-danger tags'>" . lang('none') . "</span>";
                }
                $sub_array[] = $status . ' ' . $change_status;
                $sub_array[] = (!empty($v_leads->last_contact) ? time_ago($v_leads->last_contact) : '-');

                $assigned = null;
                if ($v_leads->permission != 'all') {
                    $get_permission = json_decode($v_leads->permission);
                    if (!empty($get_permission)) :
                        foreach ($get_permission as $permission => $v_permission) :
                            $user_info = $this->db->where(array('user_id' => $permission))->get('tbl_users')->row();
                            if (!empty($user_info)) {
                                if ($user_info->role_id == 1) {
                                    $label = 'circle-danger';
                                } else {
                                    $label = 'circle-success';
                                }
                                $assigned .= '<a href="#" data-toggle="tooltip"
                                                               data-placement="top"
                                                               title="' . fullname($permission) . '"><img
                                                                    src="' . base_url() . staffImage($permission) . '"
                                                                    class="img-circle img-xs" alt="">
                                                <span style="margin: 0px 0 8px -10px;"
                                                      class="circle ' . $label . '  circle-lg"></span>
                                                            </a>';
                            }
                        endforeach;
                    endif;
                } else {
                    $assigned .= '<strong>' . lang("everyone") . '</strong><i title="' . lang('permission_for_all') . '" class="fa fa-question-circle" data-toggle="tooltip" data-placement="top"></i>';
                };
                if (!empty($can_edit) && !empty($edited)) {
                    $assigned .= '<span data-placement="top" data-toggle="tooltip" title="' . lang('add_more') . '"><a data-toggle="modal" data-target="#myModal" href="' . base_url() . 'admin/leads/update_users/' . $v_leads->leads_id . '" class="text-default ml"><i class="fa fa-plus"></i></a></span>';
                };

                $sub_array[] = $assigned;

                $custom_form_table = custom_form_table(5, $v_leads->leads_id);

                if (!empty($custom_form_table)) {
                    foreach ($custom_form_table as $c_label => $v_fields) {
                        $sub_array[] = $v_fields;
                    }
                }
                if (!empty($can_edit) && !empty($edited)) {
                    $action .= btn_edit('admin/leads/index/' . $v_leads->leads_id) . ' ';
                }
                if (!empty($can_delete) && !empty($deleted)) {
                    $action .= ajax_anchor(base_url("admin/leads/delete_leads/$v_leads->leads_id"), "<i class='btn btn-xs btn-danger fa fa-trash-o'></i>", array("class" => "", "title" => lang('delete'), "data-fade-out-on-success" => "#table_" . $_key)) . ' ';
                }
                $sub_array[] = $action;
                $data[] = $sub_array;
            }
            render_table($data, $where);
        } else {
            redirect('admin/dashboard');
        }
    }

    public function lead_status()
    {
        $data['title'] = lang('lead_status');
        $data['subview'] = $this->load->view('admin/leads/lead_status', $data, FALSE);
        $this->load->view('admin/_layout_modal', $data);
    }

    public function update_lead_status($id = null)
    {
        $this->items_model->_table_name = 'tbl_lead_status';
        $this->items_model->_primary_key = 'lead_status_id';

        $cate_data['lead_status'] = $this->input->post('lead_status', TRUE);
        $cate_data['lead_type'] = $this->input->post('lead_type', TRUE);
        $cate_data['order_no'] = $this->input->post('order_no', TRUE);

        // update root category
        $where = array('lead_status' => $cate_data['lead_status']);
        // duplicate value check in DB
        if (!empty($id)) { // if id exist in db update data
            $lead_status_id = array('lead_status_id !=' => $id);
        } else { // if id is not exist then set id as null
            $lead_status_id = null;
        }
        // check whether this input data already exist or not
        $check_lead_status = $this->items_model->check_update('tbl_lead_status', $where, $lead_status_id);
        if (!empty($check_lead_status)) { // if input data already exist show error alert
            // massage for user
            $type = 'error';
            $msg = "<strong style='color:#000'>" . $cate_data['lead_status'] . '</strong>  ' . lang('already_exist');
        } else { // save and update query
            $id = $this->items_model->save($cate_data, $id);

            $activity = array(
                'user' => $this->session->userdata('user_id'),
                'module' => 'settings',
                'module_field_id' => $id,
                'activity' => ('activity_added_a_lead_status'),
                'value1' => $cate_data['lead_status']
            );
            $this->items_model->_table_name = 'tbl_activities';
            $this->items_model->_primary_key = 'activities_id';
            $this->items_model->save($activity);

            // messages for user
            $type = "success";
            $msg = lang('lead_status_added');
        }
        if (!empty($id)) {
            $result = array(
                'id' => $id,
                'lead_status' => $cate_data['lead_status'],
                'status' => $type,
                'message' => $msg,
            );
        } else {
            $result = array(
                'status' => $type,
                'message' => $msg,
            );
        }
        echo json_encode($result);
        exit();
    }

    public function lead_source()
    {
        $data['title'] = lang('lead_source');
        $data['subview'] = $this->load->view('admin/leads/lead_source', $data, FALSE);
        $this->load->view('admin/_layout_modal', $data);
    }

    public function update_lead_source($id = null)
    {
        $this->items_model->_table_name = 'tbl_lead_source';
        $this->items_model->_primary_key = 'lead_source_id';

        $source_data['lead_source'] = $this->input->post('lead_source', TRUE);
        // update root category
        $where = array('lead_source' => $source_data['lead_source']);
        // duplicate value check in DB
        if (!empty($id)) { // if id exist in db update data
            $lead_source_id = array('lead_source_id !=' => $id);
        } else { // if id is not exist then set id as null
            $lead_source_id = null;
        }
        // check whether this input data already exist or not
        $check_lead_status = $this->items_model->check_update('tbl_lead_source', $where, $lead_source_id);
        if (!empty($check_lead_status)) { // if input data already exist show error alert
            // massage for user
            $type = 'error';
            $msg = "<strong style='color:#000'>" . $source_data['lead_source'] . '</strong>  ' . lang('already_exist');
        } else { // save and update query
            $id = $this->items_model->save($source_data, $id);

            $activity = array(
                'user' => $this->session->userdata('user_id'),
                'module' => 'settings',
                'module_field_id' => $id,
                'activity' => ('activity_added_a_lead_source'),
                'value1' => $source_data['lead_source']
            );
            $this->items_model->_table_name = 'tbl_activities';
            $this->items_model->_primary_key = 'activities_id';
            $this->items_model->save($activity);

            // messages for user
            $type = "success";
            $msg = lang('lead_source_added');
        }
        if (!empty($id)) {
            $result = array(
                'id' => $id,
                'lead_source' => $source_data['lead_source'],
                'status' => $type,
                'message' => $msg,
            );
        } else {
            $result = array(
                'status' => $type,
                'message' => $msg,
            );
        }
        echo json_encode($result);
        exit();
    }

    public function import_leads()
    {
        $data['title'] = lang('import_leads');
        $data['assign_user'] = $this->items_model->allowed_user('55');
        // get all leads status
        $status_info = $this->db->get('tbl_lead_status')->result();
        if (!empty($status_info)) {
            foreach ($status_info as $v_status) {
                $data['status_info'][$v_status->lead_type][] = $v_status;
            }
        }

        $data['subview'] = $this->load->view('admin/leads/import_leads', $data, TRUE);
        $this->load->view('admin/_layout_main', $data); //page load
    }

    public function save_imported()
    {
        //load the excel library
        $this->load->library('excel');
        ob_start();
        $file = $_FILES["upload_file"]["tmp_name"];
        if (!empty($file)) {
            $valid = false;
            $types = array('Excel2007', 'Excel5', 'CSV');
            foreach ($types as $type) {
                $reader = PHPExcel_IOFactory::createReader($type);
                if ($reader->canRead($file)) {
                    $valid = true;
                }
            }
            if (!empty($valid)) {
                try {
                    $objPHPExcel = PHPExcel_IOFactory::load($file);
                } catch (Exception $e) {
                    die("Error loading file :" . $e->getMessage());
                }
                //All data from excel
                $sheetData = $objPHPExcel->getActiveSheet()->toArray(null, true, true, true);
                $permission = $this->input->post('permission', true);
                if (!empty($permission)) {
                    if ($permission == 'everyone') {
                        $assigned = 'all';
                    } else {
                        $assigned_to = $this->items_model->array_from_post(array('assigned_to'));
                        if (!empty($assigned_to['assigned_to'])) {
                            foreach ($assigned_to['assigned_to'] as $assign_user) {
                                $assigned[$assign_user] = $this->input->post('action_' . $assign_user, true);
                            }
                        }
                    }
                    if (!empty($assigned)) {
                        if ($assigned != 'all') {
                            $assigned = json_encode($assigned);
                        }
                    } else {
                        $assigned = 'all';
                    }
                } else {
                    $assigned = 'all';
                }

                $data = $this->items_model->array_from_post(array('client_id', 'lead_status_id', 'lead_source_id'));

                for ($x = 2; $x <= count($sheetData); $x++) {
                    // **********************
                    // Save Into leads table
                    // **********************
                    $data['lead_name'] = trim($sheetData[$x]["A"]);
                    $data['organization'] = trim($sheetData[$x]["B"]);
                    $data['contact_name'] = trim($sheetData[$x]["C"]);
                    $data['email'] = trim($sheetData[$x]["D"]);
                    $data['phone'] = trim($sheetData[$x]["E"]);
                    $data['mobile'] = trim($sheetData[$x]["F"]);
                    $data['address'] = trim($sheetData[$x]["G"]);
                    $data['city'] = trim($sheetData[$x]["H"]);
                    $data['country'] = trim($sheetData[$x]["I"]);
                    $data['facebook'] = trim($sheetData[$x]["J"]);
                    $data['skype'] = trim($sheetData[$x]["K"]);
                    $data['twitter'] = trim($sheetData[$x]["L"]);
                    $data['notes'] = trim($sheetData[$x]["M"]);
                    $data['permission'] = $assigned;
                    $my_data[] = $data;
                }
                if (!empty($my_data)) {
                    $this->db->insert_batch('tbl_leads', $my_data);

                    $type = 'success';
                    $message = lang('save_leads');
                }
            } else {
                $type = 'error';
                $message = "Sorry your uploaded file type not allowed ! please upload XLS/CSV File ";
            }
        } else {
            $type = 'error';
            $message = "You did not Select File! please upload XLS/CSV File ";
        }
        set_message($type, $message);
        redirect('admin/leads');
    }

    public
    function saved_leads($id = NULL)
    {
        $created = can_action('55', 'created');
        $edited = can_action('55', 'edited');
        if (!empty($created) || !empty($edited) && !empty($id)) {
            $this->items_model->_table_name = 'tbl_leads';
            $this->items_model->_primary_key = 'leads_id';

            $data = $this->items_model->array_from_post(array('client_id', 'lead_name', 'organization', 'language', 'lead_status_id', 'tags', 'lead_source_id', 'contact_name', 'email', 'phone', 'mobile', 'address', 'city', 'state', 'country', 'facebook', 'skype', 'twitter', 'notes', 'last_contact'));

            // update root category
            $where = array('client_id' => $data['client_id'], 'lead_name' => $data['lead_name']);
            // duplicate value check in DB
            if (!empty($id)) { // if id exist in db update data
                $leads_id = array('leads_id !=' => $id);
            } else { // if id is not exist then set id as null
                $leads_id = null;
            }

            // check whether this input data already exist or not
            $check_leads = $this->items_model->check_update('tbl_leads', $where, $leads_id);
            if (!empty($check_leads)) { // if input data already exist show error alert
                // massage for user
                $type = 'error';
                $msg = "<strong style='color:#000'>" . $data['lead_name'] . '</strong>  ' . lang('already_exist');
            } else { // save and update query
                $permission = $this->input->post('permission', true);
                if (!empty($permission)) {
                    if ($permission == 'everyone') {
                        $assigned = 'all';
                    } else {
                        $assigned_to = $this->items_model->array_from_post(array('assigned_to'));
                        if (!empty($assigned_to['assigned_to'])) {
                            foreach ($assigned_to['assigned_to'] as $assign_user) {
                                $assigned[$assign_user] = $this->input->post('action_' . $assign_user, true);
                            }
                        }
                    }
                    if (!empty($assigned)) {
                        if ($assigned != 'all') {
                            $assigned = json_encode($assigned);
                        }
                    } else {
                        $assigned = 'all';
                    }
                    $data['permission'] = $assigned;
                } else {
                    set_message('error', lang('assigned_to') . ' Field is required');
                    if (empty($_SERVER['HTTP_REFERER'])) {
                        redirect('admin/leads');
                    } else {
                        redirect($_SERVER['HTTP_REFERER']);
                    }
                }
                $return_id = $this->items_model->save($data, $id);

                if (!empty($id)) {
                    $id = $id;
                    $action = 'activity_update_leads';
                    $description = 'not_update_leads';
                    $msg = lang('update_leads');
                } else {
                    $id = $return_id;
                    $action = 'activity_save_leads';
                    $description = 'not_save_leads';
                    $msg = lang('save_leads');
                }
                $u_data['index_no'] = $id;
                $id = $this->items_model->save($u_data, $id);

                save_custom_field(5, $id);
                $activity = array(
                    'user' => $this->session->userdata('user_id'),
                    'module' => 'leads',
                    'module_field_id' => $id,
                    'activity' => $action,
                    'icon' => 'fa-rocket',
                    'link' => 'admin/leads/leads_details/' . $id,
                    'value1' => $data['lead_name']
                );
                $this->items_model->_table_name = 'tbl_activities';
                $this->items_model->_primary_key = 'activities_id';
                $this->items_model->save($activity);
                // messages for user
                $type = "success";

                $leads_info = $this->items_model->check_by(array('leads_id' => $id), 'tbl_leads');
                $notifiedUsers = array();
                if (!empty($leads_info->permission) && $leads_info->permission != 'all') {
                    $permissionUsers = json_decode($leads_info->permission);
                    foreach ($permissionUsers as $user => $v_permission) {
                        array_push($notifiedUsers, $user);
                    }
                } else {
                    $notifiedUsers = $this->items_model->allowed_user_id('55');
                }
                if (!empty($notifiedUsers)) {
                    foreach ($notifiedUsers as $users) {
                        if ($users != $this->session->userdata('user_id')) {
                            add_notification(array(
                                'to_user_id' => $users,
                                'from_user_id' => true,
                                'description' => $description,
                                'link' => 'admin/leads/leads_details/' . $leads_info->leads_id,
                                'value' => lang('lead') . ' ' . $leads_info->lead_name,
                            ));
                        }
                    }
                    show_notification($notifiedUsers);
                }
            }
            $message = $msg;
            set_message($type, $message);
        }
        redirect('admin/leads');
    }

    public
    function leads_details($id, $active = NULL, $op_id = NULL)
    {
        $data['title'] = lang('leads_details');
        //get all task information
        $data['leads_details'] = $this->items_model->check_by(array('leads_id' => $id), 'tbl_leads');

        $this->items_model->_table_name = "tbl_task_attachment"; //table name
        $this->items_model->_order_by = "leads_id";
        $data['files_info'] = $this->items_model->get_by(array('leads_id' => $id), FALSE);

        foreach ($data['files_info'] as $key => $v_files) {
            $this->items_model->_table_name = "tbl_task_uploaded_files"; //table name
            $this->items_model->_order_by = "task_attachment_id";
            $data['project_files_info'][$key] = $this->items_model->get_by(array('task_attachment_id' => $v_files->task_attachment_id), FALSE);
        }
        $data['dropzone'] = true;

        if ($active == 2) {
            $data['active'] = 2;
            $data['sub_active'] = 1;
            $data['sub_metting'] = 1;
        } elseif ($active == 3) {
            $data['active'] = 3;
            $data['sub_active'] = 1;
            $data['sub_metting'] = 1;
        } elseif ($active == 4) {
            $data['active'] = 4;
            $data['sub_active'] = 1;
            $data['sub_metting'] = 1;
        } elseif ($active == 5) {
            $data['active'] = 5;
            $data['sub_active'] = 1;
            $data['sub_metting'] = 1;
        } elseif ($active == 11) {
            $data['active'] = 11;
            $data['sub_active'] = 1;
            $data['sub_metting'] = 1;
        } elseif ($active == 'metting') {
            $data['active'] = 3;
            $data['sub_active'] = 1;
            $data['sub_metting'] = 2;
            $data['mettings_info'] = $this->items_model->check_by(array('mettings_id' => $op_id), 'tbl_mettings');
        } elseif ($active == 'call') {
            $data['active'] = 2;
            $data['sub_active'] = 2;
            $data['call_info'] = $this->items_model->check_by(array('calls_id' => $op_id), 'tbl_calls');
            $data['sub_metting'] = 1;
        } 
        elseif ($active == 'tender') {
            $data['active'] = 12;
            $data['sub_active'] = 2;
            $data['tender_info'] = $this->db->select('*')->from('tbl_leads_tenders')->where(array('leads_id'=>$op_id))->get()->row();
            $data['sub_metting'] = 1;
        } 
        else {
            $data['active'] = 1;
            $data['sub_active'] = 1;
            $data['sub_metting'] = 1;
        }

        $data['subview'] = $this->load->view('admin/leads/leads_details', $data, TRUE);
        $this->load->view('admin/_layout_main', $data);
    }

    public
    function convert($id)
    {
        $data['title'] = lang('convert_to_client'); //Page title
        $data['person'] = 1;
        // get all country
        $this->items_model->_table_name = "tbl_countries"; //table name
        $this->items_model->_order_by = "id";
        $data['countries'] = $this->items_model->get();

        // get all currencies
        $this->items_model->_table_name = 'tbl_currencies';
        $this->items_model->_order_by = 'name';
        $data['currencies'] = $this->items_model->get();
        // get all language
        $data['languages'] = $this->db->where('active', 1)->order_by('name', 'ASC')->get('tbl_languages')->result();

        $data['leads_info'] = $this->items_model->check_by(array('leads_id' => $id), 'tbl_leads');
        $data['modal_subview'] = $this->load->view('admin/leads/_modal_convert', $data, FALSE);
        $this->load->view('admin/_layout_modal', $data);
    }

    public
    function converted($leads_id)
    {
        $data = $this->items_model->array_from_post(array(
            'name', 'email', 'short_note', 'website', 'phone', 'mobile', 'fax', 'address', 'city', 'zipcode', 'currency',
            'skype_id', 'linkedin', 'facebook', 'twitter', 'language', 'country', 'vat', 'hosting_company', 'hostname', 'port', 'password', 'username', 'client_status', 'latitude', 'longitude', 'customer_group_id'
        ));
        if (!empty($_FILES['profile_photo']['name'])) {
            $val = $this->items_model->uploadImage('profile_photo');
            $val == TRUE || redirect('admin/client/manage_client');
            $data['profile_photo'] = $val['path'];
        }
        $data['leads_id'] = $leads_id;
        $data['client_status'] = '0';

        $this->items_model->_table_name = 'tbl_client';
        $this->items_model->_primary_key = "client_id";
        $return_id = $this->items_model->save($data);
        // update to tbl_leads
        $u_data['converted_client_id'] = $return_id;
        $this->items_model->_table_name = 'tbl_leads';
        $this->items_model->_primary_key = "leads_id";
        $this->items_model->save($u_data, $leads_id);
        $action = ('activity_convert_to_client');
        $activities = array(
            'user' => $this->session->userdata('user_id'),
            'module' => 'leads',
            'module_field_id' => $return_id,
            'activity' => $action,
            'icon' => 'fa-rocket',
            'link' => 'admin/leads/leads_details/' . $leads_id,
            'value1' => $data['name']
        );
        $this->items_model->_table_name = 'tbl_activities';
        $this->items_model->_primary_key = "activities_id";
        $this->items_model->save($activities);
        // messages for user
        $type = "success";
        $message = lang('convert_to_client_suucess');
        set_message($type, $message);

        $leads_info = $this->items_model->check_by(array('leads_id' => $leads_id), 'tbl_leads');
        $client_info = $this->items_model->check_by(array('client_id' => $return_id), 'tbl_client');
        $notifiedUsers = array();
        if (!empty($leads_info->permission) && $leads_info->permission != 'all') {
            $permissionUsers = json_decode($leads_info->permission);
            foreach ($permissionUsers as $user => $v_permission) {
                array_push($notifiedUsers, $user);
            }
        } else {
            $notifiedUsers = $this->items_model->allowed_user_id('55');
        }
        if (!empty($notifiedUsers)) {
            foreach ($notifiedUsers as $users) {
                if ($users != $this->session->userdata('user_id')) {
                    add_notification(array(
                        'to_user_id' => $users,
                        'from_user_id' => true,
                        'description' => 'not_lead_converted_to_client',
                        'link' => 'admin/leads/leads_details/' . $leads_info->leads_id,
                        'value' => lang('lead') . ' ' . $leads_info->lead_name . ' ' . lang('client') . ' ' . $client_info->name,
                    ));
                }
            }
            show_notification($notifiedUsers);
        }

        redirect('admin/client/client_details/' . $return_id);
    }

    public function convert_to_opportunities($id)
    {
        $data['title'] = lang('convert_to_opportunity'); // Page title
        $data['person'] = 1;

        // Get all countries
        $this->items_model->_table_name = "tbl_countries"; // Table name
        $this->items_model->_order_by = "id";
        $data['countries'] = $this->items_model->get();

        // Get all currencies
        $this->items_model->_table_name = 'tbl_currencies';
        $this->items_model->_order_by = 'name';
        $data['currencies'] = $this->items_model->get();

        // Get all languages
        $data['languages'] = $this->db->where('active', 1)->order_by('name', 'ASC')->get('tbl_languages')->result();

        // Get leads information
        $data['leads_info'] = $this->items_model->check_by(array('leads_id' => $id), 'tbl_leads');

        // Load modal view for converting to opportunity
        $data['modal_subview'] = $this->load->view('admin/leads/_modal_convert_to_opportunity', $data, FALSE);
        $this->load->view('admin/_layout_modal', $data);
    }

    public function converted_to_opportunity($leads_id)
    {
        $data = $this->items_model->array_from_post(array(
            'name', 'email', 'short_note', 'website', 'phone', 'mobile', 'fax', 'address', 'city', 'zipcode', 'currency',
            'skype_id', 'linkedin', 'facebook', 'twitter', 'language', 'country', 'vat', 'hosting_company', 'hostname', 'port', 'password', 'username', 'opportunity_status', 'latitude', 'longitude', 'customer_group_id'
        ));
        if (!empty($_FILES['profile_photo']['name'])) {
            $val = $this->items_model->uploadImage('profile_photo');
            $val == TRUE || redirect('admin/opportunities/manage_opportunity');
            $data['profile_photo'] = $val['path'];
        }
        $data['leads_id'] = $leads_id;
        $data['opportunity_status'] = '0';

        $this->items_model->_table_name = 'tbl_opportunities';
        $this->items_model->_primary_key = "opportunities _id";
        $return_id = $this->items_model->save($data);

        // Update to tbl_leads
        $u_data['converted_opportunity_id'] = $return_id;
        $this->items_model->_table_name = 'tbl_leads';
        $this->items_model->_primary_key = "leads_id";
        $this->items_model->save($u_data, $leads_id);

        $action = ('activity_convert_to_opportunity');
        $activities = array(
            'user' => $this->session->userdata('user_id'),
            'module' => 'leads',
            'module_field_id' => $return_id,
            'activity' => $action,
            'icon' => 'fa-rocket',
            'link' => 'admin/leads/leads_details/' . $leads_id,
            'value1' => $data['name']
        );
        $this->items_model->_table_name = 'tbl_activities';
        $this->items_model->_primary_key = "activities_id";
        $this->items_model->save($activities);

        // Messages for user
        $type = "success";
        $message = lang('convert_to_opportunity_success');
        set_message($type, $message);

        $leads_info = $this->items_model->check_by(array('leads_id' => $leads_id), 'tbl_leads');
        $opportunity_info = $this->items_model->check_by(array('opportunity_id' => $return_id), 'tbl_opportunities');
        $notifiedUsers = array();
        if (!empty($leads_info->permission) && $leads_info->permission != 'all') {
            $permissionUsers = json_decode($leads_info->permission);
            foreach ($permissionUsers as $user => $v_permission) {
                array_push($notifiedUsers, $user);
            }
        } else {
            $notifiedUsers = $this->items_model->allowed_user_id('55');
        }
        if (!empty($notifiedUsers)) {
            foreach ($notifiedUsers as $users) {
                if ($users != $this->session->userdata('user_id')) {
                    add_notification(array(
                        'to_user_id' => $users,
                        'from_user_id' => true,
                        'description' => 'not_lead_converted_to_opportunity',
                        'link' => 'admin/leads/leads_details/' . $leads_info->leads_id,
                        'value' => lang('lead') . ' ' . $leads_info->lead_name . ' ' . lang('opportunity') . ' ' . $opportunity_info->name,
                    ));
                }
            }
            show_notification($notifiedUsers);
        }

        redirect('admin/opportunities/opportunities_details/' . $return_id);
    }

    // public function saved_opportunity($id = NULL)
    // {
    //     $created = can_action('56', 'created');
    //     $edited = can_action('56', 'edited');
    //     if (!empty($created) || !empty($edited) && !empty($id)) {
    //         $this->items_model->_table_name = 'tbl_opportunities';
    //         $this->items_model->_primary_key = 'opportunities_id';

    //         $data = $this->items_model->array_from_post(array('opportunity_name', 'stages', 'probability', 'close_date', 'opportunities_state_reason_id', 'expected_revenue', 'new_link', 'next_action', 'next_action_date', 'notes', 'company_name', 'email', 'phone', 'mobile','city','state','country','address'));
    //         $permission = $this->input->post('permission', true);
    //         if (!empty($permission)) {

    //             if ($permission == 'everyone') {
    //                 $assigned = 'all';
    //             } else {
    //                 $assigned_to = $this->items_model->array_from_post(array('assigned_to'));
    //                 if (!empty($assigned_to['assigned_to'])) {
    //                     foreach ($assigned_to['assigned_to'] as $assign_user) {
    //                         $assigned[$assign_user] = $this->input->post('action_' . $assign_user, true);
    //                     }
    //                 }
    //             }
    //             if (!empty($assigned)) {
    //                 if ($assigned != 'all') {
    //                     $assigned = json_encode($assigned);
    //                 }
    //             } else {
    //                 $assigned = 'all';
    //             }
    //             $data['permission'] = $assigned;
    //         } else {
    //             set_message('error', lang('assigned_to') . ' Field is required');
    //             if (empty($_SERVER['HTTP_REFERER'])) {
    //                 redirect('admin/opportunities');
    //             } else {
    //                 redirect($_SERVER['HTTP_REFERER']);
    //             }
    //         }
    //         // update root category
    //         $where = array('opportunity_name' => $data['opportunity_name']);
    //         // duplicate value check in DB
    //         if (!empty($id)) { // if id exist in db update data
    //             $opportunities_id = array('opportunities_id !=' => $id);
    //         } else { // if id is not exist then set id as null
    //             $opportunities_id = null;
    //         }

    //         // check whether this input data already exist or not
    //         $check_opportunity = $this->items_model->check_update('tbl_opportunities', $where, $opportunities_id);

    //         if (!empty($check_opportunity)) { // if input data already exist show error alert
    //             // massage for user
    //             $type = 'error';
    //             $msg = "<strong style='color:#000'>" . $data['opportunity_name'] . '</strong>  ' . lang('already_exist');
    //         } else { // save and update query
    //             $return_id = $this->items_model->save($data, $id);

    //             if (!empty($id)) {
    //                 $id = $id;
    //                 $action = 'activity_update_opportunity';
    //                 $msg = lang('update_opportunity');
    //                 $description = 'not_update_opportunity';
    //             } else {
    //                 $id = $return_id;
    //                 $action = 'activity_save_opportunity';
    //                 $description = 'not_save_opportunity';
    //                 $msg = lang('save_opportunity');
    //             }
    //             save_custom_field(8, $id);

    //             $activity = array(
    //                 'user' => $this->session->userdata('user_id'),
    //                 'module' => 'opportunities',
    //                 'module_field_id' => $id,
    //                 'activity' => $action,
    //                 'icon' => 'fa-filter',
    //                 'link' => 'admin/opportunities/opportunity_details/' . $id,
    //                 'value1' => $data['opportunity_name']
    //             );
    //             $this->items_model->_table_name = 'tbl_activities';
    //             $this->items_model->_primary_key = 'activities_id';
    //             $this->items_model->save($activity);
    //             // messages for user
    //             $type = "success";
    //         }

    //         $message = $msg;
    //         set_message($type, $message);

    //         $opportunity_info = $this->items_model->check_by(array('opportunities_id' => $id), 'tbl_opportunities');
    //         $notifiedUsers = array();
    //         if (!empty($opportunity_info->permission) && $opportunity_info->permission != 'all') {
    //             $permissionUsers = json_decode($opportunity_info->permission);
    //             foreach ($permissionUsers as $user => $v_permission) {
    //                 array_push($notifiedUsers, $user);
    //             }
    //         } else {
    //             $notifiedUsers = $this->items_model->allowed_user_id('56');
    //         }
    //         if (!empty($notifiedUsers) && !empty($opportunity_info)) {
    //             foreach ($notifiedUsers as $users) {
    //                 if ($users != $this->session->userdata('user_id')) {
    //                     add_notification(array(
    //                         'to_user_id' => $users,
    //                         'from_user_id' => true,
    //                         'description' => $description,
    //                         'link' => 'admin/opportunities/opportunity_details/' . $id,
    //                         'value' => lang('opportunity') . ' ' . $data['opportunity_name'],
    //                     ));
    //                 }
    //             }
    //             show_notification($notifiedUsers);
    //         }

    //     }
    //     if (!empty($id)) {
    //         redirect('admin/opportunities/opportunity_details/' . $id);
    //     } else {
    //         redirect('admin/opportunities');
    //     }

    // }
    public function saved_opportunity($id = NULL)
{
    log_message('debug', 'Entering saved_opportunity different function');
    
    $created = can_action('56', 'created');
    $edited = can_action('56', 'edited');
    
    if (!empty($created) || !empty($edited) && !empty($id)) {
        $this->items_model->_table_name = 'tbl_opportunities';
        $this->items_model->_primary_key = 'opportunities_id';

        $data = $this->items_model->array_from_post(array('opportunity_name','stages', 'probability', 'close_date', 'opportunities_state_reason_id', 'expected_revenue', 'new_link', 'next_action', 'next_action_date', 'notes','company_name','email','phone','mobile','city','state','country','address'));
        $permission = $this->input->post('permission', true);

        // Log the incoming data before processing
        log_message('info', 'Received opportunity data: ' . json_encode($data));

        if (!empty($permission)) {
            if ($permission == 'everyone') {
                $assigned = 'all';
            } else {
                $assigned_to = $this->items_model->array_from_post(array('assigned_to'));
                if (!empty($assigned_to['assigned_to'])) {
                    foreach ($assigned_to['assigned_to'] as $assign_user) {
                        $assigned[$assign_user] = $this->input->post('action_' . $assign_user, true);
                    }
                }
            }

            if (!empty($assigned)) {
                if ($assigned != 'all') {
                    $assigned = json_encode($assigned);
                }
            } else {
                $assigned = 'all';
            }
            $data['permission'] = $assigned;

            // Log the assigned data
            log_message('info', 'Assigned data: ' . json_encode($assigned));
        } else {
            set_message('error', lang('assigned_to') . ' Field is required');
            if (empty($_SERVER['HTTP_REFERER'])) {
                redirect('admin/opportunities');
            } else {
                redirect($_SERVER['HTTP_REFERER']);
            }
        }

        // Duplicate check
        $where = array('opportunity_name' => $data['opportunity_name']);
        if (!empty($id)) {
            $opportunities_id = array('opportunities_id !=' => $id);
        } else {
            $opportunities_id = null;
        }

        // Check if opportunity already exists
        $check_opportunity = $this->items_model->check_update('tbl_opportunities', $where, $opportunities_id);
        
        if (!empty($check_opportunity)) {
            $type = 'error';
            $msg = "<strong style='color:#000'>" . $data['opportunity_name'] . '</strong>  ' . lang('already_exist');
            log_message('error', 'Opportunity already exists this is diff: ' . json_encode($data));
        } else {
            $return_id = $this->items_model->save($data, $id);

            // Log the saved data and return ID
            log_message('info', 'Saved/Updated opportunity data: ' . json_encode($data) . ' with ID: ' . $return_id);

            if (!empty($id)) {
                $id = $id;
                $action = 'activity_update_opportunity';
                $msg = lang('update_opportunity');
                $description = 'not_update_opportunity';
            } else {
                $id = $return_id;
                $action = 'activity_save_opportunity';
                $description = 'not_save_opportunity';
                $msg = lang('save_opportunity');
            }

            save_custom_field(8, $id);

            $activity = array(
                'user' => $this->session->userdata('user_id'),
                'module' => 'opportunities',
                'module_field_id' => $id,
                'activity' => $action,
                'icon' => 'fa-filter',
                'link' => 'admin/opportunities/opportunity_details/' . $id,
                'value1' => $data['opportunity_name']
            );

            $this->items_model->_table_name = 'tbl_activities';
            $this->items_model->_primary_key = 'activities_id';
            $this->items_model->save($activity);

            // Log the activity
            log_message('info', 'Activity logged: ' . json_encode($activity));

            $type = "success";
        }

        $message = $msg;
        set_message($type, $message);

        $opportunity_info = $this->items_model->check_by(array('opportunities_id' => $id), 'tbl_opportunities');
        $notifiedUsers = array();

        if (!empty($opportunity_info->permission) && $opportunity_info->permission != 'all') {
            $permissionUsers = json_decode($opportunity_info->permission);
            foreach ($permissionUsers as $user => $v_permission) {
                array_push($notifiedUsers, $user);
            }
        } else {
            $notifiedUsers = $this->items_model->allowed_user_id('56');
        }

        if (!empty($notifiedUsers) && !empty($opportunity_info)) {
            foreach ($notifiedUsers as $users) {
                if ($users != $this->session->userdata('user_id')) {
                    add_notification(array(
                        'to_user_id' => $users,
                        'from_user_id' => true,
                        'description' => $description,
                        'link' => 'admin/opportunities/opportunity_details/' . $id,
                        'value' => lang('opportunity') . ' ' . $data['opportunity_name'],
                    ));
                }
            }
            show_notification($notifiedUsers);
        }
    }

    if (!empty($id)) {
        redirect('admin/opportunities/opportunity_details/' . $id);
    } else {
        redirect('admin/opportunities');
    }
}


    public
    function update_users($id)
    {
        // get all assign_user
        $can_edit = $this->items_model->can_action('tbl_leads', 'edit', array('leads_id' => $id));
        if (!empty($can_edit)) {
            // get permission user by menu id
            $data['assign_user'] = $this->items_model->allowed_user('55');

            $data['leads_info'] = $this->items_model->check_by(array('leads_id' => $id), 'tbl_leads');
            $data['modal_subview'] = $this->load->view('admin/leads/_modal_users', $data, FALSE);
            $this->load->view('admin/_layout_modal', $data);
        } else {
            set_message('error', lang('there_in_no_value'));
            if (empty($_SERVER['HTTP_REFERER'])) {
                redirect('admin/leads');
            } else {
                redirect($_SERVER['HTTP_REFERER']);
            }
        }
    }

    public
    function update_member($id)
    {
        $can_edit = $this->items_model->can_action('tbl_leads', 'edit', array('leads_id' => $id));
        if (!empty($can_edit)) {
            $leads_info = $this->items_model->check_by(array('leads_id' => $id), 'tbl_leads');

            $permission = $this->input->post('permission', true);
            if (!empty($permission)) {

                if ($permission == 'everyone') {
                    $assigned = 'all';
                } else {
                    $assigned_to = $this->items_model->array_from_post(array('assigned_to'));
                    if (!empty($assigned_to['assigned_to'])) {
                        foreach ($assigned_to['assigned_to'] as $assign_user) {
                            $assigned[$assign_user] = $this->input->post('action_' . $assign_user, true);
                        }
                    }
                }
                if (!empty($assigned)) {
                    if ($assigned != 'all') {
                        $assigned = json_encode($assigned);
                    }
                } else {
                    $assigned = 'all';
                }
                $data['permission'] = $assigned;
            } else {
                set_message('error', lang('assigned_to') . ' Field is required');
                if (empty($_SERVER['HTTP_REFERER'])) {
                    redirect('admin/leads');
                } else {
                    redirect($_SERVER['HTTP_REFERER']);
                }
            }

            //save data into table.
            $this->items_model->_table_name = "tbl_leads"; // table name
            $this->items_model->_primary_key = "leads_id"; // $id
            $this->items_model->save($data, $id);

            $msg = lang('update_leads');
            $activity = 'activity_update_leads';

            // save into activities
            $activities = array(
                'user' => $this->session->userdata('user_id'),
                'module' => 'leads',
                'module_field_id' => $id,
                'activity' => $activity,
                'icon' => 'fa-rocket',
                'link' => 'admin/leads/leads_details/' . $id,
                'value1' => $leads_info->lead_name,
            );
            // Update into tbl_project
            $this->items_model->_table_name = "tbl_activities"; //table name
            $this->items_model->_primary_key = "activities_id";
            $this->items_model->save($activities);

            $notifiedUsers = array();
            if (!empty($leads_info->permission) && $leads_info->permission != 'all') {
                $permissionUsers = json_decode($leads_info->permission);
                foreach ($permissionUsers as $user => $v_permission) {
                    array_push($notifiedUsers, $user);
                }
            } else {
                $notifiedUsers = $this->items_model->allowed_user_id('55');
            }
            if (!empty($notifiedUsers)) {
                foreach ($notifiedUsers as $users) {
                    if ($users != $this->session->userdata('user_id')) {
                        add_notification(array(
                            'to_user_id' => $users,
                            'from_user_id' => true,
                            'description' => 'assign_to_you_the_lead',
                            'link' => 'admin/leads/leads_details/' . $leads_info->leads_id,
                            'value' => lang('lead') . ' ' . $leads_info->lead_name,
                        ));
                    }
                }
                show_notification($notifiedUsers);
            }

            $type = "success";
            $message = $msg;
            set_message($type, $message);
        } else {
            set_message('error', lang('there_in_no_value'));
        }
        if (empty($_SERVER['HTTP_REFERER'])) {
            redirect('admin/leads');
        } else {
            redirect($_SERVER['HTTP_REFERER']);
        }
    }

    public
    function change_status($leads_id, $lead_status_id)
    {
        $can_edit = $this->items_model->can_action('tbl_leads', 'edit', array('leads_id' => $leads_id));
        if (!empty($can_edit)) {
            $data['lead_status_id'] = $lead_status_id;
            $this->items_model->_table_name = 'tbl_leads';
            $this->items_model->_primary_key = 'leads_id';
            $this->items_model->save($data, $leads_id);

            $leads_info = $this->items_model->check_by(array('leads_id' => $leads_id), 'tbl_leads');
            $notifiedUsers = array();
            if (!empty($leads_info->permission) && $leads_info->permission != 'all') {
                $permissionUsers = json_decode($leads_info->permission);
                foreach ($permissionUsers as $user => $v_permission) {
                    array_push($notifiedUsers, $user);
                }
            } else {
                $notifiedUsers = $this->items_model->allowed_user_id('55');
            }
            if (!empty($notifiedUsers)) {
                foreach ($notifiedUsers as $users) {
                    if ($users != $this->session->userdata('user_id')) {
                        add_notification(array(
                            'to_user_id' => $users,
                            'from_user_id' => true,
                            'description' => 'not_changed_status',
                            'link' => 'admin/leads/leads_details/' . $leads_info->leads_id,
                            'value' => lang('lead') . ' ' . $leads_info->lead_name,
                        ));
                    }
                }
                show_notification($notifiedUsers);
            }
            // messages for user
            $type = "success";
            $message = lang('change_status');
            set_message($type, $message);
        } else {
            set_message('error', lang('there_in_no_value'));
        }
        if (empty($_SERVER['HTTP_REFERER'])) {
            redirect('admin/leads');
        } else {
            redirect($_SERVER['HTTP_REFERER']);
        }
    }

    public
    function saved_call($leads_id, $id = NULL)
    {
        $data = $this->items_model->array_from_post(array('date', 'call_summary', 'client_id', 'user_id'));
        $data['leads_id'] = $leads_id;
        $this->items_model->_table_name = 'tbl_calls';
        $this->items_model->_primary_key = 'calls_id';
        $return_id = $this->items_model->save($data, $id);
        if (!empty($id)) {
            $id = $id;
            $action = 'activity_update_leads_call';
            $msg = lang('update_leads_call');
        } else {
            $id = $return_id;
            $action = 'activity_save_leads_call';
            $msg = lang('save_leads_call');
        }
        $activity = array(
            'user' => $this->session->userdata('user_id'),
            'module' => 'leads',
            'module_field_id' => $leads_id,
            'activity' => $action,
            'icon' => 'fa-rocket',
            'link' => 'admin/leads/leads_details/' . $leads_id . '/2',
            'value1' => $data['call_summary']
        );
        $this->items_model->_table_name = 'tbl_activities';
        $this->items_model->_primary_key = 'activities_id';
        $this->items_model->save($activity);

        $leads_info = $this->items_model->check_by(array('leads_id' => $leads_id), 'tbl_leads');
        $notifiedUsers = array();
        if (!empty($leads_info->permission) && $leads_info->permission != 'all') {
            $permissionUsers = json_decode($leads_info->permission);
            foreach ($permissionUsers as $user => $v_permission) {
                array_push($notifiedUsers, $user);
            }
        } else {
            $notifiedUsers = $this->items_model->allowed_user_id('55');
        }
        if (!empty($notifiedUsers)) {
            foreach ($notifiedUsers as $users) {
                if ($users != $this->session->userdata('user_id')) {
                    add_notification(array(
                        'to_user_id' => $users,
                        'from_user_id' => true,
                        'description' => 'not_add_call',
                        'link' => 'admin/leads/leads_details/' . $leads_info->leads_id . '/2',
                        'value' => lang('lead') . ' ' . $leads_info->lead_name,
                    ));
                }
            }
            show_notification($notifiedUsers);
        }
        // messages for user
        $type = "success";
        $message = $msg;
        set_message($type, $message);
        redirect('admin/leads/leads_details/' . $leads_id . '/' . '2');
    }

    public
    function delete_leads_call($leads_id, $id)
    {
        $calls_info = $this->items_model->check_by(array('calls_id' => $id), 'tbl_calls');
        $activity = array(
            'user' => $this->session->userdata('user_id'),
            'module' => 'leads',
            'module_field_id' => $leads_id,
            'activity' => 'activity_leads_call_deleted',
            'icon' => 'fa-rocket',
            'link' => 'admin/leads/leads_details/' . $leads_id . '/2',
            'value1' => $calls_info->call_summary
        );
        $this->items_model->_table_name = 'tbl_activities';
        $this->items_model->_primary_key = 'activities_id';
        $this->items_model->save($activity);

        $this->items_model->_table_name = 'tbl_calls';
        $this->items_model->_primary_key = 'calls_id';
        $this->items_model->delete($id);
        $type = 'success';
        $message = lang('leads_call_deleted');
        // messages for user
        echo json_encode(array("status" => $type, 'message' => $message));
        exit();
    }

    public
    function delete_leads_mettings($leads_id, $id)
    {
        $mettings_info = $this->items_model->check_by(array('mettings_id' => $id), 'tbl_mettings');

        $activity = array(
            'user' => $this->session->userdata('user_id'),
            'module' => 'leads',
            'module_field_id' => $leads_id,
            'activity' => 'activity_leads_call_deleted',
            'icon' => 'fa-rocket',
            'link' => 'admin/leads/leads_details/' . $leads_id . '/3',
            'value1' => $mettings_info->meeting_subject
        );
        $this->items_model->_table_name = 'tbl_activities';
        $this->items_model->_primary_key = 'activities_id';
        $this->items_model->save($activity);
        $this->items_model->_table_name = 'tbl_mettings';
        $this->items_model->_primary_key = 'mettings_id';
        $this->items_model->delete($id);
        $type = 'success';
        $message = lang('leads_mettings_deleted');

        echo json_encode(array("status" => $type, 'message' => $message));
        exit();
    }

    public
    function saved_metting($leads_id, $id = NULL)
    {
        $this->items_model->_table_name = 'tbl_mettings';
        $this->items_model->_primary_key = 'mettings_id';

        $data = $this->items_model->array_from_post(array('meeting_subject', 'user_id', 'location', 'description'));
        $data['start_date'] = strtotime($this->input->post('start_date', true) . ' ' . display_time($this->input->post('start_time', true)));
        $data['end_date'] = strtotime($this->input->post('end_date', true) . ' ' . display_time($this->input->post('end_time', true)));
        $data['leads_id'] = $leads_id;
        $user_id = serialize($this->items_model->array_from_post(array('attendees')));
        if (!empty($user_id)) {
            $data['attendees'] = $user_id;
        } else {
            $data['attendees'] = '-';
        }
        $return_id = $this->items_model->save($data, $id);

        if (!empty($id)) {
            $id = $id;
            $action = 'activity_update_leads_metting';
            $msg = lang('update_leads_metting');
        } else {
            $id = $return_id;
            $action = 'activity_save_leads_metting';
            $msg = lang('save_leads_metting');
        }
        $activity = array(
            'user' => $this->session->userdata('user_id'),
            'module' => 'leads',
            'module_field_id' => $leads_id,
            'activity' => $action,
            'icon' => 'fa-rocket',
            'link' => 'admin/leads/leads_details/' . $leads_id . '/3',
            'value1' => $data['meeting_subject']
        );

        $this->items_model->_table_name = 'tbl_activities';
        $this->items_model->_primary_key = 'activities_id';
        $this->items_model->save($activity);

        $leads_info = $this->items_model->check_by(array('leads_id' => $leads_id), 'tbl_leads');
        $notifiedUsers = array();
        if (!empty($leads_info->permission) && $leads_info->permission != 'all') {
            $permissionUsers = json_decode($leads_info->permission);
            foreach ($permissionUsers as $user => $v_permission) {
                array_push($notifiedUsers, $user);
            }
        } else {
            $notifiedUsers = $this->items_model->allowed_user_id('55');
        }
        if (!empty($notifiedUsers)) {
            foreach ($notifiedUsers as $users) {
                if ($users != $this->session->userdata('user_id')) {
                    add_notification(array(
                        'to_user_id' => $users,
                        'from_user_id' => true,
                        'description' => 'not_add_meetings',
                        'link' => 'admin/leads/leads_details/' . $leads_info->leads_id . '/3',
                        'value' => lang('lead') . ' ' . $leads_info->lead_name,
                    ));
                }
            }
            show_notification($notifiedUsers);
        }
        // messages for user
        $type = "success";
        $message = $msg;
        set_message($type, $message);
        redirect('admin/leads/leads_details/' . $leads_id . '/' . '3');
    }

    public
    function save_comments()
    {
        $data['leads_id'] = $this->input->post('leads_id', TRUE);
        $data['comment'] = $this->input->post('comment', TRUE);

        $files = $this->input->post("files", true);
        $target_path = getcwd() . "/uploads/";
        //process the fiiles which has been uploaded by dropzone
        if (!empty($files) && is_array($files)) {
            foreach ($files as $key => $file) {
                if (!empty($file)) {
                    $file_name = $this->input->post('file_name_' . $file, true);
                    $new_file_name = move_temp_file($file_name, $target_path);
                    $file_ext = explode(".", $new_file_name);
                    $is_image = check_image_extension($new_file_name);
                    $size = $this->input->post('file_size_' . $file, true) / 1000;
                    if ($new_file_name) {
                        $up_data[] = array(
                            "fileName" => $new_file_name,
                            "path" => "uploads/" . $new_file_name,
                            "fullPath" => getcwd() . "/uploads/" . $new_file_name,
                            "ext" => '.' . end($file_ext),
                            "size" => round($size, 2),
                            "is_image" => $is_image,
                        );
                        $success = true;
                    } else {
                        $success = false;
                    }
                }
            }
        }
        //process the files which has been submitted manually
        if ($_FILES) {
            $files = $_FILES['manualFiles'];
            if ($files && count($files) > 0) {
                foreach ($files["tmp_name"] as $key => $file) {
                    $temp_file = $file;
                    $file_name = $files["name"][$key];
                    $file_size = $files["size"][$key];
                    $new_file_name = move_temp_file($file_name, $target_path, "", $temp_file);
                    if ($new_file_name) {
                        $file_ext = explode(".", $new_file_name);
                        $is_image = check_image_extension($new_file_name);
                        $up_data[] = array(
                            "fileName" => $new_file_name,
                            "path" => "uploads/" . $new_file_name,
                            "fullPath" => getcwd() . "/uploads/" . $new_file_name,
                            "ext" => '.' . end($file_ext),
                            "size" => round($file_size, 2),
                            "is_image" => $is_image,
                        );
                    }
                }
            }
        }
        if (!empty($up_data)) {
            $data['comments_attachment'] = json_encode($up_data);
        }

        $data['user_id'] = $this->session->userdata('user_id');

        //save data into table.
        $this->items_model->_table_name = "tbl_task_comment"; // table name
        $this->items_model->_primary_key = "task_comment_id"; // $id
        $comment_id = $this->items_model->save($data);

        // save into activities
        $activities = array(
            'user' => $this->session->userdata('user_id'),
            'module' => 'leads',
            'module_field_id' => $data['leads_id'],
            'activity' => 'activity_new_leads_comment',
            'icon' => 'fa-rocket',
            'link' => 'admin/leads/leads_details/' . $data['leads_id'] . '/4',
            'value1' => $data['comment'],
        );

        // Update into tbl_project
        $this->items_model->_table_name = "tbl_activities"; //table name
        $this->items_model->_primary_key = "activities_id";
        $this->items_model->save($activities);

        if (!empty($comment_id)) {

            $leads_info = $this->items_model->check_by(array('leads_id' => $data['leads_id']), 'tbl_leads');
            $notifiedUsers = array();
            if (!empty($leads_info->permission) && $leads_info->permission != 'all') {
                $permissionUsers = json_decode($leads_info->permission);
                foreach ($permissionUsers as $user => $v_permission) {
                    array_push($notifiedUsers, $user);
                }
            } else {
                $notifiedUsers = $this->items_model->allowed_user_id('55');
            }
            if (!empty($notifiedUsers)) {
                foreach ($notifiedUsers as $users) {
                    if ($users != $this->session->userdata('user_id')) {
                        add_notification(array(
                            'to_user_id' => $users,
                            'from_user_id' => true,
                            'description' => 'not_new_comment',
                            'link' => 'admin/leads/leads_details/' . $leads_info->leads_id . '/4',
                            'value' => lang('lead') . ' ' . $leads_info->lead_name,
                        ));
                    }
                }
                show_notification($notifiedUsers);
            }
            $response_data = "";
            $view_data['comment_details'] = $this->db->where(array('task_comment_id' => $comment_id))->order_by('comment_datetime', 'DESC')->get('tbl_task_comment')->result();
            $response_data = $this->load->view("admin/leads/comments_list", $view_data, true);
            echo json_encode(array("status" => 'success', "data" => $response_data, 'message' => lang('leads_comment_save')));
            exit();
        } else {
            echo json_encode(array("status" => 'error', 'message' => lang('error_occurred')));
            exit();
        }
    }

    public function save_comments_reply($task_comment_id)
    {
        $data['leads_id'] = $this->input->post('leads_id', TRUE);
        $data['comment'] = $this->input->post('reply_comments', TRUE);
        $data['user_id'] = $this->session->userdata('user_id');
        $data['comments_reply_id'] = $task_comment_id;
        //save data into table.
        $this->items_model->_table_name = "tbl_task_comment"; // table name
        $this->items_model->_primary_key = "task_comment_id"; // $id
        $comment_id = $this->items_model->save($data);
        if (!empty($comment_id)) {

            $comments_info = $this->items_model->check_by(array('task_comment_id' => $task_comment_id), 'tbl_task_comment');
            $user = $this->items_model->check_by(array('user_id' => $comments_info->user_id), 'tbl_users');
            if ($user->role_id == 2) {
                $url = 'client/';
            } else {
                $url = 'admin/';
            }
            // save into activities
            $activities = array(
                'user' => $this->session->userdata('user_id'),
                'module' => 'leads',
                'module_field_id' => $data['leads_id'],
                'activity' => 'activity_new_comment_reply',
                'icon' => 'fa-rocket',
                'link' => $url . 'leads/leads_details/' . $data['leads_id'] . '/4',
                'value1' => $this->db->where('task_comment_id', $task_comment_id)->get('tbl_task_comment')->row()->comment,
                'value2' => $data['comment'],
            );
            // Update into tbl_project
            $this->items_model->_table_name = "tbl_activities"; //table name
            $this->items_model->_primary_key = "activities_id";
            $this->items_model->save($activities);

            $leads_info = $this->items_model->check_by(array('leads_id' => $data['leads_id']), 'tbl_leads');

            $notifiedUsers = array($comments_info->user_id);
            if (!empty($notifiedUsers)) {
                foreach ($notifiedUsers as $users) {
                    if ($users != $this->session->userdata('user_id')) {
                        add_notification(array(
                            'to_user_id' => $users,
                            'from_user_id' => true,
                            'description' => 'not_new_comment',
                            'link' => $url . 'leads/leads_details/' . $leads_info->leads_id . '/4',
                            'value' => lang('lead') . ' ' . $leads_info->lead_name,
                        ));
                    }
                }
                show_notification($notifiedUsers);
            }
            $response_data = "";
            $view_data['comment_reply_details'] = $this->db->where(array('task_comment_id' => $comment_id))->order_by('comment_datetime', 'DESC')->get('tbl_task_comment')->result();
            $response_data = $this->load->view("admin/leads/comments_reply", $view_data, true);
            echo json_encode(array("status" => 'success', "data" => $response_data, 'message' => lang('leads_comment_save')));
            exit();
        } else {
            echo json_encode(array("status" => 'error', 'message' => lang('error_occurred')));
            exit();
        }
    }

    public
    function delete_comments($task_comment_id = null)
    {
        $comments_info = $this->items_model->check_by(array('task_comment_id' => $task_comment_id), 'tbl_task_comment');

        if (!empty($comments_info->comments_attachment)) {
            $attachment = json_decode($comments_info->comments_attachment);
            foreach ($attachment as $v_file) {
                remove_files($v_file->fileName);
            }
        }
        // save into activities
        $activities = array(
            'user' => $this->session->userdata('user_id'),
            'module' => 'leads',
            'module_field_id' => $comments_info->leads_id,
            'activity' => 'activity_comment_deleted',
            'icon' => 'fa-rocket',
            'link' => 'admin/leads/leads_details/' . $comments_info->leads_id . '/4',
            'value1' => $comments_info->comment,
        );
        // Update into tbl_project
        $this->items_model->_table_name = "tbl_activities"; //table name
        $this->items_model->_primary_key = "activities_id";
        $this->items_model->save($activities);

        //save data into table.
        $this->items_model->_table_name = "tbl_task_comment"; // table name
        $this->items_model->delete_multiple(array('comments_reply_id' => $task_comment_id));
        //save data into table.
        $this->items_model->_table_name = "tbl_task_comment"; // table name
        $this->items_model->_primary_key = "task_comment_id"; // $id
        $this->items_model->delete($task_comment_id);

        echo json_encode(array("status" => 'success', 'message' => lang('task_comment_deleted')));
        exit();
    }

    public
    function save_attachment($task_attachment_id = NULL)
    {
        $data = $this->items_model->array_from_post(array('title', 'description', 'leads_id'));
        $data['user_id'] = $this->session->userdata('user_id');

        // save and update into tbl_files
        $this->items_model->_table_name = "tbl_task_attachment"; //table name
        $this->items_model->_primary_key = "task_attachment_id";
        if (!empty($task_attachment_id)) {
            $id = $task_attachment_id;
            $this->items_model->save($data, $id);
            $msg = lang('leads_file_updated');
        } else {
            $id = $this->items_model->save($data);
            $msg = lang('leads_file_added');
        }
        $files = $this->input->post("files", true);

        $target_path = getcwd() . "/uploads/";
        //process the fiiles which has been uploaded by dropzone
        if (!empty($files) && is_array($files)) {
            foreach ($files as $key => $file) {
                if (!empty($file)) {
                    $file_name = $this->input->post('file_name_' . $file, true);
                    $new_file_name = move_temp_file($file_name, $target_path);
                    $file_ext = explode(".", $new_file_name);
                    $is_image = check_image_extension($new_file_name);

                    if ($new_file_name) {
                        $up_data = array(
                            "files" => "uploads/" . $new_file_name,
                            "uploaded_path" => getcwd() . "/uploads/" . $new_file_name,
                            "file_name" => $new_file_name,
                            "size" => $this->input->post('file_size_' . $file, true),
                            "ext" => end($file_ext),
                            "is_image" => $is_image,
                            "image_width" => 0,
                            "image_height" => 0,
                            "task_attachment_id" => $id
                        );
                        $this->items_model->_table_name = "tbl_task_uploaded_files"; // table name
                        $this->items_model->_primary_key = "uploaded_files_id"; // $id
                        $uploaded_files_id = $this->items_model->save($up_data);

                        // saved into comments
                        $comment = $this->input->post('comment_' . $file, true);
                        if (!empty($comment)) {
                            $u_cdata = array(
                                "comment" => $comment,
                                "leads_id" => $data['leads_id'],
                                "user_id" => $this->session->userdata('user_id'),
                                "uploaded_files_id" => $uploaded_files_id,
                            );
                            $this->items_model->_table_name = "tbl_task_comment"; // table name
                            $this->items_model->_primary_key = "task_comment_id"; // $id
                            $this->items_model->save($u_cdata);
                        }
                        $success = true;
                    } else {
                        $success = false;
                    }
                }
            }
        }
        //process the files which has been submitted manually
        if ($_FILES) {
            $files = $_FILES['manualFiles'];
            if ($files && count($files) > 0) {
                $comment = $this->input->post('comment', true);
                foreach ($files["tmp_name"] as $key => $file) {
                    $temp_file = $file;
                    $file_name = $files["name"][$key];
                    $file_size = $files["size"][$key];
                    $new_file_name = move_temp_file($file_name, $target_path, "", $temp_file);
                    if ($new_file_name) {
                        $file_ext = explode(".", $new_file_name);
                        $is_image = check_image_extension($new_file_name);
                        $up_data = array(
                            "files" => "uploads/" . $new_file_name,
                            "uploaded_path" => getcwd() . "/uploads/" . $new_file_name,
                            "file_name" => $new_file_name,
                            "size" => $file_size,
                            "ext" => end($file_ext),
                            "is_image" => $is_image,
                            "image_width" => 0,
                            "image_height" => 0,
                            "task_attachment_id" => $id
                        );
                        $this->items_model->_table_name = "tbl_task_uploaded_files"; // table name
                        $this->items_model->_primary_key = "uploaded_files_id"; // $id
                        $uploaded_files_id = $this->items_model->save($up_data);

                        // saved into comments
                        if (!empty($comment[$key])) {
                            $u_cdata = array(
                                "comment" => $comment[$key],
                                "leads_id" => $data['leads_id'],
                                "user_id" => $this->session->userdata('user_id'),
                                "uploaded_files_id" => $uploaded_files_id,
                            );
                            $this->items_model->_table_name = "tbl_task_comment"; // table name
                            $this->items_model->_primary_key = "task_comment_id"; // $id
                            $this->items_model->save($u_cdata);
                        }
                    }
                }
            }
        }
        // save into activities
        $activities = array(
            'user' => $this->session->userdata('user_id'),
            'module' => 'leads',
            'module_field_id' => $data['leads_id'],
            'activity' => 'activity_new_leads_attachment',
            'icon' => 'fa-rocket',
            'link' => 'admin/leads/leads_details/' . $data['leads_id'] . '/5',
            'value1' => $data['title'],
        );
        // Update into tbl_project
        $this->items_model->_table_name = "tbl_activities"; //table name
        $this->items_model->_primary_key = "activities_id";
        $this->items_model->save($activities);

        $leads_info = $this->items_model->check_by(array('leads_id' => $data['leads_id']), 'tbl_leads');
        $notifiedUsers = array();
        if (!empty($leads_info->permission) && $leads_info->permission != 'all') {
            $permissionUsers = json_decode($leads_info->permission);
            foreach ($permissionUsers as $user => $v_permission) {
                array_push($notifiedUsers, $user);
            }
        } else {
            $notifiedUsers = $this->items_model->allowed_user_id('55');
        }
        if (!empty($notifiedUsers)) {
            foreach ($notifiedUsers as $users) {
                if ($users != $this->session->userdata('user_id')) {
                    add_notification(array(
                        'to_user_id' => $users,
                        'from_user_id' => true,
                        'description' => 'not_uploaded_attachment',
                        'link' => 'admin/leads/leads_details/' . $leads_info->leads_id . '/5',
                        'value' => lang('lead') . ' ' . $leads_info->lead_name,
                    ));
                }
            }
            show_notification($notifiedUsers);
        }
        // messages for user
        $type = "success";
        $message = $msg;
        set_message($type, $message);
        redirect('admin/leads/leads_details/' . $data['leads_id'] . '/' . '5');
    }

    public function new_attachment($id)
    {
        $data['dropzone'] = true;
        $data['leads_details'] = $this->items_model->check_by(array('leads_id' => $id), 'tbl_leads');
        $data['modal_subview'] = $this->load->view('admin/leads/new_attachment', $data, FALSE);
        $this->load->view('admin/_layout_modal', $data);
    }

    public function attachment_details($type, $id)
    {
        $data['type'] = $type;
        $data['attachment_info'] = $this->items_model->check_by(array('task_attachment_id' => $id), 'tbl_task_attachment');
        $data['modal_subview'] = $this->load->view('admin/leads/attachment_details', $data, FALSE);
        $this->load->view('admin/_layout_modal_extra_lg', $data);
    }

    public function save_attachment_comments()
    {
        $task_attachment_id = $this->input->post('task_attachment_id', true);
        if (!empty($task_attachment_id)) {
            $data['task_attachment_id'] = $task_attachment_id;
        } else {
            $data['uploaded_files_id'] = $this->input->post('uploaded_files_id', true);
        }
        $data['leads_id'] = $this->input->post('leads_id', true);
        $data['comment'] = $this->input->post('description', true);

        $files = $this->input->post("files", true);
        $target_path = getcwd() . "/uploads/";
        //process the fiiles which has been uploaded by dropzone
        if (!empty($files) && is_array($files)) {
            foreach ($files as $key => $file) {
                if (!empty($file)) {
                    $file_name = $this->input->post('file_name_' . $file, true);
                    $new_file_name = move_temp_file($file_name, $target_path);
                    $file_ext = explode(".", $new_file_name);
                    $is_image = check_image_extension($new_file_name);
                    $size = $this->input->post('file_size_' . $file, true) / 1000;
                    if ($new_file_name) {
                        $up_data[] = array(
                            "fileName" => $new_file_name,
                            "path" => "uploads/" . $new_file_name,
                            "fullPath" => getcwd() . "/uploads/" . $new_file_name,
                            "ext" => '.' . end($file_ext),
                            "size" => round($size, 2),
                            "is_image" => $is_image,
                        );
                        $success = true;
                    } else {
                        $success = false;
                    }
                }
            }
        }
        //process the files which has been submitted manually
        if ($_FILES) {
            $files = $_FILES['manualFiles'];
            if ($files && count($files) > 0) {
                $comment = $this->input->post('comment', true);
                foreach ($files["tmp_name"] as $key => $file) {
                    $temp_file = $file;
                    $file_name = $files["name"][$key];
                    $file_size = $files["size"][$key];
                    $new_file_name = move_temp_file($file_name, $target_path, "", $temp_file);
                    if ($new_file_name) {
                        $file_ext = explode(".", $new_file_name);
                        $is_image = check_image_extension($new_file_name);
                        $up_data[] = array(
                            "fileName" => $new_file_name,
                            "path" => "uploads/" . $new_file_name,
                            "fullPath" => getcwd() . "/uploads/" . $new_file_name,
                            "ext" => '.' . end($file_ext),
                            "size" => round($file_size, 2),
                            "is_image" => $is_image,
                        );
                    }
                }
            }
        }
        if (!empty($up_data)) {
            $data['comments_attachment'] = json_encode($up_data);
        }
        $data['user_id'] = $this->session->userdata('user_id');

        //save data into table.
        $this->items_model->_table_name = "tbl_task_comment"; // table name
        $this->items_model->_primary_key = "task_comment_id"; // $id
        $comment_id = $this->items_model->save($data);
        if (!empty($comment_id)) {
            // save into activities
            $activities = array(
                'user' => $this->session->userdata('user_id'),
                'module' => 'leads',
                'module_field_id' => $data['leads_id'],
                'activity' => 'activity_new_leads_comment',
                'icon' => 'fa-filter',
                'link' => 'admin/leads/leads_details/' . $data['leads_id'] . '/5',
                'value1' => $data['comment'],
            );
            // Update into tbl_project
            $this->items_model->_table_name = "tbl_activities"; //table name
            $this->items_model->_primary_key = "activities_id";
            $this->items_model->save($activities);

            $notifiedUsers = array();
            $leads_info = $this->items_model->check_by(array('leads_id' => $data['leads_id']), 'tbl_leads');
            $notifiedUsers = array();
            if (!empty($leads_info->permission) && $leads_info->permission != 'all') {
                $permissionUsers = json_decode($leads_info->permission);
                foreach ($permissionUsers as $user => $v_permission) {
                    array_push($notifiedUsers, $user);
                }
            } else {
                $notifiedUsers = $this->items_model->allowed_user_id('55');
            }
            if (!empty($notifiedUsers)) {
                foreach ($notifiedUsers as $users) {
                    if ($users != $this->session->userdata('user_id')) {
                        add_notification(array(
                            'to_user_id' => $users,
                            'from_user_id' => true,
                            'description' => 'not_new_comment',
                            'link' => 'admin/leads/leads_details/' . $leads_info->leads_id . '/5',
                            'value' => lang('lead') . ' ' . $leads_info->lead_name,
                        ));
                    }
                }
                show_notification($notifiedUsers);
            }
            $response_data = "";
            $view_data['comment_details'] = $this->db->where(array('task_comment_id' => $comment_id))->order_by('comment_datetime', 'DESC')->get('tbl_task_comment')->result();
            $response_data = $this->load->view("admin/leads/comments_list", $view_data, true);
            echo json_encode(array("status" => 'success', "data" => $response_data, 'message' => lang('leads_comment_save')));
            exit();
        } else {
            echo json_encode(array("status" => 'error', 'message' => lang('error_occurred')));
            exit();
        }
    }


    public
    function delete_files($task_attachment_id)
    {
        $file_info = $this->items_model->check_by(array('task_attachment_id' => $task_attachment_id), 'tbl_task_attachment');
        // save into activities
        $activities = array(
            'user' => $this->session->userdata('user_id'),
            'module' => 'leads',
            'module_field_id' => $file_info->leads_id,
            'activity' => 'activity_leads_attachfile_deleted',
            'icon' => 'fa-rocket',
            'link' => 'admin/leads/leads_details/' . $file_info->leads_id . '/5',
            'value1' => $file_info->title,
        );
        // Update into tbl_project
        $this->items_model->_table_name = "tbl_activities"; //table name
        $this->items_model->_primary_key = "activities_id";
        $this->items_model->save($activities);

        //save data into table.
        $this->items_model->_table_name = "tbl_task_attachment"; // table name
        $this->items_model->delete_multiple(array('task_attachment_id' => $task_attachment_id));


        $uploadFileinfo = $this->db->where('task_attachment_id', $task_attachment_id)->get('tbl_task_uploaded_files')->result();
        if (!empty($uploadFileinfo)) {
            foreach ($uploadFileinfo as $Fileinfo) {
                remove_files($Fileinfo->file_name);
            }
        }
        //save data into table.
        $this->items_model->_table_name = "tbl_task_uploaded_files"; // table name
        $this->items_model->delete_multiple(array('task_attachment_id' => $task_attachment_id));

        echo json_encode(array("status" => 'success', 'message' => lang('leads_attachfile_deleted')));
        exit();
    }

    public function download_files($uploaded_files_id, $comments = null)
    {
        $this->load->helper('download');
        if (!empty($comments)) {
            if ($uploaded_files_id) {
                $down_data = file_get_contents('uploads/' . $uploaded_files_id); // Read the file's contents
                force_download($uploaded_files_id, $down_data);
            } else {
                $type = "error";
                $message = 'Operation Fieled !';
                set_message($type, $message);
                if (empty($_SERVER['HTTP_REFERER'])) {
                    redirect('admin/leads');
                } else {
                    redirect($_SERVER['HTTP_REFERER']);
                }
            }
        } else {
            $uploaded_files_info = $this->items_model->check_by(array('uploaded_files_id' => $uploaded_files_id), 'tbl_task_uploaded_files');
            if ($uploaded_files_info->uploaded_path) {
                $data = file_get_contents($uploaded_files_info->uploaded_path); // Read the file's contents
                force_download($uploaded_files_info->file_name, $data);
            } else {
                $type = "error";
                $message = lang('operation_failed');
                set_message($type, $message);
                if (empty($_SERVER['HTTP_REFERER'])) {
                    redirect('admin/leads');
                } else {
                    redirect($_SERVER['HTTP_REFERER']);
                }
            }
        }
    }

    public
    function download_all_files($attachment_id)
    {
        $uploaded_files_info = $this->db->where('task_attachment_id', $attachment_id)->get('tbl_task_uploaded_files')->result();

        $attachment_info = $this->db->where('task_attachment_id', $attachment_id)->get('tbl_task_attachment')->row();
        $this->load->library('zip');
        if (!empty($uploaded_files_info)) {
            $filename = slug_it($attachment_info->title);
            foreach ($uploaded_files_info as $v_files) {
                $down_data = ($v_files->files); // Read the file's contents
                $this->zip->read_file($down_data);
            }
            $this->zip->download($filename . '.zip');
        } else {
            $type = "error";
            $message = lang('operation_failed');
            set_message($type, $message);
            if (empty($_SERVER['HTTP_REFERER'])) {
                redirect('admin/leads');
            } else {
                redirect($_SERVER['HTTP_REFERER']);
            }
        }
    }

    public function bulk_delete()
    {
        $selected_id = $this->input->post('ids', true);
        if (!empty($selected_id)) {
            foreach ($selected_id as $id) {
                $result[] = $this->delete_leads($id, true);
            }
            echo json_encode($result);
            exit();
        } else {
            $type = "error";
            $message = lang('you_need_select_to_delete');
            echo json_encode(array("status" => $type, 'message' => $message));
            exit();
        }
    }

    public
    function delete_leads($id, $bulk = null)
    {
        $deleted = can_action('55', 'deleted');
        $can_delete = $this->items_model->can_action('tbl_leads', 'delete', array('leads_id' => $id));
        if (!empty($deleted) && !empty($can_delete)) {
            $leads_info = $this->items_model->check_by(array('leads_id' => $id), 'tbl_leads');
            $activity = array(
                'user' => $this->session->userdata('user_id'),
                'module' => 'leads',
                'module_field_id' => $id,
                'activity' => 'activity_leads_deleted',
                'icon' => 'fa-rocket',
                'value1' => $leads_info->lead_name
            );
            $this->items_model->_table_name = 'tbl_activities';
            $this->items_model->_primary_key = 'activities_id';
            $this->items_model->save($activity);

            //delete data into table.
            $this->items_model->_table_name = "tbl_calls"; // table name
            $this->items_model->delete_multiple(array('leads_id' => $id));

            //delete data into table.
            $this->items_model->_table_name = "tbl_mettings"; // table name
            $this->items_model->delete_multiple(array('leads_id' => $id));

            // deleted comments with file
            $all_comments_info = $this->db->where(array('leads_id' => $id))->get('tbl_task_comment')->result();
            if (!empty($all_comments_info)) {
                foreach ($all_comments_info as $comments_info) {
                    if (!empty($comments_info->comments_attachment)) {
                        $attachment = json_decode($comments_info->comments_attachment);
                        foreach ($attachment as $v_file) {
                            remove_files($v_file->fileName);
                        }
                    }
                }
                //delete data into table.
                $this->items_model->_table_name = "tbl_task_comment"; // table name
                $this->items_model->delete_multiple(array('leads_id' => $id));
            }

            $this->items_model->_table_name = "tbl_task_attachment"; //table name
            $this->items_model->_order_by = "leads_id";
            $files_info = $this->items_model->get_by(array('leads_id' => $id), FALSE);
            if (!empty($files_info)) {
                foreach ($files_info as $v_files) {

                    $uploadFileinfo = $this->db->where('task_attachment_id', $v_files->task_attachment_id)->get('tbl_task_uploaded_files')->result();
                    if (!empty($uploadFileinfo)) {
                        foreach ($uploadFileinfo as $Fileinfo) {
                            remove_files($Fileinfo->file_name);
                        }
                    }
                    //save data into table.
                    $this->items_model->_table_name = "tbl_task_uploaded_files"; // table name
                    $this->items_model->delete_multiple(array('task_attachment_id' => $v_files->task_attachment_id));
                }
                //save data into table.
                $this->items_model->_table_name = "tbl_task_attachment"; // table name
                $this->items_model->delete_multiple(array('leads_id' => $id));
            }
            // deleted leads tasks and task comments , attachments,timer
            $leads_tasks = $this->db->where('leads_id', $id)->get('tbl_task')->result();
            if (!empty($leads_tasks)) {
                foreach ($leads_tasks as $v_taks) {

                    $all_comments_info = $this->db->where(array('task_id' => $v_taks->task_id))->get('tbl_task_comment')->result();
                    if (!empty($all_comments_info)) {
                        foreach ($all_comments_info as $comments_info) {
                            if (!empty($comments_info->comments_attachment)) {
                                $attachment = json_decode($comments_info->comments_attachment);
                                foreach ($attachment as $v_file) {
                                    remove_files($v_file->fileName);
                                }
                            }
                        }
                    }
                    //delete data into table.
                    $this->items_model->_table_name = "tbl_task_comment"; // table name
                    $this->items_model->delete_multiple(array('task_id' => $v_taks->task_id));

                    $this->items_model->_table_name = "tbl_task_attachment"; //table name
                    $this->items_model->_order_by = "task_id";
                    $files_info = $this->items_model->get_by(array('task_id' => $v_taks->task_id), FALSE);
                    if (!empty($files_info)) {
                        foreach ($files_info as $t_v_files) {
                            $uploadFileinfo = $this->db->where('task_attachment_id', $t_v_files->task_attachment_id)->get('tbl_task_uploaded_files')->result();
                            if (!empty($uploadFileinfo)) {
                                foreach ($uploadFileinfo as $Fileinfo) {
                                    remove_files($Fileinfo->file_name);
                                }
                            }
                            $this->items_model->_table_name = "tbl_task_uploaded_files"; //table name
                            $this->items_model->delete_multiple(array('task_attachment_id' => $t_v_files->task_attachment_id));
                        }
                    }
                    //delete into table.
                    $this->items_model->_table_name = "tbl_task_attachment"; // table name
                    $this->items_model->delete_multiple(array('task_id' => $v_taks->task_id));

                    //delete into table.
                    $this->items_model->_table_name = "tbl_tasks_timer"; // table name
                    $this->items_model->delete_multiple(array('task_id' => $v_taks->task_id));

                    $pin_info = $this->items_model->check_by(array('module_name' => 'tasks', 'module_id' => $v_taks->task_id), 'tbl_pinaction');
                    if (!empty($pin_info)) {
                        $this->items_model->_table_name = 'tbl_pinaction';
                        $this->items_model->delete_multiple(array('module_name' => 'tasks', 'module_id' => $v_taks->task_id));
                    }
                }
            }
            //save data into table.
            $this->items_model->_table_name = "tbl_task"; // table name
            $this->items_model->delete_multiple(array('leads_id' => $id));
            // get all proposal by leads id
            $proposal_info = $this->items_model->get_result(array('module' => 'leads', 'module_id' => $id), 'tbl_proposals');
            if (!empty($proposal_info)) {
                foreach ($proposal_info as $v_proposal) {
                    $this->items_model->_table_name = 'tbl_proposals_items';
                    $this->items_model->delete_multiple(array('proposals_id' => $v_proposal->proposals_id));

                    $this->items_model->_table_name = 'tbl_proposals';
                    $this->items_model->delete_multiple(array('proposals_id' => $v_proposal->proposals_id));
                }
            }
            $this->items_model->_table_name = 'tbl_reminders';
            $this->items_model->delete_multiple(array('module' => 'leads', 'module_id' => $id));

            $this->items_model->_table_name = 'tbl_pinaction';
            $this->items_model->delete_multiple(array('module_name' => 'leads', 'module_id' => $id));

            $this->items_model->_table_name = 'tbl_leads';
            $this->items_model->_primary_key = 'leads_id';
            $this->items_model->delete($id);

            $type = 'success';
            $message = lang('leads_deleted');
        } else {
            $type = 'error';
            $message = lang('there_in_no_value');
        }
        if (!empty($bulk)) {
            return (array("status" => $type, 'message' => $message));
        }
        echo json_encode(array("status" => $type, 'message' => $message));
        exit();
    }

    public function change_leads_status($lead_status_id)
    {
        $leads_id = $this->input->post('leads_id', true);
        foreach ($leads_id as $key => $id) {
            $data['index_no'] = $key + 1;
            //save data into table.
            $data['lead_status_id'] = $lead_status_id;
            $this->items_model->_table_name = 'tbl_leads';
            $this->items_model->_primary_key = 'leads_id';
            $this->items_model->save($data, $id);
        }
        $type = "success";
        $message = lang('update_leads');
        echo json_encode(array("status" => $type, "message" => $message));
        exit();
    }

    public function save_leads_notes($leads_id)
    {
        $data = $this->items_model->array_from_post(array('notes', 'contacted_indicator'));
        if ($data['contacted_indicator'] == 'touch_with_leads') {
            $data['last_contact'] = $this->input->post('last_contact', true);

            $ldata['last_contact'] = $data['last_contact'];
            $this->items_model->_table_name = 'tbl_leads';
            $this->items_model->_primary_key = 'leads_id';
            $this->items_model->save($ldata, $leads_id);
        }
        $data['leads_id'] = $leads_id;
        $data['user_id'] = my_id();
        //save data into table.
        $this->items_model->_table_name = 'tbl_leads_notes';
        $this->items_model->_primary_key = 'notes_id';
        $id = $this->items_model->save($data);

        // save into activities
        $activities = array(
            'user' => $this->session->userdata('user_id'),
            'module' => 'leads',
            'module_field_id' => $id,
            'activity' => 'activity_update_notes',
            'icon' => 'fa-folder-open-o',
            'link' => 'admin/leads/leads_details/' . $leads_id . '/11',
            'value1' => $data['notes'],
        );
        // Update into tbl_project
        $this->items_model->_table_name = "tbl_activities"; //table name
        $this->items_model->_primary_key = "activities_id";
        $this->items_model->save($activities);

        $type = "success";
        $message = lang('update_notes');
        set_message($type, $message);
        redirect('admin/leads/leads_details/' . $leads_id . '/' . '11');
    }


    public function delete_notes($notes_id, $leads_id)
    {
        $notes_info = get_row('tbl_leads_notes', array('notes_id' => $notes_id));
        // save into activities
        $activities = array(
            'user' => $this->session->userdata('user_id'),
            'module' => 'leads',
            'module_field_id' => $leads_id,
            'activity' => 'leads_notes_deleted',
            'icon' => 'fa-folder-open-o',
            'link' => 'admin/leads/leads_details/' . $leads_id . '/11',
            'value1' => $notes_info->notes,
        );
        // Update into tbl_project
        $this->items_model->_table_name = "tbl_activities"; //table name
        $this->items_model->_primary_key = "activities_id";
        $this->items_model->save($activities);

        $this->items_model->_table_name = 'tbl_leads_notes';
        $this->items_model->_primary_key = 'notes_id';
        $this->items_model->delete($notes_id);

        echo json_encode(array("status" => 'success', 'message' => lang('leads_notes_deleted')));
        exit();
    }


    public function save_form_data($row = null)
    {
        $form_id = $this->input->post('form_id', true);
        $formData = $this->input->post('formData', true);
        if (!empty($row)) {
            $data = $this->items_model->array_from_post(array('form_name', 'form_recaptcha', 'lead_status_id', 'language', 'lead_source_id', 'submit_btn_text', 'submit_btn_msg', 'allow_duplicate', 'notify_lead_imported'));
            if (empty($form_id)) {
                $data['form_key'] = app_generate_hash();
            }
            if ($data['allow_duplicate'] != 1) {
                $data['track_duplicate_field'] = $this->input->post('track_duplicate_field', true);
            }
            if (empty($data['form_recaptcha'])) {
                $data['form_recaptcha'] = '';
            }
            $permission = $this->input->post('permission', true);
            if (!empty($permission)) {
                if ($permission == 'everyone') {
                    $assigned = 'all';
                } else {
                    $assigned_to = $this->items_model->array_from_post(array('assigned_to'));
                    if (!empty($assigned_to['assigned_to'])) {
                        foreach ($assigned_to['assigned_to'] as $assign_user) {
                            $assigned[$assign_user] = $this->input->post('action_' . $assign_user, true);
                        }
                    }
                }
                if (!empty($assigned)) {
                    if ($assigned != 'all') {
                        $assigned = json_encode($assigned);
                    }
                } else {
                    $assigned = 'all';
                }
            } else {
                $assigned = 'all';
            }
            $data['permission'] = $assigned;
        } else {
            // form data should be always sent to the request and never should be empty
            // this code is added to prevent losing the old form in case any errors
            if (isset($formData) || !isset($formData) && $formData) {
                // If user paste with styling eq from some editor word and the Codeigniter XSS feature remove and apply xss=remove, may break the json.
                $data['form_data'] = preg_replace('/=\\\\/m', "=''", $formData);
            }
        }
        $this->items_model->_table_name = 'tbl_lead_form';
        $this->items_model->_primary_key = 'lead_form_id';
        $id = $this->items_model->save($data, $form_id);
        if (!empty($row)) {
            set_message('success', lang('update_lead_form'));
            redirect('admin/leads/lead_form/' . $id);
        } else {
            if (!empty($id)) {
                echo json_encode([
                    'success' => true,
                    'message' => lang('update_lead_form'),
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                ]);
            }
            exit();
        }
    }

    public function all_lead_form()
    {
        $data['title'] = lang('all') . ' ' . lang('lead_form');
        $data['load_setting'] = 'admin/leads/all_lead_form';
        $can_do = can_do(161);
        if (!empty($can_do)) {
            $data['subview'] = $this->load->view('admin/settings/settings', $data, TRUE);
        } else {
            $data['subview'] = $this->load->view('admin/settings/not_found', $data, TRUE);
        }
        $this->load->view('admin/_layout_main', $data); //page load
    }

    public function leadFormList()
    {
        if ($this->input->is_ajax_request()) {
            $this->load->model('datatables');
            $this->datatables->table = 'tbl_lead_form';
            $main_column = array('lead_form_id', 'form_name', 'create_date', 'create_date');
            $action_array = array('lead_form_id');
            $result = array_merge($main_column, $action_array);
            $this->datatables->column_order = $result;
            $this->datatables->column_search = $result;
            $this->datatables->order = array('lead_form_id' => 'ASC');

            $where = array();


            $fetch_data = make_datatables();

            $data = array();

            $edited = can_action('161', 'edited');
            $deleted = can_action('161', 'deleted');
            foreach ($fetch_data as $_key => $v_leads) {
                $action = null;
                if (!empty($v_leads)) {

                    $sub_array = array();
                    if (!empty($deleted)) {
                        $sub_array[] = '<div class="checkbox c-checkbox" ><label class="needsclick"> <input value="' . $v_leads->lead_form_id . '" type="checkbox"><span class="fa fa-check"></span></label></div>';
                    }
                    $sub_array[] = $v_leads->lead_form_id;
                    $name = null;
                    $name .= '<a class="text-info" href="' . base_url() . 'admin/leads/lead_form/' . $v_leads->lead_form_id . '">' . $v_leads->form_name . '</a>';
                    $sub_array[] = $name;

                    $sub_array[] = total_rows('tbl_leads', array('from_form_id' => $v_leads->lead_form_id));
                    $sub_array[] = time_ago($v_leads->create_date);

                    if (!empty($edited)) {
                        $action .= btn_edit('admin/leads/lead_form/' . $v_leads->lead_form_id) . ' ';
                    }
                    if (!empty($deleted)) {
                        $action .= ajax_anchor(base_url("admin/leads/delete_lead_form/$v_leads->lead_form_id"), "<i class='btn btn-xs btn-danger fa fa-trash-o'></i>", array("class" => "", "title" => lang('delete'), "data-fade-out-on-success" => "#table_" . $_key)) . ' ';
                    }
                    $sub_array[] = $action;
                    $data[] = $sub_array;
                }
            }
            render_table($data, $where);
        } else {
            redirect('admin/dashboard');
        }
    }

    public function lead_form($id = NULL)
    {
        $data['title'] = lang('lead_form');

        $created = can_action('128', 'created');
        $edited = can_action('128', 'edited');
        if (!empty($id)) {
            $data['form'] = get_row('tbl_lead_form', array('lead_form_id' => $id));
            if (!empty($data['form'])) {
                $data['title'] = $data['form']->form_name . ' - ' . lang('web_to_lead_form');
                $data['formData'] = $data['form']->form_data;
            }
        }

        $data['customFields'] = form_custom_fields(5);

        // get all leads status
        $status_info = $this->db->order_by('order_no', 'ASC')->get('tbl_lead_status')->result();
        if (!empty($status_info)) {
            foreach ($status_info as $v_status) {
                $data['status_info'][$v_status->lead_type][] = $v_status;
            }
        }
        $data['languages'] = $this->db->where('active', 1)->order_by('name', 'ASC')->get('tbl_languages')->result();

        $data['assign_user'] = $this->items_model->allowed_user('55');

        $db_fields = [];
        $fields = [
            'lead_name',
            'organization',
            'email',
            'phone',
            'mobile',
            'company_name',
            'contact_name',
            'address',
            'city',
            'state',
            'country',
            'zip',
            'notes',
            'website',
        ];


        $className = 'form-control';

        foreach ($fields as $f) {
            $_field_object = new stdClass();
            $type = 'text';
            $subtype = '';
            if ($f == 'email') {
                $subtype = 'email';
            } elseif ($f == 'notes' || $f == 'address') {
                $type = 'textarea';
            } elseif ($f == 'country') {
                $type = 'select';
            }

            $label = lang($f);
            $field_array = [
                'subtype' => $subtype,
                'type' => $type,
                'label' => $label,
                'className' => $className,
                'name' => $f,
            ];

            if ($f == 'country') {
                $field_array['values'] = [];

                $field_array['values'][] = [
                    'label' => '',
                    'value' => '',
                    'selected' => false,
                ];

                $countries = $this->db->get('tbl_countries')->result();
                foreach ($countries as $country) {
                    $selected = false;
                    if (config_item('company_country') == $country->value) {
                        $selected = true;
                    }
                    array_push($field_array['values'], [
                        'label' => $country->value,
                        'value' => $country->value,
                        'selected' => $selected,
                    ]);
                }
            }

            if ($f == 'lead_name') {
                $field_array['required'] = true;
            }

            $_field_object->label = $label;
            $_field_object->name = $f;
            $_field_object->fields = [];
            $_field_object->fields[] = $field_array;
            $db_fields[] = $_field_object;
        }
        $data['db_fields'] = $db_fields;
        //        $show_custom_fields = Leadcustom_form_Fields(5);

        $data['subview'] = $this->load->view('admin/leads/lead_form', $data, TRUE);
        $this->load->view('admin/_layout_main', $data); //page load
    }

    public function bulkLeadFormDelete()
    {
        $selected_id = $this->input->post('ids', true);
        if (!empty($selected_id)) {
            foreach ($selected_id as $id) {
                $result[] = $this->delete_lead_form($id, true);
            }
            echo json_encode($result);
            exit();
        } else {
            $type = "error";
            $message = lang('you_need_select_to_delete');
            echo json_encode(array("status" => $type, 'message' => $message));
            exit();
        }
    }

    public function delete_lead_form($id, $bulk = null)
    {
        $deleted = can_action('161', 'deleted');
        if (!empty($deleted)) {
            $lead_form = $this->items_model->check_by(array('lead_form_id' => $id), 'tbl_lead_form');
            $activity = array(
                'user' => $this->session->userdata('user_id'),
                'module' => 'settings',
                'module_field_id' => $id,
                'activity' => ('activity_delete_a_lead_form'),
                'value1' => $lead_form->lead_form,
            );
            $this->items_model->_table_name = 'tbl_activities';
            $this->items_model->_primary_key = 'activities_id';
            $this->items_model->save($activity);

            $this->items_model->_table_name = 'tbl_lead_form';
            $this->items_model->_primary_key = 'lead_form_id';
            $this->items_model->delete($id);

            // messages for user
            $type = "success";
            $message = lang('lead_form_deleted');

            if (!empty($bulk)) {
                return (array("status" => $type, 'message' => $message));
            }
            // messages for user
            echo json_encode(array("status" => $type, 'message' => $message));
            exit();
        } else {
            echo json_encode(array("status" => 'error', 'message' => lang('there_in_no_value')));
            exit();
        }
    }
    public function save_tender($leads_id){
        // echo "<pre>";
        // print_r($_POST);
        // exit();
        $data['leads_id']   = $leads_id;
        $data['tender_name']        = $_POST['tender_name'];
        $data['tender_budget']      = $_POST['tender_budget'];
        $data['tender_startdate']   = $_POST['tender_startdate'];
        $data['tender_enddate']      = $_POST['tender_enddate'];
        $data['tender_eligibility'] = $_POST['tender_eligibility'];
        
        $config= $this->set_upload();
        $this->load->library('upload');
        $this->upload->initialize($config);
        
        if(!empty($_FILES['tender_attachment']['name'])){

            if(!$this->upload->do_upload('tender_attachment')){
                $error = $this->upload->display_errors();
   
            
                redirect('admin//leads/leads_details');
            }else{
                $data['tender_attachment'] = $this->upload->data('file_name');
            }
        }
        // print_r($data);
        // exit();
        $this->db->insert('tbl_leads_tenders',$data);
        $type = 'success';
        $message = lang('tender_saved');
        set_message($type, $message);
        
        
        redirect('admin/leads/leads_details/' . $leads_id . '/' . '2');
        
        
    }
    
    public function delete_tender($leads_id, $id)
    {

        $data['deletion_indicator'] = 1;
        $this->db->where('leads_id',$id);
        $this->db->update('tbl_leads_tenders',$data);
        $type = 'success';
        $message = lang('tender_deleted');
        set_message($type, $message);
        redirect('admin/leads/leads_details/' . $leads_id . '/' . '10');
    }
    
    private function set_upload() {   
        $config = array();
        $config['upload_path'] = 'uploads/tender';
        $config['allowed_types'] = 'jpg|png|jpeg|pdf';
        // $config['max_size']      = '50000';
        $config['overwrite']     = FALSE;
        return $config;
    }
    
    public function update_tender($leads_id,$id){
        // echo "<pre>";
        // print_r($_POST);
        // exit();
        $data['tender_name']        = $_POST['tender_name'];
        $data['tender_budget']      = $_POST['tender_budget'];
        $data['tender_startdate']   = $_POST['tender_startdate'];
        $data['tender_enddate']      = $_POST['tender_enddate'];
        $data['tender_eligibility'] = $_POST['tender_eligibility'];
        
        $config= $this->set_upload();
        $this->load->library('upload');
        $this->upload->initialize($config);
        
        if(!empty($_FILES['tender_attachment']['name'])){

            if(!$this->upload->do_upload('tender_attachment')){
                $error = $this->upload->display_errors();
   
            
                redirect('admin/leads/leads_details');
            }else{
                $data['tender_attachment'] = $this->upload->data('file_name');
            }
        }
        // print_r($data);
        // exit();
        $this->db->where('leads_id',$id);
        $this->db->update('tbl_leads_tenders',$data);
        $type = 'success';
        $message = lang('tender_updated');
        set_message($type, $message);
        
        
        redirect('admin/leads/leads_details/' . $leads_id . '/' . '10');
        
        
    }
}