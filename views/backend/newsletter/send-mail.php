<div class="wrap" id="wprac-rest-api-newsletter">
<h1>WP REST API Newsletter - Send Mail</h1>
<div id="wprac-message-success" class="notice notice-success">
	<p></p>
</div>
<div id="wprac-message-error" class="notice notice-warning">
	<p></p>
</div>
	<form method="" action="">
		<table class="form-table">
			<tbody>
				<tr>
					<th>
						Subject:		
					</th>
					<td>
						<label>
							<input id="wprac-input-subject" type="text" name="wprac_subject" value="" size="43" style="width:100%;height:35px;">
						</label>
					</td>
				</tr>
				<tr>
					<th>
						Message:		
					</th>
					<td>
						<label>
							<textarea id="wprac-input-message" type="text" name="wprac_message" value="" style="width:100%;height: 100px;"></textarea>
						</label>
					</td>
				</tr>
				<tr>
					<th>
						Send to List All Contacts:		
					</th>
					<td>
						<label>
							<input id="check_sm_all" value='yes' name='wprac_sm_all' type='radio' checked="checked" />
						</label>
					</td>
				</tr>
				<tr>
					<th>
						Select email:
					</th>
					<td>
						<label>
							<input id="check_sm_all_non" value='no' name='wprac_sm_all' type='radio' />
						</label><br>
						<?php 
							if($list_mails){
						?>
						<select class="select-multi-email" size="6" name="wprac_select_mail" multiple="yes" style="width: 40%; margin-top:15px;padding: 5px;">
						<?php 
								
							foreach ($list_mails as $value) {
						?>
								<option value="<?php echo $value["email"]; ?>"><?php echo $value["email"]; ?></option>
						<?php	
							}
						}
						?>
							
						</select>

					</td>
				</tr>
			</tbody>
			<tfoot>
				<tr>
					<th>
						<?php wp_nonce_field( 'wprac_send_mail_action', 'wprac_send_mail_security'); ?>
						<button id="submit-newsletter-sendmail" class="button button-primary">Send Mail </button>
						<span class="wprac-loading">
							<img width="20" height="20" src="<?php echo WPRAC_PLUGIN_URL . 'views/loading.GIF' ?>" alt="Loading Image" style="margin-top: 5px;">
						</span>
					</th>
					<td>
						<div style="width:100%; border: 3px solid #d2d2d2; display: inline-block; position: relative;">
							<div class="wprac-progress-wrap progress" data-progress-percent="0">
							  	<div class="progress-bar progress"></div>
							</div>
							<span style="position: absolute; top: 5px; left: 40%; color: #fff;">Process of sending e mail...</span>
						</div>
					</td>
				</tr>
			</tfoot>
		</table>
		
			
			
	</form>
</div>