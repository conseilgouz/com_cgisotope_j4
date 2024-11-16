<?php
/**
* CG Isotope Component  - Joomla 4.x/5.x Component 
* Package			: CG ISotope
* copyright 		: Copyright (C) 2024 ConseilGouz. All rights reserved.
* license    		: https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
*/
namespace ConseilGouz\Component\CGIsotope\Administrator\View\Pages;
// No direct access
\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Toolbar\ToolbarHelper;

class HtmlView extends BaseHtmlView
{
	protected $items;
	protected $pagination;
	protected $state;
	/**
	 * Display the view
	 */
	public function display($tpl = null)
	{
		// Initialise variables.
		$this->items		= $this->get('Items');
		$this->pagination	= $this->get('Pagination');
		$this->state		= $this->get('State');
        $input = Factory::getApplication()->input;

		if (!\count($this->items) && $this->isEmptyState = $this->get('IsEmptyState'))
		{
			$this->setLayout('emptystate');
		}

		// Check for errors.
		if (\count($errors = $this->get('Errors')))
		{
			throw new GenericDataException(implode("\n", $errors), 500);
		}

		$this->addToolbar();
		// $this->sidebar = JHtmlSidebar::render();
		
		parent::display($tpl);
	}

	protected function addToolbar()
	{
        $canDo = ContentHelper::getActions('com_cgisotope');
		$user = Factory::getApplication()->getIdentity();
		
		ToolbarHelper::title(Text::_('COM_ISOTOPE_PAGES'), 'page.png');

		if ($canDo->get('core.create') || (count($user->getAuthorisedCategories('com_cgisotope', 'core.create'))) > 0 ) {
			ToolbarHelper::addNew('page.add');
		}

		if (($canDo->get('core.edit')) || ($canDo->get('core.edit.own'))) {
			ToolbarHelper::editList('page.edit');
		}

		if ($canDo->get('core.edit.state')) {
			ToolbarHelper::divider();
			ToolbarHelper::publish('pages.publish', 'JTOOLBAR_PUBLISH', true);
			ToolbarHelper::unpublish('pages.unpublish', 'JTOOLBAR_UNPUBLISH', true);
			ToolbarHelper::divider();
			ToolbarHelper::custom('pages.copy', 'copy', '', Text::_('COM_CGISOTOPE_COPY'), true);
		}
		if ($canDo->get('core.export')) {
			ToolbarHelper::divider();
			ToolbarHelper::custom('pages.export', 'download', '', Text::_('COM_CGISOTOPE_EXPORT'), true);
		}
/*	ToDo 
		if ($canDo->get('core.import')) {
			$layout = new LayoutHelper::render('toolbar.import');
			$bar  = JToolbar::getInstance('toolbar');
			$bar->appendButton('Custom', $layout->render(array()), Text::_('COM_CGISOTOPE_IMPORT'));
			ToolbarHelper::divider();
		} */ 
		if ($this->state->get('filter.state') == -2 && $canDo->get('core.delete')) {
			ToolBarHelper::deleteList('', 'pages.delete','JTOOLBAR_EMPTY_TRASH');			
		}
		else if ($canDo->get('core.edit.state')) {
			ToolBarHelper::trash('pages.trash');
		} 
		if ($canDo->get('core.admin')) {
			ToolbarHelper::divider();
			ToolbarHelper::inlinehelp();			
			ToolbarHelper::preferences('com_cgisotope');			
		}
	}
}
