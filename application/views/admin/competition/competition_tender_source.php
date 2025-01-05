<?php
echo message_box('success');
echo message_box('error');
$created = can_action('128', 'created');
$edited = can_action('128', 'edited');
?>
<div class="panel panel-custom">
    <header class="panel-heading ">
        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span
                class="sr-only">Close</span></button>
        <?= lang('Agency') ?></header>
    <?php
    if (!empty($created) || !empty($edited)) { ?>
        <form method="post" id="lead_sources" action="<?= base_url() ?>admin/whole_tender/update_whole_tender_source"
              class="form-horizontal" data-parsley-validate="" novalidate="">
            <div class="form-group">
                <label
                    class="col-sm-3 control-label"><?= lang('Agency') ?></label>
                <div class="col-sm-5">
                    <input type="text" name="agency_name" class="form-control"
                           placeholder="<?= lang('Agency') ?>" required>
                </div>
            </div>
            <div class="form-group mt">
                <label class="col-lg-3"></label>
                <div class="col-lg-3">
                    <button type="submit"
                            class="btn btn-sm btn-primary"><?= lang('save') ?></button>
                    <button type="button" class="btn btn-default" data-dismiss="modal"><?= lang('close') ?></button>
                </div>
            </div>
        </form>
    <?php } ?>
</div>
