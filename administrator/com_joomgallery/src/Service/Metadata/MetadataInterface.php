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
   * 
   */
  public function copyMetadata($src_file, $dst_file, $src_imagetype, $dst_imgtype, $new_orient, $bak): bool;

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
  public function saveIptcEdit(string $img, array $edits): bool;

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
