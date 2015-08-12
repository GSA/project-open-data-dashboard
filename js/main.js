/**
 * Last dashboardTotals "0" element for GAO Recommendations was removed
 */
var dashboardTotals = [null, null, 0, 0, 0, 0, 0, 0];

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

/**
 * Add totals row to dashboard
 * 
 * The last <td></td> in var row which was meant for for GAO Recommendations total 
 * was removed.
 */
var row = '<tr class="totals-row"><th>CFO Act Agencies (24)</th><td></td><td></td><td></td><td></td><td></td><td></td></tr>';
$(this).find('table.dashboard tr:last').after(row);
$(this).find('table.dashboard tr').each(function() {
    var n = 0;
    for (var col = 2; col < 8; col++) {
        n = $(this).find('td:nth-child(' + col + ')').find('i.text-success').length;
        dashboardTotals[col] += n;
    }
    n = parseInt($(this).find('td:nth-child(8)').text());
    dashboardTotals[8] += isNaN(n) ? 0 : n;
});
for (var col = 2; col <= 8; col++) {
    $(this).find('table.dashboard tr:last').find('td:nth-child(' + col + ')').html(dashboardTotals[col]);
}

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




