<?php include 'header_meta_inc_view.php';?>

<?php include 'header_inc_view.php';?>

    <div class="container">
      <!-- Example row of columns -->

      <div class="row">
        <div class="col-lg-12">
          <h2>Documentation</h2>
            <p>
                This is a brief guide to understand how the agency dashboard measures and tracks implementation of <a href="http://project-open-data.github.com">
                Project Open Data</a>
            </p>

            <h3>Agency Dashboard</h3>
            <p>
                The <a href="./offices">Agency Dashboard</a> is primarily used to track implementation of the machine readable
                data.json file described by project open data, but it also tracks implementation of the human
                readable /data page. Below is an explanation of each part of the listing seen on a specific agency page.
            </p>

            <h4>Expected URL</h4>
            <p>
                This is the URL where the data.json file is expected to be found. This is based on the main agency URL provided through the
                <a href="http://www.usa.gov/About/developer-resources/federal-agency-directory/">USA.gov Directory API</a>
            </p>

            <h4>Resolved URL</h4>
            <p>
                This is the URL that is resolved after following any redirects.
            </p>

            <h4>Redirects</h4>
            <p>
                This is the number of redirects used to reach the final data.json URL. Currently this is only set to follow 5 redirects
                before stopping.
            </p>
            <p>
                Ideally this should be 0
            </p>

            <h4>HTTP Status</h4>
            <p>
                This is the <a href="http://en.wikipedia.org/wiki/HTTP_status_codes">HTTP status code</a> received when attempting to
                reach the expected or resolved URL.
                For more information on properly using HTTP status codes, see:
                <a href="http://kinlane.com/2013/11/06/knowing-your-http-status-codes-in-federal-government/">Knowing Your HTTP Status Codes In Federal Government</a>
            </p>
            <p>
                <strong>This should be <code class="text-success">200</code> it the data.json or /data URL was found successfully.</strong>
            </p>

            <h4>Content-Type</h4>
            <p>
                The <a href="http://en.wikipedia.org/wiki/Content-Type">Content-Type</a> is how the server announces the type of file it is serving at the requested URL.
                Usually it won't break anything if this is set incorrectly,
                but some applications may need to be set to force it to be read as JSON even if it announces it's something else. This is very similiar to how a
                file extension on a file identifies the file type. Yes, the URL says data.json, but the browser just sees that as an arbitrary URL. The Content-Type is what
                identifies the actual file type. Setting this incorrectly would be like if you had a file named graph.pdf that was actually a CSV spreadsheet file.
            </p>
            <p>
                The <a href="http://en.wikipedia.org/wiki/Character_encoding">character encoding</a> should also be specified as part of the Content-Type. This
                encoding should match the actual encoding of the text in the file. The correct character encoding for <a href="http://json.org/">JSON</a> is always
                    unicode, preferably <a href="http://en.wikipedia.org/wiki/Utf-8">UTF-8</a>.
            </p>
            <p>
                <strong>For data.json this should be: <code class="text-success">application/json; charset=utf-8</code></strong>
            </p>
            <p>
                <strong>For /data this should be: <code class="text-success">text/html; charset=utf-8</code></strong>
            </p>


            <h4>Valid JSON</h4>
            <p>
                This identifies whether the data.json was actually JSON. Even if the HTTP Status is 200 for the data.json URL and the Content-Type announces it's
                application/json; charset=UTF-8 the response might actually be HTML or improperly formatted JSON. You can check how well formed your JSON is with a
                tool like <a href="http://jsonlint.com">JSONLint</a>. When using this tool it is best to enter the URL of the JSON file rather than copying and pasting
                the JSON. This is because when you copy and paste the raw JSON, your browser may attempt ot automtically fix problems that the server will not know to
                fix when it retrieves the file directly.
            </p>


            <h4>Valid Schema</h4>
            <p>
                This identifies whether the data.json has all the required fields and has values that fit within the parameters specified by the
                <a href="http://project-open-data.github.io/schema/">data.json schema metadata</a>. This is validated using the rules codified within a
                <a href="https://github.com/project-open-data/project-open-data.github.io/tree/master/schema/1_0_final">JSON Schema document</a> hosted on
                Project Open Data.
            </p>
            <p>
                The validator hosted by HHS provides even more detailed analysis: <a href="http://hub.healthdata.gov/pod/validate">http://hub.healthdata.gov/pod/validate</a>
            </p>


        </div>
    </div>

<?php include 'footer.php'; ?>