<?php
/**
* CG Isotope Component  - Joomla 4.0.0 Component 
* Version			: 2.3.1
* Package			: CG ISotope
* copyright 		: Copyright (C) 2022 ConseilGouz. All rights reserved.
* license    		: http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
* From              : isotope.metafizzy.co
*/
namespace ConseilGouz\Component\CGIsotope\Administrator\Model;

defined('_JEXEC') or die;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\CMS\Factory;
use Joomla\CMS\Table\Table;
use Joomla\Utilities\ArrayHelper;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;

class PagesModel extends ListModel
{
	public function __construct($config = array(), MVCFactoryInterface $factory = null)
	{
		if (empty($config['filter_fields'])) {
			$config['filter_fields'] = array(
				'id', 't.id',
				'title', 't.title',
                'state', 't.state',
				'sections', 't.sections',
				'fieldslinks', 't.fieldslinks',
				'language','t.language',
				'page_params', 't.page_params');
		}

		parent::__construct($config,$factory);
	}
	protected function getListQuery()
	{
		// Initialise variables.
		$db		= $this->getDbo();
		$query	= $db->getQuery(true);

		// Select the required fields from the table.
		$query->select('t.id, t.title, t.state,t.page_params, t.sections, t.language');
		// Join over the language
		$query->select('l.title AS language_title, l.image AS language_image')
			->join('LEFT', $db->quoteName('#__languages', 'l') . ' ON l.lang_code = t.language');
		$query->from('#__cgisotope_page as t');
		// Filter by published state
		$published = $this->getState('filter.state');
		if (is_numeric($published)) {
			$query->where('t.state = '.(int) $published);
		} else if ($published === '') {
			$query->where('(t.state IN (0, 1))');
		}
		// Filter by search
		$search = $this->getState('filter.search');
		if (!empty($search)) {							
			$searchLike = $db->Quote('%'.$db->escape($search, true).'%');
			$search = $db->Quote($db->escape($search, true));
			$query->where('(t.title = '.$search.' )');
		} //end search
		
		// Add the list ordering clause.
		$orderCol	= $this->state->get('list.ordering');
		$orderDirn	= $this->state->get('list.direction');		
		$query->order($db->escape($orderCol.' '.$orderDirn));
		return $query;
	}

	protected function populateState($ordering = null, $direction = null)
	{
		// Initialise variables.
		$app = Factory::getApplication('administrator');
		// Load the filter state.
		$search = $this->getUserStateFromRequest($this->context.'.filter.search', 'filter_search');
							$this->setState('filter.search', $search);
		$state = $this->getUserStateFromRequest($this->context.'.filter.state', 'filter_state', '', 'string');
							$this->setState('filter.state', $state);
		// List state information.
		parent::populateState('t.id', 'DESC');
	}
	/**
	 * Get logs data as a database iterator
	 *
	 * @param   integer[]|null  $pks  An optional array of log record IDs to load
	 *
	 * @return  JDatabaseIterator
	 *
	 * @since   3.9.0
	 */
	public function getDataAsIterator($pks = null)
	{
	    $db    = $this->getDbo();
	    $query = $this->getDataQuery($pks);
	    
	    $db->setQuery($query);
	    
	    return $db->getIterator();
	}
	
	/**
	 * Get the query for loading data
	 *
	 * @param   integer[]|null  $pks  An optional array of log record IDs to load
	 *
	 * @return  JDatabaseQuery
	 *
	 * @since   3.9.0
	 */
	private function getDataQuery($pks = null)
	{
	    $db    = $this->getDbo();
	    $query = $db->getQuery(true);
	    $query->select('a.*');
	    $query->from('#__cgisotope_page as a');
	    if (is_array($pks) && count($pks) > 0)
	    {
	        $query->where($db->quoteName('a.id') . ' IN (' . implode(',', ArrayHelper::toInteger($pks)) . ')');
	    }
	    
	    return $query;
	}
	
}