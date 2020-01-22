<?php
/**
 * Part of ci-phpunit-test
 *
 * @author     Kenji Suzuki <https://github.com/kenjis>
 * @license    MIT License
 * @copyright  2015 Kenji Suzuki
 * @link       https://github.com/kenjis/ci-phpunit-test
 */

class WelcomeTest extends TestCase
{
	public function testGeneratesTheExpectedTitle()
	{
		$output = $this->request('GET', 'welcome/index');
		$this->assertContains('<title>Project Open Data Dashboard</title>', $output);
	}

	public function testGeneratesA404InResponseToRequestsForAMissingMethod()
	{
		$this->request('GET', 'welcome/method_not_exist');
		$this->assertResponseCode(404);
	}

	public function testConfirmsTheApplicationPathIsSetCorrectly()
	{
		$actual = realpath(APPPATH);
		$expected = realpath(__DIR__ . '/../..');
		$this->assertEquals(
			$expected,
			$actual,
			'Your APPPATH seems to be wrong. Check your $application_folder in tests/Bootstrap.php'
		);
	}
}
