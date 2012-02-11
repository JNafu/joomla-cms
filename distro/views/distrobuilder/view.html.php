<?php defined('_JEXEC') || die('=;)');
/**
 * @package    L0gVi3w
 * @subpackage Views
 * @author     Nikolai Plath - elkuku
 * @author     Created on 17-Jul-2011
 * @license    GNU/GPL
 */

//-- Import the JView class
jimport('joomla.application.component.view');

/**
 * HTML View class for the L0gVi3w Component.
 *
 * @package L0gVi3w
 */
class DistrobuilderViewDistrobuilder extends JView
{
	protected $packages = array();

	/**
	 * L0gVi3w view display method.
	 *
	 * @param string $tpl The name of the template file to parse.
	 *
	 * @return void
	 */
	public function display($tpl = null)
	{
		//		$this->items = $this->get('items');

		$this->archiveFiles = DistroBuilderHelper::getArchiveFiles();

		$this->packages = DistroBuilderHelper::getPackages(JPATH_BASE . '/build');

		if (!count($this->packages))
		{
			JFactory::getApplication()->enqueueMessage(
				'No packages found. Please build up your environment by pressing "Build" - This may take some time !'
				, 'warning');
		}

		parent::display($tpl);
	}

}
