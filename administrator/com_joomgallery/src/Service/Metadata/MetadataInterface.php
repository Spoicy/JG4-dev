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

\defined('_JEXEC') or die;


/**
* Interface for the metadata class
*
* @since  4.0.0
*/
interface MetadataInterface
{
  /**
   * 
   */
  public function readMetadata(string $file);

  /**
   * Copy image metadata depending on file type (Supported: JPG,PNG / EXIF,IPTC)
   *
   * @param   string  $src_file        Path to source file
   * @param   string  $dst_file        Path to destination file
   * @param   string  $src_imagetype   Type of the source image file
   * @param   string  $dst_imgtype     Type of the destination image file
   * @param   int     $new_orient      New exif orientation (false: do not change exif orientation)
   * @param   bool    $bak             true, if a backup-file should be created if $src_file=$dst_file
   *
   * @return  int     number of bytes written on success, false otherwise
   *
   * @since   3.5.0
   */
  public function copyMetadata($src_file, $dst_file, $src_imagetype, $dst_imgtype, $new_orient, $bak);

  /**
   * Writes the stored metadata to the specified image
   * 
   * @param  string $file         Path to source file
   * @param  array  $imgmetadata  Stored image metadata
   * 
   * @return bool   true on success, false on failure
   */
  public function writeMetadata($img, $imgmetadata);

  /**
   * Writes a list of values to the exif metadata of an image
   * 
   * @param   string $img   Path to the image 
   * @param   array $edits  Array of edits to be made to the metadata
   * 
   * @return  bool          True on success, false on failure
   * 
   * @since   4.0.0
   */
  public function writeToExif(string $img, array $edits): bool;

  /**
   * Saves an edit to the iptc metadata of an image
   * 
   * @param   string $img   Path to the image 
   * @param   array $edits  Array of edits to be made to the metadata
   * 
   * @return  bool          True on success, false on failure
   * 
   * @since   4.0.0
   */
  public function writeToIptc(string $img, array $edits): bool;

  /**
   * Saves an edit to the xmp metadata of an image
   * 
   * Currently unimplemented
   * 
   * @param   string $img   Path to the image 
   * @param   array $edits  Array of edits to be made to the metadata
   * 
   * @return  bool          True on success, false on failure
   * 
   * @since   4.0.0
   */
  public function saveXmpEdit(string $img, array $edits): bool;
}
