<?= message_box('success'); ?>
<?= message_box('error'); ?>
<script src="<?php echo base_url(); ?>assets/plugins/bootstrap-tagsinput/fm.tagator.jquery.js"></script>
<?php $whole_tender_data = $this->db->select('*')->from('tbl_whole_tender')->where(array('deletion_indicator'=>0))->get()->result_array();?>


<?php
$created = can_action(6, 'created');
$edited = can_action(6, 'edited');
$deleted = can_action(6, 'deleted');

if (!empty($created) || !empty($edited)){
?>
<div class="row">
    <div class="col-sm-12">
        <?php $is_department_head = is_department_head();
        if ($this->session->userdata('user_type') == 1 || !empty($is_department_head)) { ?>
            <div class="btn-group pull-right btn-with-tooltip-group _filter_data filtered" data-toggle="tooltip"
                 data-title="<?php echo lang('filter_by'); ?>">
                <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown"
                        aria-haspopup="true" aria-expanded="false">
                    <i class="fa fa-filter" aria-hidden="true"></i>
                </button>
                <ul class="dropdown-menu group animated zoomIn"
                    style="width:300px;">
                    <li class="filter_by all_filter"><a href="#"><?php echo lang('all'); ?></a></li>
                    <li class="divider"></li>

                    <li class="filter_by" id="assigned_to_me"><a href="#"><?php echo lang('assigned_to_me'); ?></a></li>
                    <?php if (admin()) { ?>
                        <li class="filter_by" id="everyone"
                            search-type="by_staff">
                            <a href="#"><?php echo lang('assigned_to') . ' ' . lang('everyone'); ?></a>
                        </li>
                    <?php } ?>
                    <li class="dropdown-submenu pull-left  " id="from_account">
                        <a href="#" tabindex="-1"><?php echo lang('by') . ' ' . lang('project'); ?></a>
                        <ul class="dropdown-menu dropdown-menu-left from_account"
                            style="">
                            <?php
                            $project_info = $this->items_model->get_permission('tbl_project');
                            if (!empty($project_info)) {
                                foreach ($project_info as $v_project) {
                                    ?>
                                    <li class="filter_by" id="<?= $v_project->project_id ?>" search-type="by_project">
                                        <a href="#"><?php echo $v_project->project_name; ?></a>
                                    </li>
                                <?php }
                            }
                            ?>
                        </ul>
                    </li>
                    <div class="clearfix"></div>
                    <li class="dropdown-submenu pull-left  " id="from_reporter">
                        <a href="#" tabindex="-1"><?php echo lang('by') . ' ' . lang('reporter'); ?></a>
                        <ul class="dropdown-menu dropdown-menu-left from_reporter"
                            style="">
                            <?php
                            $reporter_info = $this->db->get('tbl_users')->result();;
                            if (!empty($reporter_info)) {
                                foreach ($reporter_info as $v_reporter) {
                                    ?>
                                    <li class="filter_by" id="<?= $v_reporter->user_id ?>" search-type="by_reported">
                                        <a href="#"><?php echo fullname($v_reporter->user_id); ?></a>
                                    </li>
                                <?php }
                            }
                            ?>
                        </ul>
                    </li>
                    <div class="clearfix"></div>
                    <li class="dropdown-submenu pull-left " id="to_account">
                        <a href="#" tabindex="-1"><?php echo lang('by') . ' ' . lang('department'); ?></a>
                        <ul class="dropdown-menu dropdown-menu-left to_account"
                            style="">
                            <?php
                            $department_info = get_result('tbl_departments');
                            if (count($department_info) > 0) { ?>
                                <?php foreach ($department_info as $v_department) {
                                    ?>
                                    <li class="filter_by" id="<?= $v_department->departments_id ?>"
                                        search-type="by_department">
                                        <a href="#"><?php echo $v_department->deptname; ?></a>
                                    </li>
                                <?php }
                                ?>
                                <div class="clearfix"></div>
                            <?php } ?>
                        </ul>
                    </li>
                </ul>
            </div>
        <?php } ?>
        <div class="nav-tabs-custom">
            <!-- Tabs within a box -->
            <ul class="nav nav-tabs">
                <li class="<?= $active == 1 ? 'active' : ''; ?>"><a href="#manage"
                                                                    data-toggle="tab"><?= lang('Whole Tender') ?></a>
                </li>
                <li class="<?= $active == 2 ? 'active' : ''; ?>"><a href="#new" id="form_tab"
                                                                    data-toggle="tab"><?= lang('New Tender') ?></a>
                </li>
            </ul>
            <style type="text/css">
                .custom-bulk-button {
                    display: initial;
                }
            </style>
            <div class="tab-content bg-white">
                <!-- ************** general *************-->
                <div class="tab-pane <?= $active == 1 ? 'active' : ''; ?>" id="manage">
                    <?php } else { ?>
                    <div class="panel panel-custom">
                        <header class="panel-heading ">
                            <div class="panel-title"><strong><?= lang('tickets') ?></strong></div>
                        </header>
                        <?php } ?>
                        <div class="table-responsive">
                            <table class="table table-bordered"  cellspacing="0" width="100%" style="margin-left: -20px;">
                                <thead>
        		            		<tr>
        		            		    <th  width="50%">Authority /Agency</th>
        			            		<th>Electric Bus</th>
        			            		<th>CAPEX /OPEX /GCC</th>
        			            		<th>Tender Amount</th>
        			            		<th>Security EMD</th>
        			            		<th colspan="9" style="text-align:center;">Tender Specification/Bus specification</th>
        			            		<th>Opening Date</th>
        			            		<th>Closing Date</th>
        			            		<th>Actions</th>
        			            		
        		            		</tr>
        		            		<tr>
        		            		    <th></th>
        		            		    <th></th>
        		            		    <th></th>
        			            		<th></th>
        			            		<th></th>
        			            		<th>AC/Non-AC</th>
        			            		<th>Length</th>
        			            		<th>Height</th>
        			            		<th>Width</th>
        			            		<th>Seating +D</th>
        			            		<th>Floor Height</th>
        			            		<th>Range</th>
        			            		<th>Warranty</th>
        			            		<th>AMC</th>
        			            		<th></th>
        			            		<th></th>
        			            		<th></th>
        			            		
        			            	</tr>	
        		            	</thead>
                                <tbody>
                                 <?php foreach ($whole_tender_data as $row){
                                    $gency_data = $this->db->select('*')->from('tbl_agency_source')->where(array('agency_id'=>$row['agency_id']))->get()->row_array();
                                    ?>
                                    <tr>
                                        <td><?php echo $gency_data['agency_name'];?></td>
                                        <td><?php echo $row['electric_bus_type'];?></td>
                                        <td><?php echo $row['capex'];?></td>
                                        <td><?php echo $row['tender_amount'];?></td>
                                        <td><?php echo $row['security'];?></td>
                                        <td><?php echo $row['ac'];?></td>
                                        <td><?php echo $row['length'];?></td>
                                        <td><?php echo $row['height'];?></td>
                                        <td><?php echo $row['width'];?></td>
                                        <td><?php echo $row['seating'];?></td>
                                        <td><?php echo $row['floor_height'];?></td>
                                        <td><?php echo $row['bus_range'];?></td>
                                        <td><?php echo $row['warranty'];?></td>
                                        <td><?php echo $row['amc'];?></td>
                                        <td><?php echo date('d-m-Y',strtotime($row['opening_date']));?></td>
                                        <td><?php echo date('d-m-Y',strtotime($row['closing_date']));?></td>
                                        <td>
                                            <?= btn_edit('admin/whole_tender/edit_whole_tender/'. $row['whole_tender_id']) ?>
                                            <a href="<?php echo base_url("admin/whole_tender/delete_whole_tender/" . $row['whole_tender_id'])?>" onclick=" return confirm('Are you sure you want to delete this tender?');"  title="DELETE" ><i class="btn btn-danger btn-xs fa fa-trash-o"></i> </a>
                                       </td>
                                    </tr>
                                 <?php }?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <?php if (!empty($created) || !empty($edited)) { ?>
                        <div class="tab-pane <?= $active == 2 ? 'active' : ''; ?>" id="new">
                            <?php $this->load->view("admin/whole_tender/new_whole_tender"); ?>
                        </div>
                    <?php } else { ?>
                </div>
                <?php } ?>
            </div>
        </div>
    </div>
</div>


<script>
    $(document).ready(function () {  ins_data(base_url+'admin/tickets/tickets_state_report');   });
</script>

<script>
    $(document).ready(function () {  $("#form_tab").on("click", function(){
        if($('#new_form').length){   }
       // else{ins_data(base_url+'admin/tickets/new_ticket_form');}
    }); });
</script>