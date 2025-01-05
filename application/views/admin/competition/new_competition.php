<?php 
//print_r($competition_info);
?>
    <?php if(!empty($competition_info)):?>
        <form role="form" enctype="multipart/form-data" id="form"
              action="<?php echo base_url("admin/competition/update_competition/" . $competition_info->competition_id)?>" method="post" class="form-horizontal">
               <input type="hidden" name="competition_id" value="<?php echo $competition_info->competition_id;?>">
    <?php else:?> 
        <form role="form" enctype="multipart/form-data" id="form"
              action="<?php echo base_url(); ?>admin/competition/create_competition" method="post" class="form-horizontal">
            
    <?php endif;?>

    <div class="form-group">
        <label class="col-lg-3 control-label"><?= lang('Competition Name') ?> <span
                class="text-danger">*</span></label>
        <div class="col-lg-5">
            <input type="text" min="0" value="<?php
            if (!empty($competition_info)) {
                echo $competition_info->name;
            }
            ?>" class="form-control" name="name"
                   required>
        </div>
    </div>

    <div class="form-group">
        <label class="col-lg-3 control-label"><?= lang('Bus Specification') ?> <span
                class="text-danger">*</span></label>
        <div class="col-lg-4">
            <div class="input-group">
                <select name="bus_items_id" class="form-control select_box" style="width: 100% !important;"  required="">
                    <?php
                    $bus_items_info = $this->db->order_by('saved_items_id', 'DESC')->get('tbl_saved_items')->result();
                    if (!empty($bus_items_info)) {
                        foreach ($bus_items_info as $row) {
                            ?>
                            <option
                                    value="<?= $row->saved_items_id ?>" <?php
                                if (!empty($competition_info->bus_items_id)) {
                                    echo $row->saved_items_id == $competition_info->bus_items_id ? 'selected' : '';
                                }
                                ?>><?= $row->item_name ?>
                            </option>
                            <?php
                        }
                    }
                    $_created = can_action('128', 'created');
                    ?>
                </select>
            </div>
        </div>
    </div>

    <div class="form-group">
        <label class="col-lg-3 control-label"><?= lang('No Of Orders') ?> <span
                class="text-danger">*</span></label>
        <div class="col-lg-5">
            <input type="text" min="0" value="<?php
            if (!empty($competition_info)) {
                echo $competition_info->total_order;
            }
            ?>" class="form-control" name="total_order"
                   required>
        </div>
    </div>
   
    <div class="form-group">
            <label class="col-lg-3 control-label"><?= lang('Ordering State') ?> <span
                    class="text-danger">*</span>
            </label>
        <div class="col-lg-5">
            <input type="text" class="form-control" value="<?php 
            if (!empty($competition_info)) {
                echo $competition_info->order_state;
            } 
            ?>" name="order_state" required>
        </div>
    </div>
    
    <div class="form-group">
        <label class="col-lg-3 control-label"><?= lang('No of buses for each order') ?></label>
        <div class="col-lg-5">
            <input type="text" class="form-control" value="<?php 
            if (!empty($competition_info)) {
                echo $competition_info->total_buses;
            } 
            ?>" name="total_buses" required>
        </div>
    </div>

    <div class="form-group">
        <label class="col-lg-3 control-label"><?= lang('Bus Specification as per order') ?> <span
                class="text-danger">*</span>
        </label>
        <div class="col-lg-5">
            <input type="text" class="form-control" value="<?php 
            if (!empty($competition_info)) {
                echo $competition_info->order_specification;
            } 
            ?>" name="order_specification" required>
        </div>
    </div>
    <div class="form-group">
        <label class="col-lg-3 control-label"><?= lang('Delivery Schedule') ?> <span
                class="text-danger">*</span>
        </label>
        <div class="col-lg-5">
            <input type="text" class="form-control" value="<?php 
            if (!empty($competition_info)) {
                echo $competition_info->delivery;
            } 
            ?>" name="delivery" required>
        </div>
    </div>

    <div class="form-group">
        <label class="col-lg-3 control-label"><?= lang('Delivery Feedback') ?> <span
                class="text-danger">*</span>
        </label>
        <div class="col-lg-5">
            <input type="text" class="form-control" value="<?php 
            if (!empty($competition_info)) {
                echo $competition_info->feedback;
            } 
            ?>" name="feedback" required>
        </div>
    </div>
    
    <div class="form-group">
        <label class="col-lg-3 control-label"><?= lang('Address') ?> <span
                class="text-danger">*</span>
        </label>
        <div class="col-lg-5">
            <input type="text" class="form-control" value="<?php 
            if (!empty($competition_info)) {
                echo $competition_info->address;
            } 
            ?>" name="address" required>
        </div>
    </div>

    <div class="form-group">
        <label class="col-lg-3 control-label"><?= lang('State') ?> <span
                class="text-danger">*</span>
        </label>
        <div class="col-lg-5">
            <input type="text" class="form-control" value="<?php 
            if (!empty($competition_info)) {
                echo $competition_info->state;
            } 
            ?>" name="state" required>
        </div>
    </div>

    <div class="form-group">
        <label class="col-lg-3 control-label"><?= lang('Company Brief') ?> <span
                class="text-danger">*</span>
        </label>
        <div class="col-lg-5">
            <input type="text" class="form-control" value="<?php 
            if (!empty($competition_info)) {
                echo $competition_info->brief;
            } 
            ?>" name="brief" required>
        </div>
    </div>

    <div class="form-group">
        <label class="col-lg-3 control-label"><?= lang('Company Strength') ?> <span
                class="text-danger">*</span>
        </label>
        <div class="col-lg-5">
            <input type="text" class="form-control" value="<?php 
            if (!empty($competition_info)) {
                echo $competition_info->strength;
            } 
            ?>" name="strength" required>
        </div>
    </div>

    <div class="form-group">
        <label class="col-lg-3 control-label"><?= lang('Competition Weakness') ?> <span
                class="text-danger">*</span>
        </label>
        <div class="col-lg-5">
            <input type="text" class="form-control" value="<?php 
            if (!empty($competition_info)) {
                echo $competition_info->weakness;
            } 
            ?>" name="weakness" required>
        </div>
    </div>
  

    <div class="btn-bottom-toolbar text-right">
        <?php
        if (!empty($competition_info)) { ?>
            <button type="submit" id="file-save-button"
                    class="btn btn-sm btn-primary"><?= lang('updates') ?></button>
            <button type="button" onclick="goBack()"
                    class="btn btn-sm btn-danger"><?= lang('cancel') ?></button>
        <?php } else {
            ?>
            <button type="submit" id="file-save-button"
                    class="btn btn-sm btn-primary"><?= lang('Create Competition') ?></button>
        <?php }
        ?>
    </div>
</form>
