#Documentation

This is a brief guide to understand how the agency dashboard measures and tracks implementation of [Project Open Data](http://project-open-data.github.com/)

##Agency Dashboard

The [Agency Dashboard](http://data.civicagency.org/offices) is primarily used to track implementation of the machine readable data.json file described by project open data, but it also tracks implementation of the human readable /data page. Below is an explanation of each part of the listing seen on a specific agency page.

<a name="leading_indicators"></a>
###Leading Indicators

<a name="edi_aggregate_score"></a>
####Overall Progress this Milestone
No documentation added yet 

<a name="edi_updated"></a>
####Inventory Updated this Quarter
No documentation added yet 

<a name="edi_datasets"></a>
####Number of Datasets
No documentation added yet 

<a name="edi_schedule_delivered"></a>
####Schedule Delivered
No documentation added yet 

<a name="edi_bureaus"></a>
####Bureaus represented
No documentation added yet 

<a name="edi_programs"></a>
####Programs represented
No documentation added yet 

<a name="edi_access_public"></a>
####Access Level = Public
No documentation added yet 

<a name="edi_access_restricted"></a>
####Access Level = Restricted
No documentation added yet 

<a name="edi_access_nonpublic"></a>
####Access Level = Non-Public
No documentation added yet 

<a name="edi_superset"></a>
####Inventory > Public listing
No documentation added yet 

<a name="edi_progress_evaluation"></a>
####Percentage growth in records since last quarter
No documentation added yet 

<a name="edi_schedule_risk"></a>
####Schedule Risk for Nov 30, 2014
No documentation added yet 

<a name="edi_quality_check"></a>
####Spot Check - Site search, SORNs, PIAs, FOIA
No documentation added yet 

<a name="pdl_aggregate_score"></a>
####Overall Progress this Milestone
No documentation added yet 

<a name="pdl_datasets"></a>
####Number of Datasets
No documentation added yet 

<a name="pdl_downloadable"></a>
####Number of Downloadable Datasets
No documentation added yet 

<a name="pdl_growth"></a>
####Percentage growth in records since last quarter
No documentation added yet 

<a name="pdl_valid_metadata"></a>
####Valid Metadata
No documentation added yet 

<a name="pdl_slashdata"></a>
####/data
No documentation added yet 

<a name="pdl_datajson"></a>
####/data.json
No documentation added yet 

<a name="pdl_datagov_harvested"></a>
####Harvested by data.gov
No documentation added yet 

<a name="pe_aggregate_score"></a>
####Overall Progress this Milestone
No documentation added yet 

<a name="pe_feedback_specified"></a>
####Description of feedback mechanism delivered
No documentation added yet 

<a name="pe_prioritization"></a>
####Data release is prioritized through public engagement
No documentation added yet 

<a name="pe_dialogue"></a>
####Feedback loop is closed, 2 way communication
No documentation added yet 

<a name="pe_reference"></a>
####Link to or description of Feedback Mechanism
No documentation added yet 

<a name="ps_aggregate_score"></a>
####Overall Progress this Milestone
No documentation added yet 

<a name="ps_publication_process"></a>
####Data Publication Process Delivered
No documentation added yet 

<a name="ps_publication_process_qa"></a>
####Information that should not to be made public is documented with agency's OGC
No documentation added yet 

<a name="hc_aggregate_score"></a>
####Overall Progress this Milestone
No documentation added yet 

<a name="hc_lead"></a>
####Open Data Primary Point of Contact
No documentation added yet 

<a name="hc_contacts"></a>
####POCs identified for required responsibilities
No documentation added yet 



<a name="automated_metrics"></a>
###Automated Metrics
These fields are determined by an automated script that runs every night to analyze agency data.json, digitalstrategy.json, and /data files. 

<a name="pdl_expected_url"></a>
####Expected URL

This is the URL where the data.json file is expected to be found. This is based on the main agency URL provided through the [USA.gov Directory API](http://www.usa.gov/About/developer-resources/federal-agency-directory/)

<a name="pdl_resolved_url"></a>
####Resolved URL

This is the URL that is resolved after following any redirects.

<a name="pdl_redirects"></a>
####Redirects

This is the number of redirects used to reach the final data.json URL. Currently this is only set to follow 5 redirects before stopping.

Ideally this should be 0

<a name="pdl_http_code"></a>
####HTTP Status

This is the [HTTP status code](http://en.wikipedia.org/wiki/HTTP_status_codes) received when attempting to reach the expected or resolved URL. For more information on properly using HTTP status codes, see: [Knowing Your HTTP Status Codes In Federal Government](http://kinlane.com/2013/11/06/knowing-your-http-status-codes-in-federal-government/)

This should be 200 it the data.json or /data URL was found successfully.

<a name="pdl_http_content_type"></a>
####Content-Type

The [Content-Type](http://en.wikipedia.org/wiki/Content-Type) is how the server announces the type of file it is serving at the requested URL. Usually it won't break anything if this is set incorrectly, but some applications may need to be set to force it to be read as JSON even if it announces it's something else. This is very similiar to how a file extension on a file identifies the file type. Yes, the URL says data.json, but the browser just sees that as an arbitrary URL. The Content-Type is what identifies the actual file type. Setting this incorrectly would be like if you had a file named graph.pdf that was actually a CSV spreadsheet file.

The [character encoding](http://en.wikipedia.org/wiki/Character_encoding) should also be specified as part of the Content-Type. This encoding should match the actual encoding of the text in the file. The correct character encoding for [JSON](http://json.org/) is always unicode, preferably [UTF-8](http://en.wikipedia.org/wiki/Utf-8).

For data.json this should be: `application/json; charset=utf-8`

For /data this should be: `text/html; charset=utf-8`

<a name="pdl_valid_json"></a>
####Valid JSON

This identifies whether the data.json was actually JSON. Even if the HTTP Status is 200 for the data.json URL and the Content-Type announces it's application/json; charset=UTF-8 the response might actually be HTML or improperly formatted JSON. You can check how well formed your JSON is with a tool like [JSONLint](http://jsonlint.com/). When using this tool it is best to enter the URL of the JSON file rather than copying and pasting the JSON. This is because when you copy and paste the raw JSON, your browser may attempt ot automtically fix problems that the server will not know to fix when it retrieves the file directly.

<a name="pdl_valid_schema"></a>
####Valid Schema

This identifies whether the data.json has all the required fields and has values that fit within the parameters specified by the [data.json schema metadata](http://project-open-data.github.io/schema/). This is validated using the rules codified within a [JSON Schema document](https://github.com/project-open-data/project-open-data.github.io/tree/master/schema/1_0_final) hosted on Project Open Data.

The validator hosted by HHS provides even more detailed analysis: http://hub.healthdata.gov/pod/validate 
