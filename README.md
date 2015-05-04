The Project Open Data Dashboard provides a variety of tools and capabilities to help manage the implementation of [Project Open Data](https://project-open-data.cio.gov/). It is primary used for Federal agencies, but also provides tools and resources for use by other entities like state and local government. 

The primary place for the user-facing documentation is http://data.civicagency.org/docs

Federal agencies were seeded using the [USA.gov Federal Agency Directory API](http://www.usa.gov/About/developer-resources/federal-agency-directory/) and the IDs provided by that resource are used as the primary IDs on this dashboard. 

Features
-----
* **[Dashboard overview](http://data.civicagency.org/offices)** of the status of each federal agency's implementation of [Project Open Data](https://project-open-data.cio.gov/) for each milestone.
* **Permissioned Content Editing** for the fields in the dasboard that can't be automated. The fields are stored as JSON objects so the data model is very flexible and can be customized without database changes. User accounts are handled via Github.
* **Automated crawls** for each agency to report metrics from Project Open Data assets (data.json, digitalstrategy.json, /data page, etc). This includes reporting on the number of datasets and validation against the Project Open Data metadata schema. 
* **A [validator](http://data.civicagency.org/validate)** to validate Project Open Data data.json files via URL, file upload, or text input. This can be used for testing both data.json Public Data Listing files as well as the Enterprise Data Inventory JSON. The validator can be used both by Federal agencies as well as non-federal entities by specifiying the Non-Federal schema. 
* **Converters** to transform a [CSV into a data.json](http://data.civicagency.org/datagov/csv_to_json) file or to [export](http://data.civicagency.org/export) existing data from Data.gov
* **[Changeset viewer](http://data.civicagency.org/changeset)** to see how a data.json file for an agency compares to metadata currently hosted on data.gov


CLI Interface
-----
In addition to the web interface, there's also a Command Line Interface to manage the crawls of data.json, digitalstrategy.json, and /data pages. This is helpful to run specific updates, but it's primary use is with a CRON job. 

From the root of the application, you can update the status of agencies using a few different options on the `campaign` controller. The syntax is:

`$ php index.php campaign status [id] [component]`

If you wanted to update all components (data.json, digitalstrategy.json, /data) for all agencies, you'd run this command:

`$ php index.php campaign status all all`

If you just wanted to update the data.json status for CFO Act agencies you'd run:

`$ php index.php campaign status cfo-act datajson`

If you just wanted to update the digitalstrategy.json status for the Department of Agriculture you'd run:

`$ php index.php campaign status 49015 digitalstrategy`

The options for [id] are: `all`,`cfo-act`, or the ID provided by the [USA.gov Federal Agency Directory API](http://www.usa.gov/About/developer-resources/federal-agency-directory/)

The options for [component] are: `all`, `datajson`, `datapage`, `digitalstrategy`, `download`. 

* The `datajson` component captures the basic charactersitics of a request to an agency's data.json file (like whether it returns an HTTP 200) and then attempts to parse the file, validate against the schema, and provide other reporting metrics like the number of datasets listed. 
* The `digitalstrategy` component captures the basic charactersitics of a request to an agency's digitalstrategy.json file (like whether it returns an HTTP 200) 
* The `datapage` component captures the basic charactersitics of a request to an agency's /data page (like whether it returns an HTTP 200)
* The `download` component downloads an archive of the data.json and digitalstrategy.json files
* As you'd expect, `all` does all of these things at once. 

Installation
-----

This is a [CodeIgniter](http://www.codeigniter.com/) PHP application. Installation primarily consists of editing config files in `/application/config` and importing the database schema. 

1. Grab the code `git clone https://github.com/GSA/project-open-data-dashboard.git`
1. Copy `/sample.htaccess` to `/.htaccess`. You may need to adjust the configuration of your .htaccess file to match your environment.
1. Copy `/index.php.sample` to `/index.php`. The index.php file is where you specify whether you're running a production or development environemtn. If you are running a production environment, you'll need to edit line 21 to `define('ENVIRONMENT', 'production');` otherwise you can leave the file unchanged. 
	* Note that wherever you see `<env>` in the following configuration steps, you should replace that with the environment you're currently using. To begin with, you'll probably want this to be: `development`
1. Copy `/application/config/config.php.sample` to `/application/config/<env>/config.php`
1. Edit `/application/config/<env>/config.php` with: 
	* The `base_url` where this application will run. This actually shouldn't required for anything other than the following `github_oauth_redirect` which depends on it, but you can set that manually if you'd like. 
	* Your Github OAuth Client ID, Client Secret, and redirect URL for <a href="https://github.com/settings/applications/new">GitHub authentication</a> 
	* The path to where you want to store the archival json files (`archive_dir`) - give this directory adequate permissions for your server to write to
	* You can pre-approave github users to have admin rights by adding their github username to the array in the `pre_approved_admins` option
	* Set an encryption key for the `encryption_key` option (around line 253). You can generate a key with a command like `LANG=C tr -dc A-Za-z0-9_ < /dev/urandom | head -c 32 | xargs` or get one from this [generator](http://jeffreybarke.net/tools/codeigniter-encryption-key-generator/)
1. Copy `/application/config/upload.php.sample` to `/application/config/<env>/upload.php`	
1. Edit `/application/config/<env>/upload.php` with the `upload_path` where you want to save uploaded CSV files (give this directory adequate permissions for your server to write to)
1. Create a local database and import the SQL database tables found in /sql/ into this local database:
	* `mysql> CREATE DATABASE pod_dashboard;`
    * `$> mysql -u root -p pod_dashboard < datagov_campaign.sql`
    * `$> mysql -u root -p pod_dashboard < offices.sql`
    * `$> mysql -u root -p pod_dashboard < notes.sql`
    * `$> mysql -u root -p pod_dashboard < users_auth.sql`
1. Copy `/application/config/database.php.sample` to `/application/config/<env>/database.php`
1. Edit `/application/config/<env>/database.php` with your local database settings

If you have an empty database `offices` table in the database, you'll also want to seed it with agency data by running the import script (`/application/controllers/import.php`) from a command line. You'll also need to temporarily change the `import_active` option in config.php to `true`


First populate the top of the agency hierarchy: 

`$ php index.php import`

Second, populate all the subagencies:

`$ php index.php import children`

Limitations
-----
Currently this tool does not handle large files in a memory efficient way. If you are unable to utilize a high amount of memory and are at risk of timeouts, you should set the maximum file size that the application can handle so it will avoid large files and fail more gracefully. The maximum size of JSON files to parse can be set with the `max_remote_size` option in config.php
