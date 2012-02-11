<?php
/**
 * Configuration class.
 */

defined('_JEXEC') or die;

/**
 * Configuration class.
 */
final class JConfig
{
	/**
	 * The application theme.
	 *
	 * @var    string
	 */
	public $theme = 'distrobuilder';

	/**
	 * Database settings
	 */
	public $dbtype = 'sqlite';
	public $db = 'distrobuilder.sdb';
	public $dbprefix = 'dist_';

}//class
