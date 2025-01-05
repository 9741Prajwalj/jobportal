<?php 
//print_r($tender_info);
?>
    <?php if(!empty($tender_info)):?>
        <form role="form" enctype="multipart/form-data" id="form"
              action="<?php echo base_url("admin/whole_tender/update_whole_tender/" . $tender_info->whole_tender_id)?>" method="post" class="form-horizontal">
               <input type="hidden" name="whole_tender_id" value="<?php echo $tender_info->whole_tender_id;?>">
    <?php else:?> 
        <form role="form" enctype="multipart/form-data" id="form"
              action="<?php echo base_url(); ?>admin/whole_tender/create_whole_tender" method="post" class="form-horizontal">
            
    <?php endif;?>

    <div class="form-group">
        <label class="col-lg-3 control-label"><?= lang('Authority/Agency') ?> <span
                class="text-danger">*</span></label>
        <div class="col-lg-4">
            <div class="input-group">
                <select name="agency_id" class="form-control select_box" style="width: 100%"  required="">
                    <?php
                    $agency_source_info = $this->db->order_by('agency_id', 'DESC')->get('tbl_agency_source')->result();
                    if (!empty($agency_source_info)) {
                        foreach ($agency_source_info as $v_agency_source) {
                            ?>
                            <option
                                    value="<?= $v_agency_source->agency_id ?>" <?php
                                if (!empty($tender_info->agency_id)) {
                                    echo $v_agency_source->agency_id == $tender_info->agency_id ? 'selected' : '';
                                }
                                ?>><?= $v_agency_source->agency_name ?>
                            </option>
                            <?php
                        }
                    }
                    $_created = can_action('128', 'created');
                    ?>
                </select>
                <?php if (!empty($_created)) { ?>
                    <div class="input-group-addon"
                         title="<?= lang('new') . ' ' . lang('Agency Source') ?>"
                         data-toggle="tooltip" data-placement="top">
                        <a data-toggle="modal" data-target="#myModal"
                           href="<?= base_url() ?>admin/whole_tender/whole_tender_source"><i
                                    class="fa fa-plus"></i></a>
                    </div>
                <?php } ?>
            </div>
        </div>
    </div>

    <div class="form-group">
        <label class="col-lg-3 control-label"><?= lang('No Of Buses - Electric Bus') ?> <span
                class="text-danger">*</span></label>
        <div class="col-lg-5">
            <input type="number" min="0" value="<?php
            if (!empty($tender_info)) {
                echo $tender_info->no_of_buses;
            }
            ?>" class="form-control" name="no_of_buses"
                   required>
        </div>
    </div>
   
    <div class="form-group">
            <label class="col-lg-3 control-label"><?= lang('Electric Bus Type') ?> <span
                    class="text-danger">*</span>
            </label>
        <div class="col-lg-5">
            <input type="text" class="form-control" value="<?php 
            if (!empty($tender_info)) {
                echo $tender_info->electric_bus_type;
            } 
            ?>" name="electric_bus_type" required>
        </div>
    </div>
    <div class="form-group">
        <label class="col-lg-3 control-label"><?= lang('CAPEX/OPEX/GCC') ?></label>
        <div class="col-lg-5">
            <input type="text" class="form-control" value="<?php 
            if (!empty($tender_info)) {
                echo $tender_info->capex;
            } 
            ?>" name="capex" required>
        </div>
    </div>

    <div class="form-group">
        <label class="col-lg-3 control-label"><?= lang('Tender Amount') ?> <span
                class="text-danger">*</span>
        </label>
        <div class="col-lg-5">
            <input type="text" class="form-control" value="<?php 
            if (!empty($tender_info)) {
                echo $tender_info->tender_amount;
            } 
            ?>" name="tender_amount" required>
        </div>
    </div>
    <div class="form-group">
        <label class="col-lg-3 control-label"><?= lang('Security/EMD') ?> <span
                class="text-danger">*</span>
        </label>
        <div class="col-lg-5">
            <input type="text" class="form-control" value="<?php 
            if (!empty($tender_info)) {
                echo $tender_info->security;
            } 
            ?>" name="security" required>
        </div>
    </div>
    <div class="form-group">
        <label for="field-1"
               class="col-sm-3 control-label"><?= lang('Tender Specification/Bus specification') ?></label><br>
        <div class="col-sm-3">
            <label class="col-lg-6 control-label"><?= lang('AC/Non-AC') ?> <span class="text-danger">*</span> </label>
            <input type="text" class="form-control" value="<?php 
            if (!empty($tender_info)) {
                echo $tender_info->ac;
            } 
            ?>" name="ac" required>
        </div>
        <div class="col-sm-3">
            <label class="col-lg-6 control-label"><?= lang('Length') ?> <span class="text-danger">*</span> </label>
            <input type="text" class="form-control" value="<?php 
            if (!empty($tender_info)) {
                echo $tender_info->length;
            } 
            ?>" name="length" required>
        </div>
        <div class="col-sm-3">
            <label class="col-lg-6 control-label"><?= lang('Height') ?> <span class="text-danger">*</span> </label>
            <input type="text" class="form-control" value="<?php 
            if (!empty($tender_info)) {
                echo $tender_info->height;
            } 
            ?>" name="height" required>
        </div>
    </div>
    
    <div class="form-group">
        <label for="field-1"
               class="col-sm-3 control-label"></label>
        <div class="col-sm-3">
            <label class="col-lg-6 control-label"><?= lang('Width') ?> <span class="text-danger">*</span> </label>
            <input type="text" class="form-control" value="<?php 
            if (!empty($tender_info)) {
                echo $tender_info->width;
            } 
            ?>" name="width" required>
        </div>
        <div class="col-sm-3">
            <label class="col-lg-6 control-label"><?= lang('Seating +D') ?> <span class="text-danger">*</span> </label>
            <input type="text" class="form-control" value="<?php 
            if (!empty($tender_info)) {
                echo $tender_info->seating;
            } 
            ?>" name="seating" required>
        </div>
        <div class="col-sm-3">
            <label class="col-lg-6 control-label"><?= lang('Floor Height') ?> <span class="text-danger">*</span> </label>
            <input type="text" class="form-control" value="<?php 
            if (!empty($tender_info)) {
                echo $tender_info->floor_height;
            } 
            ?>" name="floor_height" required>
        </div>
    </div>
    
    <div class="form-group">
        <label for="field-1"
               class="col-sm-3 control-label"></label>
        <div class="col-sm-3">
            <label class="col-lg-6 control-label"><?= lang('Range') ?> <span class="text-danger">*</span> </label>
            <input type="text" class="form-control" value="<?php 
            if (!empty($tender_info)) {
                echo $tender_info->bus_range;
            } 
            ?>" name="bus_range" required>
        </div>
        <div class="col-sm-3">
            <label class="col-lg-6 control-label"><?= lang('Warranty') ?> <span class="text-danger">*</span> </label>
            <input type="text" class="form-control" value="<?php 
            if (!empty($tender_info)) {
                echo $tender_info->warranty;
            } 
            ?>" name="warranty" required>
        </div>
        <div class="col-sm-3">
            <label class="col-lg-6 control-label"><?= lang('AMC') ?> <span class="text-danger">*</span> </label>
            <input type="text" class="form-control" value="<?php 
            if (!empty($tender_info)) {
                echo $tender_info->amc;
            } 
            ?>" name="amc" required>
        </div>
    </div><br>
    
    


    <div class="form-group">
        <label class="col-lg-3 control-label"><?= lang('Opening Date') ?> <span
                class="text-danger">*</span>
        </label>
        <div class="col-lg-5">
            <input type="date"  name="opening_date" min="<?php echo date('Y-m-d'); ?>" value="<?php
            if (!empty($tender_info->opening_date)) {
                echo $tender_info->opening_date;
            } else {
                echo date('Y-m-d');
            }
            ?>" required>
        </div>
    </div>
    
    <div class="form-group">
        <label class="col-lg-3 control-label"><?= lang('Closing Date') ?> <span
                class="text-danger">*</span>
        </label>
        <div class="col-lg-5">
            <input type="date"  name="closing_date" min="<?php echo date('Y-m-d'); ?>" value="<?php
            if (!empty($tender_info->closing_date)) {
                echo $tender_info->closing_date;
            } else {
                echo date('Y-m-d');
            }
            ?>" required>
        </div>
    </div>
  

    <div class="btn-bottom-toolbar text-right">
        <?php
        if (!empty($tender_info)) { ?>
            <button type="submit" id="file-save-button"
                    class="btn btn-sm btn-primary"><?= lang('updates') ?></button>
            <button type="button" onclick="goBack()"
                    class="btn btn-sm btn-danger"><?= lang('cancel') ?></button>
        <?php } else {
            ?>
            <button type="submit" id="file-save-button"
                    class="btn btn-sm btn-primary"><?= lang('Create Tender') ?></button>
        <?php }
        ?>
    </div>
</form>
