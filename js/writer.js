function init_writer(dom_preview, dom_writer)
{
	$(dom_writer).find("textarea").on( 'input', function() {
		$(dom_preview).html( marked( $(this).val() ) );
	} );
	
	$(dom_preview).html( marked( $(dom_writer).find("textarea").val() ) );
}