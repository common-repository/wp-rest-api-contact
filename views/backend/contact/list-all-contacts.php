<div class="wrap">
<h1>WP REST API Contact - List All Contacts</h1>
	<div id="poststuff">
		<div id="post-body" class="metabox-holder">
			<div id="post-body-content">
				<div class="meta-box-sortables ui-sortable">
					<form method="post">				
						<?php $contacts_obj->prepare_items();
						$contacts_obj->display(); ?>
					</form>
				</div>
			</div>
		</div>
		<br class="clear">
	</div>
</div>