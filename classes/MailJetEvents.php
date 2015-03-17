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

class MailJetEvents extends ObjectModel
{
	public $params = array();
	public $post_vars = array();

	protected $table = 'mj_events';

	protected $identifier = 'id_mj_events';

	protected $fieldsRequired = array('event', 'time');

	private $default_scheme = array();

	const DEFAULT_EVENT = 'open';

	const ALL_EVENTS_KEYS = 'keys_list';

	const LIMIT_EVENT = 10;

	private $limit_event;

	public $current_page;

	public static $definition = array(
		'table' => 'mj_events',
		'primary' => 'id_mj_events',
		'multilang' => false,
		'multilang_shop' => false);

	/**
	 * Set default value to be able to use the install / uninstall method
	 *
	 * @param string $event
	 * @param bool $time
	 */
	public function __construct($event = MailJetEvents::DEFAULT_EVENT, $post_vars = array(), $time = false, $id_events = false)
	{
		if (!$time) $time = time();

		$this->post_vars = $post_vars;
		$this->params['event'] = array('value' => $event, 'type' => 'string');
		$this->params['time'] = array('value' => $time, 'type' => 'int');

		$this->initScheme();

		$this->setLimit(MailJetEvents::LIMIT_EVENT);
		$this->setPage(1);

		// Get data from Database if id exist
		parent::__construct($id_events);

	}

	/**
	 * Get a requested scheme
	 *
	 * @param $name
	 * @return array
	 */
	public function getScheme($name)
	{
		// Already loaded
		if ($name == MailJetEvents::ALL_EVENTS_KEYS && count($this->default_scheme))
			return $this->default_scheme;

		$file = dirname(__FILE__).'/../xml/events.xml';
		$scheme = array();
		//...$log = 'Scheme not found';

		if (file_exists($file) && ($xml = simplexml_load_file($file)))
		{
			foreach ($xml->event as $event)
				if ((string)$event['name'] == $name)
				{
					// Will set GET / POST Data if exist
					foreach ($event->key as $key)
						if (isset($this->post_vars[(string)$key])) // **
							$scheme[(string)$key] = array('value' => $this->post_vars[(string)$key], 'type' => (string)$key['type']);
					//...$log = 'Scheme found';
				}
		}

		// Not used for ajax query, then no need to log it
		/*if ($name != MailJetEvents::ALL_EVENTS_KEYS)
			MailJetLog::write(MailJetLog::$file, $log.': '.$name);*/
		return $scheme;
	}

	/**
	 * Will load the xml scheme database event requested with the default one
	 */
	public function initScheme()
	{
		$this->params += $this->getScheme($this->params['event']['value']);
		if ($this->params['event']['value'] == MailJetEvents::ALL_EVENTS_KEYS) $this->default_scheme = $this->params;
		else $this->default_scheme = $this->getScheme(MailJetEvents::ALL_EVENTS_KEYS);

		$translations = MailJetTranslate::getTranslationsByName('events');
		foreach ($translations as $key => $value)
		{
			if (isset($this->params[$key]))
				$this->params[$key]['title'] = $value;
			if (isset($this->default_scheme[$key]))
				$this->default_scheme[$key]['title'] = $value;
		}
	}

	/**
	 * Fetch event list depending of the one set (filter could be used)
	 *
	 * @return mixed
	 */
	public function fetch($default = false, $filters = array())
	{
		$select = array_keys($this->getFieldsName($default));

		if (($key = array_search('agent', $select)) !== false) unset($select[$key]);
		if (($key = array_search('ip', $select)) !== false) unset($select[$key]);
		if (($key = array_search('geo', $select)) !== false) unset($select[$key]);
		if (($key = array_search('original_address', $select)) !== false) unset($select[$key]);
		if (($key = array_search('new_address', $select)) !== false) unset($select[$key]);

		$query = 'SELECT `'.$this->identifier.'`, `'.implode('`,`', array_map('bqSQL',$select)).'` FROM `'._DB_PREFIX_.$this->table.'` e ';
		if ($this->params['event']['value'] && $this->params['event']['value'] != MailJetEvents::ALL_EVENTS_KEYS)
			$query .= 'WHERE e.`event` = "'.pSQL($this->params['event']['value']).'"';

		if (isset($filters['limit']))
			$this->setLimit($filters['limit']);

		if (isset($filters['page']))
			$this->setPage($filters['page']);

		$query .= ' ORDER BY e.time DESC ';

		$limit_start = ($this->current_page == 1) ? 0 : $this->limit_event * ($this->current_page - 1);
		if ($limit_start < 0) $limit_start = 0;
		$query .= ' limit '.(int)$limit_start.', '.(int)$this->limit_event;

		return DB::getInstance()->executeS($query);
	}

	/**
	 * Return total element for the event type
	 *
	 * @return int
	 */
	public function getTotal()
	{
		$query = 'SELECT e.`id_mj_events` FROM `'._DB_PREFIX_.$this->table.'` e ';
		if ($this->params['event']['value'] && $this->params['event']['value'] != MailJetEvents::ALL_EVENTS_KEYS)
			$query .= 'WHERE e.`event` = "'.pSQL($this->params['event']['value']).'"';
		return count(DB::getInstance()->executeS($query));
	}

	/**
	 * Set the limit for any fetch
	 *
	 * @param $limit
	 */
	public function setLimit($limit)
	{
		$limit = (int)$limit;
		if ($limit > 0)
			$this->limit_event = $limit;
	}

	/**
	 * Set the limit for any fetch
	 *
	 * @param $limit
	 */
	public function setPage($page)
	{
		$page = (int)$page;
		if ($page > 0 && $page <= $this->getTotalPages())
			$this->current_page = $page;
	}

	/**
	 * Return the maximum pages
	 *
	 * @return float
	 */
	public function getTotalPages()
	{
		return ceil($this->getTotal() / $this->limit_event);
	}

	/**
	 * Get fields depending of the scheme loaded
	 *
	 * @return array
	 */
	public function getFields()
	{
		$fields = array();

		foreach ($this->params as $key => $content)
		{
			switch ($content['type'])
			{
				case 'string':
					$content['value'] = pSQL($content['value']);
				break;
				case 'int':
					$content['value'] = (int)$content['value'];
				break;
				default:
					$content['value'] = pSQL($content['value']);
			}
			$fields[$key] = $content['value'];
		}

		if ($this->id)
			$fields['id_mj_events'] = (int)$this->id;

		return $fields;
	}

	public function getFieldsName($default = false)
	{
		$fields = array();

		foreach (($default ? $this->default_scheme : $this->params) as $key => $case)
			$fields[$key] = $case['title'];
		return $fields;
	}
}