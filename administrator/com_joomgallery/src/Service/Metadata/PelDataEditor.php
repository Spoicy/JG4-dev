<?php

/**
 ******************************************************************************************
 **   @version    4.0.0-dev                                                              **
 **   @package    com_joomgallery                                                        **
 **   @author     JoomGallery::ProjectTeam <team@joomgalleryfriends.net>                 **
 **   @copyright  2008 - 2024  JoomGallery::ProjectTeam                                  **
 **   @license    GNU General Public License version 3 or later                          **
 *****************************************************************************************/

namespace Joomgallery\Component\Joomgallery\Administrator\Service\Metadata;

use \lsolesen\pel\PelEntryAscii;
use \lsolesen\pel\PelEntryCopyright;
use \lsolesen\pel\PelEntryLong;
use \lsolesen\pel\PelEntryShort;
use \lsolesen\pel\PelEntryTime;
use \lsolesen\pel\PelEntryUserComment;
use \lsolesen\pel\PelFormat;
use \lsolesen\pel\PelIfd;
use \lsolesen\pel\PelTag;

// No direct access
\defined('_JEXEC') or die;
require __DIR__ . '/../../../../../vendor/autoload.php';

/**
 * Editor class to handle exif data type editing
 * 
 * @package JoomGallery
 * @since 4.0.0
 */
class PelDataEditor {

    protected static $timeTags = [PelTag::DATE_TIME, PelTag::DATE_TIME_ORIGINAL, PelTag::DATE_TIME_DIGITIZED];

    public function makeEdit(PelIfd $ifd, int $tag, mixed $data, int $format) {
        $entry = $ifd->getEntry($tag);
        /*if ($entry == null) {
            $entry = new PelEntryAscii($tag, $data);
            $ifd->addEntry($entry);
        } else {
            $entry->setValue($data);
        }*/
        
        if (in_array($tag, self::$timeTags)) {
            if ($entry == null) {
                $entry = new PelEntryTime($tag, $data, PelEntryTime::EXIF_STRING);
                $ifd->addEntry($entry);
            } else {
                $entry->setValue($data, PelEntryTime::EXIF_STRING);
            }
        } else if ($tag == PelTag::COPYRIGHT) {
            if ($entry == null) {
                $entry = new PelEntryCopyright($data[0], $data[1]);
                $ifd->addEntry($entry);
            } else {
                $entry->setValue($data[0], $data[1]);
            }
        } else if ($tag == PelTag::USER_COMMENT) {
            if ($entry == null) {
                $entry = new PelEntryUserComment($data);
                $ifd->addEntry($entry);
            } else {
                $entry->setValue($data);
            }
        } else {
            $entryClass = "\lsolesen\pel\PelEntry" . PelFormat::getName($format);
            if ($entry == null) {
                $entry = new $entryClass($tag, $data);
                $ifd->addEntry($entry);
            } else {
                $entry->setValue($data);
            }
        }
    }
}