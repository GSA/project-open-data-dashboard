</head>
<body>
    <!--[if lt IE 7]>
        <p class="chromeframe">You are using an <strong>outdated</strong> browser. Please <a href="http://browsehappy.com/">upgrade your browser</a> or <a href="http://www.google.com/chromeframe/?redirect=true">activate Google Chrome Frame</a> to improve your experience.</p>
    <![endif]-->




<nav class="navbar navbar-inverse navbar-fixed-top" role="navigation">
  <div class="container">
    <!-- Brand and toggle get grouped for better mobile display -->
    <div class="navbar-header">
      <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1">
        <span class="sr-only">Toggle navigation</span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
      </button>
      <a class="navbar-brand" href="/">Project Open Data Dashboard</a>
    </div>

    <!-- Collect the nav links, forms, and other content for toggling -->
    <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
      <ul class="nav navbar-nav">


        <li><a href="/offices">Agencies</a></li>
        <li><a href="/validate">Validator</a></li>

        <li class="dropdown">
          <a href="#" class="dropdown-toggle" data-toggle="dropdown">Converters <b class="caret"></b></a>
          <ul class="dropdown-menu">
            <li><a href="/export">Export API</a></li>
            <li><a href="/datagov/csv_to_json">CSV Converter</a></li>
          </ul>
        </li>

        <li class="dropdown">
          <a href="#" class="dropdown-toggle" data-toggle="dropdown">Help <b class="caret"></b></a>
          <ul class="dropdown-menu">
            <li><a href="/docs">Documentation</a></li>       
            <li><a href="https://github.com/GSA/project-open-data-dashboard/issues">Feedback</a></li> 
          </ul>
        </li>        

      </ul>

      <?php if ($this->session->userdata('username')) : ?>
            <ul class="nav navbar-nav navbar-right">
              <li>
                    <div class="btn-group navbar-btn">
                      <button type="button" class="btn btn-inverse">
                        <i class="glyphicon glyphicon-user glyphicon-white"></i> 
                        <?php echo $this->session->userdata('name_full'); ?>
                      </button>
                      <button type="button" class="btn btn-inverse dropdown-toggle" data-toggle="dropdown">
                        <span class="caret"></span>
                        <span class="sr-only">Toggle Dropdown</span>
                      </button>
                      <ul class="dropdown-menu" role="menu">
                        <li><a href="/account"><i class="glyphicon glyphicon-pencil"></i> Account</a></li>
                        <li><a href="/logout"><i class="glyphicon glyphicon-remove"></i> Logout</a></li>    
                      </ul>
                    </div>
              </li>
            </ul>
      <?php endif; ?>

    </div><!-- /.navbar-collapse -->
  </div><!-- /.container-fluid -->
</nav>

