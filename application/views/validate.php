<?php include 'header_meta_inc_view.php';?>

<?php include 'header_inc_view.php';?>

    <div class="container">
      <!-- Example row of columns -->

      <div class="row">
        <div class="col-lg-12">
          <h2>Validator</h2>

            <p>There are three ways you can validate data.json, either by validating a public URL, uploading a json file, or pasting the raw JSON into the form.



            <h3 style="margin-top : 3em;">Validate data.json URL</h3>

            <form action="<?php echo site_url(); ?>validate" method="get" role="form">


                <div class="form-group">
                    <label for="datajson">Schema</label>
                    <select name="schema">                        
                        <option value="federal-v1.1" selected="selected">Federal v1.1</option>                                                
                        <option value="federal">Federal v1.0</option> 
                        <option value="non-federal-v1.1">Non-Federal v1.1</option>                           
                        <option value="non-federal">Non-Federal v1.0</option>  
                        
                    </select>
                </div>

            <p>Note: For successful Non-Federal v1.1 schema validation, <a href=https://project-open-data.cio.gov/v1.1/schema/#USG-note>USG noted fields</a> and <a href=https://project-open-data.cio.gov/v1.1/schema/#contactPoint>contactPoint</a> are not required. However when included contactPoint should always contain both the person’s appropriately formatted full name (fn) and email (hasEmail).

                <div class="form-group">
                    <label class="radio-inline">
                        <input checked="checked" type="radio" id="output-browser" name="output" value="browser"> View in Browser
                    </label>

                    <label class="radio-inline">
                        <input type="radio" id="output-browser" name="output" value="json"> Output JSON
                    </label>
                </div>

                <label for="datajson_url">data.json URL</label>
                <div class="input-group">
                    <input name="datajson_url" id="datajson_url" class="form-control"  placeholder="e.g. http://energy.gov/data.json" >
                    <input name="qa" value="true" type="hidden">
                    <span class="input-group-btn">
                        <button type="submit" class="btn btn-primary">Validate URL</button>
                    </span>

                </div>
            </form>

            <hr>


            <h3 style="margin-top : 3em;">Validate data.json file upload</h3>

            <form action="<?php echo site_url(); ?>validate" method="post" enctype="multipart/form-data" role="form">


                <div class="form-group">
                    <label for="datajson">Schema</label>
                    <select name="schema">
                        <option value="federal-v1.1" selected="selected">Federal v1.1</option>                                                
                        <option value="federal">Federal v1.0</option> 
                        <option value="non-federal-v1.1">Non-Federal v1.1</option>   
                        <option value="non-federal">Non-Federal v1.0</option>
                        
                    </select>
                </div>

                 <div class="form-group">
                    <label for="datajson_upload">Upload a data.json file</label>
                    <input type="file" name="datajson_upload">
                </div>

                <div class="form-group">
                    <input name="qa" value="true" type="hidden">
                    <input type="hidden" name="output" value="browser">
                    <input type="submit" value="Validate File" class="btn btn-primary">
                </div>


            </form>

            <hr>



            <h3 style="margin-top : 3em;">Validate raw JSON</h3>

            <form action="<?php echo site_url(); ?>validate" method="post" role="form">
                <div class="form-group">
                    <label for="datajson">data.json JSON</label>
                    <textarea class="form-control" id="datajson" name="datajson" style="height : 30em; width: 100%"></textarea>
                </div>

                <div class="form-group">
                    <label for="datajson">Schema</label>
                    <select name="schema">
                        <option value="federal-v1.1" selected="selected">Federal v1.1</option>                                                
                        <option value="federal">Federal v1.0</option>                       
                        <option value="non-federal-v1.1">Non-Federal v1.1</option>   
                        <option value="non-federal">Non-Federal v1.0</option>
                    </select>
                </div>

                <div class="form-group">
                    <input name="qa" value="true" type="hidden">
                    <input type="hidden" name="output" value="browser">
                    <input type="submit" value="Validate JSON" class="btn btn-primary">
                </div>

            </form>


        </div>
    </div>

<?php include 'footer.php'; ?>
