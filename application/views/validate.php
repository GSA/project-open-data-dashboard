<?php include 'header_meta_inc_view.php';?>

<?php include 'header_inc_view.php';?>

    <div class="container">
      <!-- Example row of columns -->
      
      <div class="row">
        <div class="col-lg-12">
          <h2>Validator</h2>
            

            <form action="/validate" method="post" role="form">
                <div class="form-group">
                    <label for="datajson">data.json JSON</label>
                    <textarea class="form-control" id="datajson" name="datajson" style="height : 30em; width: 100%"></textarea>
                </div>

                <div class="form-group">
                    <label for="datajson">Schema</label>
                    <select name="schema">
                        <option value="" selected="selected">Select an Option</option>
                        <option value="non-federal">Non-Federal</option>
                        <option value="federal">Federal</option>
                    </select>
                </div>

                <div class="form-group">
                    <input type="submit" value="Validate JSON" class="btn btn-primary">
                </div>

            </form>

            <div style="margin : 2em 0">OR</div>

            <form action="/validate" method="post" role="form">


                <div class="form-group">
                    <label for="datajson">Schema</label>
                    <select name="schema">
                        <option value="" selected="selected">Select an Option</option>
                        <option value="non-federal">Non-Federal</option>
                        <option value="federal">Federal</option>
                    </select>
                </div>

                    <label for="datajson_url">data.json URL</label>
                    <div class="input-group">
                        <input name="datajson_url" id="datajson_url" class="form-control">
                        <span class="input-group-btn">
                            <input type="submit" class="btn btn-default" value="Validate URL">   
                        </span>
                        
                    </div>
            </form>            

            
        </div>
    </div>      

<?php include 'footer.php'; ?>