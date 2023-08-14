<?php
/**
******************************************************************************************
**   @version    4.0.0                                                                  **
**   @package    com_joomgallery                                                        **
**   @author     JoomGallery::ProjectTeam <team@joomgalleryfriends.net>                 **
**   @copyright  2008 - 2022  JoomGallery::ProjectTeam                                  **
**   @license    GNU General Public License version 2 or later                          **
*****************************************************************************************/

// No direct access
defined('_JEXEC') or die;

use \Joomla\CMS\Uri\Uri;
use \Joomla\CMS\Router\Route;
use \Joomla\CMS\Language\Text;
use \Joomla\CMS\Session\Session;
use \Joomla\CMS\HTML\HTMLHelper;
use Joomgallery\Component\Joomgallery\Administrator\Helper\JoomHelper;

$wa = $this->document->getWebAssetManager();
$wa->useStyle('com_joomgallery.site');

$canEdit    = $this->acl->checkACL('edit', 'com_joomgallery.image', $this->item->id);
$canDelete  = $this->acl->checkACL('delete', 'com_joomgallery.image', $this->item->id);
$canCheckin = $this->acl->checkACL('manage', 'com_joomgallery');
?>

<h2><?php echo $this->item->imgtitle; ?></h2>

<?php if($canEdit || $canCheckin || $canDelete): ?>
  <div class="btn-group mb-3" role="group">
    <?php if ($canEdit && $this->item->checked_out == 0): ?>
      <a class="btn btn-outline-primary" href="<?php echo Route::_('index.php?option=com_joomgallery&task=image.edit&id='.$this->item->id); ?>">
        <?php echo Text::_("JGLOBAL_EDIT"); ?>
      </a>
    <?php elseif ($canCheckin && $this->item->checked_out > 0) : ?>
      <a class="btn btn-outline-primary" href="<?php echo Route::_('index.php?option=com_joomgallery&task=image.checkin&id=' . $this->item->id .'&'. Session::getFormToken() .'=1'); ?>">
        <?php echo Text::_("JLIB_HTML_CHECKIN"); ?>
      </a>
    <?php endif; ?>

    <?php if ($canDelete) : ?>

      <a class="btn btn-danger" rel="noopener noreferrer" href="#deleteModal" role="button" data-bs-toggle="modal">
        <?php echo Text::_("JACTION_DELETE"); ?>
      </a>

      <?php echo HTMLHelper::_(
                                'bootstrap.renderModal',
                                'deleteModal',
                                array(
                                    'title'  => Text::_('JACTION_DELETE'),
                                    'height' => '50%',
                                    'width'  => '20%',

                                    'modalWidth'  => '50',
                                    'bodyHeight'  => '100',
                                    'footer' => '<button class="btn btn-outline-primary" data-bs-dismiss="modal">Close</button><a href="' . Route::_('index.php?option=com_joomgallery&task=image.remove&id=' . $this->item->id, false, 2) .'" class="btn btn-danger">' . Text::_('COM_JOOMGALLERY_COMMON_DELETE_IMAGE_TIPCAPTION') .'</a>'
                                ),
                                Text::_('COM_JOOMGALLERY_COMMON_ALERT_SURE_DELETE_SELECTED_ITEM')
                              );
      ?>
    <?php endif; ?>
  </div>
<?php endif; ?>

<?php // Image ?>
<figure class="figure joom-image text-center center">
  <div class="joom-loader"><img src="<?php echo Uri::root(true); ?>/media/system/images/ajax-loader.gif" alt="loading..."></div>
  <img src="<?php echo JoomHelper::getImg($this->item, 'detail'); ?>" class="figure-img img-fluid rounded" alt="<?php echo $this->item->imgtitle; ?>" style="width:auto;" itemprop="image" loading="lazy">
  <figcaption class="figure-caption"><?php echo nl2br($this->item->imgtext); ?></figcaption>
</figure>

<?php // Image info and fields ?>
<div class="item_fields">
  <h3><?php echo Text::_('COM_JOOMGALLERY_IMAGE_INFO'); ?></h3>
	<table class="table">
		<tr>
			<th><?php echo Text::_('JCATEGORY'); ?></th>
			<td>
        <a href="<?php echo Route::_('index.php?option=com_joomgallery&view=category&id='.(int) $this->item->catid); ?>">
          <?php echo $this->escape($this->item->cattitle); ?>
        </a>
      </td>
		</tr>

		<tr>
			<th><?php echo Text::_('JAUTHOR'); ?></th>
			<td><?php echo $this->escape($this->item->imgauthor); ?></td>
		</tr>

		<tr>
			<th><?php echo Text::_('COM_JOOMGALLERY_IMGDATE'); ?></th>
			<td><?php echo $this->escape($this->item->imgdate); ?></td>
		</tr>

    <tr>
			<th><?php echo Text::_('JGLOBAL_HITS'); ?></th>
			<td><?php echo (int) $this->item->hits; ?></td>
		</tr>

    <tr>
			<th><?php echo Text::_('COM_JOOMGALLERY_DOWNLOADS'); ?></th>
			<td><?php echo (int) $this->item->downloads; ?></td>
		</tr>

    <tr>
			<th><?php echo Text::_('COM_JOOMGALLERY_IMAGE_RATING'); ?></th>
			<td><?php echo $this->item->imgvotesum; ?> (<?php echo $this->item->imgvotes.' '.Text::_('COM_JOOMGALLERY_VOTES'); ?>)</td>
		</tr>

		<tr>
			<th><?php echo Text::_('COM_JOOMGALLERY_IMGMETADATA'); ?></th>
			<td><?php echo nl2br($this->item->imgmetadata); ?></td>
		</tr>
	</table>
</div>
