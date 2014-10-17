<?php
/**
 * Mailjet Public API / The real-time Cloud Emailing platform
 *
 * Connect your Apps and Make our product yours with our powerful API
 * http://www.mailjet.com/ Mailjet SAS Website
 *
 * @author		David Coullet at Mailjet Dev team
 * @copyright	Copyright (c) 2012-2013, Mailjet SAS, http://www.mailjet.com/Terms-of-use.htm
 * @file
 */

// ---------------------------------------------------------------------


/**
 * Mailjet Public API Exception Class
 *
 * This Custom Exception class enables you to catch errors
 *
 * @code{.php}
 * try {
 * 	$response = $mj->getEmailStatisticsP($parameters);
 * } catch (Mailjet\ApiException $e) {
 * 	echo $e->getMessage();
 * 	if ($e->getCode >= 400)
 * 		echo $mj->getErrorHtml();
 * }
 * var_dump($response);
 * @endcode
 *
 * updated on 2013-08-11
 *
 * @class		ApiException
 * @author		David Coullet at Mailjet Dev team
 * @version		0.1
 */
class Mailjet_ApiException extends Exception
{
    /**
     * Mailjet HTTP Code
     * Can be obtain by $e->getCode()
     *
     * @access	private
     * @var		integer $http_code
     */
    public $http_code;

    /**
     * Mailjet Error message
     * Can be obtain by $e->getMessage()
     *
     * @access	private
     * @var		string $error_message
     */
    public $error_message = 'Unknown exception';

    /**
     * Constructor
     *
     * Create the custom exception
     *
     * @access	public
     * @uses	Mailjet::ApiException::$http_code
     * @uses	Mailjet::ApiException::$error_message
     * @param integer $code    Error code
     * @param string  $message Error message
     */
    public function __construct($code, $message = null)
    {
        try {
            $code = intval($code);
        } catch (Exception $e) {
            $code = 0;
        }
        $this->http_code = $code;

        if (isset($message))
            $this->error_message = $message;

        parent::__construct($this->error_message, $this->http_code);
    }

}
