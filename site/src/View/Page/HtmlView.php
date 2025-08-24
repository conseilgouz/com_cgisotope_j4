<?php
/**
* CG Isotope Component  - Joomla 4.x/5.x Component 
* Package			: CG ISotope
* copyright 		: Copyright (C) 2024 ConseilGouz. All rights reserved.
* license    		: https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
*
*/
namespace ConseilGouz\Component\CGIsotope\Site\View\Page;
\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;

class HtmlView extends BaseHtmlView {

    protected $items;
    protected $pagination;
    protected $state;
    protected $params;

    /**
     * Display the view
     */
    public function display($tpl = null) {
        $app = Factory::getApplication();
        $this->state = $this->get('State');
        $this->pagination = false; // php 8.0
		$model = $this->getModel();
        $this->params = $app->getParams('cgisotope',$model);
        $this->page= $app->getInput()->getInt('id'); 
        $this->_prepareDocument();
        parent::display($tpl);
    }

    /**
     * Prepares the document
     */
    protected function _prepareDocument() {
		$app              = Factory::getApplication();
		$menu             = $app->getMenu()->getActive();
		$pathway          = $app->getPathway();
		$title            = '';

		// Highest priority for "Browser Page Title".
		if ($menu)
		{
			$title = $menu->getParams()->get('page_title', '');
		}

		$this->params->def('page_heading', $this->params->get('page_title', $menu->title));
		$title = $title ?: $this->params->get('page_title', $menu->title);

		$this->setDocumentTitle($title);
		$pathway->addItem($title);


	}

}
