<?php
/**
 * 2007-2015 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2015 PrestaShop SA
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 * International Registered Trademark & Property of PrestaShop SA
*/

class MailJetPages
{
	/* Used to define the GET/POST value/key to send or get the page type */
	const REQUEST_PAGE_TYPE = 'MJ_request_page';
	const REQUIRE_PAGE = 1;
	const ALL_PAGES = 0;

	public $default_page = 'SETUP_LANDING';

	/**
	 * Available page list (load from xml)
	 * @var array
	 */
	public $available_page = array();

	/**
	 * Page require an user authentication to the mailjet service
	 * (load from xml)
	 * @var array
	 */
	public $require_authentication_pages = array();

	public $current_authentication;

	/**
	 * Construct needed to get the page translation for require pages
	 */
	public function __construct($current_authentication)
	{
		$this->current_authentication = $current_authentication;
		$this->initTemplatesAccess();
	}

	public function initPagesTranslation()
	{
		$translations = MailJetTranslate::getTranslationsByName('pages');
		foreach ($translations as $key => $value)
			if (isset($this->available_page[$key]))
				$this->available_page[$key] = $value;
		return (bool)count($translations);
	}

	/**
	 * Init the array for access page template
	 *
	 * @return bool
	 */
	public function initTemplatesAccess()
	{
		$file = dirname(__FILE__).'/../xml/template.xml';
		if (file_exists($file) && ($xml = simplexml_load_file($file)))
		{
			$this->default_page = ($this->current_authentication ?
				(string)$xml->tabs->tab->default_page['name'] :
				(string)$xml->pages->default_page['name']);

			// Get simple pages, by default set the name key as a translation to any avoid empty string
			foreach ($xml->pages->page as $page)
				$this->available_page[(string)$page['name']] = (string)$page['name'];

			// Get require authentication pages (merged to the available ones)
			foreach ($xml->tabs->tab->page as $page)
			{
				$this->require_authentication_pages[] = (string)$page['name'];
				$this->available_page[(string)$page['name']] = '';
			}

			// Get translation from xml
			$ret = count($this->available_page) && $this->initPagesTranslation();
			return (bool)$ret;
		}
		return false;
	}

	/**
	 * Extract the require authentication pages with translation
	 *
	 * @return array
	 */
	private function extractAuthenticationPages()
	{
		$pages = array();

		foreach ($this->require_authentication_pages as $name)
			if (isset($this->available_page[$name]))
				$pages[$name] = $this->available_page[$name];
		return $pages;
	}


	/**
	 * Get current Page
	 *
	 * @param $current_authentication
	 * @return int|mixed
	 */
	public function getCurrentPageName()/* $account_status) */
	{
		$page_type = (($page_type = Tools::getValue(MailJetPages::REQUEST_PAGE_TYPE)) && $this->isAvailablePage($page_type))
			? $page_type : $this->default_page;

		return Tools::strtoupper($page_type);
	}

	/**
	 * Get the pages list depending of the require var.
	 *
	 * @return array
	 */
	public function getPages($require_page = MailJetPages::ALL_PAGES)
	{
		return $require_page == MailJetPages::ALL_PAGES ? $this->available_page : $this->extractAuthenticationPages();
	}

	public function getTemplateTabName($name)
	{
		return (Tools::strtolower(($this->isAvailablePage($name)) ? $name : $this->default_page));
	}

	/**
	 * Return the template name (btw, check if the page exist)
	 *
	 * @param $name
	 * @return mixed
	 */
	public function getTemplateName($name)
	{
		$template_name = Tools::strtolower(($this->isAvailablePage($name)) ? $name : $this->default_page);
		return $this->isRequireAuthenticationPage($name) ? 'tab' : $template_name;
	}

	/**
	 * Check if the page is available into the list
	 *
	 * @param $num
	 * @return bool
	 */
	public function isAvailablePage($name)
	{
		return (bool)array_key_exists($name, $this->available_page);
	}

	/**
	 * Check if a page require that the user is logged
	 *
	 * @param $page
	 * @return bool
	 */
	public function isRequireAuthenticationPage($page)
	{
		return in_array($page, $this->require_authentication_pages);
	}

	/**
	 * Get the require page list
	 *
	 * @return array
	 */
	public function getRequireAuthenticationPages()
	{
		return $this->require_authentication_pages;
	}
}