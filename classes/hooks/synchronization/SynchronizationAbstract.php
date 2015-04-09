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

include_once(dirname(__FILE__).'/Exception.php');

/**
 * 
 * @author atanas
 *
 */
abstract class HooksSynchronizationSynchronizationAbstract
{

	/**
	 *
	 * @var string
	 */
	const LIST_NAME = 'PrestaShop Customers Master List';

	/**
	 * 
	 * @var int
	 */
	protected $_masterListId;

	/**
	 * 
	 * @var ApiOverlay
	 */
	protected $_apiOverlay;

	/**
	 * 
	 * @param Mailjet_ApiOverlay $apiOverlay
	 */
	public function __construct(Mailjet_ApiOverlay $apiOverlay)
	{
		$this->_apiOverlay = $apiOverlay;
	}

	/**
	 * 
	 * @return Mailjet_ApiOverlay
	 */
	protected function _getApiOverlay()
	{
		return $this->_apiOverlay;
	}

	/**
	 * 
	 * @return Api
	 */
	protected function _getApi()
	{
		return $this->_getApiOverlay()->getApi();
	}

	/**
	 * 
	 * @throws Exception
	 * @return Db
	 */
	public function getDbInstance()
	{
		if (!Db::getInstance())
			throw new Exception('Db instance is not provided.');

		return Db::getInstance();
	}

	/**
	 *
	 * @return number|boolean
	 */
	protected function _getAlreadyCreatedMasterListId()
	{
		if (!$this->_masterListId)
		{
			$lists = $this->_getApiOverlay()->getContactsLists();

			if ($lists !== false)
			{
				foreach ($lists as $list)
				{
					if ($list->Name === self::LIST_NAME)
						$this->_masterListId = (int)$list->ID;
				}
			}
		}
		return $this->_masterListId;
	}

}

?>