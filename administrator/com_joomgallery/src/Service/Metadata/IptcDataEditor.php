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

/**
 * Editor class to handle iptc data type editing
 * 
 * @package JoomGallery
 * @since 4.0.0
 */
class IptcDataEditor
{
    /**
     * @var array
     */
    protected $iptcHeaderArray = array(
        '2#005' => 'DocumentTitle',
        '2#010' => 'Urgency',
        '2#015' => 'Category',
        '2#020' => 'Subcategories',
        '2#040' => 'SpecialInstructions',
        '2#055' => 'CreationDate',
        '2#080' => 'AuthorByline',
        '2#085' => 'AuthorTitle',
        '2#090' => 'City',
        '2#095' => 'State',
        '2#101' => 'Country',
        '2#103' => 'OTR',
        '2#105' => 'Headline',
        '2#110' => 'Source',
        '2#115' => 'PhotoSource',
        '2#116' => 'Copyright',
        '2#120' => 'Caption',
        '2#122' => 'CaptionWriter'
    );

    /**
     * @var array
     */
    protected $iptcStringArray = array(
        '2#005' => [0, 64],
        '2#015' => [0, 3],
        '2#020' => [0, 32],
        '2#040' => 'SpecialInstructions',
        '2#080' => 'AuthorByline',
        '2#085' => 'AuthorTitle',
        '2#090' => 'City',
        '2#095' => 'State',
        '2#101' => 'Country',
        '2#103' => 'OTR',
        '2#105' => 'Headline',
        '2#110' => 'Source',
        '2#115' => 'PhotoSource',
        '2#116' => 'Copyright',
        '2#120' => 'Caption',
        '2#122' => 'CaptionWriter'
    );

    /**
     * @var array
     */
    protected $iptcDigitsArray = array(
        '2#010' => 1,
        '2#055' => 8
    );

    /**
     * Validates input and creates the octet structure to be saved with iptcembed.
     * 
     * @param   string $tag  The record & dataset tags in a 0#000 format
     * @param   mixed  $data The data to be stored
     * 
     * @return  string       Octet structure that complies to IPTC's specification
     * 
     * @since   4.0.0
     */
    public function createEdit(string $tag, mixed $data): string
    {
        // TODO: Add data validation
        $explode = explode("#", $tag);
        $octetStruct = self::makeTag(intval($explode[0]), intval($explode[1]), $data);
        return $octetStruct;
    }

    /**
     * Create the necessary octet structure to be saved.
     * Function by Thies C. Arntzen, posted as example under the iptcembed PHP Documentation page.
     * 
     * @param   int   $rec    IPTC Record Number
     * @param   int   $data   IPTC DataSet Number
     * @param   mixed $value  Value to be stored
     * 
     * @return  string        String of chars to embed
     * 
     * @since   4.0.0
     */
    private function makeTag(int $rec, int $data, mixed $value): string
    {
        $length = strlen($value);
        // First 3 octets (Tag Marker, Record Number, DataSet Number).
        $retval = chr(0x1C) . chr($rec) . chr($data);

        if ($length < 0x8000) {
            // 4th and 5th octet (total amount of octets that the value contains). Standard DataSet Tag
            // Maximum total of octets is 32767.
            $retval .= chr($length >> 8) .  chr($length & 0xFF);
        } else {
            // 4th to nth octet. Extended DataSet Tag
            // Most significant bit of octet 4 is always 1 (Flag for Extended format), remaining bits in octet 4 and 5 describe the length of the Data Field (in this instance predefined to 4).
            // 6th to 9th octet describe the total amount of octets that the value contains.
            $retval .= chr(0x80) .
                chr(0x04) .
                chr(($length >> 24) & 0xFF) .
                chr(($length >> 16) & 0xFF) .
                chr(($length >> 8) & 0xFF) .
                chr($length & 0xFF);
        }

        return $retval . $value;
    }

    public function appendTags(array $app13, string $tags): string {
        $retval = "";
        return false;
        foreach ($app13 as $tag => $value) {
            var_dump($value);
            $explode = explode("#", $tag);
            $retval .= self::makeTag(intval($explode[0]), intval($explode[1]), $value[0]);
        }
        return $retval . $tags;
    }
}
