<?php

class ApiTest extends TestCase
{

    /*
     * This test fails if someone adds/removes a controller, or a public method on an existing controller.
     * We want them to explicitly document API changes by modifying the test fixture here.
     * We could ignore __construct, but since CodeIgniter filters that method out anyway its OK to include it.
     */
    public function testOnlyExpectedControllerMethodsAreExposed() {
        $expected_public_methods = '{"Campaign":["__construct","index","convert","csv_to_json","csv_field_mapper","schema_map_filter","csv","digitalstrategy","status","status_review_update","status_update","validate","upgrade_schema","get_instance"],"Docs":["__construct","index","get_instance"],"Export":["__construct","index","get_instance"],"Healthcheck":["index","__construct","get_instance"],"Import":["__construct","index","tracker","match_agency_slugs","match_bureaus","get_instance"],"Merge":["__construct","index","get_instance"],"Migrate":["__construct","index","get_instance"],"Offices":["__construct","index","export","detail","all","qa","milestone","get_instance"],"Welcome":["index","__construct","get_instance"]}';

        $public_methods = array();
        $controllerFiles = glob(APPPATH.'controllers/*.php');
        foreach ($controllerFiles as $filename) {
            $className = basename($filename, '.php');
            $public_methods[$className] = get_class_methods($className);
        }

        $this->assertEquals($expected_public_methods, json_encode($public_methods));
    }

}
