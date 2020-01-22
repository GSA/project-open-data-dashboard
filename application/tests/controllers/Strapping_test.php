<?php

class StrappingTest extends TestCase
{

    public function testStrappedCodeDoesNotGenerateErrors() {

        // Here we're just going to call some key code that otherwise lacks specific tests
        // [INVOKE OTHERWISE UNTESTED CODE HERE AS NEEDED]

        // PHPUnit treats all PHP errors, warnings, and notices generated as exceptions:
        // See https://phpunit.de/manual/6.5/en/writing-tests-for-phpunit.html#writing-tests-for-phpunit.errors)
        // Since we don't assert that those exceptions are thrown, then any errors
        // in the code above will cause this test to fail.

        // If *nothing* above errors, without assertions PHPUnit will be suspicious that
        // this is a "risky" test. So we'll include at least one assertion here to signal that it's expected.
        // See https://stackoverflow.com/questions/28661339/phpunit-how-to-test-if-method-does-nothing/45850982#45850982
        $this->assertNull(NULL);
    }

    /**
     * These are strapping tests that just assert that no PHP errors are encountered on clicks of nav links
     * @dataProvider navLinksProvider
     */
    public function testMainNavReturns200($path)
	{
        $this->request('GET', $path);
        $this->assertResponseCode(200);
    }

    public function navLinksProvider() {
        return [
            'Home' => ['offices/qa'],
            'Agencies' => ['offices'],
            'Validator' => ['validate'],
            'Converters > ExportAPI' => ['export'],
            'Converters > CSV Converter' => ['datagov/csv_to_json'],
            'Converters > Schema Converter' => ['upgrade-schema'],
            'Converters > Data.json merger' => ['merge'],
            'Rubric' => ['docs/rubric'],
            'Help > Docs' => ['docs'],
            'About' => ['docs/about']
        ];

    }

}
