<?php
/**
* CG Isotope Component  - Joomla 4.x/5.x Component 
* Package			: CG ISotope
* copyright 		: Copyright (C) 2024 ConseilGouz. All rights reserved.
* license    		: https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
*
*/
namespace ConseilGouz\Component\CGIsotope\Site\Controller;
\defined('_JEXEC') or die;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Language\Text;
use ConseilGouz\Component\CGIsotope\Site\Helper\CGHelper;
class DisplayController extends BaseController {
    public function display($cachable = false, $urlparams = false) {
        $view = Factory::getApplication()->getInput()->getCmd('view', 'page');
        Factory::getApplication()->getInput()->set('view', $view);
        parent::display($cachable, $urlparams);
        return $this;
    }
}
