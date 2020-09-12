<?
use Bitrix\Main\Entity;
use Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);

class ShsParserTmpTable extends Entity\DataManager
{
    public static function getFilePath()
    {
       return __FILE__;
    }

   public static function getTableName()
   {
      return 'b_shs_parser_tmp';
   }
   
   public static function getMap()
   {
      return array(
         'ID' => array(
            'data_type' => 'integer',
            'primary' => true,
            'autocomplete' => true,
            'title' => "ID",
         ),
         'PARSER_ID' => array(
            'data_type' => 'integer',
            'title' => "PARSER_ID",
         ),         
         'PRODUCT_ID' => array(
            'data_type' => 'integer',
            'title' => "PRODUCT_ID",
         ),
      );
   }

//   public static function add(array $arFields)
//   {
//       file_put_contents(dirname(__FILE__).'/log.log', 'ShsParserTmpTable -> add PARSER_ID = '.$arFields['PARSER_ID'].' PRODUCT_ID = '.$arFields['PRODUCT_ID']."\n", FILE_APPEND);
//       parent::add($arFields);
//   }
//
//    public static function Delete($id)
//    {
//        file_put_contents(dirname(__FILE__).'/log.log', 'ShsParserTmpTable -> Delete row ID = '.$id."\n", FILE_APPEND);
//        parent::Delete($id);
//    }


}
?>