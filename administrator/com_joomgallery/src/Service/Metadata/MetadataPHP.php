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

// No direct access
\defined('_JEXEC') or die;
require __DIR__ . '/../../../../../vendor/autoload.php';

use \Joomla\CMS\Factory;
use \Joomgallery\Component\Joomgallery\Administrator\Helper\JoomHelper;
use \Joomgallery\Component\Joomgallery\Administrator\Extension\ServiceTrait;
use \Joomgallery\Component\Joomgallery\Administrator\Service\Metadata\Metadata as BaseMetadata;

use \lsolesen\pel\Pel;
use \lsolesen\pel\PelDataWindow;
use \lsolesen\pel\PelEntryAscii;
use \lsolesen\pel\PelExif;
use \lsolesen\pel\PelFormat;
use \lsolesen\pel\PelIfd;
use \lsolesen\pel\PelJpeg;
use \lsolesen\pel\PelTag;
use \lsolesen\pel\PelTiff;

/**
 * PHP implementation of Metadata Class
 *
 * @package JoomGallery
 * @since   4.0.0
 */
class MetadataPHP extends BaseMetadata implements MetadataInterface
{
  use ServiceTrait;

  public static $entryTypes = [
    PelTag::IMAGE_DESCRIPTION => PelFormat::ASCII,
    PelTag::MAKE => PelFormat::ASCII,
    PelTag::MODEL => PelFormat::ASCII,
    PelTag::ORIENTATION => PelFormat::SHORT,
    PelTag::X_RESOLUTION => PelFormat::RATIONAL,
    PelTag::Y_RESOLUTION => PelFormat::RATIONAL,
    PelTag::RESOLUTION_UNIT => PelFormat::SHORT,
    PelTag::SOFTWARE => PelFormat::ASCII,
    PelTag::DATE_TIME => PelFormat::ASCII,
    PelTag::WHITE_POINT => PelFormat::RATIONAL,
    PelTag::PRIMARY_CHROMATICITIES => PelFormat::RATIONAL,
    PelTag::YCBCR_COEFFICIENTS => PelFormat::RATIONAL,
    PelTag::YCBCR_POSITIONING => PelFormat::SHORT,
    PelTag::COPYRIGHT => PelFormat::ASCII,
    PelTag::EXPOSURE_TIME => PelFormat::RATIONAL,
    PelTag::FNUMBER => PelFormat::RATIONAL,
    PelTag::EXPOSURE_PROGRAM => PelFormat::SHORT,
    PelTag::ISO_SPEED_RATINGS => PelFormat::SHORT,
    PelTag::EXIF_VERSION => PelFormat::UNDEFINED,
    PelTag::DATE_TIME_ORIGINAL => PelFormat::ASCII,
    PelTag::DATE_TIME_DIGITIZED => PelFormat::ASCII,
    PelTag::COMPONENTS_CONFIGURATION => PelFormat::UNDEFINED,
    PelTag::COMPRESSED_BITS_PER_PIXEL => PelFormat::RATIONAL,
    PelTag::SHUTTER_SPEED_VALUE => PelFormat::SRATIONAL,
    PelTag::APERTURE_VALUE => PelFormat::RATIONAL,
    PelTag::BRIGHTNESS_VALUE => PelFormat::SRATIONAL,
    PelTag::EXPOSURE_BIAS_VALUE => PelFormat::SRATIONAL,
    PelTag::MAX_APERTURE_VALUE => PelFormat::RATIONAL,
    PelTag::SUBJECT_DISTANCE => PelFormat::SRATIONAL,
    PelTag::METERING_MODE => PelFormat::SHORT,
    PelTag::LIGHT_SOURCE => PelFormat::SHORT,
    PelTag::FLASH => PelFormat::SHORT,
    PelTag::FOCAL_LENGTH => PelFormat::RATIONAL,
    PelTag::MAKER_NOTE => PelFormat::UNDEFINED,
    PelTag::USER_COMMENT => PelFormat::UNDEFINED,
    PelTag::FLASH_PIX_VERSION => PelFormat::UNDEFINED,
    PelTag::COLOR_SPACE => PelFormat::SHORT,
    PelTag::PIXEL_X_DIMENSION => PelFormat::LONG,
    PelTag::PIXEL_Y_DIMENSION => PelFormat::LONG,
    PelTag::RELATED_SOUND_FILE => PelFormat::ASCII,
    PelTag::INTEROPERABILITY_IFD_POINTER => PelFormat::LONG,
    PelTag::FOCAL_PLANE_X_RESOLUTION => PelFormat::RATIONAL,
    PelTag::FOCAL_PLANE_Y_RESOLUTION => PelFormat::RATIONAL,
    PelTag::FOCAL_PLANE_RESOLUTION_UNIT => PelFormat::SHORT,
    PelTag::SENSING_METHOD => PelFormat::SHORT,
    PelTag::FILE_SOURCE => PelFormat::UNDEFINED,
    PelTag::SCENE_TYPE => PelFormat::UNDEFINED
  ];

  /**
   * First method
   *
   * @return  string
   *
   * @since   4.0.0
   */
  public function hello(): string
  {
    return 'Hello from the <b>PHP</b> version of the Metadata-Service...';
  }

  /**
   * Saves an edit to the exif metadata of an image
   * 
   * @param   string $img   Path to the image 
   * @param   array  $edits Array of edits to be made to the metadata
   * 
   * @return  bool          True on success, false on failure
   * 
   * @since   4.0.0
   */
  public function saveExifEdit(string $img, array $edits): bool {
    // Remove after implementation
    Pel::setDebug(true);
    // Temporary until form
    $file = file_get_contents(__DIR__ . "/Ricoh_Caplio_RR330.jpg");

    $data = new PelDataWindow($file);
    if (PelJpeg::isValid($data)) {
      $jpeg = new PelJpeg();
      $jpeg->load($data);
      $exifdata = $jpeg->getExif();
      // Check if APP1 section exists
      if ($exifdata == null) {
        // Create APP1 section if not exists
        $exifdata = new PelExif();
        $jpeg->setExif($exifdata);
        // Create empty TIFF structure for the APP1 section
        $tiff = new PelTiff();
        $exifdata->setTiff($tiff);
      }
      $tiff = $exifdata->getTiff();
    } elseif (PelTiff::isValid($data)) {
      // Data was recognized as TIFF. PelTiff/Ifd is what is being edited regardless.
      $tiff = new PelTiff();
      $tiff->load($data);
    } else {
      // Handle invalid data
    }
    // Grab the root IFD from the TIFF. IFDs are what is actually being edited.
    $ifd0 = $tiff->getIfd();
    if ($ifd0 == null) {
      // Image did not contain an IFD, so no former Exif data.
      // Populate Tiff with an empty IFD
      $ifd0 = new PelIfd(PelIfd::IFD0);
      $tiff->setIfd($ifd0);
    }
    // The majority of EXIF data is stored in the sub IFD
    $subIfd = $ifd0->getSubIfd(PelIfd::EXIF);

    // Cycle through all the necessary edits and perform them
    foreach ($edits as $key => $edit) {
      // Check if edit should take place on the root or the sub IFD.
      if ($key <= 33432) {
        $toEdit = $ifd0->getEntry($key);
        if ($toEdit == null) {
          $toEdit = new PelEntryAscii($key, $edit);
          $ifd0->addEntry($toEdit);
        } else {
          $toEdit->setValue($edit);
        }
      } else {
        $toEdit = $subIfd->getEntry($key);
        if ($toEdit == null) {
          $toEdit = new PelEntryAscii($key, $edit);
          $subIfd->addEntry($toEdit);
        } else {
          $toEdit->setValue($edit);
        }
      }
    }
    
    //$doc_name = $ifd0->getEntry(PelTag::DOCUMENT_NAME);
    //print($doc_name);
    echo "PelJpeg loaded";
    return false;
  }

  /**
   * Saves an edit to the iptc metadata of an image
   * 
   * @param   string $img   Path to the image 
   * @param   array  $edits Array of edits to be made to the metadata
   * 
   * @return  bool          True on success, false on failure
   * 
   * @since   4.0.0
   */
  public function saveIptcEdit(string $img, array $edits): bool {
    // Currently unimplemented, will be implemented.
    return false;
  }

  /**
   * Saves an edit to the xmp metadata of an image
   * 
   * Currently unimplemented
   * 
   * @param   string $img   Path to the image 
   * @param   array  $edits Array of edits to be made to the metadata
   * 
   * @return  bool          True on success, false on failure
   * 
   * @since   4.0.0
   */
  public function saveXmpEdit(string $img, array $edits): bool {
    // This function is currently not implemented. Potentially out of scope for the WIPRO project.
    return false;
  }
}
