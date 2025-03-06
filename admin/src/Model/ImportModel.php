<?php
/**
* CG Isotope Component  - Joomla 4.x/5.x Component 
* Version			: 2.3.3
* Package			: CG ISotope
* copyright 		: Copyright (C) 2024 ConseilGouz. All rights reserved.
* license    		: https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
* From              : isotope.metafizzy.co
*/
namespace ConseilGouz\Component\CGIsotope\Administrator\Model;

defined('_JEXEC') or die;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\CMS\Factory;
use Joomla\CMS\Table\Table;

class ImportModel extends ListModel
{
	public function __construct($config = array())
	{
		if (empty($config['filter_fields'])) {
			$config['filter_fields'] = array(
				'id', 't.id',
				'title', 't.title',
                'state', 't.state',
				'sections', 't.sections',
				'language','t.language',
				'page_params', 't.page_params');
		}

		parent::__construct($config);
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
	public function getTable($type = 'Pages', $prefix = 'CGIsotopeTable', $config = array())
	{
		return Table::getInstance($type, $prefix, $config);
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
}