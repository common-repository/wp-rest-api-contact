<div class="wrap">
	<h1>WP REST API Newsletter - Configure</h1>
	<h1>Guideline for WP REST API Newsletter</h1>
	<table class="wp-list-table widefat fixed striped posts">
		<thead>
		<tr>
			<th class="manage-column" style="width:15%;">Function</th>
			<th class="manage-column" style="width:15%;">Method</th>
			
			<th class="manage-column column-shortcode">Route</th>
			<th class="manage-column column-shortcode">Params</th>
		</tr>
		</thead>
		<tbody>
			
			<tr>
				<td>
					<span style="font-weight: bold;">Subscribe Newsletter</span>
				</td>
				<td>
					<span style="font-weight: bold;">POST</span>
				</td>
				<td>
					<input type="text" onfocus="this.select();" readonly="readonly" value="<?php echo get_site_url(); ?>/wp-json/wp/v2/newsletter-api" class="large-text code">
				</td>
				<td>
					
					<span style="font-weight: bold;">email</span> (string) | <span style="color: #7d2828;">Required</span><br>
					<span style="font-weight: bold;">token</span> (string) | <span style="color: #7d2828;">Required</span>
					
				</td>
			</tr>
		</tbody>
		<tfoot>
		<tr>
			<th class="manage-column" style="width:15%;">Function</th>
			<th class="manage-column" style="width:15%;">Method</th>
			<th class="manage-column column-shortcode">Route</th>
			<th class="manage-column column-shortcode">Params</th>
		</tr>
		</tfoot>
	</table>
	<br>
	<h1>Token for WP REST API Newsletter</h1>
	<div id="wprac-message-success" style="color: green; font-weight: bold;">
	<p></p>
	</div>
	<input id="wprac_input_generate_newsletter_token" type="text" onfocus="this.select();" readonly="readonly" value="<?php echo $token; ?>" class="code" style="width:50%">
	<?php wp_nonce_field( 'wprac_generate_newsletter_token_action', 'wprac_generate_newsletter_token_security'); ?>
	<button id="submit-generate-newsletter-token" class="button button-primary">Generate Token </button>

	<div class="contact-api-info">
		<div class="wp-box">
			<div class="inner">
				<h2>WP REST API Newsletter <?php echo WPRAC_VERSION; ?></h2>
				<h3 style="color:red;">Required</h3>
				<p>
				- Configuration SMTP ( Install the WP SMTP Plugin ).
				</p>
				<h3>Changelog</h3>
				<p>
				- Show list all subscribe newsletter (custom show columns, per page, delete multi items)<br> 
				- Rest API: Add new subscribe newsletter.<br>
				- Send email to list all subscribers or select to an email
				</p>
				<p>See what's new in <a target="_blank" href="https://wordpress.org/plugins/wp-rest-api-contact/#developers">version <?php echo WPRAC_VERSION; ?></a></p>

				
				<h3>Resources</h3>
				<ul>
					<li><a href="https://wordpress.org/plugins/wp-rest-api-contact/" target="_blank">Getting Started</a></li>
					<li><a href="https://wordpress.org/plugins/wp-rest-api-contact/#reviews" target="_blank">Reviews</a></li>
					<li><a href="https://wordpress.org/plugins/wp-rest-api-contact/#installation" target="_blank">'How to' guides</a></li>
					<li><a href="https://wordpress.org/plugins/wp-rest-api-contact/#installation" target="_blank">Tutorials</a></li>
				</ul>
			</div>
			<div class="footer footer-blue">
				<ul class="hl">
					<li>Created by NextPointAsia</li>
				</ul>
			</div>
		</div>
	</div>

	<?php require(WPRAC_PLUGIN_DIR . 'views/donate.php'); ?>

</div>