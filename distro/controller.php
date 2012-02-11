<?php defined('_JEXEC') || die('=;)');
/**
 * @package    Phonebook
 * @subpackage Base
 * @author     Nikolai Plath - elkuku
 * @author     Created on 17-Jul-2011
 * @license    GNU/GPL
 */

jimport('joomla.application.component.controller');

/**
 * L0gVi3w Controller.
 *
 * @package    Phonebook
 * @subpackage Controllers
 */
class DistrobuilderController extends JController
{
	public function build()
	{
		$xml = JFactory::getXML('elements.xml');

		DistroBuilderHelper::build($xml, PATH_CMS_ROOT, JPATH_BASE.'/build');

		JFactory::getApplication()->enqueueMessage('Your build environment is ready.');

		parent::display();
	}

	public function make()
	{
		// @todo clean
		$packages = JRequest::getVar('packages', array());

		DistroBuilderHelper::make($packages, JPATH_BASE.'/build');

		JFactory::getApplication()->enqueueMessage('Your distro has been built.');

		parent::display();
	}

}//class
