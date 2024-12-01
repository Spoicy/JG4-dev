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

use \Joomla\CMS\Factory;
use \Joomgallery\Component\Joomgallery\Administrator\Helper\JoomHelper;
use \Joomgallery\Component\Joomgallery\Administrator\Extension\ServiceTrait;
use \Joomgallery\Component\Joomgallery\Administrator\Service\Metadata\Metadata as BaseMetadata;

use \lsolesen\pel\Pel;
use \lsolesen\pel\PelDataWindow;
use \lsolesen\pel\PelEntryAscii;
use lsolesen\pel\PelEntryCopyright;
use lsolesen\pel\PelEntryRational;
use lsolesen\pel\PelEntrySRational;
use lsolesen\pel\PelEntryTime;
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

  /**
   * @var array
   */
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
    PelTag::ARTIST => PelFormat::ASCII,
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
    PelTag::EXPOSURE_INDEX => PelFormat::RATIONAL,
    PelTag::SENSING_METHOD => PelFormat::SHORT,
    PelTag::FILE_SOURCE => PelFormat::UNDEFINED,
    PelTag::SCENE_TYPE => PelFormat::UNDEFINED
  ];

  /**
   * Writes a list of values to the exif metadata of an image
   * 
   * @param   string $img   Path to the image 
   * @param   array  $edits Array of edits to be made to the metadata
   * 
   * @return  bool          True on success, false on failure
   * 
   * @since   4.0.0
   */
  public function writeToExif(string $img, array $edits): bool {
    $file = file_get_contents($img);
    $data = new PelDataWindow($file);

    if (PelJpeg::isValid($data)) {
      $jpeg = $file = new PelJpeg();
      $jpeg->load($data);
      $exifdata = $jpeg->getExif();
      // Check if APP1 section exists, create if not along with tiff
      if ($exifdata == null) {
        $exifdata = new PelExif();
        $jpeg->setExif($exifdata);
        $tiff = new PelTiff();
        $exifdata->setTiff($tiff);
      }
      $tiff = $exifdata->getTiff();
    } elseif (PelTiff::isValid($data)) {
      // Data was recognized as TIFF. PelTiff/Ifd is what is being edited regardless.
      $tiff = $file = new PelTiff();
      $tiff->load($data);
    } else {
      // Handle invalid data
      return false;
    }
    // Grab the root IFD from the TIFF. IFDs are what is actually being edited.
    $ifd0 = $tiff->getIfd();
    if ($ifd0 == null) {
      // Image did not contain an IFD, so no former Exif data, populate with an empty IFD
      $ifd0 = new PelIfd(PelIfd::IFD0);
      $tiff->setIfd($ifd0);
    }
    // The majority of EXIF data is stored in the sub IFD
    $subIfd = $ifd0->getSubIfd(PelIfd::EXIF);
    $editor = new PelDataEditor();
    // Cycle through all the necessary edits and perform them
    foreach ($edits as $tag => $edit) {
      if (!isset(self::$entryTypes[$tag])) {
        // Address does not reference a tag
        continue;
        // TODO: Handle this properly
      }
      // Check if edit should take place on the root or the sub IFD.
      if ($tag <= 33432) {
        $editor->makeEdit($ifd0, $tag, $edit, self::$entryTypes[$tag]);
      } else {
        $editor->makeEdit($subIfd, $tag, $edit, self::$entryTypes[$tag]);
      }
    }
    echo $ifd0->getEntry(PelTag::MAKE)->getValue();
    $test = $file->saveFile($img);
    var_dump($test);
    return true;
  }

  /**
   * Gets the jpeg/tiff objects from a valid JPEG or TIFF image with PEL.
   * 
   * @param  string $img    The image data
   * 
   * @return array|false    File and tiff objects on success, false on failure.
   * 
   * @since 4.0.0
   */
  private function getPelImageObjects(string $img) {
    $file = file_get_contents($img);
    $data = new PelDataWindow($file);
    if (PelJpeg::isValid($data)) {
      $jpeg = $file = new PelJpeg();
      $jpeg->load($data);
      $exifdata = $jpeg->getExif();
      // Check if APP1 section exists, create if not along with tiff
      if ($exifdata == null) {
        $exifdata = new PelExif();
        $jpeg->setExif($exifdata);
        $tiff = new PelTiff();
        $exifdata->setTiff($tiff);
      }
      $tiff = $exifdata->getTiff();
    } elseif (PelTiff::isValid($data)) {
      // Data was recognized as TIFF. PelTiff/Ifd is what is being edited regardless.
      $tiff = $file = new PelTiff();
      $tiff->load($data);
    } else {
      echo "wuh";
      // Handle invalid data
      return false;
    }
    return ["file" => $file, "tiff" => $tiff];
  }

  /**
   * Reads the metadata from an image.
   * (Current supported image formats: JPEG)
   * (Current supported metadata formats: EXIF/IPTC)
   */
  public function readMetadata(string $file) {
    return self::readJpegMetadata($file);
  }

  /**
   * Reads the EXIF and IPTC metadata from a JPEG.
   * 
   * @param  string $file  The image path
   * 
   * @return array         Metadata in array format
   * 
   * @since 4.0.0
   */
  public function readJpegMetadata(string $file) {
    $metadata = array('exif' => array(), 'iptc' => array(), 'comment' => array());
    $size = getimagesize($file, $info);

    // EXIF with PEL
    $imageObjects = self::getPelImageObjects($file);

    if ($imageObjects == false) {
      return;
    }
    $tiff = $imageObjects["tiff"];
    $ifd0 = $tiff->getIfd();
    if ($ifd0 != null) {
      $metadata['exif']['IFD0'] = array();
      foreach ($ifd0->getEntries() as $entry) {
        $metadata['exif']['IFD0'][PelTag::getName(PelIfd::IFD0, $entry->getTag())] = self::formatPELEntryForForm($entry);
      }
      $subIfd = $ifd0->getSubIfd(PelIfd::EXIF);
      if ($subIfd != null) {
        $metadata['exif']['EXIF'] = array();
        foreach ($subIfd->getEntries() as $entry) {
          $metadata['exif']['EXIF'][PelTag::getName(PelIfd::EXIF, $entry->getTag())] = self::formatPELEntryForForm($entry);
        }
      }
    }
    // IPTC
    if (isset($info["APP13"])) {
      $iptc = iptcparse($info['APP13']);
      foreach ($iptc as $key => $value) {
        $metadata['iptc'][$key] = $value[0];
      }
    }
    return $metadata;
  }
  
  /**
   * Formats a PelEntry variant to be stored in a displayable format for the imgmetadata form.
   */
  private function formatPELEntryForForm($entry) {
    if ($entry instanceof PelEntryRational || $entry instanceof PelEntrySRational) {
      // Rationals are retrieved/stored as an array, they need to be reformatted for the form.
      $numbers = $entry->getValue();
      return $entry->formatNumber($numbers);
    } elseif ($entry instanceof PelEntryCopyright) {
      // Copyright is stored in PEL as an array, it needs to be reformatted for the form.
      return $entry->getText();
    } elseif ($entry instanceof PelEntryTime) {
      return $entry->getValue(PelEntryTime::EXIF_STRING);
    } else {
      return $entry->getValue();
    }
  }

  /**
   * Copies the metadata from one file to another with PEL.
   * 
   * @param  string $srcPath Path to source file
   * @param  string $dstPath Path to destination file
   * 
   * @return int
   * 
   * @since 4.0.0
   */
  public function copyExifData($srcPath, $dstPath) {
    $srcImageObjects = self::getPelImageObjects($srcPath);
    if ($srcImageObjects == false) {
      return false;
    }
    $srcPelTiff = $srcImageObjects["tiff"];
    $dstImageObjects = self::getPelImageObjects($dstPath);
    if ($dstImageObjects == false) {
      return false;
    }
    $dstPelFile = $dstImageObjects["file"];
    if ($dstPelFile instanceof PelJpeg) {
      $exifdata = $dstPelFile->getExif();
      $exifdata->setTiff($srcPelTiff);
      echo $exifdata->getTiff();
    } else {
      // TIFF not currently supported
      return false;
    }
    return $dstPelFile->saveFile($dstPath);
  }

  /**
   * Copy iTXt, tEXt and zTXt chunks of a png from source to destination image
   *
   * read chunks; adapted from
   * Author: Andrew Moore
   * Website: https://stackoverflow.com/questions/2190236/how-can-i-read-png-metadata-from-php
   *
   * write chunks; adapted from
   * Author: leonbloy
   * Website: https://stackoverflow.com/questions/8842387/php-add-itxt-comment-to-a-png-image
   *
   * @param   string  $src_file        Path to source file
   * @param   string  $dst_file        Path to destination file
   *
   * @return  int     number of bytes written on success, false otherwise
   *
   * @since   3.5.0
   */
  protected function copyPNGmetadata($src_file, $dst_file)
  {
    if (\file_exists($src_file) && \file_exists($dst_file)) {
      $_src_chunks = array();
      $_fp         = \fopen($src_file, 'r');
      $chunks      = array();

      if (!$_fp) {
        // Unable to open file
        return false;
      }

      // Read the magic bytes and verify
      $header = \fread($_fp, 8);

      if ($header != "\x89PNG\x0d\x0a\x1a\x0a") {
        // Not a valid PNG image
        return false;
      }

      // Loop through the chunks. Byte 0-3 is length, Byte 4-7 is type
      $chunkHeader = \fread($_fp, 8);
      while ($chunkHeader) {
        // Extract length and type from binary data
        $chunk = @\unpack('Nsize/a4type', $chunkHeader);

        // Store position into internal array
        if (!\key_exists($chunk['type'], $_src_chunks)) {
          $_src_chunks[$chunk['type']] = array();
        }

        $_src_chunks[$chunk['type']][] = array(
          'offset' => \ftell($_fp),
          'size' => $chunk['size']
        );

        // Skip to next chunk (over body and CRC)
        \fseek($_fp, $chunk['size'] + 4, SEEK_CUR);

        // Read next chunk header
        $chunkHeader = \fread($_fp, 8);
      }

      // Read iTXt chunk
      if (isset($_src_chunks['iTXt'])) {
        foreach ($_src_chunks['iTXt'] as $chunk) {
          if ($chunk['size'] > 0) {
            \fseek($_fp, $chunk['offset'], SEEK_SET);
            $chunks['iTXt'] = \fread($_fp, $chunk['size']);
          }
        }
      }

      // Read tEXt chunk
      if (isset($_src_chunks['tEXt'])) {
        foreach ($_src_chunks['tEXt'] as $chunk) {
          if ($chunk['size'] > 0) {
            \fseek($_fp, $chunk['offset'], SEEK_SET);
            $chunks['tEXt'] = \fread($_fp, $chunk['size']);
          }
        }
      }

      // Read zTXt chunk
      if (isset($_src_chunks['zTXt'])) {
        foreach ($_src_chunks['zTXt'] as $chunk) {
          if ($chunk['size'] > 0) {
            \fseek($_fp, $chunk['offset'], SEEK_SET);
            $chunks['zTXt'] = \fread($_fp, $chunk['size']);
          }
        }
      }

      // Write chucks to destination image
      $_dfp = \file_get_contents($dst_file);
      $data = '';

      if (isset($chunks['iTXt'])) {
        $data .= \pack("N", \strlen($chunks['iTXt'])) . 'iTXt' . $chunks['iTXt'] . \pack("N", \crc32('iTXt' . $chunks['iTXt']));
      }

      if (isset($chunks['tEXt'])) {
        $data .= \pack("N", \strlen($chunks['tEXt'])) . 'tEXt' . $chunks['tEXt'] . \pack("N", \crc32('tEXt' . $chunks['tEXt']));
      }

      if (isset($chunks['zTXt'])) {
        $data .= \pack("N", \strlen($chunks['zTXt'])) . 'zTXt' . $chunks['zTXt'] . \pack("N", \crc32('zTXt' . $chunks['zTXt']));
      }

      $len = \strlen($_dfp);
      $png = \substr($_dfp, 0, $len - 12) . $data . \substr($_dfp, $len - 12, 12);

      return \file_put_contents($dst_file, $png);
    } else {
      // File doesn't exist
      return false;
    }
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
    $editor = new IptcDataEditor();
    $tagString = "";
    foreach ($edits as $tag => $edit) {
      $tagString .= $editor->createEdit($tag, $edit);
    }
    $tagString = $editor->createEdit("2#090", "London");
    $tagString .= $editor->createEdit("2#095", "England");
    $content = iptcembed($tagString, $img);

    $fp = fopen($img, "wb");
    fwrite($fp, $content);
    fclose($fp);
    return true;
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
