<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * CodeIgniter Slug Library
 *
 * NOTICE OF LICENSE
 *
 * Licensed under the Academic Free License version 3.0
 *
 * This source file is subject to the Academic Free License (AFL 3.0) that is
 * bundled with this package in the files license_afl.txt / license_afl.rst.
 * It is also available through the world wide web at this URL:
 * http://opensource.org/licenses/AFL-3.0
 *
 * @package     CodeIgniter
 * @author      Eric Barnes
 * @copyright   Copyright (c) Eric Barnes (http://ericlbarnes.com)
 * @license     http://opensource.org/licenses/AFL-3.0 Academic Free License (AFL 3.0)
 * @link        http://code.ericlbarnes.com
 * @filesource
 */

// ------------------------------------------------------------------------

/**
 * Slug Library
 *
 * Nothing but legless, boneless creatures who are responsible for creating
 * magic "friendly urls" in your CodeIgniter application. Slugs are nocturnal
 * feeders, hiding during daylight hours and should only be exposed in the uri.
 *
 * @subpackage Libraries
 */
class Slug
{
	/**
	 * Global ci
	 *
	 * @var object
	 **/
	protected $_ci = '';

	/**
	 * URI Field in the table
	 *
	 * @var string
	 */
	public $field_uri = '';

	/**
	 * The table
	 *
	 * @var string
	 */
	public $field_table = '';

	/**
	 * The primary id of the table
	 *
	 * @var string
	 */
	public $field_id = '';

	/**
	 * The title field
	 *
	 * @var string
	 */
	public $field_title = '';

	// ------------------------------------------------------------------------

	/**
	 * Setup all vars
	 *
	 * @param array $config
	 * @return void
	 */
	public function __construct($config = array())
	{
		$this->_ci =& get_instance();

		if ( ! empty($config))
		{
			$this->_initialize($config);
		}

		log_message('debug', 'Slug Class Initialized');
	}

	// --------------------------------------------------------------------

	/**
	 * Initialize preferences
	 *
	 * @param   array
	 * @return  void
	 * @access  private
	 */
	private function _initialize($config = array())
	{
		foreach ($config as $key => $value)
		{
			$this->{$key} = $value;
		}
	}

	// ------------------------------------------------------------------------

	/**
	 * Manually Set Config
	 *
	 * Pass an array of config vars to override previous setup
	 *
	 * @param   array
	 * @return  void
	 */
	public function set_config($config = array())
	{
		if ( ! empty($config))
		{
			$this->_initialize($config);
		}
	}

	// ------------------------------------------------------------------------

	/**
	 * Create a uri string
	 *
	 * This wraps into the _check_uri method to take a character
	 * string and convert into ascii characters.
	 *
	 * @param   mixed (string or array)
	 * @param   int
	 * @uses    Slug::_check_uri()
	 * @uses    Slug::create_slug()
	 * @return  string
	 */
	public function create_uri($data = '', $id = '')
	{
		if (empty($data))
		{
			return FALSE;
		}

		if (is_array($data))
		{
			if (isset($data[$this->field_uri]) && $data[$this->field_uri] != '')
			{
				return $this->_check_uri($this->create_slug($data[$this->field_uri]), $id);
			}
			elseif (isset($data[$this->field_title]))
			{
				return $this->_check_uri($this->create_slug($data[$this->field_title]), $id);
			}
			else
			{
				return FALSE;
			}
		}
		else
		{
			return $this->_check_uri($this->create_slug($data), $id);
		}
	}

	// ------------------------------------------------------------------------

	/**
	 * Returns a string with all spaces converted to underscores (by default), accented
	 * characters converted to non-accented characters, and non word characters removed.
	 *
	 * @param   string $string the string you want to slug
	 * @param   string $replacement will replace keys in map
	 * @return  string
	 */
	public function create_slug($string, $replacement = 'underscore')
	{
		$this->_ci->load->helper(array('url', 'text', 'string'));
		$string = convert_accented_characters($string);
		$string = strtolower(url_title($string, $replacement));

		if ($replacement == 'dash')
		{
			return reduce_multiples($string, '-', TRUE);
		}
		return reduce_multiples($string, '_', TRUE);
	}

	// ------------------------------------------------------------------------

	/**
	 * Check URI
	 *
	 * Checks other items for the same uri and if something else has it
	 * change the name to "name_1".
	 *
	 * @param   string $uri
	 * @param   int $id
	 * @param   int $count
	 * @return  string
	 */
	private function _check_uri($uri, $id = FALSE, $count = 0)
	{
		if ($count > 0)
		{
			$new_uri = $uri.'_'.$count;
		}
		else
		{
			$new_uri = $uri;
		}

		$count++;

		// Setup the query
		$this->_ci->db->select($this->field_uri)
			->from($this->field_table)
			->where($this->field_uri, $new_uri);

		if ($id)
		{
			$this->_ci->db->where($this->field_id.' !=', $id);
		}

		$query = $this->_ci->db->get();

		if ($query->num_rows() > 0)
		{
			return $this->_check_uri($uri, $id, $count);
		}
		else
		{
			return $new_uri;
		}
	}
}