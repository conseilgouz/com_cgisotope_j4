<?php
/**
* CG Isotope Component  - Joomla 4.x/5.x Component
* Package			: CG ISotope
* copyright 		: Copyright (C) 2025 ConseilGouz. All rights reserved.
* license    		: https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
*/

namespace ConseilGouz\Component\CGIsotope\Administrator\View\Pages;

// No direct access
\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\JsonView as BaseHtmlView;
use Joomla\CMS\Response\JsonResponse;
use Joomla\CMS\Session\Session;
use Joomla\Database\DatabaseInterface;

class JsonView extends BaseHtmlView
{
    public function display($tpl = null)
    {
        if (!Session::checkToken('get')) {
            echo new JsonResponse(null, Text::_('JINVALID_TOKEN'), true);
            exit;
        }
        $input = Factory::getApplication()->input;
        $pageid = (int)$input->get('page');
        if (!is_numeric($pageid)) {
            return;
        }
        $type = $input->get('type');
        if (($type != 'cat') && ($type != 'tag')) {
            return;
        }
        $db = Factory::getContainer()->get(DatabaseInterface::class);
        $query = $db->getQuery(true);
        $query->select('page_params as params')
        ->from('#__cgisotope_page')
        ->where('id = :id')
        ->where('state = 1')
        ->bind(':id', $pageid, \Joomla\Database\ParameterType::INTEGER);

        $db->setQuery($query);
        $result = $db->loadResult();
        if (!$result) {
            return;
        }
        $query->clear();

        $params = json_decode($result);
        $entree = $params->iso_entree;
        if ($entree == 'articles') {
            $com = 'com_content';
        } else {
            $com = 'com_weblinks';
        }
        if ($type == 'cat') {
            $query = $db->getQuery(true);
            $query->select('id,title')
            ->from('#__categories')
            ->where('extension = '.$db->quote($com))
            ->where('published = 1');
            if ($entree == 'articles') { // articles
                if ($list = $params->categories) {
                    $query->where('id in ('.implode(",", $list).')');
                }
            } else { // weblinks
                if ($list = $params->wl_categories) {
                    $query->where('id in ('.implode(",", $list).')');
                }
            }
            $db->setQuery($query);
            $result = $db->loadObjectList();
            if (!$result) {
                return;
            }
            $ret = json_encode($result);
            echo $ret;
            exit;
        }
        if ($type == 'tag') {
            $query->select('id,title')
            ->from('#__tags')
            ->where('published = 1');
            if ($list = $params->tags) {
                $query->where('id in ('.implode(",", $list).')');
            }
            $db->setQuery($query);
            $result = $db->loadObjectList();
            if (!$result) {
                return;
            }
            $ret = json_encode($result);
            echo $ret;
            exit;
        }

    }
}
