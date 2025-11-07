<?php echo form_open(admin_url('settings?group=whatsapp')); ?>

<div class="row">
  <div class="col-md-12">

    <h4 class="mbot15">WhatsApp Settings</h4>
    <hr>

    <div class="form-group">
      <label for="settings[greenapi_instance_id]" class="control-label">
        Green API Instance ID
      </label>
      <input type="text" id="settings[greenapi_instance_id]" name="settings[greenapi_instance_id]" class="form-control"
        value="<?php echo get_option('greenapi_instance_id'); ?>">
    </div>

    <div class="form-group">
      <label for="settings[greenapi_token]" class="control-label">
        Green API Token
      </label>
      <input type="text" id="settings[greenapi_token]" name="settings[greenapi_token]" class="form-control"
        value="<?php echo get_option('greenapi_token'); ?>">
    </div>

    <hr>

    <div class="form-group">
      <div class="checkbox checkbox-primary">
        <input type="checkbox" id="settings[auto_invoice_message]" name="settings[auto_invoice_message]" value="1"
          <?php echo get_option('auto_invoice_message') == '1' ? 'checked' : ''; ?>>
        <label for="settings[auto_invoice_message]">
          Enable Auto Invoice WhatsApp Notification
        </label>
      </div>
    </div>
    <!-- Reminder 1 -->
    <div class="form-group">
      <label for="settings[reminder1_invoice_day]" class="control-label">
        1st Reminder After Days
      </label>
      <input type="number" id="settings[reminder1_invoice_day]" name="settings[reminder1_invoice_day]" class="form-control"
        value="<?php echo get_option('reminder1_invoice_day'); ?>">
    </div>
    <div class="form-group">
      <label for="settings[reminder1_invoice_message]" class="control-label">
        1st Reminder Message
      </label>
      <textarea id="settings[reminder1_invoice_message]" name="settings[reminder1_invoice_message]" rows="3"
        class="form-control"><?php echo get_option('reminder1_invoice_message'); ?></textarea>
      <small class="text-muted">
        You can use placeholders like {customer_name}, {invoice_number}, {due_date}
      </small>
    </div>
    <!-- Reminder 2 -->
    <div class="form-group">
      <label for="settings[reminder2_invoice_day]" class="control-label">
        2nd Reminder After Days
      </label>
      <input type="number" id="settings[reminder2_invoice_day]" name="settings[reminder2_invoice_day]" class="form-control"
        value="<?php echo get_option('reminder2_invoice_day'); ?>">
    </div>
    <div class="form-group">
      <label for="settings[reminder2_invoice_message]" class="control-label">
        2nd Reminder Message
      </label>
      <textarea id="settings[reminder2_invoice_message]" name="settings[reminder2_invoice_message]" rows="3"
        class="form-control"><?php echo get_option('reminder2_invoice_message'); ?></textarea>
      <small class="text-muted">
        You can use placeholders like {customer_name}, {invoice_number}, {due_date}
      </small>
    </div>

    <!-- Reminder 3 -->
    <div class="form-group">
      <label for="settings[reminder3_invoice_day]" class="control-label">
        3rd Reminder After Days
      </label>
      <input type="number" id="settings[reminder3_invoice_day]" name="settings[reminder3_invoice_day]" class="form-control"
        value="<?php echo get_option('reminder3_invoice_day'); ?>">
    </div>
    <div class="form-group">
      <label for="settings[reminder3_invoice_message]" class="control-label">
        3rd Reminder Message
      </label>
      <textarea id="settings[reminder3_invoice_message]" name="settings[reminder3_invoice_message]" rows="3"
        class="form-control"><?php echo get_option('reminder3_invoice_message'); ?></textarea>
      <small class="text-muted">
        You can use placeholders like {customer_name}, {invoice_number}, {due_date}
      </small>
    </div>

    <!-- Reminder 4 -->
    <div class="form-group">
      <label for="settings[reminder4_invoice_day]" class="control-label">
        4rth Reminder After Days
      </label>
      <input type="number" id="settings[reminder4_invoice_day]" name="settings[reminder4_invoice_day]" class="form-control"
        value="<?php echo get_option('reminder4_invoice_day'); ?>">
    </div>
    <div class="form-group">
      <label for="settings[reminder4_invoice_message]" class="control-label">
        4rth Reminder Message
      </label>
      <textarea id="settings[reminder4_invoice_message]" name="settings[reminder4_invoice_message]" rows="3"
        class="form-control"><?php echo get_option('reminder4_invoice_message'); ?></textarea>
      <small class="text-muted">
        You can use placeholders like {customer_name}, {invoice_number}, {due_date}
      </small>
    </div>

  </div>
</div>

<?php echo form_close(); ?>