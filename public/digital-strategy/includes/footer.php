<?php
/**
 * Footer Template File
 */
 ?>
 	<footer>
 		Generator Version <?php echo DGS_VERSION; ?>
 	</footer>
    </div> <!-- /container -->

    <!-- Le javascript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
    <script src="assets/js/jquery.js"></script>
    <script src="assets/js/bootstrap-transition.js"></script>
    <script src="assets/js/bootstrap-alert.js"></script>
    <script src="assets/js/bootstrap-modal.js"></script>
    <script src="assets/js/bootstrap-dropdown.js"></script>
    <script src="assets/js/bootstrap-scrollspy.js"></script>
    <script src="assets/js/bootstrap-tab.js"></script>
    <script src="assets/js/bootstrap-tooltip.js"></script>
    <script src="assets/js/bootstrap-popover.js"></script>
    <script src="assets/js/bootstrap-button.js"></script>
    <script src="assets/js/bootstrap-collapse.js"></script>
    <script src="assets/js/bootstrap-carousel.js"></script>
    <script src="assets/js/bootstrap-typeahead.js"></script>
    <script>
   		
   		$('#import-toggle').click(function(e){ 
   			e.preventDefault();
   			$('#import-upload').fadeToggle();
   		 });
   		 
   		$('#import-upload').change( function(){ $('#upload-form').submit() });
   		
   		//add multiple fields
   		$('.add').live( 'click', function(e) {
   			e.preventDefault();
   			var fields = $(this).siblings('.fields:first').clone().addClass( 'border-top' );
   			$.each( fields, function( key, field ) { 
   				$(field).find( 'input' ).val('');
    		});
   			$(this).before( fields );
   			return false;
   		})
    </script>
  </body>
</html>