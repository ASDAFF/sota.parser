<?
use Bitrix\Highloadblock as HL;
use Bitrix\Main\Entity;
use Bitrix\Main\Type; 

if(isset($_REQUEST["ID"]) && isset($_REQUEST["btn_stop"])){
    file_put_contents($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/shs.parser/include/stop_parser_".$_REQUEST["ID"].".txt",'end');
    exit;
}

if(!isset($_REQUEST['ajax']) && !isset($_REQUEST["ajax_start"]) && !isset($_REQUEST["ajax_count"]) && !isset($_POST["auth"])):
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/shs.parser/include.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/shs.parser/prolog.php");
$arEncoding['reference'] = array(
        //GetMessage('parser_charset_default'),
        'UTF-8',
        'KOI8-R',
        'KOI8-U',
        'WINDOWS-1251',
        'WINDOWS-1252',
        'UTF-8 (fix)',
        'ISO-8859-1');
$arEncoding['reference_id'] = array(
        //'default',
        'UTF-8',
        'KOI8-R',
        'KOI8-U',
        'WINDOWS-1251',
        'WINDOWS-1252',
        'UTF8',
        'ISO-8859-1');
//require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/shs.parser/classes/mysql/list_parser.php");
//require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/shs.parser/classes/general/rss_content_parser.php");
define("HELP_FILE", "add_issue.php");
//CJSCore::Init(array("jquery"));
CUtil::InitJSCore(array('ajax', 'ls', 'jquery'));

//session expander
$arPolicy = $USER->GetSecurityPolicy();

$phpSessTimeout = ini_get("session.gc_maxlifetime");
if($arPolicy["SESSION_TIMEOUT"] > 0)
{
    $sessTimeout = min($arPolicy["SESSION_TIMEOUT"]*60, $phpSessTimeout);
}
else
{
    $sessTimeout = $phpSessTimeout;
}

$cookie_prefix = COption::GetOptionString('main', 'cookie_name', 'BITRIX_SM');
$salt = $_COOKIE[$cookie_prefix.'_UIDH']."|".$USER->GetID()."|".$_SERVER["REMOTE_ADDR"]."|".@filemtime($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/classes/general/version.php")."|".LICENSE_KEY."|".CMain::GetServerUniqID();
$key = md5(bitrix_sessid().$salt);

$bShowMess = ($USER->IsAuthorized() && COption::GetOptionString("main", "session_show_message", "Y") <> "N");

//CUtil::InitJSCore(array('ajax', 'ls', 'jquery'));

$jsMsg = '<script type="text/javascript">'."\n".
    ($bShowMess? 'bxSession.mess.messSessExpired = \''.CUtil::JSEscape(GetMessage("MAIN_SESS_MESS", array("#TIMEOUT#"=>round($sessTimeout/60)))).'\';'."\n" : '').
    'setInterval(function(){bxSession.Expand('.$sessTimeout.', \''.bitrix_sessid().'\', '.($bShowMess? 'true':'false').', \''.$key.'\');}, 600000);'."\n".
    '</script>';

$APPLICATION->AddHeadScript('/bitrix/js/main/session.js');
$APPLICATION->AddAdditionalJS($jsMsg);

$_SESSION["BX_SESSION_COUNTER"] = intval($_SESSION["BX_SESSION_COUNTER"]) + 1;
if(!defined("BX_SKIP_SESSION_TERMINATE_TIME"))
{
    $_SESSION["BX_SESSION_TERMINATE_TIME"] = time()+$sessTimeout;
}



/*
$str = '<offer id="451" type="book" available="true">
<url>http://actuall.ru/catalog/prazdniki_otkrytki_gramoty/tovary_dlya_prazdnika2/tovarydlyaprazdnika_feniksprezent_paket_bumazhnyy_kraft_prazdnichnyy_buket_250_190_80_ruchki_verevki.html?r1=yandext&amp;r2=</url>
<price>33</price>
<currencyId>RUB</currencyId>
</offer>
<offer id="452" type="book" available="true">
<url>http://actuall.ru/catalog/prazdniki_otkrytki_gramoty/tovary_dlya_prazdnika2/tovarydlyaprazdnika_feniksprezent_paket_bumazhnyy_kraft_klever_250_190_80_ruchki_verevki_43742.html?r1=yandext&amp;r2=</url>
<price>30</price>
<currencyId>RUB</currencyId>
</offer>
<offer id="453" type="book" available="true">
<url>http://actuall.ru/catalog/prazdniki_otkrytki_gramoty/tovary_dlya_prazdnika2/tovarydlyaprazdnika_feniksprezent_paket_bumazhnyy_kraft_babochki_i_rombiki_250_190_80_ruchki_verevki.html?r1=yandext&amp;r2=</url>
<price>30</price>
<currencyId>RUB</currencyId>
</offer></offers>';
$out = preg_split('/<offer/', $str);

for($i = 0; $i < count($out); $i++)
    if(strpos($out[$i], 'offer') !== false)
        $out[$i] = '<offer '.$out[$i];

for($i = 0; $i < count($out); $i++)
        var_dump(preg_match('/<offer/i', $out[$i]));
echo '<pre>';
print_r($out);
exit();*/


if(!CModule::IncludeModule('iblock')) return false;
CModule::IncludeModule('catalog');
CModule::IncludeModule("highloadblock");
CModule::IncludeModule('shs.parser');

IncludeModuleLangFile(__FILE__);
global $shs_DEMO;
CShsParser::CheckDemo($shs_DEMO);

$POST_RIGHT = $APPLICATION->GetGroupRight("shs.parser");
if($POST_RIGHT=="D")
    $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

$parentID = 0;
if(isset($_REQUEST["parent"]) && $_REQUEST["parent"])
{
    $parentID = $_REQUEST["parent"];
}
/*$aTabs = array(
    array("DIV" => "edit1", "TAB" => GetMessage("parser_tab"), "ICON"=>"main_user_edit", "TITLE"=>GetMessage("parser_tab")),
    array("DIV" => "edit2", "TAB" => GetMessage("parser_preview_tab"), "ICON"=>"main_user_edit", "TITLE"=>GetMessage("parser_preview_tab")),
    array("DIV" => "edit3", "TAB" => GetMessage("parser_detail_tab"), "ICON"=>"main_user_edit", "TITLE"=>GetMessage("parser_detail_tab")),
    array("DIV" => "edit4", "TAB" => GetMessage("parser_settings_tab"), "ICON"=>"main_user_edit", "TITLE"=>GetMessage("parser_settings_tab")),
);
$tabControl = new CAdminTabControl("tabControl", $aTabs);*/

$arTypeParser['reference'] = array('rss', 'page', 'catalog', 'xml', 'csv', 'xls', 'xls + catalog', 'xml + catalog');
$arTypeParser['reference_id'] = array('rss', 'page', 'catalog', 'xml', 'csv', 'xls', 'xls_catalo', 'xml_catalo');

$ID = intval($ID);        // Id of the edited record
$bCopy = ($action == "copy");
$message = null;
$bVarsFromForm = false;

/*function sotbitParserSetSettings(&$SETTINGS)
{
    foreach($SETTINGS as &$v)
    {
        if(is_array($v)) sotbitParserSetSettings($v);
        else $v = htmlspecialcharsEx($v);
    }
}

function sotbitParserGetSettings(&$SETTINGS)
{
    foreach($SETTINGS as &$v)
    {
        if(is_array($v)) sotbitParserGetSettings($v);
        else $v = htmlspecialcharsBack($v);
    }
}*/

$arrFilterCircs = array(
    'reference'=>array(
        GetMessage('parser_filter_equally'),
        GetMessage('parser_filter_strpos'),
        GetMessage('parser_filter_stripos'),
    ),
    'reference_id'=>array(
        'equally',
        'strpos',
        'stripos',
    ),
);

if($REQUEST_METHOD == "POST" && ($save!="" || $apply!="") && $POST_RIGHT=="W" && check_bitrix_sessid())
{
    $parser = new ShsParserContent();
    RssContentParser::sotbitParserSetSettings($SETTINGS);

    $arFields = Array(
        "NAME"    => $NAME,
        "TYPE"    => $TYPE,
        "TYPE_OUT"    => $TYPE_OUT,
        "TIMESTAMP_X"    => new Type\DateTime( date( 'Y-m-d H:i:s' ), 'Y-m-d H:i:s' ),
        "RSS"    => $RSS,
        "SORT"    => $SORT,
        "ACTIVE"    => ($ACTIVE <> "Y"? "N":"Y"),
        "IBLOCK_ID"    => $IBLOCK_ID,
        "SECTION_ID" => $SECTION_ID,
        "SELECTOR"    => $SELECTOR,
        "FIRST_URL"    => $FIRST_URL,
        "ENCODING"    => $ENCODING,
        "PREVIEW_TEXT_TYPE" => $PREVIEW_TEXT_TYPE,
        "DETAIL_TEXT_TYPE" => $DETAIL_TEXT_TYPE,
        "PREVIEW_DELETE_TAG" => $PREVIEW_DELETE_TAG,
        "DETAIL_DELETE_TAG" => $DETAIL_DELETE_TAG,
        "PREVIEW_FIRST_IMG" => ($PREVIEW_FIRST_IMG <> "Y"? "N":"Y"),
        "DETAIL_FIRST_IMG" => ($DETAIL_FIRST_IMG <> "Y"? "N":"Y"),
        "PREVIEW_SAVE_IMG" => ($PREVIEW_SAVE_IMG <> "Y"? "N":"Y"),
        "DETAIL_SAVE_IMG" => ($DETAIL_SAVE_IMG <> "Y"? "N":"Y"),
        "BOOL_PREVIEW_DELETE_TAG" =>($BOOL_PREVIEW_DELETE_TAG <> "Y"? "N":"Y"),
        "BOOL_DETAIL_DELETE_TAG" =>($BOOL_DETAIL_DELETE_TAG <> "Y"? "N":"Y"),
        "PREVIEW_DELETE_ELEMENT" => $PREVIEW_DELETE_ELEMENT,
        "DETAIL_DELETE_ELEMENT" => $DETAIL_DELETE_ELEMENT,
        "PREVIEW_DELETE_ATTRIBUTE" => $PREVIEW_DELETE_ATTRIBUTE,
        "DETAIL_DELETE_ATTRIBUTE" => $DETAIL_DELETE_ATTRIBUTE,
        "INDEX_ELEMENT" => ($INDEX_ELEMENT <> "Y"? "N":"Y"),
        "CODE_ELEMENT" => ($CODE_ELEMENT <> "Y"? "N":"Y"),
        "RESIZE_IMAGE" => ($RESIZE_IMAGE <> "Y"? "N":"Y"),
        "CREATE_SITEMAP" => ($CREATE_SITEMAP <> "Y"? "N":"Y"),
        "DATE_ACTIVE" => ($DATE_ACTIVE <> "Y"? "N":$DATE_PROP_ACTIVE),
        "DATE_PUBLIC" => ($DATE_PUBLIC <> "Y"? "N":$DATE_PROP_PUBLIC),
        "FIRST_TITLE" => ($FIRST_TITLE <> "Y"? "N":$FIRST_PROP_TITLE),
        "META_TITLE" => ($META_TITLE <> "Y"? "N":$META_PROP_TITLE),
        "META_DESCRIPTION" => ($META_DESCRIPTION <> "Y"? "N":$META_PROP_DESCRIPTION),
        "META_KEYWORDS" => ($META_KEYWORDS <> "Y"? "N":$META_PROP_KEYWORDS),
        "START_AGENT" => ($START_AGENT <> "Y"? "N":"Y"),
        "TIME_AGENT" => $TIME_AGENT,
        "ACTIVE_ELEMENT" => ($ACTIVE_ELEMENT <> "Y"? "N":"Y"),
        "SETTINGS" => base64_encode(serialize($SETTINGS)),
        "CATEGORY_ID"    => $CATEGORY_ID,
        //"START_LAST_TIME" => $START_LAST_TIME
    );
    if($ID>0)
    {
        $res = $parser->Update($ID, $arFields);

    }
    else
    {
        $ID = $parser->Add($arFields);
        $res = ($ID>0);
    }
 
    if($res)
    {
        if($apply!="")
            LocalRedirect("/bitrix/admin/parser_edit.php?ID=".$ID."&mess=ok&lang=".LANG."&tabControl_active_tab=".$_POST["tabControl_active_tab"]);
        else
            LocalRedirect("/bitrix/admin/list_parser_admin.php?lang=".LANG.(($parentID != 0) ? '&parent='.$parentID : ""));
    }
    else
    {
        if($e = $APPLICATION->GetException())
            $message = new CAdminMessage(GetMessage("parser_save_error"), $e);
        $bVarsFromForm = true;
    }

}
//Edit/Add part
ClearVars();

if($ID>0 || $copy)
{
    if($ID)$parser = ShsParserContent::GetByID($ID);
    elseif($copy) $parser = ShsParserContent::GetByID($copy);
    
    if(!$parser->ExtractFields("shs_"))
        $ID=0;
    if($ID>0 && $shs_TIME_AGENT>0){
        $arAgent = CAgent::GetList(array(), array("NAME"=>"CShsParser::startAgent(".$ID.");"))->Fetch();
        if(!$arAgent && $shs_START_AGENT=="Y"){CAgent::AddAgent(
            "CShsParser::startAgent(".$ID.");", // ��� �������
            "shs.parser",                          // ������������� ������
            "N",                                  // ����� �� �������� � ���-�� ��������
            $shs_TIME_AGENT,                                // �������� ������� - 1 �����
            "",                // ���� ������ �������� �� ������
            "Y",                                  // ����� �������
            "",                // ���� ������� �������
            100
          );}
        elseif($arAgent){
            CAgent::Update($arAgent['ID'], array(
                "AGENT_INTERVAL"=>$shs_TIME_AGENT,
                "ACTIVE"=>$shs_START_AGENT=="Y"?"Y":"N"
            ));
        }
    }

    
    $properties = CIBlockProperty::GetList(Array("sort"=>"asc", "name"=>"asc"), Array("ACTIVE"=>"Y", "IBLOCK_ID"=>$shs_IBLOCK_ID, "PROPERTY_TYPE"=>"S"));
    while($arProp = $properties->Fetch())
    {
        $arrProp['REFERENCE'][] = "[".$arProp["CODE"]."] ".$arProp["NAME"];
        $arrProp['REFERENCE_ID'][] = $arProp["CODE"];
    }

    $properties = CIBlockProperty::GetList(Array("sort"=>"asc", "name"=>"asc"), Array("ACTIVE"=>"Y", "IBLOCK_ID"=>$shs_IBLOCK_ID, "PROPERTY_TYPE"=>"F"));
    while($arProp = $properties->Fetch())
    {
        $arrPropFile['REFERENCE'][] = "[".$arProp["CODE"]."] ".$arProp["NAME"];
        $arrPropFile['REFERENCE_ID'][] = $arProp["CODE"];
    }

    $properties = CIBlockProperty::GetList(Array("sort"=>"asc", "name"=>"asc"), Array("ACTIVE"=>"Y", "IBLOCK_ID"=>$shs_IBLOCK_ID));

    $arrPropDop['REFERENCE'][] = GetMessage("shs_parser_select_prop_new");
    $arrPropDop['REFERENCE_ID'][] = "[]";
    
    $arrPropField['REFERENCE'][] = GetMessage("parser_SOTBIT_PARSER_NAME_E");
    $arrPropField['REFERENCE_ID'][] = "SOTBIT_PARSER_NAME_E";
    
    while($arProp = $properties->Fetch())
    {

        if($arProp["PROPERTY_TYPE"]=="S")
        {
            $arrPropField['REFERENCE'][] = $arProp["NAME"];
            $arrPropField['REFERENCE_ID'][] = $arProp["CODE"];
        }
        if($arProp["PROPERTY_TYPE"]=="L" || $arProp["PROPERTY_TYPE"]=="N" || $arProp["PROPERTY_TYPE"]=="S" || $arProp["PROPERTY_TYPE"]=="E" || $arProp["PROPERTY_TYPE"]=="F" || $arProp["PROPERTY_TYPE"]=="R" )
        {
            $arrPropDop['REFERENCE'][] = $arProp["NAME"];
            $arrPropDop['REFERENCE_ID'][] = $arProp["CODE"];
            $arrPropDop['REFERENCE_TYPE'][$arProp["CODE"]] = $arProp["PROPERTY_TYPE"];
            $arrPropDop['USER_TYPE'][$arProp["CODE"]] = $arProp["USER_TYPE"];
            $arrPropDop['REFERENCE_CODE_NAME'][$arProp["CODE"]] = $arProp["NAME"];
        }
        
        if($arProp["PROPERTY_TYPE"]=="L"/* && $arProp["ID"]==14*/)
        {
            $rsEnum = CIBlockPropertyEnum::GetList(Array("DEF"=>"DESC", "SORT"=>"ASC"), Array("IBLOCK_ID"=>$shs_IBLOCK_ID, "property_id"=>$arProp["ID"]));
            $arrPropDop["LIST_VALUES"][$arProp["CODE"]]["REFERENCE"][] = GetMessage("parser_prop_default");
            $arrPropDop["LIST_VALUES"][$arProp["CODE"]]["REFERENCE_ID"][] = "";
            while($arEnum = $rsEnum->Fetch())
            {
                $arrPropDop["LIST_VALUES"][$arProp["CODE"]]["REFERENCE"][] = $arEnum["VALUE"];
                $arrPropDop["LIST_VALUES"][$arProp["CODE"]]["REFERENCE_ID"][] = $arEnum["ID"];
            }
        }
        if($arProp["PROPERTY_TYPE"]=="R")
        {
        }
        if($arProp['USER_TYPE']=="directory")
        {
            $nameTable = $arProp["USER_TYPE_SETTINGS"]["TABLE_NAME"];
            
            // если $nameTable равно -1 - это в свойствах инфоблока выбрали "Создавать новый справочник"
            if(strlen($nameTable) < 1)
                continue 1;
            
            if($nameTable)
            {
                $directorySelect = array("*");
                $directoryOrder = array();
                $entityGetList = array(
                    'select' => $directorySelect,
                    'order' => $directoryOrder
                );
                
                $highBlock = \Bitrix\Highloadblock\HighloadBlockTable::getList(array("filter" => array('TABLE_NAME' => $nameTable)))->fetch();
                
                if(empty($highBlock) && $highBlock === false)
					continue 1;

                $entity = \Bitrix\Highloadblock\HighloadBlockTable::compileEntity($highBlock);
                $entityDataClass = $entity->getDataClass();
                $propEnums = $entityDataClass::getList($entityGetList);
                $arrPropDop["LIST_VALUES"][$arProp["CODE"]]["REFERENCE"][] = GetMessage("parser_prop_default");
                $arrPropDop["LIST_VALUES"][$arProp["CODE"]]["REFERENCE_ID"][] = "";
                while ($oneEnum = $propEnums->fetch())
                {
                    $arrPropDop["LIST_VALUES"][$arProp["CODE"]]["REFERENCE"][] = $oneEnum["UF_NAME"];
                    $arrPropDop["LIST_VALUES"][$arProp["CODE"]]["REFERENCE_ID"][] = $oneEnum["UF_XML_ID"];
                }
            }
            
        }
    }
}

/*if($bVarsFromForm)
    $DB->InitTableVarsForEdit("b_shs_list_parser", "", "shs_");*/
$APPLICATION->SetTitle(($ID>0? GetMessage("parser_title_edit").' "'.$shs_NAME.'"' : GetMessage("parser_title_add")));
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

$aMenu = array(
    array(
        "TEXT"=>GetMessage("parser_list"),
        "TITLE"=>GetMessage("parser_list_title"),
        "LINK"=>"list_parser_admin.php?parent=".$parentID."&lang=".LANG,
        "ICON"=>"btn_list",
    )
);
//exit($ID.' - '.$copy);
if($ID>0)
{
    $startAgent = false;
    if(file_exists($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/shs.parser/include/startAgent".$ID.".txt"))
        $startAgent = true;

    $aMenu[] = array("SEPARATOR"=>"Y");
    $aMenu[] = array(
        "TEXT"=>GetMessage("parser_export"),
        "TITLE"=>GetMessage("rubric_mnu_export"),
        "LINK"=>"parser_export.php?ID=$ID&lang=".LANG,
        "ICON"=>"btn_export",
    );
    $aMenu[] = array(
        "TEXT"=>GetMessage("parser_add"),
        "TITLE"=>GetMessage("rubric_mnu_add"),
        "LINK"=>"parser_edit.php?lang=".LANG,
        "ICON"=>"btn_new",
    );
    $aMenu[] = array(
        "TEXT"=>GetMessage("parser_copy"),
        "TITLE"=>GetMessage("rubric_mnu_copy"),
        "LINK"=>"parser_edit.php?copy=".$ID."&lang=".LANG,
        "ICON"=>"btn_copy",
    );
    if(!$startAgent){
        $aMenu[] = array(
            "TEXT"=>GetMessage("parser_delete"),
            "TITLE"=>GetMessage("parser_mnu_del"),
            "LINK"=>"javascript:if(confirm('".GetMessage("parser_mnu_del_conf")."'))window.location='list_parser_admin.php?ID=P".$ID."&action=delete&lang=".LANG."&".bitrix_sessid_get()."';",
            "ICON"=>"btn_delete",
        );
    }
    
    if($shs_ACTIVE=="Y" && !$startAgent){
        $aMenu[] = array("SEPARATOR"=>"Y");
        if($shs_TYPE=="catalog" || $_GET["type"]=="catalog" || $shs_TYPE=="catalog_HL" || $_GET["type"]=="catalog_HL"):
            $aMenu[] = array(
                "TEXT"=>GetMessage("parser_start"),
                "TITLE"=>GetMessage("parser_start_title"),
                "LINK"=>"parser_edit.php?start=1&lang=".LANG."&ID=".$ID,
                "ICON"=>"btn_start_catalog"
            );
        elseif($shs_TYPE=="page" || $_GET["type"]=="page" || $shs_TYPE=="rss" || $_GET["type"]=="rss"):
            $aMenu[] = array(
                "TEXT"=>GetMessage("parser_start"),
                "TITLE"=>GetMessage("parser_start_title"),
                "LINK"=>"parser_edit.php?start=1&lang=".LANG."&ID=".$ID,
                "ICON"=>"btn_start"
            );
        elseif($shs_TYPE=="xml" || $_GET["type"]=="xml" || $shs_TYPE=="xml_catalo" || $_GET["type"]=="xml_catalo"):
            $aMenu[] = array(
                "TEXT"=>GetMessage("parser_start"),
                "TITLE"=>GetMessage("parser_start_title"),
                "LINK"=>"parser_edit.php?start=1&lang=".LANG."&ID=".$ID,
                "ICON"=>"btn_start_xml"
            );
        elseif($shs_TYPE=="csv" || $_GET["type"]=="csv"):
            $aMenu[] = array(
                "TEXT"=>GetMessage("parser_start"),
                "TITLE"=>GetMessage("parser_start_title"),
                "LINK"=>"parser_edit.php?start=1&lang=".LANG."&ID=".$ID,
                "ICON"=>"btn_start_csv"
            );
        elseif($shs_TYPE=="xls" || $_GET["type"]=="xls" || $shs_TYPE=="xls_catalo" || $_GET["type"]=="xls_catalo"):
            $aMenu[] = array(
                "TEXT"=>GetMessage("parser_start"),
                "TITLE"=>GetMessage("parser_start_title"),
                "LINK"=>"parser_edit.php?start=1&lang=".LANG."&ID=".$ID,
                "ICON"=>"btn_start_xls"
            );
        endif;
    }
    if($shs_TYPE=="catalog" || $_GET["type"]=="catalog")
    {
        $aMenu[] = array(
            "TEXT"=>GetMessage("parser_instructions"),
            "TITLE"=>GetMessage("parser_instructions_title"),
            "LINK"=>"http://www.sotbit.ru/info/articles/instruktsiya-polzovatelya-pri-rabote-s-modulem-parser-kontenta-v-rezhime-kataloga.html",
            "ICON"=>"instruction"
        );
    }
    if($shs_TYPE=="xml" || $_GET["type"]=="xml")
    {
        $aMenu[] = array(
            "TEXT"=>GetMessage("parser_instructions"),
            "TITLE"=>GetMessage("parser_instructions_title"),
            "LINK"=>"http://www.sotbit.ru/info/articles/instruktsiya-polzovatelya-pri-rabote-s-modulem-parser-kontenta-v-rezhime-kataloga.html",
            "ICON"=>"instruction"
        );
    }
}

$context = new CAdminContextMenu($aMenu);
$context->Show();

$rsSection = \Shs\Parser\ParserSectionTable::getList(array(
    'limit' =>null,
    'offset' => null,
    'select' => array("*"),
    "filter" => array()
));

$parentID = 0;
if(isset($_REQUEST["parent"]) && $_REQUEST["parent"])
{
    $parentID = $_REQUEST["parent"];
}

while($arSection = $rsSection->Fetch())
{
    $arCategory['REFERENCE'][] = "[".$arSection["ID"]."] ".$arSection["NAME"];
    $arCategory['REFERENCE_ID'][] = $arSection["ID"];
}

$arLocType['reference'] = array(GetMessage("parser_loc_no"), GetMessage("parser_loc_yandex"));
$arLocType['reference_id'] = array('', 'yandex');

if(isset($_REQUEST['start']) && $ID>0){
    $rssParser = new RssContentParser();
    $result = $rssParser->startParser();
    if(isset($result[SUCCESS][0]))
    foreach($result[SUCCESS] as $i=>$success){
      $resultUrl .= "&SUCCESS[".$i."]=".urlencode($success);
    }
    if(isset($result[ERROR][0]))
     foreach($result[ERROR] as $i=>$error){
      $resultUrl .= "&ERROR[".$i."]=".urlencode($error);
    }
    if(!RssContentParser::TEST)LocalRedirect($APPLICATION->GetCurPageParam("end=1".$resultUrl, array("start")));
}

/***
**** to iblock
***/

if(($shs_TYPE=="xls_catalo" || $_GET["type"]=="xls_catalo" || $shs_TYPE=="xml_catalo" || $_GET["type"]=="xml_catalo" || $shs_TYPE=="catalog" || $_GET["type"]=="catalog" || $shs_TYPE=="xml" || $_GET["type"]=="xml" || $shs_TYPE=="csv" || $_GET["type"]=="csv" || $shs_TYPE=="xls" || $_GET["type"]=="xls") && ((isset($shs_TYPE_OUT) && $shs_TYPE_OUT!="HL") || ( isset($_GET["type_out"]) && $_GET["type_out"]!="HL") || (!isset($_GET['type_out']) && (!isset($shs_TYPE_OUT) || $shs_TYPE_OUT!='HL'))) )
{
    $isOfferCatalog = false;
    if(isset($shs_IBLOCK_ID) && $shs_IBLOCK_ID && CModule::IncludeModule('catalog'))
    {
        $arIblock = CCatalogSKU::GetInfoByIBlock($shs_IBLOCK_ID);
        
        if(is_array($arIblock) && !empty($arIblock) && $arIblock["PRODUCT_IBLOCK_ID"]!=0 && $arIblock["SKU_PROPERTY_ID"]!=0)$isOfferCatalog = true;
        
        
        if($arIblock["IBLOCK_ID"] && $arIblock["PRODUCT_IBLOCK_ID"])
            $OFFER_IBLOCK_ID = $arIblock["IBLOCK_ID"];
        
        if($OFFER_IBLOCK_ID)
        {
            $properties = CIBlockProperty::GetList(Array("sort"=>"asc", "name"=>"asc"), Array("ACTIVE"=>"Y", "IBLOCK_ID"=>$OFFER_IBLOCK_ID));
    
            $arrPropDopOffer['REFERENCE'][] = GetMessage("shs_parser_select_prop_new");
            $arrPropDopOffer['REFERENCE_ID'][] = "[]";
            
            while($arProp = $properties->Fetch())
            {
//                echo '1<pre>';
//                print_r($arProp);

                if($arProp["PROPERTY_TYPE"]=="L" || $arProp["PROPERTY_TYPE"]=="N" || $arProp["PROPERTY_TYPE"]=="S" || $arProp["PROPERTY_TYPE"]=="E" || $arProp["PROPERTY_TYPE"]=="F")
                {
                    $arrPropDopOffer['REFERENCE'][] = $arProp["NAME"];
                    $arrPropDopOffer['REFERENCE_ID'][] = $arProp["CODE"];
                    $arrPropDopOfferName['REFERENCE'][] = $arProp["NAME"];
                    $arrPropDopOfferName['REFERENCE_ID'][] = $arProp["CODE"];
                    $arrPropDopOffer['REFERENCE_TYPE'][$arProp["CODE"]] = $arProp["PROPERTY_TYPE"];
                    $arrPropDopOffer['USER_TYPE'][$arProp["CODE"]] = $arProp["USER_TYPE"];
                    $arrPropDopOffer['REFERENCE_CODE_NAME'][$arProp["CODE"]] = $arProp["NAME"];
                }
                
                if($arProp["PROPERTY_TYPE"]=="L"/* && $arProp["ID"]==14*/)
                {
                    $rsEnum = CIBlockPropertyEnum::GetList(Array("DEF"=>"DESC", "SORT"=>"ASC"), Array("IBLOCK_ID"=>$OFFER_IBLOCK_ID, "property_id"=>$arProp["ID"]));
                    $arrPropDopOffer["LIST_VALUES"][$arProp["CODE"]]["REFERENCE"][] = GetMessage("parser_prop_default");
                    $arrPropDopOffer["LIST_VALUES"][$arProp["CODE"]]["REFERENCE_ID"][] = "";
                    while($arEnum = $rsEnum->Fetch())
                    {
                        $arrPropDopOffer["LIST_VALUES"][$arProp["CODE"]]["REFERENCE"][] = $arEnum["VALUE"];
                        $arrPropDopOffer["LIST_VALUES"][$arProp["CODE"]]["REFERENCE_ID"][] = $arEnum["ID"];
                    }
                }
                if($arProp['USER_TYPE']=="directory")
                {
                    $nameTable = $arProp["USER_TYPE_SETTINGS"]["TABLE_NAME"];
                    $directorySelect = array("*");
                    $directoryOrder = array();
                    $entityGetList = array(
                        'select' => $directorySelect,
                        'order' => $directoryOrder
                    );
                    $highBlock = \Bitrix\Highloadblock\HighloadBlockTable::getList(array("filter" => array('TABLE_NAME' => $nameTable)))->fetch();
                    if(!$highBlock)
                        continue 1;
                    $entity = \Bitrix\Highloadblock\HighloadBlockTable::compileEntity($highBlock);
                    $entityDataClass = $entity->getDataClass();
                    $propEnums = $entityDataClass::getList($entityGetList);
                    $arrPropDopOffer["LIST_VALUES"][$arProp["CODE"]]["REFERENCE"][] = GetMessage("parser_prop_default");
                    $arrPropDopOffer["LIST_VALUES"][$arProp["CODE"]]["REFERENCE_ID"][] = "";
                    while ($oneEnum = $propEnums->fetch())
                    {
                        $arrPropDopOffer["LIST_VALUES"][$arProp["CODE"]]["REFERENCE"][] = $oneEnum["UF_NAME"];
                        $arrPropDopOffer["LIST_VALUES"][$arProp["CODE"]]["REFERENCE_ID"][] = $oneEnum["UF_XML_ID"];
                    }
                }
            }
//            exit;
        }
    }
    
    /***
    **** ���� ��� ��������
    ***/
    
    if ($shs_TYPE=="catalog" || $_GET["type"]=="catalog")
    {
        if(CModule::IncludeModule('catalog') && (($shs_IBLOCK_ID && CCatalog::GetList(Array("name" => "asc"), Array("ACTIVE"=>"Y", "ID"=>$shs_IBLOCK_ID))->Fetch()) || (is_array($arIblock) && !empty($arIblock) && $arIblock["PRODUCT_IBLOCK_ID"]!=0 && $arIblock["SKU_PROPERTY_ID"]!=0)  || !$shs_IBLOCK_ID))
        {
            //unset($aTabs[5]);
            $aTabs = array(
                array("DIV" => "edit1", "TAB" => GetMessage("parser_tab"), "ICON"=>"main_user_edit", "TITLE"=>GetMessage("parser_tab")),
                array("DIV" => "edit2", "TAB" => GetMessage("parser_pagenavigation_tab"), "ICON"=>"main_user_edit", "TITLE"=>GetMessage("parser_pagenavigation_tab")),
                array("DIV" => "edit3", "TAB" => GetMessage("parser_preview_tab"), "ICON"=>"main_user_edit", "TITLE"=>GetMessage("parser_preview_tab")),
                array("DIV" => "edit4", "TAB" => GetMessage("parser_detail_tab"), "ICON"=>"main_user_edit", "TITLE"=>GetMessage("parser_detail_tab")),
                array("DIV" => "edit5", "TAB" => GetMessage("parser_props_tab"), "ICON"=>"main_user_edit", "TITLE"=>GetMessage("parser_props_tab")),
                array("DIV" => "edit6", "TAB" => GetMessage("parser_catalog_tab"), "ICON"=>"main_user_edit", "TITLE"=>GetMessage("parser_catalog_tab")),
                array("DIV" => "edit7", "TAB" => GetMessage("parser_offer_tab"), "ICON"=>"main_user_edit", "TITLE"=>GetMessage("parser_offer_tab")),
                array("DIV" => "edit8", "TAB" => GetMessage("parser_settings_tab"), "ICON"=>"main_user_edit", "TITLE"=>GetMessage("parser_settings_tab")),
                array("DIV" => "edit9", "TAB" => GetMessage("parser_uniq_tab"), "ICON"=>"main_user_edit", "TITLE"=>GetMessage("parser_uniq_tab")),
                array("DIV" => "edit10", "TAB" => GetMessage("parser_auth"), "ICON"=>"main_user_edit", "TITLE"=>GetMessage("parser_auth")),
                array("DIV" => "edit11", "TAB" => GetMessage("parser_logs_tab"), "ICON"=>"main_user_edit", "TITLE"=>GetMessage("parser_logs_tab")),
                array("DIV" => "edit12", "TAB" => GetMessage("parser_local_tab"), "ICON"=>"main_user_edit", "TITLE"=>GetMessage("parser_local_tab")),
                array("DIV" => "edit13", "TAB" => GetMessage("parser_video_tab"), "ICON"=>"main_user_edit", "TITLE"=>GetMessage("parser_video_tab")),
            );
            $isCatalog = true;
        }else{
            $aTabs = array(
                array("DIV" => "edit1", "TAB" => GetMessage("parser_tab"), "ICON"=>"main_user_edit", "TITLE"=>GetMessage("parser_tab")),
                array("DIV" => "edit2", "TAB" => GetMessage("parser_pagenavigation_tab"), "ICON"=>"main_user_edit", "TITLE"=>GetMessage("parser_pagenavigation_tab")),
                array("DIV" => "edit3", "TAB" => GetMessage("parser_preview_tab"), "ICON"=>"main_user_edit", "TITLE"=>GetMessage("parser_preview_tab")),
                array("DIV" => "edit4", "TAB" => GetMessage("parser_detail_tab"), "ICON"=>"main_user_edit", "TITLE"=>GetMessage("parser_detail_tab")),
                array("DIV" => "edit5", "TAB" => GetMessage("parser_props_tab"), "ICON"=>"main_user_edit", "TITLE"=>GetMessage("parser_props_tab")),
                array("DIV" => "edit7", "TAB" => GetMessage("parser_settings_tab"), "ICON"=>"main_user_edit", "TITLE"=>GetMessage("parser_settings_tab")),
                array("DIV" => "edit8", "TAB" => GetMessage("parser_uniq_tab"), "ICON"=>"main_user_edit", "TITLE"=>GetMessage("parser_uniq_tab")),
                array("DIV" => "edit9", "TAB" => GetMessage("parser_auth"), "ICON"=>"main_user_edit", "TITLE"=>GetMessage("parser_auth")),
                array("DIV" => "edit10", "TAB" => GetMessage("parser_logs_tab"), "ICON"=>"main_user_edit", "TITLE"=>GetMessage("parser_logs_tab")),
                array("DIV" => "edit11", "TAB" => GetMessage("parser_local_tab"), "ICON"=>"main_user_edit", "TITLE"=>GetMessage("parser_local_tab")),
                array("DIV" => "edit12", "TAB" => GetMessage("parser_video_tab"), "ICON"=>"main_user_edit", "TITLE"=>GetMessage("parser_video_tab")),
            );
            $isCatalog = false;
        }
    }
    
    /***
    **** ���� ��� XML
    ***/
    
    if ($shs_TYPE=="xml" || $_GET["type"]=="xml" || $shs_TYPE=="xml_catalo" || $_GET["type"]=="xml_catalo" || $shs_TYPE=="csv" || $_GET["type"]=="csv")
    {
        if(CModule::IncludeModule('catalog') && (($shs_IBLOCK_ID && CCatalog::GetList(Array("name" => "asc"), Array("ACTIVE"=>"Y", "ID"=>$shs_IBLOCK_ID))->Fetch()) || (is_array($arIblock) && !empty($arIblock) && $arIblock["PRODUCT_IBLOCK_ID"]!=0 && $arIblock["SKU_PROPERTY_ID"]!=0)  || !$shs_IBLOCK_ID))
        {
            $aTabs = array(
                array("DIV" => "edit1", "TAB" => GetMessage("parser_tab"), "ICON"=>"main_user_edit", "TITLE"=>GetMessage("parser_tab")),
                //array("DIV" => "edit2", "TAB" => GetMessage("parser_pagenavigation_tab"), "ICON"=>"main_user_edit", "TITLE"=>GetMessage("parser_pagenavigation_tab")),
                array("DIV" => "edit12", "TAB" => GetMessage("parser_catalog_file_tab"), "ICON"=>"main_user_edit", "TITLE"=>GetMessage("parser_catalog_file_tab")),
                array("DIV" => "edit2", "TAB" => GetMessage("parser_basic_settings_tab"), "ICON"=>"main_user_edit", "TITLE"=>GetMessage("parser_basic_settings_tab")),
                //array("DIV" => "edit4", "TAB" => GetMessage("parser_detail_tab"), "ICON"=>"main_user_edit", "TITLE"=>GetMessage("parser_detail_tab")),
                array("DIV" => "edit3", "TAB" => GetMessage("parser_props_tab"), "ICON"=>"main_user_edit", "TITLE"=>GetMessage("parser_props_tab")),
                array("DIV" => "edit4", "TAB" => GetMessage("parser_catalog_tab"), "ICON"=>"main_user_edit", "TITLE"=>GetMessage("parser_catalog_tab")),
                array("DIV" => "edit5", "TAB" => GetMessage("parser_offer_tab"), "ICON"=>"main_user_edit", "TITLE"=>GetMessage("parser_offer_tab")),
                array("DIV" => "edit6", "TAB" => GetMessage("parser_settings_tab"), "ICON"=>"main_user_edit", "TITLE"=>GetMessage("parser_settings_tab")),
                array("DIV" => "edit7", "TAB" => GetMessage("parser_uniq_tab"), "ICON"=>"main_user_edit", "TITLE"=>GetMessage("parser_uniq_tab")),
                array("DIV" => "edit8", "TAB" => GetMessage("parser_auth"), "ICON"=>"main_user_edit", "TITLE"=>GetMessage("parser_auth")),
                array("DIV" => "edit9", "TAB" => GetMessage("parser_logs_tab"), "ICON"=>"main_user_edit", "TITLE"=>GetMessage("parser_logs_tab")),
                array("DIV" => "edit10", "TAB" => GetMessage("parser_local_tab"), "ICON"=>"main_user_edit", "TITLE"=>GetMessage("parser_local_tab")),
                array("DIV" => "edit11", "TAB" => GetMessage("parser_video_tab"), "ICON"=>"main_user_edit", "TITLE"=>GetMessage("parser_video_tab")),
            );
            $isCatalog = true;
        }else{
            $aTabs = array(
                array("DIV" => "edit1", "TAB" => GetMessage("parser_tab"), "ICON"=>"main_user_edit", "TITLE"=>GetMessage("parser_tab")),
                //array("DIV" => "edit2", "TAB" => GetMessage("parser_pagenavigation_tab"), "ICON"=>"main_user_edit", "TITLE"=>GetMessage("parser_pagenavigation_tab")),
                array("DIV" => "edit12", "TAB" => GetMessage("parser_catalog_file_tab"), "ICON"=>"main_user_edit", "TITLE"=>GetMessage("parser_catalog_file_tab")),
                array("DIV" => "edit2", "TAB" => GetMessage("parser_preview_tab"), "ICON"=>"main_user_edit", "TITLE"=>GetMessage("parser_preview_tab")),
                //array("DIV" => "edit4", "TAB" => GetMessage("parser_detail_tab"), "ICON"=>"main_user_edit", "TITLE"=>GetMessage("parser_detail_tab")),
                array("DIV" => "edit3", "TAB" => GetMessage("parser_props_tab"), "ICON"=>"main_user_edit", "TITLE"=>GetMessage("parser_props_tab")),
                array("DIV" => "edit4", "TAB" => GetMessage("parser_settings_tab"), "ICON"=>"main_user_edit", "TITLE"=>GetMessage("parser_settings_tab")),
                array("DIV" => "edit5", "TAB" => GetMessage("parser_uniq_tab"), "ICON"=>"main_user_edit", "TITLE"=>GetMessage("parser_uniq_tab")),
                array("DIV" => "edit6", "TAB" => GetMessage("parser_auth"), "ICON"=>"main_user_edit", "TITLE"=>GetMessage("parser_auth")),
                array("DIV" => "edit7", "TAB" => GetMessage("parser_logs_tab"), "ICON"=>"main_user_edit", "TITLE"=>GetMessage("parser_logs_tab")),
                array("DIV" => "edit8", "TAB" => GetMessage("parser_local_tab"), "ICON"=>"main_user_edit", "TITLE"=>GetMessage("parser_local_tab")),
                array("DIV" => "edit9", "TAB" => GetMessage("parser_video_tab"), "ICON"=>"main_user_edit", "TITLE"=>GetMessage("parser_video_tab")),
            );
            $isCatalog = false;
        }
    }
    elseif ($shs_TYPE=="xls" || $_GET["type"]=="xls") //���� ��� XLS
    {
        if(CModule::IncludeModule('catalog') && (($shs_IBLOCK_ID && CCatalog::GetList(Array("name" => "asc"), Array("ACTIVE"=>"Y", "ID"=>$shs_IBLOCK_ID))->Fetch()) || (is_array($arIblock) && !empty($arIblock) && $arIblock["PRODUCT_IBLOCK_ID"]!=0 && $arIblock["SKU_PROPERTY_ID"]!=0)  || !$shs_IBLOCK_ID))
        {
            $aTabs = array(
                array("DIV" => "edit1", "TAB" => GetMessage("parser_tab"), "ICON"=>"main_user_edit", "TITLE"=>GetMessage("parser_tab")),
                array("DIV" => "edit12", "TAB" => GetMessage("parser_preview_xls_tab"), "ICON"=>"main_user_edit", "TITLE"=>GetMessage("parser_preview_xls_tab")),
                array("DIV" => "edit2", "TAB" => GetMessage("parser_basic_settings_tab"), "ICON"=>"main_user_edit", "TITLE"=>GetMessage("parser_basic_settings_tab")),
                array("DIV" => "edit3", "TAB" => GetMessage("parser_props_tab"), "ICON"=>"main_user_edit", "TITLE"=>GetMessage("parser_props_tab")),
                array("DIV" => "edit4", "TAB" => GetMessage("parser_catalog_tab"), "ICON"=>"main_user_edit", "TITLE"=>GetMessage("parser_catalog_tab")),
                array("DIV" => "edit5", "TAB" => GetMessage("parser_offer_tab"), "ICON"=>"main_user_edit", "TITLE"=>GetMessage("parser_offer_tab")),
                array("DIV" => "edit6", "TAB" => GetMessage("parser_settings_tab"), "ICON"=>"main_user_edit", "TITLE"=>GetMessage("parser_settings_tab")),
                array("DIV" => "edit7", "TAB" => GetMessage("parser_uniq_tab"), "ICON"=>"main_user_edit", "TITLE"=>GetMessage("parser_uniq_tab")),
                array("DIV" => "edit8", "TAB" => GetMessage("parser_auth"), "ICON"=>"main_user_edit", "TITLE"=>GetMessage("parser_auth")),
                array("DIV" => "edit9", "TAB" => GetMessage("parser_logs_tab"), "ICON"=>"main_user_edit", "TITLE"=>GetMessage("parser_logs_tab")),
                array("DIV" => "edit10", "TAB" => GetMessage("parser_local_tab"), "ICON"=>"main_user_edit", "TITLE"=>GetMessage("parser_local_tab")),
                array("DIV" => "edit11", "TAB" => GetMessage("parser_video_tab"), "ICON"=>"main_user_edit", "TITLE"=>GetMessage("parser_video_tab")),
            );
            $isCatalog = true;
        }else{
            $aTabs = array(
                array("DIV" => "edit1", "TAB" => GetMessage("parser_tab"), "ICON"=>"main_user_edit", "TITLE"=>GetMessage("parser_tab")),
                array("DIV" => "edit12", "TAB" => GetMessage("parser_preview_xls_tab"), "ICON"=>"main_user_edit", "TITLE"=>GetMessage("parser_preview_xls_tab")),
                array("DIV" => "edit2", "TAB" => GetMessage("parser_preview_tab"), "ICON"=>"main_user_edit", "TITLE"=>GetMessage("parser_preview_tab")),
                array("DIV" => "edit3", "TAB" => GetMessage("parser_props_tab"), "ICON"=>"main_user_edit", "TITLE"=>GetMessage("parser_props_tab")),
                array("DIV" => "edit4", "TAB" => GetMessage("parser_settings_tab"), "ICON"=>"main_user_edit", "TITLE"=>GetMessage("parser_settings_tab")),
                array("DIV" => "edit5", "TAB" => GetMessage("parser_uniq_tab"), "ICON"=>"main_user_edit", "TITLE"=>GetMessage("parser_uniq_tab")),
                array("DIV" => "edit6", "TAB" => GetMessage("parser_auth"), "ICON"=>"main_user_edit", "TITLE"=>GetMessage("parser_auth")),
                array("DIV" => "edit7", "TAB" => GetMessage("parser_logs_tab"), "ICON"=>"main_user_edit", "TITLE"=>GetMessage("parser_logs_tab")),
                array("DIV" => "edit8", "TAB" => GetMessage("parser_local_tab"), "ICON"=>"main_user_edit", "TITLE"=>GetMessage("parser_local_tab")),
                array("DIV" => "edit9", "TAB" => GetMessage("parser_video_tab"), "ICON"=>"main_user_edit", "TITLE"=>GetMessage("parser_video_tab")),
            );
            $isCatalog = false;
        }
    }
    elseif ($shs_TYPE=="xls_catalo" || $_GET["type"]=="xls_catalo") //���� ��� XLS
    {
        if(CModule::IncludeModule('catalog') && (($shs_IBLOCK_ID && CCatalog::GetList(Array("name" => "asc"), Array("ACTIVE"=>"Y", "ID"=>$shs_IBLOCK_ID))->Fetch()) || (is_array($arIblock) && !empty($arIblock) && $arIblock["PRODUCT_IBLOCK_ID"]!=0 && $arIblock["SKU_PROPERTY_ID"]!=0)  || !$shs_IBLOCK_ID))
        {
            $aTabs = array(
                array("DIV" => "edit1", "TAB" => GetMessage("parser_tab"), "ICON"=>"main_user_edit", "TITLE"=>GetMessage("parser_tab")),
                array("DIV" => "edit12", "TAB" => GetMessage("parser_preview_xls_tab"), "ICON"=>"main_user_edit", "TITLE"=>GetMessage("parser_preview_xls_tab")),
                array("DIV" => "edit2", "TAB" => GetMessage("parser_basic_settings_tab"), "ICON"=>"main_user_edit", "TITLE"=>GetMessage("parser_basic_settings_tab")),
                array("DIV" => "edit3", "TAB" => GetMessage("parser_props_tab"), "ICON"=>"main_user_edit", "TITLE"=>GetMessage("parser_props_tab")),
                array("DIV" => "edit4", "TAB" => GetMessage("parser_catalog_tab"), "ICON"=>"main_user_edit", "TITLE"=>GetMessage("parser_catalog_tab")),
                array("DIV" => "edit5", "TAB" => GetMessage("parser_offer_tab"), "ICON"=>"main_user_edit", "TITLE"=>GetMessage("parser_offer_tab")),
                array("DIV" => "edit6", "TAB" => GetMessage("parser_settings_tab"), "ICON"=>"main_user_edit", "TITLE"=>GetMessage("parser_settings_tab")),
                array("DIV" => "edit7", "TAB" => GetMessage("parser_uniq_tab"), "ICON"=>"main_user_edit", "TITLE"=>GetMessage("parser_uniq_tab")),
                array("DIV" => "edit8", "TAB" => GetMessage("parser_auth"), "ICON"=>"main_user_edit", "TITLE"=>GetMessage("parser_auth")),
                array("DIV" => "edit9", "TAB" => GetMessage("parser_logs_tab"), "ICON"=>"main_user_edit", "TITLE"=>GetMessage("parser_logs_tab")),
                array("DIV" => "edit10", "TAB" => GetMessage("parser_local_tab"), "ICON"=>"main_user_edit", "TITLE"=>GetMessage("parser_local_tab")),
                array("DIV" => "edit11", "TAB" => GetMessage("parser_video_tab"), "ICON"=>"main_user_edit", "TITLE"=>GetMessage("parser_video_tab")),
            );
            $isCatalog = true;
        } else {
            $aTabs = array(
                array("DIV" => "edit1", "TAB" => GetMessage("parser_tab"), "ICON"=>"main_user_edit", "TITLE"=>GetMessage("parser_tab")),
                array("DIV" => "edit12", "TAB" => GetMessage("parser_preview_xls_tab"), "ICON"=>"main_user_edit", "TITLE"=>GetMessage("parser_preview_xls_tab")),
                array("DIV" => "edit2", "TAB" => GetMessage("parser_preview_tab"), "ICON"=>"main_user_edit", "TITLE"=>GetMessage("parser_preview_tab")),
                array("DIV" => "edit3", "TAB" => GetMessage("parser_props_tab"), "ICON"=>"main_user_edit", "TITLE"=>GetMessage("parser_props_tab")),
                array("DIV" => "edit4", "TAB" => GetMessage("parser_settings_tab"), "ICON"=>"main_user_edit", "TITLE"=>GetMessage("parser_settings_tab")),
                array("DIV" => "edit5", "TAB" => GetMessage("parser_uniq_tab"), "ICON"=>"main_user_edit", "TITLE"=>GetMessage("parser_uniq_tab")),
                array("DIV" => "edit6", "TAB" => GetMessage("parser_auth"), "ICON"=>"main_user_edit", "TITLE"=>GetMessage("parser_auth")),
                array("DIV" => "edit7", "TAB" => GetMessage("parser_logs_tab"), "ICON"=>"main_user_edit", "TITLE"=>GetMessage("parser_logs_tab")),
                array("DIV" => "edit8", "TAB" => GetMessage("parser_local_tab"), "ICON"=>"main_user_edit", "TITLE"=>GetMessage("parser_local_tab")),
                array("DIV" => "edit9", "TAB" => GetMessage("parser_video_tab"), "ICON"=>"main_user_edit", "TITLE"=>GetMessage("parser_video_tab")),
            );
            $isCatalog = false;
        }
    }
    /*elseif($shs_TYPE=="csv_catalo" || $_GET["type"]=="csv_catalo")
    {
        if(CModule::IncludeModule('catalog') && (($shs_IBLOCK_ID && CCatalog::GetList(Array("name" => "asc"), Array("ACTIVE"=>"Y", "ID"=>$shs_IBLOCK_ID))->Fetch()) || (is_array($arIblock) && !empty($arIblock) && $arIblock["PRODUCT_IBLOCK_ID"]!=0 && $arIblock["SKU_PROPERTY_ID"]!=0)  || !$shs_IBLOCK_ID))
        {
            $aTabs = array(
                array("DIV" => "edit1", "TAB" => GetMessage("parser_tab"), "ICON"=>"main_user_edit", "TITLE"=>GetMessage("parser_tab")),
                //array("DIV" => "edit2", "TAB" => GetMessage("parser_pagenavigation_tab"), "ICON"=>"main_user_edit", "TITLE"=>GetMessage("parser_pagenavigation_tab")),
                array("DIV" => "edit12", "TAB" => GetMessage("parser_catalog_file_tab"), "ICON"=>"main_user_edit", "TITLE"=>GetMessage("parser_catalog_file_tab")),
                array("DIV" => "edit2", "TAB" => GetMessage("parser_basic_settings_tab"), "ICON"=>"main_user_edit", "TITLE"=>GetMessage("parser_basic_settings_tab")),
                //array("DIV" => "edit4", "TAB" => GetMessage("parser_detail_tab"), "ICON"=>"main_user_edit", "TITLE"=>GetMessage("parser_detail_tab")),
                array("DIV" => "edit3", "TAB" => GetMessage("parser_props_tab"), "ICON"=>"main_user_edit", "TITLE"=>GetMessage("parser_props_tab")),
                array("DIV" => "edit4", "TAB" => GetMessage("parser_catalog_tab"), "ICON"=>"main_user_edit", "TITLE"=>GetMessage("parser_catalog_tab")),
                array("DIV" => "edit_detail_catalog", "TAB" => GetMessage("parser_detail_from_catalog_tab"), "ICON"=>"main_user_edit", "TITLE"=>GetMessage("parser_detail_from_catalog_tab")),
                array("DIV" => "edit5", "TAB" => GetMessage("parser_offer_from_catalog_tab"), "ICON"=>"main_user_edit", "TITLE"=>GetMessage("parser_offer_from_catalog_tab")),
                array("DIV" => "edit6", "TAB" => GetMessage("parser_settings_tab"), "ICON"=>"main_user_edit", "TITLE"=>GetMessage("parser_settings_tab")),
                array("DIV" => "edit7", "TAB" => GetMessage("parser_uniq_tab"), "ICON"=>"main_user_edit", "TITLE"=>GetMessage("parser_uniq_tab")),
                array("DIV" => "edit8", "TAB" => GetMessage("parser_auth"), "ICON"=>"main_user_edit", "TITLE"=>GetMessage("parser_auth")),
                array("DIV" => "edit9", "TAB" => GetMessage("parser_logs_tab"), "ICON"=>"main_user_edit", "TITLE"=>GetMessage("parser_logs_tab")),
                array("DIV" => "edit10", "TAB" => GetMessage("parser_local_tab"), "ICON"=>"main_user_edit", "TITLE"=>GetMessage("parser_local_tab")),
                array("DIV" => "edit11", "TAB" => GetMessage("parser_video_tab"), "ICON"=>"main_user_edit", "TITLE"=>GetMessage("parser_video_tab")),
            );
            $isCatalog = true;
        }else{
            $aTabs = array(
                array("DIV" => "edit1", "TAB" => GetMessage("parser_tab"), "ICON"=>"main_user_edit", "TITLE"=>GetMessage("parser_tab")),
                //array("DIV" => "edit2", "TAB" => GetMessage("parser_pagenavigation_tab"), "ICON"=>"main_user_edit", "TITLE"=>GetMessage("parser_pagenavigation_tab")),
                array("DIV" => "edit2", "TAB" => GetMessage("parser_preview_tab"), "ICON"=>"main_user_edit", "TITLE"=>GetMessage("parser_preview_tab")),
                //array("DIV" => "edit4", "TAB" => GetMessage("parser_detail_tab"), "ICON"=>"main_user_edit", "TITLE"=>GetMessage("parser_detail_tab")),
                array("DIV" => "edit3", "TAB" => GetMessage("parser_props_tab"), "ICON"=>"main_user_edit", "TITLE"=>GetMessage("parser_props_tab")),
                array("DIV" => "edit4", "TAB" => GetMessage("parser_settings_tab"), "ICON"=>"main_user_edit", "TITLE"=>GetMessage("parser_settings_tab")),
                array("DIV" => "edit5", "TAB" => GetMessage("parser_uniq_tab"), "ICON"=>"main_user_edit", "TITLE"=>GetMessage("parser_uniq_tab")),
                array("DIV" => "edit6", "TAB" => GetMessage("parser_auth"), "ICON"=>"main_user_edit", "TITLE"=>GetMessage("parser_auth")),
                array("DIV" => "edit7", "TAB" => GetMessage("parser_logs_tab"), "ICON"=>"main_user_edit", "TITLE"=>GetMessage("parser_logs_tab")),
                array("DIV" => "edit8", "TAB" => GetMessage("parser_local_tab"), "ICON"=>"main_user_edit", "TITLE"=>GetMessage("parser_local_tab")),
                array("DIV" => "edit9", "TAB" => GetMessage("parser_video_tab"), "ICON"=>"main_user_edit", "TITLE"=>GetMessage("parser_video_tab")),
            );
            $isCatalog = false;
        }
    } */
    $aTabs[] = array("DIV" => "edit_notification", "TAB" => GetMessage("parser_notification"), "ICON"=>"main_user_edit", "TITLE"=>GetMessage("parser_notification"));
    //$rsIBlock = CCatalog::GetList(Array("name" => "asc"), Array("ACTIVE"=>"Y"));
    $rsIBlock = CIBlock::GetList(Array("name" => "asc"), Array("ACTIVE"=>"Y"));
    while($arr=$rsIBlock->Fetch()){
        $arIBlock['REFERENCE'][] = "[".$arr["ID"]."] ".$arr["NAME"];
        $arIBlock['REFERENCE_ID'][] = $arr["ID"];
    }
}
/*
to highload
*/
elseif (($shs_TYPE=="catalog" || $_GET["type"]=="catalog" || $shs_TYPE=="xml" || $_GET["type"]=="xml" || $shs_TYPE=="csv" || $_GET["type"]=="csv" || $shs_TYPE=="xls" || $_GET["type"]=="xls") && (isset($shs_TYPE_OUT) && $shs_TYPE_OUT=="HL") || (isset($_GET["type_out"]) && $_GET["type_out"]=="HL"))
{
    $Hlist = HL\HighloadBlockTable::getList(array(
        'select'=>array('ID','NAME'),
    ));
    $arIBlock = array();
    while($hl = $Hlist->fetch()){
        $arIBlock['REFERENCE'][] = '['.$hl['ID'].'] '.$hl['NAME'];
        $arIBlock['REFERENCE_ID'][] = $hl['ID'];
    }
    if($shs_TYPE=="catalog" || $_GET["type"]=="catalog"){
        $aTabs = array(
            array("DIV" => "edit1", "TAB" => GetMessage("parser_tab"), "ICON"=>"main_user_edit", "TITLE"=>GetMessage("parser_tab")),
            array("DIV" => "edit2", "TAB" => GetMessage("parser_pagenavigation_tab"), "ICON"=>"main_user_edit", "TITLE"=>GetMessage("parser_pagenavigation_tab")),
            array("DIV" => "edit3", "TAB" => GetMessage("parser_preview_tab"), "ICON"=>"main_user_edit", "TITLE"=>GetMessage("parser_preview_tab")),
            array("DIV" => "edit4", "TAB" => GetMessage("parser_detail_tab"), "ICON"=>"main_user_edit", "TITLE"=>GetMessage("parser_detail_tab")),
            array("DIV" => "edit5", "TAB" => GetMessage("parser_props_tab"), "ICON"=>"main_user_edit", "TITLE"=>GetMessage("parser_props_tab")),
            //array("DIV" => "edit6", "TAB" => GetMessage("parser_catalog_tab"), "ICON"=>"main_user_edit", "TITLE"=>GetMessage("parser_catalog_tab")),
            //array("DIV" => "edit7", "TAB" => GetMessage("parser_offer_tab"), "ICON"=>"main_user_edit", "TITLE"=>GetMessage("parser_offer_tab")),
            array("DIV" => "edit8", "TAB" => GetMessage("parser_settings_tab"), "ICON"=>"main_user_edit", "TITLE"=>GetMessage("parser_settings_tab")),
            array("DIV" => "edit9", "TAB" => GetMessage("parser_uniq_tab"), "ICON"=>"main_user_edit", "TITLE"=>GetMessage("parser_uniq_tab")),
            array("DIV" => "edit10", "TAB" => GetMessage("parser_auth"), "ICON"=>"main_user_edit", "TITLE"=>GetMessage("parser_auth")),
            array("DIV" => "edit11", "TAB" => GetMessage("parser_logs_tab"), "ICON"=>"main_user_edit", "TITLE"=>GetMessage("parser_logs_tab")),
            array("DIV" => "edit12", "TAB" => GetMessage("parser_local_tab"), "ICON"=>"main_user_edit", "TITLE"=>GetMessage("parser_local_tab")),
            array("DIV" => "edit13", "TAB" => GetMessage("parser_video_tab"), "ICON"=>"main_user_edit", "TITLE"=>GetMessage("parser_video_tab")),
        );
    } elseif($shs_TYPE=="csv" || $_GET["type"]=="csv"){
      $aTabs = array(
                array("DIV" => "edit1", "TAB" => GetMessage("parser_tab"), "ICON"=>"main_user_edit", "TITLE"=>GetMessage("parser_tab")),
                //array("DIV" => "edit2", "TAB" => GetMessage("parser_pagenavigation_tab"), "ICON"=>"main_user_edit", "TITLE"=>GetMessage("parser_pagenavigation_tab")),
                //array("DIV" => "edit12", "TAB" => GetMessage("parser_catalog_file_tab"), "ICON"=>"main_user_edit", "TITLE"=>GetMessage("parser_catalog_file_tab")),
                array("DIV" => "edit2", "TAB" => GetMessage("parser_basic_settings_tab"), "ICON"=>"main_user_edit", "TITLE"=>GetMessage("parser_basic_settings_tab")),
                //array("DIV" => "edit4", "TAB" => GetMessage("parser_detail_tab"), "ICON"=>"main_user_edit", "TITLE"=>GetMessage("parser_detail_tab")),
                array("DIV" => "edit3", "TAB" => GetMessage("parser_props_tab"), "ICON"=>"main_user_edit", "TITLE"=>GetMessage("parser_props_tab")),
                //array("DIV" => "edit4", "TAB" => GetMessage("parser_catalog_tab"), "ICON"=>"main_user_edit", "TITLE"=>GetMessage("parser_catalog_tab")),
                //array("DIV" => "edit5", "TAB" => GetMessage("parser_offer_tab"), "ICON"=>"main_user_edit", "TITLE"=>GetMessage("parser_offer_tab")),
                array("DIV" => "edit6", "TAB" => GetMessage("parser_settings_tab"), "ICON"=>"main_user_edit", "TITLE"=>GetMessage("parser_settings_tab")),
                array("DIV" => "edit7", "TAB" => GetMessage("parser_uniq_tab"), "ICON"=>"main_user_edit", "TITLE"=>GetMessage("parser_uniq_tab")),
                array("DIV" => "edit8", "TAB" => GetMessage("parser_auth"), "ICON"=>"main_user_edit", "TITLE"=>GetMessage("parser_auth")),
//                array("DIV" => "edit9", "TAB" => GetMessage("parser_logs_tab"), "ICON"=>"main_user_edit", "TITLE"=>GetMessage("parser_logs_tab")),
                array("DIV" => "edit10", "TAB" => GetMessage("parser_local_tab"), "ICON"=>"main_user_edit", "TITLE"=>GetMessage("parser_local_tab")),
                array("DIV" => "edit11", "TAB" => GetMessage("parser_video_tab"), "ICON"=>"main_user_edit", "TITLE"=>GetMessage("parser_video_tab")),
            );
    }
    elseif($shs_TYPE=="xml" || $_GET["type"]=="xml"){
      $aTabs = array(
                 array("DIV" => "edit1", "TAB" => GetMessage("parser_tab"), "ICON"=>"main_user_edit", "TITLE"=>GetMessage("parser_tab")),
//                array("DIV" => "edit2", "TAB" => GetMessage("parser_pagenavigation_tab"), "ICON"=>"main_user_edit", "TITLE"=>GetMessage("parser_pagenavigation_tab")),
//                array("DIV" => "edit12", "TAB" => GetMessage("parser_catalog_file_tab"), "ICON"=>"main_user_edit", "TITLE"=>GetMessage("parser_catalog_file_tab")),
                array("DIV" => "edit2", "TAB" => GetMessage("parser_highload_block_tab"), "ICON"=>"main_user_edit", "TITLE"=>GetMessage("parser_highload_block_tab")),
                //array("DIV" => "edit4", "TAB" => GetMessage("parser_detail_tab"), "ICON"=>"main_user_edit", "TITLE"=>GetMessage("parser_detail_tab")),
//                array("DIV" => "edit3", "TAB" => GetMessage("parser_props_tab"), "ICON"=>"main_user_edit", "TITLE"=>GetMessage("parser_props_tab")),
//                array("DIV" => "edit4", "TAB" => GetMessage("parser_catalog_tab"), "ICON"=>"main_user_edit", "TITLE"=>GetMessage("parser_catalog_tab")),
//                array("DIV" => "edit5", "TAB" => GetMessage("parser_offer_tab"), "ICON"=>"main_user_edit", "TITLE"=>GetMessage("parser_offer_tab")),
                array("DIV" => "edit6", "TAB" => GetMessage("parser_settings_tab"), "ICON"=>"main_user_edit", "TITLE"=>GetMessage("parser_settings_tab")),
                array("DIV" => "edit7", "TAB" => GetMessage("parser_uniq_tab"), "ICON"=>"main_user_edit", "TITLE"=>GetMessage("parser_uniq_tab")),
//                array("DIV" => "edit8", "TAB" => GetMessage("parser_auth"), "ICON"=>"main_user_edit", "TITLE"=>GetMessage("parser_auth")),
//                array("DIV" => "edit9", "TAB" => GetMessage("parser_logs_tab"), "ICON"=>"main_user_edit", "TITLE"=>GetMessage("parser_logs_tab")),
//                array("DIV" => "edit10", "TAB" => GetMessage("parser_local_tab"), "ICON"=>"main_user_edit", "TITLE"=>GetMessage("parser_local_tab")),
                array("DIV" => "edit11", "TAB" => GetMessage("parser_video_tab"), "ICON"=>"main_user_edit", "TITLE"=>GetMessage("parser_video_tab")),
            );
    }
    $aTabs[] = array("DIV" => "edit_notification", "TAB" => GetMessage("parser_notification"), "ICON"=>"main_user_edit", "TITLE"=>GetMessage("parser_notification"));
    $isCatalog = false;
}
/***
**** RSS/PAGE to iblock
***/

elseif((isset($shs_TYPE_OUT) && $shs_TYPE_OUT!="HL") || ( isset($_GET["type_out"]) && $_GET["type_out"]!="HL") || !isset($_GET['type_out']))
{
    $rsIBlock = CIBlock::GetList(Array("name" => "asc"), Array("ACTIVE"=>"Y"));
    while($arr=$rsIBlock->Fetch()){
        $arIBlock['REFERENCE'][] = "[".$arr["ID"]."] ".$arr["NAME"];
        $arIBlock['REFERENCE_ID'][] = $arr["ID"];
    }

    $aTabs = array(
        array("DIV" => "edit1", "TAB" => GetMessage("parser_tab"), "ICON"=>"main_user_edit", "TITLE"=>GetMessage("parser_tab")),
        array("DIV" => "edit2", "TAB" => GetMessage("parser_preview_tab"), "ICON"=>"main_user_edit", "TITLE"=>GetMessage("parser_preview_tab")),
        array("DIV" => "edit3", "TAB" => GetMessage("parser_detail_tab"), "ICON"=>"main_user_edit", "TITLE"=>GetMessage("parser_detail_tab")),
        array("DIV" => "edit4", "TAB" => GetMessage("parser_settings_tab"), "ICON"=>"main_user_edit", "TITLE"=>GetMessage("parser_settings_tab")),
        array("DIV" => "edit5", "TAB" => GetMessage("parser_local_tab"), "ICON"=>"main_user_edit", "TITLE"=>GetMessage("parser_local_tab")),
        array("DIV" => "edit6", "TAB" => GetMessage("parser_video_tab"), "ICON"=>"main_user_edit", "TITLE"=>GetMessage("parser_video_tab")),
    );
}

$tabControl = new CAdminTabControl("tabControl", $aTabs);

if(!empty($shs_IBLOCK_ID)){
    $rsSections = CIBlockSection::GetList(array("left_margin"=>"asc"), array(/*'ACTIVE'=>"Y",*/ "IBLOCK_ID"=>$shs_IBLOCK_ID), false, array('ID', 'NAME', "IBLOCK_ID", "DEPTH_LEVEL"));

    while($arr=$rsSections->Fetch()){
        $arr["NAME"] = str_repeat(" . ", $arr["DEPTH_LEVEL"]).$arr["NAME"];
        $arSection['REFERENCE'][] = $arr["NAME"];
        $arSection['REFERENCE_ID'][] = $arr["ID"];
    }
}

$arrDateActive['REFERENCE'][0] =  GetMessage("parser_date_active_now");
$arrDateActive['REFERENCE'][1] =  GetMessage("parser_date_active_now_time");
$arrDateActive['REFERENCE'][2] =  GetMessage("parser_date_active_public");
$arrDateActive['REFERENCE_ID'][0] = "NOW";
$arrDateActive['REFERENCE_ID'][1] = "NOW_TIME";
$arrDateActive['REFERENCE_ID'][2] = "PUBLIC";

unset($arParamIndex['REFERENCE'][0]);
unset($arParamIndex['REFERENCE_ID'][0]);
?>
<a target="blank" href=""><?=GetMessage("parser_instruction")?></a>
<div id="status_bar" style="display:none;overflow:hidden;">
    <div id="progress_bar" style="width: 500px;float:left;" class="adm-progress-bar-outer">
        <div id="progress_bar_inner" style="width: 0px;" class="adm-progress-bar-inner"></div>
        <div id="progress_text" style="width: 500px;" class="adm-progress-bar-inner-text">0%</div>
    </div>
    <div id="catalog_bar" style="float:left;width:700px;height:62px;line-height:20px;font-weight:bold;margin-left:30px;"></div>
    <div id="current_test"></div>
</div>
<div style="clear:both;"></div>
<?
if(isset($_REQUEST["mess"]) && $_REQUEST["mess"] == "ok" && $ID>0)
    CAdminMessage::ShowMessage(array("MESSAGE"=>GetMessage("parser_saved"), "TYPE"=>"OK"));

if($message)
    echo $message->Show();
elseif($rubric->LAST_ERROR!="")
    CAdminMessage::ShowMessage($rubric->LAST_ERROR);

if(isset($_REQUEST['end']) && $_REQUEST['end']==1 && $ID>0){
    if(isset($_GET['SUCCESS'][0])){
      foreach($_GET['SUCCESS'] as $success) CAdminMessage::ShowMessage(array("MESSAGE"=>$success, "TYPE"=>"OK"));
    }
    if(isset($_GET['ERROR'][0])){
        foreach($_GET['ERROR'] as $error) CAdminMessage::ShowMessage($error);
    }
}
$shs_SETTINGS = (string)$shs_SETTINGS;
$shs_SETTINGS = unserialize(base64_decode($shs_SETTINGS));

$shsDebug = $shs_SETTINGS["catalog"]["mode"];

if(!function_exists('curl_getinfo')) CAdminMessage::ShowMessage(array("MESSAGE"=>GetMessage("parser_exists_libcurl")));
if(!class_exists('XMLReader')) CAdminMessage::ShowMessage(array("MESSAGE"=>GetMessage("parser_class_exists_XMLReader")));

if($shs_DEMO==2)CAdminMessage::ShowMessage(array("MESSAGE"=>GetMessage("parser_demo")));
if($shs_DEMO==3)CAdminMessage::ShowMessage(array("MESSAGE"=>GetMessage("parser_demo_end")));

$arTypeOut['reference']=array(GetMessage('parser_type_out_iblock'),GetMessage('parser_type_out_hl'));
$arTypeOut['reference_id']=array('iblock', 'HL');
?>
<div id="shs_message"></div>
<form method="POST" id="shs-parser" Action="<?echo $APPLICATION->GetCurPage()?>" ENCTYPE="multipart/form-data" name="post_form">
<?
$tabControl->Begin();

if($shs_TYPE=="page" || (isset($_GET["type"]) && $_GET["type"]=="page")) include("parser_edit_page.php");
elseif($shs_TYPE=="catalog" || (isset($_GET["type"]) && $_GET["type"]=="catalog")) {
    if ($shs_TYPE_OUT=="HL" || (isset($_GET["type_out"]) && $_GET["type_out"]=="HL"))
        include("parser_edit_catalog_hl.php");
    elseif($shs_TYPE_OUT!="HL" || (isset($_GET["type_out"]) && $_GET["type_out"]!="HL"))
        include("parser_edit_catalog.php");
}
elseif($shs_TYPE=="csv" || (isset($_GET["type"]) && $_GET["type"]=="csv")){
    if ($shs_TYPE_OUT=="HL" || (isset($_GET["type_out"]) && $_GET["type_out"]=="HL"))
        include("parser_edit_csv_hl.php");
    elseif($shs_TYPE_OUT!="HL" || (isset($_GET["type_out"]) && $_GET["type_out"]!="HL"))
        include("parser_edit_csv.php");
    //if(($shs_TYPE_OUT!="HL" || !isset($_GET["type_out"]) || $_GET["type_out"]!="HL")) include("parser_edit_csv.php");
}
elseif((($shs_TYPE=="xml" || $shs_TYPE=="xml_catalo") || (isset($_GET["type"]) && ($_GET["type"]=="xml" || $_GET["type"]=="xml_catalo"))) && ($shs_TYPE_OUT!="HL" || !isset($_GET["type_out"]) || $_GET["type_out"]!="HL")) include("parser_edit_xml.php");
elseif(($shs_TYPE=="xls" || (isset($_GET["type"]) && $_GET["type"]=="xls")) && ($shs_TYPE_OUT!="HL" || !isset($_GET["type_out"]) || $_GET["type_out"]!="HL")) include("parser_edit_xls.php");
//elseif(($shs_TYPE=="csv_catalo" || (isset($_GET["type"]) && $_GET["type"]=="csv_catalo")) && ($shs_TYPE_OUT!="HL" || !isset($_GET["type_out"]) || $_GET["type_out"]!="HL")) include("parser_edit_csv_catalog.php");
elseif(($shs_TYPE=="xls_catalo" || (isset($_GET["type"]) && $_GET["type"]=="xls_catalo")) && ($shs_TYPE_OUT!="HL" || !isset($_GET["type_out"]) || $_GET["type_out"]!="HL")) include("parser_edit_xls_catalog.php");
elseif((!$shs_TYPE && $ID) || $shs_TYPE=="rss" || (isset($_GET["type"]) && $_GET["type"]=="rss") || !isset($ID) || !$ID) include("parser_edit_rss.php");
?>

<?echo BeginNote();?>
<span class="required">*</span><?echo GetMessage("REQUIRED_FIELDS")?>
<?echo EndNote();?>


<script language="JavaScript">
            var target_id = '';
            var target_select_id = '';
            var target_shadow_id = '';
            function addSectionProperty(iblock_id, select_id, tr, table_id)
            {
                {
                    target_id = table_id;
                    target_select_id = select_id;
                    target_shadow_id = tr;
                    (new BX.CDialog({
                        'content_url' : '/bitrix/admin/iblock_edit_property.php?lang=<?echo LANGUAGE_ID?>&IBLOCK_ID='+iblock_id+'&ID=n0&bxpublic=Y&from_module=iblock&return_url=section_edit',
                        'width' : 700,
                        'height' : 400,
                        'buttons': [BX.CDialog.btnSave, BX.CDialog.btnCancel]
                    })).Show();
                }
            }
            function deleteSectionProperty(id, select_id, shadow_id, table_id)
            {
                var hidden = BX('hidden_SECTION_PROPERTY_' + id);
                var tr = BX('tr_SECTION_PROPERTY_' + id);
                if(hidden && tr)
                {
                    hidden.value = 'N';
                    tr.style.display = 'none';
                    var select = BX(select_id);
                    var shadow = BX(shadow_id);
                    if(select && shadow)
                    {
                        jsSelectUtils.deleteAllOptions(select);
                        for(var i = 0; i < shadow.length; i++)
                        {
                            if(shadow[i].value <= 0)
                                jsSelectUtils.addNewOption(select, shadow[i].value, shadow[i].text);
                            else if (BX('hidden_SECTION_PROPERTY_' + shadow[i].value).value == 'N')
                                jsSelectUtils.addNewOption(select, shadow[i].value, shadow[i].text);
                        }
                    }
                    adjustEmptyTR(table_id);
                }
            }
            function createSectionProperty(id, name, type)
            {
                jQuery.ajax({
                    url: "",
                    type: "POST",
                    data: 'ajax=1&prop_id='+id,
                    dataType: 'html',
                    success: function(code){
                        code = $.trim(code);
                        if(target_select_id=="loadDopPropOffer")
                        {
                            str = '<tr><td width="40%" class="adm-detail-content-cell-l">'+name+'&nbsp;['+code+']:</td><td width="60%" class="adm-detail-content-cell-r"><input type="text" value="" name="SETTINGS[offer][selector_prop]['+code+']" data-code="'+code+'" size="40">&nbsp;<a href="#" class="prop_delete">Delete</a></td></tr>';
                            target_shadow_id.before(str);
                            
                            strName = '<option value="'+code+'">'+name+'</option>';
                            $(".add_name").append(strName);
                        }else if(target_select_id=="loadDopPropOffer1")
                        {
                            str = '<tr><td width="40%" class="adm-detail-content-cell-l">'+name+'&nbsp;['+code+']:</td><td width="60%" class="adm-detail-content-cell-r"><input type="text" value="" name="SETTINGS[offer][find_prop]['+code+']" data-code="'+code+'" size="40">&nbsp;<a href="#" class="prop_delete">Delete</a></td></tr>';
                            target_shadow_id.before(str);
                            strName = '<option value="'+code+'">'+name+'</option>';
                            $(".add_name").append(strName);
                        }
                        else if(target_select_id=="loadDopProp")
                        {
                            str = '<tr><td width="40%" class="adm-detail-content-cell-l">'+name+'&nbsp;['+code+']:</td><td width="60%" class="adm-detail-content-cell-r"><input type="text" value="" name="SETTINGS[catalog][selector_prop]['+code+']" data-code="'+code+'" size="40">&nbsp;<a href="#" class="prop_delete">Delete</a></td></tr>';
                            target_shadow_id.before(str);
                        }
                        else if(target_select_id=="loadDopPropDetail")
                        {
                            str = '<tr><td width="40%" class="adm-detail-content-cell-l">'+name+'&nbsp;['+code+']:</td><td width="60%" class="adm-detail-content-cell-r"><input type="text" value="" name="SETTINGS[catalog][detail][selector_prop]['+code+']" data-code="'+code+'" size="40">&nbsp;<a href="#" class="prop_delete">Delete</a></td></tr>';
                            target_shadow_id.before(str);
                        }
                        else if(target_select_id=="loadFilterProps")
                        {
                            selectFilter = '<select name="SETTINGS[props_filter_circs]['+code+']"><option value="equally"><?=GetMessage('parser_filter_equally');?></option><option value="strpos"><?=GetMessage('parser_filter_strpos');?></option><option value="stripos"><?=GetMessage('parser_filter_stripos');?></option></select>';
                            str = '<tr><td width="40%" class="adm-detail-content-cell-l">'+name+'&nbsp;['+code+']:</td><td width="60%" class="adm-detail-content-cell-r">'+selectFilter+'<input type="text" value="" name="SETTINGS[props_filter_value]['+code+']" data-code="'+code+'" size="40">&nbsp;<a href="#" class="prop_delete">Delete</a></td></tr>';
                            target_shadow_id.before(str);
                    
                        }
                        else if(target_select_id=="loadDopProp1")
                        {
                            str = '<tr><td width="40%" class="adm-detail-content-cell-l">'+name+'&nbsp;['+code+']:</td><td width="60%" class="adm-detail-content-cell-r"><input type="text" value="" name="SETTINGS[catalog][find_prop]['+code+']" data-code="'+code+'" size="40">&nbsp;<a href="#" class="prop_delete">Delete</a></td></tr>';
                            target_shadow_id.before(str);
                        }
                        else if(target_select_id=="loadDopProp1Detail")
                        {
                            str = '<tr><td width="40%" class="adm-detail-content-cell-l">'+name+'&nbsp;['+code+']:</td><td width="60%" class="adm-detail-content-cell-r"><input type="text" value="" name="SETTINGS[catalog][detail][find_prop]['+code+']" data-code="'+code+'" size="40">&nbsp;<a href="#" class="prop_delete">Delete</a></td></tr>';
                            target_shadow_id.before(str);
                        }
                        else if(target_select_id=="loadDopProp2")
                        {
                            str = '<tr><td width="40%" class="adm-detail-content-cell-l">'+name+'&nbsp;['+code+']:</td><td width="60%" class="adm-detail-content-cell-r"><input type="text" value="" name="SETTINGS[catalog][selector_prop_preview]['+code+']" data-code="'+code+'" size="40">&nbsp;<a href="#" class="prop_delete">Delete</a></td></tr>';
                            target_shadow_id.before(str);
                        }else if(target_select_id=="loadDopProp3")
                        {
                            str = '<tr><td width="40%" class="adm-detail-content-cell-l">'+name+'&nbsp;['+code+']:</td><td width="60%" class="adm-detail-content-cell-r"><input type="text" value="" name="SETTINGS[catalog][find_prop_preview]['+code+']" data-code="'+code+'" size="40">&nbsp;<a href="#" class="prop_delete">Delete</a></td></tr>';
                            target_shadow_id.before(str);
                        }
                        else if(target_select_id=="loadPropField")
                        {
                            str = '<tr><td width="40%" class="adm-detail-content-cell-l">'+name+'&nbsp;['+code+']:</td><td width="60%" class="adm-detail-content-cell-r"><input type="text" value="" name="SETTINGS[catalog][action_props_val]['+code+'][]" data-code="'+code+'" size="40">&nbsp; <select id="SETTINGS[catalog][action_props]['+code+']" name="SETTINGS[catalog][action_props]['+code+'][]"><option value=""><?=GetMessage("shs_parser_select_action_props")?></option><option value="delete"><?=GetMessage("parser_action_props_delete")?></option><option value="add_b"><?=GetMessage("parser_action_props_add_begin")?></option><option value="add_e"><?=GetMessage("parser_action_props_add_end")?></option><option value="lower"><?=GetMessage("parser_action_props_to_lower")?></option></select> <a href="#" class="find_delete">Delete</a></td></tr>';
                            target_shadow_id.before(str);
                        }
                    }
                 })
            }
            function createSectionPropertyOffer(id, name, type)
            {
                jQuery.ajax({
                    url: "",
                    type: "POST",
                    data: 'ajax=1&iblock=<?=$OFFER_IBLOCK_ID?>&prop_id='+id,
                    dataType: 'html',
                    success: function(code){
                        code = $.trim(code);
                        if(target_select_id=="loadDopProp")
                        {
                            str = '<tr><td width="40%" class="adm-detail-content-cell-l">'+name+'&nbsp;['+code+']:</td><td width="60%" class="adm-detail-content-cell-r"><input type="text" value="" name="SETTINGS[catalog][selector_prop]['+code+']" data-code="'+code+'" size="40">&nbsp;<a href="#" class="prop_delete">Delete</a></td></tr>';
                            target_shadow_id.before(str);
                        }
                        if(target_select_id=="loadDopPropDetail")
                        {
                            str = '<tr><td width="40%" class="adm-detail-content-cell-l">'+name+'&nbsp;['+code+']:</td><td width="60%" class="adm-detail-content-cell-r"><input type="text" value="" name="SETTINGS[catalog][detail][selector_prop]['+code+']" data-code="'+code+'" size="40">&nbsp;<a href="#" class="prop_delete">Delete</a></td></tr>';
                            target_shadow_id.before(str);
                        }
                        if(target_select_id=="loadFilterProps")
                        {
                            selectFilter = '<select name="SETTINGS[props_filter_circs]['+code+']"><option value="equally"><?=GetMessage('parser_filter_equally');?></option><option value="strpos"><?=GetMessage('parser_filter_strpos');?></option><option value="stripos"><?=GetMessage('parser_filter_stripos');?></option></select>';
                            str = '<tr><td width="40%" class="adm-detail-content-cell-l">'+name+'&nbsp;['+code+']:</td><td width="60%" class="adm-detail-content-cell-r">'+selectFilter+'<input type="text" value="" name="SETTINGS[props_filter_value]['+code+']" data-code="'+code+'" size="40">&nbsp;<a href="#" class="prop_delete">Delete</a></td></tr>';
                            target_shadow_id.before(str);
                    
                        }
                        else if(target_select_id=="loadDopProp1")
                        {
                            str = '<tr><td width="40%" class="adm-detail-content-cell-l">'+name+'&nbsp;['+code+']:</td><td width="60%" class="adm-detail-content-cell-r"><input type="text" value="" name="SETTINGS[catalog][find_prop]['+code+']" data-code="'+code+'" size="40">&nbsp;<a href="#" class="prop_delete">Delete</a></td></tr>';
                            target_shadow_id.before(str);
                        }
                        else if(target_select_id=="loadDopProp1Detail")
                        {
                            str = '<tr><td width="40%" class="adm-detail-content-cell-l">'+name+'&nbsp;['+code+']:</td><td width="60%" class="adm-detail-content-cell-r"><input type="text" value="" name="SETTINGS[catalog][detail][find_prop]['+code+']" data-code="'+code+'" size="40">&nbsp;<a href="#" class="prop_delete">Delete</a></td></tr>';
                            target_shadow_id.before(str);
                        }
                        else if(target_select_id=="loadDopProp2")
                        {
                            str = '<tr><td width="40%" class="adm-detail-content-cell-l">'+name+'&nbsp;['+code+']:</td><td width="60%" class="adm-detail-content-cell-r"><input type="text" value="" name="SETTINGS[catalog][selector_prop_preview]['+code+']" data-code="'+code+'" size="40">&nbsp;<a href="#" class="prop_delete">Delete</a></td></tr>';
                            target_shadow_id.before(str);
                        }else if(target_select_id=="loadDopProp3")
                        {
                            str = '<tr><td width="40%" class="adm-detail-content-cell-l">'+name+'&nbsp;['+code+']:</td><td width="60%" class="adm-detail-content-cell-r"><input type="text" value="" name="SETTINGS[catalog][find_prop_preview]['+code+']" data-code="'+code+'" size="40">&nbsp;<a href="#" class="prop_delete">Delete</a></td></tr>';
                            target_shadow_id.before(str);
                        }
                    }
                 })
            }
            function adjustEmptyTR(table_id)
            {
                var tbl = BX(table_id);
                if(tbl)
                {
                    var cnt = tbl.rows.length;
                    var tr = tbl.rows[cnt-1];

                    var display = 'table-row';
                    for(var i = 1; i < cnt-1; i++)
                    {
                        if(tbl.rows[i].style.display != 'none')
                            display = 'none';
                    }
                    tr.style.display = display;
                }
            }
</script>
<script language="JavaScript">
    jQuery(document).ready(function(){
        $("#loadDopPropOffer").on("click", function(e){
            e.preventDefault();
            tr = $(this).parents("tr").eq(0);
            
            v = tr.find("select[name=arrPropDopOffer] option:selected").val();
            t = tr.find("select[name=arrPropDopOffer] option:selected").text();
            if(v=="") return false;
            else if(v=="[]")
            {
                sotbit_iblock_edit_property_offer("loadDopPropOffer", tr);
                return false;
            }
            str = '<tr><td width="40%" class="adm-detail-content-cell-l">'+t+'&nbsp;['+v+']:</td><td width="60%" class="adm-detail-content-cell-r"><input type="text" value="" name="SETTINGS[offer][selector_prop]['+v+']" data-code="'+v+'" size="40">&nbsp;<a href="#" class="prop_delete">Delete</a></td></tr>';
            tr.before(str);
        });
        
        $(".add_index_csv").on("click", function(e){
            var popup = new BX.CDialog({
               'title': '��������� ����',
               'content': '<div class="dfdfdf">������</div>',
               'draggable': true,
               'resizable': true,
               'buttons': [BX.CDialog.btnClose]
            });
         
            popup.Show();
        });
        
        $("#loadDopPropOffer2").on("click", function(e){
            e.preventDefault();
            tr = $(this).parents("tr").eq(0);
            
            v = tr.find("select[name=arrPropDopOffer] option:selected").val();
            t = tr.find("select[name=arrPropDopOffer] option:selected").text();
            if(v=="") return false;
            else if(v=="[]")
            {
                sotbit_iblock_edit_property_offer("loadDopPropOffer", tr);
                return false;
            }
            str = '<tr><td width="40%" class="adm-detail-content-cell-l">'+t+'&nbsp;['+v+']:</td><td width="60%" class="adm-detail-content-cell-r"><input type="text" value="" name="SETTINGS[offer][selector_prop_more]['+v+']" data-code="'+v+'" size="40">&nbsp;<a href="#" class="prop_delete">Delete</a></td></tr>';
            tr.before(str);
        });
        
        $("#loadDopPropOffer1").on("click", function(e){
            e.preventDefault();
            tr = $(this).parents("tr").eq(0);
            
            v = tr.find("select[name=arrPropDopOffer] option:selected").val();
            t = tr.find("select[name=arrPropDopOffer] option:selected").text();
            if(v=="") return false;
            else if(v=="[]")
            {
                sotbit_iblock_edit_property_offer("loadDopPropOffer1", tr);
                return false;
            }
            str = '<tr><td width="40%" class="adm-detail-content-cell-l">'+t+'&nbsp;['+v+']:</td><td width="60%" class="adm-detail-content-cell-r"><input type="text" value="" name="SETTINGS[offer][find_prop]['+v+']" data-code="'+v+'" size="40">&nbsp;<a href="#" class="prop_delete">Delete</a></td></tr>';
            tr.before(str);
        });
        
        $("#addPrice").on("click", function(e){
            e.preventDefault();
            tr = $(this).parents("tr").eq(0);
            
            v = tr.find("select[name=arrPricetypes] option:selected").val();
            t = tr.find("select[name=arrPricetypes] option:selected").text();
            if(v=="" || v=='[]') return false;
            if($("tr.adittional_preview_prices_id_"+v).length > 0) return false;
            
            str = '<tr class="adittional_preview_prices_id_'+v+'"><td width="40%" class="adm-detail-content-cell-l">'+'<?php echo GetMessage('parser_preview_price');?>'+t+'&nbsp;['+v+']:</td><td width="60%" class="adm-detail-content-cell-r"><input type="text" value="" name="SETTINGS[prices_preview]['+v+'][value]" size="40" maxlength="250">&nbsp;<a href="#" class="price_delete" data-price-id="'+v+'">Delete</a><input type="hidden" name="SETTINGS[prices_preview]['+v+'][name]" value="'+t+'"></td></tr>';
            
            tr.before(str);
            
            r = $("tr.adittional_prices_settings").eq(0);
            str='';
            if($(".adittional_prices_settings_id_"+v).length == 0){
                str = '<tr width="40%" class="adittional_prices_settings_id_'+v+'"><td class="adm-detail-content-cell-l"><?echo GetMessage('parser_aditt_currency');?>'+t+'&nbsp;['+v+']:</td><td width="60%" class="adm-detail-content-cell-r"><?=SelectBoxFromArray('SETTINGS[adittional_currency][currency_id]', $arCurrency, "RUB", "", "");?></td></tr>';
                r.after(str);
                document.getElementById('SETTINGS[adittional_currency][currency_id]').setAttribute("name",'SETTINGS[adittional_currency]['+v+']');
                document.getElementById('SETTINGS[adittional_currency][currency_id]').setAttribute("id",'SETTINGS[adittional_currency]['+v+']');
            }
        });
        
        $("#addPrevProp").on("click", function(e){
            e.preventDefault();
            tr = $(this).parents("tr").eq(0);
            
            v = tr.find("select[name=arrProps] option:selected").val();
            t = tr.find("select[name=arrProps] option:selected").text();
            if(v=="" || v=='[]') return false;
            if($("tr.adittional_preview_props_id_"+v).length > 0) return false;
            
            str = '<tr class="adittional_preview_props_id_'+v+'"><td width="40%" class="adm-detail-content-cell-l">'+'<?php echo GetMessage('parser_field');?>'+t+'&nbsp;['+v+']:</td><td width="60%" class="adm-detail-content-cell-r"><input type="text" value="" name="SETTINGS[properties][preview]['+v+'][value]" size="40" maxlength="250">&nbsp;<a href="#" class="prev_prop_delete" data-prop-id="'+v+'"><?php echo GetMessage('parser_delete');?></a><input type="hidden" name="SETTINGS[properties][preview]['+v+'][name]" value="'+t+'"></td></tr>';
            
            tr.before(str);
        });
        
        $("#addFieldCategory").on("click", function(e){
            e.preventDefault();
            tr = $(this).parents("tr").eq(0);
            
            v = tr.find("select[name=arrFieldCategory] option:selected").val();
            t = tr.find("select[name=arrFieldCategory] option:selected").text();
            if(v=="" || v=='[]') return false;
            if($("tr.fields_category_"+v).length > 0) return false;
            
            str = '<tr class="fields_category_'+v+'"><td width="40%" class="adm-detail-content-cell-l">'+v+':</td><td width="60%" class="adm-detail-content-cell-r"><input type="text" value="" name="SETTINGS[catalog][fields_category]['+v+']" size="40" maxlength="250"></td></tr>';
            
            tr.before(str);
        });
        
        $("#addDetailProp").on("click", function(e){
            e.preventDefault();
            tr = $(this).parents("tr").eq(0);
            
            v = tr.find("select[name=arrPropsDetail] option:selected").val();
            t = tr.find("select[name=arrPropsDetail] option:selected").text();
            if(v=="" || v=='[]') return false;
            if($("tr.adittional_detail_props_id_"+v).length > 0) return false;
            
            str = '<tr class="adittional_detail_props_id_'+v+'"><td width="40%" class="adm-detail-content-cell-l">'+'<?php echo GetMessage('parser_field');?>'+t+'&nbsp;['+v+']:</td><td width="60%" class="adm-detail-content-cell-r"><input type="text" value="" name="SETTINGS[properties][detail]['+v+'][value]" size="40" maxlength="250">&nbsp;<a href="#" class="prev_prop_delete" data-prop-id="'+v+'"><?php echo GetMessage('parser_delete');?></a><input type="hidden" name="SETTINGS[properties][detail]['+v+'][name]" value="'+t+'"></td></tr>';
            
            tr.before(str);
        });
        
        $("#addStore").on("click", function(e){
            e.preventDefault();
            tr = $(this).parents("tr").eq(0);
            
            v = tr.find("select[name=arrAdditStores] option:selected").val();
            t = tr.find("select[name=arrAdditStores] option:selected").text();
            if(v=="" || v=='[]') return false;
            if($("tr.adittional_store_id_"+v).length > 0) return false;
            
            str = '<tr class="adittional_store_id_'+v+'"><td width="40%" class="adm-detail-content-cell-l">'+'<?php echo GetMessage('parser_addit_store');?>'+t+'&nbsp;['+v+']:</td><td width="60%" class="adm-detail-content-cell-r"><input type="text" value="" name="SETTINGS[addit_stores]['+v+'][value]" size="40" maxlength="250">&nbsp;<a href="#" class="store_delete" data-price-id="'+v+'">Delete</a><input type="hidden" name="SETTINGS[addit_stores]['+v+'][name]" value="'+t+'"></td></tr>';
            
            tr.before(str);
        });
        
        $("#addStoreDetail").on("click", function(e){
            e.preventDefault();
            tr = $(this).parents("tr").eq(0);
            
            v = tr.find("select[name=arrAdditStoresDetail] option:selected").val();
            t = tr.find("select[name=arrAdditStoresDetail] option:selected").text();
            if(v=="" || v=='[]') return false;
            if($("tr.adittional_store_detail_id_"+v).length > 0) return false;
            
            str = '<tr class="adittional_store_detail_id_'+v+'"><td width="40%" class="adm-detail-content-cell-l">'+'<?php echo GetMessage('parser_addit_store');?>'+t+'&nbsp;['+v+']:</td><td width="60%" class="adm-detail-content-cell-r"><input type="text" value="" name="SETTINGS[detail][addit_stores]['+v+'][value]" size="40" maxlength="250">&nbsp;<a href="#" class="store_delete" data-price-id="'+v+'">Delete</a><input type="hidden" name="SETTINGS[detail][addit_stores]['+v+'][name]" value="'+t+'"></td></tr>';
            
            tr.before(str);
        });
        
        $("#addStore_preview").on("click", function(e){
            e.preventDefault();
            tr = $(this).parents("tr").eq(0);
            
            v = tr.find("select[name=arrAdditStores_preview] option:selected").val();
            t = tr.find("select[name=arrAdditStores_preview] option:selected").text();
            if(v=="" || v=='[]') return false;
            if($("tr.adittional_store_id_"+v).length > 0) return false;
            
            str = '<tr class="adittional_store_preview_id_'+v+'"><td width="40%" class="adm-detail-content-cell-l">'+'<?php echo GetMessage('parser_addit_store');?>'+t+'&nbsp;['+v+']:</td><td width="60%" class="adm-detail-content-cell-r"><input type="text" value="" name="SETTINGS[addit_stores_preview]['+v+'][value]" size="40" maxlength="250">&nbsp;<a href="#" class="store_delete" data-price-id="'+v+'">Delete</a><input type="hidden" name="SETTINGS[addit_stores_preview]['+v+'][name]" value="'+t+'"></td></tr>';
            
            tr.before(str);
        });
        
        $("#addPriceDetail").on("click", function(e){
            e.preventDefault();
            tr = $(this).parents("tr").eq(0);
            
            v = tr.find("select[name=arrPricetypes] option:selected").val();
            t = tr.find("select[name=arrPricetypes] option:selected").text();
            
            if(v=="" || v=='[]') return false;
            if($("tr.adittional_detail_prices_id_"+v).length > 0) return false;
    
            str = '<tr class="adittional_detail_prices_id_'+v+'"><td width="40%" class="adm-detail-content-cell-l">'+'<?php echo GetMessage('parser_preview_price');?>'+t+'&nbsp;['+v+']:</td><td width="60%" class="adm-detail-content-cell-r"><input type="text" value="" name="SETTINGS[prices_detail]['+v+'][value]" size="40" maxlength="250">&nbsp;<a href="#" class="price_delete" data-price-id="'+v+'">Delete</a><input type="hidden" name="SETTINGS[prices_detail]['+v+'][name]" value="'+t+'"></td></tr>';
            tr.before(str);
            
            r = $("tr.adittional_prices_settings").eq(0);
            str='';
            if($(".adittional_prices_settings_id_"+v).length == 0) {
                str = '<tr width="40%" class="adittional_prices_settings_id_'+v+'"><td class="adm-detail-content-cell-l"><?echo GetMessage('parser_aditt_currency');?>'+t+'&nbsp;['+v+']:</td><td width="60%" class="adm-detail-content-cell-r"><?=SelectBoxFromArray('SETTINGS[adittional_currency][currency_id]', $arCurrency, "RUB", "", "");?></td></tr>';
                r.after(str);
                document.getElementById('SETTINGS[adittional_currency][currency_id]').setAttribute("name",'SETTINGS[adittional_currency]['+v+']');
                document.getElementById('SETTINGS[adittional_currency][currency_id]').setAttribute("id",'SETTINGS[adittional_currency]['+v+']');
            }
        });
        
        $("#addPriceOffer").on("click", function(e){
            e.preventDefault();
            tr = $(this).parents("tr").eq(0);
            
            v = tr.find("select[name=arrPricetypes] option:selected").val();
            t = tr.find("select[name=arrPricetypes] option:selected").text();
            
            if(v=="" || v=='[]') return false;
            if($("tr.offer_additional_prices_id_"+v).length > 0) return false;
    
            str = '<tr class="offer_additional_prices_id_'+v+'"><td width="40%" class="adm-detail-content-cell-l">'+'<?php echo GetMessage('parser_preview_price');?>'+t+'&nbsp;['+v+']:</td><td width="60%" class="adm-detail-content-cell-r"><input type="text" value="" name="SETTINGS[offer][selector_additional_prices]['+v+'][value]" size="40" maxlength="250">&nbsp;<a href="#" class="price_delete" data-price-id="'+v+'">Delete</a><input type="hidden" name="SETTINGS[offer][selector_additional_prices]['+v+'][name]" value="'+t+'"></td></tr>';
            tr.before(str);
            
            r = $("tr.adittional_prices_settings").eq(0);
            str='';
            if($(".adittional_prices_settings_id_"+v).length == 0) {
                str = '<tr width="40%" class="adittional_prices_settings_id_'+v+'"><td class="adm-detail-content-cell-l"><?echo GetMessage('parser_aditt_currency');?>'+t+'&nbsp;['+v+']:</td><td width="60%" class="adm-detail-content-cell-r"><?=SelectBoxFromArray('SETTINGS[adittional_currency][currency_id]', $arCurrency, "RUB", "", "");?></td></tr>';
                r.after(str);
                document.getElementById('SETTINGS[adittional_currency][currency_id]').setAttribute("name",'SETTINGS[adittional_currency]['+v+']');
                document.getElementById('SETTINGS[adittional_currency][currency_id]').setAttribute("id",'SETTINGS[adittional_currency]['+v+']');
            }
        });
        
        $("#addNamePriceOffer").on("click", function(e){
            e.preventDefault();
            tr = $(this).parents("tr").eq(0);
            
            v = tr.find("select[name=arrPricetypes] option:selected").val();
            t = tr.find("select[name=arrPricetypes] option:selected").text();
            
            if(v=="" || v=='[]') return false;
            if($("tr.offer_additional_prices_name_id_"+v).length > 0) return false;
    
            str = '<tr class="offer_additional_prices_name_id_'+v+'"><td width="40%" class="adm-detail-content-cell-l">'+'<?php echo GetMessage('parser_preview_price');?>'+t+'&nbsp;['+v+']:</td><td width="60%" class="adm-detail-content-cell-r"><input type="text" value="" name="SETTINGS[offer][find_price]['+v+'][value]" size="40" maxlength="250">&nbsp;<a href="#" class="price_delete" data-price-id="'+v+'">Delete</a><input type="hidden" name="SETTINGS[offer][find_price]['+v+'][name]" value="'+t+'"></td></tr>';
            tr.before(str);
            
            r = $("tr.adittional_prices_settings").eq(0);
            str='';
            if($(".adittional_prices_settings_id_"+v).length == 0) {
                str = '<tr width="40%" class="adittional_prices_settings_id_'+v+'"><td class="adm-detail-content-cell-l"><?echo GetMessage('parser_aditt_currency');?>'+t+'&nbsp;['+v+']:</td><td width="60%" class="adm-detail-content-cell-r"><?=SelectBoxFromArray('SETTINGS[adittional_currency][currency_id]', $arCurrency, "RUB", "", "");?></td></tr>';
                r.after(str);
                document.getElementById('SETTINGS[adittional_currency][currency_id]').setAttribute("name",'SETTINGS[adittional_currency]['+v+']');
                document.getElementById('SETTINGS[adittional_currency][currency_id]').setAttribute("id",'SETTINGS[adittional_currency]['+v+']');
            }
        });

        $("body").on("click", ".price_delete", function(e){
            e.preventDefault();
            tr = $(this).parents("tr").eq(0);
            tr.remove();
            price_id = $(this).attr("data-price-id");
            if($("tr.adittional_detail_prices_id_"+price_id).length == 0 && $("tr.adittional_preview_prices_id_"+price_id).length == 0 && $("tr.offer_additional_prices_id_"+price_id).length == 0&&$("tr.offer_additional_prices_name_id_"+price_id).length == 0){
                $("tr.adittional_prices_settings_id_"+price_id).remove();
            }
        });
        
        $("body").on("click", ".store_delete", function(e){
            e.preventDefault();
            tr = $(this).parents("tr").eq(0);
            tr.remove();
        });
        
        $("body").on("click", ".prev_prop_delete", function(e){
            e.preventDefault();
            tr = $(this).parents("tr").eq(0);
            tr.remove();
        });
        
        $("body").on("click", ".delete_availability_row", function(e){
            e.preventDefault();
            $(this).parent().parent().remove();
        });
        
        $("body").on("click", ".delete_proxy_server", function(e){
            e.preventDefault();
            $(this).parent().parent().remove();
        });
        
        $("body").on("click", "#addProxyServer", function(e){
            e.preventDefault();
            tr = $(this).parent().parent().prev();
            id = tr.attr('data-id');
            if(id==undefined)
                id = 0;
            id++;
            str = '<tr data-id="'+id+'"><td class="adm-detail-content-cell-l"><?php echo GetMessage('parser_proxy');?> '+id+':</td><td class="adm-detail-content-cell-r"><input size="40" type="text" name="SETTINGS[proxy][servers]['+id+'][ip]"> <input placeholder="username:password" size="30" type="text" name="SETTINGS[proxy][servers]['+id+'][username_password]"> <a href="#" class="delete_proxy_server"><?php echo GetMessage('delete');?></a></td></tr>';
            tr.after(str);
        });
        
        $("#addAvailabilityRow").on("click", function(e){
            e.preventDefault();
            tr = $(this).parent().parent().prev();
            count = tr.attr('data-count');
            if(count==undefined)
                count = 0;
            count++;
            str = '<tr data-count="'+count+'"><td width="40%" class="adm-detail-content-cell-l"><?php echo GetMessage('parser_availability_row').':';?></td><td width="60%" class="adm-detail-content-cell-r"><?php echo GetMessage('parser_availability_informer');?> <input type="text" name="SETTINGS[availability][list]['+count+'][text]"><?php echo ' - '.GetMessage('parser_availability_count');?> <input type="text" name="SETTINGS[availability][list]['+count+'][count]"><a href="#" class="delete_availability_row">Delete</a></td></tr>';
            tr.after(str);
        });
        
        $("#loadDopProp").on("click", function(e){
            e.preventDefault();
            tr = $(this).parents("tr").eq(0);
            
            v = tr.find("select[name=arrPropDop] option:selected").val();
            t = tr.find("select[name=arrPropDop] option:selected").text();
            if(v=="") return false;
            else if(v=="[]")
            {
                sotbit_iblock_edit_property("loadDopProp", tr);
                return false;
            }
            str = '<tr class="row_dop_prop"><td width="40%" class="adm-detail-content-cell-l">'+t+'&nbsp;['+v+']:</td><td width="60%" class="adm-detail-content-cell-r"><input type="text" value="" name="SETTINGS[catalog][selector_prop]['+v+']" data-code="'+v+'" size="40">&nbsp;<a href="#" class="prop_delete">Delete</a></td></tr>';
            tr.before(str);
        });
        
        $("#loadFilterProps").on("click", function(e){
            e.preventDefault();
            tr = $(this).parents("tr").eq(0);
            
            v = tr.find("select[name=arrPropsFilter] option:selected").val();
            t = tr.find("select[name=arrPropsFilter] option:selected").text();
            if(v=="") return false;
            else if(v=="[]")
            {
                sotbit_iblock_edit_property("loadFilterProps", tr);
                return false;
            }
            
            selectFilter = '<select name="SETTINGS[props_filter_circs][]['+v+']"><option value="equally"><?=GetMessage('parser_filter_equally');?></option><option value="strpos"><?=GetMessage('parser_filter_strpos');?></option><option value="stripos"><?=GetMessage('parser_filter_stripos');?></option></select>';
            
            str = '<tr class="row_dop_prop"><td width="40%" class="adm-detail-content-cell-l">'+t+'&nbsp;['+v+']:</td><td width="60%" class="adm-detail-content-cell-r">'+selectFilter+'<input type="text" value="" name="SETTINGS[props_filter_value][]['+v+']" data-code="'+v+'" size="40">&nbsp;<a href="#" class="prop_delete">Delete</a></td></tr>';
            tr.before(str);
        });
        
        $("#loadPropDefault").on("click", function(e){
            e.preventDefault();
            tr = $(this).parents("tr").eq(0);
            
            v = tr.find("select[name=arrPropDefault] option:selected").val();
            t = tr.find("select[name=arrPropDefault] option:selected").text();
            if(v=="") return false;
            /*else if(v=="[]")
            {
                sotbit_iblock_edit_property("loadPropDefault", tr);
                return false;
            }*/
            
            jQuery.ajax({
                    url: "",
                    type: "POST",
                    data: 'default=1&ajax=1&prop_id='+v+"&iblock_id="+<?=isset($shs_IBLOCK_ID)?$shs_IBLOCK_ID:0?>,
                    dataType: 'html',
                    success: function(data){
                        str = '<tr><td width="40%" class="adm-detail-content-cell-l">'+t+'&nbsp;['+v+']:</td><td width="60%" class="adm-detail-content-cell-r">'+data+'</td></tr>';
                        tr.before(str);
                    }
            });
        });

          $("body").on("click", ".delete_default_field_hl", function(e){
            e.preventDefault();
            $(this).closest('tr').remove();
        });

        $("#loadFieldDefault").on("click", function(e){
            e.preventDefault();
            tr = $(this).parents("tr").eq(0);
            $exit = false;
            v = tr.find("select[name=arrFieldsDefault] option:selected").val();
            t = tr.find("select[name=arrFieldsDefault] option:selected").text();

            if(v=="")
                return false;

            $('input.arrFieldsDefaultInput').each(function()
            {
                if($(this).val() == v)
                {
                    $exit = true;
                    return false;
                }
            });

            if($exit)
                return false;
            
            str = '<tr>' +
             '<td width="40%" class="adm-detail-content-cell-l">'+t+':</td>' +
              '<td width="60%" class="adm-detail-content-cell-r">' +
              '<input class="arrFieldsDefaultInput" type="hidden" name="SETTINGS[catalog][uniq][fields][]" value="'+v+'">'+
              '<a class="delete_default_field_hl" href="#"><?=GetMessage("parser_caption_detete_button");?></a>'+
               '</td></tr>';
            tr.before(str);
            tr.prev('tr').find('a.delete_default_field_hl').bind("click", function(e){
                        e.preventDefault();
                        $(this).closest('tr').remove();
                    });
        });
        
        $("#loadPropDefaultHL").on("click", function(e){
            e.preventDefault();
            tr = $(this).parents("tr").eq(0);
            
            v = tr.find("select[name=arrPropDefault] option:selected").val();
            t = tr.find("select[name=arrPropDefault] option:selected").text();
            if(v=="") return false;

            if($("tr.default_props_"+v).length > 0) return false;
            
            str = '<tr class="default_props_'+v+'"><td width="40%" class="adm-detail-content-cell-l">'+'<?php echo GetMessage('parser_field');?>'+t+'&nbsp;['+v+']:</td><td width="60%" class="adm-detail-content-cell-r"><input type="text" value="" name="SETTINGS[properties][default]['+v+'][value]" size="40" maxlength="250">&nbsp;<a href="#" class="prev_prop_delete" data-prop-id="'+v+'"><?php echo GetMessage('parser_delete');?></a><input type="hidden" name="SETTINGS[properties][default]['+v+'][name]" value="'+t+'"></td></tr>';
            
            tr.before(str);
            
        });
        
        $("#loadDopPropHL").on("click", function(e){
            e.preventDefault();
            tr = $(this).parents("tr").eq(0);
            
            v = tr.find("select[name=arrPropDop] option:selected").val();
            t = tr.find("select[name=arrPropDop] option:selected").text();
            if(v=="") return false;
            if($("tr.dop_props_id_"+v).length > 0) return false;
            
            str = '<tr class="dop_props_id_'+v+'"><td width="40%" class="adm-detail-content-cell-l">'+'<?php echo GetMessage('parser_field');?>'+t+'&nbsp;['+v+']:</td><td width="60%" class="adm-detail-content-cell-r"><input type="text" value="" name="SETTINGS[catalog][selector_prop]['+v+']" size="40" maxlength="250">&nbsp;<a href="#" class="prev_prop_delete" data-prop-id="'+v+'"><?php echo GetMessage('parser_delete');?></a></td></tr>';
            
            tr.before(str);
            
        });
        
        $("#loadDopPropHL1").on("click", function(e){
            e.preventDefault();
            tr = $(this).parents("tr").eq(0);
            
            v = tr.find("select[name=arrPropDop1] option:selected").val();
            t = tr.find("select[name=arrPropDop1] option:selected").text();
            if(v=="") return false;
            if($("tr.dop_props1_id_"+v).length > 0) return false;
            
            str = '<tr class="dop_props1_id_'+v+'"><td width="40%" class="adm-detail-content-cell-l">'+'<?php echo GetMessage('parser_field');?>'+t+'&nbsp;['+v+']:</td><td width="60%" class="adm-detail-content-cell-r"><input type="text" value="" name="SETTINGS[catalog][find_prop]['+v+']" size="40" maxlength="250">&nbsp;<a href="#" class="prev_prop_delete" data-prop-id="'+v+'"><?php echo GetMessage('parser_delete');?></a></td></tr>';
            
            tr.before(str);
            
        });
        
        $("#loadDopPropHL2").on("click", function(e){
            e.preventDefault();
            tr = $(this).parents("tr").eq(0);
            
            v = tr.find("select[name=arrPropDop2] option:selected").val();
            t = tr.find("select[name=arrPropDop2] option:selected").text();
            if(v=="") return false;
            if($("tr.dop_props2_id_"+v).length > 0) return false;
            
            str = '<tr class="dop_props2_id_'+v+'"><td width="40%" class="adm-detail-content-cell-l">'+'<?php echo GetMessage('parser_field');?>'+t+'&nbsp;['+v+']:</td><td width="60%" class="adm-detail-content-cell-r"><input type="text" value="" name="SETTINGS[catalog][selector_prop_preview]['+v+']" size="40" maxlength="250">&nbsp;<a href="#" class="prev_prop_delete" data-prop-id="'+v+'"><?php echo GetMessage('parser_delete');?></a></td></tr>';
            
            tr.before(str);
            
        });
        
        $("#loadDopPropHL3").on("click", function(e){
            e.preventDefault();
            tr = $(this).parents("tr").eq(0);
            
            v = tr.find("select[name=arrPropDop3] option:selected").val();
            t = tr.find("select[name=arrPropDop3] option:selected").text();
            if(v=="") return false;
            if($("tr.dop_props3_id_"+v).length > 0) return false;
            
            str = '<tr class="dop_props3_id_'+v+'"><td width="40%" class="adm-detail-content-cell-l">'+'<?php echo GetMessage('parser_field');?>'+t+'&nbsp;['+v+']:</td><td width="60%" class="adm-detail-content-cell-r"><input type="text" value="" name="SETTINGS[catalog][find_prop_preview]['+v+']" size="40" maxlength="250">&nbsp;<a href="#" class="prev_prop_delete" data-prop-id="'+v+'"><?php echo GetMessage('parser_delete');?></a></td></tr>';
            
            tr.before(str);
            
        });
        
        $("#loadPropUpdate").on("click", function(e){
            e.preventDefault();
            tr = $(this).parents("tr").eq(0);
            
            v = tr.find("select[name=arrPropUpdate] option:selected").val();
            t = tr.find("select[name=arrPropUpdate] option:selected").text();
            if(v=="") return false;
            if($("tr.update_prop_"+v).length > 0) return false;
            
            str = '<tr class="update_prop_'+v+'"><td width="40%" class="adm-detail-content-cell-l">'+'<?php echo GetMessage('parser_field');?>'+t+':</td><td width="60%" class="adm-detail-content-cell-r"><input type="checkbox" value="Y" checked name="SETTINGS[catalog][update][props]['+v+']">&nbsp;<a href="#" class="prev_prop_delete" data-prop-id="'+v+'"><?php echo GetMessage('parser_delete');?></a></td></tr>';
            
            tr.before(str);
            
        });
        
        $("#loadDopProp2").on("click", function(e){
            e.preventDefault();
            tr = $(this).parents("tr").eq(0);
            
            v = tr.find("select[name=arrPropDop] option:selected").val();
            t = tr.find("select[name=arrPropDop] option:selected").text();
            if(v=="") return false;
            else if(v=="[]")
            {
                sotbit_iblock_edit_property("loadDopProp2", tr);
                return false;
            }
            str = '<tr><td width="40%" class="adm-detail-content-cell-l">'+t+'&nbsp;['+v+']:</td><td width="60%" class="adm-detail-content-cell-r"><input type="text" value="" name="SETTINGS[catalog][selector_prop_preview]['+v+']" data-code="'+v+'" size="40">&nbsp;<a href="#" class="prop_delete">Delete</a></td></tr>';
            tr.before(str);
        });
        
        $("#loadDopProp1").on("click", function(e){
            e.preventDefault();
            tr = $(this).parents("tr").eq(0);
            
            v = tr.find("select[name=arrPropDop1] option:selected").val();
            t = tr.find("select[name=arrPropDop1] option:selected").text();
            if(v=="") return false;
            else if(v=="[]")
            {
                sotbit_iblock_edit_property("loadDopProp1", tr);
                return false;
            }
            str = '<tr><td width="40%" class="adm-detail-content-cell-l">'+t+'&nbsp;['+v+']:</td><td width="60%" class="adm-detail-content-cell-r"><input type="text" value="" name="SETTINGS[catalog][find_prop]['+v+']" data-code="'+v+'" size="40">&nbsp;<a href="#" class="prop_delete">Delete</a></td></tr>';
            tr.before(str);
        });
        
        $("#loadDopProp3").on("click", function(e){
            e.preventDefault();
            tr = $(this).parents("tr").eq(0);
            
            v = tr.find("select[name=arrPropDop1] option:selected").val();
            t = tr.find("select[name=arrPropDop1] option:selected").text();
            if(v=="") return false;
            else if(v=="[]")
            {
                sotbit_iblock_edit_property("loadDopProp3", tr);
                return false;
            }
            str = '<tr><td width="40%" class="adm-detail-content-cell-l">'+t+'&nbsp;['+v+']:</td><td width="60%" class="adm-detail-content-cell-r"><input type="text" value="" name="SETTINGS[catalog][find_prop_preview]['+v+']" data-code="'+v+'" size="40">&nbsp;<a href="#" class="prop_delete">Delete</a></td></tr>';
            tr.before(str);
        });
        
        $("label").on("click", function(e){
            checked = $(this).prev("input[type='checkbox']");
            name = checked.attr("name");
            if(name == "SETTINGS[catalog][update][active]")
            {
                if(checked.is(":checked"))
                {
                    $("tr.show_block_add_element").hide();
                    $("input[name='SETTINGS[catalog][update][add_element]']").removeAttr("checked");
                }
                else
                {
                    $("tr.show_block_add_element").show();
                }
            }
        });
        
        $("#loadPropField").on("click", function(e){
            e.preventDefault();
            tr = $(this).parents("tr").eq(0);
            
            v = tr.find("select[name=arrPropField] option:selected").val();
            t = tr.find("select[name=arrPropField] option:selected").text();
            if(v=="") return false;
            else if(v=="[]")
            {
                sotbit_iblock_edit_property("loadPropField", tr);
                return false;
            }
            //str = '<tr><td width="40%" class="adm-detail-content-cell-l">'+t+'&nbsp;['+v+']:</td><td width="60%" class="adm-detail-content-cell-r"><input type="text" value="" name="SETTINGS[catalog][find_prop_preview]['+v+']" data-code="'+v+'" size="40">&nbsp;<a href="#" class="prop_delete">Delete</a></td></tr>';
            if(v=="SOTBIT_PARSER_NAME_E") str = '<tr><td width="40%" class="adm-detail-content-cell-l">'+t+':</td><td width="60%" class="adm-detail-content-cell-r"><input type="text" value="" name="SETTINGS[catalog][action_props_val]['+v+'][]" data-code="'+v+'" size="40">&nbsp; <select id="SETTINGS[catalog][action_props]['+v+']" name="SETTINGS[catalog][action_props]['+v+'][]"><option value=""><?=GetMessage("shs_parser_select_action_props")?></option><option value="delete"><?=GetMessage("parser_action_props_delete")?></option><option value="add_b"><?=GetMessage("parser_action_props_add_begin")?></option><option value="add_e"><?=GetMessage("parser_action_props_add_end")?></option><option value="lower"><?=GetMessage("parser_action_props_to_lower")?></option></select> <a href="#" class="find_delete">Delete</a></td></tr>';
            else str = '<tr><td width="40%" class="adm-detail-content-cell-l">'+t+'&nbsp;['+v+']:</td><td width="60%" class="adm-detail-content-cell-r"><input type="text" value="" name="SETTINGS[catalog][action_props_val]['+v+'][]" data-code="'+v+'" size="40">&nbsp; <select id="SETTINGS[catalog][action_props]['+v+']" name="SETTINGS[catalog][action_props]['+v+'][]"><option value=""><?=GetMessage("shs_parser_select_action_props")?></option><option value="delete"><?=GetMessage("parser_action_props_delete")?></option><option value="add_b"><?=GetMessage("parser_action_props_add_begin")?></option><option value="add_e"><?=GetMessage("parser_action_props_add_end")?></option><option value="lower"><?=GetMessage("parser_action_props_to_lower")?></option></select> <a href="#" class="find_delete">Delete</a></td></tr>';
            
            tr.before(str);
        });
        
        function sotbit_iblock_edit_property(select_id, tr)
        {
            <?if(isset($shs_IBLOCK_ID) && $shs_IBLOCK_ID):?>addSectionProperty(<?echo $shs_IBLOCK_ID;?>, select_id, tr, 'table_SECTION_PROPERTY')<?endif;?>
        }
        
        function sotbit_iblock_edit_property_offer(select_id, tr)
        {
            <?if(isset($OFFER_IBLOCK_ID) && $OFFER_IBLOCK_ID):?>addSectionProperty(<?echo $OFFER_IBLOCK_ID;?>, select_id, tr, 'table_SECTION_PROPERTY')<?endif;?>
        }
        
        
        $("#instruction").attr("target", "_blank");
        
        $("body").on("click", ".prop_delete", function(e)
        {
            e.preventDefault();
            tr = $(this).parents("tr").eq(0);
            tr.hide();
            tr.find("input").val("");
            v = tr.find("input").attr("data-code");
            prev = $("#delete_selector_prop").val();
            $("#delete_selector_prop").val(prev+","+v);
        });
        
        $("body").on("click", ".dop_rss_delete", function(e){
            e.preventDefault();
            tr = $(this).parents("tr").eq(0);
            tr.html("");
            tr.remove();
            var arrDopRss = $(".admin_tr_rss_dop");
            //remove_element_dop_rss();
        });

        function remove_element_dop_rss()
        {
            numer = 1;
            $(".admin_tr_rss_dop").each(function(){
                td_1 = $(this).children("td").eq(0);
                td_2 = $(this).children("td").eq(1);
                td_1.html("<?echo GetMessage("parser_dop_load_rss");?>" + numer);
                select = td_2.children("select").eq(0);
                input = td_2.children("input").eq(0);
                select.attr("name", "SETTINGS[catalog][section_dop]["+numer+"]");
                input.attr("name", "SETTINGS[catalog][rss_dop]["+numer+"]");
                numer ++;
            });
        }
        
        $("body").on("click", ".id_category_main", function(e){
            e.preventDefault();
            tr = $(this).parents("tr").eq(0);
            tr.html("");
            tr.remove();
            var arrDopRss = $(".admin_tr_rss_dop");
            //remove_element_dop_rss();
        });

        function remove_element_dop_rss()
        {
            numer = 1;
            $(".admin_tr_rss_dop").each(function(){
                td_1 = $(this).children("td").eq(0);
                td_2 = $(this).children("td").eq(1);
                td_1.html("<?echo GetMessage("parser_dop_load_rss");?>" + numer);
                select = td_2.children("select").eq(0);
                input = td_2.children("input").eq(0);
                select.attr("name", "SETTINGS[catalog][section_main]["+numer+"]");
                input.attr("name", "SETTINGS[catalog][id_category_main]["+numer+"]");
                numer ++;
            });
        }
        
        $("body").on("click", ".find_delete", function(e)
        {
            e.preventDefault();
            tr = $(this).parents("tr").eq(0);
            tr.hide();
            tr.find("input").val("");
            v = tr.find("input").attr("data-code");
            prev = $("#delete_find_prop").val();
            $("#delete_find_prop").val(prev+","+v);
        })
        
        $("body").on("click", ".show_prop", function(e){
            e.preventDefault();
            id = $(this).attr("data-name");
            $("#"+id).val("");
            tr = $(this).parents("tr").eq(0);
            if(!tr.is("#header_find_prop"))tr.nextAll().not("#header_find_prop~tr").show();
            else tr.nextAll().show();
        })
        
        $("body").on("click", ".add_usl", function(e){
            e.preventDefault();
            
            tr = $(".tr_add").clone();
            n = parseInt($(".tr_add.heading").attr("data-num"));
            $(".tr_add").removeClass("tr_add");
            $(".tr_last").after(tr);
            $(".tr_last").not(".tr_add").removeClass("tr_last");
            $(this).remove();
            $(".tr_add.heading").attr("data-num", n+1);
            $(".tr_add.heading span").text(n+1);
            $(".tr_add .del_usl").show();
        })
        
        $("body").on("click", "#auth", function(e){
            e.preventDefault();
            url = $(this).attr("data-href");
            jQuery.ajax({
               url: url,
               type: "POST",
               data: "auth=1",
               dataType: 'html',
               success: function(data){
                 $("#shs_message").html(data);
               }
            })
        })
        
        $("body").on("click", ".del_usl", function(e){
            e.preventDefault();
            tr = $(this).parents(".heading").eq(0);
            if(tr.is(".tr_add"))
            {
                tr.prev().addClass("tr_last");
                bool = false;
                tr.prevAll("tr").each(function(){
                    if(!bool) $(this).addClass("tr_add");
                    if($(this).is(".heading") && !bool)
                    {
                        bool = true;
                        attr = parseInt($(this).attr("data-num"));
                        if(attr!=1)$(this).html(tr.html());
                    }
                })
            }
            bool = false;
            tr.nextAll("tr").each(function(){
                if($(this).is(".heading")) bool = true;
                if(!bool) $(this).remove();
            })
            tr.remove();
        })

        jQuery('#iblock').change(function(){
            iblock = jQuery(this).val();
            jQuery.ajax({
               url: "",
               type: "POST",
               data: 'ajax=1&iblock='+iblock,
               dataType: 'html',
               beforeSend: function(){BX.showWait();},
               success: function(data){
                 var ar = new Array();
                 ar = data.split("#SOTBIT#");
                 $('#section').html(ar[0]);
                 add_list_section(ar[0]);
                 BX.closeWait();
               }
            })
        })
        
        function add_list_section(ar_html)
        {
            $(".admin_tr_rss_dop td select").each(function(){
                $(this).html(ar_html);
            });
        }
        
        jQuery('#loadDopRSS').click(function(){
            iblock = jQuery("#iblock").val();
            var el = $(this);
            jQuery.ajax({
               url: "",
               type: "POST",
               data: 'ajax=1&iblock='+iblock,
               dataType: 'html',
               beforeSend: function(){BX.showWait();},
               success: function(data){
                 var ar = new Array();
                 ar = data.split("#SOTBIT#");
                 var count_rss = el.parent().parent().prev(".admin_tr_rss_dop").attr('data-id');//$(".admin_tr_rss_dop").length;
                 if(typeof count_rss == "undefined")
                    count_rss = 0;
                 //var count_rss = $(".admin_tr_rss_dop").length;
                 count_rss = count_rss*1 + 1;
                 var str = '<tr class="admin_tr_rss_dop" data-id="'+count_rss+'"><td class="adm-detail-content-cell-l"><?echo GetMessage("parser_dop_load_rss");?>'+count_rss+'</td><td class="adm-detail-content-cell-r"><input type="text" name="SETTINGS[catalog][rss_dop]['+count_rss+']" value="" size="50" maxlength="500"/><select style="width:262px;" name="SETTINGS[catalog][section_dop]['+count_rss+']">'+ar[0]+'</select><a class="dop_rss_delete" href="#"><?=GetMessage("parser_caption_detete_button");?></a></td></tr>';
                 var element = el.closest("tr");
                 element.before(str);
                 BX.closeWait();
               }
            })
        })
        
        jQuery('#loadMainCategory').click(function(){
            iblock = jQuery("#iblock").val();
            var el = $(this);
            jQuery.ajax({
               url: "",
               type: "POST",
               data: 'ajax=1&iblock='+iblock,
               dataType: 'html',
               beforeSend: function(){BX.showWait();},
               success: function(data){
                 var ar = new Array();
                 ar = data.split("#SOTBIT#");
                 var count_rss = el.parent().parent().prev(".admin_tr_rss_dop").attr('data-id');//$(".admin_tr_rss_dop").length;
                 if(typeof count_rss == "undefined")
                    count_rss = 0;
                 count_rss = count_rss*1 + 1;
                 var str = '<tr class="admin_tr_rss_dop" data-id="'+count_rss+'"><td class="adm-detail-content-cell-l"><?echo GetMessage("parser_id_category_main");?>'+count_rss+'</td><td class="adm-detail-content-cell-r"><input type="text" name="SETTINGS[catalog][id_category_main]['+count_rss+']" value="" size="50" maxlength="500"/><select style="width:262px;" name="SETTINGS[catalog][section_main]['+count_rss+']">'+ar[0]+'</select><a class="id_category_main" href="#"><?=GetMessage("parser_caption_detete_button");?></a></td></tr>';
                 
                 var element = el.closest("tr");
                 element.before(str);
                 BX.closeWait();
               }
            })
        })

        $('.bool-delete').change(function(){
          if($(this).prop('checked')){
            $(this).next().removeAttr('disabled');
            $(this).next().next().removeAttr('disabled');
          }
          else{
            $(this).next().val("");
            $(this).next().attr('disabled', "");

            $(this).next().next().val("");
            $(this).next().next().attr('disabled', "");
          }
        })

        $('.number_img').change(function(){
          if(!$(this).prop('checked')){
            $(this).next().removeAttr('disabled');
            $(this).next().next().removeAttr('disabled');
          }
          else{
            $(this).next().val("");
            $(this).next().attr('disabled', "");
            $(this).next().next().val("");
            $(this).next().next().attr('disabled', "");
          }
        })

        $("#TYPE").change(function(e){
                href = location.href;

                types = href.match(/(\&|\?)(type=)[a-z]+/gi);
                if(types)
                    types.forEach(function(item){
                        href = href.replace(item, '');
                    });

                if(href.indexOf("?") == -1)
                    location.href=href+'?type='+$(this).val();
                else
                    location.href=href+'&type='+$(this).val();

        })

        $("#TYPE_OUT").change(function(e){
                href = location.href;
                location.href=href+'&type_out='+$(this).val();
        })
        
        $(".select_load").change(function(e){
            $("input[name=apply]").click();
        })

        var debug = 0;
        <?if($shsDebug=="debug"):?>
        debug = 1;
        <?endif;?>
        
        function sotbitAjaxStart(href2, start)
        {
            if(start==1)
            {
                href = href2+"&begin=1";
                sotbitStop = 0;
            }
            else href=href2;
            BX.ajax.get(href, "", function(data){
                //prog = 100;
                //$('#progress_text').html(prog + '%');
                //$('#progress_bar_inner').width(500 * prog / 100);
                ////$("#status_bar").hide();
                //$('#progress_text').html(100 + '%');
                if(data!="stop")$("#shs_message").html(data);
                //
                //sotbitStop = 1;
                //var id = $("#btn_stop").data('id');
                //$("#btn_stop").attr("id", id);
                //$("#"+id).text(<?//echo '"'.GetMessage("parser_start").'"'?>//);
            })
        }

         var sotbitStop = 0;
         var href1, href2;

         $("body").on('click', "#btn_start_catalog", function(e) {   //alert("test");
            $(this).data('id', $(this).attr('id'));
            e.preventDefault();
            $(this).attr("id", "btn_stop");
            // $(this).attr("id", "btn_stop_catalog");
            $("#status_bar").show();
            $(this).text(<?echo '"'.GetMessage("btn_stop_catalog").'"'?>);
            href1 = $(this).attr("href")+"&ajax_count=1&type=catalog";
            href2 = $(this).attr("href")+"&ajax_start=1&type=catalog";
            sotbitCountAjax(href1, 0);
            sotbitAjaxStart(href2, 1);
            

            return false;
        })

         //start parsing xml
        $("body").on('click', "#btn_start_xml", function(e) {
            e.preventDefault();
            $(this).data('id', $(this).attr('id'));
            $(this).attr("id", "btn_stop");
            $("#status_bar").show();
            $(this).text(<?echo '"'.GetMessage("btn_stop_catalog").'"'?>);
            href1 = $(this).attr("href")+"&ajax_count=1&type=xml";
            href2 = $(this).attr("href")+"&ajax_start=1&type=xml";
            sotbitCountAjax(href1, 0);
            sotbitAjaxStart(href2, 1);

            return false;

        })
        
        $("body").on('click', "#btn_start_csv", function(e) {
            e.preventDefault();
            $(this).data('id', $(this).attr('id'));
            $(this).attr("id", "btn_stop");
            $("#status_bar").show();
            $(this).text(<?echo '"'.GetMessage("btn_stop_catalog").'"'?>);
            href1 = $(this).attr("href")+"&ajax_count=1&type=csv";
            href2 = $(this).attr("href")+"&ajax_start=1&type=csv";
            sotbitCountAjax(href1, 0);
            sotbitAjaxStart(href2, 1);

            return false;
        })
        
        $("body").on('click', "#btn_start_xls", function(e) {
            e.preventDefault();
            $(this).attr("id", "btn_stop_xls");
            $("#status_bar").show();
            $(this).text(<?echo '"'.GetMessage("btn_stop_catalog").'"'?>);
            href1 = $(this).attr("href")+"&ajax_count=1&type=xls";
            href2 = $(this).attr("href")+"&ajax_start=1&type=xls";
            sotbitCountAjax(href1, 0);
            sotbitAjaxStart(href2, 1);

            return false;
        })
        
        $("body").on('click', "#btn_stop", function(e) {   //alert("test");
            e.preventDefault();
            var href = $(this).attr("href");
            href = window.location;
            BX.ajax.get(href + "&btn_stop=1", "sessid="+BX.bitrix_sessid(), function(data){
            });

            return false;
        })
        
        function sotbitCountAjax(href1, num)
        {
            BX.ajax.post(href1, "sessid="+BX.bitrix_sessid(), function(data){
                arData = data.split("|");
                
                if(sotbitStop!=1)
                {
                    if(arData[1]>0)prog = Math.ceil((arData[1]/arData[0])*100);
                    else prog = 0;
                    $('#progress_text').html(prog + '%');
                    $('#progress_bar_inner').width(500 * prog / 100);
                }
    
                page = arData[2];
                elements = arData[3];
                elementError = arData[4];
                allError = arData[5];
                if(sotbitStop!=1)sotbitStop = parseInt(arData[6]);
                msg = <?echo '"'.GetMessage("parser_load_page").'"'?>+page+<?echo '"'.GetMessage("parser_load_product").'"'?>+elements+<?echo '"<span style=\"color:red\">'.GetMessage("parser_load_product_error").'"'?>+elementError+'</span>'+<?echo '"<span style=\"color:red\">'.GetMessage("parser_all_error").'"'?>+allError+'</span>';
                $("#catalog_bar").html(msg);
                var ArrGet = parseGetParams(href1);
                //var arrTypePars = JSON.parse(ArrGet);
                
                if(ArrGet['type'] == 'xml')
                {
                    if((arData[6] == null) || (arData[6] == 0) || (arData[6] == "") || (arData[6] == false))
                    {
                        arData[6] = 0;
                    }
                    var sec = "<?echo GetMessage('parser_add_all_section');?>"+arData[6]+"</span>";
                    $("#catalog_bar").html($("#catalog_bar").html() + sec);
                }
    
                if(sotbitStop==1)
                {
                    prog = 100;
                    $('#progress_text').html(prog + '%');
                    $('#progress_bar_inner').width(500 * prog / 100);
                    $('#progress_text').html(<?echo '"'.GetMessage("parser_loading_end").'"'?>);
                    
                    var id = $('#btn_stop').data('id');
                    
                    $("#btn_stop").text(<?echo '"'.GetMessage("parser_start").'"'?>);
                    $("#btn_stop").attr("id", id);
                } else {
                    setTimeout(function(){sotbitCountAjax(href1, 0);},2000)
                }
                
                
            })
        }
    })
    
    function parseGetParams(href) {
       var $_GET = {};
       var __GET = href.split("&");
       for(var i=0; i<__GET.length; i++) {
          var getVar = __GET[i].split("=");
          $_GET[getVar[0]] = typeof(getVar[1])=="undefined" ? "" : getVar[1];
       }
       return $_GET;
    }

    BX.ready(function(){

        BX.bind(BX('btn_start'), 'click', function(e) {
        e.preventDefault();
        BX.show(BX('status_bar'));
       
        return false;
    })

    })
</script>
<style>
.adm-info-message table td, .heading .adm-info-message td, .heading .adm-info-message td{
    padding:0!important;
}
.item_row td{
  padding:0!important;
}
</style>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");?>
<?elseif(isset($_REQUEST["ajax_start"]) && isset($_REQUEST["ID"]) && isset($_REQUEST["start"]) && !isset($_REQUEST["ajax_count"])):
    set_time_limit(0);
    require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
    require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/shs.parser/include.php");
    require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/shs.parser/prolog.php");
    IncludeModuleLangFile(__FILE__);

    if(CModule::IncludeModule('iblock') && CModule::IncludeModule('main')):
        $parser = ShsParserContent::GetByID($ID);
        file_put_contents($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/shs.parser/include/parser_start".$ID.".txt", '1');
        if(!$parser->ExtractFields("shs_")) $ID=0;
        $rssParser = new RssContentParser();
        $result = $rssParser->startParser(0);
    endif;
?>
<?elseif(isset($_REQUEST["ID"]) && isset($_REQUEST["start"]) && isset($_REQUEST["ajax_count"])):
    set_time_limit(0);
    $file = $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/shs.parser/include/count_parser".$_REQUEST["ID"].".txt";
    $result = '';
    
    if(isset($_REQUEST["ID"]) && file_exists($file))
    {
        $result = file_get_contents($file);
       
        echo htmlspecialchars($result);
    }
    else
    {
        echo "0|0";
    }


    if($_GET["type"]=="catalog" || $_GET["type"]=="catalog_HL" || $_GET["type"]=="csv" || $_GET["type"]=="xml" || $_GET["type"]=="xls" || $_GET["type"]=="xls_catalo")
    {
        $file1 = $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/shs.parser/include/count_parser_catalog".$_REQUEST["ID"].".txt";
        
        if(isset($_REQUEST["ID"]) && file_exists($file1))
        {
            $count = file_get_contents($file1);
            
            echo htmlspecialchars($count);
        }
        else
        {
            echo "|0|0|0|0";
        }
    }
?>
<?
elseif(isset($_REQUEST["prop_id"])):
    require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
    require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/shs.parser/include.php");
    require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/shs.parser/prolog.php");
    IncludeModuleLangFile(__FILE__);
    CModule::IncludeModule('iblock');
    if(isset($_REQUEST["default"])) $properties = CIBlockProperty::GetList(Array("sort"=>"asc", "name"=>"asc"), Array("ACTIVE"=>"Y", "CODE"=>$_REQUEST["prop_id"], "IBLOCK_ID"=>$_REQUEST["iblock_id"]));
    else $properties = CIBlockProperty::GetList(Array("sort"=>"asc", "name"=>"asc"), Array("ACTIVE"=>"Y", "ID"=>$_REQUEST["prop_id"]));
    while($arProp = $properties->Fetch())
    {   //printr($arProp);
        if(!isset($_REQUEST["default"]))
        {
            echo $arProp["CODE"];
            return false;
        }else{
            $code = $arProp["CODE"];
            if($arProp["PROPERTY_TYPE"]=="L" || $arProp["PROPERTY_TYPE"]=="N" || $arProp["PROPERTY_TYPE"]=="S" || $arProp["PROPERTY_TYPE"]=="E" || $arProp["PROPERTY_TYPE"]=="F")
            {
                $arrPropDop['REFERENCE'][] = $arProp["NAME"];
                $arrPropDop['REFERENCE_ID'][] = $arProp["CODE"];
                $arrPropDop['REFERENCE_TYPE'][$arProp["CODE"]] = $arProp["PROPERTY_TYPE"];
                $arrPropDop['USER_TYPE'][$arProp["CODE"]] = $arProp["USER_TYPE"];
                $arrPropDop['REFERENCE_CODE_NAME'][$arProp["CODE"]] = $arProp["NAME"];
            }
            
            if($arProp["PROPERTY_TYPE"]=="L"/* && $arProp["ID"]==14*/)
            {
                $rsEnum = CIBlockPropertyEnum::GetList(Array("DEF"=>"DESC", "SORT"=>"ASC"), Array("IBLOCK_ID"=>$shs_IBLOCK_ID, "property_id"=>$arProp["ID"]));
                $arrPropDop["LIST_VALUES"][$arProp["CODE"]]["REFERENCE"][] = GetMessage("parser_prop_default");
                $arrPropDop["LIST_VALUES"][$arProp["CODE"]]["REFERENCE_ID"][] = "";
                while($arEnum = $rsEnum->Fetch())
                {
                    $arrPropDop["LIST_VALUES"][$arProp["CODE"]]["REFERENCE"][] = $arEnum["VALUE"];
                    $arrPropDop["LIST_VALUES"][$arProp["CODE"]]["REFERENCE_ID"][] = $arEnum["ID"];
                }
            }
            if($arProp['USER_TYPE']=="directory")
            {
                $nameTable = $arProp["USER_TYPE_SETTINGS"]["TABLE_NAME"];
                $directorySelect = array("*");
                $directoryOrder = array();
                $entityGetList = array(
                    'select' => $directorySelect,
                    'order' => $directoryOrder
                );
                $highBlock = \Bitrix\Highloadblock\HighloadBlockTable::getList(array("filter" => array('TABLE_NAME' => $nameTable)))->fetch();
                $entity = \Bitrix\Highloadblock\HighloadBlockTable::compileEntity($highBlock);
                $entityDataClass = $entity->getDataClass();
                $propEnums = $entityDataClass::getList($entityGetList);
                $arrPropDop["LIST_VALUES"][$arProp["CODE"]]["REFERENCE"][] = GetMessage("parser_prop_default");
                $arrPropDop["LIST_VALUES"][$arProp["CODE"]]["REFERENCE_ID"][] = "";
                while ($oneEnum = $propEnums->fetch())
                {
                    $arrPropDop["LIST_VALUES"][$arProp["CODE"]]["REFERENCE"][] = $oneEnum["UF_NAME"];
                    $arrPropDop["LIST_VALUES"][$arProp["CODE"]]["REFERENCE_ID"][] = $oneEnum["UF_XML_ID"];
                }
            }
                
                ?><?if($arrPropDop['REFERENCE_TYPE'][$code]=="L"):
            ?>
            <?=SelectBoxFromArray('SETTINGS[catalog][default_prop]['.$code.']', $arrPropDop["LIST_VALUES"][$code], "", "", "");?>
            <?elseif($arrPropDop['USER_TYPE'][$code]=="directory"):?>
            <?=SelectBoxFromArray('SETTINGS[catalog][default_prop]['.$code.']', $arrPropDop["LIST_VALUES"][$code], "", "", "");?>
            <?else:?>
            <input type="text" placeholder="<?=GetMessage("parser_prop_default")?>" name="SETTINGS[catalog][default_prop][<?=$code?>]" value="" />
            <?endif?><?
        }
    }
elseif(isset($_REQUEST["iblock"])):
        require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
        require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/shs.parser/include.php");
        require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/shs.parser/prolog.php");
        IncludeModuleLangFile(__FILE__);
    CModule::IncludeModule('iblock');
    $rsSections = CIBlockSection::GetList(array("left_margin"=>"asc"), array(/*'ACTIVE'=>"Y", */"IBLOCK_ID"=>$_REQUEST["iblock"]), false, array('ID', 'NAME', "IBLOCK_ID", "DEPTH_LEVEL"));

    $first = true;
    echo '<option value="">'.GetMessage("parser_section_id").'</option>';
    while($arr=$rsSections->Fetch()){
        $arr["NAME"] = str_repeat(" . ", $arr["DEPTH_LEVEL"]).$arr["NAME"];
        echo '<option value="'.$arr["ID"].'">'.$arr["NAME"].'</option>';
    }
    echo '#SOTBIT#<option value="">'.GetMessage("parser_prop_id").'</option>';
    $properties = CIBlockProperty::GetList(Array("sort"=>"asc", "name"=>"asc"), Array("ACTIVE"=>"Y", "IBLOCK_ID"=>$_REQUEST['iblock'], "PROPERTY_TYPE"=>"S"));
    while($arProp = $properties->Fetch())
    {
        echo '<option value="'.$arProp["CODE"].'">'."[".$arProp["CODE"]."] ".$arProp["NAME"].'</option>';
    }
    echo '#SOTBIT#';
    $properties = CIBlockProperty::GetList(Array("sort"=>"asc", "name"=>"asc"), Array("ACTIVE"=>"Y", "IBLOCK_ID"=>$_REQUEST['iblock']));
    while($arProp = $properties->Fetch())
    {
        if($arProp["PROPERTY_TYPE"]=="S" || $arProp["PROPERTY_TYPE"]=="L" || $arProp["PROPERTY_TYPE"]=="N" ||  $arProp["PROPERTY_TYPE"]=="E")
        {
            $arPropsDop[] = $arProp;
        }

         //echo '<option value="'.$arProp["CODE"].'">'."[".$arProp["CODE"]."] ".$arProp["NAME"].'</option>';
    }
    foreach($arPropsDop as $val)
    {
        echo '<tr><td width="40%" class="adm-detail-content-cell-l">'.$val["NAME"].'&nbsp;['.$val["CODE"].']:</td><td width="60%" class="adm-detail-content-cell-r"><input data-code="'.$val["CODE"].'" size="40" type="text" value="" name="SETTINGS[catalog][selector_prop]['.$val["CODE"].']">&nbsp;<a class="prop_delete" href="#">Delete</a></td></tr>';
    }
    echo '#SOTBIT#';
    foreach($arPropsDop as $val)
    {
        echo '<tr><td width="40%" class="adm-detail-content-cell-l">'.$val["NAME"].'&nbsp;['.$val["CODE"].']:</td><td width="60%" class="adm-detail-content-cell-r"><input data-code="'.$val["CODE"].'" size="40" type="text" value="" name="SETTINGS[catalog][find_prop]['.$val["CODE"].']">&nbsp;<a class="find_delete" href="#">Delete</a></td></tr>';
    }

    echo '#SOTBIT#<option value="">'.GetMessage("parser_prop_id").'</option>';
    $properties = CIBlockProperty::GetList(Array("sort"=>"asc", "name"=>"asc"), Array("ACTIVE"=>"Y", "IBLOCK_ID"=>$_REQUEST['iblock'], "PROPERTY_TYPE"=>"F"));
    while($arProp = $properties->Fetch())
    {
        echo '<option value="'.$arProp["CODE"].'">'."[".$arProp["CODE"]."] ".$arProp["NAME"].'</option>';
    }
elseif(isset($_POST["auth"])):
    set_time_limit(0);
    require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
    require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/shs.parser/include.php");
    require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/shs.parser/prolog.php");
    IncludeModuleLangFile(__FILE__);
    if(CModule::IncludeModule('iblock') && CModule::IncludeModule('main')):
    $parser = ShsParserContent::GetByID($ID);
    if(!$parser->ExtractFields("shs_")) $ID=0;
    $rssParser = new RssContentParser();
    $rssParser->auth(true);
    endif;

endif;
?>