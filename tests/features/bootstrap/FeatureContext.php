<?php

use Behat\Behat\Context\Context;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;

/**
 * Features context.
 */
class FeatureContext implements Context
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

	/**
 	 * @param BeforeScenarioScope $scope
	 *
	 * @beforeScenario
     */
    public function alterMinkParameters(BeforeScenarioScope $scope) {
        $this->commonContext->getSession()->resizeWindow(1440, 900);
    }

    /**
     * Click on the element with the provided CSS Selector
     *
     * @When /^I click on the element with css selector "([^"]*)"$/
	 * @throws Exception
     */
    public function iClickOnTheElementWithCSSSelector($cssSelector) {
        $page = $this->commonContext->getSession()->getPage();
        $element = $page->find('css', $cssSelector);

        if (empty($element)) {
            throw new Exception("No html element found for the selector ('$cssSelector')");
        }

        $element->click();

    }

	/**
	 * Click on the element with the provided xpath query
	 *
	 * @When /^I click on the element with xpath "([^"]*)"$/
	 */
	public function iClickOnTheElementWithXPath($xpath)
	{
		$session = $this->commonContext->getSession();
		$element = $session->getPage()->find(
			'xpath',
			$session->getSelectorsHandler()->selectorToXpath('xpath', $xpath)
		);

		if (null === $element) {
			throw new \InvalidArgumentException(sprintf('Could not evaluate XPath: "%s"', $xpath));
		}

		$element->click();
	}

	/**
	 * Click on the element with $text
	 *
	 * @When /^I click on "([^"]*)"$/
	 * @param $text
	 * @throws Exception
	 */
	public function iClickOn($text) {
		$session = $this->commonContext->getSession();
		$element = $session->getPage()->find('css', sprintf('button:contains("%s")', $text));
		if (null === $element) {
			throw new \InvalidArgumentException(sprintf('Cannot find text: "%s"', $text));
		}

		$element->click();

	}

	/**
     * @When /^(?:|I )hover over "([^"]*)"$/
     */
    public function iHoverOver($locator)
    {
        $session = $this->commonContext->getSession();
        $element = $session->getPage()->find('css', $locator);

        if (null === $element) {
            throw new \InvalidArgumentException(sprintf('Could not evaluate CSS selector: "%s"', $locator));
        }

        $element->mouseOver();
    }

    /**
     * Wait for specified miliseconds
     *
     * @When /^I wait "([^"]*)" Miliseconds$/
     */
    public function iWait($miliseconds) {
		$this->commonContext->getSession()->wait($miliseconds);
    }


    /**
     * @Given /^(?:|I )log in as "([^"]*)" with "([^"]*)"/
     */
    public function iLogInAs($user, $password)
    {
		$this->commonContext->visit('/');
        $this->iClickOnTheElementWithCSSSelector("#btn__login");
		$this->commonContext->fillField('username', $user);
		$this->commonContext->fillField('password', $password);
        $this->iClickOnTheElementWithCSSSelector("#login-menu button");
    }

    /**
     * @Given /^(?:|I )am logged in as root/
     */
    public function iAmLoggedInAsRoot() {
        $this->iLogInAs($this->commonContext->config['login']['username'], $this->commonContext->config['login']['password']);
    }


	/**
	 * @Then /^I should be able to download chart.png$/
	 *
	 * @throws \Exception
	 */
	public function assertFileDownloaded() {
		$page = $this->commonContext->getSession()->getPage();
		$element = $page->find('css', "#leute-stats-statistics-line > a.btn.btn-default.absolute-br-btn.discrete-btn.google-charts-download-btn");

		if ($element->getAttribute("href") === null) {
			throw new Exception("No download button with data for chart.png found");
		}
	}

}