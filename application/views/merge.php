<?php include 'header_meta_inc_view.php';?>

<script src="<?php echo site_url('js/vendor/FileSaver.js')?>"></script>

<script type="text/javascript">

function readfile(f,i) {
    var reader = new FileReader(); 
    reader.readAsText(f); 
    reader.onload = function() { 
        
        var text = reader.result; 
        
        var out = document.getElementById("datafile"+i);  
        out.innerHTML = "";                          
        out.appendChild(document.createTextNode(text));

    }
    reader.onerror = function(e) {
        console.log("Error", e);  
    };
}    

function addRows() {
    $NumberOfRows = document.getElementById("NumberOfRows").value;     
    $NumberOfRows++;
    //alert($NumberOfRows);

    $rowHTML = '<div class="form-group row"><div class="col-md-3"><label>' + $NumberOfRows + ') Upload Data.json</label><div class="settingsGroup"><input type="file" onchange="readfile(this.files[0],' + $NumberOfRows + ')"></input></div></div><textarea class="col-md-9" rows="5" id="datafile' + $NumberOfRows + '"></textarea></div>'; 
    $("#mergefiles").append($rowHTML);

    document.getElementById("NumberOfRows").value = $NumberOfRows;
}

function mergeFiles() {

    $mergedArray = new Array();
    $totalCount = 0;

    mf = document.getElementsByTagName("textarea");
    $.each(mf, function(index, item) {                

        $thisJSON = item.value;

        if($thisJSON!='') {                 

            $thisArray = jQuery.parseJSON($thisJSON);

            $.each($thisArray, function(index2, item2) {
                $itemArray = {};

                for (var key in item2)  {
                    $itemArray[key] = item2[key]; 
                }

                $mergedArray.push($itemArray);
                $totalCount++;
            }); 

        }

    });  
                
    $mergedJSON = JSON.stringify($mergedArray);
               
    var blob = new Blob([$mergedJSON], {type: "application/json;charset=utf-8"});
    saveAs(blob, "data.json");

}
</script>   


<?php include 'header_inc_view.php';?>

    <div class="container">
      <!-- Example row of columns -->

      <div class="row">
        <div class="col-lg-12">
          <h2>Data.json Merge Tool</h2>
            <p>
                This is a simple tool for merging multiple data.json files into one. 
            </p>

            <form class="form-horizontal" role="form" id="settingsForm">
                <div id="mergefiles">
                    <input type="hidden" id="NumberOfRows" value="2" />
                    <div class="form-group row">
                        <div class="col-md-3">
                            <label>1) Upload Data.json</label>
                            <div class="settingsGroup">
                                <input type="file" onchange="readfile(this.files[0],1)"></input>
                            </div>   
                        </div>
                        <textarea class="col-md-9" rows="5" id="datafile1"></textarea>                        
                    </div>
                    <div class="form-group row">
                        <div class="col-md-3">
                            <label>2) Upload Data.json</label>
                            <div class="settingsGroup">
                                <input type="file" onchange="readfile(this.files[0],2)"></input>
                            </div>   
                        </div>
                        <textarea class="col-md-9" rows="5" id="datafile2"></textarea>                       
                    </div>                          
                </div>    

                <div class="row">
                    <div class="col-md-3">
                        <button class="btn btn-default" name="addRow" onclick="addRows(); return false;">+ Add Another File</button>
                    </div>
                    <button class="btn btn-success col-md-9"  name="addRow" onclick="mergeFiles(); return false;">Merge Files</button>
                    </div>    
                </div>

            </form>  



        </div>
    </div>

<?php include 'footer.php'; ?>