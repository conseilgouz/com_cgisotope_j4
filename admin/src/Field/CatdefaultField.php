<?php
/**
* CG Isotope Component  - Joomla 4.x/5.x Component
* Package			: CG ISotope
* copyright 		: Copyright (C) 2025 ConseilGouz. All rights reserved.
* license    		: https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
* From              : isotope.metafizzy.co
*/

namespace ConseilGouz\Component\CGIsotope\Administrator\Field;

\defined('_JEXEC') or die;

use Joomla\CMS\Form\Field\ListField;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

/* cette classe est utile pour le choix menu : affichage des pages */
class CatdefaultField extends ListField
{
    public $type = 'Defaultcat';

    public function getOptions()
    {
        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select('id AS value')
            ->select('title AS text')
            ->from('#__categories')
            ->where('published = 1')
            ->where('extension IN ('.$db->quote('com_content').','.$db->quote('com_weblinks').')');
        $db->setQuery($query);

        $options = $db->loadObjectList();

        array_unshift($options, HTMLHelper::_('select.option', '', Text::_('JSELECT')));

        return array_merge(parent::getOptions(), $options);
    }
}
