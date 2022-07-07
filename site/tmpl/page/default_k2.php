<?php
/**
* CG Isotope Component  - Joomla 4.0.0 Component 
* Version			: 2.3.1
* Package			: CG ISotope
* copyright 		: Copyright (C) 2022 ConseilGouz. All rights reserved.
* license    		: http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
* From              : isotope.metafizzy.co
*/
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\PluginHelper;
use \Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use ConseilGouz\Component\CGIsotope\Site\Helper\CGHelper;

PluginHelper::importPlugin('content');
PluginHelper::importPlugin('cgisotope');

$uri = JUri::getInstance();
$user = Factory::getUser();
$app = Factory::getApplication();
$com_id = $app->input->getInt('Itemid');
$comfield = ''.JURI::base(true).'/media/com_cgisotope/';

$defaultdisplay = $this->iso_params->get('defaultdisplay', 'date_desc');
$displaysortinfo = $this->iso_params->get('displaysortinfo', 'show');
$article_cat_tag = $this->iso_params->get('cat_or_tag',$this->iso_entree == "webLinks"?'cat':'tags'); 
$displayfiltercat = $this->iso_params->get('displayfiltercat','button');
$displayfiltertags =  $this->iso_params->get('displayfiltertags','button');
$tagsfilterorder = $this->iso_params->get('tagsfilterorder','false');
$tagsfilterimg =  $this->iso_params->get('tagsfilterimg','false');
$tagsfilterparent =  $this->iso_params->get('tagsfilterparent','false');
$tagsfilterparentlabel =  $this->iso_params->get('tagsfilterparentlabel','false');
$catsfilterimg =  $this->iso_params->get('catsfilterimg','false');
$blocklink =  $this->iso_params->get('blocklink','false'); 
$titlelink =  $this->iso_params->get('titlelink','true'); 
$displaysort =  $this->iso_params->get('displaysort','show');  
$displaybootstrap = $this->iso_params->get('bootstrapbutton','false'); 
$displaysearch=$this->iso_params->get('displaysearch','false');  
$language_filter=$this->iso_params->get('language_filter','false');
$displayalpha = $this->iso_params->get('displayalpha','false');  
$imgmaxwidth = $this->iso_params->get('introimg_maxwidth','0'); 
$imgmaxheight = $this->iso_params->get('introimg_maxheight','0'); 
$params_fields = $this->iso_params->get('displayfields',array());
$button_bootstrap = "isotope_button";
if ($displaybootstrap == 'true') { 
	$button_bootstrap = "btn btn-sm ";
}
//==============================LAYOUTS======================================//
$layouts_prm = json_decode($this->iso_params->layouts);
$layouts = [];
$layouts_order = [];
// Default values 
$width = 0;
$line = 1;
$pos = 0;
if ($displaysort != "hide") {
	$values = new stdClass();
	$values->div = "sort";
	$values->div_line = "1";
	$values->div_pos = "1";
	$pos = $values->div_pos;
	$values->div_width = "5";
	$values->div_align="";
	$width += 5;
	$layouts['sort'] = $values;
}
if ($displaysearch == "true") {
	$values = new stdClass();
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
	$values->div_align="";
	$layouts['search'] = $values;
}
if ($article_cat_tag == 'cattags') {
	$values = new stdClass();
	$values->div = "cat";
	$pos += 1;
	if ($width + 6 > 12) {
		$pos = 1; 
		$line +=1;
		$width = 0;
	}
	$width += 6;
	$values->div_line = $line;
	$values->div_pos = $pos;
	$values->div_width = "6";
	$values->div_align="";
	$layouts['cat'] = $values;
	$values = new stdClass();
	$values->div = "tag";
	$pos += 1;
	if ($width + 6 > 12) {
		$pos = 1; 
		$line +=1;
		$width = 0;
	}
	$width += 6;
	$values->div_line = $line;
	$values->div_pos = $pos;
	$values->div_width = "6";
	$values->div_align="";
	$layouts['tag'] = $values;
}
if ($article_cat_tag == 'cat') {
	$values = new stdClass();
	$values->div = "cat";
	$values->div_width = "7";
	$pos += 1;
	if ($width + 7 > 12) {
		$pos = 1; 
		$line +=1;
		$width = 0;
	}
	$width += 7;
	$values->div_line = $line;
	$values->div_pos = $pos;
	$values->div_align="";
	$layouts['cat'] = $values;
}
if ($article_cat_tag == 'tags') {
	$values = new stdClass();
	$values->div = "tag";
	$values->div_width = "7";
	$pos += 1;
	if ($width + 7 > 12) {
		$pos = 1; 
		$line +=1;
		$width = 0;
	}
	$width += 7;
	$values->div_line = $line;
	$values->div_pos = $pos;
	$values->div_align="";
	$layouts['tag'] = $values;
}
if ($displayalpha != "false") {
	$values = new stdClass();
	$values->div = "alpha";
	$line +=1;
	$values->div_line = $line;
	$values->div_pos = "1";
	$values->div_width = "12";
	$values->div_align="";
	$layouts['alpha'] = $values;
}
if ($language_filter != "false") { // php 8
	$values = new stdClass();
	$values->div = "lang";
	$line +=1;
	$values->div_line = $line;
	$values->div_pos = "1";
	$values->div_width = "12";
	$values->div_align="";
	$layouts['lang'] = $values;
}
$values = new stdClass();
$values->div = "iso";
$line +=1;
$values->div_line = $line;
$values->div_pos = "1";
$values->div_width = "12";
$values->div_align="";
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
$libreverse=Text::_('CG_ISO_LIBREVERSE');
$liball = Text::_('CG_ISO_LIBALL');
$libdate = Text::_('CG_ISO_LIBDATE');
$libcategory = Text::_('CG_ISO_LIBCAT');
$libvisit= Text::_('CG_ISO_LIBVISIT');
$librating= Text::_('CG_ISO_LIBRATING');
$libid= Text::_('CG_ISO_LIBID');
$libalpha=Text::_('CG_ISO_LIBALPHA');
$libcreated=Text::_('CG_ISO_LIBCREATED'); 
$libupdated=Text::_('CG_ISO_LIBUPDATED');
$librandom=Text::_('CG_ISO_RANDOM');
$libfilter=Text::_('CG_ISO_LIBFILTER');  
$libdateformat = $this->params->get('formatsortdate',Text::_('CG_ISO_DATEFORMAT')) // format d'affichage des dates au format php d/m/Y H:i  = format fran�ais avec heure/minutes
$libotherdateformat = $this->params->get('formatsortdate',Text::_('CG_ISO_DATEFORMAT')) // format d'affichage des dates au format php d/m/Y H:i  = format fran�ais avec heure/minutes
$libsearch = Text::_('CG_ISO_LIBSEARCH');
$libmore = Text::_('CG_ISO_LIBMORE');
$libsearchclear = Text::_('CG_ISO_SEARCHCLEAR');
if ($this->params->get('show_page_heading')) {
	echo "<h1>";
	echo $this->escape($this->params->get('page_heading')); 
	echo "</h1>";
}
if (strlen(trim($this->iso_params->get('intro'))) > 0) {
	// apply content plugins on weblinks
	$item_cls = new stdClass;
	$item_cls->text = $this->iso_params->get('intro');
	$item_cls->params = $this->params;
    $item_cls->id= $com_id;
	Factory::getApplication()->triggerEvent('onContentPrepare', array ('com_cgisotope.content', &$item_cls, &$item_cls->params, 0)); // Joomla 4.0
	$intro = 	$item_cls->text;	
	echo $intro; 
}
?>
<div id="isotope-main-<?php echo $com_id;?>" data="<?php echo $com_id;?>" class="isotope-main">
<div class="isotope-div fg-row" >
<?php 
// =====================================sort buttons div =================================================// 
$sort_buttons_div = "";
if ($displaysort != "hide") { 
$sort_buttons_div = '<div class="isotope_button-group sort-by-button-group fg-c'.$layouts["sort"]->div_width.' fg-cs12 fg-cm6 '.$layouts["sort"]->div_align.'">';
$checked = " is-checked ";
if ($this->iso_params->get('btndate','true') != "false") {
    $sens = $this->iso_params->get('btndate','true') == 'true' ? '+':'-'; 
	$sens = $defaultdisplay=="date_desc"? "-": $sens;
	$sort_buttons_div .= '<button class="'.$button_bootstrap.$checked.' is-checked iso_button_date" data-sort-value="date,title,category,click" data-init="'.$sens.'" data-sens="'.$sens.'" title="'.$libreverse.'">'.$libdate.'</button>';
	$checked = "";
}
if ($this->iso_params->get('btncat','true') != "false") {
    $sens = $this->iso_params->get('btncat','true') == 'true' ? '+':'-';
	$sens = $defaultdisplay=="cat_desc"? "-": $sens;
	$sort_buttons_div .= '<button class="'.$button_bootstrap.$checked.' iso_button_cat" data-sort-value="category,title,date,click" data-init="'.$sens.'" data-sens="'.$sens.'" title="'.$libreverse.'">'.$libcategory.'</button>';
	$checked = "";
}
if ($this->iso_params->get('btnalpha','true') != "false") {
    $sens = $this->iso_params->get('btnalpha','true') == 'true' ? '+':'-';
	$sens = $defaultdisplay=="alpha_desc"? "-": $sens;
	$sort_buttons_div .= '<button class="'.$button_bootstrap.$checked.' iso_button_alpha" data-sort-value="title,category,date,click" data-init="'.$sens.'" data-sens="'.$sens.'" title="'.$libreverse.'">'.$libalpha.'</button>';
	$checked = "";
}
if ($this->iso_params->get('btnvisit','true') != "false") {
    $sens = $this->iso_params->get('btnvisit','true') == 'true' ? '+':'-';
	$sens = $defaultdisplay=="click_desc"? "-": $sens;
	$sort_buttons_div .= '<button class="'.$button_bootstrap.$checked.' iso_button_click" data-sort-value="click,category,title,date" data-init="'.$sens.'" data-sens="'.$sens.'" title="'.$libreverse.'">'.$libvisit.'</button>';
	$checked = "";
}
if ($this->iso_params->get('btnid','false') != "false") {
    $sens = $this->iso_params->get('btnid','true') == 'true' ? '+':'-';
	$sens = $defaultdisplay=="click_desc"? "-": $sens;
	$sort_buttons_div .= '<button class="'.$button_bootstrap.$checked.' iso_button_click" data-sort-value="id" data-init="'.$sens.'" data-sens="'.$sens.'" title="'.$libreverse.'">'.$libid.'</button>';
	$checked = "";
}
if ($this->iso_params->get('btnrandom','false') != "false") {
	$sens = ''; 
	$sort_buttons_div .= '<button class="'.$button_bootstrap.$checked.' iso_button_random" data-sort-value="random">'.$librandom.'</button>';
	$checked = "";
}
if ($iso_entree != "webLinks") {
    if ((JPluginHelper::isEnabled('content', 'vote')) && ($this->iso_params->get('btnrating','true') != "false")) {
	    $sens = $this->iso_params->get('btnrating','true') == 'true' ? '+':'-';
		$sens = $defaultdisplay=="rating_desc"? "-": $sens;
		$sort_buttons_div .= '<button class="'.$button_bootstrap.$checked.' iso_button_click" data-sort-value="rating,category,title,date" data-init="'.$sens.'" data-sens="'.$sens.'" title="'.$libreverse.'">'.$librating.'</button>';
		$checked = "";
	}
}
$sort_buttons_div .= "</div>";
}
// ============================search div ============================================//
$search_div = "";
if ($displaysearch == "true") { 
	$search_div .= '<div class="iso_search fg-c'.$layouts["search"]->div_width.' fg-cs12 '.$layouts["search"]->div_align.'" >';
	$search_div .= '<input type="text" class="quicksearch " placeholder="'.$libsearch.'" style="width:80%;float:left">';
	$search_div .= '<i class="ison-cancel-squared " title="'.$libsearchclear.'" style="width:20%;float:right"></i>';
	$search_div .= '</div>';
}
//============================filter div===============================================//
$filter_cat_div = "";
$filter_tag_div= "";
if (($displayfiltertags != "hide") || ($displayfiltercat != "hide")) {
 if (($article_cat_tag == "tags") || ($article_cat_tag =="cat") || ($article_cat_tag == "cattags")) {
	$filters = array();
	if ( ($article_cat_tag  == "tags") ||  ($article_cat_tag  == "cattags") ) {
		if (count($this->tags_list) > 0) { // on a defini une liste de tags
			foreach ($this->tags_list as $key) {
			$filters['tags'][]= CGHelper::getTagTitle($key);
			}
		} 
	}
	if (($article_cat_tag  == "cat") ||  ($article_cat_tag  == "cattags")) { 
	    if (is_null($this->categories) ) {
           $keys = array_keys($this->cats_lib);
           $filters['cat'] = $keys;
	    } else {
		  $filters['cat']= $this->categories;
	    }
	}
	if (($article_cat_tag  == "cat") || ($article_cat_tag  == "cattags")) {
		// categories sort
		$sortFilter = array();
		// 2.3.1 : category alias parameter
		if ($this->iso_params->get('catfilteralias','false') == 'true') { // sort category aliases
			foreach ($this->cats_alias as $key => $filter) {
				$sortFilter[$key] = $this->cats_alias[$key];
			}
		} else { // sort category names
			foreach ($filters['cat'] as $filter) {
				$sortFilter[$filter] = $this->cats_lib[$filter];
			}	
		}
		if ($this->iso_params->get('catfilteralias','false') != 'order') { // don't sort categories
			asort($sortFilter,  SORT_STRING | SORT_FLAG_CASE | SORT_NATURAL ); // alphabatic order
		}
	    if  (($displayfiltercat == "button")  || ($displayfiltercat == "multi") || ($displayfiltercat == "multiex")) {
    	    $filter_cat_div .= '<div class="isotope_button-group filter-button-group-cat fg-c'.$layouts["cat"]->div_width.' fg-cs12 '.$layouts["cat"]->div_align.'" data-filter-group="cat">';
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
					if ($this->default_cat == $aff_alias) {$checked = "is-checked";}
					$img="";
					if ($catsfilterimg == "true") {
						$catparam  = json_decode($this->cats_params[$key]);	
						if ($catparam->image != "") {
							$img = '<img src="'.JURI::root().$catparam->image.'"  
							class="iso_cat_img" alt="'.$tagimage->image_alt.'" /> ';
						}
					}
					$filter_cat_div .= '<button class="'.$button_bootstrap.'  iso_button_cat_'.$aff_alias.' '.$checked.'" data-sort-value="'.$aff_alias.'" />'.$img.Text::_($aff).'</button>';
				}
			}
			$filter_cat_div .= '</div>';
		} else {
		    $filter_cat_div .= '<div class="isotope_button-group filter-button-group-cat fg-c'.$layouts["cat"]->div_width.' fg-cs12 '.$layouts["cat"]->div_align.'" data-filter-group="cat">';
		    $filter_cat_div .= '<p class="hidden-phone" >'.$libfilter.' : </p><select class="isotope_select" data-filter-group="cat">';
		    $filter_cat_div .= '<option>'.$liball.'</option>';
		    foreach ($sortFilter as $key => $filter) {
		        $aff = $cats_lib[$key];
		        $aff_alias = $cats_alias[$key];
		        if (!is_null($aff)) {
					$selected = "";
					if ($default_cat == $aff_alias) {$selected = "selected";}
					$filter_cat_div .= '<option value="'.$aff_alias.'" '.$selected.'>'. Text::_($aff).'</option>';
		        }
		    }
		    $filter_cat_div .= '</select>';
		    $filter_cat_div .= '</div>';
		}
	}
	if (( $article_cat_tag  == "tags") || ($article_cat_tag  == "cattags")) {
	    // tri des tags
		$sortFilter = array();
		$alias=array();
		if (count($this->tags_list) > 0) {
			foreach ($filters['tags'] as $filter) {
				$sortFilter[] = '&'.$filter[0]->tag;
				$alias[$filter[0]->tag] = $filter[0]->alias;
			}
		} else { // empty tags list: take all tags found in articles
			foreach ($this->tags as $key=>$value) {
				$sortFilter[] = "&".$value;
				$alias[$value] = $this->tags_alias[$key];
			}
		}
		if ($tagsfilterorder == "false") {
			asort($sortFilter,  SORT_STRING | SORT_FLAG_CASE | SORT_NATURAL ); // alphabetic order
		}
	    if (($displayfiltertags == "button") || ($displayfiltertags == "multi") || ($displayfiltertags == "multiex") ) {
			$checked = "";
			if ($this->default_tag == "") {
				$checked = "is-checked";
			}
			if ($tagsfilterparent != "true") {
        		$filter_tag_div .= '<div class="isotope_button-group filter-button-group-tags fg-c'.$layouts["tag"]->div_width.' fg-cs12 '.$layouts["tag"]->div_align.'" data-filter-group="tags">';
				$filter_tag_div .= '<button class="'.$button_bootstrap.'  iso_button_cat_tout '.$checked.'" data-sort-value="*" />'.$liball.'</button>';
			} else {
        		$filter_tag_div .= '<div class="fg-c'.$layouts["tag"]->div_width.' fg-cs12 '.$layouts["tag"]->div_align.'">';
			}
			$cur_parent = '';
			foreach ($sortFilter as $aval) {
				$res = explode("&",$aval);
				$filter = $res[1];
				$parent = $res[0];
				if (($tagsfilterparent == "true") && ($cur_parent != $parent) )  {
					if ($cur_parent != '') $filter_tag_div .= "</div>";
					$cur_parent = $parent;
					$filter_tag_div .= '<div class="isotope_button-group filter-button-group-tags" data-filter-group="tags">';
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
                                                if (property_exists($tagimage,'image_image_fulltext') || property_exists($tagimage,'image_intro')) {
                                                    if ($tagimage->image_intro != "") {
							$img = '<img src="'.JURI::root().$tagimage->image_intro.'" style="float:'.$tagimage->float_intro.'" 
							class="iso_tag_img" alt="'.$tagimage->image_intro_alt.'" title="'.$tagimage->image_intro_caption.'"/> ';
                                                    } elseif ($tagimage->image_fulltext != "") {
							$img = '<img src="'.JURI::root().$tagimage->image_fulltext.'" style="float:'.$tagimage->float_fulltext.'" 
							class="iso_tag_img" alt="'.$tagimage->image_fulltext_alt.'" title="'.$tagimage->image_fulltext_caption.'"/> ';
                                                    }
                                                }
					}
					$checked = "";
					if ($this->default_tag == $aff_alias) {$checked = "is-checked";}
					$filter_tag_div .= '<button class="'.$button_bootstrap.'  iso_button_tags_'.$aff_alias.' '.$checked.'" data-sort-value="'.$aff_alias.'" />'.$img.Text::_($aff).'</button>';
				}
			}
			if ($tagsfilterparent == "true") $filter_tag_div .= '</div>';
                        $filter_tag_div .= '</div>';
		}   else  {	// affichage Liste
			$filter_tag_div .= '<div class="isotope_button-group filter-button-group-tags fg-c'.$layouts["tag"]->div_width.' fg-cs12 '.$layouts["tag"]->div_align.'" data-filter-group="tags">';
			$filter_tag_div .= '<p class="hidden-phone" >'.$libfilter.' : </p><select class="isotope_select" data-filter-group="cat">';
			$filter_tag_div .= '<option>'.$liball.'</option>';
			foreach ($sortFilter as $aval) {
				$res = explode("&",$aval);
				$filter = $res[1];
				$parent = $res[0];
			    $aff = $filter; 
			    $aff_alias = $alias[$filter];
				if (!is_null($aff)) {
					$selected = "";
					if ($default_tag == $aff_alias) {$selected = "selected";}
					$filter_tag_div .= '<option value="'.$aff_alias.'" '.$selected.'>'. Text::_($aff).'</option>';
				}
			}
			$filter_tag_div .= '</select>';
			$filter_tag_div .= '</div>';
		}
	}
   }
 }
//============================= isotope grid =============================================//
$width = $layouts["iso"]->div_width;
$isotope_grid_div = '<div class="isotope_grid fg-c'.$width.' fg-cs12" style="padding:0">'; // bootstrap : suppression du padding pour isotope
foreach ($this->list as $key=>$category) {
	foreach ($category as $item) {
		$tag_display = "";
		$tag_img = "";
		$cat_img = "";
		if (($article_cat_tag  == "tags") || ($article_cat_tag  == "cattags")) { // filtre tag
			foreach ($this->article_tags[$item->id] as $tag) {
				$tag_display .= " ".$this->tags_alias[$tag->id];
				$tagimage  = json_decode($this->tags_image[$this->tags_alias[$tag->id]]);
				if (!$tagimage) continue;
                                if ((!property_exists($tagimage,'image_image_fulltext')) || ($tagimage->image_fulltext == "") && ($tagimage->image_intro == ""))  continue;
        				if ($tagimage->image_intro != "") {
						$tag_img .= '<img src="'.JURI::root().$tagimage->image_intro.'" style="float:'.$tagimage->float_intro.'" 
									class="iso_tag_img_art" alt="'.$tagimage->image_intro_alt.'" title="'.$tagimage->image_intro_caption.'"/> ';
					} elseif ($tagimage->image_fulltext != "") {
						$tag_img .=  '<img src="'.JURI::root().$tagimage->image_fulltext.'" style="float:'.$tagimage->float_fulltext.'" 
								class="iso_tag_img_art" alt="'.$tagimage->image_fulltext_alt.'" title="'.$tagimage->image_fulltext_caption.'"/> ';
				};
			}
		} else { // filtre catgories
		    $tag_display =  $this->iso_entree == "webLinks"? $this->cats_alias[$item->catid] : $item->category_alias;
		}
		$cat_params = json_decode($this->cats_params[$item->catid]);
		if (($cat_params) && ($cat_params->image != "")) {
			$cat_img = "<img src='".JURI::root().$cat_params->image."' alt='".$cat_params->image_alt."' class='iso_cat_img_art'/>";
		}
		$field_value = "";
		$field_cust = array();
		if (isset($this->article_fields) and array_key_exists($item->id,$this->article_fields)) { 
			foreach ($this->article_fields[$item->id] as $key_f=>$tag_f) {
                if (is_array($tag_f)) { // multiple answers
                    $afield = "";
                    foreach ($tag_f as $avalue) {
                        $obj = $this->fields[$avalue];
                        $afield .= $afield=="" ?$obj->val : ", ".$obj->val;
                    }
                    $field_cust['{'.$key_f.'}'] = $afield;
                    $field_value .= " ".implode(' ',$tag_f);
                } else { // one field
			      $obj = $this->fields[$tag_f];
			      if ((count($params_fields) == 0) ||  (in_array($obj->field_id, $params_fields))) {
      			     $field_value .= " ".$tag_f;
			      }
      		      $field_cust['{'.$key_f.'}'] = $obj->val; // field display value
                }
			};
		}
		$ladate = $item->displayDate;
		$data_cat = $this->cats_alias[$item->catid];
		if (isset($item->rating)) {
			$item->rating = $item->rating;
		} else {
			$item->rating ='';
			$item->rating_count = 0;
		}
		$click = $item->hits;
		$t = "";
		$c = "";
		if ($blocklink == "true") { 
			$t = 'onclick=go_click("'.$this->iso_entree.'","'.$item->link.'")';
			$c = 'isotope_pointer'; // add class cursor = pointer
		}
		$isotope_grid_div .=  '<div class="isotope_item iso_cat_'.$item->catid.' '.$tag_display.' '.$c.'" data-title="'.$item->title.'" data-category="'.$data_cat.'" data-date="'.$ladate.'" data-click="'.$click.'" data-rating="'.$item->rating.'" data-id="'.$item->id.'" data-lang="'.$item->language.'" data-alpha="'.substr($item->title,0,1).'" '.$t.'>';
    // 01.00.07 : Depuis ajout de Lomart :  prise en charge sous-titre par tilde et badge nouveau
		$item->new = "";
		if ($this->iso_params->get('btnnew','false') == 'true') {
			$nbday = $this->iso_params->get('new_limit',0);
			$tmp = date('Y-m-d H:i:s', mktime(date("H"), date("i"), 0, date("m"), date("d")-intval($nbday), date("Y")));    
			$item->new = ($tmp < $item->publish_up) ? ' <span class="iso_badge_new">'.Text::_('CG_ISO_NEW').'</span> ' : '';
		}
		$item->subtitle = "";
		if ($this->iso_params->get('btnsubtitle','false') == 'true') {
        // Preparation du titre
        // mise en SMALL du texte apres ~ (tilde) 
        // + BR si texte > 10 car
			list($title, $item->subtitle) = explode('~',$item->title . '~');
			$item->title = trim($title); 
			if ($item->subtitle) {
				$item->subtitle = '<small>'.trim($item->subtitle).'</small>';
			}
		}
		$canEdit = $user->authorise('core.edit', 'com_content.article.'.$item->id);
		if ($canEdit) {
			$isotope_grid_div .= '<span class="edit-icon">';
			$isotope_grid_div .= '<a href="index.php?option=com_content&task=article.edit&a_id='.$item->id.'&return='.base64_encode($uri).'">';
			$isotope_grid_div .= '<img src="media/system/images/edit.png" alt="modifier" class="iso-img-max-width"></a>';
			$isotope_grid_div .= '</span>';
		}
		if ($titlelink == "true") { 
			$title = '<a href="'.$item->link.'">'.$item->title.'</a>';
		} else {
			$title = $item->title;
		}
		$rating = '';
		for ($i = 1; $i <= $item->rating; $i++) {
			$rating .= '<img src="'.$comfield.'images/icon.png" />';
		}
		// plugin phocaount => parameter {count}
		$phocacount = CGHelper::getArticlePhocaCount($item->fulltext);
		$choixdate = $this->iso_params->get('choixdate', 'modified');
		$libdate = $choixdate == "modified" ? $libupdated : $libcreated;
		$perso = $this->iso_params->get('perso');
		$perso = CGHelper::checkNullFields($perso,$item,$phocacount); // suppress null field if required			
		$arr_css= array("{title}"=>$title, "{cat}"=>$this->cats_lib[$item->catid],"{date}"=>$libdate.JHtml::_('date', $item->displayDate, $libdateformat), "{visit}" =>$item->hits, "{intro}" => $item->introtext,"{stars}"=>$rating,"{rating}"=>$item->rating,"{ratingcnt}"=>$item->rating_count,"{count}"=>$phocacount,"{tagsimg}" => $tag_img, "{catsimg}" => $cat_img, "{link}" => $item->link, "{introimg}"=>$item->introimg, "{subtitle}" => $item->subtitle, "{new}" => $item->new);
		foreach ($arr_css as $key_c => $val_c) {
			$perso = str_replace($key_c, Text::_($val_c),$perso);
		}
		foreach ($field_cust as $key_f => $val_f) { // affichage du contenu des champs personnalis?es
			$perso = str_replace($key_f, Text::_($val_f),$perso);
		}
		$perso = CGHelper::checkNoField($perso); // suppress empty fields
		// additionnal perso in isotope plugin
		$item_cls = new stdClass;
		$item_cls->id = $item->id;
		$item_cls->text = $perso;
		$item_cls->params = $this->iso_params;
		Factory::getApplication()->triggerEvent('onCGIsotopeRender', array ('com_cgisotope.article', &$item_cls,&$item_cls->params, 0)); 		
		$perso = 	$item_cls->text;	
		
		$isotope_grid_div .= $perso;
		if ($this->iso_params->get('readmore','false') !='false') {
			$isotope_grid_div .= '<p class="isotope-readmore">';
			$isotope_grid_div .= '<a class="isotope-readmore-title"  data-articleid="'.$item->id.'" data-href="'.$item->link.'" href="'.$item->link.'">';
			$isotope_grid_div .= Text::_('CG_ISO_READMORE');
			$isotope_grid_div .= '</a></p>';
		}
		$isotope_grid_div .= '</div>';
	}
}
// ============================alpha div ==============================================//
$isotope_alpha_div = "";
if ($displayalpha != "false") {
    $isotope_alpha_div = '<div class="isotope_button-group filter-button-group-alpha iso_alpha fg-row fg-c'.$layouts["alpha"]->div_width.' fg-cs12 '.$layouts["alpha"]->div_align.'" data-filter-group="alpha">';
	$isotope_alpha_div .= CGHelper::create_alpha_buttons($this,$button_bootstrap);
    $isotope_alpha_div .= '</div>';
}
// =============================Lang. filter ============================================//
$isotope_lang_div = "";
if (($language_filter == "button") || ($language_filter == "multi")) {
    $isotope_lang_div = '<div class="isotope_button-group iso_lang fg-row fg-c'.$layouts["lang"]->div_width.' fg-cs12 '.$layouts["lang"]->div_align.'" data-filter-group="lang">';
	$isotope_lang_div .= CGHelper::create_language_buttons($this,$button_bootstrap);
    $isotope_lang_div .= '</div>';
}
//=====================================layouts==============================================//
ksort($layouts_order,  SORT_STRING | SORT_FLAG_CASE | SORT_NATURAL ); // order
$val = 0;
foreach ($layouts_order as $layout) {
    $key = (string)$layout;
    $obj = $layouts[$key];
	$val += $obj->div_width;
	if ($line == 0) $line = $obj->div_line; 
	if (($val > 12) || ( ($obj->div_width == 12) && ($val > 12)) || ($line < $obj->div_line)) { // new line needed
		if (($obj->div == "iso") && ($obj->div_width == 12)) {
			echo "</div><div>";
		} else {
			echo "</div><div class='fg-row'>";
		}
		$val = $obj->div_width;
		if ($line < $obj->div_line) { // requested new line
			$line = $obj->div_line; 
		} else { // calculated new line
			$line += 1; 
		}
	}
	if ($obj->div == "search") echo $search_div;
	if ($obj->div == "sort") echo $sort_buttons_div;
	if ($obj->div == "cat") echo $filter_cat_div;
	if ($obj->div == "tag") echo $filter_tag_div;
	if ($obj->div == "lang") echo $isotope_lang_div;
	if ($obj->div == "iso") echo $isotope_grid_div;
	if ($obj->div == "alpha") echo $isotope_alpha_div;	
}
?>
</div>
</div>
<?php 
// view article in isotope component
$width = $layouts["iso"]->div_width;
if ($this->iso_params->get('readmore','false') =='iframe') {
   echo '<div id="isotope_an_article" class="isotope_an_article fg-c'.$width.' fg-cs12 isotope-hide" ><button type="button" class="close">X</button><iframe src="" id="isotope_article_frame"></iframe></div>'; 
} elseif ($this->iso_params->get('readmore','false') =='ajax') {
	echo '<input id="token" type="hidden" name="' . JSession::getFormToken() . '" value="1" />';
	echo '<div id="isotope_an_article" class="isotope_an_article fg-c'.$width.' fg-cs12 isotope-hide" ></div>'; 
}
?>
<div class="iso_div_empty iso_hide_elem">
	<?php echo Text::_('CG_ISO_EMPTY'); ?>
</div>
<div class="iso_div_more">
<button class="<?php echo $button_bootstrap;?> iso_button_more"><?php echo $libmore;?></button>
</div>
<?php if (($this->iso_entree != "webLinks") && ($this->iso_params->get('pagination','true') == 'true')) {?> 
<div class="pagination isotope-pagination">	
<?php  $lapagination = $this->pagination->getPagesLinks($this->params);
	echo $lapagination; 
?>
</div>
<?php } ?>
</div>
<?php if (strlen(trim($this->iso_params->get('bottom'))) > 0) {
	// apply content plugins on weblinks
	$item_cls = new stdClass;
	$item_cls->text = $this->iso_params->get('bottom');
	$item_cls->params = $this->params;
    $item_cls->id= $com_id;
	Factory::getApplication()->triggerEvent('onContentPrepare', array ('com_cgisotope.content', &$item_cls, &$item_cls->params, 0)); // Joomla 4.0
	$bottom = 	$item_cls->text;	
    $bottom = str_replace('{cg-version}',CGHelper::getCGVersion(), $bottom);
    $bottom = str_replace('{cg-name}',CGHelper::getCGName(), $bottom);
	echo $bottom; 
	}
?>

