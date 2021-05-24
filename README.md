[![CircleCI](https://circleci.com/gh/GSA/project-open-data-dashboard.svg?style=svg)](https://circleci.com/gh/GSA/project-open-data-dashboard)

The Project Open Data Dashboard provides a variety of tools and capabilities to help manage the implementation of [Project Open Data](https://project-open-data.cio.gov/). It is primary used for Federal agencies, but also provides tools and resources for use by other entities like state and local government. 

The primary place for the user-facing documentation is https://labs.data.gov/dashboard/docs

## Features

* **[Dashboard overview](https://labs.data.gov/dashboard/offices)** of the status of each federal agency's implementation of [Project Open Data](https://project-open-data.cio.gov/) for each milestone.
* **Permissioned Content Editing** for the fields in the dashboard that can't be automated. The fields are stored as JSON objects so the data model is very flexible and can be customized without database changes. User accounts are handled via Github.
* **Automated crawls** for each agency to report metrics from Project Open Data assets (data.json, digitalstrategy.json, /data page, etc). This includes reporting on the number of data sets and validation against the Project Open Data metadata schema. 
* **A [validator](https://labs.data.gov/dashboard/validate)** to validate Project Open Data data.json files via URL, file upload, or text input. This can be used for testing both data.json Public Data Listing files as well as the Enterprise Data Inventory JSON. The validator can be used both by Federal agencies as well as non-federal entities by specifying the Non-Federal schema. 
* **Converters** to [export](https://labs.data.gov/dashboard/export) existing data from data.gov
* **[Changeset viewer](https://labs.data.gov/dashboard/changeset)** to see how a data.json file for an agency compares to metadata currently hosted on data.gov

## CLI Interface

In addition to the web interface, there's also a Command Line Interface to manage the crawls of data.json, digitalstrategy.json, and /data pages. This is helpful to run specific updates, but it's primary use is with a CRON job. 

From the root of the application, you can update the status of agencies using a few different options on the `campaign` controller. The syntax is:

`$ php public/index.php campaign status [id] [component]`

If you wanted to update all components (data.json, digitalstrategy.json, /data) for all agencies, you'd run this command:

`$ php public/index.php campaign status all all`

If you just wanted to update the data.json status for CFO Act agencies you'd run:

`$ php public/index.php campaign status cfo-act datajson`

If you just wanted to update the data.json status for agencies being monitored by the OMB you'd run:

`$ php public/index.php campaign status omb-monitored datajson`

If you just wanted to update the digitalstrategy.json status for the Department of Agriculture you'd run:

`$ php public/index.php campaign status 49015 digitalstrategy`

There are agencies whose crawls take a long time to complete. These are identified with the `id` of `long-running`. You can find a current list of these [in this db migration](application/migrations/010_add_long_running_flag.php). To initiate a full-scan for these agencies, you'd run:

`$ php public/index.php campaign status long-running full-scan`

The options for [id] are: `all`,`cfo-act`, `omb-monitored`, `long-running` or the ID provided by the [USA.gov Federal Agency Directory API](http://www.usa.gov/About/developer-resources/federal-agency-directory/).

The options for [component] are: `all`, `datajson`, `datapage`, `digitalstrategy`, `download`, `full-scan`. 

* The `datajson` component captures the basic characteristics of a request to an agency's data.json file (like whether it returns an HTTP 200) and then attempts to parse the file, validate against the schema, and provide other reporting metrics like the number of datasets listed. 
* The `digitalstrategy` component captures the basic characteristics of a request to an agency's digitalstrategy.json file (like whether it returns an HTTP 200) 
* The `datapage` component captures the basic characteristics of a request to an agency's /data page (like whether it returns an HTTP 200)
* The `download` component downloads an archive of the data.json and digitalstrategy.json files
* The `full-scan` component does further validation based on the content of the response
* As you'd expect, `all` does all of these things at once. 

## Development

This is a [CodeIgniter](http://www.codeigniter.com/) PHP application. We use Docker and Docker compose for local development and cloud.gov for testing and production (pending migration from BSP.)

Prerequisites:

* [Docker Engine](https://docs.docker.com/install/) v18+
* [Docker Compose](https://docs.docker.com/compose/install/) v1.24+

By default, the `ENVIRONMENT` variable is set to production so that error messages will not be displayed. To display these messages while developing, you should edit your `.env` file to include the variable `CI_ENV` set to anything other than `production`. See [index.php](https://github.com/GSA/project-open-data-dashboard/blob/master/public/index.php#L56-L91) for more details.

### Setup

Install application dependencies

    make install-dev-dependencies

Start up the application and database

    make up

Run tests

    make test

Open your browser to [localhost:8000](http://localhost:8000/).

### Restoring database dumps

If you need a database dump, you can create one following instructions from the
[Runbook](https://github.com/GSA/datagov-deploy/wiki/Dashboard#database-dump). Clean up the database dump by removing any `USE database` statement, or `CREATE DATABASE` statement. Then:

    cat cleaned_database.sql |
      docker-compose run --rm database mysql \
      --host=database --user=root --password=mysql dashboard

After a database restore, test by viewing a USDA detail page:

    curl http://localhost:8000/offices/detail/49015/2017-08-31

### Making database schema changes

#### To update the schema

Add a new [numbered migration class](application/migrations), then change [the configured version number](application/config/migration.php#L72) to match. To perform the migration, CodeIgniter will automatically run `up()` migration methods until the schema version in the database matches the configured version.

If you want to invoke the migration explicitly to test that it's working, you can run `php public/index.php migrate`. Otherwise expect that the migration will be invoked automatically before CodeIgniter will handle any other requests.

#### To revert the schema

Change [the configured version number](application/config/migration.php#L72) to match the schema version you want to revert to. CodeIgniter will automatically run `down()` migration methods until the schema version in the database matches the configured version. 

You can invoke the reversion as described for updates above.

#### Migration requirements

The dashboard uses MySQL for the backend database. [MySQL doesn't support transactions around schema-altering statements.](https://hashrocket.com/blog/posts/mysql-has-transactions-sorta) If any problems are encountered during a migration, the app is likely to wind up in a confused state where schema-altering statements have been applied, but the version of the schema in the database remains at the previous version. The migration will be attempted over and over again, often exhibiting user-visible errors or other bad behavior until manual intervention happens.

To avoid this, we need to be careful to write migrations that are both idempotent and reversible. (That is, we should be able to run them again without generating errors, and we should be able to downgrade to previous schema versions automatically.)

This requires some care because there's no guaranteed way to make it happen. Whenever we do a PR review that includes a schema change, the answer should be "yes" to all of these questions: 
* Does each of the schema-altering statements happen in its own migration?
* Does the `down()` method exist on the migration, and does it undo any schema-changing action performed in the `up()` method?
* Does every `CREATE TABLE` statement use `IF NOT EXISTS`?
* Does every `DROP TABLE` statement use `IF EXISTS`? 
* Does every `ADD/CHANGE/ALTER COLUMN` happen via a call to the idempotent [add_column_if_not_exists](application/migrations/009_add_idempotent_migration_scaffolding.php#L75) helper?
* Does every `DROP COLUMN` happen via a call to the idempotent [drop_column_if_exists](application/migrations/009_add_idempotent_migration_scaffolding.php#L60) helper?

### CircleCI testing

All pushes to GitHub are integration tested with our [CircleCI tests](https://circleci.com/gh/GSA/project-open-data-dashboard).

### Updating composer dependencies

Edit version constraints in [composer.json](./composer.json).

    make update-dependencies

Commit the updated composer.json and composer.lock.


## Deploying to cloud.gov

### Quickstart with an empty database

Copy the [vars.yml.template file](./vars.yml.template) and rename it to vars.yml. Edit any values following the comments in the file.

If you are not logged in for the Cloud Foundry CLI, follow the steps [in this guide](https://docs.cloudfoundry.org/cf-cli/getting-started.html)

Assuming you're logged in for the Cloud Foundry CLI, Run the following commands and replacing ${app_name} with the value in your vars.yml file.

```sh
$ cf create-service aws-rds small-mysql-redundant ${app_name}-db

$ cf create-service s3 basic ${app_name}-s3

$ cf create-user-provided-service ${app_name}-secrets -p '{
  "ENCRYPTION_KEY": "long-random-string"
}'

$ cf push --vars-file vars.yml
Waiting for app to start...

name:              app
requested state:   started
routes:            <b><u>app-boring-sable.app.cloud.gov</u></b>
last uploaded:     Wed 28 Aug 10:02:06 EDT 2019
stack:             cflinuxfs3
buildpacks:        php_buildpack

type:            web
instances:       1/1
memory usage:    256M
start command:   $HOME/.bp/bin/start
     state     since                  cpu    memory          disk             details
#0   running   2019-08-28T14:02:25Z   0.3%   24.3M of 256M   301.7M of 512M
```

You should be able to visit https://&lt;ROUTE&gt;/offices/qa, where &lt;ROUTE&gt; is the route reported from `cf push`:

### Restoring a database backup to cloud.gov:

If you need a database dump, you can create one following instructions from the
[Runbook](https://github.com/GSA/datagov-deploy/wiki/Dashboard#database-dump). Clean up the database dump by removing any `USE database` statement, or `CREATE DATABASE` statement. We'll call this `cleaned_database.sql` below. Then:

Install the [cf-service-connect](https://github.com/18F/cf-service-connect) plugin, e.g., for version 1.1.0 of the plugin on a MacOS system:

    cf install-plugin https://github.com/18F/cf-service-connect/releases/download/1.1.0/cf-service-connect-darwin-amd64

Open up a tunnel to the database, and leave the tunnel open for the next step:

    $ cf connect-to-service --no-client app database
    Host: localhost
    Port: NNNN
    Username: randomuser
    Password: randompass
    Name: cgawsbrokerrandomname


In a separate terminal session, use the connection information to make a MySQL connection to restore `cleaned_database.sql`. When prompted for a password, paste in the password (e.g `randompass` in this example).

    cat cleaned_database.sql | 
      mysql -h 127.0.0.1 -PNNNN -u randomuser -p cgawsbrokerrandomname

After a restore, you should be able to view an agency's detail page, such as: https://&lt;ROUTE&gt;/offices/detail/49015/2017-08-31

### CI configuration

Create a GitHub environment for each application you're deploying. Each
GH environment should be configured with secrets from a [ci-deployer service
account](https://github.com/GSA/datagov-deploy/wiki/Cloud.gov-Cheat-Sheet#space-organization).

Secret name | Description
----------- | -----------
CF_SERVICE_AUTH | The service key password.
CF_SERVICE_USER | The service key username.

## Known issues

The agency hierarchy is designed to be populated from the `contacts` API at https://www.usa.gov/api/USAGovAPI/contacts.json/contact, but that is no longer available, so these
following steps no longer work:

> * Federal agencies were seeded using the [USA.gov Federal Agency Directory API](http://www.usa.gov/About/developer-resources/federal-agency-directory/) and the IDs provided by that resource are used as the primary IDs on this dashboard. 
> * First populate the top of the agency hierarchy: `$ php public/index.php import`
> * Second, populate all the subagencies: `$ php public/index.php import children`
> * If you have an empty database `offices` table in the database, you'll also want to seed it with agency data by running the import script (`/application/controllers/import.php`) from a command line. You'll also need to temporarily change the `import_active` option in config.php to `true`


Currently this tool does not handle large files in a memory efficient way. If you are unable to utilize a high amount of memory and are at risk of timeouts, you should set the maximum file size that the application can handle so it will avoid large files and fail more gracefully. The maximum size of JSON files to parse can be set with the `max_remote_size` option in config.php

**What about S3?**

S3 is used in a few places when `config[use_local_storage]` is false:
- for archiving data.json and digitalstrategy (public)

The `use_local_storage` setting does not impact all uses of the `upload` class, just those cases above.

The `archive_file` function will use config[use_local_storage] anytime it's called but the logic doesn't apply when to `datajson_lines` is set as `filetype`. 

Here's an outline of where S3 is used in the code:

`models/Campaign_model.php`:
  - `archive_file` which calls  `archive_to_s3` when `use_local_storage` is false
    - the `validate_datajson` function calls `archive_file` but sets `filetype` to `datajson-lines` so the `archive_file` function does not store it in S3, regardless of `use_local_storage` setting.
  - `archive_to_s3` which calls put_to_s3 and stores with a PUBLIC acl
  - `put_to_s3` which stores private by default
  - `get_from_s3` previously used by `csv_to_json`; unused now 

`views/office_detail.php`:
  - Builds a URL based on values of `config/s3_bucket` for displaying the "Analyze archive copies" line of _Automated Metrics_.


**S3 changes for cloud.gov***

- There's a need for one public S3 bucket for archiving data.json from crawls, and fetching them in the `office_detail.php`.
