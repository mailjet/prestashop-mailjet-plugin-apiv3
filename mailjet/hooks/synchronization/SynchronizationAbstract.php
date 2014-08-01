<?php 


namespace Hooks\Synchronization;

include_once(dirname(__FILE__).'/Exception.php');

use Mailjet\ApiOverlay as ApiOverlay;


/**
 * 
 * @author atanas
 *
 */
abstract class SynchronizationAbstract
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
	 * @param ApiOverlay $apiOverlay
	 */
	public function __construct(ApiOverlay $apiOverlay)
	{
		$this->_apiOverlay = $apiOverlay;
	}
	
	/**
	 * 
	 * @return ApiOverlay
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
		if (!\Db::getInstance()) {
			throw new Exception('Db instance is not provided.');
		}
		
		return \Db::getInstance();
	}
	
	/**
	 *
	 * @return number|boolean
	 */
	protected function _getAlreadyCteatedMasterListId()
	{
		if (!$this->_masterListId) {
			$lists = $this->_getApiOverlay()->getContactsLists();
		
			if ($lists !== false) {
				foreach ($lists as $list) {
					if ($list->Name === self::LIST_NAME) {
						$this->_masterListId = (int)$list->ID;
					}
				}
			}
		}
	
		return $this->_masterListId;
	}
	
}

?>