<?php
/**
 * Created by PhpStorm.
 * User: tikey
 * Date: 22.10.18
 * Time: 12:31
 */

use Behat\Behat\Context\Context;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;

class IcalContext implements Context {
	/** @var CommonContext $commonContext */
	private $commonContext;

	public $ical_link;

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
	 * @Given /^I copy the iCal\-Link "([^"]*)"$/
	 * @throws Exception
	 */
	public function iCopyTheICalLink($text) {
		$link = $this->commonContext->getSession()->getPage()->find('css', sprintf('.ical-links a:contains("%s")', $text));

		if(!$link->hasAttribute("href")) {
			throw new Exception("Text '{$text}' is not found in the iCal-Links.");
		}

		$this->commonContext->download_url = $link->getAttribute("href");
	}


	/**
	 * @Given /^I use an old iCal\-Link for "([^"]*)"$/
	 * @param $module
	 * @throws Exception
	 */
	public function iUseAnOldIcalLinkFor($module) {
		$hash = md5(
			$this->commonContext->config['login']['userid'].
			md5($this->commonContext->config['login']['password']).
			$this->commonContext->config['KOOL_ENCRYPTION_KEY']
		);

		if($module == "events") {
			$this->commonContext->download_url = "http://kool.local/ical/?user=" . $hash;
		} else {
			$this->commonContext->download_url = "http://kool.local/resical/?user=" . $hash;
		}
	}

}