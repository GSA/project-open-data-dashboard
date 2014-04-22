<?php include 'header_meta_inc_view.php';?>

<?php include 'header_inc_view.php';?>

    <div class="container">
      <!-- Example row of columns -->
      
      <div class="row">
        <div class="col-lg-12">
          <h2>Validator</h2>
            
            <p>There are three ways you can validate data.json, either by validating a public URL, uploading a json file, or pasting the raw JSON into the form. 



            <h3 style="margin-top : 3em;">Validate data.json URL</h3>

            <form action="/validate" method="get" role="form">


                <div class="form-group">
                    <label for="datajson">Schema</label>
                    <select name="schema">
                        <option value="" selected="selected">Project Open Data</option>
                        <option value="non-federal">Non-Federal</option>
                        <option value="federal">Federal (strict)</option>
                    </select>
                </div>

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
                    <span class="input-group-btn">
                        <button type="submit" class="btn btn-primary">Validate URL</button>  
                    </span>
                    
                </div>
            </form>    

            <hr>       


            <h3 style="margin-top : 3em;">Validate data.json file upload</h3>

            <form action="/validate" method="post" enctype="multipart/form-data" role="form">


                <div class="form-group">
                    <label for="datajson">Schema</label>
                    <select name="schema">
                        <option value="" selected="selected">Project Open Data</option>
                        <option value="non-federal">Non-Federal</option>
                        <option value="federal">Federal (strict)</option>
                    </select>
                </div>
               
                 <div class="form-group">
                    <label for="datajson_upload">Upload a data.json file</label>
                    <input type="file" name="datajson_upload">
                </div>

                <div class="form-group">
                    <input type="hidden" name="output" value="browser">
                    <input type="submit" value="Validate File" class="btn btn-primary">
                </div>                
                    
                
            </form>    

            <hr> 



            <h3 style="margin-top : 3em;">Validate raw JSON</h3>

            <form action="/validate" method="post" role="form">
                <div class="form-group">
                    <label for="datajson">data.json JSON</label>
                    <textarea class="form-control" id="datajson" name="datajson" style="height : 30em; width: 100%"></textarea>
                </div>

                <div class="form-group">
                    <label for="datajson">Schema</label>
                    <select name="schema">
                        <option value="" selected="selected">Project Open Data</option>
                        <option value="non-federal">Non-Federal</option>
                        <option value="federal">Federal (strict)</option>
                    </select>
                </div>

                <div class="form-group">
                    <input type="hidden" name="output" value="browser">
                    <input type="submit" value="Validate JSON" class="btn btn-primary">
                </div>

            </form>

            
        </div>
    </div>      

<?php include 'footer.php'; ?>