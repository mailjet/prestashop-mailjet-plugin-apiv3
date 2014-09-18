<?php 

namespace Hooks;


/**
 * 
 * @author atanas
 */
class Events 
{
	

	/**
	 * 
	 * @param array $event
	 */
	public function unsubscribe(array $event)
	{
		if (!array_key_exists('email', $event)) {
			return false;
		}
		
		if (!$event['email']) {
			return false;
		}
		
		$customerClass = new \Customer();
		$customer = $customerClass->getByEmail($event['email']);
		
		if ($customer) {
			$customer->newsletter = 0;
			$customer->update();
		}
	}
	
}


?>