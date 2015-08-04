<?php include 'header_meta_inc_view.php';?>

<?php include 'header_inc_view.php';?>

    <div class="container">
      <!-- Example row of columns -->

      <div class="row">
        <div class="col-lg-12">
          <h2>Data.json Export API</h2>
            <p>
                This is a simple API for exporting data from <a href="http://catalog.data.gov">http://catalog.data.gov</a> in a way that conforms to the data.json schema as both JSON and CSV files
            </p>

            <h3>As JSON</h3>
            <code>GET /ciogov/convert</code>
            <p>
                The only parameter is <code>?orgs</code> where the <code>orgs</code> refers the the name of the organization in CKAN. For example: <code><a href="<?php echo site_url('ciogov/convert?orgs=usgs-gov'); ?>"><?php echo site_url('ciogov/convert?orgs=usgs-gov'); ?></a></code>
            </p>

            <h3>As CSV</h3>
            <code>GET /ciogov/csv</code>
            <p>
                The only parameter is <code>?orgs</code> where the <code>orgs</code> refers the the name of the organization in CKAN. For example: <code><a href="<?php echo site_url('ciogov/csv?orgs=usgs-gov'); ?>"><?php echo site_url('ciogov/csv?orgs=usgs-gov'); ?></a></code>
            </p>

        </div>
    </div>

<?php include 'footer.php'; ?>