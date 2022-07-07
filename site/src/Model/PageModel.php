<?php
/**
* CG Isotope Component  - Joomla 4.0.0 Component 
* Version			: 2.3.2
* Package			: CG ISotope
* copyright 		: Copyright (C) 2022 ConseilGouz. All rights reserved.
* license    		: http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
* From              : isotope.metafizzy.co
*/
namespace ConseilGouz\Component\CGIsotope\Site\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\MVC\Model\ListModel;

class PageModel extends ListModel {

	public function __construct($config = array(), MVCFactoryInterface $factory = null)
	{
		if (empty($config['filter_fields'])) {
			$config['filter_fields'] = array(
				'id', 't.id',
				'title', 't.title',
                'state', 't.state',
				'sections', 't.sections',
				'fieldslinks', 't.fieldslinks',
				'page_params', 't.page_params'
			);
		}

		parent::__construct($config,$factory);
	}
	/**
	 * Method to get a table object, load it if necessary.
	 *
	 * @param   string  $name     The table name. Optional.
	 * @param   string  $prefix   The class prefix. Optional.
	 * @param   array   $options  Configuration array for model. Optional.
	 *
	 * @return  Table  A Table object
	 *
	 * @since   4.0.0
	 * @throws  \Exception
	 */
	public function getTable($name = 'Page', $prefix = 'Administrator', $options = array())
	{
		return parent::getTable($name, $prefix, $options);
	}

}
