<?php

use Behat\Behat\Context\Context;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;

/**
 * Features context.
 */
class TaxonomyContext implements Context
{
	/** @var CommonContext $commonContext */
	private $commonContext;

	/**
	 * @param BeforeScenarioScope $scope
	 *
	 * @BeforeScenario
	 */
	public function gatherContexts(BeforeScenarioScope $scope)
	{
		$environment = $scope->getEnvironment();
		$this->commonContext = $environment->getContext(CommonContext::class);
	}

	/** @AfterScenario */
	public function cleanup($event) {


	}

	/**
     * @Then add :arg1 to taxonomy field
	 */
	public function addTermToTaxonomyField($term) {
		$session = $this->commonContext->getSession();
		$locator = "//input[@data-fieldid='taxonomy_input']";
		$dynamicfield = $session->getPage()->find("xpath", $locator);

		try {
			$session->getDriver()->executeScript("$(\"input[data-fieldid='taxonomy_input']\").val('" . $term . "')");
		}
		catch (\Behat\Mink\Exception\UnsupportedDriverActionException $e) {
			throw new \InvalidArgumentException("Cannot execute Jquery");
		}
		catch (\Behat\Mink\Exception\DriverException $e) {
			throw new \InvalidArgumentException("Cannot execute Jquery");
		}

		$dynamicfield->focus();
		sleep(1);
		$session->getPage()->find("css", "ul.typeahead li.active")->click();
	}

}