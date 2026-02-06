<?php
/**
* CG Isotope Component  - Joomla 4.x/5.x/6.x Component
* Package			: CG ISotope
* copyright 		: Copyright (C) 2026 ConseilGouz. All rights reserved.
* license    		: https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
*
*/

namespace ConseilGouz\Component\CGIsotope\Site\Controller;

\defined('_JEXEC') or die;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Controller\BaseController;

class DisplayController extends BaseController
{
    public function display($cachable = false, $urlparams = false)
    {
        $input = Factory::getApplication()->getInput();
        $view = $input->getCmd('view', 'page');
        $input->set('view', $view);
        $cachable = (bool)$this->app->getConfig()->get('caching');
        parent::display($cachable, $urlparams);
        return $this;
    }
}
