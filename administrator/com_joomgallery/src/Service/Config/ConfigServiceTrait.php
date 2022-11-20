<?php
/**
******************************************************************************************
**   @version    4.0.0                                                                  **
**   @package    com_joomgallery                                                        **
**   @author     JoomGallery::ProjectTeam <team@joomgalleryfriends.net>                 **
**   @copyright  2008 - 2022  JoomGallery::ProjectTeam                                  **
**   @license    GNU General Public License version 2 or later                          **
*****************************************************************************************/

namespace Joomgallery\Component\Joomgallery\Administrator\Service\Config;

\defined('JPATH_PLATFORM') or die;

use \Joomgallery\Component\Joomgallery\Administrator\Service\Config\Config;
use \Joomla\CMS\Component\ComponentHelper;

/**
* Trait to implement ConfigServiceInterface
*
* @since  4.0.0
*/
trait ConfigServiceTrait
{
  /**
	 * Storage for the config helper class.
	 *
	 * @var ConfigInterface
	 *
	 * @since  4.0.0
	 */
	private $config = null;

  /**
	 * Returns the config helper class.
	 *
	 * @return  ConfigInterface
	 *
	 * @since  4.0.0
	 */
	public function getConfig(): ConfigInterface
	{
		return $this->config;
	}

  /**
	 * Creates the config helper class based on the selected
   * inheritance method in global component settings
	 *
   * @return  void
   *
	 * @since  4.0.0
	 */
	public function createConfig(): void
	{
    $inheritance = ComponentHelper::getParams(_JOOM_OPTION)->get('inheritance_config', 'default');

    switch($inheritance)
    {
      default:
      $this->config = new Config;
        break;
    }

    return;
	}
}
