</head>
<body>
    <!--[if lt IE 7]>
        <p class="chromeframe">You are using an <strong>outdated</strong> browser. Please <a href="http://browsehappy.com/">upgrade your browser</a> or <a href="http://www.google.com/chromeframe/?redirect=true">activate Google Chrome Frame</a> to improve your experience.</p>
    <![endif]-->

<?php if(($this->config->item('google_analytics_id'))): ?>
  <script>
    (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
    (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
    m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
    })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

    ga('create', '<?php echo $this->config->item('google_analytics_id')?>', 'auto');
    ga('set', 'anonymizeIp', true);
    ga('send', 'pageview');

  </script>
<?php endif; ?>

<script id="_fed_an_ua_tag" src="https://analytics.usa.gov/dap/dap.min.js?agency=GSA"></script>

<nav class="navbar navbar-inverse navbar-fixed-top" role="navigation">
  <div class="container <?php if(!empty($container_class)) echo $container_class; ?>">
    <!-- Brand and toggle get grouped for better mobile display -->
    <div class="navbar-header">
      <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1">
        <span class="sr-only">Toggle navigation</span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
      </button>
      <a class="navbar-brand" href="<?php echo site_url('')?>" title="Link to FITARA Dashboard" tabindex="1">FITARA Dashboard</a>
    </div>

    <!-- Collect the nav links, forms, and other content for toggling -->
    <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
      <ul class="nav navbar-nav">


        <li><a href="<?php echo site_url('offices')?>" title="Link to Agency Offices" tabindex="2">Agencies</a></li>
        <!--<li><a href="<?php echo site_url('validate')?>">Validator</a></li>-->

        <!--<li class="dropdown">
          <a href="#" class="dropdown-toggle" data-toggle="dropdown">Converters <b class="caret"></b></a>
          <ul class="dropdown-menu">
            <li><a href="<?php echo site_url('export')?>">Export API</a></li>
            <li><a href="<?php echo site_url('ciogov/csv_to_json')?>">CSV Converter</a></li>
            <li><a href="<?php echo site_url('upgrade-schema')?>">Schema Converter</a></li>
            <li><a href="<?php echo site_url('merge')?>">Data.json Merger</a></li>
          </ul>
        </li>-->

        <!--<li class="dropdown">
          <a href="#" class="dropdown-toggle" data-toggle="dropdown">Help <b class="caret"></b></a>
          <ul class="dropdown-menu">
            <li><a href="<?php echo site_url('docs')?>">Documentation</a></li>
            <li><a href="https://github.com/GSA/project-open-data-dashboard/issues">Feedback</a></li>
          </ul>
        </li>-->

        <!--<li><a href="<?php echo site_url('docs/about')?>">About</a></li>-->


      </ul>


      <ul class="nav navbar-nav navbar-right">
        <?php if($this->config->item('feedback_url')): ?>
        <li><a href="<?php echo $this->config->item('feedback_url');?>" title="Feedback" target="_blank">Feedback</a></li>
        <?php endif; ?>
        <?php if ($this->session->userdata('username')) : ?>
        <li>
          <div class="btn-group navbar-btn">
            <button type="button" class="btn btn-inverse" tabindex="-1">
              <i class="glyphicon glyphicon-user glyphicon-white"></i>
              <?php echo $this->session->userdata('name_full'); ?>
            </button>
            <button type="button" class="btn btn-inverse dropdown-toggle" data-toggle="dropdown" tabindex="3">
              <span class="caret"></span>
              <span class="sr-only">Toggle Dropdown</span>
            </button>
            <ul class="dropdown-menu" role="menu">
              <li><a href="<?php echo site_url('account')?>" title="link to Account" tabindex="-1"><i class="glyphicon glyphicon-pencil"></i> Account</a></li>
              <li><a href="<?php echo site_url('logout')?>" title="link to logout" tabindex="-1"><i class="glyphicon glyphicon-remove"></i> Logout</a></li>
            </ul>
          </div>
        </li>
        <?php endif; ?>
      </ul>

    </div><!-- /.navbar-collapse -->
  </div><!-- /.container-fluid -->
</nav>

<div id="main-container">
