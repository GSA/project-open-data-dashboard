<?php include 'header_meta_inc_view.php';?>

<?php include 'header_inc_view.php';?>

    <div class="container">
      <!-- Example row of columns -->
      
      <div class="row">
        <div class="col-lg-12">
          <h2>Changeset Report Generator</h2>
            

            <form action="/changeset" method="post" role="form">

                <div class="form-group">
                    <label for="json_old">Old CKAN JSON URL - http://catalog.data.gov/api/3/action/package_search?q=</label>
                    <input name="json_old" id="json_old" class="form-control">
                </div>


                <div class="form-group">
                    <label for="datajson_new">New data.json URL</label>
                    <input name="datajson_new" id="datajson_new" class="form-control">
                </div>                

                <div class="form-group">
                    <input type="submit" value="Generate Report" class="btn btn-primary">
                </div>

            </form>       

            
        </div>
    </div>      

<?php include 'footer.php'; ?>