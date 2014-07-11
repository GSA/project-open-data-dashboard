
$(function() {


	$(".edit-toggle").each( function() {

		$(this).children('.edit-button').click( function(){
		    
		    $(this).parent().children('.edit-area').replaceWith(function() {
		    	
		    	var textfield = $('<textarea class="form-control">').text(
		  							$(this).parent().children('.edit-raw').html()
		  						);

		    	var fieldname = 'this_name';
		    	var fieldname = $(this).parent().children('.edit-raw').attr( "data-fieldname" );

		    		textfield.attr( "name", fieldname);

		  		return textfield;
			});

		});	    

	});



$('#accShow').on('click', function() {
    if($(this).text() == 'Show All Notes') {
        $('.edit-toggle.collapse:not(.in)').each(function (index) {
            $(this).collapse("toggle");
        });
        $(this).text('Hide All Notes');
    } else {
        $(this).text('Show All Notes');
        $('.edit-toggle.in').each(function (index) {
            $(this).collapse("toggle");
        });
    }
    return false;
});




var shiftWindow = function() { scrollBy(0, -50) };
if (location.hash) shiftWindow();
window.addEventListener("hashchange", shiftWindow);


});




