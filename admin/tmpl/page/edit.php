<?php/*** CG Isotope Component  - Joomla 4.x/5.x Component * Version			: 3.2.1* Package			: CG ISotope* copyright 		: Copyright (C) 2023 ConseilGouz. All rights reserved.* license    		: http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL* From              : isotope.metafizzy.co*/// no direct accessdefined('_JEXEC') or die;use Joomla\CMS\HTML\HTMLHelper;use Joomla\CMS\Language\Text;use Joomla\CMS\Router\Route;use Joomla\CMS\Factory;use Joomla\CMS\Uri\Uri;HtmlHelper::_('formbehavior.chosen', 'select');$document = Factory::getDocument();$comfield	= ''.URI::base(true).'/components/com_cgisotope/';$document->addStyleSheet($comfield.'admincss/gene.css');$document->addStyleSheet($comfield.'admincss/iso.css');$document->addStyleSheet($comfield.'admincss/layout.css');$document->addStyleSheet($comfield.'admincss/perso.css');$document->addStyleSheet($comfield.'admincss/depend.css');/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */$wa = $this->document->getWebAssetManager();$wa->useScript('keepalive')	->useScript('form.validate');?><div class="span12"><div class="nr-app nr-app-page">    <div class="nr-row">        <div class="nr-main-container">            <div class="nr-main-content">        		<form action="<?php echo Route::_('index.php?option=com_cgisotope&layout=edit&id='.(int) $this->item->id); ?>" method="post" name="adminForm"  class="form-validate" id="page-form" >    		      <div class="form-horizontal">                    	<?php                             echo HTMLHelper::_('uitab.startTabSet', 'tab', array('active' => 'gene_tab'));                            foreach ($this->form->getFieldSets() as $key => $fieldset)                            {                                echo HTMLHelper::_('uitab.addTab', 'tab', $fieldset->name, Text::_($fieldset->label));                                echo $this->form->renderFieldSet($fieldset->name);                                echo HTMLHelper::_('uitab.endTab');                            }                            echo HTMLHelper::_('uitab.endTabSet');                        ?>        		    </div>        		    <?php echo HTMLHelper::_('form.token'); ?>				    <input type="hidden" name="task" value="" />        		</form>            </div>        </div>    </div></div></div>