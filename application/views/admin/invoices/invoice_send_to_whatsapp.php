<!-- WhatsApp Send Modal -->
<div class="modal fade" id="whatsappSendModal" tabindex="-1" role="dialog">
      <div class="modal-dialog" role="document">
            <div class="modal-content">
                  <div class="modal-header">
                        <h4 class="modal-title">Send Invoice via WhatsApp</h4>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                  </div>

                  <div class="modal-body">
                        <input type="hidden" id="wa_invoice_id">

                        <div class="form-group">
                              <label>Customer Number</label>
                              <input type="text" id="wa_customer_number" class="form-control">
                        </div>

                        <div class="form-group">
                              <label>Invoice Preview</label>
                              <textarea id="wa_message_preview" class="form-control" rows="5" readonly></textarea>
                        </div>
                  </div>

                  <div class="modal-footer">
                        <button class="btn btn-success" id="wa_send_btn">
                              <i class="fa fa-paper-plane"></i> Send
                        </button>
                  </div>
            </div>
      </div>
</div>