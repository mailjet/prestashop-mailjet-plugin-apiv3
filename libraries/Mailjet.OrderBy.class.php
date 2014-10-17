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
 * Mailjet Public API Orderby Class
 *
 * This class enables you to sort an array of object found in API call's response
 *
 * @code{.php}
 * //Sort the $key field by 'id' descending order
 * Mailjet\OrderBy::sort($response[$key], array('id', 'DESC'));
 *
 * //Sort the $key field by 'name' ascending order
 * Mailjet\OrderBy::sort($response[$key], 'name ASC');
 * @endcode
 *
 * updated on 2013-08-12
 *
 * @class		OrderBy
 * @author		David Coullet at Mailjet Dev team
 * @version		0.1
 */
class Mailjet_OrderBy
{

    /**
     * Static Metadata (field's name and Order)
     *
     * @access	private
     * @var		array $_args
     */
    private static $_args;

    /**
     * Sort (Default order : ASC)
     *
     * $args[0] = name of the field and $args[1] = order
     * @code{.php}
     * //Sort the $key field by 'id' descending order
     * Mailjet\OrderBy::sort($response[$key], array('id', 'DESC'));
     *
     * //Sort the $key field by 'name' ascending order
     * Mailjet\OrderBy::sort($response[$key], 'name ASC');
     * @endcode
     *
     * @access	public
     * @uses	Mailjet::OrderBy::$_args
     * @uses	Mailjet::OrderBy::compare()
     * @param array $array Array of Objects
     * @param mixed $args  Metadata
     */
    public static function sort(&$array, $args)
    {
        if (is_array($array)) {
            if (!is_array($args))
                $args = explode(' ', $args);
            if (count($args) == 1)
                self::$_args[1] = 'ASC';
            self::$_args = array_map('trim', $args);
            usort($array, array('Mailjet_OrderBy', 'compare'));
        }
    }

    /**
     * Compare 2 Objects
     *
     * @access	public
     * @uses	Mailjet::OrderBy::$_args
     * @param  $a	Object a
     * @param  $b	Object b
     */
    public static function compare($a, $b)
    {
        $name	= self::$_args[0];
        $order	= self::$_args[1];

        if ($order == 'DESC')
            return ($a->$name < $b->$name);
        else
            return ($a->$name > $b->$name);
    }

}
