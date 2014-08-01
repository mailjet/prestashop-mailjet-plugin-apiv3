<?php
/**
 * Mailjet Public API / The real-time Cloud Emailing platform
 *
 * Connect your Apps and Make our product yours with our powerful API
 *
 * @author		Mailjet Dev team
 * @copyright	Copyright (c) 2012-2013, Mailjet SAS
 * @license		http://www.mailjet.com/Terms-of-use.htm
 * @link		http://www.mailjet.com/ Mailjet SAS Website
 * @filesource
 */

// ---------------------------------------------------------------------

namespace Mailjet;

/**
 * Mailjet Public API Main Class
 *
 * This class enables you to connect your Apps and use our powerful API.
 *
 * updated on 2013-06-13
 *
 * @package		Mailjet Public API
 * @author		Mailjet Dev team
 * @link		http://www.mailjet.com/docs/api
 * @version		0.2
 */
class Api
{
    /**
     * Mailjet API Key
     * You can edit directly and add here your Mailjet infos
     *
     * @access	private
     * @var		string
     */
    private $_apiKey = '';

    /**
     * Mailjet API Secret Key
     * You can edit directly and add here your Mailjet infos
     *
     * @access	private
     * @var		string
     */
    private $_secretKey = '';

    /**
     * Secure flag to connect through https protocol
     * You can edit directly
     *
     * @access	private
     * @var		boolean
     */
    private $_secure = TRUE;

    /**
     * Debug flag :
     * 0 none / 1 errors only / 2 all
     * You can edit directly
     *
     * @access	private
     * @var		integer
     */
    private $_debug = 1;

    /**
     * Echo debug ?
     * If not, you can read and display the html error code block
     * by access the public string $debugErrorHtml
     * You can edit directly
     *
     * @access	private
     * @var		boolean
     */
    private $_debugEcho = TRUE;

// ---------------------------------------------------------------------

    /**
     * Debug html error
     *
     * @access	public
     * @var		string
     */
    public $debugErrorHtml = '';

    /**
     * API version to use.
     *
     * @access	private
     * @var		string
     */
    private $_version = '0.1';

    /**
     * Output mode :
     * php, json, xml, serialize, html, csv
     *
     * @access	private
     * @var		string
     */
    private $_output = 'json';

    /**
     * API URL.
     *
     * @access	private
     * @var		string
     */
    private $_apiUrl = '';

    /**
     * cURL handle resource
     *
     * @access	private
     * @var		resource
     */
    private $_curl_handle = NULL;

    /**
     * Singleton pattern : Current instance
     *
     * @access	private
     * @var		resource
     */
    private static $_instance = NULL;

    /**
     * Response of the API
     *
     * @access	private
     * @var		mixed
     */
    private $_response = NULL;

    /**
     * Response code of the API
     *
     * @access	private
     * @var		integer
     */
    private $_response_code = 0;

    /**
     * Boolean FALSE or Array of POST args
     *
     * @access	private
     * @var		mixed
     */
    private $_request_post = FALSE;

    /**
     * Full Call URL for debugging purpose
     *
     * @access	private
     * @var		string
     */
    private $_debugCallUrl = '';

    /**
     * Method for debugging purpose
     *
     * @access	private
     * @var		string
     */
    private $_debugMethod = '';

    /**
     * Request for debugging purpose
     *
     * @access	private
     * @var		string
     */
    private $_debugRequest = '';


    /**
     * Constructor
     *
     * Set $_apiKey and $_secretKey if provided & Update $_apiUrl with protocol
     *
     * @access	public
     * @uses	Mailjet::Api::$_apiKey
     * @uses	Mailjet::Api::$_secretKey
     * @uses	Mailjet::Api::$_version
     * @param string $apiKey    Mailjet API Key
     * @param string $secretKey Mailjet API Secret Key
     */
    public function __construct($apiKey = FALSE, $secretKey = FALSE)
    {
        if ( $apiKey )		$this->_apiKey = $apiKey;
        if ( $secretKey )	$this->_secretKey = $secretKey;
        $this->_apiUrl = (($this->_secure) ? 'https' : 'http').'://api.mailjet.com/'.$this->_version;
    }

    /**
     * Singleton pattern :
     * Get the instance of the object if it already exists
     * or create a new one.
     *
     * @access	public
     * @uses	Mailjet::Api::$_instance
     *
     * @return instance
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
     * @uses	Mailjet::Api::$_curl_handle
     */
    public function __destruct()
    {
        if(!is_null($this->_curl_handle))
            curl_close($this->_curl_handle);
        $this->_curl_handle = NULL;
    }

    /**
     * Update or set consumer keys
     *
     * @access	public
     * @uses	Mailjet::Api::$_apiKey
     * @uses	Mailjet::Api::$_secretKey
     * @param string $apiKey    Mailjet API Key
     * @param string $secretKey Mailjet API Secret Key
     */
    public function setKeys($apiKey, $secretKey)
    {
        $this->_apiKey = $apiKey;
        $this->_secretKey = $secretKey;
    }

    /**
     * Get the API Key
     *
     * @access	public
     * @uses	Mailjet::Api::$_apiKey
     * @return string Api Key
     */
    public function getAPIKey()
    {
        return $this->_apiKey;
    }

    /**
     * Secure or not the transaction through https
     *
     * @access	public
     * @uses	Mailjet::Api::$_apiUrl
     * @param boolean $secure TRUE to secure the transaction, FALSE otherwise
     */
    public function secure($secure = TRUE)
    {
        $this->_secure = $secure;
        $protocol = 'http';
        if ($secure)
            $protocol = 'https';
        $this->_apiUrl = preg_replace('/http(s)?:\/\//', $protocol.'://', $this->_apiUrl);
    }

    /**
     * Make the magic call ;)
     *
     * Check for arguments and order them before sending the request.
     *
     * @access	public
     * @uses	Mailjet::Api::$_debug
     * @uses	Mailjet::Api::debug() to display the debug output
     * @uses	Mailjet::Api::sendRequest() to send the request
     * @param string $method Method to call
     * @param array  $args   Array of parameters
     *
     * @return mixed array with the status of the response
     * and the result of the request OR FALSE on failure.
     */
    public function __call($method, $args)
    {
        $params = (sizeof($args) > 0) ? $args[0] : array();
        $request = isset($params["method"]) ? strtoupper($params["method"]) : 'GET';
		if (isset($params["method"])) unset($params["method"]);
        $result = $this->sendRequest($method,$params,$request);
        $return = ($result === TRUE) ? $this->_response : FALSE;
        if ( $this->_debug == 2 || ( $this->_debug == 1 && $return == FALSE ) )
            $this->debug();

        return $return;
    }

    /**
     * Build the full Url for the request
     *
     * @access	private
     * @uses	Mailjet::Api::$_apiUrl
     * @uses	Mailjet::Api::$_debugCallUrl
     * @param string $method  Method to call
     * @param array  $params  Additional parameters for the request
     * @param string $request Request method
     *
     * @return string Full built Url for the request
     */
    private function requestUrlBuilder($method, $params, $request)
    {
        $query_string = array('output' => 'output='.$this->_output);
        foreach ($params as $key => $value) {
            if ($request == "GET" || in_array($key, array('apikey','output')))
                $query_string[$key] = $key.'='.urlencode($value);
            if ($key == "output")
                $this->_output = $value;
        }
        $this->_debugCallUrl = $this->_apiUrl.'/'.$method.'/?'.join('&',$query_string);

        return $this->_debugCallUrl;
    }

    /**
     * Send Request
     *
     * Send the request to the Mailjet API server and get back the result
     * Basically, setup and execute the curl process
     *
     * @access	private
     * @uses	Mailjet::Api::$_debug
     * @uses	Mailjet::Api::$_apiKey
     * @uses	Mailjet::Api::$_secretKey
     * @uses	Mailjet::Api::$_curl_handle
     * @uses	Mailjet::Api::requestUrlBuilder() to build the full Url for the request
     * @param string $method  Method to call
     * @param array  $params  Additional parameters for the request
     * @param string $request Request method
     *
     * @return string the result of the request
     */
    private function sendRequest($method = FALSE,$params=array(),$request="GET")
    {
        if ($this->_debug != 0) {
            $this->_debugMethod = $method;
            $this->_debugRequest = $request;
        }
        $url = $this->requestUrlBuilder($method, $params, $request);

        if(is_null($this->_curl_handle))
            $this->_curl_handle = curl_init();

        curl_setopt($this->_curl_handle, CURLOPT_URL, $url);
        curl_setopt($this->_curl_handle, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($this->_curl_handle, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($this->_curl_handle, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($this->_curl_handle, CURLOPT_USERPWD, $this->_apiKey.':'.$this->_secretKey);

        switch ($request) {
            case 'GET' :
                curl_setopt($this->_curl_handle, CURLOPT_CUSTOMREQUEST, 'GET');
                curl_setopt($this->_curl_handle, CURLOPT_HTTPGET, TRUE);
                curl_setopt($this->_curl_handle, CURLOPT_POSTFIELDS, NULL);
                $this->_request_post = FALSE;
                break;
            case 'POST':
                curl_setopt($this->_curl_handle, CURLOPT_CUSTOMREQUEST, 'POST');
                curl_setopt($this->_curl_handle, CURLOPT_POST, count($params));
                curl_setopt($this->_curl_handle, CURLOPT_POSTFIELDS, http_build_query($params, '', '&'));
                $this->_request_post = $params;
                break;
        }

        $buffer = curl_exec($this->_curl_handle);

        if ($this->_debug > 2)
            var_dump($buffer);

        $this->_response_code = curl_getinfo($this->_curl_handle,CURLINFO_HTTP_CODE);
        $this->_response = ($this->_output == 'json') ? json_decode($buffer) : $buffer;

        return ($this->_response_code == 200) ? TRUE : FALSE;
    }

    /**
     * Display debugging information
     *
     * @access	private
     * @uses	Mailjet::Api::$_response
     * @uses	Mailjet::Api::$_response_code
     * @uses	Mailjet::Api::$_debugCallUrl
     * @uses	Mailjet::Api::$_debugMethod
     * @uses	Mailjet::Api::$_debugRequest
     * @uses	Mailjet::Api::$_request_post
     */
    private function debug()
    {
        $this->debugErrorHtml = '<style type="text/css">';
        $this->debugErrorHtml .= '

        #debugger {width: 100%; font-family: arial;}
        #debugger table {padding: 0; margin: 0 0 20px; width: 100%; font-size: 11px; text-align: left;border-collapse: collapse;}
        #debugger th, #debugger td {padding: 2px 4px;}
        #debugger tr.h {background: #999; color: #fff;}
        #debugger tr.Success {background:#90c306; color: #fff;}
        #debugger tr.Error {background:#c30029 ; color: #fff;}
        #debugger tr.Not-modified {background:orange ; color: #fff;}
        #debugger th {width: 20%; vertical-align:top; padding-bottom: 8px;}

        ';
        $this->debugErrorHtml .= '</style>';

        $this->debugErrorHtml .= '<div id="debugger">';

        if (isset($this->_response_code)) {
            if ($this->_response_code == 200) {
                $this->debugErrorHtml .= '<table>';
                $this->debugErrorHtml .= '<tr class="Success"><th>Success</th><td></td></tr>';
                $this->debugErrorHtml .= '<tr><th>Status code</th><td>'.$this->_response_code.'</td></tr>';
                if (isset($this->_response))
                    $this->debugErrorHtml .= '<tr><th>Response</th><td><pre>'.utf8_decode(print_r($this->_response,1)).'</pre></td></tr>';
                $this->debugErrorHtml .= '</table>';
            } elseif ($this->_response_code == 304) {
                $this->debugErrorHtml .= '<table>';
                $this->debugErrorHtml .= '<tr class="Not-modified"><th>Error</th><td></td></tr>';
                $this->debugErrorHtml .= '<tr><th>Error no</th><td>'.$this->_response_code.'</td></tr>';
                $this->debugErrorHtml .= '<tr><th>Message</th><td>Not Modified</td></tr>';
                $this->debugErrorHtml .= '</table>';
            } else {
                $this->debugErrorHtml .= '<table>';
                $this->debugErrorHtml .= '<tr class="Error"><th>Error</th><td></td></tr>';
                $this->debugErrorHtml .= '<tr><th>Error no</th><td>'.$this->_response_code.'</td></tr>';
                if (isset($this->_response)) {
                    if ( is_array($this->_response) OR  is_object($this->_response) ) {
                        $this->debugErrorHtml .= '<tr><th>Status</th><td><pre>'.print_r($this->_response,TRUE).'</pre></td></tr>';
                    } else {
                        $this->debugErrorHtml .= '<tr><th>Status</th><td><pre>'.$this->_response.'</pre></td></tr>';
                    }
                }
                $this->debugErrorHtml .= '</table>';
            }
        }

        $call_url = parse_url($this->_debugCallUrl);

        $this->debugErrorHtml .= '<table>';
        $this->debugErrorHtml .= '<tr class="h"><th>API config</th><td></td></tr>';
        $this->debugErrorHtml .= '<tr><th>Protocole</th><td>'.$call_url['scheme'].'</td></tr>';
        $this->debugErrorHtml .= '<tr><th>Host</th><td>'.$call_url['host'].'</td></tr>';
        $this->debugErrorHtml .= '<tr><th>Version</th><td>'.$this->_version.'</td></tr>';
        $this->debugErrorHtml .= '</table>';

        $this->debugErrorHtml .= '<table>';
        $this->debugErrorHtml .= '<tr class="h"><th>Call infos</th><td></td></tr>';
        $this->debugErrorHtml .= '<tr><th>Method</th><td>'.$this->_debugMethod.'</td></tr>';
        $this->debugErrorHtml .= '<tr><th>Request type</th><td>'.$this->_debugRequest.'</td></tr>';
        $this->debugErrorHtml .= '<tr><th>Get Arguments</th><td>';

        $args = explode("&",$call_url['query']);
        foreach ($args as $arg) {
            $arg = explode("=",$arg);
            $this->debugErrorHtml .= ''.$arg[0].' = <span style="color:#ff6e56;">'.$arg[1].'</span><br/>';
        }

        $this->debugErrorHtml .= '</td></tr>';

        if ($this->_request_post) {
            $this->debugErrorHtml .= '<tr><th>Post Arguments</th><td>';

            foreach ($this->_request_post as $k=>$v) {
                $this->debugErrorHtml .= $k.' = <span style="color:#ff6e56;">'.$v.'</span><br/>';
            }

            $this->debugErrorHtml .= '</td></tr>';
        }

        $this->debugErrorHtml .= '<tr><th>Call url</th><td>'.$this->_debugCallUrl.'</td></tr>';
        $this->debugErrorHtml .= '</table>';

        $this->debugErrorHtml .= '</div>';

        if ($this->_debugEcho)
            echo $this->debugErrorHtml;
    }

}
