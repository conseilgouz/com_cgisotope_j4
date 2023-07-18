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
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;

// HTMLHelper::_('behavior.tooltip');
HTMLHelper::_('behavior.multiselect');

$user		= Factory::getUser();
$userId		= $user->get('id');
$canOrder	= ContentHelper::getActions('com_cgisotope');
?>
<form action="<?php echo Route::_('index.php?option=com_cgisotope&view=import'); ?>" method="post" name="adminForm" id="adminForm">
	<?php if (!empty( $this->sidebar)) : ?>
	<div id="j-sidebar-container" class="span2">
		<?php echo $this->sidebar; ?>
	</div>
	<div id="j-main-container" class="span10">
	<?php else : ?>
	<div id="j-main-container">
	<?php endif; ?>

	<div class="clr"> </div>
	<h1><?=Text::_('COM_CGISOTOPE_IMPORT_MODULE_DESC')?></h1>
	<h2><?php echo Text::_('CG_ISO_IMPORT_ALREADY');?></h2>
    <?php if (empty($this->pages)) : ?>
        <div class="alert alert-no-items">
            <?php echo Text::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
        </div>
    <?php else : ?>   
    <ul>
		<?php foreach ($this->pages as $i => $page) :
			?>
				<li><?php echo $this->escape($page->title); ?>
                    <?php 
						$compl = new Registry($page->page_params);
						$text = "";
						$msg = "";
						if ($compl['cat_or_tag'] == "cattags") $msg = Text::_('CG_ISO_CAT_TAGS_CAT_TAGS');
						if ($compl['cat_or_tag'] == "cat") $msg = Text::_('CG_ISO_CAT_TAGS_CAT');
						if ($compl['cat_or_tag'] == "tags") $msg = Text::_('CG_ISO_CAT_TAGS_TAGS');
						if ($compl['cat_or_tag'] == "fields") $msg = Text::_('CG_ISO_CAT_TAGS_FIELDS');
						$text .= $compl['iso_entree'].' : '.$msg;
						if (strlen($text) > 70) $text = substr($text,0,70).'...';
						echo '('.$text.')'; ?>                     
				</li>
			<?php endforeach; ?>
	</ul>
<?php endif; ?>        
	<h2><?php echo Text::_('CG_ISO_IMPORT_TODO');?></h2>
        <table class="table table-striped" id="articleList">
		<thead>
			<tr>
				<th width="1%">
					<input type="checkbox" name="checkall-toggle" value="" title="<?php echo Text::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)" />
				</th>
				<th class="center">
					<?php echo Text::_('CG_ISO_TITLE'); ?>
				</th>
                                 <th width="15%">
                                    <?php echo Text::_( 'CG_ISO_LANGUAGE'); ?>
                                </th>
				<th width="5%">
					<?php echo Text::_('JSTATUS'); ?>
				</th>
				
			</tr>
		</thead>
		<tbody>
                    <?php foreach ($this->modules as $i => $module) :
			?>
			<tr class="row<?php echo $i % 2; ?>">
				<td class="center">
					<?php echo HTMLHelper::_('grid.id', $i, $module['id']); ?>
				</td>
				<td class="center">
                    <?php echo $this->escape($module['title']); ?>                     
				</td>
                <td align="center">
                    <?php 
                        $lang = new \stdClass();
                        $lang->language = $module['language'];
                        $lang->language_image = str_replace('-','_',strtolower((string)$module['language']));
						$lang->language_title = $module['language'];
                        echo LayoutHelper::render('joomla.content.language', $lang); ?>
                </td>
				<td>
				      <?php echo HTMLHelper::_('jgrid.published', $module['published'], $i, 'import.', false, 'cb'); ?>                  
				</td>
			</tr>
                <?php endforeach; ?>
		</tbody>
	</table>
	<div>
		<input type="hidden" name="task" value="import" />
		<input type="hidden" name="boxchecked" value="0" />
		<?php echo HTMLHelper::_('form.token'); ?>
	</div>
	</div>
</form>
