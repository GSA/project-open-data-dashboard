var dashboardTotals = [null, null]; // initialize with nulls that represent non-data columns

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
var doDashboardTotals = function() {
    
    // Initialize totals row, and append to table
    var cols = $(document).find('table.dashboard:first tr:last td').length;
    var row = '<tr class="totals-row"><th>CFO Act Agencies (24)</th>';
    for (var col = 0; col < cols; col++) {
        row += '<td></td>';
        dashboardTotals.push(0);
    }
    row += '</tr>';
    $(document).find('table.dashboard:first tr:last').after(row);
 
    // Get totals
    $(document).find('table.dashboard:first tr').each(function() {
        var n = 0;
        var cols = $(this).find('td').length;
        if (cols > 5) { // exclude header rows
            for (var col = 2; col <= cols + 1; col++) {
                if (parseInt($(this).find('td:nth-child(' + col + ')').text()) > 0) {
                    n = parseInt($(this).find('td:nth-child(' + col + ')').text());
                } else {
                    n = $(this).find('td:nth-child(' + col + ')').find('i.text-success').length;
                }
                dashboardTotals[col] += isNaN(n) ? 0 : n;
            }
        }
    });
    
    // Insert totals into totals row
    for (var col = 2; col <= cols + 1; col++) {
        $(document).find('table.dashboard:first tr:last').find('td:nth-child(' + col + ')').html(dashboardTotals[col]);
    }
    
};

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



doDashboardTotals();




$('.datepicker').datepicker({
    format:'yyyy-mm-dd'
});



});




