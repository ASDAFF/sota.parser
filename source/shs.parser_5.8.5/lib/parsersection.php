<?
namespace Shs\Parser;
use Bitrix\Main\Entity;
use Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);

class ParserSectionTable extends Entity\DataManager
{
    public static function getFilePath()
    {
       return __FILE__;
    }

   public static function getTableName()
   {
      return 'b_shs_parser_section';
   }
   
   public static function getMap()
   {
        return array(
            'ID' => new \Bitrix\Main\Entity\IntegerField('ID', array(
                'primary' => true,
                'autocomplete' => true,
                'title' => 'ID',
            )),
            'TIMESTAMP_X' => new \Bitrix\Main\Entity\DatetimeField('TIMESTAMP_X', array(
                'default_value' => function(){ return new \Bitrix\Main\Type\DateTime(); },
                'title' => 'TIMESTAMP_X',
            )),
            'DATE_CREATE' => new \Bitrix\Main\Entity\DatetimeField('DATE_CREATE', array(
                'title' => Loc::getMessage("shs_parser_section_date_title"),
            )),
            'ACTIVE' => new \Bitrix\Main\Entity\BooleanField('ACTIVE', array(
                'values' => array('N', 'Y'),
                'default_value' => 'Y',
                'title' => Loc::getMessage("shs_parser_section_active_title")
            )),
            'SORT' => new \Bitrix\Main\Entity\IntegerField('SORT', array(
                'required' => true,
                'title' => Loc::getMessage("shs_parser_section_sort_title"),
            )),
            'NAME' => new \Bitrix\Main\Entity\StringField('NAME', array(
                'required' => true,
                'title' => Loc::getMessage("shs_parser_section_name_title"),
            )),
            'DESCRIPTION' => new \Bitrix\Main\Entity\StringField('DESCRIPTION', array(
                'required' => true,
                'title' => Loc::getMessage("shs_parser_section_description_title"),
            )),
            'PARENT_CATEGORY_ID' => new \Bitrix\Main\Entity\IntegerField('PARENT_CATEGORY_ID', array(
                'title' => Loc::getMessage("shs_parser_section_parent_title"),
            )),

        );
   }
}
?>