<?php
namespace Tags\Test\TestSuite\Fixture;

use Cake\TestSuite\Fixture\FixtureInjector as CakeFixtureInjector;
use PHPUnit\Framework\Test;

/**
 * Test listener used to inject a fixture manager in all tests that
 * are composed inside a Test Suite
 */
class FixtureInjector extends CakeFixtureInjector {
	/**
	 * Unloads fixtures from the test case.
	 *
	 * @param \PHPUnit\Framework\Test $test The test case
	 * @param float $time current time
	 * @return void
	 */
	public function endTest(Test $test, $time) {
		parent::endTest($test, $time);

		$this->_fixtureManager->shutDown();
	}

}
