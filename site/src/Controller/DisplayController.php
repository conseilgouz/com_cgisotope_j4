<?php
/**
* CG Isotope Component  - Joomla 4.x/5.x Component 
* Version			: 4.2.2
* Package			: CG ISotope
* copyright 		: Copyright (C) 2023 ConseilGouz. All rights reserved.
* license    		: https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
* From              : isotope.metafizzy.co
*/
namespace ConseilGouz\Component\CGIsotope\Site\Controller;
\defined('_JEXEC') or die;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Language\Text;
use ConseilGouz\Component\CGIsotope\Site\Helper\CGHelper;
class DisplayController extends BaseController {
    public function display($cachable = false, $urlparams = false) {
        $view = Factory::getApplication()->input->getCmd('view', 'page');
        Factory::getApplication()->input->set('view', $view);
        parent::display($cachable, $urlparams);
        return $this;
    }
}
