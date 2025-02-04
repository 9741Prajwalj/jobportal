<?php
echo message_box('success');
echo message_box('error');
$created = can_action('129', 'created');
$edited = can_action('129', 'edited');
?>

<!-- Include jQuery UI for draggable functionality -->
<link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>


<div class="panel panel-custom top-modal">
    <header class="panel-heading">
        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span
                class="sr-only">Close</span></button>
        <?= lang('opportunities_state_reason') ?></header>
    <?php
    if (!empty($created) || !empty($edited)) { ?>
        <form method="post" id="state_reason"
              action="<?= base_url() ?>admin/opportunities/update_state_reason" class="form-horizontal"
              data-parsley-validate="" novalidate="">
            <div class="form-group">
                <label
                    class="col-sm-4 control-label"><?= lang('opportunities_state') ?></label>
                <div class="col-sm-7">
                    <select name="opportunities_state" class="form-control">
                        <option value="open"><?= lang('open') ?></option>
                        <option value="won"><?= lang('won') ?></option>
                        <option value="abandoned"><?= lang('abandoned') ?></option>
                        <option value="suspended"><?= lang('suspended') ?></option>
                        <option value="lost"><?= lang('lost') ?></option>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label
                    class="col-sm-4 control-label"><?= lang('reason') ?></label>
                <div class="col-sm-7">
                    <input type="text" name="opportunities_state_reason" class="form-control"
                           placeholder="<?= lang('opportunities_state_reason') ?>">
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
<script type="text/javascript">

$(document).ready(function () {
        // Make the modal draggable
        $('.top-modal').draggable({
            handle: '.panel-heading' // Drag the modal by its header
        });
    });

    $(document).on("submit", "form", function (event) {
        var form = $(event.target);
        if (form.attr('action') == '<?= base_url('admin/opportunities/update_state_reason')?>') {
            event.preventDefault();
            $.ajax({
                type: form.attr('method'),
                url: form.attr('action'),
                data: form.serialize()
            }).done(function (response) {
                response = JSON.parse(response);
                if (response.status == 'success') {
                    if (typeof(response.id) != 'undefined') {
                        var groups = $('select[name="opportunities_state_reason_id"]');
                        groups.prepend('<option selected value="' + response.id + '">' + response.reason + '</option>');
                        var select2Instance = groups.data('select2');
                        var resetOptions = select2Instance.options.options;
                        groups.select2('destroy').select2(resetOptions)
                    }
                    toastr[response.status](response.message);
                }
                $('#myModal').modal('hide');
            }).fail(function () {
                alert('There was a problem with AJAX');
            });
        }
    });

</script>
