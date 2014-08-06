
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





/* Adjust the scroll height of anchors to compensate for the fixed navbar */
window.disableShift = false;
var shiftWindow = function() {
    if (window.disableShift) {
        window.disableShift = false;
    } else {
        /* If we're at the bottom of the page, don't erronously scroll up */
        var scrolledToBottomOfPage = (
            (window.innerHeight + window.scrollY) >= document.body.offsetHeight
        );
        if (!scrolledToBottomOfPage) {
            scrollBy(0, -50);
        };
    };
};

if (window.location.hash){
	setTimeout(shiftWindow, 300);
} 

window.addEventListener("hashchange", shiftWindow);












});




