jQuery(document).ready(function($) {
	$('#gna_sortable').sortable({
		'tolerance': 'intersect',
		'cursor': 'pointer',
		'items': 'li',
		'placeholder': 'ui-state-highlight',
		'nested': 'ul'
	});
	
	$('#gna_sortable').disableSelection();
	
	$('form#frm_order').bind( 'submit', function(e) {
		$('#order').val( $('#gna_sortable').sortable('serialize') );
		
		return;
	});
});
