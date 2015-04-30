<?php
/**
 * Mailjet Public DATA API / The real-time Cloud Emailing platform
 *
 * Connect your Apps and Make our product yours with our powerful API
 * http://www.mailjet.com/ Mailjet SAS Website
 *
 * @author		David Coullet
 * @author		Mailjet Dev team
 * @copyright	Copyright (c) 2012-2013, Mailjet SAS, http://www.mailjet.com/Terms-of-use.htm
 * @file
 */

// ---------------------------------------------------------------------

/**
 * Mailjet Data Type Enum Class
 *
 * updated on 2013-09-03
 *
 * @class		MailjetDataType
 * @author		David Coullet
 * @author		Mailjet Dev team
 * @version		0.1
 */
class MailjetDataType
{
    const SourceType	= 2;
    const MimeType	= 3;
    const MaxSizeMType	= 10;
    const ResourceType	= 11;
    const AllowedRType	= 9;
    //list_type NNN : a list of resource types for source type NNN.
}


/**
 * Mailjet Public DATA API Main Class
 *
 * This class enables you to connect your Apps and use our powerful API.
 * http://www.mailjet.com/docs/api
 *
 * updated on 2013-09-03
 *
 * @class		MailjetData
 * @author		David Coullet
 * @author		Mailjet Dev team
 * @version		0.1
 */
class mailjetdata
{
    /**
     * Mailjet API Key
     * You can edit directly and add here your Mailjet infos
     *
     * @access	private
     * @var		string $_apiKey
     */
    private $_apiKey = '';

    /**
     * Mailjet API Secret Key to use.
     * You can edit directly and add here your Mailjet infos
     *
     * @access	private
     * @var		string $_secretKey
     */
    private $_secretKey = '';

    /**
     * @todo not available right now for the Data API.
     *
     * Seconds before updating the cache object
     * If set to 0, Object caching will be disabled
     *
     * @access	private
     * @var		integer $_cache
     */
    private $_cache = 0;//600;

// ---------------------------------------------------------------------

   /**
     * Mailjet API Instance
     *
     * @access	private
     * @var		resource $_api
     */
    private $_api = NULL;

   /**
     * DATA Types
     *
     * @access	private
     * @var		array $DataType
     */
    private $DataType = array(
                    /* Available Source Type */
                    MailjetDataType::SourceType		=> NULL,
                    /* Available Mime Type */
                    MailjetDataType::MimeType		=> NULL,
                    /* Max Size Mime Type */
                    MailjetDataType::MaxSizeMType	=> NULL,
                    /* Available Resource Type */
                    MailjetDataType::ResourceType	=> NULL,
                    /* Allowed Resource Type */
                    MailjetDataType::AllowedRType	=> NULL
                    );

    /**
     * API URL
     *
     * @access	private
     * @var		string $_api_url
     */
    //private $_api_url = 'ks381064.kimsufi.com/apitest4/';
    //private $_api_url = 'betapi.mailjet.com/fastapitest/';
    private $_api_url = 'api.mailjet.com/v3/';

    /**
     * API version to use
     *
     * @access	private
     * @var		string $_version
     */
    private $_version = 'DATA';

    /**
     * Debug internal flag
     *
     * @access	private
     * @var		boolean $_debug
     */
    private $_debug = 2;

    /**
     * Debug DATA access
     *
     * @access	private
     * @var		string $_debug_access
     */
    private $_debug_access = '';

    /**
     * Debug buffer copy
     *
     * @access	private
     * @var		string $_buffer
     */
    private $_buffer = '';

    /**
     * Debug method copy
     *
     * @access	private
     * @var		string $_method
     */
    private $_method = '';

    /**
     * Debug by cURL
     *
     * @access	private
     * @var		array $_info
     */
    private $_info = NULL;

    /**
     * cURL handle resource
     *
     * @access	private
     * @var		resource $_curl_handle
     */
    private $_curl_handle = NULL;

    /**
     * Singleton pattern : Current instance
     *
     * @access	private
     * @var		resource $_instance
     */
    private static $_instance = NULL;

    /**
     * Post DATA
     *
     * @access	private
     * @var		array $_post_data
     */
    private $_post_data = NULL;

    /**
     * Constructor
     *
     * Set API Key, Secret Key and Secure mode if provided.
     * Create a new Mailjet API object
     *
     * @access	public
     * @uses	MailjetData::$_apiKey
     * @uses	MailjetData::$_secretKey
     * @uses	MailjetData::$_version
     * @uses	MailjetData::$_api
     * @uses	MailjetData::$_api_url
     * @param string  $apiKey    Mailjet API Key
     * @param string  $secretKey Mailjet API Secret Key
     * @param boolean $secure    TRUE to secure the transaction, FALSE otherwise
     */
    public function __construct($apiKey = NULL, $secretKey = NULL, $secure = TRUE)
    {
        if (isset($apiKey))
            $this->_apiKey = $apiKey;
        if (isset($secretKey))
            $this->_secretKey = $secretKey;
        $this->_api_url = 'http://'.$this->_api_url.$this->_version;
		//$this->_api = new Api($this->_apiKey, $this->_secretKey, $secure);
		$this->_api = new Mailjet_Api($this->_apiKey, $this->_secretKey);
        $this->secure($secure);
    }

    /**
     * Singleton pattern :
     * Get the instance of the object if it already exists
     * or create a new one.
     *
     * @access	public
     * @uses	MailjetData::$_instance
     *
     * @return resource instance
     */
    public static function getInstance()
    {
        if (!(self::$_instance instanceof self))
            self::$_instance = new self();

        return self::$_instance;
    }

    /**
     * Destructor
     *
     * Close the cURL handle resource
     *
     * @access	public
     * @uses	MailjetData::$_curl_handle
     */
    public function __destruct()
    {
        if(!is_null($this->_curl_handle))
            curl_close($this->_curl_handle);
        $this->_curl_handle = NULL;
    }

    /**
     * Update or set consumer keys for the DATA and API
     *
     * @access	public
     * @uses	MailjetData::$_api
     * @uses	MailjetData::$_apiKey
     * @uses	MailjetData::$_secretKey
     * @param string $apiKey    Mailjet API Key
     * @param string $secretKey Mailjet API Secret Key
     */
    public function setKeys($apiKey, $secretKey)
    {
        $this->_apiKey = $apiKey;
        $this->_secretKey = $secretKey;
        $this->_api->setKeys($this->_apiKey, $this->_secretKey);
    }

    /**
     * Set the seconds before updating the cache object
     * If set to 0, Object caching will be disabled
     *
     * @access	public
     * @uses	MailjetData::$_cache
     * @param integer $cache Cache to set in seconds
     */
    public function setCache($cache)
    {
        $this->_cache = $cache;
    }

    /**
     * Get the seconds before updating the cache object
     * If set to 0, Object caching will be disabled
     *
     * @access	public
     * @uses	MailjetData::$_cache
     *
     * @return integer Cache in seconds
     */
    public function getCache()
    {
        return ($this->_cache);
    }

    /**
     * Secure or not the transaction through https for the DATA and API
     *
     * @access	public
     * @uses	MailjetData::$_api
     * @uses	MailjetData::$_api_url
     * @param boolean $secure TRUE to secure the transaction, FALSE otherwise
     */
    public function secure($secure = TRUE)
    {
        $protocol = 'http';
        if ($secure)
            $protocol = 'https';
        $this->_api_url = preg_replace('/http(s)?:\/\//', $protocol.'://', $this->_api_url);
        $this->_api->secure($secure);
    }



    /**
     * GET/Fetch List of token in the LanguageString API Object
     *
     * @access	private
     * @uses	MailjetData::$_api
     *
     * @return array Selected part of the response
     */
    private function fetchType($datatype)
    {
        $response = $this->_api->languagestring(array('ListType' => $datatype, 'limit' => 0));
        $result = array();
        if ($this->_api->getLastHTTPCode() < 400) {
            foreach ($response->Data as $data)
                $result[$data->StringId] = $data->Value;
        }

        return ($result);
    }

    /**
     * GET/Fetch List of all token for the DATA API in the LanguageString API Object.
     * Default cache of 600 seconds (10m)
     *
     * @access	private
     * @uses	MailjetData::$_api
     * @uses	MailjetData::$DataType
     * @uses	MailjetData::fetchType()
     *
     * @return boolean TRUE on success, FALSE otherwise
     */
    private function fetchAllTypes()
    {
        if (is_null($this->_api))
            return (FALSE);
        $default_cache = $this->_api->getCache();
        $this->_api->setCache(600);
        foreach ($this->DataType as $key => $value)
            if (is_null($value) || empty($value))
                $this->DataType[$key] = $this->fetchType($key);
        $this->_api->setCache($default_cache);

        return (TRUE);
    }

    /**
     * Retrieve a list of all Mime types available
     *
     * @access	public
     * @uses	MailjetDataType::MimeType
     * @uses	MailjetData::$DataType
     * @uses	MailjetData::fetchAllTypes()
     *
     * @return mixed Array of result or FALSE on error
     */
    public function getMimeType()
    {
        if ($this->fetchAllTypes())
            return ($this->DataType[MailjetDataType::MimeType]);

        return (FALSE);
    }

    /**
     * Public function to retrieve Raw Data from File
     *
     * @access	public
     *
     * @return mixed Raw Data or NULL on error
     * @todo curl_file ? Warning no boundaries accepted
     */
    public function getRawFile($File)
    {
        if (!is_null($File) && ($File = realpath($File)) !== FALSE)
            return (Tools::file_get_contents($File));
            //return (curl_file_create($File));
            //$curl_file = new CURLFile($File);
            //return (array('data' => '@'.$File));
        return (NULL);
    }


    /**
     * Short function to check and build the full request Url
     *
     * @access	public
     * @param array $request List of arguments
     *
     * @return mixed JSON string or Data content
     */
    public function sDATA($request)
    {
        $args = array('Method', 'SourceType', 'SourceID', 'ResourceType', 'MimeType', 'ID', 'RawData', 'akid');
        foreach ($args as $arg)
            if (!isset($request[$arg]))
                $request[$arg] = NULL;
        if (!isset($request['Debug']))
            $request['Debug'] = FALSE;

        return ($this->DATA($request['Method'], $request['SourceType'], $request['SourceID'],
                        $request['ResourceType'], $request['MimeType'], $request['ID'],
                        $request['RawData'], $request['akid'], $request['Debug']));
    }

    /**
     * Check and build the full request Url
     *
     * @access	public
     * @uses	MailjetData::$_debug
     * @uses	MailjetData::$_debug_access
     * @uses	MailjetData::fetchAllTypes()
     * @uses	MailjetData::$DataType
     * @uses	MailjetDataType::SourceType
     * @uses	MailjetDataType::ResourceType
     * @uses	MailjetDataType::MimeType
     * @param string $Method POST:	Create a resource
     * 						GET:	Read one resource
     * 						PUT:	Update one resource
     * 						DELETE:	Delete one resource
     * @param string $SourceType   SourceType
     * @param string $SourceID     SourceID
     * @param string $ResourceType ResourceType
     * @param string $MimeType     MimeType
     * @param string $ID           ID
     * @param string $RawData      RawData
     * @param string $akid         ApiKey ID
     * @param string $Debug        Debug flag
     *
     * @return mixed JSON string or Data content
     * @todo Add MailjetDataType::AllowedRType verification
     */
    public function DATA($Method, $SourceType, $SourceID, $ResourceType, $MimeType,
                            $ID = NULL, $RawData = NULL, $akid = NULL, $Debug = FALSE)
    {
        $this->_debug_access = '';
        if (! $this->fetchAllTypes())
            $this->_debug_access .= "Error with API";

        $this->_debug = $Debug;

        $Method = strtoupper($Method);
        if (!in_array($Method, array('GET', 'POST', 'PUT', 'DELETE', 'JSON')))
            $Method = 'GET';

        $uri = '';
        $args = array($SourceType, $SourceID, $ResourceType, $MimeType);
        $uriAccess = array(MailjetDataType::SourceType, NULL, MailjetDataType::ResourceType, MailjetDataType::MimeType);
        $uriAccess_len = count($uriAccess);
        for ($index = 0; $index < $uriAccess_len; $index++) {
            if (isset($args[$index]))
                if (!is_null($uriAccess[$index]) && !in_array($args[$index], $this->DataType[$uriAccess[$index]])) {
                    $this->_debug_access .= " Parameter $index is not valid. Expected :";
                    $count = 0;
                    foreach ($this->DataType[$uriAccess[$index]] as $k => $v) {
                        if ($k != 0)
                            $this->_debug_access .= " $v ,";
                        if ($count > 10) {
                            $this->_debug_access .= '..,';
                            break;
                        }
                        $count++;
                    }
                    $this->_debug_access = preg_replace('/,$/', '.', $this->_debug_access, 1);
                    if ($uriAccess[$index] == MailjetDataType::MimeType)
                        $this->_debug_access .= ' You can use "getMimeType()" to retrieve the complete list of Mime-Types available.';
                } elseif (is_null($uriAccess[$index]))
                    $args[$index] = intval($args[$index]);
                if (!is_null($args[$index]) && !empty($args[$index]))
                    $uri .= '/'.$args[$index];
        }
        if (!is_null($ID))
            $uri .= '/'.$ID;
        if (!is_null($akid))
            $uri .= '?akid='.intval($akid);
        if (in_array(strtoupper($MimeType), array('MULTIPART/FORM-DATA', 'APPLICATION/X-WWW-FORM-URLENCODED')))
            $RawData = array('data' => $RawData);
            
        return ($this->sendRequest($Method, $uri, $MimeType, $RawData));
    }

    /**
     * Send Request
     *
     * Send the request to the Mailjet DATA API server and get back the result
     * Basically, setup and execute the curl process
     *
     * @access	private
     * @uses	MailjetData::$_info
     * @uses	MailjetData::$_apiKey
     * @uses	MailjetData::$_secretKey
     * @uses	MailjetData::$_api_url
     * @uses	MailjetData::$_curl_handle
     * @uses	MailjetData::$_buffer
     * @uses	MailjetData::$_method
     * @uses	MailjetData::$_debug
     *
     * @return string the result of the request
     * @todo CURLOPT_BINARYTRANSFER ?
     */
    private function sendRequest($Method, $Uri, $MimeType, $RawData)
    {
        if(is_null($this->_curl_handle))
            $this->_curl_handle = curl_init();

        curl_setopt($this->_curl_handle, CURLOPT_URL, $this->_api_url.$Uri);
        curl_setopt($this->_curl_handle, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($this->_curl_handle, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($this->_curl_handle, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($this->_curl_handle, CURLOPT_USERPWD, $this->_apiKey.':'.$this->_secretKey);
        curl_setopt($this->_curl_handle, CURLOPT_HTTPHEADER, array("Content-Type: ".$MimeType));
        curl_setopt($this->_curl_handle, CURLOPT_CUSTOMREQUEST, $Method);
        curl_setopt($this->_curl_handle, CURLOPT_USERAGENT, 'prestashop-3.0');
    	
        switch ($Method) {
            case 'GET' :
                curl_setopt($this->_curl_handle, CURLOPT_HTTPGET, TRUE);
                curl_setopt($this->_curl_handle, CURLOPT_POSTFIELDS, NULL);
            break;

            case 'POST':
                curl_setopt($this->_curl_handle, CURLOPT_POST, TRUE);
                curl_setopt($this->_curl_handle, CURLOPT_POSTFIELDS, $RawData);
            break;

            case 'PUT':
                curl_setopt($this->_curl_handle, CURLOPT_POSTFIELDS, $RawData);
            break;            
            
            case 'JSON':
            	$RawData = Tools::jsonEncode($RawData);
            	curl_setopt($this->_curl_handle, CURLOPT_CUSTOMREQUEST, "POST");
				curl_setopt($this->_curl_handle, CURLOPT_POSTFIELDS, $RawData);
				curl_setopt($this->_curl_handle, CURLOPT_RETURNTRANSFER, TRUE);
				curl_setopt($this->_curl_handle, CURLOPT_HTTPHEADER, array(
				    'Content-Type: application/json',
				    'Content-Length: ' . strlen($RawData))
				);
            break;
        }
        
        $buffer = curl_exec($this->_curl_handle);

        $this->_info = curl_getinfo($this->_curl_handle);

        $this->_buffer = $buffer;
        $this->_method = $Method;
        $this->_debug = FALSE;
        
        // If an error is encountered, return an array
        if($this->_info['http_code'] >= 300)
        	$buffer = Tools::jsonDecode($buffer);
        	
        return $buffer;
    }

    /**
     * Get the last HTTP code retrieved by cURL
     *
     * Warning : Information returned by this function is kept.
     * So, if you call it again, the previous info is returned.
     *
     * @access	public
     * @uses	MailjetData::$_info
     *
     * @return integer last HTTP code retrieved by cURL or 0 if not set
     */
    public function getLastHTTPCode()
    {
        if (isset($this->_info['http_code']))
            return ($this->_info['http_code']);

        return (0);
    }

    /**
     * Get some info for debugging purpose
     *
     * Warning : Information returned by this function is kept.
     * So, if you call it again, the previous info is returned.
     *
     * @access	public
     * @uses	MailjetData::$_info
     * @uses	MailjetData::$_method
     * @uses	MailjetData::$_buffer
     * @uses	MailjetData::$_debug_access
     *
     * @return array with some debug info
     */
    public function getDebugInfo()
    {
        $status_code = array (
            200 => 'OK - Everything went fine.',
            201 => 'OK - Created : The POST request was successfully executed.',
            204 => 'OK - No Content : The Delete request was successful.',
            304 => 'OK - Not Modified : The PUT request didn’t affect any record.',
            400 => 'KO - Bad Request : Please check the parameters.',
            401 => 'KO - Unauthorized : A problem occurred with the apiKey/secretKey. You may be not authorized to access the API or your apiKey may have expired.',
            403 => 'KO - Forbidden : You are not authorized to call that function.',
            404 => 'KO - Not Found : The resource with the specified ID does not exist.',
            405 => 'KO - Method not allowed : Attempt to put/post multiple resources in 1 request.',
            500 => 'KO - Internal Server Error.',
            503 => 'KO - Service unavailable.'
        );

        if (array_key_exists($this->_info['http_code'], $status_code))
            $http_code_text = $status_code[$this->_info['http_code']];
        else
            $http_code_text = 'KO - Service unavailable.';

        $status_message = '';
        if ($this->_info['http_code'] >= 400) {
            $buffer = Tools::jsonDecode($this->_buffer);
            if (!is_null($buffer) && isset($buffer->StatusCode) && isset($buffer->ErrorMessage)) {
                $status_message = $buffer->StatusCode.' - '.$buffer->ErrorMessage;
                if (isset($buffer->ErrorInfo) && !empty($buffer->ErrorInfo))
                    $status_message .= ' ('.$buffer->ErrorInfo.')';
                }
        }

        $res = array(
            'method'					=> $this->_method,
            'url'						=> $this->_info['url'],
            'duration'					=> $this->_info['total_time'] - $this->_info['pretransfer_time'],
            'http_code'					=> $this->_info['http_code'],
            'http_code_text'			=> $http_code_text,
            'status_message'			=> $status_message,
            'uri_error'					=> $this->_debug_access,
            'content_type'				=> $this->_info['content_type'],
            'download_content_length'	=> $this->_info['download_content_length'],
            'upload_content_length'		=> $this->_info['upload_content_length'],
            'buffer'					=> $this->_buffer
        );

        return $res;
    }

}
