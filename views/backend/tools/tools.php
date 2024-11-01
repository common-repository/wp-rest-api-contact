<div class="wrap">
	<h1>WP REST API NPA - Tools</h1>
	<form action="options.php" method="post">
		<?php
			settings_fields('wprac_tools_options_group');
			do_settings_sections('wprac-tools-npa');
			submit_button();
		?>
	</form>
	

	<div class="contact-api-info">
		<div class="wp-box">
			<div class="inner">
				<h2>WP REST API NPA <?php echo WPRAC_VERSION; ?></h2>
				<h3 style="color:red;">Required</h3>
				<p>
				- Configuration SMTP ( Install the WP SMTP Plugin ).
				</p>
				<h3>Changelog</h3>
				<p>
				- Show list all contact (custom show columns, per page, delete multi items)<br> 
				- Rest API: Add new contact.<br>
				- On / Off send new contact to admin email.
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
