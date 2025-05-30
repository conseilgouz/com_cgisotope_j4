<?php
/**
* CG Isotope Component  - Joomla 4.x/5.x Component
* Package			: CG ISotope
* copyright 		: Copyright (C) 2025 ConseilGouz. All rights reserved.
* license    		: https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
*
*/

namespace ConseilGouz\Component\CGIsotope\Site\Helper;

\defined('_JEXEC') or die;

use Joomla\CMS\Access\Access;
use Joomla\CMS\Application\ApplicationHelper;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Date\Date;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Router\Route;
use Joomla\Component\Content\Site\Model\ArticlesModel;
use Joomla\Component\Content\Site\Helper\RouteHelper;
use Joomla\Component\Tags\Site\Helper\RouteHelper as TagRouteHelper;
use Joomla\Component\Weblinks\Site\Helper\RouteHelper as WeblinkRouter;
use Joomla\Component\Fields\Administrator\Helper\FieldsHelper;
use Joomla\Component\Modules\Administrator\Helper\ModulesHelper;
use Joomla\Database\DatabaseInterface;
use Joomla\Registry\Registry;

class CGHelper extends ComponentHelper
{
    public static function getCGName()
    {
        return Text::_('CG_ISOTOPE');
    }
    public static function getCGVersion()
    {
        $db = Factory::getContainer()->get(DatabaseInterface::class);
        $query = $db->getQuery(true);
        $query->select('manifest_cache');
        $query->from($db->quoteName('#__extensions'));
        $query->where('name = "' . self::getCGName() . '"');
        $db->setQuery($query);
        $manifest = json_decode($db->loadResult(), true);
        return $manifest['version'];
    }
    public static function getParams($id, $model = null)
    {
        // Table::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_cgisotope/src/Table');
        $table = $model->getTable();
        $table->load((int)$id);
        $lesparams = json_decode($table->page_params, true);
        $lesparams['layouts'] = $table->sections;
        $lesparams['fieldslinks'] = $table->fieldslinks;
        $params = new Registry(json_encode($lesparams));
        $params->set('language', $table->language);
        return $params;
    }

    public static function getWebLinks($params, $weblinks_params, &$iso)
    {
        $categories = self::getAllCategories($params);
        $introtext_img  = $params->get('introtext_img', 'false');
        if (!$categories) {
            Factory::getApplication()->enqueueMessage(Text::_('Module Simple Isotope : pas de cat&eacute;gorie liens web'), 'notice');
            return false; // pas de categorie: on sort
        }
        $sel_cat = $params->get('wl_categories', array());
        foreach ($categories as $categorie) {
            if (count($sel_cat) > 0) {
                if (in_array($categorie->id, $sel_cat)) { // found in categories selection list
                    $result[$categorie->id] = self::getWebLinksCategorie($categorie->id, $categorie->alias, $introtext_img, $weblinks_params, $iso, $params);
                }
            } else { // take all categories
                $result[$categorie->id] = self::getWebLinksCategorie($categorie->id, $categorie->alias, $introtext_img, $weblinks_params, $iso, $params);
            }
        }
        return $result;
    }
    //$tags,&$tags_alias,&$tags_image,&$tags_note,&$tags_link,&$tags_parent,&$tags_parent_alias, &$article_tags,&$cats_lib, &$cats_alias, &$cats_note, &$cats_params,&$fields,&$article_fields, &$article_fields_names,&$alpha
    public static function getWebLinksCategorie($id, $alias, $introtext_img, $wl_params, &$iso, $params)
    {
        $introtext_img_link  = $params->get('introtext_img_link', 'false'); // image as link

        $db = Factory::getContainer()->get(DatabaseInterface::class);
        $query = $db->getQuery(true);
        $query->select('*')
            ->from('#__weblinks AS u')
            ->where('catid = '.(int)$id.' AND state = 1')
        ;
        $db->setQuery($query);
        $items = $db->loadObjectList();
        if ($items) {
            foreach ($items as $itemkey => $item) { // link to update click counter (visits)
                // $item->link	= 'index.php?option=com_weblinks&task=weblink.go&catid=' . $id . ':'.$alias.'&id=' . $item->id.':'.$item->alias;
                $item->link = Route::_(WeblinkRouter::getWeblinkRoute($item->id, $id, $item->language));
                $str = '?';
                if (strpos($item->link, '?')) {
                    $str = '&';
                }
                $item->link .= $str.'task=weblink.go'; // force open weblink
                $images  = json_decode($item->images);
                $item->introimg = ""; 			// image d'introduction
                if (!empty($images->image_first)) { // first img exists
                    $imgfloat = (empty($images->float_first)) ? $wl_params->get('float_first') : $images->float_first;
                    $float = 'style="float:'.$imgfloat.'"';
                    $uneimage = '<div class="iso_img_'.$imgfloat.'"><img src="'.htmlspecialchars($images->image_first).'" alt="'.htmlspecialchars($images->image_first_alt).'" '.$float.' class="iso_img_'.$imgfloat.'"></div>';
                    $item->introimg = $uneimage; // image d'introduction
                }
                if ($introtext_img == "true") { // first image
                    if (!empty($images->image_first)) { // first img exists
                        $item->description = self::cleanIntrotext($item->description, $params); // remove img
                        $item->description = $item->introimg.$item->description;
                        if ($introtext_img_link == "true") { // intro image as link
                            $item->description = preg_replace('/(<img[^>]+>)/', '<a href="'.$item->link.'">$1</a>', $item->description, 1); // only first image
                        }
                    } else { // no intro img: keep article imag if it exists
                        $item->description = self::cleanIntrotext_keepimg($item->description, $params);
                        if ($introtext_img_link == "true") { // intro image as link
                            $item->description = preg_replace('/(<img[^>]+>)/', '<a href="'.$item->link.'">$1</a>', $item->description, 1); // only first image
                        }
                    }
                } else { // no intro img needed
                    $item->description = self::cleanIntrotext($item->description, $params);
                }
                $tag_ok = false;
                if (!$params->get('tagsmissinghidden') || ($params->get('tagsmissinghidden') == "false") ||
                    !$params->get('tags') || !sizeOf($params->get('tags'))) { // don't check tags list
                    $tag_ok = true;
                }
                $authorised = Access::getAuthorisedViewLevels(Factory::getApplication()->getIdentity()->id);
                $iso->article_tags[$item->id] = self::getWebLinkTags($item->id, $authorised); // weblink's tags
                foreach ($iso->article_tags[$item->id] as $tag) {
                    if (!$tag_ok && in_array($tag->id, $params->get('tags'))) { // check tags list ?
                        $tag_ok = true; // tag in params list
                    }
                    if (!in_array($tag->tag, $iso->tags)) {
                        $iso->tags[] = $tag->tag;
                        $iso->tags_alias[$tag->tag] = $tag->alias;
                        $iso->tags_image[$tag->alias] = $tag->images;
                        $iso->tags_note[$tag->alias] = $tag->note;
                        $iso->tags_link[$tag->alias] = self::getTagLink($tag);
                        $iso->tags_parent[$tag->alias] = $tag->parent_title;
                        $iso->tags_parent_alias[$tag->alias] = $tag->parent_alias;
                    }
                    if (!isset($iso->tags_count[$tag->alias])) {
                        $iso->tags_count[$tag->alias] = 0;
                    }
                    $iso->tags_count[$tag->alias]++;
                }
                if (!$tag_ok) { // no tag for this article in tags list
                    unset($items[$itemkey]); // don't show it
                    continue;
                }

                $info_cat = self::getCategoryName($item->catid);
                if (!in_array($info_cat[0]->alias, $iso->cats_alias)) {
                    $iso->cats_lib[$item->catid] = $info_cat[0]->title;
                    $iso->cats_alias[$item->catid] = $info_cat[0]->alias;
                    $iso->cats_note[$item->catid] = $info_cat[0]->note;
                    $iso->cats_params[$item->catid] = $info_cat[0]->params;
                }
                if (!in_array(substr($item->title, 0, 1), $iso->alpha)) {
                    $iso->alpha[] = substr($item->title, 0, 1);
                }
                if (!isset($iso->cats_count[$item->catid])) {
                    $iso->cats_count[$item->catid] = 0;
                }
                $iso->cats_count[$item->catid]++;
            }
            return $items;
        }
        return false;
    }

    public static function getCategoryName($id)
    {
        $db = Factory::getContainer()->get(DatabaseInterface::class);
        $query = $db->getQuery(true);
        $query->select('*')
            ->from('#__categories ')
            ->where('id = '.(int)$id)
        ;
        $db->setQuery($query);
        return $db->loadObjectList();
    }
    public static function getAllCategories($params)
    {
        $app       = Factory::getApplication();
        $lang = $app->getLanguage()->getTag();
        $sqllang = 'AND (cat.language like "'.$lang.'" or cat.language like "*")';
        if ($params->get('language_filter', 'false') != 'false') {
            $sqllang = ''; // ignore lang
        }
        $db = Factory::getContainer()->get(DatabaseInterface::class);
        $query = $db->getQuery(true);
        if ($params->get('iso_entree', 'webLinks') == 'webLinks') {
            $query->select('distinct cat.id,cat.alias, count(cont.id) as count')
            ->from('#__categories as cat ')
            ->join('left', '#__weblinks cont on cat.id = cont.catid')
            ->where('extension like "com_weblinks" AND cat.published = 1 '.$sqllang.' and cat.access = 1 and cont.state = 1')
            ->group('catid')
            ;
        } else {
            $query->select('distinct cat.id,count(cont.id) as count')
            ->from('#__categories as cat ')
            ->join('left', '#__content cont on cat.id = cont.catid')
            ->where('extension like "com_content" AND cat.published = 1 '.$sqllang.' and cat.access = 1 and cont.state = 1')
            ->group('catid')
            ;
        }
        $db->setQuery($query);
        return $db->loadObjectList();
    }
    public static function getItems($categories, $params, &$iso, &$pagination)
    {
        $articles     = new ArticlesModel(array('ignore_request' => true));
        if ($articles) {
            $app       = Factory::getApplication();
            $appParams = $app->getParams();
            $articles->setState('params', $appParams);
            $articles->setState('list.start', $iso->start);
            $articles->setState('list.limit', $iso->limit);
            // $articles->setState('filter.published', 1);
            $access     = true; // check user access level
            $authorised = Access::getAuthorisedViewLevels(Factory::getApplication()->getIdentity()->id);
            $articles->setState('filter.access', $access); // check access
            $articles->setState('filter.viewlevels', $authorised);
            $catids = $iso->categories;
            $articles->setState('filter.category_id', $catids);
            $articles->setState('filter.category_id.include', true);
            $order = (string)$iso->order;
            if (strpos($order, 'ASC') !== false) {
                $articles->setState('list.direction', 'ASC');
                $order = substr($order, 0, strlen($order) - 3);
            }
            if (strpos($order, 'DESC') !== false) {
                $articles->setState('list.direction', 'DESC');
                $order = substr($order, 0, strlen($order) - 4);
            }
            if ($order == 'random') { // random order
                // $articles->setState('list.direction','RAND()');
                $order = 'RAND()';
            }
            $articles->setState('list.ordering', $order);
            $articles->setState('filter.featured', 'show');
            $articles->setState('filter.author_id', "");
            $articles->setState('filter.author_id.include', 1);
            $excluded_articles = '';
            $date_filtering = 'off';
            if ($params->get('language_filter', 'false') == 'false') {
                $articles->setState('filter.language', $app->getLanguageFilter());
            }
            $user = Factory::getApplication()->getIdentity();

            if ((!$user->authorise('core.edit.state', 'com_content')) && (!$user->authorise('core.edit', 'com_content'))) {
                // Filter on published for those who do not have edit or edit.state rights.
                $articles->setState('filter.published', 1);
            }
            $items = $articles->getItems();
            $pagination = $articles->getPagination(); //---------------------> pagination <-----------------
            $introtext_limit  = $params->get('introtext_limit', 100);
            $introtext_img  = $params->get('introtext_img', 'false');
            $introtext_img_link  = $params->get('introtext_img_link', 'false');
            $show_date_field  = $params->get('choixdate', 'modified');
            $show_date        = true;
            $show_introtext   = true;
            $show_category    =  true;
            $show_hits        =  0;
            $show_author      =  0;
            $show_date_format = 'Y-m-d H:i:s';

            PluginHelper::importPlugin('cgisotope');
            Factory::getApplication()->triggerEvent('onCGIsotopeFilter', array('com_cgisotope.article', &$items));

            foreach ($items as $itemkey => &$item) {
                $images  = json_decode($item->images);
                $item->introimg = ""; // image d'introduction
                if (!empty($images->image_intro)) { // into img exists
                    $uneimage = '<img src="'.htmlspecialchars($images->image_intro).'" alt="'.htmlspecialchars($images->image_intro_alt).'">';
                    $item->introimg = $uneimage;
                }
                $item->slug    = $item->id . ':' . $item->alias;
                $item->catslug = $item->catid . ':' . $item->category_alias;
                if ($access || in_array($item->access, $authorised)) {
                    $item->link = Route::_(RouteHelper::getArticleRoute($item->slug, $item->catid, $item->language));
                } else {
                    $menu      = $app->getMenu();
                    $menuitems = $menu->getItems('link', 'index.php?option=com_users&view=login');
                    if (isset($menuitems[0])) {
                        $Itemid = $menuitems[0]->id;
                    } elseif ($app->input->getInt('Itemid') > 0) {
                        $Itemid = $app->input->getInt('Itemid');
                    }
                    $item->link = Route::_('index.php?option=com_users&view=login&Itemid=' . $Itemid);
                }
                $item->displayDate = '';
                if ($show_date) {
                    if (!is_numeric($show_date_field)) {
                        $item->displayDate = HtmlHelper::_('date', $item->$show_date_field, $show_date_format);
                    }
                }

                if ($item->catid) {
                    $item->displayCategoryLink  = Route::_(RouteHelper::getCategoryRoute($item->catid));
                    $item->displayCategoryTitle = $show_category ? '<a href="' . $item->displayCategoryLink . '">' . $item->category_title . '</a>' : '';
                } else {
                    $item->displayCategoryTitle = $show_category ? $item->category_title : '';
                }
                $item->displayHits       = $show_hits ? $item->hits : '';
                $item->displayAuthorName = $show_author ? $item->author : '';
                if ($show_introtext) {
                    if ($introtext_limit == 0) { // no text
                        $item->introtext = "";
                    }
                    if ($introtext_img == "true") {
                        if (!empty($images->image_intro)) { // intro img exists
                            $item->introtext = self::cleanIntrotext($item->introtext, $params); // remove img
                            $item->introtext = $item->introimg.$item->introtext;
                            if ($introtext_img_link == "true") { // intro image as link
                                $item->introtext = preg_replace('/(<img[^>]+>)/', '<a href="'.$item->link.'">$1</a>', $item->introtext, 1); // only first image
                            }
                        } else { // no intro img: keep article imag if it exists
                            $item->introtext = self::cleanIntrotext_keepimg($item->introtext, $params);
                            if ($introtext_img_link == "true") { // intro image as link
                                $item->introtext = preg_replace('/(<img[^>]+>)/', '<a href="'.$item->link.'">$1</a>', $item->introtext, 1); // only first image
                            }
                        }
                    } else { // no intro img needed
                        $item->introtext = self::cleanIntrotext($item->introtext, $params);
                    }
                }
                if ($introtext_limit == 500) { // full text
                    $item->displayIntrotext = $show_introtext ? self::truncate($item->introtext, 0) : '';
                } else {
                    $item->displayIntrotext = $show_introtext ? self::truncate($item->introtext, $introtext_limit) : '';
                    if ($params->get('hide_more', 'false') == 'true') {
                        if (substr($item->displayIntrotext, -3, 3) == '...') { // suppress ... if present
                            $item->displayIntrotext = substr($item->displayIntrotext, 0, -3);
                        }
                    } else {
                        if (substr($item->displayIntrotext, -3, 3) == '...') { // ... are present, add a class
                            $item->displayIntrotext = substr($item->displayIntrotext, 0, -3);
                            $item->displayIntrotext .= '<span class="iso_3points">...</span>';
                        }
                    }
                }
                $item->displayReadmore  = $item->alternative_readmore;
                $iso->article_tags[$item->id] = self::getArticleTags($item->id, $authorised); // article's tags
                $tag_ok = false;
                if (!$params->get('tagsmissinghidden') || ($params->get('tagsmissinghidden') == "false") ||
                    !$params->get('tags') || !sizeOf($params->get('tags'))) { // don't check tags list
                    $tag_ok = true;
                }
                foreach ($iso->article_tags[$item->id] as $tag) {
                    if (!$tag_ok && in_array($tag->id, $params->get('tags'))) {// check tags list ?
                        $tag_ok = true; // tag in params list
                    }
                    if (!in_array($tag->tag, $iso->tags)) {
                        $iso->tags[] = $tag->tag;
                        $iso->tags_alias[$tag->tag] = $tag->alias;
                        $iso->tags_image[$tag->alias] = $tag->images;
                        $iso->tags_note[$tag->alias] = $tag->note;
                        $iso->tags_link[$tag->alias] = self::getTagLink($tag);
                        $iso->tags_parent[$tag->alias] = $tag->parent_title;
                        $iso->tags_parent_alias[$tag->alias] = $tag->parent_alias;
                    }
                    if (!isset($iso->tags_count[$tag->alias])) {
                        $iso->tags_count[$tag->alias] = 0;
                    }
                    $iso->tags_count[$tag->alias]++;
                }
                if (!$tag_ok) { // no tag for this article in tags list
                    unset($items[$itemkey]); // don't show it
                    continue;
                }
                $test = FieldsHelper::getFields('com_content.article', $item);

                foreach ($test as $field) {
                    $lang = $params->get('language', '*');
                    if (($lang != '*') && ($field->language != '*') && ($lang != $field->language)) {
                        continue;
                    }

                    if (property_exists($field, 'value')) {
                        $val = $field->value;
                        if (!is_array($field->value)) {
                            $alias_sort = ApplicationHelper::stringURLSafe((string) $val);
                        }
                    }
                    if (($field->id == $iso->rangefields) && ($field->value != "")) { // min/max range values
                        $iso->rangetitle = $field->title;
                        $iso->rangelabel = $field->label;
                        $iso->rangedesc = $field->description;
                        if (($field->value < $iso->minrange) || ($iso->minrange == '')) {
                            $iso->minrange = $field->value;
                        }
                        if (($field->value > $iso->maxrange) || ($iso->minrange == '')) {
                            $iso->maxrange = $field->value;
                        }
                    }
                    if (in_array($field->id, $iso->calendarfields) && ($field->value != "")) { // calendar values
                        if ($field->type == 'calendar') {
                            $date = HTMLHelper::_('date', $field->value, 'Y-m-d');
                            if (!in_array($date, $iso->calendar)) {
                                $iso->calendar[] = $date;
                            }
                        } elseif ($field->type == 'subform') { // repeatable with calendar ?
                            $param = json_decode($field->fieldparams);
                            $fieldname = 'field'.$param->options->option0->customfield;
                            $afieldtype = self::get_one_field_type($param->options->option0->customfield);
                            if ($afieldtype == "calendar") {
                                $dates = json_decode($field->rawvalue);

                                foreach ($dates as $unedate) {
                                    $date = HTMLHelper::_('date', $unedate->$fieldname, 'Y-m-d');
                                    if (!in_array($date, $iso->calendar)) {
                                        $iso->calendar[] = $date;
                                    }
                                }

                            }

                        }
                    }
                    $param = json_decode($field->fieldparams);
                    if (property_exists($param, 'options')) { // fields with options
                        $ix_field = 0;
                        foreach ($param->options as $option) {
                            if (is_array($field->value)) { // multiple field values
                                foreach ($field->value as $avalue) {
                                    if ($option->value == $avalue) {
                                        $val = $option->name;
                                        $alias_sort = ApplicationHelper::stringURLSafe((string) $ix_field.'_'.$option->name);
                                        $alias =  ApplicationHelper::stringURLSafe((string) $val);
                                        if ($alias == "") {
                                            continue;
                                        }
                                        if (!property_exists($field, 'name')) {
                                            continue;
                                        }
                                        $alias = $field->name.'_'.$alias;
                                        $iso->article_fields[$item->id][$field->title][] = $alias;
                                        $iso->article_fields_names[$item->id][$field->name][] = $alias;
                                        if (!in_array($alias, $iso->fields)) {
                                            $iso->fields[$alias] = self::field_info($item, $val, $alias, $alias_sort, $field, $params);
                                        }
                                        if (!isset($iso->fields_count[$alias])) {
                                            $iso->fields_count[$alias] = 0;
                                        }
                                        $iso->fields_count[$alias]++;
                                    }
                                }

                            } elseif (property_exists($option, 'value') && $option->value == $field->value) {
                                $val = $option->name;
                                $alias_sort = ApplicationHelper::stringURLSafe((string) $ix_field.'_'.$val);
                                $alias =  ApplicationHelper::stringURLSafe((string) $val);
                                if ($alias == "") {
                                    continue;
                                }
                                $alias = $field->name.'_'.$alias;
                                $iso->article_fields[$item->id][$field->title] = $alias;
                                $iso->article_fields_names[$item->id][$field->name] = $alias;
                                if (!in_array($alias, $iso->fields)) {
                                    $iso->fields[$alias] = self::field_info($item, $val, $alias, $alias_sort, $field, $params);
                                }
                                if (!isset($iso->fields_count[$alias])) {
                                    $iso->fields_count[$alias] = 0;
                                }
                                $iso->fields_count[$alias]++;
                                $ix_field += 1;
                            } elseif ($field->type == 'subform') {
                                $dates = json_decode($field->rawvalue);

                            }
                        }
                    } else { // not an option field
                        if (is_string($val) && $val) {  // 30/09/2021 : ignore if not string
                            $alias =  ApplicationHelper::stringURLSafe((string)$val);
                            $alias = $field->name.'_'.$alias;
                            if (!in_array($alias, $iso->fields)) {
                                $alias =  ApplicationHelper::stringURLSafe((string)$val);
                                if ($alias == "") {
                                    continue;
                                }
                                $iso->article_fields[$item->id][$field->title] = $alias;
                                $iso->article_fields_names[$item->id][$field->name] = $alias;
                                $iso->fields[$alias] = self::field_info($item, $val, $alias, $alias_sort, $field, $params);
                            }
                            if (!isset($iso->fields_count[$alias])) {
                                $iso->fields_count[$alias] = 0;
                            }
                            $iso->fields_count[$alias]++;
                        }
                    }
                    if (is_numeric($show_date_field) && ($field->id == $show_date_field)) {
                        $item->displayDate = HTMLHelper::_('date', $field->value, $show_date_format);
                    }
                    if (!in_array(substr($item->title, 0, 1), $iso->alpha)) {
                        $iso->alpha[] = substr($item->title, 0, 1);
                    }

                }
                if (!in_array($item->category_alias, $iso->cats_alias)) {
                    $iso->cats_lib[$item->catid] = $item->category_title;
                    $iso->cats_alias[$item->catid] = $item->category_alias;
                    $infos = self::getCategoryName($item->catid);
                    $iso->cats_note[$item->catid] = $infos[0]->note;
                    $iso->cats_params[$item->catid] = $infos[0]->params;
                }
                if (!isset($iso->cats_count[$item->catid])) {
                    $iso->cats_count[$item->catid] = 0;
                }
                $iso->cats_count[$item->catid]++;
            }
            return $items;
        } else {
            return false;
        }

    }
    private static function field_info($item, $value, $alias, $alias_sort, $field, $params, $type = 'article')
    {
        PluginHelper::importPlugin('fields');
        $obj = new \stdClass();
        $obj->alias = $alias;
        $obj->val = $value;
        $obj->alias_sort = $alias_sort;
        $obj->field_id = $field->id;
        $obj->field_title = $field->title;
        $obj->field_label = $field->label;
        $obj->field_name = $field->name;
        if ($type == 'article') {
            $context = "com_content.article";
        } else { // weblink
            $context = "com_weblinks.weblink";
        }
        $field_alias = ApplicationHelper::stringURLSafe((string) $field->title);
        if ($field->type == 'color') {
            $obj->render = $value;
        } elseif (!is_array($field->value)) {
            $atext = Factory::getApplication()->triggerEvent('onCustomFieldsPrepareField', array($context, $item,$field))[0];
            if (!$atext) {
                $atext = $field->value;
            }
            $obj->render = '<span class="iso_field_'.$field_alias.'">'.$atext.'</span>';
        } else {
            $obj->render = $value;
        }
        $params_links = json_decode($params->get('fieldslinks'));
        if ($params_links) { // fields links
            $model = Factory::getApplication()->bootComponent('com_fields')
              ->getMVCFactory()->createModel('Field', 'Administrator', ['ignore_request' => true]);

            $obj->parent = "";
            $obj->child = "";
            foreach ($params_links as $key => $value) {
                if ($value->fieldchild == $field->id) {
                    $val = $model->getFieldValue($value->fieldparent, $item->id);
                    $obj->parent = ApplicationHelper::stringURLSafe((string) $val);
                }
                if ($value->fieldparent == $field->id) {
                    $obj->child = $value->fieldchild;
                }
            }
        } else {
            $obj->parent = "";
            $obj->child = "";
        }
        return $obj;
    }
    private static function get_one_field_type($id)
    {
        $db = Factory::getContainer()->get(DatabaseInterface::class);
        $query = $db->getQuery(true);
        // Construct the query
        $query->select('type')
        ->from('#__fields')
        ->where('id = '.(int)$id)
        ;
        $db->setQuery($query);
        return $db->loadResult();

    }
    public static function getTagLink($tag)
    {
        $link = Route::_(TagRouteHelper::getComponentTagRoute($tag->id . ':' . $tag->alias, $tag->language));
        return $link;
    }

    public static function getTagTitle($id)
    {
        $db = Factory::getContainer()->get(DatabaseInterface::class);
        $query = $db->getQuery(true);
        // Construct the query
        $query->select('tags.title as tag, tags.alias as alias,tags.note as note,tags.language as language,parent.title as parent_title, parent.alias as parent_alias')
            ->from('#__tags as tags')
            ->innerJoin('#__tags as parent on parent.id = tags.parent_id')
            ->where('tags.id = '.(int)$id)
        ;
        $db->setQuery($query);
        return $db->loadObjectList();
    }
    public static function getTagAccess($id, $authorised)
    {
        $db = Factory::getContainer()->get(DatabaseInterface::class);
        $query = $db->getQuery(true);
        // Construct the query
        $query->select('tags.title as tag')
            ->from('#__tags as tags')
            ->where('tags.id = '.(int)$id.' AND tags.access IN ('.implode(',', $authorised).')')
        ;
        $db->setQuery($query);
        return $db->loadResult();
    }
    public static function getArticleTags($id, $authorised)
    {
        $db = Factory::getContainer()->get(DatabaseInterface::class);
        $query = $db->getQuery(true);
        $query->select('tags.title as tag, tags.alias as alias, tags.images as images, tags.note as note,tags.language as language, parent.title as parent_title, parent.alias as parent_alias,tags.id as id')
            ->from('#__contentitem_tag_map as map ')
            ->innerJoin('#__content as c on c.id = map.content_item_id')
            ->innerJoin('#__tags as tags on tags.id = map.tag_id')
            ->innerJoin('#__tags as parent on parent.id = tags.parent_id')
            ->where('c.id = '.(int)$id.' AND map.type_alias like "com_content%" AND tags.access IN ('.implode(',', $authorised).')')
        ;
        $db->setQuery($query);
        return $db->loadObjectList();
    }
    // pagination : add tags information from tags_list
    public static function getMissingTags($tags_list, $authorised)
    {
        $db = Factory::getContainer()->get(DatabaseInterface::class);
        $query = $db->getQuery(true);
        $query->select('DISTINCT tags.title as tag, tags.alias as alias, tags.note as note,tags.language as language, tags.images as images, parent.title as parent_title, parent.alias as parent_alias, tags.id')
        ->from('#__contentitem_tag_map as map ')
        ->innerJoin('#__tags as tags on tags.id = map.tag_id')
        ->innerJoin('#__tags as parent on parent.id = tags.parent_id')
        ->where('tags.id IN ('.implode(',', $tags_list).') AND map.type_alias like "com_content%" AND tags.access IN ('.implode(',', $authorised).')')
        ;
        $db->setQuery($query);
        return $db->loadObjectList();
    }
    public static function getWebLinkTags($id, $authorised)
    {
        $db = Factory::getContainer()->get(DatabaseInterface::class);
        $query = $db->getQuery(true);
        $query->select('tags.title as tag, tags.alias as alias, tags.images as images,tags.language as language, tags.note as note,parent.title as parent_title, parent.alias as parent_alias,tags.id as id')
            ->from('#__contentitem_tag_map as map ')
            ->innerJoin('#__weblinks as w on w.id = map.content_item_id')
            ->innerJoin('#__tags as tags on tags.id = map.tag_id')
            ->innerJoin('#__tags as parent on parent.id = tags.parent_id')
            ->where('w.id = '.(int)$id.' AND map.type_alias like "com_weblinks%" AND tags.access IN ('.implode(',', $authorised).')')
        ;
        $db->setQuery($query);
        return $db->loadObjectList();
    }
    /*-------------------------------------- general functions -------------------------------------------*/
    public static function cleanIntrotext($introtext, $params)
    {
        if ($params->get('introtext_leave_tags', '0') == '0') {
            $introtext = str_replace('<p>', ' ', $introtext);
            $introtext = str_replace('</p>', ' ', $introtext);
            $introtext = strip_tags($introtext, '<a><em><strong><br>');
            $introtext = trim($introtext);
        }
        return $introtext;
    }
    public static function cleanIntrotext_keepimg($introtext, $params)
    {
        if ($params->get('introtext_leave_tags', '0') == '0') {
            $introtext = str_replace('<p>', ' ', $introtext);
            $introtext = str_replace('</p>', ' ', $introtext);
            $introtext = strip_tags($introtext, '<img><a><em><strong><br>');
            $introtext = trim($introtext);
        }
        return $introtext;
    }

    public static function truncate($html, $maxLength = 0)
    {
        $baseLength = strlen($html);
        $ptString = HTMLHelper::_('string.truncate', $html, $maxLength, $noSplit = true, $allowHtml = false);
        for ($maxLength; $maxLength < $baseLength;) {
            // use truncateComplex to handle self closing tags like <br />
            $htmlString = HTMLHelper::_('string.truncateComplex', $html, $maxLength, $noSplit = true);
            $htmlStringToPtString = HTMLHelper::_('string.truncate', $htmlString, $maxLength, $noSplit = true, $allowHtml = false);
            if ($ptString == $htmlStringToPtString) {
                return $htmlString;
            }
            $diffLength = strlen($ptString) - strlen($htmlStringToPtString);
            $maxLength += $diffLength;
            if ($baseLength <= $maxLength || $diffLength <= 0) {
                return $htmlString;
            }
        }
        return $html;
    }
    // check if PhocaCount exists in article. If so, get it.
    public static function getArticlePhocaCount($text)
    {
        $regex_one		= '/(\{phocacount\s*)(.*?)(\})/si';
        $regex_all		= '/{phocacount\s*.*?}/si';
        $matches 		= array();
        $count 			= 0;
        $count_matches	= preg_match_all($regex_all, $text, $matches, PREG_OFFSET_CAPTURE | PREG_PATTERN_ORDER);
        if ($count_matches == 0) {
            return '?';
        }
        for ($i = 0; $i < $count_matches; $i++) {
            $phocacount	= $matches[0][$i][0];
            preg_match($regex_one, $phocacount, $phocacount_parts);
            $values = explode("=", $phocacount_parts[2], 2);
            $id				= $values[1];
            $db 			= Factory::getContainer()->get(DatabaseInterface::class);
            $query = 'SELECT a.hits'
                    . ' FROM #__phocadownload AS a';
            $query .= ' WHERE a.id = '.(int)$id;
            $db->setQuery($query);
            $item = $db->loadResult();
            if (!empty($item)) {
                $count += $item;
            }
        }
        return $count;
    }
    // look for {notnull} or {!}
    public static function checkNullFields($perso, $item, $phocacount, $deb = "{", $end = "}")
    {
        $regexopen = '/\\'.$deb.'((?:notnull)\b(.*)|!)\\'.$end.'/siU';
        if (!preg_match($regexopen, $perso)) {
            return $perso; // no update
        }
        while (preg_match($regexopen, $perso, $matches, PREG_OFFSET_CAPTURE)) {
            $replace_deb = $matches[0][1];
            $replace_len = strlen($matches[0][0]);
            $regexclose = '/\\'.$deb.'\/'.$matches[1][0].'\\'.$end.'/siU';
            preg_match($regexclose, $perso, $matchesclose, PREG_OFFSET_CAPTURE);
            $content_deb = $replace_deb + $replace_len;
            $content_len = $matchesclose[0][1] - $content_deb;
            $content = substr($perso, $content_deb, $content_len);
            $replace_len += $content_len + strlen($matchesclose[0][0]);
            $regexone = '/\\'.$deb.'(.*)\\'.$end.'/siU';
            preg_match($regexone, $content, $matchesone, PREG_OFFSET_CAPTURE);
            $one = $matchesone[1][0];
            $onefield = explode(',', $one);
            $field = $onefield[0];
            $subfield = isset($onefield[1]) ? $onefield[1] : null ;
            if (isset($item->$field)) { // is a db field ?
                if (isset($subfield)) { // sub field ?
                    $content = $item->$field;
                    $subs =  json_decode($content);
                    if (empty($subs->$subfield)) {
                        $content = "";
                    } else {
                        $content = $subs->$subfield;
                    }
                } elseif ($field == 'urls') {
                    $ret = self::getUrls($item->urls, $item->params);
                    if ($ret) {
                        $content = str_replace($matchesone[0][0], $ret, $content);
                    } else { // empty field
                        $content = "";
                    }
                } elseif ($item->$field) {
                    $content = str_replace($matchesone[0][0], $item->$field, $content);
                } else { // empty field
                    $content = "";
                }
            } elseif (($field == 'ratingcnt') && ($item->rating_count == "0")) {
                $content = "";
            } elseif (($field == 'count') && ($phocacount == '?')) {
                $content = "";
            }
            $perso = substr($perso, 0, $replace_deb).$content.substr($perso, $replace_deb + $replace_len);
        }
        return $perso;
    }
    // look for {nofield} or {!f}
    public static function checkNoField($perso, $deb = '{', $end = '}')
    {
        $regexopen = '/\\'.$deb.'((?:nofield)\b(.*)|(?:!f)\b(.*))\\'.$end.'/siU';
        if (!preg_match($regexopen, $perso)) {
            return $perso; // no update
        }
        while (preg_match($regexopen, $perso, $matches, PREG_OFFSET_CAPTURE)) {
            $replace_deb = $matches[0][1];
            $replace_len = strlen($matches[0][0]);

            $regexclose = '/\\'.$deb.'\/'.$matches[1][0].'\\'.$end.'/siU';
            preg_match($regexclose, $perso, $matchesclose, PREG_OFFSET_CAPTURE);
            $content_deb = $replace_deb + $replace_len;
            $content_len = $matchesclose[0][1] - $content_deb;
            $content = substr($perso, $content_deb, $content_len);
            $replace_len += $content_len + strlen($matchesclose[0][0]);
            if ((strpos($content, $deb.'field ') !== false) ||
                (strpos($content, $deb) !== false)) {
                $content = "";
            }
            $perso = substr($perso, 0, $replace_deb).$content.substr($perso, $replace_deb + $replace_len);
        }
        return $perso;
    }
    // look for field to be found in datatbase
    public static function checkDBFields($item, $perso, $deb = "{", $end = "}")
    {
        $regexopen = '/\\'.$deb.'(.*)\\'.$end.'/siU';
        $count_matches	= preg_match_all($regexopen, $perso, $matches, PREG_OFFSET_CAPTURE | PREG_PATTERN_ORDER);

        if (!$count_matches) {
            return $perso; // no update
        }
        // replace shortcut by different size field, so make this reversed
        for ($i = $count_matches - 1; $i >= 0; $i--) {
            $replace_deb = $matches[0][$i][1];
            $replace_len = strlen($matches[0][$i][0]);
            $one = $matches[1][$i][0];
            $onefield = explode(',', $one);
            $field = $onefield[0];
            $subfield = isset($onefield[1]) ? $onefield[1] : null ;
            $content = $matches[0][$i][0]; // keep content
            if (isset($item->$field)) { // is a db field ?
                $content = $item->$field;
                if (isset($subfield)) { // sub field ?
                    $subs =  json_decode($content);
                    if (!empty($subs->$subfield)) {
                        $content = $subs->$subfield;
                    }
                } elseif ($field == 'urls') {
                    $content = self::getUrls($item->urls, $item->params);
                }
            }
            $perso = substr($perso, 0, $replace_deb).$content.substr($perso, $replace_deb + $replace_len);
        }
        return $perso;
    }
    // from components/com_content/tmpl/article/default_links.php
    public static function getUrls($urls, $params)
    {
        $urls = json_decode($urls);
        if (!$urls || (empty($urls->urla) && empty($urls->urlb) && empty($urls->urlc))) {
            return "";
        }
        $ret = '<ul class="iso_links">';
        $urlarray = [
            [$urls->urla, $urls->urlatext, $urls->targeta, 'a'],
            [$urls->urlb, $urls->urlbtext, $urls->targetb, 'b'],
            [$urls->urlc, $urls->urlctext, $urls->targetc, 'c']
            ];
        foreach ($urlarray as $url) :
            $link = $url[0];
            $label = $url[1];
            $target = $url[2];
            $id = $url[3];

            if (! $link) :
                continue;
            endif;

            // If no label is present, take the link
            $label = $label ?: $link;

            $ret .= '<li class="iso_link_'.$id.'">';
            // If no target is present, use the default
            if (!$target && $target != 0) { // default
                $comparams = ComponentHelper::getParams('com_content');
                $target = $comparams->get('target'.$id);
            }
            switch ($target) {
                case 1:
                    // Open in a new window
                    $ret .= '<a href="' . htmlspecialchars($link, ENT_COMPAT, 'UTF-8') . '" target="_blank" rel="nofollow noopener noreferrer">' .
                        htmlspecialchars($label, ENT_COMPAT, 'UTF-8') . '</a>';
                    break;

                case 2:
                    // Open in a popup window
                    $attribs = 'toolbar=no,location=no,status=no,menubar=no,scrollbars=yes,resizable=yes,width=600,height=600';
                    $ret .=  "<a href=\"" . htmlspecialchars($link, ENT_COMPAT, 'UTF-8') . "\" onclick=\"window.open(this.href, 'targetWindow', '" . $attribs . "'); return false;\" rel=\"noopener noreferrer\">" .
                        htmlspecialchars($label, ENT_COMPAT, 'UTF-8') . '</a>';
                    break;
                case 3:
                    $ret .= '<a href="' . htmlspecialchars($link, ENT_COMPAT, 'UTF-8') . '" rel="noopener noreferrer" data-bs-toggle="modal" data-bs-target="#linkModal">' .
                        htmlspecialchars($label, ENT_COMPAT, 'UTF-8') . ' </a>';
                    $ret .= HTMLHelper::_(
                        'bootstrap.renderModal',
                        'linkModal',
                        [
                            'url'    => $link,
                            'title'  => $label,
                            'height' => '100%',
                            'width'  => '100%',
                            'modalWidth'  => '500',
                            'bodyHeight'  => '500',
                            'footer' => '<button type="button" class="btn btn-secondary" data-bs-dismiss="modal" aria-hidden="true">'
                                . \Joomla\CMS\Language\Text::_('JLIB_HTML_BEHAVIOR_CLOSE') . '</button>'
                        ]
                    );
                    break;

                default:
                    // Open in parent window
                    $ret .= '<a href="' . htmlspecialchars($link, ENT_COMPAT, 'UTF-8') . '" rel="nofollow">' .
                        htmlspecialchars($label, ENT_COMPAT, 'UTF-8') . ' </a>';
                    break;
            }
            $ret .= '</li>';
        endforeach;
        $ret .= '</ul>';
        return $ret;
    }
    // Check a tag is in the selected tags list
    public static function checkTagSet($tag, $filter)
    {
        foreach ($filter as $onefilter) {
            if ($onefilter[0]->tag == $tag) {
                return true;
            }
        }
        return false;
    }
    //---------------------------------------------------- Create Fields buttons	----------------------------------------------//
    public static function create_buttons($fields, $group_lib, $onefilter, $params, $col_width, $button_bootstrap, $splitfieldstitle, $group_title, $group_id, $comid = 0, $fieldsfiltercount = 'false', $fields_count = [])
    {


        $params_fields = $params->get('displayfields', array());
        $libfilter = Text::_('CG_ISO_LIBFILTER');
        $liball = Text::_('CG_ISO_LIBALL');
        $splitfields = $params->get('displayfiltersplitfields', 'false');
        $displayfilterfields =  $params->get('displayfilterfields', 'button');
        $libmulti = Text::_('CG_ISO_LIBLISTMULTI');
        $aliasorder = array();
        //		$group_id = 0;
        foreach ($onefilter as $key => $one) {
            if ((count($params_fields) == 0) ||  (in_array($fields[$key]->field_id, $params_fields))) {
                $obj = $fields[$key];
                $aliasorder[$key] = $obj->alias_sort;
                //	              $group_id = $obj->field_id;
            }
        }
        asort($aliasorder, SORT_STRING | SORT_FLAG_CASE | SORT_NATURAL); // alpha order
        $onefilter = $aliasorder;
        $result = "";
        if (($displayfilterfields == "button")  || ($displayfilterfields == "multi") || ($displayfilterfields == "multiex")) {
            if ($splitfieldstitle == "true") {
                $result .= "<p class='iso_fields_title ".$col_width."' data-filter-group='".$group_lib."' data-group-id='".$group_id."' data-group-id='".$group_id."' data='".$comid."'>". Text::_($group_title)."<br/>";
            }
            $first_time = true;
            foreach ($onefilter as $key => $filter) {
                $obj = $fields[$key];
                $fieldcount = '';
                if ($fieldsfiltercount == 'true') {
                    $fieldcount = '<span class="field-count badge bg-info">'.$fields_count[$key].'</span>';
                }
                if ($first_time) {
                    $result .=  '<button class="'.$button_bootstrap.'  iso_button_tout isotope_button_first is-checked filter-button-group-'.$group_lib.'" data-sort-value="*" data-parent="'.$obj->parent.'" data-child="'.$obj->child.'" />'.$liball.'</button>';
                    $first_time = false;
                }
                $aff_alias = $obj->alias;
                $aff = $obj->render;
                if (!is_null($aff)) {
                    $result .=  '<button class="'.$button_bootstrap.'  iso_button_'.$group_lib.'_'.$aff_alias.'" data-sort-value="'.$aff_alias.'" data-parent="'.$obj->parent.'" data-child="'.$obj->child.'"/>'. Text::_($aff).$fieldcount.'</button>';
                }
            }
            if ($splitfieldstitle == "true") {
                $result .= "</p>";
            }

        } else { // list
            Factory::getApplication()->getDocument()->getWebAssetManager()
                ->useScript('webcomponent.field-fancy-select')
                ->usePreset('choicesjs');
            $selectAttr = array('allowHTML:true');
            $multiple = "";
            $multiple_id = "";
            if ($displayfilterfields == "listmulti") {
                $libmulti = Text::_('CG_ISO_LIBLISTMULTI');
                $multiple = "  place-placeholder='".$libmulti."'";
                $selectAttr[] = ' multiple';
                $multiple_id = "fields-";
            }
            $attributes = array(
                'class="isotope_select"',
                ' data-filter-group="'.$group_lib.'"',
                ' id="isotope-select-'.$group_lib.'"',
                ' allowHTML="true"'
            );
            $name = "isotope-select-".$multiple_id.$group_id;

            $first_time = true;
            foreach ($onefilter as $key => $filter) {
                $obj = $fields[$key];
                $fieldcount = '';
                if ($fieldsfiltercount == 'true') {
                    $fieldcount = ' ('.$fields_count[$key].')';
                }
                if ($first_time) {
                    $options['']['items'][] = ModulesHelper::createOption('', $liball);
                    $first_time = false;
                }
                $aff_alias = $obj->alias;
                $aff = strip_tags($obj->render);
                if (!is_null($aff)) {
                    $options['']['items'][] = ModulesHelper::createOption($aff_alias, Text::_($aff).$fieldcount);
                }
            }

            $result .= "<div class='iso_fields_title  ".$col_width." '><p>";
            if ($splitfieldstitle == "true") {
                $result .= Text::_($group_title).' : ';
            } else {
                $result .=  '<span class="hidden-phone" >'.$libfilter.' : </span>';
            }
            $result .=  '</p><joomla-field-fancy-select '.implode(' ', $attributes).'>';
            $result .= HTMLHelper::_('select.groupedlist', $options, $name, array('id'          => $name,'list.select' => null,'list.attr'   => implode(' ', $selectAttr)));
            $result .= '</joomla-field-fancy-select></div>';

        }
        return $result;
    }
    //--------------------------Language buttons --------------------------------//
    public static function create_language_buttons($iso, $button_bootstrap)
    {
        $result = "";
        $liball = Text::_('CG_ISO_LIBALL');
        $result .=  '<button class="'.$button_bootstrap.'  iso_button_lang_tout isotope_button_first is-checked" data-sort-value="*">'.$liball.'</button>';
        foreach ($iso->languagelist as $language) {
            if ($language->image) {
                $result .= "<button class='".$button_bootstrap." iso_button_lang_".$language->lang_code."' data-sort-value='".$language->lang_code."' title='".$language->title_native."'>";
                $result .= HTMLHelper::_('image', 'mod_languages/' . $language->image . '.gif', '', null, true);
                $result .= "</button>";
            }
        }
        return $result;
    }
    //--------------------------Alpha buttons --------------------------------//
    public static function create_alpha_buttons($iso, $button_bootstrap)
    {
        $result = "";
        $liball = Text::_('CG_ISO_LIBALL');
        $result .=  '<button class="'.$button_bootstrap.'  iso_button_alpha_tout isotope_button_first is-checked" data-sort-value="*">'.$liball.'</button>';
        asort($iso->alpha);
        foreach ($iso->alpha as $alpha) {
            $result .= "<button class='".$button_bootstrap." iso_button_alpha_".$alpha."' data-sort-value='".$alpha."' title='".$alpha."'>".$alpha;
            $result .= "</button>";
        }
        return $result;
    }
    //--------------------------Calendar buttons --------------------------------//
    public static function create_calendar_buttons($iso, $button_bootstrap) // filter-button-group-calendar
    {
        $result = "";
        $date = new Date();
        for ($i = 1 ;$i <= 365;$i++) {
            $cls_calendar = " disabled "; // suppose not found
            $ladate = $date->format('Y-m-d');
            if (in_array($ladate, $iso->calendar)) {
                $cls_calendar = ' has_date iso_button_calendar_'.$ladate;
            }
            $txt_calendar = $date->format('D').'<br>'.$date->format('d');

            $result .= "<button class='iso_calendar ".$button_bootstrap." ".$cls_calendar."' data-sort-value='".$ladate."' title='".$date->format('d-m-Y')."'>".$txt_calendar;
            $result .= "</button>";
            $date->modify('+1 day');
        }
        return $result;
    }
}
