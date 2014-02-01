<?php include 'header_meta_inc_view.php';?>

<?php include 'header_inc_view.php';?>

    <div class="container">
      <!-- Example row of columns -->
      
      <div class="row">
        <div class="col-lg-12">
          <h2>Validator</h2>
            
            <form action="/validate" method="post">
                <textarea name="datajson" style="height : 30em; width: 100%"></textarea>
                <input type="submit" value="Submit">
            </form>



            <form action="/validate" method="post">
                <input name="datajson_url">
                <input type="submit" value="Submit">
            </form>            

            
        </div>
    </div>      

<?php include 'footer.php'; ?>