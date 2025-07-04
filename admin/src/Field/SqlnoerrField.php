<?php
/**
* CG Isotope Component  - Joomla 4.x/5.x Component 
* Package			: CG ISotope
* copyright 		: Copyright (C) 2025 ConseilGouz. All rights reserved.
* license    		: https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
* From              : isotope.metafizzy.co
*/
namespace ConseilGouz\Component\CGIsotope\Administrator\Field;
defined('_JEXEC') or die;
use Joomla\CMS\Form\Field\SqlField;
use Joomla\CMS\Factory;

class SQLnoerrField extends SqlField
{
	public $type = 'SQLnoerr';

	/**
	 * Method to check if SQL query contains errors
	 * @return  array  The field option objects or empty (if error in query)
	 */
	protected function getOptions()
	{
		$options = array();

		// Initialize some field attributes.
		$key   = $this->keyField;
		$value = $this->valueField;
		$header = $this->header;

		if ($this->query)
		{
			// Get the database object.
			$db = $this->getDatabase();

			try
			{
			// Set the query and get the result list.
				$db->setQuery($this->query);
			}
			catch (\Exception $e)
			{
				 return $options; // SQL Error : return empty
			}
			try
			{
				$items = $db->loadObjectlist();
			}
			catch (\Exception $e)
			{
				 return $options; // SQL Error : return empty
			}
		}
		// No error : execute SQL
		$options = array_merge(parent::getOptions(), $options);

		return $options;
	}
}
