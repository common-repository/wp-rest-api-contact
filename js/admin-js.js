jQuery(document).ready(function() {
	/*********************************************
	 * AJAX SEND MAIL - Subcribe Newsletter Module
	 *********************************************/
	jQuery("#check_sm_all_non").click(function(event){
		jQuery('.select-multi-email').show();
	});
	jQuery("#check_sm_all").click(function(event){
		jQuery('.select-multi-email').hide();
	});

	jQuery('#submit-newsletter-sendmail').click(function(event) {
		event.preventDefault(); 			//tránh load lại trang
		// console.log(wprac_ajax_config);
		jQuery('.wprac-loading').show();	// hiển thị icon loading
		jQuery('#submit-newsletter-sendmail').prop('disabled', true); // khóa nút submit
		jQuery('#wprac-input-subject').prop('disabled', true);
		jQuery('#wprac-input-message').prop('disabled', true);
		jQuery('#check_sm_all').prop('disabled', true);
		jQuery('#check_sm_all_non').prop('disabled', true);

		var subject = jQuery('#wprac-input-subject').val();
		var message = jQuery('#wprac-input-message').val();
		var check_sm_all = jQuery('[name="wprac_sm_all"]:checked').val();
		var select_mail = jQuery('[name="wprac_select_mail"]').val();
		var nonce = jQuery('[name="wprac_send_mail_security"]').val();

		//process bar
		jQuery('.wprac-progress-wrap').data('progress-percent', 25);
		moveProgressBar();

		//AJAX
		jQuery.ajax({
			'url' : wprac_ajax_config.url,
			'type' : 'POST',
			'data' : {
				'action' : 'do_send_mail_newsletter', 
				'wpracSubject' 				: subject,
				'wpracMessage' 				: message,
				'wpracCheck_sm_all' 		: check_sm_all,
				'wpracSelect_mail'			: select_mail,
				'wprac_send_mail_security' 	: nonce
			} 
		}).done(function(result) {
			console.log(result);
			setTimeout(function(){ 
				jQuery('.wprac-loading').hide();	// ẩn icon loading
				jQuery('#submit-newsletter-sendmail').prop('disabled', false); // mở khóa nút submit
				jQuery('#wprac-input-subject').prop('disabled', false);//mở khóa input subject
				jQuery('#wprac-input-message').prop('disabled', false);//mở khóa input message
				jQuery('#check_sm_all').prop('disabled', false);//mở khóa input radio
				jQuery('#check_sm_all_non').prop('disabled', false);//mở khóa input radio
			}, 2000);
			

			if(result.data.status){
				// process bar
				jQuery('.wprac-progress-wrap').data('progress-percent', 50);
				moveProgressBar();
				setTimeout(function(){ 
					jQuery('.wprac-progress-wrap').data('progress-percent', 75);
					moveProgressBar();
				}, 500);
				setTimeout(function(){ 
					jQuery('.wprac-progress-wrap').data('progress-percent', 100);
					moveProgressBar();
					jQuery('#wprac-message-success p').html('');
					jQuery('#wprac-message-error').hide();// Ẩn div notice error
					jQuery('#wprac-message-success').fadeIn("slow");
					jQuery('#wprac-message-success p').append(result.data.message);
				}, 2000);
				///////////////

				

			}else{
				setTimeout(function(){ 
					jQuery('#wprac-message-error p').html('');
					// Ẩn div notice success
					jQuery('#wprac-message-success').hide();
					jQuery('#wprac-message-error').fadeIn("slow");
					jQuery('#wprac-message-error p').append(result.data.message);
				}, 2000);
				
			}
		});
	});


	// SIGNATURE PROGRESS
    function moveProgressBar() {
      	// console.log("moveProgressBar");
        var getPercent = (jQuery('.wprac-progress-wrap').data('progress-percent') / 100);
        var getProgressWrapWidth = jQuery('.wprac-progress-wrap').width();
        var progressTotal = getPercent * getProgressWrapWidth;
        var animationLength = 1000;
        
        // on page load, animate percentage bar to data percentage length
        // .stop() used to prevent animation queueing
        jQuery('.progress-bar').stop().animate({
            left: progressTotal
        }, animationLength);
    }

    /*********************************************
     * Generate Newsletter Token - Subcribe Newsletter Module
     *********************************************/
    jQuery("#submit-generate-newsletter-token").click(function(event){
    	event.preventDefault(); 			//tránh load lại trang
		if(confirm('WOULD YOU LIKE TO GENEGATE A NEW TOKEN???\nYOU WILL HAVE TO CHANGE THE API ROUTE!!!\nARE YOU SURE TO DO THIS???')){
			
			var nonce = jQuery('[name="wprac_generate_newsletter_token_security"]').val();
			// console.log(wprac_ajax_config);
			//AJAX
			jQuery.ajax({
				'url' : wprac_ajax_config.url,
				'type' : 'POST',
				'data' : {
					'action' : 'do_generate_newsletter_token', 
					'wprac_generate_newsletter_token_security' 	: nonce
				} 
			}).done(function(result) {
				// console.log(result);
				if(result.data.status){
					jQuery('#wprac_input_generate_newsletter_token').val(result.data.token);
					jQuery('#wprac-message-success p').html('');
					jQuery('#wprac-message-success p').append(result.data.message);
					jQuery('#wprac-message-success').fadeIn("slow");
				}else{	
					
				}
			});
		}else{
			console.log('cancel');
		}
	});

	/*********************************************
     * Generate Contact Token - Subcribe Contact Module
     *********************************************/
    jQuery("#submit-generate-contact-token").click(function(event){
    	event.preventDefault(); 			//tránh load lại trang
		if(confirm('WOULD YOU LIKE TO GENEGATE A NEW TOKEN???\nYOU WILL HAVE TO CHANGE THE API ROUTE!!!\nARE YOU SURE TO DO THIS???')){
			
			var nonce = jQuery('[name="wprac_generate_contact_token_security"]').val();
			// console.log(wprac_ajax_config);
			//AJAX
			jQuery.ajax({
				'url' : wprac_ajax_config.url,
				'type' : 'POST',
				'data' : {
					'action' : 'do_generate_contact_token', 
					'wprac_generate_contact_token_security' 	: nonce
				} 
			}).done(function(result) {
				// console.log(result);
				if(result.data.status){
					jQuery('#wprac_input_generate_contact_token').val(result.data.token);
					jQuery('#wprac-message-success-generate-token p').html('');
					jQuery('#wprac-message-success-generate-token p').append(result.data.message);
					jQuery('#wprac-message-success-generate-token').fadeIn("slow");
				}else{	
					
				}
			});
		}else{
			console.log('cancel');
		}
	});

});

	

    