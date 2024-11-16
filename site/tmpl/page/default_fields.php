<?php
/**
* CG Isotope Component  - Joomla 4.x/5.x Component
* Version			: 4.3.11
* Package			: CG ISotope
* copyright 		: Copyright (C) 2024 ConseilGouz. All rights reserved.
* license    		: https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
* From              : isotope.metafizzy.co
*/
defined('_JEXEC') or die;
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\Component\Modules\Administrator\Helper\ModulesHelper;
use Joomla\CMS\Session\Session;
use ConseilGouz\Component\CGIsotope\Site\Helper\CGHelper;

PluginHelper::importPlugin('content');
PluginHelper::importPlugin('cgisotope');

$uri = Uri::getInstance();
$app = Factory::getApplication();
$user = $app->getIdentity();

$com_id = $app->input->getInt('Itemid');
$comfield = ''.URI::base(true).'/media/com_cgisotope/';

$defaultdisplay = $this->iso_params->get('defaultdisplay', 'date_desc');
$displaysortinfo = $this->iso_params->get('displaysortinfo', 'show');
$article_cat_tag = $this->iso_params->get('cat_or_tag');
$tagsfilterorder = $this->iso_params->get('tagsfilterorder', 'false');
$tagsfilterimg =  $this->iso_params->get('tagsfilterimg', 'false');
$tagsfiltercount =  $this->iso_params->get('tagsfiltercount', 'false');
$tagsfilterlink =  $this->iso_params->get('tagsfilterlink', 'false');
$tagsfilterparent =  $this->iso_params->get('tagsfilterparent', 'false');
$tagsfilterparentlabel =  $this->iso_params->get('tagsfilterparentlabel', 'false');
$filtersoffcanvas = $this->iso_params->get('offcanvas', 'false');
$catsfilterimg =  $this->iso_params->get('catsfilterimg', 'false');
$splitfields = $this->iso_params->get('displayfiltersplitfields', 'false');
$splitfieldstitle = $this->iso_params->get('splitfieldstitle', 'false');
$blocklink =  $this->iso_params->get('blocklink', 'false');
$titlelink =  $this->iso_params->get('titlelink', 'true');
$displayfiltertags =  $this->iso_params->get('displayfiltertags', 'button');
$displayfilterfields =  $this->iso_params->get('displayfilterfields', 'button');
$displayfiltercat = $this->iso_params->get('displayfiltercat', 'button');
$displaysort =  $this->iso_params->get('displaysort', 'show');
$displaybootstrap = $this->iso_params->get('bootstrapbutton', 'false');
$displaysearch = $this->iso_params->get('displaysearch', 'false');
$language_filter = $this->iso_params->get('language_filter', 'false');
$displayrange = $this->iso_params->get('displayrange', 'false');
$rangefields = $this->iso_params->get('rangefields', 'false');
$rangestep = $this->iso_params->get('rangestep', 'false');
$displayalpha = $this->iso_params->get('displayalpha', 'false');
$imgmaxwidth = $this->iso_params->get('introimg_maxwidth', '0');
$imgmaxheight = $this->iso_params->get('introimg_maxheight', '0');
$params_fields = $this->iso_params->get('displayfields', array());

$div_bootstrap = "";
$button_bootstrap = "isotope_button";
$col_bootstrap_sort = "";
$col_bootstrap_filter = "";
$div_bootstrap = "row";
if ($displaybootstrap == 'true') {
    $button_bootstrap = "btn btn-sm ";
}
//==============================LAYOUTS======================================//
$layouts_prm = json_decode($this->iso_params->get('layouts'));
$layouts = [];
$layouts_order = [];
// Default values
$width = 0;
$line = 1;
$pos = 0;
if ($displaysort != "hide") {
    $values = new \stdClass();
    $values->div = "sort";
    $values->div_line = "1";
    $values->div_pos = "1";
    $pos = $values->div_pos;
    $values->div_width = "5";
    $values->div_align = "";
    $values->offcanvas = "false";
    $width += 5;
    $layouts['sort'] = $values;
}
if ($displaysearch == "true") {
    $values = new \stdClass();
    $values->div = "search";
    if ($pos > 0) {// on affiche le tri => recherche en dessous
        $values->div_line = "2";
        $values->div_pos = "0";
        $pos = 1;
    } else {
        $pos += 1;
        $values->div_pos = $pos;
        $values->div_line = "1";
        $width += 4;
    }
    $values->div_width = "4";
    $values->div_align = "";
    $values->offcanvas = "false";
    $layouts['search'] = $values;
}
if ($displayfilterfields != "hide") {
    if (($article_cat_tag == 'catfields') || ($article_cat_tag == 'cattagsfields')) {
        $values = new \stdClass();
        $values->div = "cat";
        $pos += 1;
        if ($width + 6 > 12) {
            $pos = 1;
            $line += 1;
            $width = 0;
        }
        $width += 6;
        $values->div_line = $line;
        $values->div_pos = $pos;
        $values->div_width = "6";
        $values->div_align = "";
        $values->offcanvas = "false";
        $layouts['cat'] = $values;
    }
    if (($article_cat_tag == 'tagsfields') || ($article_cat_tag == 'cattagsfields')) {
        $values = new \stdClass();
        $values->div = "tag";
        $pos += 1;
        if ($width + 6 > 12) {
            $pos = 1;
            $line += 1;
            $width = 0;
        }
        $width += 6;
        $values->div_line = $line;
        $values->div_pos = $pos;
        $values->div_width = "6";
        $values->div_align = "";
        $values->offcanvas = "false";
        $layouts['tag'] = $values;
    }
    $values = new \stdClass();
    $values->div = "field";
    $values->div_width = "12";
    $pos += 1;
    if ($width + 12 > 12) {
        $pos = 1;
        $line += 1;
        $width = 0;
    }
    $width += 12;
    $values->div_line = $line;
    $values->div_pos = $pos;
    $values->div_align = "";
    $values->offcanvas = "false";
    $layouts['field'] = $values;
}
if ($displayrange == "true") {
    $values = new \stdClass();
    $values->div = "range";
    $line += 1;
    $values->div_line = $line;
    $values->div_pos = "1";
    $values->div_width = "12";
    $values->div_align = "";
    $values->offcanvas = "false";
    $layouts['range'] = $values;
}
if ($displayalpha != "false") {
    $values = new \stdClass();
    $values->div = "alpha";
    $line += 1;
    $values->div_line = $line;
    $values->div_pos = "1";
    $values->div_width = "12";
    $values->div_align = "";
    $values->offcanvas = "false";
    $layouts['alpha'] = $values;
}
if ($language_filter != "false") { // php 8
    $values = new \stdClass();
    $values->div = "lang";
    $line += 1;
    $values->div_line = $line;
    $values->div_pos = "1";
    $values->div_width = "12";
    $values->div_align = "";
    $values->offcanvas = "false";
    $layouts['lang'] = $values;
}
$values = new \stdClass();
$values->div = "iso";
$line += 1;
$values->div_line = $line;
$values->div_pos = "1";
$values->div_width = "12";
$values->div_align = "";
$values->offcanvas = "false";
$layouts["iso"] = $values;

if ($layouts_prm) { // we have a parameter definition: replace default behaviour
    foreach ($layouts_prm as $layout) {
        $layouts[$layout->div] = $layout;
    }
}
foreach ($layouts as $layout) {
    $layouts_order[$layout->div_line.$layout->div_pos] = $layout->div;
}
//====================================Messages=====================================//
$libreverse = Text::_('CG_ISO_LIBREVERSE');
$liball = Text::_('CG_ISO_LIBALL');
$libdate = Text::_('CG_ISO_LIBDATE');
$libcategory = Text::_('CG_ISO_LIBCAT');
$libvisit = Text::_('CG_ISO_LIBVISIT');
$librating = Text::_('CG_ISO_LIBRATING');
$libid = Text::_('CG_ISO_LIBID');
$libalpha = Text::_('CG_ISO_LIBALPHA');
$libcreated = Text::_('CG_ISO_LIBCREATED');
$libpublished = Text::_('CG_ISO_LIBPUBLISHED');
$libupdated = Text::_('CG_ISO_LIBUPDATED');
$librandom = Text::_('CG_ISO_RANDOM');
$libfilter = Text::_('CG_ISO_LIBFILTER');
$libdateformat = $this->iso_params->get('formatsortdate', Text::_('CG_ISO_DATEFORMAT'));
$libotherdateformat = $this->iso_params->get('formatotherdate', Text::_('CG_ISO_DATEFORMAT'));
$libsearch = Text::_('CG_ISO_LIBSEARCH');
$libmore = Text::_('CG_ISO_LIBMORE');
$libsearchclear = Text::_('CG_ISO_SEARCHCLEAR');

if ($this->params->get('show_page_heading')) {
    echo "<h1>";
    echo $this->escape($this->params->get('page_heading'));
    echo "</h1>";
}
if (strlen(trim($this->iso_params->get('intro', ''))) > 0) {
    // apply content plugins
    $item_cls = new \stdClass();
    $item_cls->text = $this->iso_params->get('intro');
    $item_cls->params = $this->params;
    $item_cls->id = $com_id;
    $app->triggerEvent('onContentPrepare', array('com_cgisotope.content', &$item_cls, &$item_cls->params, 0));
    $intro = 	$item_cls->text;
    echo $intro;
}
?>
<div id="isotope-main-<?php echo $com_id;?>" data="<?php echo $com_id;?>" class="isotope-main">
<div class="isotope-div row" >
<?php
// =====================================sort buttons div =================================================//
$sort_buttons_div = "";
if ($displaysort != "hide") {
    $awidth = $layouts["sort"]->div_width;
    if (!property_exists($layouts["sort"], 'offcanvas')) {
        $layouts["sort"]->offcanvas = "false";
    }
    if ($layouts["sort"]->offcanvas == "true") {
        $awidth = 12;
    }
    $sort_buttons_div = '<div class="isotope_button-group sort-by-button-group col-md-'.$awidth.' col-12 '.$layouts["sort"]->div_align.'" data="'.$com_id.'">';
    $checked = " is-checked ";
    $featured = "";
    if ($this->iso_params->get('btnfeature', 'false') != "false") { // featured articles always displayed first
        $featured = 'featured,';
    }
    if ($this->iso_params->get('btndate', 'true') != "false") {
        $sens = $this->iso_params->get('btndate', 'true') == 'true' ? '+' : '-';
        $sens = $defaultdisplay == "date_desc" ? "-" : $sens;
        $sort_buttons_div .= '<button class="'.$button_bootstrap.$checked.' is-checked iso_button_date" data-sort-value="'.$featured.'date,title,category,click" data-init="'.$sens.'" data-sens="'.$sens.'" title="'.$libreverse.'">'.$libdate.'</button>';
        $checked = "";
    }
    if ($this->iso_params->get('btncat', 'true') != "false") {
        $sens = $this->iso_params->get('btncat', 'true') == 'true' ? '+' : '-';
        $sens = $defaultdisplay == "cat_desc" ? "-" : $sens;
        $sort_buttons_div .= '<button class="'.$button_bootstrap.$checked.' iso_button_cat" data-sort-value="'.$featured.'category,title,date,click" data-init="'.$sens.'" data-sens="'.$sens.'" title="'.$libreverse.'">'.$libcategory.'</button>';
        $checked = "";
    }
    if ($this->iso_params->get('btnalpha', 'true') != "false") {
        $sens = $this->iso_params->get('btnalpha', 'true') == 'true' ? '+' : '-';
        $sens = $defaultdisplay == "alpha_desc" ? "-" : $sens;
        $sort_buttons_div .= '<button class="'.$button_bootstrap.$checked.' iso_button_alpha" data-sort-value="'.$featured.'title,category,date,click" data-init="'.$sens.'" data-sens="'.$sens.'" title="'.$libreverse.'">'.$libalpha.'</button>';
        $checked = "";
    }
    if ($this->iso_params->get('btnvisit', 'true') != "false") {
        $sens = $this->iso_params->get('btnvisit', 'true') == 'true' ? '+' : '-';
        $sens = $defaultdisplay == "click_desc" ? "-" : $sens;
        $sort_buttons_div .= '<button class="'.$button_bootstrap.$checked.' iso_button_click" data-sort-value="'.$featured.'click,category,title,date" data-init="'.$sens.'" data-sens="'.$sens.'" title="'.$libreverse.'">'.$libvisit.'</button>';
        $checked = "";
    }
    if ($this->iso_params->get('btnid', 'false') != "false") {
        $sens = $this->iso_params->get('btnid', 'true') == 'true' ? '+' : '-';
        $sens = $defaultdisplay == "click_desc" ? "-" : $sens;
        $sort_buttons_div .= '<button class="'.$button_bootstrap.$checked.' iso_button_click" data-sort-value="'.$featured.'id" data-init="'.$sens.'" data-sens="'.$sens.'" title="'.$libreverse.'">'.$libid.'</button>';
        $checked = "";
    }
    if ($this->iso_params->get('btnrandom', 'false') != "false") {
        $sens = '';
        $sort_buttons_div .= '<button class="'.$button_bootstrap.$checked.' iso_button_random" data-sort-value="random">'.$librandom.'</button>';
        $checked = "";
    }
    if ($this->iso_entree != "webLinks") {
        if ((PluginHelper::isEnabled('content', 'vote')) && ($this->iso_params->get('btnrating', 'true') != "false")) {
            $sens = $this->iso_params->get('btnrating', 'true') == 'true' ? '+' : '-';
            $sens = $defaultdisplay == "rating_desc" ? "-" : $sens;
            $sort_buttons_div .= '<button class="'.$button_bootstrap.$checked.' iso_button_click" data-sort-value="'.$featured.'rating,category,title,date" data-init="'.$sens.'" data-sens="'.$sens.'" title="'.$libreverse.'">'.$librating.'</button>';
            $checked = "";
        }
    }
    $sort_buttons_div .= "</div>";
}
// ============================search div ============================================//
$search_div = "";
if ($displaysearch == "true") {
    $awidth = $layouts["search"]->div_width;
    if (!property_exists($layouts["search"], 'offcanvas')) {
        $layouts["search"]->offcanvas = "false";
    }
    if ($layouts["search"]->offcanvas == "true") {
        $awidth = 12;
    }
    $search_div .= '<div class="iso_search col-md-'.$awidth.' col-12 '.$layouts["search"]->div_align.'" data="'.$com_id.'">';
    $search_div .= '<input type="text" class="quicksearch " placeholder="'.$libsearch.'" style="width:80%;float:left">';
    $search_div .= '<i class="ison-cancel-squared " title="'.$libsearchclear.'" style="width:20%;float:right" ></i>';
    $search_div .= '</div>';
}
//============================filter div===============================================//
$filter_div = "";
$filter_tag_div = "";
if ($displayfilterfields != "hide") {
    $filter_cat_div = "";
    $filters = array();
    if (($article_cat_tag  == "tagsfields") || ($article_cat_tag  == "cattagsfields")) {
        if (count($this->tags_list) > 0) { // on a defini une liste de tags
            foreach ($this->tags_list as $key) {
                $filters['tags'][] = CGHelper::getTagTitle($key);
            }
        }
        $sortFilter = array();
        $alias = array();
        if (count($this->tags_list) > 0) {
            foreach ($filters['tags'] as $filter) {
                if ($tagsfilterparent == "true") {
                    $sortFilter[] = $filter[0]->parent_alias.'&'.$filter[0]->tag;
                } else {
                    $sortFilter[] = '&'.$filter[0]->tag;
                }
                $alias[$filter[0]->tag] = $filter[0]->alias;
            }
        } else { // empty tags list: take all tags found in articles
            foreach ($this->tags as $key => $value) {
                if ($tagsfilterparent == "true") {
                    $sortFilter[] = $this->tags_parent_alias[$value]."&".$value;
                } else {
                    $sortFilter[] = "&".$value;
                }
                $alias[$value] = $this->tags_alias[$value];
            }
        }
        if ($tagsfilterorder == "false") {
            asort($sortFilter, SORT_STRING | SORT_FLAG_CASE | SORT_NATURAL); // alphabetic order
        }
        if (($displayfiltertags == "button") || ($displayfiltertags == "multi") || ($displayfiltertags == "multiex")) {
            $awidth = $layouts["tag"]->div_width;
            if (!property_exists($layouts["tag"], 'offcanvas')) {
                $layouts["tag"]->offcanvas = "false";
            }
            if ($layouts["tag"]->offcanvas == "true") {
                $awidth = 12;
            }
            $checked = "";
            if ($this->default_tag == "") {
                $checked = "is-checked";
            }
            if ($tagsfilterparent != "true") {
                $filter_tag_div .= '<div class="isotope_button-group filter-button-group-tags col-md-'.$awidth.' col-12 '.$layouts["tag"]->div_align.'" data-filter-group="tags" data="'.$com_id.'">';
                $filter_tag_div .= '<button class="'.$button_bootstrap.'  iso_button_tags_tout '.$checked.'" data-sort-value="*" />'.$liball.'</button>';
            } else {
                $filter_tag_div .= '<div class="col-md-'.$awidth.' col-12 '.$layouts["tag"]->div_align.'">';
            }
            $cur_parent = '';
            foreach ($sortFilter as $aval) {
                $res = explode("&", $aval);
                $filter = $res[1];
                $parent = $res[0];
                if (($tagsfilterparent == "true") && ($cur_parent != $parent)) {
                    if ($cur_parent != '') {
                        $filter_tag_div .= "</div>";
                    }
                    $cur_parent = $parent;
                    $filter_tag_div .= '<div class="isotope_button-group filter-button-group-tags" data-filter-group="tags" data="'.$com_id.'">';
                    if ($tagsfilterparentlabel == "true") {
                        $filter_tag_div .= '<p class="iso_tags_parent_title">'.$this->tags_parent[$alias[$filter]].'</p>';
                    }
                }
                $aff = $filter;
                $aff_alias = $alias[$filter];
                if (!is_null($aff)) {
                    $img = "";
                    if ($tagsfilterimg == "true") {
                        $tagimage  = json_decode($this->tags_image[$aff_alias]);
                        if (is_object($tagimage) && (property_exists($tagimage, 'image_fulltext') || property_exists($tagimage, 'image_intro'))) {
                            if ($tagimage->image_intro != "") {
                                $img = '<img src="'.URI::root().$tagimage->image_intro.'" style="float:'.$tagimage->float_intro.'" 
							class="iso_tag_img" alt="'.$tagimage->image_intro_alt.'" title="'.$tagimage->image_intro_caption.'"/> ';
                            } elseif ($tagimage->image_fulltext != "") {
                                $img = '<img src="'.URI::root().$tagimage->image_fulltext.'" style="float:'.$tagimage->float_fulltext.'" 
							class="iso_tag_img" alt="'.$tagimage->image_fulltext_alt.'" title="'.$tagimage->image_fulltext_caption.'"/> ';
                            }
                        }
                    }
                    $checked = "";
                    if ($this->default_tag == $aff_alias) {
                        $checked = "is-checked";
                    }
                    $tagcount = '';
                    if ($tagsfiltercount == 'true') {
                        $tagcount = '<span class="tag-count badge bg-info">'.$this->tags_count[$aff_alias].'</span>';
                    }
                    $filter_tag_div .= '<button class="'.$button_bootstrap.'  iso_button_tags_'.$aff_alias.' '.$checked.'" data-sort-value="'.$aff_alias.'" title="'.$this->tags_note[$aff_alias].'"/>'.$img.Text::_($aff).$tagcount.'</button>';
                }
            }
            if ($tagsfilterparent == "true") {
                $filter_tag_div .= '</div>';
            }
            $filter_tag_div .= '</div>';
        } else {	// affichage Liste
            Text::script('JGLOBAL_SELECT_PRESS_TO_SELECT');
            Factory::getApplication()->getDocument()->getWebAssetManager()
                    ->useScript('webcomponent.field-fancy-select')
                    ->usePreset('choicesjs');
            $attributes = array(
                'class="isotope_select"',
                ' data-filter-group="tags"',
                ' id="isotope-select-tags"'
            );
            $selectAttr = array();

            $multiple = "";
            if ($displayfiltertags == "listmulti") {
                $libmulti = Text::_('CG_ISO_LIBLISTMULTI');
                $multiple = "  place-placeholder='".$libmulti."'";
                $selectAttr[] = ' multiple';
            }
            $filter_tag_div .= '<div class="isotope_button-group filter-button-group-tags col-md-'.$layouts["tag"]->div_width.' col-12 '.$layouts["tag"]->div_align.'" data-filter-group="tags" data="'.$com_id.'">';
            $filter_tag_div .= '<p class="hidden-phone" >'.$libfilter.' : </p>';
            $name = 'isotope-select-tags';
            $options = array();
            $options['']['items'][] = ModulesHelper::createOption('', $liball);
            foreach ($sortFilter as $aval) {
                $res = explode("&", $aval);
                $filter = $res[1];
                $parent = $res[0];
                $aff = $filter;
                $aff_alias = $alias[$filter];
                if (!is_null($aff)) {
                    $selected = "";
                    if ($this->default_tag == $aff_alias) {
                        $selected = "selected";
                    }
                    $options['']['items'][] = ModulesHelper::createOption($aff_alias, Text::_($aff));
                }
            }
            $filter_tag_div .= '<joomla-field-fancy-select '.implode(' ', $attributes).'>';
            $filter_tag_div .= HTMLHelper::_('select.groupedlist', $options, $name, array('id'          => $name,'list.select' => null,'list.attr'   => implode(' ', $selectAttr)));

            $filter_tag_div .= '</joomla-field-fancy-select>';
            $filter_tag_div .= '</div>';
        }

    }
    if (($article_cat_tag  == "catfields") || ($article_cat_tag  == "cattagsfields")) {
        $filter_cat_div = "";
        if (is_null($this->categories)) {
            $keys = array_keys($this->cats_lib);
            $filters['cat'] = $keys;
        } else {
            $filters['cat'] = $this->categories;
        }
        $sortFilter = array();
        if ($this->iso_params->get('catfilteralias', 'false') == 'true') { // sort category aliases
            foreach ($this->cats_alias as $key => $filter) {
                $sortFilter[$key] = $this->cats_alias[$key];
            }
        } else { // sort category names
            foreach ($filters['cat'] as $filter) {
                if (array_key_exists($filter, $this->cats_lib)) { // 01/10/2021
                    $sortFilter[$filter] = $this->cats_lib[$filter];
                }
            }
        }
        if ($this->iso_params->get('catfilteralias', 'false') != 'order') { // don't sort categories
            asort($sortFilter, SORT_STRING | SORT_FLAG_CASE | SORT_NATURAL); // alphabatic order
        }
        if (($displayfiltercat == "button")  || ($displayfiltercat == "multi") || ($displayfiltercat == "multiex")) {
            $awidth = $layouts["cat"]->div_width;
            if (!property_exists($layouts["cat"], 'offcanvas')) {
                $layouts["cat"]->offcanvas = "false";
            }
            if ($layouts["cat"]->offcanvas == "true") {
                $awidth = 12;
            }
            $filter_cat_div .= '<div class="isotope_button-group filter-button-group-cat col-md-'.$awidth.' col-12 '.$layouts["cat"]->div_align.'" data-filter-group="cat" data="'.$com_id.'">';
            $checked = "";
            if ($this->default_cat == "") {
                $checked = "is-checked";
            }
            $filter_cat_div .= '<button class="'.$button_bootstrap.'  iso_button_cat_tout '.$checked.'" data-sort-value="*" />'.$liball.'</button>';
            foreach ($sortFilter as $key => $filter) {
                $aff = $this->cats_lib[$key];
                $aff_alias = $this->cats_alias[$key];
                if (!is_null($aff)) {
                    $checked = "";
                    if ($this->default_cat == $aff_alias) {
                        $checked = "is-checked";
                    }
                    $img = "";
                    if ($catsfilterimg == "true") {
                        $catparams = "";
                        if ($this->cats_params[$key]) {
                            $catparam  = json_decode($this->cats_params[$key]);
                        }
                        if ($catparams && ($catparam->image != "")) {
                            $img = '<img src="'.URI::root().$catparam->image.'"  
							class="iso_cat_img" alt="'.$catparam->image_alt.'" /> '; // pascal
                        }
                    }
                    $filter_cat_div .= '<button class="'.$button_bootstrap.'  iso_button_cat_'.$aff_alias.' '.$checked.'" data-sort-value="'.$aff_alias.'"  title="'.$this->cats_note[$key].'"/>'.$img.Text::_($aff).'</button>';
                }
            }
            $filter_cat_div .= '</div>';
        } else {
            Text::script('JGLOBAL_SELECT_PRESS_TO_SELECT');
            $app->getDocument()->getWebAssetManager()
                    ->useScript('webcomponent.field-fancy-select')
                    ->usePreset('choicesjs');
            $attributes = array(
                'class="isotope_select"',
                ' data-filter-group="cat"',
                ' id="isotope-select-cat"'
            );
            $selectAttr = array();

            $multiple = "";
            if ($displayfiltercat == "listmulti") {
                $libmulti = Text::_('CG_ISO_LIBLISTMULTI');
                $multiple = "  place-placeholder='".$libmulti."'";
                $selectAttr[] = ' multiple';
            }
            $awidth = $layouts["cat"]->div_width;
            if (!property_exists($layouts["cat"], 'offcanvas')) {
                $layouts["cat"]->offcanvas = "false";
            }
            if ($layouts["cat"]->offcanvas == "true") {
                $awidth = 12;
            }
            $filter_cat_div .= '<div class="isotope_button-group filter-button-group-cat col-md-'.$awidth.' col-12 '.$layouts["cat"]->div_align.'" data-filter-group="cat" data="'.$com_id.'">';
            $filter_cat_div .= '<p class="hidden-phone" >'.$libfilter.' : </p>';
            $name = 'isotope-select-cat';
            $options = array();
            $options['']['items'][] = ModulesHelper::createOption('', $liball);
            foreach ($sortFilter as $key => $filter) {
                $aff = $this->cats_lib[$key];
                $aff_alias = $this->cats_alias[$key];
                if (!is_null($aff)) {
                    $selected = "";
                    if ($this->default_cat == $aff_alias) {
                        $selected = "selected";
                    }
                    $options['']['items'][] = ModulesHelper::createOption($aff_alias, Text::_($aff));
                }
            }
            $filter_cat_div .= '<joomla-field-fancy-select '.implode(' ', $attributes).'>';
            $filter_cat_div .= HTMLHelper::_('select.groupedlist', $options, $name, array('id'          => $name,'list.select' => null,'list.attr'   => implode(' ', $selectAttr)));

            $filter_cat_div .= '</joomla-field-fancy-select>';
            $filter_cat_div .= '</div>';
        }
    } // end of catfields
    $filters = array();
    if ($splitfields == "true") { // split fields buttons per field
        $group = array();
        $group_label = array();
        $group_id = 0;
        foreach ($this->article_fields_names as $key_article => $one) {
            foreach ($one as $key => $onefield) {
                if (is_array($onefield)) { // multiple values field
                    foreach ($onefield as $oneobj) {
                        $obj = $this->fields[$oneobj];
                        if ((count($params_fields) > 0)  &&  (!in_array($obj->field_id, $params_fields))) {
                            continue;
                        }
                        if (!array_key_exists($key, $group)) {
                            $val = array();
                        } else {
                            $val = $group[$key];
                        }
                        if (!in_array($oneobj, $val)) {
                            $val[$oneobj] = $obj;
                        }
                        if (!array_key_exists($key, $group_label)) {
                            $group_label[$key] = $obj->field_label;
                        }
                        $group[$key] = $val;
                    }
                } else { // single value field
                    $obj = $this->fields[$onefield];
                    if ((count($params_fields) > 0)  &&  (!in_array($obj->field_id, $params_fields))) {
                        continue;
                    }
                    if (!array_key_exists($key, $group)) {
                        $val = array();
                    } else {
                        $val = $group[$key];
                    }
                    if (!in_array($onefield, $val)) {
                        $val[$onefield] = $obj;
                    }
                    if (!array_key_exists($key, $group_label)) {
                        $group_label[$key] = $obj->field_label;
                    }
                    $group[$key] = $val;
                }
            }
        }
        $width = $layouts['field']->div_width;
        $col_width = "col-md-".$width." col-12";
        if ($width == 12) { // full width button list: try to figure out a correct width for every group
            $col_width = "col-12 col-md-12 col-xs-12";
            if (count($group) == 2) {
                $col_width = "col-md-6 col-12";
            }
            if (count($group) == 3) {
                $col_width = "col-md-4 col-12";
            }
            if (count($group) == 4) {
                $col_width = "col-md-3 col-12";
            }
            if (count($group) == 5) {
                $col_width = "col-md-2 col-12";
            }
            if (count($group) == 6) {
                $col_width = "col-md-2 col-12";
            }
        }
        if (!property_exists($layouts["field"], 'offcanvas')) {
            $layouts["field"]->offcanvas = "false";
        }
        if ($layouts["field"]->offcanvas == "true") {
            $col_width = 12;
        }

        if (count($params_fields) == 0) {// all fields : sort otherwise keep parameter's order
            ksort($group, SORT_STRING | SORT_FLAG_CASE | SORT_NATURAL);
        }
        if ($this->iso_params->get('splitfieldscolumn', 'false') == "true") {
            $filter_div .=  '<div class="fields-groups-class '.$col_width.'">';
        }
        foreach ($group as $group_lib => $onegroup) {

            $first =  array_key_first($onegroup);
            $group_id = $onegroup[$first]->field_id;
            $filter_div .=  '<div class="'.$col_width.'  isotope_button-group filter-button-group-fields class_fields_'.$group_lib.' '.$layouts["field"]->div_align.'" data-filter-group="'.$group_lib.'" data-group-id="'.$group_id.'" data="'.$com_id.'">';

            $filter_div .= CGHelper::create_buttons($this->fields, $group_lib, $onegroup, $this->iso_params, $col_width, $button_bootstrap, $splitfieldstitle, $group_label[$group_lib], $group_id, $com_id);
            $filter_div .= "</div>";
        }
        if ($this->iso_params->get('splitfieldscolumn', 'false') == "true") {
            $filter_div .= '</div>';
        }
    } else { // keep fields groups together
        $group_lib = "fields";
        $width = $layouts['field']->div_width;
        $col_width = "col-md-".$width." col-12";
        $filter_div .=  '<div class="'.$col_width.'  isotope_button-group filter-button-group-fields class_fields_'.$group_lib.'" data-filter-group="'.$group_lib.'" data="'.$com_id.'">';
        $filter_div .=  CGHelper::create_buttons($this->fields, 'fields', $this->fields, $this->iso_params, $col_width, $button_bootstrap, 'false', 'fields', 0, $com_id);
        $filter_div .= "</div>";
    }
}
//============================ isotope grid ===================================================================//
$width = $layouts["iso"]->div_width;
$isotope_grid_div = "";
if ($this->iso_params->get('middle') && (strlen(trim($this->iso_params->get('middle', ''))) > 0)) {
    // apply content plugins on weblinks
    $item_cls = new \stdClass();
    $item_cls->text = $this->iso_params->get('middle');
    $item_cls->params = $this->params;
    $item_cls->id = $com_id;
    $app->triggerEvent('onContentPrepare', array('com_cgisotope.content', &$item_cls, &$item_cls->params, 0)); // Joomla 4.0
    $isotope_grid_div = 	$item_cls->text;
}
$isotope_grid_div .= '<div class="isotope_grid col-md-'.$width.' col-12" style="padding:0" data="'.$com_id.'">'; // bootstrap : suppression du padding pour isotope
foreach ($this->list as $key => $category) {
    foreach ($category as $item) {
        $tag_display = "";
        $tag_img = "";
        $cat_img = "";
        if (($article_cat_tag  == "tagsfields") || ($article_cat_tag  == "cattagsfields")) { // filtre tag
            $isdefined = false; // supposed not found
            if (($this->params->get('tagsmissinghidden', 'false') == 'false') || ($this->iso_params->get('pagination', 'false') != 'false')) {
                $isdefined = true;
            } // don't check => ok
            foreach ($this->article_tags[$item->id] as $tag) {
                // ignore tags not defined in the tags list if tagsmissinghidden param is set
                if ($this->tags_list && (count($this->tags_list) > 0) && ($this->params->get('tagsmissinghidden', 'false') == 'true')) {
                    $isdefined = CGHelper::checkTagSet($tag->tag, $filters['tags']);
                }
                $tag_display .= " ".$this->tags_alias[$tag->tag];
                $tagimage  = json_decode($this->tags_image[$this->tags_alias[$tag->tag]]);
                if (!$tagimage) {
                    continue;
                }
                if ((!property_exists($tagimage, 'image_fulltext')) || ($tagimage->image_fulltext == "") && ($tagimage->image_intro == "")) {
                    continue;
                }
                if ($tagimage->image_intro != "") {
                    $tag_img .= '<img src="'.URI::root().$tagimage->image_intro.'" style="float:'.$tagimage->float_intro.'" 
									class="iso_tag_img_art" alt="'.$tagimage->image_intro_alt.'" title="'.$tagimage->image_intro_caption.'"/> ';
                } elseif ($tagimage->image_fulltext != "") {
                    $tag_img .=  '<img src="'.URI::root().$tagimage->image_fulltext.'" style="float:'.$tagimage->float_fulltext.'" 
								class="iso_tag_img_art" alt="'.$tagimage->image_fulltext_alt.'" title="'.$tagimage->image_fulltext_caption.'"/> ';
                };
            }
            if (!$isdefined) {
                continue;
            } // not in list : ignore it
        }
        $cat_params = json_decode($this->cats_params[$item->catid]);
        if (($cat_params) && (isset($cat_params->image)) && ($cat_params->image != "")) {
            $cat_img = "<img src='".URI::root().$cat_params->image."' alt='".$cat_params->image_alt."' class='iso_cat_img_art'/>";
        }
        $field_value = "";
        $field_cust = array();
        $data_range = "";
        if (isset($this->article_fields) and array_key_exists($item->id, $this->article_fields)) {
            foreach ($this->article_fields[$item->id] as $key_f => $tag_f) {
                if (is_array($tag_f)) { // multiple answers
                    $afield = "";
                    foreach ($tag_f as $avalue) {
                        $obj = $this->fields[$avalue];
                        $afield .= $afield == "" ? $obj->render : ", ".$obj->render;
                    }
                    $field_cust['{'.$key_f.'}'] = (string)$afield; // php 8
                    $field_value .= " ".implode(' ', $tag_f);
                } else { // one field
                    $obj = $this->fields[$tag_f];
                    if ((count($params_fields) == 0) ||  (in_array($obj->field_id, $params_fields))) {
                        $field_value .= " ".$tag_f;
                    }
                    $field_cust['{'.$key_f.'}'] = (string)$obj->render; // field display value, php 8
                }
                if (($displayrange == "true") && ($key_f == $this->rangetitle)) {
                    $data_range = " data-range='".$obj->val."' ";
                }
            };
        }
        $itemtags = "";
        foreach ($this->article_tags[$item->id] as $tag) {
            $iso_link_cls == "";
            $iso_link_sort == "";
            if ($tagsfilterlink == 'joomla') { // joomla link to tag component
                $iso_link_cls = $this->tags_link[$tag->alias] ? " iso_tag_link" : "";
            }
            if ($tagsfilterlink == 'iso') { // isotope filtering
                $iso_link_sort = ' data-sort-value="'.$tag->alias.'"';
                $iso_link_cls = ' iso_tag_link';
            }
            $itemtags .= '<span class="iso_tag_'.$this->tags_alias[$tag->tag].$iso_link_cls.'"'.$iso_link_sort.'>';
            if ($tagsfilterlink == 'joomla') { // joomla link to tag component
                $itemtags .= '<a href="'.$this->tags_link[$tag->alias].'"  target="_blank">';
            }
            $itemtags .= ($itemtags == "") ? $tag->tag : "<span class='iso_tagsep'><span> - </span></span>".$tag->tag;
            if ($tagsfilterlink == 'joomla') { // joomla link to tag component
                $itemtags .= '</a>';
            }
            $itemtags .= '</span>';
        }
        $ladate = $item->displayDate;
        $data_cat =  $item->category_alias;
        $click = $item->hits;
        if (!isset($item->rating)) {
            $item->rating = "";
            $item->rating_count = 0;
        }
        $t = "";
        $c = "";
        if ($blocklink == "true") {
            $t = 'onclick=go_click("'.$this->iso_entree.'","'.$item->link.'")';
            $c = 'isotope_pointer'; // add class cursor = pointer
        }

        $isotope_grid_div .= '<div class="isotope_item iso_cat_'.$key.' '.$field_value.' '.$c.' '.$tag_display.'" data-featured="'.$item->featured.'" data-title="'.$item->title.'" data-category="'.$data_cat.'" data-date="'.$ladate.'" data-click="'.$click.'"data-rating="'.$item->rating.'" data-blog="'.$item->ordering.'" data-id="'.$item->id.'" data-lang="'.$item->language.'"  data-alpha="'.substr($item->title, 0, 1).'" '.$data_range.$t.'>';
        $canEdit = $user->authorise('core.edit', 'com_content.article.'.$item->id);
        if ($canEdit) {
            $isotope_grid_div .=  '<span class="edit-icon">';
            $isotope_grid_div .=  '<a href="index.php?option=com_content&task=article.edit&a_id='.$item->id.'&return='.base64_encode($uri).'">';
            $isotope_grid_div .=  '<span class="icon-edit" aria-hidden="true"></span>';//'<img src="media/system/images/edit.png" alt="modifier" class="iso-img-max-width"></a>';
            $isotope_grid_div .=  '</span>';
        }

        // prise en charge sous-titre par tilde et badge nouveau
        $item->new = "";
        if ($this->iso_params->get('btnnew', 'false') == 'true') {
            $nbday = $this->iso_params->get('new_limit', 0);
            $tmp = date('Y-m-d H:i:s', mktime(date("H"), date("i"), 0, date("m"), date("d") - intval($nbday), date("Y")));
            $item->new = ($tmp < $item->publish_up) ? ' <span class="iso_badge_new">'.Text::_('CG_ISO_NEW').'</span> ' : '';
        }
        $item->subtitle = "";
        if ($this->iso_params->get('btnsubtitle', 'false') == 'true') {
            // Préparation du titre
            // mise en SMALL du texte après ~ (tilde)
            // + BR si texte > 10 car
            list($title, $item->subtitle) = explode('~', $item->title . '~');
            $item->title = trim($title);
            if ($item->subtitle) {
                $item->subtitle = '<small>'.trim($item->subtitle).'</small>';
            }
        }
        // fin prise en charge sous-titre par tilde et badge nouveau

        if ($titlelink == "true") {
            $title = '<a href="'.$item->link.'">'.$item->title.'</a>';
        } else {
            $title =  $item->title;
        }
        $rating = "";
        for ($i = 1; $i <= $item->rating; $i++) {
            $rating .= '<img src="'.$comfield.'images/icon.png" />';
        }
        $phocacount = CGHelper::getArticlePhocaCount($item->fulltext);
        $choixdate = $this->iso_params->get('choixdate', 'modified');
        $libdate = "";
        if (!is_numeric($choixdate)) {
            $libdate = $choixdate == "modified" ? $libupdated : ($choixdate == "created" ? $libcreated : $libpublished);
        }
        $perso = $this->iso_params->get('perso');
        $perso = CGHelper::checkNullFields($perso, $item, $phocacount); // suppress null field if required
        $arr_css = array("{id}" => $item->id,"{title}" => $title, "{cat}" => $this->cats_lib[$item->catid],"{date}" => $libdate.date($libdateformat, strtotime($item->displayDate)),"{create}" => HTMLHelper::_('date', $item->created, $libotherdateformat),"{pub}" => HTMLHelper::_('date', $item->publish_up, $libotherdateformat),"{modif}" => HTMLHelper::_('date', $item->modified, $libotherdateformat), "{visit}" => $item->hits, "{intro}" => $item->displayIntrotext,"{stars}" => $rating,"{rating}" => $item->rating,"{ratingcnt}" => $item->rating_count,"{count}" => $phocacount,"{tagsimg}" => $tag_img, "{catsimg}" => $cat_img, "{link}" => $item->link, "{introimg}" => $item->introimg, "{subtitle}" => $item->subtitle, "{new}" => $item->new, "{tags}" => $itemtags,"{featured}" => $item->featured);
        foreach ($arr_css as $key_c => $val_c) {
            $perso = str_replace($key_c, Text::_($val_c), $perso);
        }
        foreach ($field_cust as $key_f => $val_f) { // display fields values
            $perso = str_replace($key_f, Text::_($val_f), $perso);
        }
        $perso = CGHelper::checkNoField($perso); // suppress empty fields
        // apply content plugins
        $item_cls = new \stdClass();
        $item_cls->id = $item->id;
        $item_cls->text = $perso;
        $item_cls->params = $this->iso_params;
        $app->triggerEvent('onContentPrepare', array('com_content.article', &$item_cls, &$item_cls->params, 0)); // Joomla 4.0
        $perso = 	$item_cls->text;
        // additionnal perso in isotope plugin
        $item_cls = $item;
        $item_cls->text = $perso;
        $item_cls->params = $this->iso_params;
        $app->triggerEvent('onCGIsotopeRender', array('com_cgisotope.article', &$item_cls,&$item_cls->params, 0));
        $perso = 	$item_cls->text;

        $isotope_grid_div .=  $perso;
        if ($this->iso_params->get('readmore', 'false') != 'false') {
            $isotope_grid_div .=  '<p class="isotope-readmore">';
            $isotope_grid_div .= '<a class="isotope-readmore-title"  data-articleid="'.$item->id.'" data-href="'.$item->link.'" href="'.$item->link.'">';
            $isotope_grid_div .=  Text::_('CG_ISO_READMORE');
            $isotope_grid_div .=  '</a></p>';
        }
        $isotope_grid_div .=  '</div>';
    }
}
// ============================range div ==============================================//
$isotope_range_div = "";
if ($displayrange == "true") {
    $awidth = $layouts["range"]->div_width;
    if (!property_exists($layouts["range"], 'offcanvas')) {
        $layouts["range"]->offcanvas = "false";
    }
    if ($layouts["range"]->offcanvas == "true") {
        $awidth = 12;
    }
    $isotope_range_div = '<div class="iso_range col-md-'.$awidth.' col-12 '.$layouts["range"]->div_align.'" data="'.$com_id.'">';
    $isotope_range_div .= '<div class="col-12"><label title="'.$this->rangedesc.'">'.$this->rangelabel.'</label></div><div class="col-12 col-md-12"><input type="text" id="rSlider" data="'.$com_id.'"/></div>';
    $isotope_range_div .= '</div>';
}
// ============================alpha div ==============================================//
$isotope_alpha_div = "";
if ($displayalpha != "false") {
    $awidth = $layouts["alpha"]->div_width;
    if (!property_exists($layouts["alpha"], 'offcanvas')) {
        $layouts["alpha"]->offcanvas = "false";
    }
    if ($layouts["alpha"]->offcanvas == "true") {
        $awidth = 12;
    }
    $isotope_alpha_div = '<div class="isotope_button-group filter-button-group-alpha iso_alpha col-md-'.$awidth.' col-12 '.$layouts["alpha"]->div_align.'" data-filter-group="alpha" data="'.$com_id.'">';
    $isotope_alpha_div .= CGHelper::create_alpha_buttons($this, $button_bootstrap);
    $isotope_alpha_div .= '</div>';
}
// =============================Lang. filter ============================================//
$isotope_lang_div = "";
if (($language_filter == "button") || ($language_filter == "multi")) {
    $awidth = $layouts["lang"]->div_width;
    if (!property_exists($layouts["lang"], 'offcanvas')) {
        $layouts["lang"]->offcanvas = "false";
    }
    if ($layouts["lang"]->offcanvas == "true") {
        $awidth = 12;
    }
    $isotope_lang_div = '<div class="isotope_button-group iso_lang col-md-'.$awidth.' col-12 '.$layouts["lang"]->div_align.'" data-filter-group="lang" data="'.$com_id.'">';
    $isotope_lang_div .= CGHelper::create_language_buttons($this, $button_bootstrap);
    $isotope_lang_div .= '</div>';
}
//=====================================layouts==============================================//
ksort($layouts_order, SORT_STRING | SORT_FLAG_CASE | SORT_NATURAL); // order
$val = 0;
$line = 0;
$offcanvas = false;
$offcanvasopened = false;
foreach ($layouts_order as $layout) {
    $key = (string)$layout;
    $obj = $layouts[$key];
    $val = $obj->div_width;
    $line = $obj->div_line;
    if (!property_exists($layouts[$key], 'offcanvas')) {
        $layouts[$key]->offcanvas = "false";
        $obj->offcanvas = "false";
    }
    if ($obj->div == "iso") {// no offcanvas on iso
        $obj->offcanvas = "false";
    }
    if ($line == 0) {
        $line = $obj->div_line;
    }
    if ($offcanvasopened) {
        if ($obj->offcanvas == "false") {
            $offcanvasopened = false;
            echo "</div></div>";
        }
    }
    $offcanvas = ($obj->offcanvas == "true");
    if ($offcanvas && !$offcanvasopened) {// offcanvas
        \Joomla\CMS\HTML\HTMLHelper::_('bootstrap.offcanvas', '.selector', []);
        $offcanvasopened = true;
        echo '<div class="col-md-'.$obj->div_width.'" id="offcanvas-clone">';
        $offcanvasbtnpos = ($this->offcanvasbtnpos == "leave") ? "" : $this->offcanvasbtnpos;
        echo '<a class="'.$button_bootstrap.' btn-info navbar-dark '.$offcanvasbtnpos.'" data-bs-toggle="offcanvas" href="#offcanvas'.$obj->div.'" role="button" aria-controls="offcanvas'.$obj->div.'"  title="Filtre" id="offcanvas-hamburger-btn">';
        if ($this->displayoffcanvas == 'hamburger') {
            echo '<span class="navbar-toggler-icon"></span>';
        } else {
            echo '<span class="navbar-toggler-text">'.Text::_('CG_ISO_LIBFILTER').'</span>';
        }
        echo '</a><div id="clonedbuttons" data="'.$com_id.'"></div></div>';
        echo '<div class="offcanvas offcanvas-'.$this->offcanvaspos.'" tabindex="-1" id="offcanvas'.$obj->div.'" aria-labelledby="offcanvas'.$obj->div.'Label" data-bs-scroll="true">';
        $liboff = Text::_('CG_ISO_LIBFILTER');
        echo '<div class="offcanvas-header"><h5 class="offcanvas-title" id="offcanvas'.$obj->div.'Label">'.$liboff.'</h5>';
        echo '<button type="button" class="btn ison-cancel-squared" title="'.Text::_('CG_ISO_CLEAR_FILTER').'" data="'.$com_id.'">'.Text::_('CG_ISO_CLEAR_FILTER').'</button>';
        echo '<button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close" title="'.Text::_('CG_ISO_CLOSE').'"></button>';
        echo '</div><div class="offcanvas-body">';
    }
    if (($val > 12) || (($obj->div_width == 12) && ($val > 12)) || ($line < $obj->div_line)) { // new line needed
        if (($obj->div == "iso") && ($obj->div_width == 12)) {
            echo "</div><div>";
        } else {
            echo "</div><div class='row'>";
        }
        $val = $obj->div_width;
        if ($line < $obj->div_line) { // requested new line
            $line = $obj->div_line;
        } else { // calculated new line
            $line += 1;
        }
    }
    if ($obj->div == "search") {
        echo $search_div;
    }
    if ($obj->div == "sort") {
        echo $sort_buttons_div;
    }
    if ($obj->div == "cat") {
        echo $filter_cat_div;
    }
    if ($obj->div == "tag") {
        echo $filter_tag_div;
    }
    if ($obj->div == "field") {
        echo $filter_div;
    }
    if ($obj->div == "iso") {
        echo $isotope_grid_div;
    }
    if ($obj->div == "lang") {
        echo $isotope_lang_div;
    }
    if ($obj->div == "range") {
        echo $isotope_range_div;
    }
    if ($obj->div == "alpha") {
        echo $isotope_alpha_div;
    }
}

?>
</div>
</div>
<?php
// view article in isotope component
$width = $layouts["iso"]->div_width;
if ($this->iso_params->get('readmore', 'false') == 'iframe') {
    echo '<div id="isotope_an_article" class="isotope_an_article col-md-'.$width.' col-12 isotope-hide" ><button type="button" class="close">X</button><iframe src="" id="isotope_article_frame"></iframe></div>';
} elseif ($this->iso_params->get('readmore', 'false') == 'ajax') {
    echo '<input id="token" type="hidden" name="' . Session::getFormToken() . '" value="1" />';
    echo '<div id="isotope_an_article" class="isotope_an_article col-md-'.$width.' col-12 isotope-hide" ></div>';
}
?>
<div class="iso_div_empty iso_hide_elem">
	<?php echo Text::_('CG_ISO_EMPTY'); ?>
</div>
<div class="iso_div_more">
<button class="<?php echo $button_bootstrap;?> iso_button_more" data="'.$com_id.'"><?php echo $libmore;?></button>
</div>
<?php if ($this->iso_params->get('pagination', 'true') == 'true') {?> 
<div class="pagination isotope-pagination">	
	<?php  $lapagination = $this->pagination->getPagesLinks($this->params);
    echo $lapagination;
    ?>
</div>
<?php }
if ($this->iso_params->get('pagination', 'true') == 'infinite') { ?>
<div class="page-load-status">
  <div class="loader-ellips infinite-scroll-request">
    <span class="loader-ellips__dot"></span>
    <span class="loader-ellips__dot"></span>
    <span class="loader-ellips__dot"></span>
    <span class="loader-ellips__dot"></span>
  </div>
  <p class="infinite-scroll-last"><?php echo Text::_('CG_ISO_END_OF_CONTENT'); ?></p>
  <p class="infinite-scroll-error"><?php echo Text::_('CG_ISO_NO_MORE_PAGE'); ?></p>
</div>
<?php } ?>
</div>
<?php if (strlen(trim($this->iso_params->get('bottom', ''))) > 0) {
    // apply content plugins on bottom message
    $item_cls = new \stdClass();
    $item_cls->text = $this->iso_params->get('bottom');
    $item_cls->params = $this->params;
    $item_cls->id = $com_id;
    $app->triggerEvent('onContentPrepare', array('com_cgisotope.content', &$item_cls, &$item_cls->params, 0)); // Joomla 4.0
    $bottom = 	$item_cls->text;
    $bottom = str_replace('{cg-version}', CGHelper::getCGVersion(), $bottom);
    $bottom = str_replace('{cg-name}', CGHelper::getCGName(), $bottom);
    echo $bottom;
}
?>

