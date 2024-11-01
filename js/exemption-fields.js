jQuery(document).ready(function($) {
	if ($('#tefw_exempt').is(':checked')) {
		$('#tefw_additional_fields').show();
	} else {
		$('#tefw_additional_fields').hide();
		$('#tefw_additional_fields_file').hide();
	}
	$('#tefw_exempt').change(function(){
		if (this.checked) {
			$('#tefw_additional_fields').fadeIn();
			$('#tefw_additional_fields_file').fadeIn();
			$('body').trigger('update_checkout');
		} else {
			$('#tefw_additional_fields').fadeOut();
			$('#tefw_additional_fields_file').fadeOut();
			$('body').trigger('update_checkout');
		}
	});
});