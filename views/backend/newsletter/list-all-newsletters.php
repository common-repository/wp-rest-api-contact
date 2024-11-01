<div class="wrap">
	<h1>WP REST API Newsletter - List All Newsletters</h1>
	<div id="poststuff">
		<div id="post-body" class="metabox-holder">
			<div id="post-body-content">
				<div class="meta-box-sortables ui-sortable">
					<form method="post">				
						<?php $newsletter_obj->prepare_items();
						$newsletter_obj->display(); ?>
					</form>
				</div>
			</div>
		</div>
		<br class="clear">
	</div>
</div>