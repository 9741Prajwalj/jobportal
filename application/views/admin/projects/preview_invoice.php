<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.16.0/jquery.validate.js"></script>
<?php include_once 'assets/admin-ajax.php'; ?>
<?php include_once 'assets/js/sales.php'; ?>
<style>
    .note-editor .note-editable {
        height: 150px;
    }
</style>

<form
        action="<?php echo base_url() ?>admin/projects/save_invoice/<?php if (!empty($project_info->project_id)) echo $project_info->project_id; ?>"
        method="post" class="form-horizontal form-groups-bordered">
    <div class="panel panel-custom">
        <div class="panel-heading">
            <a class="close" href="<?= base_url() ?>admin/projects/project_details/<?= $project_info->project_id ?>">
                <span aria-hidden="true">&times;</span><span
                        class="sr-only"><?php echo lang('close') ?></span>
            </a>

            <h4 class="modal-title"
                id="myModalLabel"><?= $project_info->project_name . ' - ' . lang('preview_invoice') ?></h4>
        </div>

        <?php
        $client_info = $this->invoice_model->check_by(array('client_id' => $project_info->client_id), 'tbl_client');
        $currency = $this->invoice_model->client_currency_symbol($project_info->client_id);
        $client_lang = $client_info->language;
        unset($this->lang->is_loaded[5]);
        $language_info = $this->lang->load('sales_lang', $client_lang, TRUE, FALSE, '', TRUE);
        ?>

        <div class="panel-body">
            <form role="form" data-parsley-validate="" novalidate="" enctype="multipart/form-data" id="form"
                  action="<?php echo base_url(); ?>admin/projects/save_invoice/<?php
                  if (!empty($invoice_info)) {
                      echo $invoice_info->invoices_id;
                  }
                  ?>" method="post" class="form-horizontal  ">

                <div class="row mb-lg">
                    <div class="col-xs-6 br pv">
                        <div class="row">

                            <div class="form-group">
                                <label class="col-lg-3 control-label"><?= lang('reference_no') ?> <span
                                            class="text-danger">*</span></label>
                                <div class="col-lg-7">
                                    <input type="text" class="form-control" value="<?php
                                    if (!empty($invoice_info)) {
                                        echo $invoice_info->reference_no;
                                    } else {
                                        if (empty(config_item('invoice_number_format'))) {
                                            echo config_item('invoice_prefix');
                                        }
                                        if (config_item('increment_invoice_number') == 'FALSE') {
                                            $this->load->helper('string');
                                            echo random_string('nozero', 6);
                                        } else {
                                            echo $this->invoice_model->generate_invoice_number();
                                        }
                                    }
                                    ?>" name="reference_no">
                                </div>
                                <div class="btn btn-xs btn-info" id="start_recurring"><?= lang('recurring') ?></div>

                            </div>
                            <div id="recurring" class="hide">
                                <div class="form-group">
                                    <label class="col-lg-3 control-label"><?= lang('recur_frequency') ?> </label>
                                    <div class="col-lg-4">
                                        <select name="recuring_frequency" id="recuring_frequency"
                                                class="form-control">
                                            <option value="none"><?= lang('none') ?></option>
                                            <option
                                                    value="7D"><?= lang('week') ?></option>
                                            <option
                                                    value="1M"><?= lang('month') ?></option>
                                            <option
                                                    value="3M"><?= lang('quarter') ?></option>
                                            <option
                                                    value="6M"><?= lang('six_months') ?></option>
                                            <option
                                                    value="1Y"><?= lang('1year') ?></option>
                                            <option
                                                    value="2Y"><?= lang('2year') ?></option>
                                            <option
                                                    value="3Y"><?= lang('3year') ?></option>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-lg-3 control-label"><?= lang('start_date') ?></label>
                                    <div class="col-lg-7">
                                        <?php
                                        if (!empty($invoice_info) && $invoice_info->recurring == 'Yes') {
                                            $recur_start_date = date('Y-m-d', strtotime($invoice_info->recur_start_date));
                                            $recur_end_date = date('Y-m-d', strtotime($invoice_info->recur_end_date));
                                        } else {
                                            $recur_start_date = date('Y-m-d');
                                            $recur_end_date = date('Y-m-d');
                                        }
                                        ?>
                                        <div class="input-group">
                                            <input class="form-control datepicker" type="text"
                                                   value="<?= $recur_start_date; ?>"
                                                   name="recur_start_date"
                                                   data-date-format="<?= config_item('date_picker_format'); ?>">
                                            <div class="input-group-addon">
                                                <a href="#"><i class="fa fa-calendar"></i></a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-lg-3 control-label"><?= lang('end_date') ?></label>
                                    <div class="col-lg-7">
                                        <div class="input-group">
                                            <input class="form-control datepicker" type="text"
                                                   value="<?= $recur_end_date; ?>"
                                                   name="recur_end_date"
                                                   data-date-format="<?= config_item('date_picker_format'); ?>">
                                            <div class="input-group-addon">
                                                <a href="#"><i class="fa fa-calendar"></i></a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-lg-3 control-label"><?= lang('client') ?> <span
                                            class="text-danger">*</span>
                                </label>
                                <div class="col-lg-7">
                                    <select class="form-control select_box" required style="width: 100%"
                                            name="client_id"
                                            onchange="get_project_by_id(this.value)">
                                        <option value="-"><?= lang('select') . ' ' . lang('client') ?></option>
                                        <?php
                                        if (!empty($all_client)) {
                                            foreach ($all_client as $v_client) {
                                                if (!empty($project_info->client_id)) {
                                                    $client_id = $project_info->client_id;
                                                } elseif ($invoice_info->client_id) {
                                                    $client_id = $invoice_info->client_id;
                                                }
                                                ?>
                                                <option value="<?= $v_client->client_id ?>"
                                                    <?php
                                                    if (!empty($client_id)) {
                                                        echo $client_id == $v_client->client_id ? 'selected' : null;
                                                    }
                                                    ?>
                                                ><?= ucfirst($v_client->name) ?></option>
                                                <?php
                                            }
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-lg-3 control-label"><?= lang('project') ?></label>
                                <div class="col-lg-7">
                                    <select class="form-control " style="width: 100%" name="project_id"
                                            id="client_project">
                                        <option value=""><?= lang('none') ?></option>
                                        <?php

                                        if (!empty($client_id)) {

                                            if (!empty($project_info->project_id)) {
                                                $project_id = $project_info->project_id;
                                            } elseif ($invoice_info->project_id) {
                                                $project_id = $invoice_info->project_id;
                                            }
                                            $all_project = $this->db->where('client_id', $client_id)->get('tbl_project')->result();
                                            if (!empty($all_project)) {
                                                foreach ($all_project as $v_project) {
                                                    ?>
                                                    <option value="<?= $v_project->project_id ?>" <?php
                                                    if (!empty($project_id)) {
                                                        echo $v_project->project_id == $project_id ? 'selected' : '';
                                                    }
                                                    ?>><?= $v_project->project_name ?></option>
                                                    <?php
                                                }
                                            }
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group">
                                <label
                                        class="col-lg-3 control-label"><?= lang('invoice_date') ?></label>
                                <div class="col-lg-7">
                                    <div class="input-group">
                                        <input type="text" name="invoice_date"
                                               class="form-control datepicker"
                                               value="<?php
                                               if (!empty($invoice_info->invoice_date)) {
                                                   echo $invoice_info->invoice_date;
                                               } else {
                                                   echo date('Y-m-d');
                                               }
                                               ?>"
                                               data-date-format="<?= config_item('date_picker_format'); ?>">
                                        <div class="input-group-addon">
                                            <a href="#"><i class="fa fa-calendar"></i></a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-lg-3 control-label"><?= lang('due_date') ?></label>
                                <div class="col-lg-7">
                                    <div class="input-group">
                                        <input type="text" name="due_date" class="form-control datepicker" value="<?php
                                        if (!empty($invoice_info->due_date)) {
                                            echo $invoice_info->due_date;
                                        } else {
                                            echo date('Y-m-d');
                                        }
                                        ?>" data-date-format="<?= config_item('date_picker_format'); ?>">
                                        <div class="input-group-addon">
                                            <a href="#"><i class="fa fa-calendar"></i></a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="discount_type"
                                       class="control-label col-sm-3"><?= lang('discount_type') ?></label>
                                <div class="col-sm-7">
                                    <select name="discount_type" class="selectpicker" data-width="100%">
                                        <option value=""
                                                selected><?php echo lang('no') . ' ' . lang('discount'); ?></option>
                                        <option value="before_tax" <?php
                                        if (isset($invoice_info)) {
                                            if ($invoice_info->discount_type == 'before_tax') {
                                                echo 'selected';
                                            }
                                        } ?>><?php echo lang('before_tax'); ?></option>
                                        <option value="after_tax" <?php if (isset($invoice_info)) {
                                            if ($invoice_info->discount_type == 'after_tax') {
                                                echo 'selected';
                                            }
                                        } ?>><?php echo lang('after_tax'); ?></option>
                                    </select>
                                </div>
                            </div>


                            <?php
                            $permissionL = null;
                            if (!empty($invoice_info->permission)) {
                                $permissionL = $invoice_info->permission;
                            }
                            ?>
                            <?= get_permission(3, 9, $permission_user, $permissionL, ''); ?>

                            <?php
                            if (!empty($invoice_info)) {
                                $invoices_id = $invoice_info->invoices_id;
                            } else {
                                $invoices_id = null;
                            }
                            ?>
                            <?= custom_form_Fields(9, $invoices_id); ?>

                        </div>
                    </div>
                    <div class="col-xs-6 br pv">
                        <div class="row">
                            <div class="form-group">
                                <label for="field-1"
                                       class="col-sm-4 control-label"><?= lang('sales') . ' ' . lang('agent') ?></label>
                                <div class="col-sm-7">
                                    <select class="form-control select_box" required style="width: 100%"
                                            name="user_id">
                                        <option
                                                value=""><?= lang('select') . ' ' . lang('sales') . ' ' . lang('agent') ?></option>
                                        <?php
                                        $all_user = $this->db->where('role_id != ', 2)->get('tbl_users')->result();
                                        if (!empty($all_user)) {
                                            foreach ($all_user as $v_user) {
                                                $profile_info = $this->db->where('user_id', $v_user->user_id)->get('tbl_account_details')->row();
                                                if (!empty($profile_info)) {
                                                    ?>
                                                    <option value="<?= $v_user->user_id ?>"
                                                        <?php
                                                        if (!empty($invoice_info->user_id)) {
                                                            echo $invoice_info->user_id == $v_user->user_id ? 'selected' : null;
                                                        } else {
                                                            echo $this->session->userdata('user_id') == $v_user->user_id ? 'selected' : null;
                                                        }
                                                        ?>
                                                    ><?= $profile_info->fullname ?></option>
                                                    <?php
                                                }
                                            }
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                            <?php
                                        $all_payment = get_result('tbl_online_payment');
                                        foreach ($all_payment as $key => $payment) {
                                            $allow_gateway = 'allow_' . slug_it(strtolower($payment->gateway_name));
                                            $gateway_status = slug_it(strtolower($payment->gateway_name)).'_status' ;
                                            if (config_item($gateway_status) == 'active') {?>
                                            <div class="form-group">
                                                <label for="field-1"
                                                       class="col-sm-4 control-label"><?= lang($allow_gateway) ?></label>
                                                <div class="col-sm-7">
                                                    <div class="checkbox c-checkbox">
                                                        <label class="needsclick">
                                                            <input type="checkbox" value="Yes"
                                                                <?php if (!empty($invoice_info) && $invoice_info->$allow_gateway == 'Yes') {
                                                                    echo 'checked';
                                                                } ?> name="<?= $allow_gateway?>">
                                                            <span class="fa fa-check"></span>
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php }
                                    }?>
                            
                            <?php if (!empty($project_id)): ?>
                                <div class="form-group">
                                    <label for="field-1"
                                           class="col-sm-4 control-label"><?= lang('visible_to_client') ?>
                                        <span class="required">*</span></label>
                                    <div class="col-sm-8">
                                        <input data-toggle="toggle" name="client_visible" value="Yes" <?php
                                        if (!empty($invoice_info->client_visible) && $invoice_info->client_visible == 'Yes') {
                                            echo 'checked';
                                        }
                                        ?> data-on="<?= lang('yes') ?>" data-off="<?= lang('no') ?>"
                                               data-onstyle="success" data-offstyle="danger" type="checkbox">
                                    </div>
                                </div>
                            <?php endif ?>
                        </div>
                    </div>
                    <div class="col-sm-12 ">

                        <div class="">
                            <label class="col-lg-1 control-label"><?= lang('notes') ?> </label>
                            <div class="col-lg-11 row">
                        <textarea name="notes" class="textarea"><?php
                            if (!empty($invoice_info)) {
                                echo $invoice_info->notes;
                            } else {
                                echo $this->config->item('default_terms');
                            }
                            ?></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <style type="text/css">
                    .dropdown-menu > li > a {
                        white-space: normal;
                    }

                    .dragger {
                        background: url(../../../assets/img/dragger.png) 10px 32px no-repeat;
                        cursor: pointer;
                    }

                    .input-transparent {
                        box-shadow: none;
                        outline: 0;
                        border: 0 !important;
                        background: 0 0;
                        padding: 3px;
                    }

                </style>
                <?php
                $saved_items = $this->invoice_model->get_all_items();
                ?>
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <select name="item_select" class="selectpicker m0" data-width="100%"
                                    id="item_select"
                                    data-none-selected-text="<?php echo lang('add_items'); ?>"
                                    data-live-search="true">
                                <option value=""></option>
                                <?php
                                if (!empty($saved_items)) {
                                    $saved_items = array_reverse($saved_items, true);
                                    foreach ($saved_items as $group_id => $v_saved_items) {
                                        if ($group_id != 0) {
                                            $group = $this->db->where('customer_group_id', $group_id)->get('tbl_customer_group')->row()->customer_group;
                                        } else {
                                            $group = '';
                                        }
                                        ?>
                                        <optgroup data-group-id="<?php echo $group_id; ?>"
                                                  label="<?php echo $group; ?>">
                                            <?php
                                            if (!empty($v_saved_items)) {
                                                foreach ($v_saved_items as $v_item) { ?>
                                                    <option value="<?php echo $v_item->saved_items_id; ?>"
                                                            data-subtext="<?php echo strip_html_tags(mb_substr($v_item->item_desc, 0, 200)) . '...'; ?>">
                                                        (<?= display_money($v_item->unit_cost, $currency->symbol); ?>
                                                        ) <?php echo $v_item->item_name; ?></option>
                                                <?php }
                                            }
                                            ?>
                                        </optgroup>

                                    <?php } ?>
                                    <?php
                                    $item_created = can_action('39', 'created');
                                    if (!empty($item_created)) { ?>
                                        <option data-divider="true"></option>
                                        <option value="newitem"
                                                data-content="<span class='text-info'><?php echo lang('new_item'); ?></span>"></option>
                                    <?php } ?>
                                    <?php
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-5 pull-right">
                        <div class="form-group">
                            <label
                                    class="col-sm-4 control-label"><?php echo lang('show_quantity_as'); ?></label>
                            <div class="col-sm-8">
                                <label class="radio-inline c-radio">
                                    <input type="radio" value="qty" id="<?php echo lang('qty'); ?>"
                                           name="show_quantity_as"
                                        <?php if (isset($invoice_info) && $invoice_info->show_quantity_as == 'qty') {
                                            echo 'checked';
                                        } else if (!isset($hours_quantity) && !isset($qty_hrs_quantity)) {
                                            echo 'checked';
                                        } ?>>
                                    <span class="fa fa-circle"></span><?php echo lang('qty'); ?>
                                </label>
                                <label class="radio-inline c-radio">
                                    <input type="radio" value="hours" id="<?php echo lang('hours'); ?>"
                                           name="show_quantity_as" <?php if (isset($invoice_info) && $invoice_info->show_quantity_as == 'hours' || isset($hours_quantity)) {
                                        echo 'checked';
                                    } ?>>
                                    <span class="fa fa-circle"></span><?php echo lang('hours'); ?></label>
                                <label class="radio-inline c-radio">
                                    <input type="radio" value="qty_hours"
                                           id="<?php echo lang('qty') . '/' . lang('hours'); ?>"
                                           name="show_quantity_as"
                                        <?php if (isset($invoice_info) && $invoice_info->show_quantity_as == 'qty_hours' || isset($qty_hrs_quantity)) {
                                            echo 'checked';
                                        } ?>>
                                    <span
                                            class="fa fa-circle"></span><?php echo lang('qty') . '/' . lang('hours'); ?>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive s_table">
                        <table class="table invoice-items-table items">
                            <thead style="background: #e8e8e8">
                            <tr>
                                <th></th>
                                <th><?= $language_info['item_name'] ?></th>
                                <th><?= $language_info['description'] ?></th>
                                <?php
                                $qty_heading = $language_info['qty'];
                                if (isset($invoice_info) && $invoice_info->show_quantity_as == 'hours' || isset($hours_quantity)) {
                                    $qty_heading = lang('hours');
                                } else if (isset($invoice_info) && $invoice_info->show_quantity_as == 'qty_hours') {
                                    $qty_heading = lang('qty') . '/' . lang('hours');
                                }
                                ?>
                                <th class="qty col-sm-1"><?php echo $qty_heading; ?></th>
                                <th class="col-sm-2"><?= $language_info['price'] ?></th>
                                <th class="col-sm-2"><?= $language_info['tax_rate'] ?> </th>
                                <th class="col-sm-1"><?= $language_info['total'] ?></th>
                                <th class="col-sm-1 hidden-print"><?= $language_info['action'] ?></th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php if (isset($invoice_info)) {
                                echo form_hidden('merge_current_invoice', $invoice_info->invoices_id);
                                echo form_hidden('isedit', $invoice_info->invoices_id);
                            }
                            ?>
                            <tr class="main">
                                <td></td>
                                <td>
                        <textarea name="item_name" class="form-control"
                                  placeholder="<?php echo lang('item_name'); ?>"></textarea>
                                </td>
                                <td>
                        <textarea name="item_desc" class="form-control"
                                  placeholder="<?php echo lang('description'); ?>"></textarea>
                                </td>
                                <td>
                                    <input type="number" name="quantity" step="0.01" min="0" value="1"
                                           class="form-control"
                                           placeholder="<?php echo lang('qty'); ?>">
                                    <input type="text"
                                           placeholder="<?php echo lang('unit') . ' ' . lang('type'); ?>"
                                           name="unit"
                                           class="form-control input-transparent">
                                </td>
                                <td>
                                    <input type="hidden" name="new_itmes_id" class="form-control">
                                    <input type="hidden" name="saved_items_id" class="form-control">
                                    <input type="number" step="0.01" name="unit_cost" class="form-control"
                                           placeholder="<?php echo lang('price'); ?>">
                                </td>
                                <td>
                                    <?php
                                    $taxes = $this->db->order_by('tax_rate_percent', 'ASC')->get('tbl_tax_rates')->result();
                                    $default_tax = config_item('default_tax');
                                    if (!is_numeric($default_tax)) {
                                        $default_tax = unserialize($default_tax);
                                    }
                                    $select = '<select class="selectpicker tax main-tax" data-width="100%" name="taxname" multiple data-none-selected-text="' . lang('no_tax') . '">';
                                    foreach ($taxes as $tax) {
                                        $selected = '';
                                        if (!empty($default_tax) && is_array($default_tax)) {
                                            if (in_array($tax->tax_rates_id, $default_tax)) {
                                                $selected = ' selected ';
                                            }
                                        }
                                        $select .= '<option value="' . $tax->tax_rate_name . '|' . $tax->tax_rate_percent . '"' . $selected . 'data-taxrate="' . $tax->tax_rate_percent . '" data-taxname="' . $tax->tax_rate_name . '" data-subtext="' . $tax->tax_rate_name . '">' . $tax->tax_rate_percent . '%</option>';
                                    }
                                    $select .= '</select>';
                                    echo $select;
                                    ?>
                                </td>
                                <td></td>
                                <td>
                                    <?php
                                    $new_item = 'undefined';
                                    if (isset($invoice_info)) {
                                        $new_item = true;
                                    }
                                    ?>
                                    <button type="button"
                                            onclick="add_item_to_table('undefined','undefined',<?php echo $new_item; ?>); return false;"
                                            class="btn-xs btn btn-info"><i class="fa fa-check"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php
                            if (!empty($tasks)) {
                                $tasks = $tasks;
                            } else {
                                $tasks = null;
                            }

                            if (!empty($expense)) {
                                $expense = $expense;
                            } else {
                                $expense = null;
                            }
                            $add_items = $this->items_model->make_all_items($project_info->project_id, $items_name, $tasks, $expense);

                            if (isset($invoice_info) || isset($add_items)) {
                                $i = 1;
                                $items_indicator = 'items';
                                if (isset($invoice_info)) {
                                    $add_items = $this->invoice_model->ordered_items_by_id(0);
                                    $items_indicator = 'items';
                                }
                                foreach ($add_items as $item) {
                                    $manual = false;
                                    $table_row = '<tr class="sortable item">';
                                    $table_row .= '<td class="dragger">';
                                    if (!is_numeric($item->quantity)) {
                                        $item->quantity = 1;
                                    }
                                    $taxes = $this->db->order_by('tax_rate_percent', 'ASC')->get('tbl_tax_rates')->result();
                                    $default_tax = config_item('default_tax');
                                    if (!is_numeric($default_tax)) {
                                        $default_tax = unserialize($default_tax);
                                    }
                                    $select = '<select class="selectpicker tax main-tax" data-width="100%" name="taxname" multiple data-none-selected-text="' . lang('no_tax') . '">';
                                    foreach ($taxes as $tax) {
                                        $selected = '';
                                        if (!empty($default_tax) && is_array($default_tax)) {
                                            if (in_array($tax->tax_rates_id, $default_tax)) {
                                                $selected = ' selected ';
                                            }
                                        }
                                        $select .= '<option value="' . $tax->tax_rate_name . '|' . $tax->tax_rate_percent . '"' . $selected . 'data-taxrate="' . $tax->tax_rate_percent . '" data-taxname="' . $tax->tax_rate_name . '" data-subtext="' . $tax->tax_rate_name . '">' . $tax->tax_rate_percent . '%</option>';
                                    }
                                    $select .= '</select>';

                                    $table_row .= form_hidden('' . $items_indicator . '[' . $i . '][items_id]', $item->items_id);
                                    $table_row .= form_hidden('' . $items_indicator . '[' . $i . '][saved_items_id]', $item->saved_items_id);
                                    $amount = $item->unit_cost * $item->quantity;
                                    $amount = ($amount);
                                    // order input
                                    $table_row .= '<input type="hidden" class="order" name="' . $items_indicator . '[' . $i . '][order]"><input type="hidden" name="items_id[]" value="' . $item->items_id . '">';
                                    if (!empty($item->task_id)) {
                                        $table_row .= '<input type="hidden" name="task_id[]" value="' . $item->task_id . '">';
                                    }

                                    $table_row .= '</td>';
                                    $table_row .= '<td class="bold item_name"><textarea name="' . $items_indicator . '[' . $i . '][item_name]" class="form-control">' . $item->item_name . '</textarea></td>';
                                    $table_row .= '<td><textarea name="' . $items_indicator . '[' . $i . '][item_desc]" class="form-control" >' . $item->item_desc . '</textarea></td>';
                                    $table_row .= '<td><input type="number" step="0.01" min="0" onblur="calculate_total();" onchange="calculate_total();" data-quantity name="' . $items_indicator . '[' . $i . '][quantity]" value="' . $item->quantity . '" class="form-control">';
                                    $unit_placeholder = '';
                                    if (!$item->unit) {
                                        $unit_placeholder = lang('unit');
                                        $item->unit = '';
                                    }
                                    $table_row .= '<input type="text" placeholder="' . $unit_placeholder . '" name="' . $items_indicator . '[' . $i . '][unit]" class="form-control input-transparent text-right" value="' . $item->unit . '">';
                                    $table_row .= '</td>';
                                    $table_row .= '<td class="rate"><input type="text" data-toggle="tooltip"  onblur="calculate_total();" onchange="calculate_total();" name="' . $items_indicator . '[' . $i . '][unit_cost]" value="' . $item->unit_cost . '" class="form-control"></td>';
                                    $table_row .= '<td class="taxrate">' . $select . '</td>';
                                    $table_row .= '<td class="amount">' . $amount . '</td>';
                                    $table_row .= '<td><a href="#" class="btn-xs btn btn-danger pull-left" onclick="delete_item(this,' . $item->items_id . '); return false;"><i class="fa fa-trash"></i></a></td>';
                                    $table_row .= '</tr>';
                                    echo $table_row;
                                    $i++;
                                }
                            }
                            ?>

                            </tbody>
                        </table>
                    </div>

                    <div class="row">
                        <div class="col-xs-8 pull-right">
                            <table class="table text-right">
                                <tbody>
                                <tr id="subtotal">
                                    <td><span class="bold"><?php echo lang('sub_total'); ?> :</span>
                                    </td>
                                    <td class="subtotal">
                                    </td>
                                </tr>
                                <tr id="discount_percent">
                                    <td>
                                        <div class="row">
                                            <div class="col-md-7">
                                                            <span class="bold"><?php echo lang('discount'); ?>
                                                                (%)</span>
                                            </div>
                                            <div class="col-md-5">
                                                <?php
                                                $discount_percent = 0;
                                                if (isset($invoice_info)) {
                                                    if ($invoice_info->discount_percent != 0) {
                                                        $discount_percent = $invoice_info->discount_percent;
                                                    }
                                                }
                                                ?>
                                                <input type="number" step="0.01"
                                                       value="<?php echo $discount_percent; ?>"
                                                       class="form-control pull-left" min="0" max="100"
                                                       name="discount_percent">
                                            </div>
                                        </div>
                                    </td>
                                    <td class="discount_percent"></td>
                                </tr>
                                <tr>
                                    <td>
                                        <div class="row">
                                            <div class="col-md-7">
                                                <span class="bold"><?php echo lang('adjustment'); ?></span>
                                            </div>
                                            <div class="col-md-5">
                                                <input type="number" step="0.01"
                                                       value="<?php if (isset($invoice_info)) {
                                                           echo $invoice_info->adjustment;
                                                       } else {
                                                           echo 0;
                                                       } ?>" class="form-control pull-left"
                                                       name="adjustment">
                                            </div>
                                        </div>
                                    </td>
                                    <td class="adjustment"></td>
                                </tr>
                                <tr>
                                    <td><span class="bold"><?php echo lang('total'); ?> :</span>
                                    </td>
                                    <td class="total">
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div id="removed-items"></div>
                    <div class="modal-footer">
                        <a href="<?= base_url() ?>admin/projects/project_details/<?= $project_info->project_id ?>"
                           class="btn btn-default"><?= lang('close') ?></a>
                        <input type="submit" value="<?= lang('save_as_draft') ?>" name="save_as_draft"
                               class="btn btn-primary">
                        <input type="submit" value="<?= lang('update') ?>" name="update"
                               class="btn btn-success">
                    </div>
                </div>
        </div>
    </div>
</form>
<script type="text/javascript">
    function slideToggle($id) {
        $('#quick_state').attr('data-original-title', '<?= lang('view_quick_state') ?>');
        $($id).slideToggle("slow");
    }

    $(document).ready(function () {
        $("#select_all_tasks").click(function () {
            $(".tasks_list").prop('checked', $(this).prop('checked'));
        });
        $("#select_all_expense").click(function () {
            $(".expense_list").prop('checked', $(this).prop('checked'));
        });
        $('[data-toggle="popover"]').popover();

    });
</script>

<script type="text/javascript">
    $(document).ready(function () {
        init_items_sortable();
        $('#start_recurring').click(function () {
            $('#recurring').slideToggle("fast");
            $('#recurring').removeClass("hide");
            $('#recuring_frequency').prop('disabled', false);
        });
    });
</script>

