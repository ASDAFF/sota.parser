<?
/**
 * Copyright (c) 11/9/2020 Created By/Edited By ASDAFF asdaff.asad@yandex.ru
 */

use Bitrix\Seo\Engine;
use Bitrix\Main\Text\Converter;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\IO\Path;
use Bitrix\Main;

\Bitrix\Main\Loader::includeModule('seo');
\Bitrix\Main\Loader::includeModule('socialservices');

IncludeModuleLangFile(__FILE__);
global $DB;
$db_type = strtolower($DB->type);
$module_id = 'shs.parser';
$module_status = CModule::IncludeModuleEx($module_id);


CModule::AddAutoloadClasses(
    'shs.parser',
    array(
        'ShsParserContentGeneral' => 'classes/general/list_parser.php',
        'SotbitContentParser' => 'classes/general/main_classes.php',
        'SotbitHLCatalogParser' => 'classes/general/main_classes_catalog_HL.php',
        'SotbitXmlParser' => 'classes/general/main_classes_xml.php',
        'SotbitCsvParser' => 'classes/general/main_classes_csv.php',
        'SotbitXlsParser' => 'classes/general/main_classes_xls.php',
        'SotbitXlsCatalogParser' => 'classes/general/main_classes_xls_catalog.php',
        'SotbitXmlCatalogParser' => 'classes/general/main_classes_xml_catalog.php',
        'SotbitCsvCatalogParser' => 'classes/general/main_classes_csv_catalog.php',
        'ParserEventHandler' => 'classes/general/event_handlers.php',
        'ShsParserContent' => 'classes/' . $db_type . '/list_parser.php',
        'Export' => 'lib/helper/export.php',
    )
);

include($_SERVER["DOCUMENT_ROOT"] . '/bitrix/modules/shs.parser/classes/phpQuery/phpQuery.php');
include($_SERVER["DOCUMENT_ROOT"] . '/bitrix/modules/shs.parser/classes/general/sotbit_idna_convert.class.php');
include($_SERVER["DOCUMENT_ROOT"] . '/bitrix/modules/shs.parser/classes/general/file_get_html.php');

Class RssContentParser extends SotbitCsvCatalogParser
{
    public function __construct()
    {
        CModule::IncludeModule('highloadblock');
        parent::__construct();
    }


    public function sotbitParserSetSettings(&$SETTINGS)
    {
        foreach ($SETTINGS as &$v) {
            if (is_array($v)) self::sotbitParserSetSettings($v); else {
                $v = htmlentities(htmlspecialcharsBack($v), ENT_QUOTES, SITE_CHARSET);
            }
        }
    }

    public function createFolder()
    {
        $dir = $_SERVER['DOCUMENT_ROOT'] . BX_ROOT . '/modules/shs.parser/include';
        if (!file_exists($dir)) mkdir($dir, BX_DIR_PERMISSIONS);
    }

    public function auth($check = false, $type = "http")
    {
        $this->check = $check;
        $this->GetAuthForm($check);
    }
}

Class CShsParser
{
    static function startAgent($ID)
    {
        ignore_user_abort(true);
        @set_time_limit(0);
        if (CModule::IncludeModule('iblock') && CModule::IncludeModule('main')): CModule::IncludeModule("highloadblock");
            $parser = ShsParserContent::GetByID($ID);
            if (!$parser->ExtractFields('shs_')) $ID = 0;
            if (!file_exists(dirname(__FILE__) . '/include/startAgent' . $ID . '.txt')) file_put_contents(dirname(__FILE__) . '/include/startAgent' . $ID . '.txt', 'start parser ' . $ID); else {
                unset($parser);
                return 'CShsParser::startAgent(' . $ID . ');';
            }
            $rssParser = new RssContentParser();
            $rssParser->startParser(1);
            if (file_exists(dirname(__FILE__) . '/include/startAgent' . $ID . '.txt')) unlink(dirname(__FILE__) . '/include/startAgent' . $ID . '.txt');
            unset($rssParser, $parser);
            return 'CShsParser::startAgent(' . $ID . ');'; endif;
    }

} ?>