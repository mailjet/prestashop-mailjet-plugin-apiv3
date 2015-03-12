<?php
/**
 * Mailjet Public API Overlay Data Type / The real-time Cloud Emailing platform
 *
 * Connect your Apps and Make our product yours with our powerful API
 * http://www.mailjet.com/ Mailjet SAS Website
 *
 * @author		David Coullet at Mailjet Dev team
 * @copyright	Copyright (c) 2013, Mailjet SAS, http://www.mailjet.com/Terms-of-use.htm
 * @file
 */

// ---------------------------------------------------------------------


/**
 * Mailjet Public API Parameters class
 *
 * http://www.mailjet.com/docs/api
 *
 * @code{.php}
 * $parameters = new Mailjet\Parameters();
 *
 * //Set a 5 minutes cache
 * $parameters->cache = 300;
 *
 * //display the set of parameters
 * echo $parameters->dump();
 *
 * //test if the cache parameter is set and unset it
 * if (isset($parameters->cache))
 * 	unset($parameters->cache);
 *
 * // Example of Method chaining
 * $parameters->set_start(0)->set_limit(5);
 *
 * //Serialized the list of parameters for storing purpose
 * $stored = $parameters->serialized();
 *
 * //Display the number of parameters
 * echo 'Nb : '.$parameters->count();
 *
 * // Retrieve it later by doing a
 * $storedParameters = new Parameters(Tools::jsonDecode($stored));
 *
 * //Reset all the parameters to use it with a new Api call
 * $parameters->reset();
 * @endcode
 *
 * updated on 2013-08-11
 *
 * @class		Parameters
 * @author		David Coullet at Mailjet Dev team
 * @version		0.1
 */
class Mailjet_Parameters
{
    /**
     * Parameters array
     *
     * @access	private
     * @var		array $data
     */
    private $data = array();

    /**
     * Constructor
     *
     * Fill the object with parameters from an array if provided
     *
     * @code{.php}
     * //You can set an array to create the object with default value
     * $paramsArray = array ('start' => 0, 'limit' => 5);
     * $parameters = new Mailjet\Parameters($paramsArray);
     * @endcode
     * @access	public
     * @param array $params Array of parameters
     */
    function __construct(array $params = null)
    {
        if (!is_null($params))
            foreach ($params as $key => $value)
                $this->data[$key] = $value;
    }

    /**
     * Magic Methods for setting a parameter
     *
     * @code{.php}
     * $parameters->start = 0;
     * $parameters->limit = 5;
     * @endcode
     * @access	public
     * @param string $key   Parameter name (Required)
     * @param string $value Parameter value (Required)
     */
    public function __set($key, $value)
    {
        $this->data[$key] = $value;
    }

    /**
     * Magic Methods for setting a parameter via a set_key(value) for Methods chaining
     *
     * @code{.php}
     * // Example of Method chaining
     * $parameters = $parameters->set_start(0)->set_limit(5);
     * if (is_null($parameters))
     * 	echo 'Usage : ->set_key(value)';
     * @endcode
     * @access	public
     * @param string $key   set_ Parameter name (Required)
     * @param string $value Parameter value (Required)
     *
     * @return mixed Object on success, null otherwise (to stop the chaining)
     */
    public function __call($key, $value)
    {
        if ((strpos($key, 'set_', 0) !== 0)
            || (($key = substr($key, 4)) == FALSE)
            || (sizeof($value) == 0))

            return (null);
        $this->data[$key] = $value[0];

        return ($this);
    }

    /**
     * Magic Methods for getting a new parameter
     *
     * @code{.php}
     * echo $parameters->start;
     * var_dump($parameters->limit);
     * @endcode
     * @access	public
     * @param string $key Parameter name (Required)
     *
     * @return mixed Parameter value
     */
    public function __get($key)
    {
        if (array_key_exists($key, $this->data))
            return $this->data[$key];

        return (null);
    }

    /**
     * Magic Methods for testing if a parameter exists
     *
     * @code{.php}
     * if (isset($parameters->start)
     * 	echo $parameters->start;
     * @endcode
     * @access	public
     * @param string $key Parameter name (Required)
     *
     * @return boolean TRUE if set, FALSE otherwise
     */
    public function __isset($key)
    {
        return isset($this->data[$key]);
    }

    /**
     * Magic Methods to unset a parameter
     *
     * @code{.php}
     * unset($parameters->limit);
     * @endcode
     * @access	public
     * @param string $key Parameter name (Required)
     */
    public function __unset($key)
    {
        unset($this->data[$key]);
    }

    /**
     * Reset the list of parameters
     *
     * @code{.php}
     * $parameters->reset();
     * echo $parameters->dump();
     * @endcode
     * @access	public
     */
    public function reset()
    {
        unset($this->data);
        $this->data = array();
    }

    /**
     * Get the number of parameters stored in the object
     *
     * @code{.php}
     * echo $parameters->count();
     * @endcode
     * @access	public
     */
    public function count()
    {
        return count($this->data);
    }

    /**
     * Dump a parsable string representation of the list of parameters
     *
     * @code{.php}
     * echo $parameters->dump();
     * @endcode
     * @access	public
     *
     * @return string parsable string representation of the list of parameters
     */
    public function dump()
    {
        return var_export($this->data);
    }

    /**
     * Serialize the list of parameters as an array
     *
     * @code{.php}
     * $stored = $parameters->serialized();
     * @endcode
     * @access	public
     *
     * @return string list of parameters serialized as an array
     */
    public function serialized()
    {
        return serialize($this->data);
    }
}
