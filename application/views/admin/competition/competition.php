<?= message_box('success'); ?>
<?= message_box('error'); ?>
<script src="<?php echo base_url(); ?>assets/plugins/bootstrap-tagsinput/fm.tagator.jquery.js"></script>
<?php $competition_data = $this->db->select('*')->from('tbl_competition')->where(array('deletion_indicator'=>0))->get()->result_array();?>


<?php
$created = can_action(6, 'created');
$edited = can_action(6, 'edited');
$deleted = can_action(6, 'deleted');

if (!empty($created) || !empty($edited)){
?>
<div class="row">
    <div class="col-sm-12">
        <div class="nav-tabs-custom">
            <!-- Tabs within a box -->
            <ul class="nav nav-tabs">
                <li class="<?= $active == 1 ? 'active' : ''; ?>"><a href="#manage"
                                                                    data-toggle="tab"><?= lang('All Competition') ?></a>
                </li>
                <li class="<?= $active == 2 ? 'active' : ''; ?>"><a href="#new" id="form_tab"
                                                                    data-toggle="tab"><?= lang('New competition') ?></a>
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
                            <table class="table table-striped"  cellspacing="0" width="100%">
                                <thead>
        		            		<tr>
        		            		    <th>Name</th>
        			            		<th>Bus Specifications</th>
        			            		<th>No Order</th>
        			            		<th>State</th>
        			            		<th>Buses/Order</th>
        			            		<th>Specification/Order</th>
        			            		<th>Schedule</th>
        			            		<th>Feedback</th>
                                        <th>Address</th>
                                        <th>State</th>
                                        <th>Brief</th>
                                        <th>Strength</th>
                                        <th>Weakness</th>
        			            		<th>Actions</th>
        		            		</tr>
        		            	</thead>
                                <tbody>
                                 <?php foreach ($competition_data as $row){
                                    $row_data = $this->db->select('*')->from('tbl_saved_items')->where(array('saved_items_id'=>$row['bus_items_id']))->get()->row_array();
                                    ?>
                                    <tr>
                                        <td><?php echo $row['name'];?></td>
                                        <td><?php echo $row_data['item_name'];?></td>
                                        <td><?php echo $row['total_order'];?></td>
                                        <td><?php echo $row['order_state'];?></td>
                                        <td><?php echo $row['total_buses'];?></td>
                                        <td><?php echo $row['order_specification'];?></td>
                                        <td><?php echo $row['delivery'];?></td>
                                        <td><?php echo $row['feedback'];?></td>
                                        <td><?php echo $row['address'];?></td>
                                        <td><?php echo $row['state'];?></td>
                                        <td><?php echo $row['brief'];?></td>
                                        <td><?php echo $row['strength'];?></td>
                                        <td><?php echo $row['weakness'];?></td>
                                        <td>
                                            <?= btn_edit('admin/competition/edit_competition/'. $row['competition_id']) ?>
                                            <a href="<?php echo base_url("admin/competition/delete_competition/" . $row['competition_id'])?>" onclick=" return confirm('Are you sure you want to delete this tender?');"  title="DELETE" ><i class="btn btn-danger btn-xs fa fa-trash-o"></i> </a>
                                       </td>
                                    </tr>
                                 <?php }?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <?php if (!empty($created) || !empty($edited)) { ?>
                        <div class="tab-pane <?= $active == 2 ? 'active' : ''; ?>" id="new">
                            <?php $this->load->view("admin/competition/new_competition"); ?>
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