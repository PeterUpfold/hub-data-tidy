jQuery(document).ready(function($) {

	var dirty = false;

	$('#datepicker').datepicker();
	$('#datepicker').datepicker(
		'option', 'dateFormat', 'yy-mm-dd'
	);

	window.onbeforeunload = function() {
		if (dirty) {
			return hub_data_tidy_l10n.before_unload;
		}
	};

	$('form#data-tidy-form').submit(function(e) {
		e.preventDefault();

		$('input#submit').prop('disabled', 'disabled');
		$('input#submit').val(hub_data_tidy_l10n.submit_button_processing);
		$('#results-area').show('slow');

		dirty = true;

		var selected_wp_post_types = [];
		for (var i = 0; i < $('input.wp-post-type').length; i++) {
			var post_type =  $('input.wp-post-type')[i];
			if ( $(post_type).prop('checked') ) {
				selected_wp_post_types.push($(post_type).attr('id'));
			}
		}

		var selected_custom_types = [];
		for (i = 0; i < $('input.custom-type').length; i++) {
			var post_type =  $('input.custom-type')[i];
			if ( $(post_type).prop('checked') ) {
				selected_custom_types.push($(post_type).attr('id'));
			}
		}


		$.post( ajaxurl, {
			'action':                           'hub_data_tidy',
			'wp_post_types':                    selected_wp_post_types,
			'custom_types':                     selected_custom_types,
			'attached_username_prefix':         $('#attached-username-prefix').val(),
			'attached_username_toggle':         $('#attached-username-toggle').prop('checked'),
			'date_toggle':                      $('#date-toggle').prop('checked'),
			'date':                             $('#datepicker').val(),
			'simulate':                         $('#simulate').prop('checked'),
			'batch_size':                       $('#batch-size').val()

		}, function(data, textStatus, jqXHR) {
			if (data.success == true) {
				dirty = false;
				$('input#submit').removeProp('disabled');
				$('input#submit').val(hub_data_tidy_l10n.submit_button_normal);
				$('#spinner').css('visibility', 'hidden');
				$('#loading-text').text('');

				for (var i = 0; i < data.data.messages.length; i++) {
					$('#message-area').append('<p>' + data.data.messages[i] + '</p>' );
				}

			}
		}, 'json').fail(function() {

		});

	});

});
