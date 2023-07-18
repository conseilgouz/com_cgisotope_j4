<?php
/**
* CG Isotope Component  - Joomla 4.x/5.x Component 
* Version			: 3.2.1
* Package			: CG ISotope
* copyright 		: Copyright (C) 2023 ConseilGouz. All rights reserved.
* license    		: http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
* From              : isotope.metafizzy.co
*/
// no direct access
defined('_JEXEC') or die;
use Joomla\Registry\Registry;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Layout\LayoutHelper;

// JHtml::_('behavior.tooltip');
HTMLHelper::_('behavior.multiselect');

$user		= Factory::getUser();
$userId		= $user->get('id');
$listOrder	= $this->escape($this->state->get('list.ordering'));
$listDirn	= $this->escape($this->state->get('list.direction'));
$canOrder	= ContentHelper::getActions('com_cgisotope');
$saveOrder	= $listOrder=='ordering';
?>
<form action="<?php echo Route::_('index.php?option=com_cgisotope&view=pages'); ?>" method="post" name="adminForm" id="adminForm">
	<?php if (!empty( $this->sidebar)) : ?>
	<div id="j-sidebar-container" class="span2">
		<?php echo $this->sidebar; ?>
	</div>
	<div id="j-main-container" class="span10">
	<?php else : ?>
	<div id="j-main-container">
	<?php endif; ?>
	<div id="filter-bar" class="btn-toolbar">
		<div class="filter-search btn-group pull-left">
			<label for="filter_search" class="element-invisible"><?php echo Text::_('JSEARCH_FILTER_LABEL'); ?></label>  
			<input type="text" name="filter_search" id="filter_search" value="<?php echo $this->escape($this->state->get('filter.search')); ?>" title="<?php echo Text::_('COM_CGISOTOPE_SEARCH_IN_TITLE'); ?>" />
        </div>
        <div class="btn-group pull-left">            
			<button type="submit" class="btn hasTooltip"><?php echo Text::_('JSEARCH_FILTER_SUBMIT'); ?></button>
			<button type="button" class="btn hasTooltip" onclick="document.id('filter_search').value='';this.form.submit();"><?php echo Text::_('JSEARCH_FILTER_CLEAR'); ?></button>
		</div>
		<div class="btn-group pull-right hidden-phone">
			
					<select name="filter_state" class="inputbox" onchange="this.form.submit()">
						<option value=""><?php echo Text::_('JOPTION_SELECT_PUBLISHED');?></option>
						<?php echo HtmlHelper::_('select.options', HtmlHelper::_('jgrid.publishedOptions'), 'value', 'text', $this->state->get('filter.state'), true);?>
					</select>
		</div>
	</div>
	<div class="clr"> </div>

    <?php if (empty($this->items)) : ?>
        <div class="alert alert-no-items">
            <?php echo Text::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
        </div>
    <?php else : ?>   
    <table class="table table-striped" id="articleList">
		<thead>
			<tr>
				<th width="1%">
					<input type="checkbox" name="checkall-toggle" value="" title="<?php echo Text::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)" />
				</th>
				<th width="5%" class="nowrap">
					<?php echo HtmlHelper::_('grid.sort', 'JGRID_HEADING_ID', 't.id', $listDirn, $listOrder); ?>
				</th>
				<th class="center">
					<?php echo HtmlHelper::_('grid.sort',  'CG_ISO_TITLE', 't.title', $listDirn, $listOrder); ?>
				</th>
				<th class="center">
					<?php echo HtmlHelper::_('grid.sort',  'CG_ISO_SECTIONS', 't.info', $listDirn, $listOrder); ?>
				</th>
               <th width="15%">
                    <?php echo HtmlHelper::_('searchtools.sort', 'CG_ISO_LANGUAGE', 'language', $listDirn, $listOrder); ?>
                </th>
				<th width="5%">
					<?php echo HtmlHelper::_('grid.sort', 'JSTATUS', 'state', $listDirn, $listOrder); ?>
				</th>
				
			</tr>
		</thead>
		<tfoot>
			<tr>
				<td colspan="13">
					<?php echo $this->pagination->getListFooter(); ?>
				</td>
			</tr>
		</tfoot>
		<tbody>
		<?php foreach ($this->items as $i => $page) :
			$ordering	= ($listOrder == 'ordering');
			$canCreate	= $user->authorise('core.create');
			$canEdit	= $user->authorise('core.edit');
			$canCheckin	= $user->authorise('core.manage',		'com_checkin') || $page->checked_out == $userId || $page->checked_out == 0;
			$canChange	= $user->authorise('core.edit.state') && $canCheckin;
			?>
			<tr class="row<?php echo $i % 2; ?>">
				<td class="center">
					<?php echo HtmlHelper::_('grid.id', $i, $page->id); ?>
				</td>
				<td class="center">
                    <a href="<?php echo Route::_('index.php?option=com_cgisotope&task=page.edit&id='.(int) $page->id); ?>">
                    <?php echo $this->escape($page->id); ?>                     
					</a>
				</td>
				<td class="center">
					<a href="<?php echo Route::_('index.php?option=com_cgisotope&task=page.edit&id='.(int) $page->id); ?>">
                    <?php echo $this->escape($page->title); ?>                     
					</a>
				</td>
				<td class="center">
                    <?php 
						$compl = new Registry($page->page_params);
						$text = "";
						$msg = "";
						if ($compl['cat_or_tag'] == "cattags") $msg = Text::_('CG_ISO_CAT_TAGS_CAT_TAGS');
						if ($compl['cat_or_tag'] == "cat") $msg = Text::_('CG_ISO_CAT_TAGS_CAT');
						if ($compl['cat_or_tag'] == "tags") $msg = Text::_('CG_ISO_CAT_TAGS_TAGS');
						if ($compl['cat_or_tag'] == "fields") $msg = Text::_('CG_ISO_CAT_TAGS_FIELDS');
						if ($compl['cat_or_tag'] == "catfields") $msg = Text::_('CG_ISO_CAT_FIELDS');
						if ($compl['cat_or_tag'] == "tagsfields") $msg = Text::_('CG_ISO_TAGS_FIELDS');
						if ($compl['cat_or_tag'] == "cattagsfields") $msg = Text::_('CG_ISO_CAT_TAGS_ALL');
						$text .= $compl['iso_entree'].' : '.$msg;
						if (strlen($text) > 70) $text = substr($text,0,70).'...';
						echo $text; ?>                     
				</td>
                <td align="center">
                    <?php echo LayoutHelper::render('joomla.content.language', $page); ?>
                </td>
				<td class="center">
					<?php echo HTMLHelper::_('jgrid.published', $page->state, $i, 'pages.', $canChange, 'cb'); ?>
				</td>
			</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
    <?php endif; ?> 
	<div>
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="boxchecked" value="0" />
		<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
		<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />
		<?php echo HtmlHelper::_('form.token'); ?>
	</div>
	</div>
</form>

