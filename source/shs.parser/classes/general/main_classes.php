<?
/**
 * Copyright (c) 11/9/2020 Created By/Edited By ASDAFF asdaff.asad@yandex.ru
 */

use Bitrix\Main\Entity;
use Bitrix\Main\IO\Path;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Text\Converter;
use Bitrix\Seo\Engine;

\Bitrix\Main\Loader::includeModule('seo');
\Bitrix\Main\Loader::includeModule('socialservices');
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/shs.parser/lib/result_parser.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/shs.parser/lib/result_parser_product.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/shs.parser/classes/general/smart_logs.php");

class SotbitContentParser {
    const TEST = 0;
    const DEFAULT_DEBUG_LIST = 3;
    const DEFAULT_DEBUG_ITEM = 3;
    public $result_id;
    public $id = false;
    public $rss;
    public $typeN;
    public $active;
    public $iblock_id;
    public $section_id;
    public $detail_dom;
    public $encoding;
    public $preview_delete_tag = "";
    public $bool_preview_delete_tag = "";
    public $detail_delete_tag = "";
    public $bool_detail_delete_tag = "";
    public $preview_first_img = "";
    public $detail_first_img = "";
    public $preview_save_img = "";
    public $detail_save_img = "";
    public $text = "";
    public $site = "";
    public $link = "";
    public $preview_delete_element = "";
    public $detail_delete_element = "";
    public $preview_delete_attribute = "";
    public $detail_delete_attribute = "";
    public $index_element = "";
    public $resize_image = "";
    public $meta_description = "";
    public $meta_keywords = "";
    public $meta_description_text = "";
    public $meta_keywords_text = "";
    public $agent = false;
    public $active_element = "Y";
    public $header_url;
    public $settings;
    public $countPage = 0;
    public $countItem = 0;
    public $stepStart = false;
    public $paramIndex = array();
    public $additionalStore = array();
    public $property_filter = true;
    public $page;
    protected $arAdditionalPrice = array();
    
    public function __construct() {
        global $zis, $shs_ID, $shs_TYPE, $shs_TYPE_OUT, $shs_ACTIVE, $shs_IBLOCK_ID, $shs_RSS, $shs_SECTION_ID, $shs_SELECTOR, $shs_ENCODING, $shs_PREVIEW_DELETE_TAG, $shs_PREVIEW_TEXT_TYPE, $shs_DETAIL_TEXT_TYPE, $shs_BOOL_PREVIEW_DELETE_TAG, $shs_PREVIEW_FIRST_IMG, $shs_PREVIEW_SAVE_IMG, $shs_DETAIL_DELETE_TAG, $shs_BOOL_DETAIL_DELETE_TAG, $shs_DETAIL_FIRST_IMG, $shs_DETAIL_SAVE_IMG, $shs_PREVIEW_DELETE_ELEMENT, $shs_DETAIL_DELETE_ELEMENT, $shs_PREVIEW_DELETE_ATTRIBUTE, $shs_DETAIL_DELETE_ATTRIBUTE, $shs_INDEX_ELEMENT, $shs_CODE_ELEMENT, $shs_RESIZE_IMAGE, $shs_META_DESCRIPTION, $shs_META_KEYWORDS, $shs_ACTIVE_ELEMENT, $shs_FIRST_TITLE, $shs_DATE_PUBLIC, $shs_FIRST_URL, $shs_DATE_ACTIVE, $shs_META_TITLE, $shs_SETTINGS, $shs_TMP;
        $this->id = $shs_ID;
        $this->typeN = $shs_TYPE;
        $this->type_out = $shs_TYPE_OUT;
        $this->rss = $shs_RSS;
        $this->active = $shs_ACTIVE;
        $this->iblock_id = $shs_IBLOCK_ID;
        $this->section_id = $shs_SECTION_ID;
        $this->detail_dom = htmlspecialchars_decode($shs_SELECTOR);
        $this->first_url = trim($shs_FIRST_URL);
        $this->encoding = $shs_ENCODING;
        $this->preview_text_type = $shs_PREVIEW_TEXT_TYPE;
        $this->detail_text_type = $shs_DETAIL_TEXT_TYPE;
        $this->preview_delete_tag = $shs_PREVIEW_DELETE_TAG;
        $this->detail_delete_tag = $shs_DETAIL_DELETE_TAG;
        $this->bool_preview_delete_tag = $shs_BOOL_PREVIEW_DELETE_TAG;
        $this->bool_detail_delete_tag = $shs_BOOL_DETAIL_DELETE_TAG;
        $this->preview_first_img = $shs_PREVIEW_FIRST_IMG;
        $this->detail_first_img = $shs_DETAIL_FIRST_IMG;
        $this->preview_save_img = $shs_PREVIEW_SAVE_IMG;
        $this->detail_save_img = $shs_DETAIL_SAVE_IMG;
        $this->preview_delete_element = $shs_PREVIEW_DELETE_ELEMENT;
        $this->detail_delete_element = $shs_DETAIL_DELETE_ELEMENT;
        $this->preview_delete_attribute = $shs_PREVIEW_DELETE_ATTRIBUTE;
        $this->detail_delete_attribute = $shs_DETAIL_DELETE_ATTRIBUTE;
        $this->index_element = ($shs_INDEX_ELEMENT == "Y") ? true : false;
        $this->code_element = $shs_CODE_ELEMENT;
        $this->resize_image = ($shs_RESIZE_IMAGE == "Y") ? true : false;
        $this->meta_title = $shs_META_TITLE;
        $this->meta_description = $shs_META_DESCRIPTION;
        $this->meta_keywords = $shs_META_KEYWORDS;
        $this->active_element = $shs_ACTIVE_ELEMENT;
        $this->first_title = $shs_FIRST_TITLE;
        $this->date_public = $shs_DATE_PUBLIC;
        $this->date_active = $shs_DATE_ACTIVE;
        $this->tmp = $shs_TMP;
        $this->settings = (is_array($shs_SETTINGS)) ? $shs_SETTINGS : $this->sotbitParserDecoderSettings(unserialize(base64_decode($shs_SETTINGS)));
        $this->header_url = "";
        $this->sleep = (int)$this->settings[$this->typeN]["sleep"];
        $pr = is_array($this->settings["proxy"]['servers']) ? $this->settings["proxy"]['servers'] : array();
        if($this->settings[$this->typeN]["proxy"] != '')
            $proxy = array(
                array(
                    'ip' => $this->settings[$this->typeN]["proxy"],
                    'username_password' => $this->settings["proxy"]['username_password']
                )
            );
        else
            $proxy = array();
        $this->proxy = array_merge($proxy, $pr);
        $this->errors = array();
        $this->auth = $this->settings[$this->typeN]["auth"]["active"] ? true : false;
        $this->currentPage = 0;
        $this->activeCurrentPage = 0;
        $this->debugErrors = array();
        $this->stepStart = false;
        $this->pagePrevElement = array();
        $this->pagenavigationPrev = array();
        $this->pagenavigation = array();
        if(strtoupper($this->encoding) == 'UTF8') 
        {
        $this->encoding == 'UTF-8';
        $this->fix_utf8 = '<meta http-equiv="content-type" content="text/html; charset=utf-8" />';
        }
    }
    
    private function sotbitParserDecoderSettings($SETTINGS) {
        if(mb_strtolower(SITE_CHARSET) != "windows-1251")
            return $SETTINGS;
        foreach($SETTINGS as &$v) {
            if(is_array($v))
                $v = $this->sotbitParserDecoderSettings($v);
            else
                $v = html_entity_decode(htmlspecialcharsBack($v), ENT_QUOTES, SITE_CHARSET);
        }
        
        return $SETTINGS;
    }
    
    public function startParser($agent = false) {
        global $DB;
        $this->createFolder();
        $this->createAlbum();
        if($this->active != "Y") {
            $result["ERROR"][] = GetMessage("parser_active_no");
            $this->errors[] = GetMessage("parser_active_no");
            if(!$agent)
                CAdminMessage::ShowMessage(GetMessage("parser_active_no"));
            
            return $result;
        }
        $this->checkSettings();

        foreach (glob($_SERVER["DOCUMENT_ROOT"].BX_ROOT.'/modules/shs.parser/include/*'.$this->id.'.txt') as $file) 
        if (!strpos($file,'_log_')) unlink($file);
        
        $parser = new ShsParserContent();
        $now = time() + CTimeZone::GetOffset();
        $arFieldsTime['START_LAST_TIME_X'] = date($DB->DateFormatToPHP(FORMAT_DATETIME), $now);
        $parser->Update($this->id, $arFieldsTime);
        unset($parser, $now, $arFieldsTime);
        $this->convetCyrillic($this->rss);
        if($this->type_out != "HL") {
            if($this->meta_description != "N") {
                $propDescr = CIBlockProperty::GetList(Array(
                    "sort" => "asc",
                    "name" => "asc"
                ), Array(
                    "ACTIVE" => "Y",
                    "IBLOCK_ID" => $this->iblock_id,
                    "CODE" => $this->meta_description
                ))->Fetch();
                if(!$propDescr) {
                    $result["ERROR"][] = GetMessage("parser_error_description");
                    $this->errors[] = GetMessage("parser_error_description");
                }
                unset($propDescr);
            }
            if($this->meta_keywords != "N") {
                $propKey = CIBlockProperty::GetList(Array(
                    "sort" => "asc",
                    "name" => "asc"
                ), Array(
                    "ACTIVE" => "Y",
                    "IBLOCK_ID" => $this->iblock_id,
                    "CODE" => $this->meta_keywords
                ))->Fetch();
                if(!$propKey) {
                    $result["ERROR"][] = GetMessage("parser_error_keywords");
                    $this->errors[] = GetMessage("parser_error_keywords");
                }
                unset($propKey);
            }
            if($this->meta_title != "N") {
                $propKey = CIBlockProperty::GetList(Array(
                    "sort" => "asc",
                    "name" => "asc"
                ), Array(
                    "ACTIVE" => "Y",
                    "IBLOCK_ID" => $this->iblock_id,
                    "CODE" => $this->meta_title
                ))->Fetch();
                if(!$propKey) {
                    $result["ERROR"][] = GetMessage("parser_error_title");
                    $this->errors[] = GetMessage("parser_error_title");
                }
                unset($propKey);
            }
            if($this->first_title != "N") {
                $propFirst = CIBlockProperty::GetList(Array(
                    "sort" => "asc",
                    "name" => "asc"
                ), Array(
                    "ACTIVE" => "Y",
                    "IBLOCK_ID" => $this->iblock_id,
                    "CODE" => $this->first_title
                ))->Fetch();
                if(!$propFirst) {
                    $result["ERROR"][] = GetMessage("parser_error_first");
                    $this->errors[] = GetMessage("parser_error_first");
                }
                unset($propFirst);
            }
            if($this->date_public != "N") {
                $propDate = CIBlockProperty::GetList(Array(
                    "sort" => "asc",
                    "name" => "asc"
                ), Array(
                    "ACTIVE" => "Y",
                    "IBLOCK_ID" => $this->iblock_id,
                    "CODE" => $this->date_public
                ))->Fetch();
                if(!$propDate) {
                    $result["ERROR"][] = GetMessage("parser_error_date");
                    $this->errors[] = GetMessage("parser_error_date");
                }
                unset($propDate);
            }
            if(($this->type == 'xls_catalo' || $this->type == 'xml_catalo') && !$this->settings['parser_id_detail'])
                $this->errors[] = GetMessage("parser_error_detail_page");
        }
        if(isset($result['ERROR']) && !$agent) {
            foreach($result['ERROR'] as $error)
                CAdminMessage::ShowMessage($error);
            
            return false;
        }
        $this->agent = $agent;

        if($_GET["begin"]) {
            $this->auth(true);
            foreach(GetModuleEvents("shs.parser", "startPars", true) as $arEvent)
                ExecuteModuleEventEx($arEvent, array(
                    $this->id,
                    &$this
                ));
        }
        else if($this->agent || $this->settings["catalog"]["mode"] == "debug") {
            $this->auth(true);
            foreach(GetModuleEvents("shs.parser", "startPars", true) as $arEvent)
                ExecuteModuleEventEx($arEvent, array(
                    $this->id,
                    &$this
                ));
        }
        if($this->settings['smart_log']['enabled'] == 'Y' && $_GET["begin"]) {
            $this->settings['smart_log']['result_id'] = \Bitrix\Shs\ParserResultTable::saveParserResult($this->id, $this->settings["smart_log"]["settings"], intval($this->settings["smart_log"]["iteration"]) != 0 ? intval($this->settings["smart_log"]["iteration"]) : 1);
            file_put_contents($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/shs.parser/include/result_id".$this->id.".txt", $this->settings['smart_log']['result_id']);
        }
        
        try {
            if($this->typeN == "catalog") {
                if($this->type_out != 'HL') {
                    $this->isCatalog();
                    $this->getUniqElement();
                    $this->isUpdateElement();
                    $this->GetSortFields();
                    $this->getArrayIblock();
                    $this->DoPageNavigation();
                    $this->CheckFields($this->settings["catalog"]);
                    if(!$this->errors) {
                        $this->parseCatalog();
                    }
                    else {
                        if(!$agent)
                            foreach($this->errors as $error)
                                CAdminMessage::ShowMessage($error);
                        $this->SaveLog();
                        if(isset($this->settings['smart_log']['result_id']) && $this->settings['smart_log']['result_id'] > 0)
                            \Bitrix\Shs\ParserResultTable::updateStatus($this->settings['smart_log']['result_id'], -1);
                
                        return false;
                    }
                    if($this->debugErrors && $this->settings["catalog"]["mode"] == "debug") {
                        if(!$agent && is_array($this->errors)) {
                            foreach($this->errors as $error)
                                CAdminMessage::ShowMessage($error);
                        }
                        
                        $this->SaveLog();
                        if(isset($this->settings['smart_log']['result_id']) && $this->settings['smart_log']['result_id'] > 0)
                            \Bitrix\Shs\ParserResultTable::updateStatus($this->settings['smart_log']['result_id'], -1);
                    }
                }
                else if($this->type_out == 'HL') {
                    $this->getUniqElementHL();
                    $this->isUpdateElementHL();
                    $this->GetSortFieldsHL();
                    $this->getArrayHLblockHL();
                    $this->DoPageNavigation();
                    $this->CheckFieldsHL($this->settings["catalog"]);
                    if(!$this->errors)
                        $this->parseCatalog();
                    else {
                        if(!$agent)
                            foreach($this->errors as $error)
                                CAdminMessage::ShowMessage($error);
                        $this->SaveLog();
                
                        return false;
                    }
                    if($this->debugErrors && $this->settings["catalog"]["mode"] == "debug") {
                        if(!$agent)
                            foreach($this->debugErrors as $error)
                                CAdminMessage::ShowMessage($error);
                        $this->SaveLog();
                
                        return false;
                    }
                }
            }
            if($this->typeN == "xml") {
                if($this->type_out != 'HL') {
                    $this->isCatalog(); 
                    $this->getUniqElement(); 
                    $this->isUpdateElement(); 
                    $this->GetSortFields();
                    $this->getArrayIblock();
                    $this->CheckFields($this->settings["catalog"]);
                  
                    if(!$this->errors)
                        $this->parseXmlCatalog();
                    else {
                        if(!$agent)
                            foreach($this->errors as $error)
                                CAdminMessage::ShowMessage($error);
                        $this->SaveLog();
                        \Bitrix\Shs\ParserResultTable::updateStatus($this->settings['smart_log']['result_id'], -1);
                
                        return false;
                    }
                    if($this->debugErrors && $this->settings["catalog"]["mode"] == "debug") {
                        if(!$agent)
                            foreach($this->debugErrors as $error)
                                CAdminMessage::ShowMessage($error);
                        $this->SaveLog();
                
                        return false;
                    }
                }
                else if($this->type_out == 'HL') {
                    if(!CModule::IncludeModule('highloadblock')) {
                        CAdminMessage::ShowMessage(GetMessage('parser_highloadblock_not_exists'));
                
                        return false;
                    }
                    $hlblock = Bitrix\Highloadblock\HighloadBlockTable::getById($this->iblock_id)->fetch();
                    if(!$hlblock) {
                        CAdminMessage::ShowMessage(GetMessage('parser_highloadblock_not_exists_id').' ['.$this->iblock_id.']');
                
                        return false;
                    }
                    $entity = Bitrix\Highloadblock\HighloadBlockTable::compileEntity($hlblock);
                    $this->highload_class = $entity->getDataClass();
                    $this->prepareRss();
                    if(!$this->errors) {
                        try {
                            $this->parseXmlCatalog();
                        }
                        catch(\InvalidArgumentException  $e) {
                            CAdminMessage::ShowMessage($e->getMessage());
                        }
                        catch(\Exception $e) {
                            CAdminMessage::ShowMessage($e->getMessage());
                        }
                    }
                    else {
                        if(!$agent)
                            foreach($this->errors as $error)
                                CAdminMessage::ShowMessage($error);
                        $this->SaveLog();
                        if(isset($this->settings['smart_log']['result_id']) && !empty($this->settings['smart_log']['result_id']))
                            \Bitrix\Shs\ParserResultTable::updateStatus($this->settings['smart_log']['result_id'], -1);
                
                        return false;
                    }
                    if(!$agent)
                        foreach($this->errors as $error)
                            CAdminMessage::ShowMessage($error);
                    if($this->debugErrors && $this->settings["catalog"]["mode"] == "debug") {
                        if(!$agent)
                            foreach($this->debugErrors as $error)
                                CAdminMessage::ShowMessage($error);
                        $this->SaveLog();
                
                        return false;
                    }
                }
            }
            if($this->typeN == "csv") {
                $this->isCatalog(); 
                $this->getUniqElementXls(); 
                $this->isUpdateElement(); 
                $this->GetSortFields(); 
                $this->getArrayIblock(); 
                $this->CheckFields($this->settings["catalog"]);
                if(!$this->errors)
                    $this->parseCsvCatalog();
                else {
                    if(!$agent)
                        foreach($this->errors as $error)
                            CAdminMessage::ShowMessage($error);
                    $this->SaveLog();
                    \Bitrix\Shs\ParserResultTable::updateStatus($this->settings['smart_log']['result_id'], -1);
                }
                if($this->debugErrors && $this->settings["catalog"]["mode"] == "debug") {
                    if(!$agent)
                        foreach($this->debugErrors as $error)
                            CAdminMessage::ShowMessage($error);
                    $this->SaveLog();
                }
    
                $this->stopParser();
            }
            if($this->typeN == "xls") {
                $this->isCatalog();
                $this->getUniqElementXls();
                $this->isUpdateElement();
                $this->GetSortFields();
                $this->getArrayIblock(); 
                $this->CheckFields($this->settings["catalog"]); 
                if(!$this->errors) {
                    $this->parseXlsCatalog();
                }
                else {
                    if(!$agent)
                        foreach($this->errors as $error)
                            CAdminMessage::ShowMessage($error);
                    $this->SaveLog();
                    \Bitrix\Shs\ParserResultTable::updateStatus($this->settings['smart_log']['result_id'], -1);
            
                    return false;
                }
                if($this->debugErrors && $this->settings["catalog"]["mode"] == "debug") {
                    if(!$agent)
                        foreach($this->debugErrors as $error)
                            CAdminMessage::ShowMessage($error);
                    $this->SaveLog();
            
                    return false;
                }
        
                return true;
            }
            if($this->typeN == "xls_catalo") {
                $this->isCatalogXlsCatalog(); 
                $this->getUniqElementXls(); 
                $this->isUpdateElement(); 
                $this->GetSortFields(); 
                $this->getArrayIblock(); 
                $this->CheckFieldsXlsCatalog($this->settings["catalog"]);
                if(!$this->errors)
                    $this->parseXlsCatalogCatalog();
                else {
                    if(!$agent)
                        foreach($this->errors as $error)
                            CAdminMessage::ShowMessage($error);
                    $this->SaveLog();
                    \Bitrix\Shs\ParserResultTable::updateStatus($this->settings['smart_log']['result_id'], -1);
            
                    return false;
                }
                if($this->debugErrors && $this->settings["catalog"]["mode"] == "debug") {
                    if(!$agent)
                        foreach($this->debugErrors as $error)
                            CAdminMessage::ShowMessage($error);
                    $this->SaveLog();
            
                    return false;
                }
        
                return true;
            }
            if($this->typeN == "xml_catalo") {
                $this->isCatalogXlsCatalog(); 
                $this->getUniqElement(); 
                $this->isUpdateElement(); 
                $this->GetSortFields(); 
                $this->getArrayIblock(); 
                $this->CheckFieldsXlsCatalog($this->settings["catalog"]); 
                if(!$this->errors)
                    $this->parseXmlCatalog();
                else {
                    if(!$agent)
                        foreach($this->errors as $error)
                            CAdminMessage::ShowMessage($error);
                    $this->SaveLog();
                    \Bitrix\Shs\ParserResultTable::updateStatus($this->settings['smart_log']['result_id'], -1);
            
                    return false;
                }
                if($this->debugErrors && $this->settings["catalog"]["mode"] == "debug") {
                    if(!$agent)
                        foreach($this->debugErrors as $error)
                            CAdminMessage::ShowMessage($error);
                    $this->SaveLog();
            
                    return false;
                }
        
                return true;
            }
        }
        catch(RuntimeException $e)
        {
            echo $e->getMessage();
            $this->stopParser();
        }
            
        $this->stopParser();
        
        return;
    }
    
    protected function stopParser() {
        foreach(GetModuleEvents("shs.parser", "EndPars", true) as $arEvent)
            ExecuteModuleEventEx($arEvent, array($this->id));
        
        if(file_exists($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/shs.parser/include/stop_parser_".$this->id.".txt")) {
            unlink($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/shs.parser/include/stop_parser_".$this->id.".txt");
        }
    
        if(file_exists($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/shs.parser/include/parser_start".$this->id.".txt")) {
            unlink($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/shs.parser/include/parser_start".$this->id.".txt");
        }

        file_put_contents($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/shs.parser/include/count_parser_catalog".$this->id.".txt", 1, FILE_APPEND);
        
        $this->clearFields();
    }
    
    protected function createAlbum() {
        CModule::IncludeModule("fileman");
        CMedialib::Init();
        $collection = CMedialibCollection::GetList(array(
            'arOrder' => Array('ID' => 'ASC'),
            'arFilter' => array(
                'ACTIVE' => 'Y',
                "ID" => $this->settings['madialibrary_id']
            )
        ));
        if($this->settings['madialibrary_id'] != '' && is_array($collection) && !empty($collection))
            $this->albumID = $this->settings['madialibrary_id'];
        else {
            $arCollections = CMedialibCollection::GetList(array(
                'arOrder' => Array('ID' => 'ASC'),
                'arFilter' => array(
                    'ACTIVE' => 'Y',
                    "NAME" => "SOTBIT_PARSER"
                )
            ));
            if(!$arCollections)
                $this->albumID = CMedialibCollection::Edit(array(
                    "arFields" => array(
                        "NAME" => "SOTBIT_PARSER",
                        "ML_TYPE" => "1"
                    )
                ));
            else
                $this->albumID = $arCollections[0]["ID"];
        }
    }
    
    /**
     * check same neсessary settings
     * if same checks fail - add error to $this->errors
     *
     * */
    protected function checkSettings() {
        if($this->settings["catalog"]["section_main_filter"] == "Y" && empty($this->settings["catalog"]["section_main"]))
            $this->errors[] = GetMessage("parser_error_count_matches_categories");
    }
    
    protected function convetCyrillic(&$url) {
        if(preg_match("/^\/{2}www/", $url))
            $url = preg_replace("/^\/{2}www/", "www", $url);
    }
    
    protected function isCatalog() {
        $this->isOfferCatalog = false;
        $this->isOfferParsing = false;
        $this->iblockOffer = 0;
        if(CModule::IncludeModule('catalog') && ($this->iblock_id && CCatalog::GetList(Array("name" => "asc"), Array(
                    "ACTIVE" => "Y",
                    "ID" => $this->iblock_id
                ))->Fetch())) {
            if((isset($this->settings["catalog"]["preview_price"]) && $this->settings["catalog"]["preview_price"] !== '') || (isset($this->settings["catalog"]["preview_count"]) && $this->settings["catalog"]["preview_count"] !== "") || (isset($this->settings["catalog"]["detail_price"]) && $this->settings["catalog"]["detail_price"] !== '') || (isset($this->settings["catalog"]["detail_count"]) && $this->settings["catalog"]["detail_count"] !== "") || (isset($this->settings["catalog"]["count_default"]) && $this->settings["catalog"]["count_default"] !== "") || (isset($this->settings["prices_detail"]) && !empty($this->settings["prices_detail"]))) {
                $this->isCatalog = true;
            }
            else
                $this->isCatalog = false;
        }
        else
            $this->isCatalog = false;
        if(CModule::IncludeModule('catalog') && isset($this->settings["catalog"]["cat_vat_price_offer"]) && $this->settings["catalog"]["cat_vat_price_offer"] == "Y") {
            $arIblock = CCatalogSKU::GetInfoByIBlock($this->iblock_id);
            if(is_array($arIblock) && !empty($arIblock) && $arIblock["PRODUCT_IBLOCK_ID"] != 0 && $arIblock["SKU_PROPERTY_ID"] != 0) {
                $this->isOfferCatalog = true;
                $this->offerArray = $arIblock;
                $this->isCatalog = true;
            }
            else
                $this->isOfferCatalog = false;
        }
        if(CModule::IncludeModule('catalog') && isset($this->settings["offer"]["load"]) && $this->settings["offer"]["load"]) {
            if(!isset($this->settings["catalog"]["cat_vat_price_offer"]) || isset($this->settings["catalog"]["cat_vat_price_offer"]) && $this->settings["catalog"]["cat_vat_price_offer"] != "Y")
                $arIblock = CCatalogSKU::GetInfoByIBlock($this->iblock_id);
            if(is_array($arIblock) && !empty($arIblock) && $arIblock["PRODUCT_IBLOCK_ID"] != 0 && $arIblock["SKU_PROPERTY_ID"] != 0 && $arIblock["IBLOCK_ID"]) {
                $this->offerArray = $arIblock;
                $this->isCatalog = true;
                $this->isOfferParsing = true;
                if($arIblock["IBLOCK_ID"] && $arIblock["PRODUCT_IBLOCK_ID"])
                    $this->iblockOffer = $arIblock["IBLOCK_ID"];
            }
            else
                $this->isOfferParsing = false;
        }
        if(isset($arIblock))
            unset($arIblock);
    }
    
    protected function getUniqElement() {
        $this->uniqFields["NAME"] = "NAME";
        $this->uniqFields["LINK"] = "LINK";
        if($this->settings["catalog"]["uniq"]["prop"]) {
            unset($this->uniqFields["LINK"]);
            unset($this->uniqFields["NAME"]);
            $prop = $this->settings["catalog"]["uniq"]["prop"];
            $this->uniqFields[$prop] = $prop;
            unset($prop);
        }
        if($this->settings["catalog"]["uniq"]["name"]) {
            unset($this->uniqFields["LINK"]);
            $this->uniqFields["NAME"] = "NAME";
        }
    }
    
    protected function isUpdateElement() {
        $this->updateActive = false;
        if($this->settings["catalog"]["update"]["active"]) {
            unset($this->settings["catalog"]["update"]["active"]);
            $this->updateActive = true;
            foreach($this->settings["catalog"]["update"] as $id => $val)
                if($val == "Y" || $val == "empty")
                    $this->isUpdate[$id] = $val;
            if(!isset($this->isUpdate) || !$this->isUpdate)
                $this->isUpdate = false;
        }
        else
            $this->isUpdate = false;
    }
    
    protected function GetSortFields() {
        $this->arSortUpdate = array();
        $this->arEmptyUpdate = array();
        if($this->isUpdate)
            foreach($this->isUpdate as $id => $val) {
                if($val != "empty")
                    continue;
                if($id == "preview_img")
                    $this->arSortUpdate[] = "PREVIEW_PICTURE";
                else if($id == "detail_img")
                    $this->arSortUpdate[] = "DETAIL_PICTURE";
                else if($id == "preview_descr")
                    $this->arSortUpdate[] = "PREVIEW_TEXT";
                else if($id == "detail_descr")
                    $this->arSortUpdate[] = "DETAIL_TEXT";
            }
    }
    
    protected function getArrayIblock() {
        $this->arrayIblock = CIBlock::GetArrayByID($this->iblock_id);;
    }
    
    protected function DoPageNavigation() {
        $this->arPageNavigationDelta[0] = $this->settings["catalog"]["pagenavigation_begin"];
        $this->arPageNavigationDelta[1] = $this->settings["catalog"]["pagenavigation_end"];
    }
    
    protected function CheckFields($settings) {
        if(preg_match("/\D/", $settings["pagenavigation_begin"]) && $settings["pagenavigation_begin"] != "")
            $this->errors[] = GetMessage("parser_error_pagenavigation_begin");
        if(preg_match("/\D/", $settings["pagenavigation_end"]) && $settings["pagenavigation_end"] != "")
            $this->errors[] = GetMessage("parser_error_pagenavigation_end");
        if(preg_match("/\D/", $settings["step"]))
            $this->errors[] = GetMessage("parser_error_step");
        if(is_array($settings["price_updown"]))
            foreach($settings["price_updown"] as $i => $val) {
                if($settings["price_updown"][$i]) {
                    if($settings["price_terms"][$i] && !self::isFloat($settings["price_terms_value"][$i]))
                        $this->errors[] = GetMessage("parser_error_price_terms_value");
                    if($settings["price_terms"][$i] && !self::isFloat($settings["price_terms_value_to"][$i]))
                        $this->errors[] = GetMessage("parser_error_price_terms_value");
                    if($settings["price_updown"][$i] && !self::isFloat($settings["price_value"][$i]))
                        $this->errors[] = GetMessage("parser_error_price_value");
                }
            }
        $properties = CIBlockProperty::GetList(Array(
            "sort" => "asc",
            "name" => "asc"
        ), Array(
            "ACTIVE" => "Y",
            "IBLOCK_ID" => $this->iblock_id
        ));
        while($prop_fields = $properties->GetNext()) {
            $this->arProperties[$prop_fields["CODE"]] = $prop_fields;
        }
        if($this->iblockOffer) {
            $properties = CIBlockProperty::GetList(Array(
                "sort" => "asc",
                "name" => "asc"
            ), Array(
                "ACTIVE" => "Y",
                "IBLOCK_ID" => $this->iblockOffer
            ));
            while($prop_fields = $properties->GetNext()) {
                $this->arPropertiesOffer[$prop_fields["CODE"]] = $prop_fields;
            }
        }
        unset($properties);
        $this->arSelectorProduct = $this->getSelectorProduct();
        $this->arFindProduct = $this->getFindProduct();
        $this->arSelectorProperties = $this->getSelectorProperties();
        $this->arSelectorPropertiesOffer = $this->getSelectorPropertiesOffer();
        $this->arFindProperties = $this->getFindProperties();
        $this->arFindPropertiesOffer = $this->getFindPropertiesOffer();
        $this->arDubleFindProperties = $this->getFindDubleProperties();
        $this->arDubleFindPropertiesOffer = $this->getFindDublePropertiesOffer();
        $this->arSelectorPropertiesPreview = $this->getSelectorPropertiesPreview();
        $this->arFindPropertiesPreview = $this->getFindPropertiesPreview();
        $this->arDubleFindPropertiesPreview = $this->getFindDublePropertiesPreview();
    }
    
    protected function isFloat($n) {
        if(preg_match("/^(?:\+|\-)?(?:(?:\d+)|(?:\d+\.)|(?:\.\d+)|(?:\d+\.\d+)){1}(?:e(?:\+|\-)?\d+)?$/i", $n))
            return true;
        else
            return false;
    }
    
    public function getSelectorProduct() {
        if(isset($this->settings["catalog"]["selector_product"]) && !empty($this->settings["catalog"]["selector_product"]))
            foreach($this->settings["catalog"]["selector_product"] as $i => $prop) {
                $prop = trim($prop);
                if(!empty($prop)) {
                    $arProps[$i] = $prop;
                }
            }
        if(!$arProps)
            return false;
        
        return $arProps;
    }
    
    public function getFindProduct() {
        if(isset($this->settings["catalog"]["find_product"]) && !empty($this->settings["catalog"]["find_product"]))
            foreach($this->settings["catalog"]["find_product"] as $i => $prop) {
                $prop = trim($prop);
                if(!empty($prop))
                    $arProps[$i] = $prop;
            }
        if(!$arProps)
            return false;
        
        return $arProps;
    }
    
    protected function getSelectorProperties() {
        if(isset($this->settings["catalog"]["selector_prop"]) && !empty($this->settings["catalog"]["selector_prop"])) {
            $arProps = false;
            foreach($this->settings["catalog"]["selector_prop"] as $i => $prop) {
                $prop = trim($prop);
                if($prop != '')
                    $arProps[$i] = $prop;
            }
            unset($i, $prop);
            if(!$arProps)
                return false;
            
            return $arProps;
        }
        
        return false;
    }
    
    protected function getSelectorPropertiesOffer() {
        if(isset($this->settings["offer"]["selector_prop"]) && !empty($this->settings["offer"]["selector_prop"])) {
            $arProps = false;
            foreach($this->settings["offer"]["selector_prop"] as $i => $prop) {
                $prop = trim($prop);
                if(!empty($prop))
                    $arProps[$i] = $prop;
            }
            unset($i, $prop);
            if(!$arProps)
                return false;
            
            return $arProps;
        }
        else if(isset($this->settings["offer"]["selector_prop_more"]) && !empty($this->settings["offer"]["selector_prop_more"])) {
            $arProps = false;
            foreach($this->settings["offer"]["selector_prop_more"] as $i => $prop) {
                $prop = trim($prop);
                if(!empty($prop))
                    $arProps[$i] = $prop;
            }
            unset($i, $prop);
            if(!$arProps)
                return false;
            
            return $arProps;
        }
        
        return false;
    }
    
    protected function getFindProperties() {
        if(isset($this->settings["catalog"]["find_prop"]) && !empty($this->settings["catalog"]["find_prop"])) {
            foreach($this->settings["catalog"]["find_prop"] as $i => $prop) {
                $prop = trim($prop);
                if(!empty($prop))
                    $arProps[$i] = $prop;
            }
            unset($i, $prop);
            if(!isset($arProps))
                return false;
            
            return $arProps;
        }
        
        return false;
    }
    
    protected function getFindPropertiesOffer() {
        if(isset($this->settings["offer"]["find_prop"]) && !empty($this->settings["offer"]["find_prop"])) {
            foreach($this->settings["offer"]["find_prop"] as $i => $prop) {
                $prop = trim($prop);
                if(!empty($prop))
                    $arProps[$i] = $prop;
            }
            if(!isset($arProps))
                return false;
            
            return $arProps;
        }
        
        return false;
    }
    
    public function getFindDubleProperties() {
        if(!empty($this->arFindProperties))
            foreach($this->arFindProperties as $code => $prop) {
                foreach($this->arFindProperties as $code1 => $prop1) {
                    if(strpos($prop1, $prop) !== false && $code1 != $code && $prop1 != $prop) {
                        $arDubleProps[$code][] = $code1;
                    }
                }
            }
        unset($code1, $prop1);
        if(isset($arDubleProps))
            return $arDubleProps;
        else
            return false;
    }
    
    public function getFindDublePropertiesOffer() {
        if(!empty($this->arFindPropertiesOffer))
            foreach($this->arFindPropertiesOffer as $code => $prop) {
                foreach($this->arFindPropertiesOffer as $code1 => $prop1) {
                    if(strpos($prop1, $prop) !== false && $code1 != $code && $prop1 != $prop) {
                        $arDubleProps[$code][] = $code1;
                    }
                }
            }
        if(isset($arDubleProps))
            return $arDubleProps;
        else
            return false;
    }
    
    protected function getSelectorPropertiesPreview() {
        if(isset($this->settings["catalog"]["selector_prop_preview"]) && !empty($this->settings["catalog"]["selector_prop_preview"])) {
            $arProps = false;
            foreach($this->settings["catalog"]["selector_prop_preview"] as $i => $prop) {
                $prop = trim($prop);
                if(!empty($prop))
                    $arProps[$i] = $prop;
            }
            unset($i, $prop);
            if(!$arProps)
                return false;
            
            return $arProps;
        }
        
        return false;
    }
    
    protected function getFindPropertiesPreview() {
        if(isset($this->settings["catalog"]["find_prop_preview"]) && !empty($this->settings["catalog"]["find_prop_preview"])) {
            foreach($this->settings["catalog"]["find_prop_preview"] as $i => $prop) {
                $prop = trim($prop);
                if(!empty($prop))
                    $arProps[$i] = $prop;
            }
            unset($i, $prop);
            if(!isset($arProps))
                return false;
            
            return $arProps;
        }
        
        return false;
    }
    
    protected function getFindDublePropertiesPreview() {
        if(!empty($this->arFindPropertiesPreview))
            foreach($this->arFindPropertiesPreview as $code => $prop) {
                foreach($this->arFindPropertiesPreview as $code1 => $prop1) {
                    if(strpos($prop1, $prop) !== false && $code1 != $code && $prop1 != $prop) {
                        $arDubleProps[$code][] = $code1;
                    }
                }
            }
        if(isset($arDubleProps))
            return $arDubleProps;
        else
            return false;
    }
    
    protected function parseCatalog() {
        
        set_time_limit(0);
        $this->ClearAjaxFiles();
        $this->DeleteLog();
        $this->checkActionBegin();
        $this->arUrl = array();
        $this->settings["catalog"]["url_dop"]=str_replace("&amp;","&",$this->settings["catalog"]["url_dop"]);
        if(isset($this->settings["catalog"]["url_dop"]) && !empty($this->settings["catalog"]["url_dop"]))
            $this->arUrl = explode("\r\n", $this->settings["catalog"]["url_dop"]);
        $this->arUrl = array_merge(array($this->rss), $this->arUrl);
        $this->arUrl = $this->GetArUrlSave();
        $this->arUrlSave = $this->arUrl;
        if(!$this->PageFromFile())
            return false;
        $this->CalculateStep();
        
        if($this->settings["catalog"]["mode"] == "debug")
            $this->arUrlSave = array_slice($this->arUrlSave, 0, self::DEFAULT_DEBUG_LIST);
        
        $countParsedPages = 0;
    
        foreach($this->arUrlSave as $rss) {
            
            $flag = 0;
            $inRange = false;
            
            do {
                $this->rss = trim($rss);
                if(file_exists($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/shs.parser/include/stop_parser_".$this->id.".txt")) {
                    throw new RuntimeException("stop");
                }
     
                if(empty($this->rss))
                    continue;
                $this->convetCyrillic($this->rss);
                $this->connectCatalogPage($this->rss, true);
                $this->SaveParseSection($this->rss);
                if(!$this->agent && $this->settings["catalog"]["mode"] != "debug" && isset($this->errors) && count($this->errors) > 0) {
                    $this->SaveLog();
                    unlink($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/shs.parser/include/count_parser_catalog_step".$this->id.".txt");
                    unlink($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/shs.parser/include/count_parser_copy_page".$this->id.".txt");
        
                    return false;
                }
                
                $this->parseCatalogNavigation($this->rss);
                
                if($this->inRangePages($this->currentPage)) {
                    $inRange = true;
                    
                } elseif($inRange) {
                    break;
                }
                
                if(!$inRange) {
                    continue;
                }

                if($this->type_out != 'HL')
                    $this->parseCatalogProducts();
                else if($this->type_out == 'HL')
                    $this->parseCatalogProductsHL();
    
                if($this->isDebug() && !$this->checkDebugCountPages(++$countParsedPages)) {
                    throw new RuntimeException("The work of demo parser is complited");
                }
               
            
            } while (!$this->CheckOnePageNavigation() && $rss = $this->getNextPaginationPage());
            
            if($this->settings['smart_log']['enabled'] == 'Y') {
                $this->settings['smart_log']['result_id'] = file_get_contents($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/shs.parser/include/result_id".$this->id.".txt");
                $this->settings['smart_log']['result_id'] = \Bitrix\Shs\ParserResultTable::updateEndTime($this->settings['smart_log']['result_id']);
            }
        }
    }
    
    protected function isDebug() {
        return $this->settings["catalog"]["mode"] == "debug";
    }
    
    protected function checkDebugCountPages($numberPage) {
        return $numberPage < self::DEFAULT_DEBUG_LIST;
    }
    
    protected function inRangePages($pageNumber) {
        if(!$this->settings["catalog"]["pagenavigation_begin"] && !$this->settings["catalog"]["pagenavigation_end"])
            return true;
        
        if($this->settings["catalog"]["pagenavigation_begin"] && $this->settings["catalog"]["pagenavigation_end"]
            && $pageNumber >= $this->settings["catalog"]["pagenavigation_begin"] && $pageNumber <= $this->settings["catalog"]["pagenavigation_end"])
            return true;
    
        if($this->settings["catalog"]["pagenavigation_begin"] && !$this->settings["catalog"]["pagenavigation_end"]
            && $pageNumber >= $this->settings["catalog"]["pagenavigation_begin"])
            return true;
        
        if(!$this->settings["catalog"]["pagenavigation_begin"] && $this->settings["catalog"]["pagenavigation_end"]
            && $pageNumber <= $this->settings["catalog"]["pagenavigation_end"])
            return true;
        
        return false;
    }
    
    protected function getNextPaginationPage() {
        if(empty($this->pagenavigation)) {
            return false;
        }
        
        if(!isset($this->pagenavigation[$this->rss])) {
            $this->pagenavigation = array_diff_key($this->pagenavigation, $this->pagenavigationPrev);
          
            return current($this->pagenavigation);
        } else {
            
            $findCurPage = false;
            
            foreach($this->pagenavigation as $rss) {
                
                if($findCurPage) {
                    return $rss;
                }
                
                if($rss == $this->rss) {
                    $findCurPage = true;
                }
            }
        }
      
        return false;
    }
    
    protected function ClearAjaxFiles() {
        if(!$this->agent && $_GET["begin"]) {
            if(file_exists($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/shs.parser/include/catalog_parser_prev_page".$this->id.".txt"))
                unlink($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/shs.parser/include/catalog_parser_prev_page".$this->id.".txt");
            if(file_exists($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/shs.parser/include/catalog_parser_prev_element".$this->id.".txt"))
                unlink($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/shs.parser/include/catalog_parser_prev_element".$this->id.".txt");
            if(file_exists($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/shs.parser/include/catalog_parser_current_page".$this->id.".txt"))
                unlink($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/shs.parser/include/catalog_parser_current_page".$this->id.".txt");
            if(file_exists($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/shs.parser/include/count_parser_catalog".$this->id.".txt"))
                unlink($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/shs.parser/include/count_parser_catalog".$this->id.".txt");
            if(file_exists($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/shs.parser/include/catalog_log_".$this->id.".txt"))
                unlink($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/shs.parser/include/catalog_log_".$this->id.".txt");
            if(file_exists($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/shs.parser/include/count_parser".$this->id.".txt"))
                unlink($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/shs.parser/include/count_parser".$this->id.".txt");
            if(file_exists($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/shs.parser/include/count_parser_catalog_step".$this->id.".txt")) {
                unlink($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/shs.parser/include/count_parser_catalog_step".$this->id.".txt");
            }
            if(file_exists($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/shs.parser/include/count_parser_copy_page".$this->id.".txt"))
                unlink($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/shs.parser/include/count_parser_copy_page".$this->id.".txt");
            if(file_exists($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/shs.parser/include/loc".$this->id.".txt"))
                unlink($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/shs.parser/include/loc".$this->id.".txt");
            if(file_exists($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/shs.parser/include/count_parser_section".$this->id.".txt"))
                unlink($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/shs.parser/include/count_parser_section".$this->id.".txt");
            if(file_exists($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/shs.parser/include/catalog_parser_current_list".$this->id.".txt"))
                unlink($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/shs.parser/include/catalog_parser_current_list".$this->id.".txt");
        }
        else if($this->agent) {
            if(file_exists($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/shs.parser/include/loc".$this->id.".txt"))
                unlink($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/shs.parser/include/loc".$this->id.".txt");
            if(file_exists($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/shs.parser/include/count_parser_section".$this->id.".txt"))
                unlink($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/shs.parser/include/count_parser_section".$this->id.".txt");
        }
        if(is_dir($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/shs.parser/include/".$this->id)) {
            $this->removeFolder($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/shs.parser/include/".$this->id);
        }
    }
    
    protected function removeFolder($folder) {
        if($files = glob($folder."/*")) {
            foreach($files as $file) {
                if(is_dir($file))
                    $this->removeFolder($file);
                else
                    unlink($file);
            }
            unset($files, $file);
        }
        rmdir($folder);
    }
    
    protected function DeleteLog() {
        if($this->agent)
            unlink($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/shs.parser/include/catalog".$this->id.".txt");
    }
    
    protected function checkActionBegin() {
        if((!$this->agent && $_GET["begin"]) || $this->agent) {
            $arr = array(
                "select" => array('ID'),
                "filter" => array("PARSER_ID" => $this->id)
            );
            if($this->tmp == "b_shs_parser_tmp" || !$this->tmp) {
                $rsData = \Shs\Parser\ParserTmpTable::GetList($arr);
                while($arData = $rsData->Fetch()) {
                    \Shs\Parser\ParserTmpTable::Delete($arData["ID"]);
                }
            }
            else if($this->tmp == "b_shs_parser_tmp_old") {
                $rsData = \Shs\Parser\ParserTmpOldTable::GetList($arr);
                while($arData = $rsData->Fetch()) {
                    \Shs\Parser\ParserTmpOldTable::Delete($arData["ID"]);
                }
            }
            unset($arr, $arData, $rsData);
        }
    }
    
    protected function GetArUrlSave() {
        $arrUrl = array();
        $this->section_array = array();
        if(isset($this->arUrl) && !empty($this->arUrl)) {
            foreach($this->arUrl as $key => $url) {
                if(empty($url))
                    continue 1;
                $this->convetCyrillic($url);
                $arrUrl[] = $url;
                $this->section_array[$url] = $this->section_id;
            }
        }
        $this->settings["catalog"]["rss_dop"]=str_replace("&amp;","&",$this->settings["catalog"]["rss_dop"]);
        if(isset($this->settings["catalog"]["rss_dop"]) && !empty($this->settings["catalog"]["rss_dop"])) {
            foreach($this->settings["catalog"]["rss_dop"] as $key => $url) {
                if(empty($url))
                    continue 1;
                $this->convetCyrillic($url);
                $arrUrl[] = $url;
                $this->section_array[$url] = $this->settings["catalog"]["section_dop"][$key];
            }
        }
        if(!empty($arrUrl))
            return $arrUrl;
        
        return false;
    }
    
    protected function PageFromFile() {
        if($this->settings["catalog"]["mode"] == "debug" || $this->agent || $_GET["begin"])
            return true;
        $prevPage = $prevElement = $currentPage = 0;
        if(file_exists($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/shs.parser/include/catalog_parser_prev_page".$this->id.".txt"))
            $prevPage = file_get_contents($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/shs.parser/include/catalog_parser_prev_page".$this->id.".txt");
        if(file_exists($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/shs.parser/include/catalog_parser_prev_element".$this->id.".txt"))
            $prevElement = file_get_contents($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/shs.parser/include/catalog_parser_prev_element".$this->id.".txt");
        if(file_exists($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/shs.parser/include/catalog_parser_current_page".$this->id.".txt"))
            $currentPage = file_get_contents($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/shs.parser/include/catalog_parser_current_page".$this->id.".txt");
        if($prevPage) {
            $arPrevPage = explode("|", $prevPage);
            $arPrevElement = explode("|", $prevElement);
            $arCurrentPage = explode("|", $currentPage);
        }
        else {
            $arPrevPage = array();
            $arCurrentPage = array();
        }
        
 
        if(isset($arPrevElement) && is_array($arPrevElement))
            foreach($arPrevElement as $i => $p) {
                $p = trim($p);
                if(empty($p))
                    continue;
                $this->pagePrevElement[$p] = $p;
            }
        if(isset($prevPage))
            unset($prevPage);
        if(isset($prevElement))
            unset($prevElement);
        if(isset($arPrevPage) && is_array($arPrevPage))
            foreach($arPrevPage as $i => $p) {
                $p = trim($p);
                if(empty($p))
                    continue;
                $this->pagenavigationPrev[$p] = $p;
            }
        if(isset($arPrevPage))
            unset($arPrevPage);
        if(isset($arPrevElement))
            unset($arPrevElement);
        if(isset($arCurrentPage) && is_array($arCurrentPage))
            foreach($arCurrentPage as $p) {
                $p = trim($p);
                if(empty($p))
                    continue;
                $this->pagenavigation[$p] = $p;
            }
        if(isset($arCurrentPage))
            unset($arCurrentPage);
        if(isset($this->pagenavigationPrev) && is_array($this->pagenavigationPrev))
            foreach($this->pagenavigationPrev as $i => $v) {
                foreach($this->pagenavigation as $i1 => $v1) {
                    if($v1 == $v)
                        unset($this->pagenavigation[$i1]);
                }
            }
        if(isset($this->pagenavigation) && is_array($this->pagenavigation))
            foreach($this->pagenavigation as $p) {
                $isContinue = true;
                $this->rss = $p;
                break;
            }
        if(!$isContinue && !empty($this->pagenavigationPrev) && $this->IsEndSectionUrl()) {
            $this->ClearBufferStop();
            
            return false;
        }
        else if(!$isContinue && !empty($this->pagenavigationPrev) && !$this->IsEndSectionUrl()) {
            $isContinue = true;
            $this->rss = $this->GetUrlRss();
        }
        $this->currentPage = count($this->pagenavigationPrev);
        if($this->IsNumberPageNavigation() && $this->CheckPageNavigation($this->currentPage)) {
            $this->activeCurrentPage = $this->currentPage - $this->arPageNavigationDelta[0] + 1;
        }
        else if(!$this->IsNumberPageNavigation())
            $this->activeCurrentPage = $this->currentPage;
        
        return true;
    }
    
    protected function IsEndSectionUrl() {
        if(empty($this->arUrl))
            return true;
        $count = 0;
        foreach($this->arUrl as $i => $url) {
            if(isset($this->pagenavigationPrev[$url]))
                $count++;
        }
        if($count == count($this->arUrl))
            return true;
        else
            return false;
    }
    
    protected function ClearBufferStop() {
        if(!$this->agent && $this->settings["catalog"]["mode"] != "debug") {
            global $APPLICATION;
            $APPLICATION->RestartBuffer();
            $this->checkActionAgent(false);

            throw new RuntimeException("buffer stop");
        }
    }
    
    protected function checkActionAgent($agent = true) {
        if((($this->agent && $agent) || !$agent) && $this->updateActive && isset($this->settings["catalog"]["uniq"]["action"]) && $this->settings["catalog"]["uniq"]["action"] != "N") {
            $arr = array(
                "select" => array(
                    'ID',
                    "PRODUCT_ID"
                ),
                "filter" => array("PARSER_ID" => $this->id)
            );
            if($this->tmp == "b_shs_parser_tmp" || !$this->tmp) {
                $rsData = \Shs\Parser\ParserTmpTable::GetList($arr);
                while($arData = $rsData->Fetch()) {
                    $arProd[$arData["PRODUCT_ID"]] = $arData["PRODUCT_ID"];
                }
                $rsDataOld = \Shs\Parser\ParserTmpOldTable::GetList($arr);
                while($arDataOld = $rsDataOld->Fetch()) {
                    $arProdOld[$arDataOld["PRODUCT_ID"]] = $arDataOld["PRODUCT_ID"];
                }
                if(isset($arProdOld) && !empty($arProdOld)) {
                    foreach($arProdOld as $p) {
                        if(!isset($arProd[$p]))
                            $this->doProductAction($p);
                    }
                }
                $parser = new ShsParserContent();
                $parser->Update($this->id, array('TMP' => "b_shs_parser_tmp_old"));
            }
            else if($this->tmp == "b_shs_parser_tmp_old") {
                $rsData = \Shs\Parser\ParserTmpOldTable::GetList($arr);
                while($arData = $rsData->Fetch()) {
                    $arProd[$arData["PRODUCT_ID"]] = $arData["PRODUCT_ID"];
                }
                $rsDataOld = \Shs\Parser\ParserTmpTable::GetList($arr);
                while($arDataOld = $rsDataOld->Fetch()) {
                    $arProdOld[$arDataOld["PRODUCT_ID"]] = $arDataOld["PRODUCT_ID"];
                }
                if(isset($arProdOld) && !empty($arProdOld)) {
                    foreach($arProdOld as $p) {
                        if(!isset($arProd[$p]))
                            $this->doProductAction($p);
                    }
                }
                $parser = new ShsParserContent();
                $parser->Update($this->id, array('TMP' => "b_shs_parser_tmp"));
            }
            unset($parser, $rsData, $arData, $arProd, $rsDataOld, $arDataOld, $arProdOld, $p);
        }
    }
    
    protected function doProductAction($ID) {
        if($this->settings["catalog"]["uniq"]["action"] == "D") {
            CIBlockElement::Delete($ID);
        }
        else if($this->settings["catalog"]["uniq"]["action"] == "A") {
            $el = new CIBlockElement;
            $el->Update($ID, array("ACTIVE" => "N"));
            unset($el);
        }
        else if($this->settings["catalog"]["uniq"]["action"] == "NULL") {
            CCatalogProduct::Update($ID, array("QUANTITY" => 0));
        }
    }
    
    protected function GetUrlRss() {
        foreach($this->arUrl as $i => $url) {
            if(isset($this->pagenavigationPrev[$url]))
                continue;
            
            return $url;
        }
    }
    
    protected function IsNumberPageNavigation() {
        if(!$this->settings["catalog"]["pagenavigation_begin"] && !$this->settings["catalog"]["pagenavigation_end"])
            return false;
        else
            return true;
    }
    
    protected function CheckPageNavigation($n) {
        if(!preg_match("/\d/", $n) || empty($n))
            return false;
        if($this->currentPage > $n)
            return false;
        
        if($this->arPageNavigationDelta[0] && $this->arPageNavigationDelta[1]) {
            if($n >= $this->arPageNavigationDelta[0] && $n <= $this->arPageNavigationDelta[1])
                return $n;
        }
        else if($this->arPageNavigationDelta[0] && !$this->arPageNavigationDelta[1]) {
            if($n >= $this->arPageNavigationDelta[0])
                return $n;
        }
        else if(!$this->arPageNavigationDelta[0] && $this->arPageNavigationDelta[1]) {
            if($n <= $this->arPageNavigationDelta[1])
                return $n;
        }
        
        return false;
    }
    
    protected function CalculateStep($count = 0) {
        if($this->settings["catalog"]["mode"] == "debug" || $this->agent || $this->stepStart)
            return true;
        
        $step = $this->settings["catalog"]["step"];
        
        if($step > $count && $count > 0) {
            $this->stepStart = true;
            
            return true;
        }
        
        $file = 0;
        
        if(file_exists($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/shs.parser/include/count_parser_catalog_step".$this->id.".txt"))
            $file = file_get_contents($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/shs.parser/include/count_parser_catalog_step".$this->id.".txt");
        
        if($file) {
            $arFile = explode("|", $file);
            $countElement = (int)$arFile[0];
            $currentElement = (int)$arFile[1];
            unset($arFile);
        }
        else {
            $countElement = $count;
            $currentElement = 0;
        }
        
        unset($file);
        
        if($countElement - $currentElement <= $step && $countElement > 0 && $count == 0) {
            $this->stepStart = true;
        }
        
        if($count == 0)
            return true;
        
        $currentElement++;
        file_put_contents($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/shs.parser/include/count_parser_catalog_step".$this->id.".txt", $countElement."|".$currentElement);
        
        if($currentElement % $step == 0 && !$this->stepStart) {
            $this->clearFields();
            $this->ClearBufferStep();
        }
        
        unset($step);
    }
    
    protected function clearFields() {
        unset($this->arFields, $this->elementUpdate, $this->elementID, $this->detailHtml, $this->arEmptyUpdate);
        if($this->arProduct)
            unset($this->arProduct);
        if(isset($this->arPrice))
            unset($this->arPrice);
        if(isset($this->arAdditionalPrice))
            unset($this->arAdditionalPrice);
        if(isset($this->elementOfferUpdate))
            unset($this->elementOfferUpdate);
        if(isset($this->arPhoto))
            unset($this->arPhoto);
        if(isset($this->arOfferAll))
            unset($this->arOfferAll);
        if(isset($this->additionalStore))
            unset($this->additionalStore);
        if(isset($this->elementName))
            unset($this->elementName);
        if(isset($this->arrFilesTemp))
            unset($this->arrFilesTemp);
        $this->SaveLog();
        unset($this->errors);
    }
    
    protected function SaveLog() {
        if($this->settings["catalog"]["log"] == "Y" && isset($this->errors) && count($this->errors) > 0)
            file_put_contents($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/shs.parser/include/catalog_log_".$this->id.".txt", print_r($this->errors, true), FILE_APPEND);
        if(!isset($this->errors))
            $this->errors = array();
        $this->debugErrors = array_merge($this->debugErrors, $this->errors);
    }
    
    protected function ClearBufferStep() {
        if(!$this->agent && $this->settings["catalog"]["mode"] != "debug" && self::TEST == 0) {
            global $APPLICATION;
            $APPLICATION->RestartBuffer();
        }
    }
    
    protected function connectCatalogPage($page, $saveProtocol = false) {
        $this->catalogSleep();
        $this->sectionPage = $page;
        $this->fileHtml = new FileGetHtml();
        $this->page = $this->GetCopyPage();
        if(!$this->page) {
            if($this->ValidateUrl($page) === true) {
                $this->page = $this->fileHtml->file_get_html($page, $this->proxy, $this->auth, $this);
            }
            else if($this->ValidateUrl($page) === false)
                $this->page = $this->fileHtml->file_get_local_html($page);
        }
        else {
            $this->fileHtml->httpCode = 200;
            $this->fileHtml->headerUrl = $this->GetCopyUrl();
        }
        if($saveProtocol) {
            if(!empty($this->fileHtml->effectivUrl))
                $this->protocol = current(explode(':', $this->fileHtml->effectivUrl));
        }
        $this->DeleteCharsetHtml5($this->page);
        $this->SaveCopyPage();
        $this->httpCode = $this->fileHtml->httpCode;
        if($this->httpCode != 200 && $this->httpCode != 301 && $this->httpCode != 302 && $this->httpCode != 303) {
            $this->errors[] = "[".$page."]".GetMessage("parser_error_connect")."[".$this->httpCode."]";
            $this->SaveLog();
            unset($this->errors);
            if($this->settings["catalog"]["404"] != "Y") {
                if(!$this->agent && $this->settings["catalog"]["mode"] != "debug") {
                    $this->stepStart = 1;
                    $this->SavePrevPage($page);
                    if(file_exists($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/shs.parser/include/count_parser_catalog_step".$this->id.".txt")) {
                        unlink($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/shs.parser/include/count_parser_catalog_step".$this->id.".txt");
                    }
                    $this->DeleteCopyPage();
                    $this->activeCurrentPage++;
                    $this->SetCatalogElementsResult($this->activeCurrentPage);
                    $this->clearFields();
                    $this->ClearBufferStep();
                }
                
                return false;
            }
        }
        $this->currentPage++;
        if(!$this->agent && $this->settings["catalog"]["mode"] != "debug")
            $this->SavePrevPage($page);
        $this->urlCatalog = $this->fileHtml->headerUrl;
        $this->urlSite = $this->getCatalogUrlSite();
        
        return true;
    }
    
    protected function catalogSleep() {
        if($this->settings["catalog"]["sleep"])
            sleep($this->settings["catalog"]["sleep"]);
    }
    
    protected function GetCopyPage() {
        if(!$this->agent && $this->settings["catalog"]["mode"] != "debug" && file_exists($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/shs.parser/include/catalog_parser_copy_page".$this->id.".txt")) {
            $this->httpCode = 200;
            
            return file_get_contents($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/shs.parser/include/catalog_parser_copy_page".$this->id.".txt");
        }
        $this->httpCode = 0;
        
        return false;
    }
    
    protected function ValidateUrl($url) {
        return boolval(preg_match("/^(http|https)?(:\/\/)?([A-Z0-9][A-Z0-9_-]*(?:\..[A-Z0-9][A-Z0-9_-]*)+):?(d+)?\/?/Diu", $url)); //�������� �����������
    }
    
    protected function GetCopyUrl() {
        if(!$this->agent && $this->settings["catalog"]["mode"] != "debug" && file_exists($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/shs.parser/include/catalog_parser_copy_url".$this->id.".txt"))
            return file_get_contents($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/shs.parser/include/catalog_parser_copy_url".$this->id.".txt");
        $this->httpCode = 0;
        
        return false;
    }
    
    public function DeleteCharsetHtml5(&$data) {
        $data = preg_replace("/\s*<meta\s+charset=[\"|']{0,1}.+?[\"|']{0,1}\s*\/{0,1}\>/i", "", $data);
    }
    
    protected function SaveCopyPage() {
        if(!$this->agent && $this->settings["catalog"]["mode"] != "debug") {
            file_put_contents($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/shs.parser/include/catalog_parser_copy_page".$this->id.".txt", $this->page);
            file_put_contents($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/shs.parser/include/catalog_parser_copy_url".$this->id.".txt", $this->fileHtml->headerUrl);
        }
    }
    
    protected function SavePrevPage($page) {
        if(!$this->agent && $this->settings["catalog"]["mode"] != "debug" && $this->stepStart) {
            file_put_contents($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/shs.parser/include/catalog_parser_prev_page".$this->id.".txt", $page."|", FILE_APPEND);
        }
    }
    
    protected function DeleteCopyPage() {
        if(!$this->agent && $this->settings["catalog"]["mode"] != "debug" && file_exists($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/shs.parser/include/catalog_parser_copy_page".$this->id.".txt")) {
            unlink($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/shs.parser/include/catalog_parser_copy_page".$this->id.".txt");
            unlink($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/shs.parser/include/catalog_parser_copy_url".$this->id.".txt");
        }
    }
    
    protected function SetCatalogElementsResult($page = false) {
        $file = 0;
        if(file_exists(($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/shs.parser/include/count_parser_catalog".$this->id.".txt")))
            $file = file_get_contents($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/shs.parser/include/count_parser_catalog".$this->id.".txt");
        if($file) {
            $arFile = explode("|", $file);
            $countPage = (int)$arFile[1];
            $ciElement = (int)$arFile[2];
            $errorElement = (int)$arFile[3];
            $allError = (int)$arFile[4];
            unset($arFile);
        }
        else {
            $countPage = 0;
            $ciElement = 0;
            $errorElement = 0;
            $allError = 0;
        }
    
        unset($file);
        if($page) {
            $countPage = $page;
        }
        else if(isset($this->elementID)) {
            $ciElement++;
            if(isset($this->errors) && count($this->errors))
                $errorElement++;
            if(empty($this->arFields["LINK"]))
                $this->arFields["LINK"] = $this->arFields["NAME"];
            $this->SavePrevPageDetail($this->arFields["LINK"]);
        }
        if(isset($this->errors) && count($this->errors) > 0)
            $allError = $allError + count($this->errors);
    
        file_put_contents($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/shs.parser/include/count_parser_catalog".$this->id.".txt", "|".$countPage."|".$ciElement."|".$errorElement."|".$allError."|".$this->countSection);
    }
    
    protected function SavePrevPageDetail($page) {
        if(!$this->agent && $this->settings["catalog"]["mode"] != "debug") {
            file_put_contents($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/shs.parser/include/catalog_parser_prev_element".$this->id.".txt", $page."|", FILE_APPEND);
        }
    }
    
    protected function getCatalogUrlSite() {
        if(preg_match("/http:/", $this->rss)) {
            $url = str_replace("http://", "", $this->rss);
            $url = preg_replace("/\/.*/", "", $url);
            $url = "http://".$url;
        }
        else if(preg_match("/https:/", $this->rss)) {
            $url = str_replace("https://", "", $this->rss);
            $url = preg_replace("/\/.*/", "", $url);
            $url = "https://".$url;
        }
        else {
            $url = preg_replace("/\/.*/", "", $this->rss);
        }
        
        return $url;
    }
    
    protected function SaveParseSection($rss) {
        if(isset($this->section_array[$rss]) && !empty($this->section_array[$rss])) {
            file_put_contents($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/shs.parser/include/count_parser_section".$this->id.".txt", $this->section_array[$rss]);
        }
    }
    
    protected function parseCatalogNavigation($pageHref) {
        $page = $this->fileHtml->file_get_html($pageHref, $this->proxy, $this->auth, $this);
        $this->html = phpQuery::newDocument($this->fix_utf8.$page, "text/html;charset=".LANG_CHARSET);
        $this->base = $this->GetMetaBase($this->html);
        if($this->settings["catalog"]["pagenavigation_selector"]) {
            $this->deleteCatalogElement($this->settings["catalog"]["pagenavigation_delete"], $this->settings["catalog"]["pagenavigation_selector"]);
            
            if(!$this->settings["catalog"]["pagenavigation_one"])
                $this->settings["catalog"]["pagenavigation_one"] = "a[href]";
            
            $arPath = $this->GetArraySrcAttr($this->settings["catalog"]["pagenavigation_one"]);
           
            $attr = $arPath["attr"] ? $arPath["attr"] : "href";
            $element = $this->settings["catalog"]["pagenavigation_selector"]." ".$arPath["path"];
            unset($this->pagenavigation[$pageHref], $this->pagenavigation[$this->currentPage]);
            $this->pagenavigationPrev[$pageHref] = $pageHref;
    
            $this->pagenavigation = array();
            foreach($this->html[$element] as $id => $page) {
                $p = pq($page)->attr($attr);
                $p = $this->getCatalogLink($p);
                $p1 = $p."\\r\\n";
                $n = pq($page)->text();
                $n = $this->ValidatePageNavigation($n);
                $p = $this->clearLink($p);
                
                if(!$p || empty($p) || (isset($this->settings["catalog"]["pagenavigation_var"]) && $this->settings["catalog"]["pagenavigation_var"])) {
                    if(isset($this->settings["catalog"]["pagenavigation_var"]) && $this->settings["catalog"]["pagenavigation_var"]) {
                        $nV = (int)$this->settings["catalog"]["pagenavigation_page_count"];
                        $pV = $this->settings["catalog"]["pagenavigation_var"];
                        $other = $this->settings["catalog"]["pagenavigation_other_var"];
                        $req = $pV."=".$n;
                        if($other)
                            $req .= "&".$other;
                        $p = $this->getUrlPageNavigation($req, $pV);
                    }
                    else
                        continue 1;
                }
                
                if(preg_match("/\d/ui", $n)) {
                    $this->pagenavigation[$p] = $p;
                    $this->pageNumberNavigation[$n] = $p;
                }
            }
            
            unset($arPath, $attr, $element, $page, $p, $p1, $n);
            
            return true;
        }
        else if(isset($this->settings["catalog"]["pagenavigation_var"]) && $this->settings["catalog"]["pagenavigation_var"] && isset($this->settings["catalog"]["pagenavigation_page_count"]) && $this->settings["catalog"]["pagenavigation_page_count"]) {
            $n = (int)$this->settings["catalog"]["pagenavigation_page_count"];
            $p = $this->settings["catalog"]["pagenavigation_var"];
            $other = $this->settings["catalog"]["pagenavigation_other_var"];
            $step = trim(intval($this->settings["catalog"]["pagenavigation_var_step"]));
            if($step == 1) {
                $step = 2;
                $n--;
                $step1 = 1;
            }
            else if($step != 0) {
                $step1 = $step;
                $n--;
            }
            $i = $step;
            for($j = 0; $j < $n; $j++) {
                $req = $p."=".$i;
                if($other)
                    $req .= "&".$other;
                $page = $this->getUrlPageNavigation($req, $p, $other);
                $this->pagenavigation[$page] = $page;
                $i = $i + $step1;
            }
            unset($n, $p, $other, $step, $step1, $i, $req, $page);
            
            return true;
        }
        
        return false;
    }
    
    protected function clearLink($p) {
        return str_replace(array(
            'javascript:void(0);'
        ), '', $p);
    }
    
    public function getMetaBase($html) {
        if(isset($this->base))
            unset($this->base);
        if($this->typeN == "catalog" || $this->typeN == "xls_catalo")
            $base = pq($html)->find("base:eq(0)")->attr("href");
        else if($this->typeN == "xml")
            $base = mb_detect_encoding($html, "auto");
        
        return $base;
    }
    
    protected function deleteCatalogElement($element, $parentElement = false, $dom = false) {
        
        if($parentElement) {
            $arElement = explode(",", $element);
            $parentElement = trim($parentElement);
            $element = "";
            foreach($arElement as $i => $el) {
                $el = trim($el);
                if(empty($el)) {
                    unset($arElement[$i]);
                    continue 1;
                }
                $element .= $parentElement." ".$el;
                if(($i + 1) != count($arElement))
                    $element .= ",";
            }
            unset($arElement, $parentElement, $i, $el);
        }
        pq($element)->remove();
        unset($element);
    }
    
    protected function GetArraySrcAttr($path) {
        preg_match('#\[[^\[]+$#', $path, $matches);
        
        return array(
            'path' => preg_replace('#\[[^\[]+$#', '', $path),
            'attr' => str_replace(array(
                "[",
                "]"
            ), "", $matches[0])
        );
    }
    
    protected function getCatalogLink($url, $convertCyrillic = true) {
        $url = trim($url);
        if(empty($url))
            return false;
        else if(preg_match("/^\/{2}www/", $url)) {
            $url = preg_replace("/^\/{2}www/", "www", $url);
        }
        else if(preg_match('/^ftp:/', $url) || preg_match('/^http:/', $url) || preg_match('/www\./', $url) || preg_match('/^https:/', $url)/* || preg_match('/^\/{2}/', $url)*/) {
            $url = $url;
        }
        else if(preg_match("/^\/{2}/", $url)) {
            $url = $this->protocol.':'.$url;
        }
        else if(preg_match("/^\/{1}[A-Za-z0-9-_]+/", $url)) {
            $url = substr($this->urlCatalog, 0, strpos($this->urlCatalog, '/', 9)).'/'.ltrim($url, '/');
        }
        else if(!preg_match("/^\/{2}/", $url) && preg_match("/\/{1}$/", $this->urlCatalog) && $url[0]!='/') {
            if($this->base)
                $url = $this->base.$url;
            else
                $url = $this->urlCatalog.$url;
        }
        else if(!preg_match("/^\?/", $url) && !preg_match("/^\//", $url) && !preg_match("/\/{1}$/", $this->urlCatalog)) {
            if($this->base) {
                if(!preg_match("/\/{1}$/", $this->base))
                    $this->base = $this->base."/";
                $url = $this->base.$url;
            }
            else {
                $uri = preg_replace('#/[^/]+$#', '', $this->urlCatalog);
                $url = $uri."/".$url;
            }
        }
        else if(preg_match("/\?/", $url) && preg_match("/\?/", $this->urlCatalog)) {
            if(preg_match("/^\?/", $url)) {
                $uri = preg_replace("/\?.+/", "", $this->urlCatalog);
                $url = $uri.$url;
            }
            else {
                $uri = preg_replace('#/[^/]+$#', '', $this->urlCatalog);
                $url = $uri."/".$url;
            }
        }
        unset($uri);
        
        if(substr($url,0,4)!="http") { 
            $a = explode('/', $this->urlCatalog);   
            $a = $a[0]."/".$a[1]."/".$a[2];   
            $url=$a.$url; 
        }

        return $url;
    }
    
    protected function ValidatePageNavigation($n) {
        $n = strip_tags($n);
        $n = preg_replace("/\D/", "", $n);
        
        return $n;
    }
    
    protected function getUrlPageNavigation($url = "", $p = "", $other = "") {
        $url = trim($url);
        $p = trim($p);
        if(empty($url) || empty($p))
            return false;
        $this->urlCatalog = $this->DeleteParam($this->urlCatalog, $p, $other);
        if(preg_match("/\/{1}$/", $this->urlCatalog)) {
            $url = $this->urlCatalog."?".$url;
        }
        else if(!preg_match("/\/{1}$/", $this->urlCatalog) && !preg_match("/\?/", $this->urlCatalog)) {
            $url = $this->urlCatalog."?".$url;
        }
        else if(preg_match("/\?/", $this->urlCatalog)) {
            $url = $this->urlCatalog."&".$url;
        }
        unset($p);
        
        return $url;
    }
    
    protected function DeleteParam($url = "", $p = "", $other = "") {
        if(empty($url) || empty($p))
            return false;
        $url = str_replace($other, "", $url);
        $reg = "/\?".$p."\=(\d)/";
        $url = preg_replace($reg, "", $url);
        $reg = "/".$p."\=(\d)/";
        $url = preg_replace($reg, "", $url);
        $url = str_replace("?&", "?", $url);
        $url = str_replace("&&", "&", $url);
        $url = str_replace("/&", "/?", $url);
        $url = preg_replace("/\?{1}$/", "", $url);
        unset($reg);
        
        return $url;
    }
    
    protected function CheckValidatePageNavigation($n) {
        if($this->arPageNavigationDelta[0] && $this->arPageNavigationDelta[1]) {
            if($n <= $this->arPageNavigationDelta[0] && $n <= $this->arPageNavigationDelta[1])
                return true;
        }
        else if($this->arPageNavigationDelta[0] && !$this->arPageNavigationDelta[1]) {
            if($n <= $this->arPageNavigationDelta[0] && $n <= 100000)
                return true;
        }
        else if(!$this->arPageNavigationDelta[0] && $this->arPageNavigationDelta[1]) {
            if($n <= $this->arPageNavigationDelta[1])
                return true;
        }
        
        return false;
    }
    
    protected function CheckPageNavigationLess($n) {
        if(!preg_match("/\d/", $n) || empty($n))
            return false;
        if($this->currentPage > $n)
            return false;
        if($this->arPageNavigationDelta[1]) {
            if($n <= $this->arPageNavigationDelta[1])
                return $n;
        }
        else if(!$this->arPageNavigationDelta[1])
            return true;
        
        return false;
    }
    
    protected function parseCatalogProducts() {
        $count = 0;
        $i = 0;
        $ci = 0;
        $this->activeCurrentPage++;
        $this->SetCatalogElementsResult($this->activeCurrentPage);
        $element = htmlspecialchars_decode($this->settings["catalog"]["selector"]);
        if($this->preview_delete_element)
            $this->deleteCatalogElement($this->preview_delete_element, $element, $this->html[$element]);
        if($this->preview_delete_attribute)
            $this->deleteCatalogAttribute($this->preview_delete_attribute, $element, $this->html[$element]);
        foreach($this->html[$element] as $el) {
            $count++;
        }
        
        if($this->settings["catalog"]["mode"] != "debug" && !$this->agent) {
            if($count > $this->settings["catalog"]["step"])
                $countStep = $this->settings["catalog"]["step"];
            else {
                $this->stepStart = true;
                if($this->CheckOnePageNavigation() || $this->CheckAlonePageNavigation($this->currentPage))
                    $this->pagenavigation[$this->rss] = $this->rss;
                $this->SaveCurrentPage($this->pagenavigation);
                $this->SavePrevPage($this->sectionPage);
                $countStep = $count;
            }
        }
        else {
            $countStep = $count;
        }
        
        file_put_contents($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/shs.parser/include/count_parser".$this->id.".txt", $countStep."|".$ci);
        
        if($count == 0) {
            $this->errors[] = GetMessage("parser_error_selector_notfound")."[".$element."]";
            $this->clearFields();
        }
        
        foreach($this->html[$element] as $el) {
            if(file_exists($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/shs.parser/include/stop_parser_".$this->id.".txt")) {
                throw new RuntimeException("stop");
            }
            $ci++;
            if($this->typeN == "xml")
                $debug_item = SotbitXmlParser::DEFAULT_DEBUG_ITEM;
            else
                $debug_item = self::DEFAULT_DEBUG_ITEM;
            if($i == $debug_item && $this->settings["catalog"]["mode"] == "debug")
                break;
            if($this->typeN == "catalog") {
                $this->parseCatalogProductElement($el);
            }
            if($this->typeN == "xml" || $this->typeN == "xml_catalo") {
                $this->parseCatalogProductElementXml($el);
            }

            if($this->settings['catalog']['enable_props_filter'] && !$this->property_filter)
                continue;
           
            file_put_contents($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/shs.parser/include/count_parser".$this->id.".txt", $countStep."|".++$i);
            
            if($i >= $countStep) {
                $i = 0;
            }
        }
        unlink($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/shs.parser/include/count_parser".$this->id.".txt");
        $this->clearHtml();
        if(isset($count))
            unset($count);
        if(isset($countStep))
            unset($countStep);
        if(isset($element))
            unset($element);
    }
    
    protected function deleteCatalogAttribute($element, $parentElement = false, $dom = false) {
        if($parentElement) {
            $arElement = explode(",", $element);
            $parentElement = trim($parentElement);
            $element = "";
            foreach($arElement as $i => $el) {
                $el = trim($el);
                if(empty($el)) {
                    unset($arElement[$i]);
                    continue;
                }
                preg_match('#\[[^\[]+$#', $el, $matches);
                $el = preg_replace('#\[[^\[]+$#', '', $el);
                $attr = str_replace(array(
                    "[",
                    "]"
                ), "", $matches[0]);
                $element = $parentElement." ".$el;
                pq($element)->removeAttr($attr);
            }
            unset($arElement, $parentElement, $element, $el, $i, $attr);
        }
    }
    
    protected function CheckOnePageNavigation() {
        if($this->settings["catalog"]["pagenavigation_begin"] == 1 && $this->settings["catalog"]["pagenavigation_end"] == 1)
            return true;
        else if(!$this->settings["catalog"]["pagenavigation_selector"] && (!isset($this->settings["catalog"]["pagenavigation_var"]) || !$this->settings["catalog"]["pagenavigation_var"]) && $this->typeN != 'xml')
            return true;
        
        return false;
    }
    
    protected function CheckAlonePageNavigation($n) {
        if(!empty($this->settings["catalog"]["pagenavigation_begin"]) && !empty($this->settings["catalog"]["pagenavigation_end"]) && $this->settings["catalog"]["pagenavigation_end"] == $this->settings["catalog"]["pagenavigation_begin"] && $n == $this->settings["catalog"]["pagenavigation_begin"])
            return true;
        
        return false;
    }
    
    protected function SaveCurrentPage($arPage) {
        if(!$this->agent && $this->settings["catalog"]["mode"] != "debug" && $this->stepStart) {
            $page = implode("|", $arPage);
            if(!empty($arPage))
                file_put_contents($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/shs.parser/include/catalog_parser_current_page".$this->id.".txt", $page."|");
            else if($this->IsEndSectionUrl())
                file_put_contents($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/shs.parser/include/catalog_parser_current_page".$this->id.".txt", "");
            else
                file_put_contents($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/shs.parser/include/catalog_parser_current_page".$this->id.".txt", "0");
            unset($arPage, $page);
        }
    }
    
    protected function StepContinue($n, $count = 0) {
        $this->property_filter = true;
        if($this->settings["catalog"]["mode"] == "debug" || $this->agent)
            return false;
        $step = (int)$this->settings["catalog"]["step"];
        if($step > $count && $count > 0)
            return false;
        $file = 0;
        if(file_exists($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/shs.parser/include/count_parser_catalog_step".$this->id.".txt"))
            $file = file_get_contents($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/shs.parser/include/count_parser_catalog_step".$this->id.".txt");
        if($file) {
            $arFile = explode("|", $file);
            $countElement = (int)$arFile[0];//20
            $currentElement = (int)$arFile[1];//2
            unset($arFile);
        }
        else {
            return false;
        }
        unset($file);
        if($currentElement > 0 && $n <= $currentElement)
            return true;
        else
            return false;
    }
    
    protected function parseCatalogProductElement(&$el) {
        $this->countItem++;
        if(!$this->parserCatalogPreview($el)) {
            $this->SaveCatalogError();
            $this->clearFields();
            
            return false;
        }
        $this->parseAdditionalStores($el);
        $this->parserCatalogDetail();
        $this->parseCatalogFirstUrl();
        $this->parseCatalogSection();
        $this->parseCatalogMeta();
        $this->parseCatalogDate();
        $this->parseCatalogAllFields();
        
        if($this->settings['catalog']['enable_props_filter'] && !$this->property_filter) {
            $this->SetCatalogElementsResultPlus();
            $this->clearFilesTemp();
            $this->clearFields();
            
            return false;
        }
        $db_events = GetModuleEvents("shs.parser", "parserBeforeAddElementCatalog", true); 
        $error = false;
        foreach($db_events as $arEvent) {
            $bEventRes = ExecuteModuleEventEx($arEvent, array(
                &$this,
                &$el
            ));
            if($bEventRes === false) {
                $error = true;
                break 1;
            }
        }
        unset($db_events);
        if(!$error && !$error_isad) 
        {
            $this->AddElementCatalog();
            foreach(GetModuleEvents("shs.parser", "parserAfterAddElementCatalog", true) as $arEvent)
                ExecuteModuleEventEx($arEvent, array(
                    &$this,
                    &$el
                ));
        }
        unset($error);
        if($this->isCatalog && $this->elementID) {
            if($this->isOfferCatalog && !$this->boolOffer) {
                $this->AddElementOfferCatalog();
                $this->elementID = $this->elementOfferID;
                $this->elementUpdate = $this->elementOfferUpdate;
            }
            if($this->boolOffer) {
                $this->addProductPriceOffers();
            }
            else {
                $this->AddProductCatalog();
                $this->AddMeasureCatalog();
                $this->AddPriceCatalog();
                $this->addAvailable();
            }
            $this->parseStore();
            $this->updateQuantity();
        }
        if($this->settings['smart_log']['enabled'] == 'Y' && $this->elementID) {
            $this->settings['smart_log']['result_id'] = file_get_contents($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/shs.parser/include/result_id".$this->id.".txt");
            SmartLogs::saveNewValues($this->elementID, $this->settings["smart_log"], $this->arFields, isset($this->arPrice['PRICE']) ? $this->arPrice['PRICE'] : null, $this->arProduct);
        }
        $this->SetCatalogElementsResult();
        $this->clearFilesTemp();
        $this->clearFields();
    }
    
    protected function parserCatalogPreview(&$el) {
        foreach(GetModuleEvents("shs.parser", "parserCatalogPreview", true) as $arEvent)
            ExecuteModuleEventEx($arEvent, array(
                $this->id,
                &$el,
                &$this->arFields
            ));
        if(!$this->parseCatalogUrlPreview($el))
            return false;
        self::parseCatalogNamePreview($el);
        $this->parseCatalogPropertiesPreview($el);
        if($this->isCatalog)
            $this->parseCatalogPricePreview($el);
        if($this->isCatalog)
            $this->parseCatalogAdditionalPricesPreview($el);
        if($this->isCatalog)
            $this->ParseCatalogAvailablePreview($el);
        $this->parseCatalogPreviewPicturePreview($el);
        $this->parseCatalogDescriptionPreview($el);
        $this->parserOffers($el, "Y");
        
        return true;
    }
    
    protected function parseCatalogUrlPreview($el) {
        if($this->settings["catalog"]["href"] == "a:parent") {
            $p = pq($el)->attr("href");
        }
        else {
            $url = $this->settings["catalog"]["href"] ? $this->settings["catalog"]["href"] : "a:eq(0)";
            $this->settings["catalog"]["href"] = htmlspecialchars_decode($url);
            $p = pq($el)->find($url)->attr("href");
        }
        if(!$p) {
            $this->errors[] = GetMessage("parser_error_href_notfound");
            unset($url, $p);
            
            return false;
        }
      
        $p = $this->getCatalogLink($p);
        $this->arFields["LINK"] = str_replace(' ', '%20', $p);
        if(isset($this->pagePrevElement[$p]))
            return false;
        
        return true;
    }
    
    /**
     * get name of xml element and put to $this->arFields["NAME"]
     * if didn't find name add error with name "parser_error_name_notfound";
     * */
    protected function parseCatalogNamePreview($el) {
        global $APPLICATION;
        if(isset($this->settings["catalog"]["detail_name"]) && $this->settings["catalog"]["detail_name"])
            return false;
        $name = $this->settings["catalog"]["name"] ? $this->settings["catalog"]["name"] : $this->settings["catalog"]["href"];
        $name = htmlspecialchars_decode($name);
        $ar = $this->GetArraySrcAttr($name);
        if(empty($ar["attr"]))
            $this->arFields["NAME"] = trim(strip_tags($this->stripCdata(pq($el)->find($ar["path"])->text())));
        else if(!empty($ar["attr"]))
            $this->arFields["NAME"] = trim(htmlspecialchars(trim(strip_tags(pq($el)->find($ar["path"])->attr($ar["attr"])))));
        if($this->arFields["NAME"]) {
            $this->arFields["NAME"] = $this->actionFieldProps("SOTBIT_PARSER_NAME_E", $this->arFields["NAME"]);
            if(isset($this->settings["loc"]["f_name"]) && $this->settings["loc"]["f_name"] == "Y")
                $this->arFields["NAME"] = $this->locText($this->arFields["NAME"]);
        }
        $this->arFields["NAME"] = $this->convertDataCharset($this->arFields["NAME"]);
        unset($name, $ar);
        if(strtolower(SITE_CHARSET) != 'utf-8')
        	$this->arFields["NAME"] = $APPLICATION->ConvertCharset($this->arFields["NAME"],"utf-8",SITE_CHARSET);
        if(!$this->arFields["NAME"]) {
            $this->errors[] = GetMessage("parser_error_name_notfound");
            
            return false;
        }
    }
    
    /**
     * delete CDATA tags with saving contents
     * @param string $string
     * @return mixed
     */
    protected function stripCdata($string) {
        preg_match_all('/<!\[cdata\[(.*?)\]\]>/is', $string, $matches);
        
        return str_replace($matches[0], $matches[1], $string);
    }
    
    protected function actionFieldProps($code, $val) {
        if(isset($this->settings["catalog"]["action_props_val"][$code]) && $this->settings["catalog"]["action_props_val"][$code]) {
            $val = $this->convertDataCharset($val);
            foreach($this->settings["catalog"]["action_props_val"][$code] as $i => $v) {
                $v = html_entity_decode($v);
                if($this->settings["catalog"]["action_props"][$code][$i] == "")
                    continue 1;
                if($this->settings["catalog"]["action_props"][$code][$i] == "delete") {
                    $val = str_replace($v, "", $val);
                }
                if($this->settings["catalog"]["action_props"][$code][$i] == "add_b") {
                    $val = $v.$val;
                }
                if($this->settings["catalog"]["action_props"][$code][$i] == "add_e") {
                    $val = $val.$v;
                }
                if($this->settings["catalog"]["action_props"][$code][$i] == "lower") {
                    $val = strtolower($val);
                    $fc = mb_strtoupper(mb_substr($val, 0, 1));
                    $val = $fc.mb_substr($val, 1);
                }
            }
            unset($fc, $v, $i);
        }
        
        return !is_array($val) ? trim($val) : $val;
    }
    
    /**
     * Convert charset of string to other charsets
     * @param string $data
     * @return string
     */
    protected function convertDataCharset($data) {
            return $data;
    }
    
    protected function locText($text = "", $format = "plain", $test = false) {
        global $APPLICATION;
        if(isset($this->settings["loc"]["type"]) && $this->settings["loc"]["type"] && $text) {
            if($this->settings["loc"]["type"] == "yandex") {
                $key = $this->settings["loc"]["yandex"]["key"];
                $lang = $this->settings["loc"]["yandex"]["lang"];
                $text0 = $text;
                $charset = strtolower(SITE_CHARSET);
                if($charset != "utf-8") {
                    $text = $APPLICATION->ConvertCharset($text, $charset, "utf-8");
                }
                $text = urlencode($text);
                $url = "https://translate.yandex.net/api/v1.5/tr/translate?key=".$key."&text=".$text."&lang=".$lang."&format=".$format;
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, "https://translate.yandex.net/api/v1.5/tr/translate");
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, "key=".$key."&text=".$text."&lang=".$lang."&format=".$format);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_TIMEOUT, 60);
                $arrorLoc[401] = GetMessage("shs_parser_loc_401");
                $arrorLoc[402] = GetMessage("shs_parser_loc_402");
                $arrorLoc[403] = GetMessage("shs_parser_loc_403");
                $arrorLoc[404] = GetMessage("shs_parser_loc_404");
                $arrorLoc[413] = GetMessage("shs_parser_loc_413");
                $arrorLoc[422] = GetMessage("shs_parser_loc_422");
                $arrorLoc[501] = GetMessage("shs_parser_loc_501");
                $data = curl_exec($ch);
                $xml = new CDataXML();
                $xml->LoadString($data);
                $arData = $xml->GetArray();
                if(isset($arData["Translation"]["#"]["text"]["0"]["#"])) {
                    $data = $arData["Translation"]["#"]["text"]["0"]["#"];
                    $charset = strtolower(SITE_CHARSET);
                    if($charset != "utf-8") {
                        $APPLICATION->ConvertCharset($data, "utf-8", $charset);
                    }
                }
                else if(isset($arData["Error"]["@"]["code"])) {
                    $this->errors[] = $arrorLoc[$arData["Error"]["@"]["code"]];
                    $data = $text0;
                }
                else
                    $data = $text0;
                curl_close($ch);
                unset($key, $lang, $text0, $text, $charset, $url, $ch, $arrorLoc, $xml, $arData, $xml, $arData, $arrorLoc);
                
                return $data;
            }
        }
    }
    
    protected function parseCatalogPropertiesPreview(&$el) {
        if($this->checkUniq() && !$this->isUpdate)
            return false;
        $this->parseCatalogSelectorPropertiesPreview($el);
        $this->parseCatalogFindPropertiesPreview($el);
    }
    
    protected function checkUniq() {
        if($this->elementUpdate)
            return $this->elementUpdate;
      
        if(!isset($this->arSortUpdate))
            $this->arSortUpdate = array();
       
        if(isset($this->uniqFields["LINK"]) && $this->uniqFields["LINK"] && isset($this->arFields["NAME"]) && $this->arFields["NAME"]) {
            $uniq = md5($this->arFields["NAME"].$this->arFields["LINK"]);
            $isElement = CIBlockElement::GetList(Array(), array(
                "XML_ID" => $uniq,
                "IBLOCK_ID" => $this->iblock_id
            ), false, Array("nTopCount" => 1), array_merge(array("ID"), $this->arSortUpdate))->Fetch();
            $this->elementUpdate = $isElement["ID"];
            
            if($isElement) {
                $this->arEmptyUpdate = $isElement;
                $result = $isElement["ID"];
            }
            else
                $result = false;
            unset($uniq, $isElement);
            
            return $result;
        }
        else {
            if($this->settings["catalog"]["uniq"]["prop"]) {
                $prop = $this->settings["catalog"]["uniq"]["prop"];
                if($this->arFields["PROPERTY_VALUES"][$prop])
                    $arFields["PROPERTY_".$prop] = $this->arFields["PROPERTY_VALUES"][$prop];
            }
            if($this->settings["catalog"]["uniq"]["name"]) {
                $prop = $this->settings["catalog"]["uniq"]["prop"];
                if($this->arFields["NAME"])
                    $arFields["NAME"] = $this->arFields["NAME"];
            }
            if(count($arFields) >= count($this->uniqFields))
                $isElement = CIBlockElement::GetList(Array(), array_merge(array("IBLOCK_ID" => $this->iblock_id), $arFields), false, Array("nTopCount" => 1), array_merge(array("ID"), $this->arSortUpdate))->Fetch();
            $this->elementUpdate = $isElement["ID"];
            if($isElement) {
                $this->arEmptyUpdate = $isElement;
                $result = $isElement["ID"];
            }
            else
                $result = false;
            unset($prop, $arFields, $isElement);
            
            return $result;
        }
        
        return false;
    }
    
    protected function parseCatalogSelectorPropertiesPreview(&$el) {
        if(!$this->arSelectorPropertiesPreview)
            return false;
        if($this->settings["catalog"]["catalog_delete_selector_props_symb_preview"]) {
            $deleteSymb = explode(",", $this->settings["catalog"]["catalog_delete_selector_props_symb_preview"]);
            foreach($deleteSymb as $i => &$symb) {
                $symb = trim($symb);
                $symb = htmlspecialcharsBack($symb);
                if(empty($symb)) {
                    unset($deleteSymb[$i]);
                    continue;
                }
                if($symb == "\\\\") {
                    $deleteSymb[$i] = ",";
                }
            }
        }
        $this->property_filter = false;
        foreach($this->arSelectorPropertiesPreview as $code => $val) {
            if($this->arProperties[$code]["PROPERTY_TYPE"] == "F") {
                $this->parseCatalogPropFilePreview($code, $el);
            }
            else {
                $ar = $this->GetArraySrcAttr(htmlspecialchars_decode($this->settings["catalog"]["selector_prop_preview"][$code]));
                if($ar["attr"])
                    $text = pq($el)->find($ar["path"])->attr($ar["attr"]);
                else
                    $text = pq($el)->find($this->settings["catalog"]["selector_prop_preview"][$code])->html();
                if($this->arProperties[$code]["USER_TYPE"] != "HTML")
                    $text = strip_tags($text);
                $text = str_replace($deleteSymb, "", $text);
                $this->property_filter = $this->parseCatalogProp($code, $val, $text) || $this->property_filter;
            }
        }
        unset($text, $ar);
        if(isset($deleteSymb))
            unset($deleteSymb);
    }
    
    protected function parseCatalogPropFilePreview($code, $el) {
        if($this->checkUniq() && (!$this->isUpdate || !$this->isUpdate["props"]))
            return false;
        $ar = $this->GetArraySrcAttr($this->settings["catalog"]["selector_prop_preview"][$code]);
        $n = 0;
        $isElement = $this->checkUniq();
        foreach(pq($el)->find($ar["path"]) as $f) {
            $src = pq($f)->attr($ar["attr"]);
            $descr = strip_tags(pq($f)->html());
            $src = $this->parseCaralogFilterSrc($src);
            $src = $this->getCatalogLink($src);
            $this->arFields["PROPERTY_VALUES"][$code]["n".$n]["VALUE"] = $this->MakeFileArray($src);
            $this->arrFilesTemp[] = $this->arFields["PROPERTY_VALUES"][$code]["n".$n]["VALUE"]["tmp_name"];
            $this->arFields["PROPERTY_VALUES"][$code]["n".$n]["DESCRIPTION"] = $descr;
            $n++;
        }
        unset($ar, $n, $src, $f, $descr);
        if($isElement) {
            $obElement = new CIBlockElement;
            $rsProperties = $obElement->GetProperty($this->iblock_id, $isElement, "sort", "asc", Array("CODE" => $code));
            while($arProperty = $rsProperties->Fetch()) {
                $this->arFields["PROPERTY_VALUES"][$code][$arProperty["PROPERTY_VALUE_ID"]] = array(
                    "tmp_name" => "",
                    "del" => "Y",
                );
            }
            CIBlockElement::SetPropertyValueCode($isElement, $code, $this->arFields["PROPERTY_VALUES"][$code]);
            unset($obElement, $rsProperties, $arProperty, $this->arFields["PROPERTY_VALUES"][$code]);
        }
    }
    
    public function parseCaralogFilterSrc($src) {
        $src = preg_replace('/#.+/', '', $src);
        $countPoint = substr_count($src, ".");
        if($countPoint >= 2 && preg_match("/^\/{2}/", $src) && !preg_match("/^\/{2}www\./", $src) && !preg_match("/http:\//", $src) && !preg_match("/https:\//", $src))
            $src = preg_replace("/^\/{2}/", "http://", $src);
        $src = str_replace('//', '/', $src);
        $src = str_replace('http:/', 'http://', $src);
        $src = str_replace('https:/', 'https://', $src);
        $src = str_replace('ftp:/', 'ftp://', $src);
        $src = str_replace('ftps:/', 'ftps://', $src);
        if(preg_match("/www\./", $src) || preg_match("/http:\//", $src) || preg_match("/https:\//", $src) || preg_match("/ftp:\//", $src) || preg_match("/ftps:\//", $src)) {
            if(preg_match("/https:\//", $src))
                $src = preg_replace("/^\/{2}/", "https://", $src);
            else if(preg_match("/http:\//", $src) || preg_match("/www\./", $src))
                $src = preg_replace("/^\/{2}/", "http://", $src);
            else if(preg_match("/ftp:\//", $src))
                $src = preg_replace("/^\/{2}/", "ftp://", $src);
            else if(preg_match("/ftps:\//", $src))
                $src = preg_replace("/^\/{2}/", "ftps://", $src);
        }
        if(preg_match("/www\./", $src) || preg_match("/http:\//", $src) || preg_match("/https:\//", $src) || preg_match("/ftp:\//", $src) || preg_match("/ftps:\//", $src)) {
            if(preg_match("/https:\//", $src))
                $src = preg_replace("/^\/{1}/", "https://", $src);
            else if(preg_match("/http:\//", $src) || preg_match("/www\./", $src))
                $src = preg_replace("/^\/{1}/", "http://", $src);
            else if(preg_match("/ftp:\//", $src))
                $src = preg_replace("/^\/{1}/", "ftp://", $src);
            else if(preg_match("/ftps:\//", $src))
                $src = preg_replace("/^\/{1}/", "ftps://", $src);
        }
        
        return $src;
    }
    
    /**
     * This wrapper of standart function need for same changes url, like replace  space by %20, may be in the  future need add same changes
     * @param string $url
     */
    protected function MakeFileArray($url) {
        if(strpos($url, 'https:') !== false) {
            $error_reporting = ini_get('error_reporting');
            $display_errors = ini_get('display_errors');
            $display_startup_errors = ini_get('display_startup_errors');
            ini_set('display_errors', 'Off');
        }
        $ar = CFile::MakeFileArray(str_replace(' ', '%20', $url));
        
        if(is_array($ar))
            $ar = $this->checkExtention($ar);
      
        if(isset($ar['type']) && $ar['type'] == 'unknown') {
            $arUrl = explode('/', $url);
            $upload_path = $_SERVER['DOCUMENT_ROOT'].'/upload/img_parser/'.$this->id.'/';
            if(!is_dir($upload_path))
                mkdir($upload_path, 0775, true);
            $path = $upload_path.rand(0, 100).'_'.$arUrl[count($arUrl) - 1];
            unset($upload_path, $arUrl);
            $ch = curl_init($url);
            $fp = fopen($path, 'wb');
            curl_setopt($ch, CURLOPT_FILE, $fp);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            curl_exec($ch);
            curl_close($ch);
            fclose($fp);
            $ar = CFile::MakeFileArray(str_replace(' ', '%20', $path));
            if($ar["type"] == "application/x-gzip") {
                $current = file_get_contents($path);
                $current = gzdecode($current);
                file_put_contents($path, $current);
                $ar = CFile::MakeFileArray(str_replace(' ', '%20', $path));
            }
        }
        if(strpos($url, 'https:') !== false) {
            ini_set('error_reporting', $error_reporting);
            ini_set('display_errors', $display_errors);
            ini_set('display_startup_errors', $display_startup_errors);
            unset($error_reporting, $display_errors, $display_startup_errors);
        }
        unset($fp, $ch);
        
        return $ar;
    }
    
    protected function checkExtention(array $ar) {
        if($ar['type'] == 'unknown')
            return $ar;
        if ($ar['type'] == 'application/pdf')
            return $ar; 
        
        $imageExtention = image_type_to_extension(exif_imagetype($ar['tmp_name']));
    
        if(!preg_match('/\\'.$imageExtention.'$/', $ar['name'])) {
            $ar['name'] = 'image'.$imageExtention;
        }
        
        if(!preg_match('/\.'.$imageExtention.'$/', $ar['tmp_name'])) {
            rename($ar['tmp_name'], $ar['tmp_name'].$imageExtention);
            $ar['tmp_name'] = $ar['tmp_name'].$imageExtention;
        }
        
        if(end(explode(".", $ar['name'])) == 'php') {
            $arType = explode('/', $ar['type']);
            if(!isset($arType[1]) || empty($arType['1']))
                return $ar;
            $fileEx = end(explode(".", $ar['name']));
            $ar['name'] = preg_replace('/\.'.$fileEx.'$/', '.'.$arType[1], $ar['name']);
            $newTmpName = preg_replace('/\.'.$fileEx.'$/', '.'.$arType[1], $ar['tmp_name']);
            rename($ar['tmp_name'], $newTmpName);
            $ar['tmp_name'] = $newTmpName;
        }
        
        return $ar;
    }
    
    public function parseCatalogProp($code, $val, $text) {
        $val = preg_quote($val, "/");
        if(!is_array($text)) {
            $text = preg_replace("/(".$val.")/", "", $text, 1);
            $val = trim($text);
            $val = trim($val, chr(0xC2).chr(0xA0));
            $val = html_entity_decode($val);
        }
        else {
            foreach($text as $key => $txt) {
                $text[$key] = preg_replace("/(".$val.")/", "", $text[$key], 1);
                $text[$key] = trim($text[$key]);
                $text[$key] = trim($text[$key], chr(0xC2).chr(0xA0));
                $text[$key] = html_entity_decode($text[$key]);
            }
            $val = $text;
        }
       
        $arProp = $this->arProperties[$code];
        
        if($arProp["PROPERTY_TYPE"] != "N" && isset($this->settings["loc"]["f_props"]) && $this->settings["loc"]["f_props"])
            $val = $this->locText($val, $arProp["USER_TYPE"] == "HTML" ? "html" : "plain");
        if($arProp["USER_TYPE"] == "HTML" && $arProp["MULTIPLE"] != "Y") {
            $this->arFields["PROPERTY_VALUES"][$code] = Array(
                "VALUE" => Array(
                    "TEXT" => $val,
                    "TYPE" => "html"
                )
            );
        }
        else if($arProp["USER_TYPE"] == "HTML" && $arProp["MULTIPLE"] == "Y") {
            foreach($val as $key => $vl)
                $this->arFields["PROPERTY_VALUES"][$code]["n".$key] = Array(
                    "VALUE" => Array(
                        "TEXT" => $vl,
                        "TYPE" => "html"
                    )
                );
        }
        else if($arProp["PROPERTY_TYPE"] == "S" && $arProp["MULTIPLE"] != "Y" && $arProp["USER_TYPE"] == "directory") {
            $this->arFields["PROPERTY_VALUES"][$code] = $this->CheckPropsDirectory($arProp, $code, $val);;
        }
        else if($arProp["PROPERTY_TYPE"] == "S" && $arProp["MULTIPLE"] == "Y" && $arProp["USER_TYPE"] == "directory") {
            foreach($val as $key => $vl)
                $this->arFields["PROPERTY_VALUES"][$code]["n".$key] = $this->CheckPropsDirectory($arProp, $code, $vl);
        }
        else if($arProp["PROPERTY_TYPE"] == "S" && $arProp["MULTIPLE"] != "Y") {
            $val = $this->convertDataCharset($this->actionFieldProps($code, $val));
            $this->arFields["PROPERTY_VALUES"][$code] = $val;
        }
        else if($arProp["PROPERTY_TYPE"] == "S" && $arProp["MULTIPLE"] == "Y") {
            if(!is_array($val))
                $val = array($val);
            foreach($val as $key => $vl) {
                $vl = $this->convertDataCharset($this->actionFieldProps($code, $vl));
                $this->arFields["PROPERTY_VALUES"][$code]["n".$key] = $vl;
            }
        }
        else if($arProp["PROPERTY_TYPE"] == "N") {
            $val = str_replace(",", ".", $val);
            $val = preg_replace("/\.{1}$/", "", $val);
            $val = preg_replace('/[^0-9.]/', "", $val);
            $this->arFields["PROPERTY_VALUES"][$code] = $val;
        }
        else if($arProp["PROPERTY_TYPE"] == "L" && $arProp["MULTIPLE"] != "Y") {
            $this->arFields["PROPERTY_VALUES"][$code] = $this->CheckPropsL($arProp["ID"], $code, $val);
        }
        else if($arProp["PROPERTY_TYPE"] == "L" && $arProp["MULTIPLE"] == "Y") {
            foreach($val as $key => $vl)
                $this->arFields["PROPERTY_VALUES"][$code]["n".$key] = $this->CheckPropsL($arProp["ID"], $code, $vl);
        }
        else if($arProp["PROPERTY_TYPE"] == "E" && $arProp["MULTIPLE"] != "Y") {
            $this->arFields["PROPERTY_VALUES"][$code] = $this->CheckPropsE($arProp, $code, $val);
        }
        else if($arProp["PROPERTY_TYPE"] == "E" && $arProp["MULTIPLE"] == "Y") {
            if(!is_array($val))
                $val = array($val);
            foreach($val as $key => $vl)
                $this->arFields["PROPERTY_VALUES"][$code]["n".$key] = $this->CheckPropsE($arProp, $code, $vl);
        }
    
        $res = $this->filterProps($code, $val);
        
        if(!$res) {
            unset($val, $text, $res);
        
            return false;
        }
        
        unset($val, $res, $arProp, $text);
        
        return true;
    }
    
    public function filterProps($code, $val) {
        if(count($this->settings["props_filter_value"]) > 0 && count($this->settings["props_filter_circs"]) > 0) {
            $count = 0;
            foreach($this->settings["props_filter_value"] as $id => $props) {
                if(strlen($id) <= 0)
                    continue 1;
                if(isset($props[$code]) && strlen($props[$code]) > 0) {
                    if($this->settings["props_filter_circs"][$id][$code] == "equally") {
                        if(!is_array($val)) {
                            if($props[$code] == $val) {
                                $this->propsFilter[$code] = "Y";
                                $count++;
                                break;
                            }
                        }
                        else if(is_array($val)) {
                            $result = false;
                            foreach($val as $v)
                                if($props[$code] == $v) {
                                    $result = true;
                                    break;
                                }
                            if($result) {
                                $this->propsFilter[$code] = "Y";
                                $count++;
                                break;
                            }
                        }
                    }
                    else if($this->settings["props_filter_circs"][$id][$code] == "strpos") {
                        if(!is_array($val)) {
                            if(strpos($val, $props[$code]) !== false) {
                                $this->propsFilter[$code] = "Y";
                                $count++;
                                break;
                            }
                        }
                        else if(is_array($val)) {
                            $result = false;
                            foreach($val as $v)
                                if(strpos($v, $props[$code]) !== false) {
                                    $result = true;
                                    break;
                                }
                            if($result) {
                                $this->propsFilter[$code] = "Y";
                                $count++;
                                break;
                            }
                        }
                    }
                    else if($this->settings["props_filter_circs"][$id][$code] == "stripos") {
                        if(!is_array($val)) {
                            if(strpos($val, $props[$code]) !== false) {
                                $this->propsFilter[$code] = "Y";
                                $count++;
                                break;
                            }
                        }
                        else if(is_array($val)) {
                            $result = false;
                            foreach($val as $v)
                                if(stripos($v, $props[$code]) !== false) {
                                    $result = true;
                                    break;
                                }
                            if($result) {
                                $this->propsFilter[$code] = "Y";
                                $count++;
                                break;
                            }
                        }
                    }
                }
            }
            if($count <= 0)
                return false;
            else
                return true;
        }
        
        return true;
    }
    
    public function CheckPropsDirectory($arProp, $code, $val) {
        if(empty($val) || ($arProp["USER_TYPE"] != "directory"))
            return false;
        $element_xml_id = Cutil::translit($val, 'ru', array('change_case' => 'U'));
        $element_xml_id = self::GetCleanCode($element_xml_id);
        $arr = \Bitrix\Highloadblock\HighloadBlockTable::getList(array('filter' => array('TABLE_NAME' => $arProp["USER_TYPE_SETTINGS"]["TABLE_NAME"])))->Fetch();
        $hlblock = Bitrix\Highloadblock\HighloadBlockTable::getById($arr["ID"])->fetch();
        $entity = Bitrix\Highloadblock\HighloadBlockTable::compileEntity($hlblock);
        $entity_data_class = $entity->getDataClass();
        $entity_table_name = $hlblock['TABLE_NAME'];
        $rsData = $entity_data_class::getList(array(
            "select" => array('UF_XML_ID'),
            "filter" => array("UF_NAME" => $val),
            "order" => array("UF_SORT" => "ASC")
        ));
        $rsData = new CDBResult($rsData, 'tbl_'.$entity_table_name);
        $arRes = $rsData->Fetch();
        if($arRes) {
            $result = $arRes["UF_XML_ID"];
        }
        else {
            $data = array(
                "UF_NAME" => $val,
                "UF_SORT" => 100,
                "UF_XML_ID" => $element_xml_id,
            );
            $result = $entity_data_class::add($data);
            unset($data);
            $result = $data["UF_XML_ID"];
        }
        unset($element_xml_id, $arr, $hlblock, $entity, $entity_data_class, $entity_table_name, $rsData);
        
        return $result;
    }
    
    public function GetCleanCode($code) {
        if(is_numeric($code[0]))
            return 'N'.$code;
        
        return $code;
    }
    
    public function CheckPropsL($id, $code, $val) {
        $arReplace = array(
            '%' => '\%',
            '!' => '\!',
            '?' => '\?',
            '=' => '\=',
            '>' => '\>',
            '<' => '\<'
        );
        $arRes2 = CIBlockProperty::GetPropertyEnum($id, array(), array(
            "IBLOCK_ID" => $this->iblock_id,
            "VALUE" => str_replace(array_keys($arReplace), $arReplace, $val)
        ))->Fetch();
        if($arRes2) {
            $kz = $arRes2["ID"];
        }
        else {
            $kz = CIBlockPropertyEnum::Add(array(
                "PROPERTY_ID" => $id,
                "VALUE" => $val,
                "TMP_ID" => md5(uniqid(""))
            ));
        }
        unset($arRes2);
        
        return $kz;
    }
    
    public function CheckPropsE($arProp, $code, $val) {
        $IBLOCK_ID = $arProp["LINK_IBLOCK_ID"];
        $rsProp = CIBlockElement::GetList(array(), array(
            "IBLOCK_ID" => $IBLOCK_ID,
            "%NAME" => $val
        ), false, false, array(
            "ID",
            "NAME"
        ));
        while($arIsProp = $rsProp->Fetch()) {
            $arIsProp["NAME"] = mb_strtolower($arIsProp["NAME"], LANG_CHARSET);
            $val0 = mb_strtolower($val, LANG_CHARSET);
            if($val0 == $arIsProp["NAME"])
                $isProp = $arIsProp["ID"];
        }
        if($isProp) {
            unset($IBLOCK_ID, $rsProp, $arIsProp, $val0);
            
            return $isProp;
        } else {
            $arFields = array(
                "NAME" => $val,
                "ACTIVE" => "Y",
                "IBLOCK_ID" => $IBLOCK_ID,
                "CODE" => CUtil::translit($val, "ru", array(
                    "max_len" => 100,
                    "change_case" => 'L',
                    "replace_space" => '_',
                    "replace_other" => '_',
                    "delete_repeat_replace" => true,
                ))
            );
            $el = new CIBlockElement;
          
            $id = $el->Add($arFields);
            if(!$id)
                $this->errors[] = GetMessage("error_add_prop_e").$this->arFields["NAME"]."[".$this->arFields["LINK"]."] - ".$el->LAST_ERROR;
            unset($IBLOCK_ID, $rsProp, $arIsProp, $val0, $isProp, $el);
            
            return $id;
        }
    }
    
    protected function parseCatalogFindPropertiesPreview(&$el) {
        if(!$this->arFindPropertiesPreview)
            return false;
        $find = htmlspecialchars_decode($this->settings["catalog"]["selector_find_props_preview"]);
        if($this->settings["catalog"]["catalog_delete_selector_find_props_symb_preview"]) {
            $deleteSymb = explode(",", $this->settings["catalog"]["catalog_delete_selector_find_props_symb_preview"]);
            foreach($deleteSymb as $i => &$symb) {
                $symb = trim($symb);
                $symb = htmlspecialcharsBack($symb);
                if(empty($symb)) {
                    unset($deleteSymb[$i]);
                    continue;
                }
                if($symb == "\\\\") {
                    $deleteSymb[$i] = ",";
                }
            }
        }
        $arFind = explode(",", $find);
        foreach($arFind as $vFind) {
            if(strpos($vFind, " br") !== false || strpos($vFind, "<br/>") || strpos($vFind, "<br />")) {
                $vFind = str_replace(array(
                    " br",
                    "<br/>",
                    "<br />"
                ), "", $vFind);
                $vFind = trim($vFind);
                foreach(pq($el)->find($vFind) as $prop) {
                    $arBr = array(
                        "<br>",
                        "<br/>",
                        "<br />"
                    );
                    $text = pq($prop)->html();
                    $text = str_replace($arBr, "<br>", $text);
                    unset($arBr[1]);
                    unset($arBr[2]);
                    foreach($arBr as $br) {
                        $arTextBr = explode($br, $text);
                        if(!empty($arTextBr) && count($arTextBr) > 1) {
                            foreach($arTextBr as $textBr) {
                                $textBr = strip_tags($textBr);
                                $textBr = str_replace($deleteSymb, "", $textBr);
                                foreach($this->arFindPropertiesPreview as $code => $val) {
                                    if($this->CheckFindPropsPreview($code, $val, $textBr)) {
                                        $this->parseCatalogProp($code, $val, $textBr);
                                    }
                                }
                            }
                        }
                    }
                }
            }
            else {
                foreach(pq($el)->find($vFind) as $prop) {
                    $text = pq($prop)->html();
                    $text = str_replace($deleteSymb, "", $text);
                    $text1 = $text;
                    foreach($this->arFindPropertiesPreview as $code => $val) {
                        $text1 = $text;
                        if($this->arProperties[$code]["USER_TYPE"] != "HTML")
                            $text1 = strip_tags($text);
                        if($this->CheckFindPropsPreview($code, $val, $text1)) {
                            $this->parseCatalogProp($code, $val, $text1);
                        }
                    }
                }
            }
        }
        unset($arFind, $arBr, $vFind, $text, $text1, $prop, $prop, $arTextBr, $textBr, $arBr);
        if(isset($deleteSymb))
            unset($deleteSymb);
    }
    
    protected function CheckFindPropsPreview($code, $val, $text) {
        $bool = false;
        if(isset($this->arDubleFindPropertiesPreview[$code])) {
            foreach($this->arDubleFindPropertiesPreview[$code] as $prop) {
                $v = $this->arFindPropertiesPreview[$prop];
                if(strpos($text, $v) !== false) {
                    $bool = true;
                }
            }
            unset($prop, $v);
            if($bool)
                return false;
        }
        if(strpos($text, $val) !== false)
            return true;
        else
            return false;
    }
    
    protected function parseCatalogPricePreview(&$el) {
        if($this->settings["catalog"]["preview_price"]) {
            if($this->checkUniq() && (!$this->isUpdate || !$this->isUpdate["price"]))
                return false;
            $price = htmlspecialchars_decode($this->settings["catalog"]["preview_price"]);
            $price = $this->GetArraySrcAttr($price);
            if($price["attr"] && stripos($price["attr"], '=') != 0)
                $price = strip_tags(pq($el)->find($this->UtfParams($this->settings["catalog"]["preview_price"]))->html());
            else if(empty($price["attr"]))
                $price = strip_tags(pq($el)->find($price["path"])->html());
            else if(!empty($price["attr"]))
                $price = trim(pq($el)->find($price["path"])->attr($price["attr"]));
            $price = $this->parseCatalogPriceFormat($price);
            //$price = $this->parseCatalogPriceOkrug($price);
            $this->arPrice["PRICE"] = $price;
            $this->arPrice["PRICE"] = trim($this->arPrice["PRICE"]);
            if(!$this->arPrice["PRICE"]) {
                $this->errors[] = $this->arFields["NAME"]."[".$this->arFields["LINK"]."]".GetMessage("parser_error_price_notfound");
                unset($this->arPrice["PRICE"]);
                
                return false;
            }
            $this->arPrice["CATALOG_GROUP_ID"] = $this->settings["catalog"]["price_type"];
            $this->arPrice["CURRENCY"] = $this->settings["catalog"]["currency"];
        }
    }
    
    protected function UtfParams($param) {
        if(mb_detect_encoding($param) != SITE_CHARSET)
            return iconv(SITE_CHARSET, mb_detect_encoding($param), $param);
        
        return $param;
    }
    
    protected function parseCatalogPriceFormat($price) {
        $price = trim($price);
        if(isset($this->settings["catalog"]["price_format1"]) && $this->settings["catalog"]["price_format1"] && isset($this->settings["catalog"]["price_format2"]) && $this->settings["catalog"]["price_format2"]) {
            $price = str_replace($this->settings["catalog"]["price_format1"], "", $price);
            $price = str_replace($this->settings["catalog"]["price_format2"], ".", $price);
        }
        else
            $price = str_replace(",", ".", $price);
        $price = preg_replace("/\.{1}$/", "", $price);
        $price = preg_replace('/[^0-9.]/', "", $price);
        $pr= explode('.', $price);   
        if($pr[1]) $price = $pr[0].'.'.$pr[1]; else $price = $pr[0];       
        return $price;
    }
    
    protected function parseCatalogAdditionalPricesPreview(&$el) {
        if($this->settings["prices_preview"] && !empty($this->settings["prices_preview"])) {
            if($this->checkUniq() && (!$this->isUpdate || !$this->isUpdate["price"]))
                return false;
            foreach($this->settings["prices_preview"] as $id_price => $price_arr) {
                if($price_arr['value'] == '')
                    continue;
                $addit_price = array();
                $price = htmlspecialchars_decode($price_arr['value']);
                $price = $this->GetArraySrcAttr($price);
                if(empty($price["attr"]))
                    $price = strip_tags(pq($el)->find($price["path"])->html());
                else if(!empty($price["attr"]))
                    $price = trim(pq($el)->find($price["path"])->attr($price["attr"]));
                $price = $this->parseCatalogPriceFormat($price);
                $addit_price["PRICE"] = $price;
                $addit_price["PRICE"] = trim($addit_price["PRICE"]);
                if(!$addit_price["PRICE"]) {
                    $this->errors[] = $this->arFields["NAME"]."[".$this->arFields["LINK"]."] ".$price_arr['name'].GetMessage("parser_error_price_notfound");
                    unset($addit_price["PRICE"]);
                    
                    return false;
                }
                $addit_price["CATALOG_GROUP_ID"] = $id_price;
                $addit_price['CURRENCY'] = $this->settings['adittional_currency'][$id_price];
                $this->arAdditionalPrice[$id_price] = $addit_price;
            }
            unset($addit_price, $price_arr, $id_price, $price);
        }
    }
    
    public function parseCatalogPreviewPicturePreview(&$el)
    {
        if($this->checkUniq() && (!$this->isUpdate || !$this->isUpdate["preview_img"]))
            return false;
        if($this->settings["catalog"]["preview_picture"] && $this->settings["catalog"]["img_preview_from_detail"] != "Y") {
            $arSelPic = explode(",", $this->settings["catalog"]["preview_picture"]);
             foreach($arSelPic as $sel) {
                $sel = trim($sel);
                if(empty($sel))
                    continue;
                $ar = $this->GetArraySrcAttr($sel);
                if(!empty($ar["attr"])) {
                    $src = pq($el)->find($ar["path"])->attr($ar["attr"]);
                    $src = $this->parseSelectorStyle($ar["attr"], $src);
                }
                else if(empty($ar["attr"])) {
                    $src = pq($el)->find($ar["path"])->text();
                }
            $src = $this->parseCaralogFilterSrc($src);
            $src = $this->getCatalogLink($src);
                foreach(GetModuleEvents("shs.parser", "ParserPreviewPicture", true) as $arEvent)
                ExecuteModuleEventEx($arEvent, array(
                    &$this,
                    &$src
                ));
            
            $this->arPhoto[$src] = 1;
            $this->arFields["PREVIEW_PICTURE"] = $this->MakeFileArray($src);
    
                if(!self::CheckImage($this->arFields["PREVIEW_PICTURE"]['tmp_name'])) {
                    unset($this->arFields["PREVIEW_PICTURE"]['tmp_name']);
                    unset($this->arPhoto[$src]);
                        continue;
                    return;
                }
             
            $this->arrFilesTemp[] = $this->arFields["PREVIEW_PICTURE"]["tmp_name"];
            }
            unset($arSelPic, $sel, $ar, $src);
        }
    } 
    
    protected function parseSelectorStyle($attr, $src) {
        if($attr == "style" && $src) {
            preg_match("/url\(([^)]*)\)/", $src, $matches);
            if(isset($matches[1]) && $matches[1])
                $src = str_replace(array(
                    '"',
                    "'"
                ), "", $matches[1]);
        }
        
        return $src;
    }
    
    protected function CheckImage($src) {
        return !empty($src) ? boolval(exif_imagetype($src)) : false;
        
        if(!empty($src) && preg_match("/(jpeg|jpg|gif|png|JPEG|JPG|GIF|PNG)$/", preg_replace("/\?.+/", '', $src)))
            return true;
        else {
            return false;
        }
    }
    
    protected function parseCatalogDescriptionPreview(&$el) {
        if($this->checkUniq() && (!$this->isUpdate || $this->isUpdate["preview_descr"] == "N"))
            return false;
        if($this->settings["catalog"]["preview_text_selector"] && $this->settings["catalog"]["text_preview_from_detail"] != "Y") {
            $preview = htmlspecialchars_decode($this->settings["catalog"]["preview_text_selector"]);
            foreach(pq($el)->find($preview." img") as $img) {
                $src = pq($img)->attr("src");
                $src = $this->parseCaralogFilterSrc($src);
                $src = $this->getCatalogLink($src);
                $this->parseCatalogSaveImgServer($img, $src);
            }
            if($this->bool_preview_delete_tag == "Y")
                $preview_text = strip_tags(pq($el)->find($preview)->html(), htmlspecialcharsBack($this->preview_delete_tag));
            else
                $preview_text = pq($el)->find($preview)->html();
            $preview_text = trim($preview_text);
            if(isset($this->settings["loc"]["f_preview_text"]) && $this->settings["loc"]["f_preview_text"] == "Y")
                $preview_text = $this->locText($preview_text, $this->preview_text_type == "html" ? "html" : "plain");
            $this->arFields["PREVIEW_TEXT"] = $this->convertDataCharset(trim($preview_text));
            $this->arFields["PREVIEW_TEXT_TYPE"] = $this->preview_text_type;
            unset($preview, $src, $img, $preview_text);
        }
    }
    
    protected function parseCatalogSaveImgServer($img, $src) {
        $arImg = $this->MakeFileArray($src);
        $this->arrFilesTemp[] = $arImg["tmp_name"];
        if(isset($this->albumID) && $this->albumID)
            $this->addAlbumCollection($arImg, $img);
        else {
            $fid = CFile::SaveFile($arImg, "shs.parser");
            pq($img)->attr('src', CFile::GetPath($fid));
        }
        unset($arImg, $fid);
    }
    
    protected function addAlbumCollection($arImg, $img) {
        $res = CMedialibItem::Edit(array(
            'file' => $arImg,
            'arFields' => array(
                'ID' => 0,
                'NAME' => $arImg["name"],
                'DESCRIPTION' => "",
                'KEYWORDS' => ""
            ),
            'arCollections' => array($this->albumID)
        ));
        unset($res);
    }
    
    protected function parserOffers($el, $check = "") {
        if($this->settings["offer"]["preview_or_detail"] == $check) {
            $this->boolOffer = (isset($this->arOfferAll) && !empty($this->arOfferAll)) ? true : false;
            if($this->settings["offer"]["load"] == "table" && $this->isOfferParsing && isset($this->settings["offer"]["selector"]) && $this->settings["offer"]["selector"] && isset($this->settings["offer"]["selector_item"]) && $this->settings["offer"]["selector_item"]) {
                $offerItem = $this->settings["offer"]["selector"]." ".$this->settings["offer"]["selector_item"];
                $this->parserHeadTableOffer($el);
                foreach(pq($el)->find($offerItem) as $offer) {
                    $this->boolOffer = true;
                    if($this->parseOfferName($offer)) {
                        $this->parseOfferPrice($offer);
                        $this->parseOfferAdditionalPrice($offer);
                        $this->parseOfferQuantity($offer);
                        $this->parseOfferProps($offer);
                        if(!$this->parseOfferGetUniq()) {
                            $this->deleteOfferFields();;
                            continue 1;
                        }
                    }
                    else
                        continue 1;
                    $this->arOfferAll["FIELDS"][] = $this->arOffer;
                    $this->arOfferAll["PRICE"][] = $this->arPriceOffer;
                    $this->arOfferAll["ADDIT_PRICE"][] = $this->arAdditionalPriceOffer;
                    $this->arOfferAll["QUANTITY"][] = $this->arOfferQuantity;
                    $this->deleteOfferFields();
                }
            }
            else if($this->settings["offer"]["load"] == "one" && $this->isOfferParsing && isset($this->settings["offer"]["one"]["selector"]) && $this->settings["offer"]["one"]["selector"]) {
                $offerItem = trim($this->settings["offer"]["one"]["selector"]);
                $arr = $this->GetArraySrcAttr($offerItem);
                $path = $arr["path"];
                $attr = $arr["attr"];
                foreach(pq($el)->find($path) as $offer) {
                    $this->boolOffer = true;
                    if($this->parseOfferName($offer)) {
                        $this->parseOfferDetailImg($offer);
                        $this->parseOfferPrice($offer);
                        $this->parseOfferAdditionalPrice($offer);
                        $this->parseOfferQuantity($offer);
                        $this->parseOfferProps($offer);
                        if(!$this->parseOfferGetUniq()) {
                            $this->deleteOfferFields();;
                            continue 1;
                        }
                    }
                    else
                        continue 1;
                    $this->arOfferAll["FIELDS"][] = $this->arOffer;
                    $this->arOfferAll["PRICE"][] = $this->arPriceOffer;
                    $this->arOfferAll["ADDIT_PRICE"][] = $this->arAdditionalPriceOffer;
                    $this->arOfferAll["QUANTITY"][] = $this->arOfferQuantity;
                    $this->deleteOfferFields();
                    unset($offer);
                }
            }
            else if($this->settings["offer"]["load"] == "more" && $this->isOfferParsing && isset($this->settings["offer"]["selector_prop_more"]) && (count($this->settings["offer"]["selector_prop_more"]) > 0)) {
                if(isset($this->settings["offer"]["catalog_offer_selector_table"]) && ($this->settings["offer"]["catalog_offer_selector_table"] != '')) {
                    foreach(pq($el)->find($this->settings["offer"]["catalog_offer_selector_table"]) as $val) {
                        $allOfferProps = $this->parseOffersSelectorPropMore($val);
                        if(($allOfferProps !== false) && is_array($allOfferProps)) {
                            $nm = 0;
                            $arRes = array();
                            $count = count($allOfferProps);
                            foreach($allOfferProps as $code => $props) {
                                $nm++;
                                foreach($props as $id => $valProps) {
                                    $val = $valProps["value"];
                                    $arTemp[] = $valProps;
                                    $this->funcX($val, $nm, $allOfferProps, $arRes, $arTemp, $count);
                                }
                                break 1;
                            }
                            $this->parseAllOffersMoreProps($arRes);
                        }
                    }
                    unset($nm, $arRes, $count, $allOfferProps, $arTemp);
                }
                else {
                    $allOfferProps = $this->parseOffersSelectorPropMore($el);
                    if(($allOfferProps !== false) && is_array($allOfferProps)) {
                        $nm = 0;
                        $arRes = array();
                        $count = count($allOfferProps);
                        foreach($allOfferProps as $code => $props) {
                            $nm++;
                            foreach($props as $id => $valProps) {
                                $val = $valProps["value"];
                                $arTemp[] = $valProps;
                                $this->funcX($val, $nm, $allOfferProps, $arRes, $arTemp, $count);
                            }
                            break 1;
                        }
                        $this->parseAllOffersMoreProps($arRes);
                        unset($nm, $arRes, $count, $allOfferProps, $arTemp);
                    }
                }
            }
            foreach(GetModuleEvents("shs.parser", "parserAfterParsingOffers", true) as $arEvent)
                ExecuteModuleEventEx($arEvent, array(
                    &$this,
                    &$el
                ));
        }
    }
    
    protected function parserHeadTableOffer($el) {
        if(isset($this->settings["offer"]["selector"]) && $this->settings["offer"]["selector"] && isset($this->settings["offer"]["selector_head"]) && $this->settings["offer"]["selector_head"] && isset($this->settings["offer"]["selector_head_th"]) && $this->settings["offer"]["selector_head_th"]) {
            $offerHead = $this->settings["offer"]["selector"]." ".$this->settings["offer"]["selector_head"]." ".$this->settings["offer"]["selector_head_th"];
            $i = 0;
            foreach(pq($el)->find($offerHead) as $head) {
                $textHead = trim(strip_tags(pq($head)->html()));
                $this->tableHeaderNumber[$textHead] = $i;
                $i++;
            }
            unset($head);
            unset($offerHead);
            unset($i);
        }
    }
    
    protected function parseOfferName($offer) {
        if(isset($this->settings["offer"]["selector_name"]) && $this->settings["offer"]["selector_name"]) {
            $arr = $this->GetArraySrcAttr($this->settings["offer"]["selector_name"]);
            if(empty($arr["path"]) && !empty($arr["attr"])) {
                $name = trim(pq($offer)->attr($arr["attr"]));
            }
            else {
                if(empty($arr["attr"])) {
                    $name = trim(strip_tags(pq($offer)->find($arr["path"])->html()));
                }
                else if(!empty($arr["attr"])) {
                    $name = trim(pq($offer)->find($arr["path"])->attr($arr["attr"]));
                }
            }
            $deleteSymb = $this->getOfferDeleteSelector();
            $name = str_replace($deleteSymb, "", $name);
            $this->arOffer["NAME"] = htmlspecialchars_decode($name);
            if(isset($this->settings["loc"]["f_name"]) && $this->settings["loc"]["f_name"] == "Y") {
                $this->arOffer["NAME"] = $this->locText($this->arOffer["NAME"]);
            }
            unset($arr, $name, $deleteSymb);
        }
        else if(isset($this->settings["offer"]["find_name"]) && $this->settings["offer"]["find_name"]) {
            if(isset($this->settings["offer"]["selector_item_td"]) && $this->settings["offer"]["selector_item_td"]) {
                $deleteSymb = $this->getOfferDeleteFind();
                $name = $this->settings["offer"]["find_name"];
                if(isset($this->tableHeaderNumber[$name])) {
                    $n = $this->tableHeaderNumber[$name];
                    $name = pq($offer)->find($this->settings["offer"]["selector_item_td"].":eq(".$n.")");
                    $this->arOffer["NAME"] = htmlspecialchars_decode(trim(strip_tags($name)));
                    $this->arOffer["NAME"] = str_replace($deleteSymb, "", $this->arOffer["NAME"]);
                    if(isset($this->settings["loc"]["f_name"]) && $this->settings["loc"]["f_name"] == "Y") {
                        $this->arOffer["NAME"] = $this->locText($this->arOffer["NAME"]);
                    }
                }
            }
            unset($n, $name, $deleteSymb);
        }
        else if(isset($this->settings["offer"]["selector_prop_more"]) && $this->settings["offer"]["selector_prop_more"] && (!isset($this->settings["offer"]["add_name"]) || empty($this->settings["offer"]["add_name"]))) {
            if(!empty($offer) && is_array($offer)) {
                if(isset($this->settings["offer"]["add_offer_name_more"]) && !empty($this->settings["offer"]["add_offer_name_more"])) {
                    $arName = explode("|", trim(str_replace(" ", "", $this->settings["offer"]["add_offer_name_more"])));
                }
                else {
                    if(isset($this->settings["offer"]["selector_prop_more"]) && count($this->settings["offer"]["selector_prop_more"]) > 0) {
                        foreach($this->settings["offer"]["selector_prop_more"] as $code => $value) {
                            if(empty($code))
                                continue 1;
                            $arName[] = $code;
                        }
                    }
                    else
                        return false;
                }
                $this->arOffer["NAME"] = "";
                foreach($arName as $code) {
                    if(empty($code))
                        continue 1;
                    foreach($offer as $val) {
                        if($val["code"] == $code) {
                            if($this->arOffer["NAME"] != "") {
                                $this->arOffer["NAME"] = $this->arOffer["NAME"]." / ".$val["value"];
                            }
                            else {
                                $this->arOffer["NAME"] = $val["value"];
                            }
                        }
                    }
                }
                $this->arOffer["NAME"] = trim(str_replace("  ", " ", $this->arOffer["NAME"]));
                $this->arOffer["NAME"] = htmlspecialchars_decode(trim(strip_tags($this->arOffer["NAME"])));
                $this->arOffer["NAME"] = $this->arFields["NAME"]." (".$this->arOffer["NAME"].")";
                if(isset($this->settings["loc"]["f_name"]) && $this->settings["loc"]["f_name"] == "Y") {
                    $this->arOffer["NAME"] = $this->locText($this->arOffer["NAME"]);
                }
            }
            unset($arName, $code, $value, $val);
        }
        if(isset($deleteSymb))
            unset($deleteSymb);
        if(isset($arr))
            unset($arr);
        if(isset($name))
            unset($name);
        if(!isset($this->arOffer["NAME"]) && (!isset($this->settings["offer"]["add_name"]) || empty($this->settings["offer"]["add_name"]))) {
            $this->errors[] = GetMessage("parser_error_name_notfound_offer");
            
            return false;
        }
        else if(!isset($this->arOffer["NAME"]))
            $this->arOffer["NAME"] = $this->arFields["NAME"];
        
        return true;
    }
    
    protected function getOfferDeleteSelector() {
        $deleteSymb = array();
        if($this->settings["offer"]["catalog_delete_selector_props_symb"]) {
            $deleteSymb = explode("||", $this->settings["offer"]["catalog_delete_selector_props_symb"]);
            foreach($deleteSymb as $i => &$symb) {
                $symb = trim($symb);
                $symb = htmlspecialcharsBack($symb);
                if(empty($symb)) {
                    unset($deleteSymb[$i]);
                    continue;
                }
                if(preg_match("/^\//", $symb) && preg_match("/\/$/", $symb)) {
                    unset($deleteSymb[$i]);
                    continue;
                }
            }
        }
        
        return $deleteSymb;
    }
    
    protected function getOfferDeleteFind() {
        $deleteSymb = array();
        if($this->settings["offer"]["catalog_delete_selector_find_props_symb"]) {
            $deleteSymb = explode("||", $this->settings["offer"]["catalog_delete_selector_find_props_symb"]);
            foreach($deleteSymb as $i => &$symb) {
                $symb = trim($symb);
                $symb = htmlspecialcharsBack($symb);
                if(empty($symb)) {
                    unset($deleteSymb[$i]);
                    continue;
                }
                if(preg_match("/^\//", $symb) && preg_match("/\/$/", $symb)) {
                    unset($deleteSymb[$i]);
                    continue;
                }
            }
        }
        
        return $deleteSymb;
    }
    
    protected function parseOfferPrice($offer) {
        if($this->checkUniq() && (!$this->isUpdate || !$this->isUpdate["price"]))
            return false;
        if(isset($this->settings["offer"]["selector_price"]) && $this->settings["offer"]["selector_price"]) {
            $arr = $this->GetArraySrcAttr($this->settings["offer"]["selector_price"]);
            if(empty($arr["path"]) && !empty($arr["attr"])) {
                $price = trim(pq($offer)->attr($arr["attr"]));
            }
            else {
                if(empty($arr["attr"])) {
                    $price = trim(strip_tags(pq($offer)->find($arr["path"])->html()));
                }
                else if(!empty($arr["attr"])) {
                    $price = trim(pq($offer)->find($arr["path"])->attr($arr["attr"]));
                }
            }
            $price = $this->parseCatalogPriceFormat($price);
            $this->arPriceOffer["PRICE"] = $price;
            $this->arPriceOffer["CATALOG_GROUP_ID"] = $this->settings["catalog"]["price_type"];
            $this->arPriceOffer["CURRENCY"] = $this->settings["catalog"]["currency"];
        }
        else if(isset($this->settings["offer"]["find_price"]) && $this->settings["offer"]["find_price"]) {
            if(isset($this->settings["offer"]["selector_item_td"]) && $this->settings["offer"]["selector_item_td"]) {
                $name = $this->settings["offer"]["find_price"];
                if(isset($this->tableHeaderNumber[$name])) {
                    $n = $this->tableHeaderNumber[$name];
                    $price = pq($offer)->find($this->settings["offer"]["selector_item_td"].":eq(".$n.")");
                    $price = trim(strip_tags($price));
                    $price = $this->parseCatalogPriceFormat($price);
                    $this->arPriceOffer["PRICE"] = $price;
                    $this->arPriceOffer["CATALOG_GROUP_ID"] = $this->settings["catalog"]["price_type"];
                    $this->arPriceOffer["CURRENCY"] = $this->settings["catalog"]["currency"];
                }
            }
        }
        else if(isset($this->settings["offer"]["one"]["price"]) && $this->settings["offer"]["one"]["price"]) {
            $attr = $this->settings["offer"]["one"]["price"];
            $price = trim(strip_tags(pq($offer)->find($attr)->html()));
            $price = $this->parseCatalogPriceFormat($price);
            $this->arPriceOffer["PRICE"] = $price;
            $this->arPriceOffer["CATALOG_GROUP_ID"] = $this->settings["catalog"]["price_type"];
            $this->arPriceOffer["CURRENCY"] = $this->settings["catalog"]["currency"];
        }
        if(isset($this->arPrice["PRICE"]) && !empty($this->arPrice["PRICE"]) && (!isset($this->arPriceOffer["PRICE"]) || empty($this->arPriceOffer["PRICE"]))) {
            $this->arPriceOffer["PRICE"] = $this->arPrice["PRICE"];
            $this->arPriceOffer["CATALOG_GROUP_ID"] = $this->settings["catalog"]["price_type"];
            $this->arPriceOffer["CURRENCY"] = $this->settings["catalog"]["currency"];
        }
        if(isset($price))
            unset($price);
        if(isset($arr))
            unset($arr);
        if(isset($n))
            unset($n);
        if(isset($attr))
            unset($attr);
        if(!isset($this->arPriceOffer["PRICE"])) {
            $this->errors[] = GetMessage("parser_error_name_notfound_offer");
            
            return false;
        }
        
        return true;
    }
    
    protected function parseOfferAdditionalPrice($offer) {
        if($this->checkUniq() && (!$this->isUpdate || !$this->isUpdate["price"]))
            return false;
        $this->arAdditionalPriceOffer = array();
        if(isset($this->settings["offer"]["selector_additional_prices"]) && !empty($this->settings["offer"]["selector_additional_prices"])) {
            foreach($this->settings["offer"]["selector_additional_prices"] as $id_price => $price1) {
                if($price1['value'] === '')
                    continue;
                $arr_price = array();
                $arr = $this->GetArraySrcAttr($price1['value']);
                if(empty($arr["path"]) && !empty($arr["attr"])) {
                    $price = trim(pq($offer)->attr($arr["attr"]));
                }
                else {
                    if(empty($arr["attr"])) {
                        $price = trim(strip_tags(pq($offer)->find($arr["path"])->html()));
                    }
                    else if(!empty($arr["attr"])) {
                        $price = trim(pq($offer)->find($arr["path"])->attr($arr["attr"]));
                    }
                }
                $price = $this->parseCatalogPriceFormat($price);
                $arr_price["PRICE"] = $price;
                $arr_price["CATALOG_GROUP_ID"] = $id_price;
                $arr_price['CURRENCY'] = $this->settings['adittional_currency'][$id_price];
                $this->arAdditionalPriceOffer[$id_price] = $arr_price;
            }
            unset($price1, $arr_price, $price, $arr, $id_price);
        }
        else if(isset($this->settings["offer"]["find_additional_price"]) && !empty($this->settings["offer"]["find_additional_price"])) {
            if(isset($this->settings["offer"]["selector_item_td"]) && $this->settings["offer"]["selector_item_td"]) {
                foreach($this->settings["offer"]["find_additional_price"] as $id_price => $price1) {
                    if($price1['value'] === '')
                        continue;
                    $arr_price = array();
                    $name = $price1['value'];
                    if(isset($this->tableHeaderNumber[$name])) {
                        $n = $this->tableHeaderNumber[$name];
                        $price = pq($offer)->find($this->settings["offer"]["selector_item_td"].":eq(".$n.")");
                        $price = trim(strip_tags($price));
                        $price = $this->parseCatalogPriceFormat($price);
                        $arr_price["PRICE"] = $price;
                        $arr_price["CATALOG_GROUP_ID"] = $id_price;
                        $arr_price['CURRENCY'] = $this->settings['adittional_currency'][$id_price];
                        $this->arAdditionalPriceOffer[$id_price] = $arr_price;
                    }
                }
                unset($price1, $arr_price, $price, $name, $id_price);
            }
        }
        else if(isset($this->settings["offer"]["one"]["price"]) && $this->settings["offer"]["one"]["price"]) {
            $attr = $this->settings["offer"]["one"]["price"];
            $price = trim(strip_tags(pq($offer)->find($attr)->html()));
            $price = $this->parseCatalogPriceFormat($price);     
            $this->arPriceOffer["PRICE"] = $price;
            $this->arPriceOffer["CATALOG_GROUP_ID"] = $this->settings["catalog"]["price_type"];
            $this->arPriceOffer["CURRENCY"] = $this->settings["catalog"]["currency"];
            unset($price);
        }
        if(isset($this->arAdditionalPrice) && !empty($this->arAdditionalPrice) && (!isset($this->arAdditionalPriceOffer) || empty($this->arAdditionalPriceOffer))) {
            $this->arAdditionalPriceOffer = $this->arAdditionalPrice;
        }
        if(!isset($this->arAdditionalPriceOffer)) {
            $this->errors[] = GetMessage("parser_error_name_notfound_offer");
            
            return false;
        }
        
        return true;
    }
    
    protected function parseOfferProps($offer, $nameOffer = false) {
        if($this->checkUniq() && !$this->isUpdate)
            return false;
        if(isset($this->settings["offer"]["selector_prop"]) && !empty($this->settings["offer"]["selector_prop"])) {
            $deleteSymb = $this->getOfferDeleteSelector();
            $deleteSymbRegular = $this->getOfferDeleteSelectorRegular();
            $arProperties = $this->arSelectorPropertiesOffer;
            foreach($arProperties as $code => $val) {
                $arProp = $this->arPropertiesOffer[$code];
                if($arProp["PROPERTY_TYPE"] == "F") {
                    $this->parseCatalogPropFile($code, $offer);
                }
                else {
                    $arr = $this->GetArraySrcAttr($this->settings["offer"]["selector_prop"][$code]);
                    if(empty($arr["path"]) && !empty($arr["attr"])) {
                        $text = trim(pq($offer)->attr($arr["attr"]));
                    }
                    else {
                        if(empty($arr["attr"])) {
                            $text = trim(strip_tags(pq($offer)->find($arr["path"])->html()));
                        }
                        else if(!empty($arr["attr"])) {
                            $text = trim(pq($offer)->find($arr["path"])->attr($arr["attr"]));
                        }
                    }
                    if($arProp["USER_TYPE"] != "HTML")
                        $text = strip_tags($text);
                    $text = str_replace($deleteSymb, "", $text);
                    $text = preg_replace($deleteSymbRegular, "", $text);
                    $this->parseCatalogPropOffer($code, $val, $text);
                }
            }
            unset($deleteSymb, $deleteSymbRegular, $arProperties, $code, $val, $arProp, $arr, $text);
        }
        if(isset($this->settings["offer"]["find_prop"]) && !empty($this->settings["offer"]["find_prop"])) {
            $deleteSymb = $this->getOfferDeleteFind();
            $deleteSymbRegular = $this->getOfferDeleteFindRegular();
            $arProperties = $this->arFindPropertiesOffer;
            foreach($arProperties as $code => $val) {
                $arProp = $this->arPropertiesOffer[$code];
                if(isset($this->tableHeaderNumber[$val])) {
                    $n = $this->tableHeaderNumber[$val];
                    $text = pq($offer)->find($this->settings["offer"]["selector_item_td"].":eq(".$n.")");
                    $text = str_replace($deleteSymb, "", $text);
                    $text = preg_replace($deleteSymbRegular, "", $text);
                    $text1 = $text;
                    if($arProp["USER_TYPE"] != "HTML")
                        $text1 = strip_tags($text);
                    if($this->CheckFindPropsOffer($code, $val, $text1)) {
                        $this->parseCatalogPropOffer($code, $val, $text1);
                    }
                }
            }
            unset($deleteSymb, $deleteSymbRegular, $arProperties, $arProp, $text, $text1, $code, $val);;
        }
        if(isset($this->settings["offer"]["one"]["selector"]) && !empty($this->settings["offer"]["one"]["selector"]) && isset($this->settings["offer"]["add_name"]) && !empty($this->settings["offer"]["add_name"])) {
            $arProperties = $this->settings["offer"]["add_name"];
            $deleteSymb = $this->getOfferDeleteSelector();
            $deleteSymbRegular = $this->getOfferDeleteSelectorRegular();
            $arr = $this->GetArraySrcAttr(trim($this->settings["offer"]["one"]["selector"]));
            $path = $arr["path"];
            $attr = $arr["attr"];
            foreach($arProperties as $code) {
                if($nameOffer === false) {
                    if(!empty($path)) {
                        if(empty($attr))
                            $text = pq($offer)->html();
                        else if(!empty($attr))
                            $text = pq($offer)->attr($attr);
                    }
                }
                else if($nameOffer !== false) {
                    $text = $nameOffer;
                }
                $text = str_replace($deleteSymb, "", $text);
                $text = preg_replace($deleteSymbRegular, "", $text);
                $text1 = strip_tags($text);
                $this->parseCatalogPropOffer($code, "", $text1);
                break 1;
            }
            unset($arProperties, $deleteSymb, $deleteSymbRegular, $arr, $path, $attr, $code, $nameOffer, $text, $text1);
        }
        if(isset($this->settings["offer"]["selector_prop_more"]) && !empty($this->settings["offer"]["selector_prop_more"])) {
            if(!empty($offer) && is_array($offer)) {
                $deleteSymb = $this->getOfferDeleteSelector();
                $deleteSymbRegular = $this->getOfferDeleteSelectorRegular();
                $arProperties = $this->arSelectorPropertiesOffer;
                foreach($offer as $props) {
                    if(array_key_exists($props["code"], $arProperties)) {
                        $arProp = $this->arPropertiesOffer[$props["code"]];
                        $text = $props["value"];
                        if($arProp["USER_TYPE"] != "HTML") {
                            $text = strip_tags($text);
                        }
                        $text = str_replace($deleteSymb, "", $text);
                        $text = preg_replace($deleteSymbRegular, "", $text);
                        $this->parseCatalogPropOffer($props["code"], $arProperties[$props["code"]], $text);
                    }
                }
            }
            unset($deleteSymb, $deleteSymbRegular, $arProperties, $props, $arProp, $text);
        }
    }
    
    protected function getOfferDeleteSelectorRegular() {
        $deleteSymb = array();
        if($this->settings["offer"]["catalog_delete_selector_props_symb"]) {
            $deleteSymb = explode("||", $this->settings["offer"]["catalog_delete_selector_props_symb"]);
            foreach($deleteSymb as $i => &$symb) {
                $symb = trim($symb);
                $symb = htmlspecialcharsBack($symb);
                if(empty($symb)) {
                    unset($deleteSymb[$i]);
                    continue;
                }
                if(!preg_match("/^\//", $symb) || !preg_match("/\/$/", $symb)) {
                    unset($deleteSymb[$i]);
                    continue;
                }
            }
        }
        
        return $deleteSymb;
    }
    
    protected function parseCatalogPropFile($code, $el) {
        if($this->checkUniq() && (!$this->isUpdate || !$this->isUpdate["props"]))
            return false;
        $ar = $this->GetArraySrcAttr($this->settings["catalog"]["selector_prop"][$code]);
        $n = 0;
        $isElement = $this->checkUniq();
        foreach(pq($el)->find($ar["path"]) as $f) {
            if(!empty($ar["attr"])) {
                $src = pq($f)->attr($ar["attr"]);
            }
            else if(empty($ar["attr"])) {
                $src = pq($f)->html();
                $src = strip_tags(pq($f)->html());
            }
            $descr = strip_tags(pq($f)->html());
            $src = $this->parseCaralogFilterSrc($src);
            $src = $this->getCatalogLink($src);
            $this->arFields["PROPERTY_VALUES"][$code]["n".$n]["VALUE"] = $this->MakeFileArray($src);
            $this->arrFilesTemp[] = $this->arFields["PROPERTY_VALUES"][$code]["n".$n]["VALUE"]["tmp_name"];
            $this->arFields["PROPERTY_VALUES"][$code]["n".$n]["DESCRIPTION"] = $descr;
            $n++;
        }
        unset($descr, $src, $f, $n);
        if($isElement) {
            $obElement = new CIBlockElement;
            $rsProperties = $obElement->GetProperty($this->iblock_id, $isElement, "sort", "asc", Array("CODE" => $code));
            while($arProperty = $rsProperties->Fetch()) {
                $this->arFields["PROPERTY_VALUES"][$code][$arProperty["PROPERTY_VALUE_ID"]] = array(
                    "tmp_name" => "",
                    "del" => "Y",
                );
            }
            CIBlockElement::SetPropertyValueCode($isElement, $code, $this->arFields["PROPERTY_VALUES"][$code]);
            unset($obElement, $arProperty, $rsProperties, $this->arFields["PROPERTY_VALUES"][$code]);
        }
    }
    
    public function parseCatalogPropOffer($code, $val, $text) {
        $val = preg_quote($val, "/");
        $text = preg_replace("/(".$val.")/", "", $text, 1);
        $val = trim($text);
        if(empty($val)) {
            unset($val, $text);
            
            return false;
        }
        $val = html_entity_decode($val);
        $arProp = $this->arPropertiesOffer[$code];
        if($arProp["PROPERTY_TYPE"] != "N" && isset($this->settings["loc"]["f_props"]) && $this->settings["loc"]["f_props"])
            $val = $this->locText($val, $arProp["USER_TYPE"] == "HTML" ? "html" : "plain");
        if($arProp["USER_TYPE"] == "HTML" && $arProp["MULTIPLE"] != "Y") {
            $this->arOffer["PROPERTY_VALUES"][$code] = Array(
                "VALUE" => Array(
                    "TEXT" => $val,
                    "TYPE" => "html"
                )
            );
        }
        else if($arProp["USER_TYPE"] == "HTML" && $arProp["MULTIPLE"] == "Y") {
            $this->arOffer["PROPERTY_VALUES"][$code]["n0"] = Array(
                "VALUE" => Array(
                    "TEXT" => $val,
                    "TYPE" => "html"
                )
            );
        }
        else if($arProp["PROPERTY_TYPE"] == "S" && $arProp["MULTIPLE"] != "Y" && $arProp["USER_TYPE"] == "directory") {
            $this->arOffer["PROPERTY_VALUES"][$code] = $this->CheckPropsDirectory($arProp, $code, $val);;
        }
        else if($arProp["PROPERTY_TYPE"] == "S" && $arProp["MULTIPLE"] == "Y" && $arProp["USER_TYPE"] == "directory") {
            $this->arOffer["PROPERTY_VALUES"][$code]["n0"] = $this->CheckPropsDirectory($arProp, $code, $val);;
        }
        else if($arProp["PROPERTY_TYPE"] == "S" && $arProp["MULTIPLE"] != "Y") {
            $this->arOffer["PROPERTY_VALUES"][$code] = $val;
        }
        else if($arProp["PROPERTY_TYPE"] == "S" && $arProp["MULTIPLE"] == "Y") {
            $this->arOffer["PROPERTY_VALUES"][$code]["n0"] = $val;
        }
        else if($arProp["PROPERTY_TYPE"] == "N") {
            $val = str_replace(",", ".", $val);
            $val = preg_replace("/\.{1}$/", "", $val);
            $val = preg_replace('/[^0-9.]/', "", $val);
            $this->arOffer["PROPERTY_VALUES"][$code] = $val;
        }
        else if($arProp["PROPERTY_TYPE"] == "L" && $arProp["MULTIPLE"] != "Y") {
            $this->arOffer["PROPERTY_VALUES"][$code] = $this->CheckPropsLOffer($arProp["ID"], $code, $val);
        }
        else if($arProp["PROPERTY_TYPE"] == "L" && $arProp["MULTIPLE"] == "Y") {
            $this->arOffer["PROPERTY_VALUES"][$code]["n0"] = $this->CheckPropsLOffer($arProp["ID"], $code, $val);
        }
        else if($arProp["PROPERTY_TYPE"] == "E" && $arProp["MULTIPLE"] != "Y") {
            $this->arOffer["PROPERTY_VALUES"][$code] = $this->CheckPropsE($arProp, $code, $val);
        }
        else if($arProp["PROPERTY_TYPE"] == "E" && $arProp["MULTIPLE"] == "Y") {
            $this->arOffer["PROPERTY_VALUES"][$code]["n0"] = $this->CheckPropsE($arProp, $code, $val);
        }
        
        $this->arOfferPropertyValues[$code] = $val;
        
        unset($val, $text, $arProp);
    }
    
    public function CheckPropsLOffer($id, $code, $val) {
        $arRes2 = CIBlockProperty::GetPropertyEnum($id, array(), array(
            "IBLOCK_ID" => $this->iblockOffer,
            "VALUE" => $val
        ))->Fetch();
        if($arRes2) {
            $kz = $arRes2["ID"];
        }
        else {
            $kz = CIBlockPropertyEnum::Add(array(
                "PROPERTY_ID" => $id,
                "VALUE" => $val,
                "TMP_ID" => md5(uniqid(""))
            ));
        }
        unset($arRes2);
        
        return $kz;
    }
    
    protected function getOfferDeleteFindRegular() {
        $deleteSymb = array();
        if($this->settings["offer"]["catalog_delete_selector_find_props_symb"]) {
            $deleteSymb = explode("||", $this->settings["offer"]["catalog_delete_selector_find_props_symb"]);
            foreach($deleteSymb as $i => &$symb) {
                $symb = trim($symb);
                $symb = htmlspecialcharsBack($symb);
                if(empty($symb)) {
                    unset($deleteSymb[$i]);
                    continue;
                }
                if(!preg_match("/^\//", $symb) || !preg_match("/\/$/", $symb)) {
                    unset($deleteSymb[$i]);
                    continue;
                }
            }
        }
        
        return $deleteSymb;
    }
    
    protected function CheckFindPropsOffer($code, $val, $text) {
        $bool = false;
        if(isset($this->arDubleFindPropertiesOffer[$code])) {
            foreach($this->arDubleFindPropertiesOffer[$code] as $prop) {
                $v = $this->arFindPropertiesOffer[$prop];
                if(strpos($text, $v) !== false) {
                    $bool = true;
                }
            }
            unset($prop, $v);
            if($bool)
                return false;
        }
        
        return true;
    }
    
    protected function parseOfferGetUniq() {
        if(isset($this->settings["offer"]["add_name"]) && !empty($this->settings["offer"]["add_name"])) {
            $strV = "";
            $bool = true;
            foreach($this->settings["offer"]["add_name"] as $v) {
                if(isset($this->arOfferPropertyValues[$v])) {
                    if(is_array($this->arOfferPropertyValues[$v])) {
                        foreach($this->arOfferPropertyValues[$v] as $val) {
                            if($bool)
                                $strV .= $val;
                            else
                                $strV .= " / ".$val;
                            $bool = false;
                        }
                    }
                    else {
                        $val = $this->arOfferPropertyValues[$v];
                        if($bool)
                            $strV .= $val;
                        else
                            $strV .= " / ".$val;
                        $bool = false;
                    }
                }
            }
            if(!isset($this->arOffer["NAME"]))
                $this->arOffer["NAME"] = "";
            if($strV)
                $strV = " (".$strV.")";
            if($this->typeN == "catalog")
                $this->arOffer["NAME"] .= $strV;
            if($this->typeN == "xml")
                $this->arOffer["NAME"] = $this->arFields["NAME"].$strV;
            unset($strV, $bool, $val);
            if(!$this->arOffer["NAME"]) {
                $this->errors[] = GetMessage("parser_error_name_notfound_offer");
                
                return false;
            }
        }
        if($this->arOffer["NAME"]) {
            $this->arOffer["XML_ID"] = "offer#".md5($this->arFields["LINK"].$this->arOffer["NAME"]);
        }
     
        return true;
    }
    
    protected function deleteOfferFields() {
        if(isset($this->arOffer))
            unset($this->arOffer);
        if(isset($this->arPriceOffer))
            unset($this->arPriceOffer);
        if(isset($this->arOfferQuantity))
            unset($this->arOfferQuantity);
    }
    
    protected function parseOfferDetailImg($offer) {
        if(isset($this->settings["offer"]["one"]["selector"]) && !empty($this->settings["offer"]["one"]["selector"]) && isset($this->settings["offer"]["add_name"]) && !empty($this->settings["offer"]["add_name"]) && isset($this->settings["offer"]["one"]["detail_img"]) && !empty($this->settings["offer"]["one"]["detail_img"])) {
            $arr = $this->GetArraySrcAttr(trim($this->settings["offer"]["one"]["detail_img"]));
            if(empty($arr["path"]) && !empty($arr["attr"])) {
                $src = pq($offer)->attr($arr["attr"]);
            }
            else if(!empty($arr["path"]) && empty($arr["attr"])) {
                $src = pq($offer)->find($arr["path"])->html();
            }
            else if(!empty($arr["path"]) && !empty($arr["attr"])) {
                $src = pq($offer)->find($arr["path"])->attr($arr["attr"]);
            }
            $src = strip_tags($src);
            $src = $this->parseCaralogFilterSrc($src);
            $src = $this->getCatalogLink($src);
            $this->arOffer["DETAIL_PICTURE"] = $this->MakeFileArray($src);
            $this->arrFilesTemp[] = $this->arOffer["DETAIL_PICTURE"]["tmp_name"];
            unset($src, $arr);
        }
    }
    
    protected function parseOffersSelectorPropMore($el) {
        $deleteSymb = $this->getOfferDeleteSelector();
        $deleteSymbRegular = $this->getOfferDeleteSelectorRegular();
        if(empty($this->arSelectorPropertiesOffer))
            return false;
        $arrPropsAll = array();
        foreach($this->arSelectorPropertiesOffer as $code => $selector) {
            if(empty($selector))
                continue 1;
            $arProp = $this->arPropertiesOffer[$code];
            $arr = $this->GetArraySrcAttr($selector);
            $path = $arr["path"];
            $attr = $arr["attr"];
            $item = 0;
            if(!empty($path)) {
                foreach(pq($el)->find($path) as $valProps) {
                    if(!empty($path) && empty($attr)) {
                        $arrPropsAll[$code][$item]["value"] = trim(pq($valProps)->html());
                    }
                    else if(!empty($path) && !empty($attr)) {
                        $arrPropsAll[$code][$item]["value"] = pq($valProps)->find($path)->attr($attr);
                    }
                    if($arProp["USER_TYPE"] != "HTML") {
                        $arrPropsAll[$code][$item]["value"] = strip_tags($arrPropsAll[$code][$item]["value"]);
                    }
                    $arrPropsAll[$code][$item]["value"] = str_replace($deleteSymb, "", $arrPropsAll[$code][$item]["value"]);
                    $arrPropsAll[$code][$item]["value"] = preg_replace($deleteSymbRegular, "", $arrPropsAll[$code][$item]["value"]);
                    $arrPropsAll[$code][$item]["code"] = $code;
                    $item++;
                }
            }
            else {
                $arrPropsAll[$code][$item]["value"] = pq($el)->attr($attr);
                if($arProp["USER_TYPE"] != "HTML") {
                    $arrPropsAll[$code][$item]["value"] = strip_tags($arrPropsAll[$code][$item]["value"]);
                }
                $arrPropsAll[$code][$item]["value"] = str_replace($deleteSymb, "", $arrPropsAll[$code][$item]["value"]);
                $arrPropsAll[$code][$item]["value"] = preg_replace($deleteSymbRegular, "", $arrPropsAll[$code][$item]["value"]);
                $arrPropsAll[$code][$item]["code"] = $code;
            }
        }
        unset($code, $arr, $path, $attr, $item, $selector, $arProp, $offerPropsAll, $deleteSymb, $deleteSymbRegular);
        if(!isset($arrPropsAll) || empty($arrPropsAll))
            return false;
        
        return $arrPropsAll;
    }
    
    protected function funcX(&$val, &$nm, &$allOfferProps, &$arRes, &$arTemp, $count) {
        $i = 0;
        foreach($allOfferProps as $idProp => $arProp) {
            $i++;
            if($i <= $nm)
                continue 1;
            foreach($arProp as $idP => $prop) {
                $val = $prop["value"];
                $sovp = 0;
                foreach($arTemp as $v) {
                    if($prop["code"] == $v["code"]) {
                        $sovp++;
                        break 1;
                    }
                }
                if($sovp <= 0) {
                    $arTemp[] = $prop;
                }
                $this->funcX($val, $i, $allOfferProps, $arRes, $arTemp, $count);
            }
        }
        if($count == count($arTemp)) {
            $arRes[] = array_slice($arTemp, 0, $count);
        }
        $arTemp = array_slice($arTemp, 0, $nm - 1);
        unset($sovp);
    }
    
    protected function parseAllOffersMoreProps($offers) {
        if(empty($offers) || !is_array($offers))
            return false;
        foreach($offers as $id => $offer) {
            $this->boolOffer = true;
            if($this->parseOfferName($offer)) {
                $this->parseOfferPrice($offer);
                $this->parseOfferQuantity($offer);
                $this->parseOfferProps($offer);
                if(!$this->parseOfferGetUniq()) {
                    $this->deleteOfferFields();;
                    continue 1;
                }
            }
            else
                continue 1;
            $this->arOfferAll["FIELDS"][] = $this->arOffer;
            $this->arOfferAll["PRICE"][] = $this->arPriceOffer;
            $this->arOfferAll["QUANTITY"][] = $this->arOfferQuantity;
            $this->deleteOfferFields();
            unset($offer);
        }
    }
    
    protected function SaveCatalogError() {
        $file = 0;
        if(file_exists(($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/shs.parser/include/count_parser_catalog".$this->id.".txt")))
            $file = file_get_contents($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/shs.parser/include/count_parser_catalog".$this->id.".txt");
        if($file) {
            $arFile = explode("|", $file);
            $countPage = (int)$arFile[1];
            $ciElement = (int)$arFile[2];
            $errorElement = (int)$arFile[3];
            $allError = (int)$arFile[4];
            unset($arFile);
        }
        else {
            $countPage = 0;
            $ciElement = 0;
            $errorElement = 0;
            $allError = 0;
        }
        unset($file);
        if(isset($this->elementID)) {
            if(isset($this->errors) && count($this->errors))
                $errorElement++;
        }
        if(isset($this->errors) && count($this->errors) > 0)
            $allError = $allError + count($this->errors);
        file_put_contents($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/shs.parser/include/count_parser_catalog".$this->id.".txt", "|".$countPage."|".$ciElement."|".$errorElement."|".$allError."|".$this->countSection);
    }
    
    protected function parseAdditionalStores(&$el) {
        if(isset($this->settings['addit_stores']) && !empty($this->settings['addit_stores'])) {
            if($this->checkUniqCsv() && (!$this->isUpdate || !$this->isUpdate["count"]))
                return false;
            foreach($this->settings['addit_stores'] as $id => $store) {
                $selector_count = trim($store['value']);
                if($selector_count != '') {
                    $count = htmlspecialchars_decode($selector_count);
                    $count = $this->GetArraySrcAttr($count);
                    $path = $count["path"];
                    $attr = $count["attr"];
                    if(empty($attr))
                        $count = strip_tags(pq($el)->find($path)->html());
                    else if(!empty($attr))
                        $count = trim(pq($el)->find($path)->attr($attr));
                    $value = $this->findAvailabilityValue($count);
                    if($value)
                        $count = $value['count'];
                    unset($value, $path, $attr);
                    $count = preg_replace('/[^0-9.]/', "", $count);
                }
                else {
                    $this->errors[] = $this->arFields["NAME"].'['.$store['name'].']'.GetMessage("parser_error_count_notfound_csv");
                    continue;
                }
                $this->additionalStore[$id] = $count;
                unset($count, $store);
            }
        }
    }
    
    protected function parserCatalogDetail() {
        if($this->checkUniq() && !$this->isUpdate)
            return false;
        foreach(GetModuleEvents("shs.parser", "parserCatalogDetailBefore", true) as $arEvent)
            ExecuteModuleEventEx($arEvent, array(
                $this->id,
                &$el,
                &$this->arFields
            ));
        $el = $this->parserCatalogDetailPage();
        foreach(GetModuleEvents("shs.parser", "parserCatalogDetail", true) as $arEvent)
            ExecuteModuleEventEx($arEvent, array(
                $this->id,
                &$el,
                &$this->arFields
            ));
        $this->parseCatalogNameDetail($el);
        $this->parseCatalogProperties($el);
        $this->parseCatalogDetailPicture($el);
        $this->parseCatalogDetailMorePhoto($el);
        if($this->isCatalog)
            $this->parseCatalogPriceDetail($el);
        if($this->isCatalog)
            $this->parseCatalogAdittionalPriceDetail($el);
        if($this->isCatalog)
            $this->ParseCatalogAvailableDetail($el);
        $this->parseCatalogDescriptionDetail($el);
        $this->parseAdditionalStores($el);
        $this->parserOffers($el);
        unset($el);
    }
    
    protected function parserCatalogDetailPage() {
        $this->catalogSleep();
        $this->detailFileHtml = new FileGetHtml();
        $this->detailPage = $this->fileHtml->file_get_html($this->arFields["LINK"], $this->proxy, $this->auth, $this);
        foreach(GetModuleEvents("shs.parser", "parserCatalogDetailPageAfter", true) as $arEvent)
            ExecuteModuleEventEx($arEvent, array(&$this));
        $this->DeleteCharsetHtml5($this->detailPage);
        $this->detailHttpCode = $this->fileHtml->httpCode;
        if($this->detailHttpCode != 200 && $this->detailHttpCode != 301 && $this->detailHttpCode != 302 && $this->detailHttpCode != 303) {
            $this->errors[] = $this->arFields["NAME"]."[".$this->arFields["LINK"]."]".GetMessage("parser_error_connect")."[".$this->detailHttpCode."]";
        }
        $this->detailHtml = phpQuery::newDocument($this->fix_utf8.$this->detailPage, "text/html;charset=".LANG_CHARSET);
        $this->base = $this->GetMetaBase($this->detailHtml);
        foreach($this->detailHtml[$this->detail_dom] as $k => $detail)
            return $detail;
        $this->errors[] = GetMessage("parser_error_selecto_detail_notfound");
    }
    
    protected function parseCatalogNameDetail($el) {
        if($this->detail_delete_element)
            $this->deleteCatalogElement($this->detail_delete_element, $this->detail_dom, $this->detailHtml[$this->detail_dom]);
        if($this->detail_delete_attribute)
            $this->deleteCatalogAttribute($this->detail_delete_attribute, $this->detail_dom, $this->detailHtml[$this->detail_dom]);
        if(!isset($this->settings["catalog"]["detail_name"]) || !$this->settings["catalog"]["detail_name"])
            return false;
        $name = htmlspecialchars_decode($this->settings["catalog"]["detail_name"]);
        $this->arFields["NAME"] = htmlspecialchars_decode(trim(pq($el)->find($name)->html()));
        unset($name);
        if($this->arFields["NAME"]) {
            $this->arFields["NAME"] = $this->actionFieldProps("SOTBIT_PARSER_NAME_E", $this->arFields["NAME"]);
            if(isset($this->settings["loc"]["f_name"]) && $this->settings["loc"]["f_name"] == "Y")
                $this->arFields["NAME"] = $this->locText($this->arFields["NAME"]);
        }
        if(!$this->arFields["NAME"]) {
            $this->errors[] = GetMessage("parser_error_name_notfound");
            
            return false;
        }
    }
    
    protected function parseCatalogProperties(&$el) {
        if($this->checkUniq() && !$this->isUpdate)
            return false;
        $this->parseCatalogDefaultProperties($el);
        $this->parseCatalogSelectorProperties($el);
        $this->parseCatalogFindProperties($el);
        $this->AllDoProps();
        if($this->isCatalog) {
            $this->parseCatalogFindProduct($el);
            $this->parseCatalogSelectorProduct($el);
        }
    }
    
    protected function parseCatalogDefaultProperties(&$el) {
        if(isset($this->settings["catalog"]["default_prop"]) && !empty($this->settings["catalog"]["default_prop"])) {
            foreach($this->settings["catalog"]["default_prop"] as $code => $val) {
                if($val)
                    $this->parseCatalogDefaultProp($code, $val);
            }
            unset($code, $val);
        }
    }
    
    public function parseCatalogDefaultProp($code, $val) {
        $val = trim($val);
        if(empty($val))
            return false;
        $arProp = $this->arProperties[$code];
        if($arProp["PROPERTY_TYPE"] == "S" && $arProp["MULTIPLE"] != "Y" && $arProp["USER_TYPE"] == "directory") {
            $this->arFields["PROPERTY_VALUES"][$code] = $val;
        }
        else if($arProp["PROPERTY_TYPE"] == "S" && $arProp["MULTIPLE"] == "Y" && $arProp["USER_TYPE"] == "directory") {
            $this->arFields["PROPERTY_VALUES"][$code]["n0"] = $val;
        }
        else if($arProp["PROPERTY_TYPE"] == "S" && $arProp["MULTIPLE"] != "Y") {
            $this->arFields["PROPERTY_VALUES"][$code] = $val;
        }
        else if($arProp["PROPERTY_TYPE"] == "S" && $arProp["MULTIPLE"] == "Y") {
            $this->arFields["PROPERTY_VALUES"][$code]["n0"] = $val;
        }
        else if($arProp["PROPERTY_TYPE"] == "N") {
            $val = str_replace(",", ".", $val);
            $val = preg_replace("/\.{1}$/", "", $val);
            $val = preg_replace('/[^0-9.]/', "", $val);
            $this->arFields["PROPERTY_VALUES"][$code] = $val;
        }
        else if($arProp["PROPERTY_TYPE"] == "L" && $arProp["MULTIPLE"] != "Y") {
            $this->arFields["PROPERTY_VALUES"][$code] = $val;
        }
        else if($arProp["PROPERTY_TYPE"] == "L" && $arProp["MULTIPLE"] == "Y") {
            $this->arFields["PROPERTY_VALUES"][$code]["n0"] = $val;
        }
        else if($arProp["PROPERTY_TYPE"] == "E" && $arProp["MULTIPLE"] != "Y") {
            $this->arFields["PROPERTY_VALUES"][$code] = $this->CheckPropsE($arProp, $code, $val);
        }
        else if($arProp["PROPERTY_TYPE"] == "E" && $arProp["MULTIPLE"] == "Y") {
            $this->arFields["PROPERTY_VALUES"][$code]["n0"] = $this->CheckPropsE($arProp, $code, $val);
        }
        unset($val, $arProp);
    }
    
    protected function parseCatalogSelectorProperties(&$el) {
        $arProperties = $this->arSelectorProperties;
        if(!$arProperties)
            return false;
        if($this->settings["catalog"]["catalog_delete_selector_props_symb"]) {
            $deleteSymb = explode(",", $this->settings["catalog"]["catalog_delete_selector_props_symb"]);
            foreach($deleteSymb as $i => &$symb) {
                $symb = trim($symb);
                $symb = htmlspecialcharsBack($symb);
                if(empty($symb)) {
                    unset($deleteSymb[$i]);
                    continue;
                }
                if($symb == "\\\\") {
                    $deleteSymb[$i] = ",";
                }
            }
        }
        $this->property_filter = false;
        foreach($arProperties as $code => $val) {
            if($this->arProperties[$code]["PROPERTY_TYPE"] == "F") {
                $this->parseCatalogPropFile($code, $el);
            }
            else {
                $ar = $this->GetArraySrcAttr(htmlspecialchars_decode($this->settings["catalog"]["selector_prop"][$code]));
                if($ar["attr"] && strpos($ar["attr"], '=') != 0) {
                    $elements = pq($el)->find(htmlspecialchars_decode($this->UtfParams($this->settings["catalog"]["selector_prop"][$code])));
                }
                else {
                    $elements = pq($el)->find($ar["path"]);
                }
                $text = array();
                foreach($elements as $element) {
                    if($ar["attr"] && strpos($ar["attr"], '=') != 0) {
                        $text[] = pq($element)->html();
                    }
                    else if($ar["attr"]) {
                        $text[] = pq($element)->attr($ar["attr"]);
                    }
                    else
                        $text[] = pq($element)->html();
                }
                if($this->arProperties[$code]['MULTIPLE'] !== 'Y' && !empty($text))
                    $text = implode(', ', $text);
                if($this->arProperties[$code]["USER_TYPE"] != "HTML") {
                    if(is_array($text)) {
                        foreach($text as $key => $txt)
                            $text[$key] = strip_tags($text[$key]);
                    }
                    else
                        $text = strip_tags($text);
                }
                if(is_array($text)) {
                    foreach($text as $key => $txt)
                        $text[$key] = str_replace($deleteSymb, "", $text[$key]);
                }
                else
                    $text = str_replace($deleteSymb, "", $text);
                $this->property_filter = $this->parseCatalogProp($code, $val, $text) || $this->property_filter;
            }
        }
        unset($text, $arProperties, $val, $code);
        if(isset($deleteSymb))
            unset($deleteSymb);
        if(isset($symb))
            unset($symb);
    }
    
    protected function parseCatalogFindProperties(&$el) {
        if(!$this->arFindProperties)
            return false;
        $find = htmlspecialchars_decode($this->settings["catalog"]["selector_find_props"]);
        if($this->settings["catalog"]["catalog_delete_selector_find_props_symb"]) {
            $deleteSymb = explode(",", $this->settings["catalog"]["catalog_delete_selector_find_props_symb"]);
            foreach($deleteSymb as $i => &$symb) {
                $symb = trim($symb);
                $symb = htmlspecialcharsBack($symb);
                if(empty($symb)) {
                    unset($deleteSymb[$i]);
                    continue;
                }
                if($symb == "\\\\") {
                    $deleteSymb[$i] = ",";
                }
            }
        }
        $arFind = explode(",", $find);
        foreach($arFind as $vFind) {
            if(strpos($vFind, " br") !== false || strpos($vFind, "<br/>") || strpos($vFind, "<br />")) {
                $vFind = str_replace(array(
                    " br",
                    "<br/>",
                    "<br />"
                ), "", $vFind);
                $vFind = trim($vFind);
                foreach(pq($el)->find($vFind) as $prop) {
                    $arBr = array(
                        "<br>",
                        "<br/>",
                        "<br />"
                    );
                    $text = pq($prop)->html();
                    $text = str_replace($arBr, "<br>", $text);
                    unset($arBr[1]);
                    unset($arBr[2]);
                    foreach($arBr as $br) {
                        $arTextBr = explode($br, $text);
                        if(!empty($arTextBr) && count($arTextBr) > 1)
                            foreach($arTextBr as $textBr) {
                                $textBr = strip_tags($textBr);
                                $textBr = str_replace($deleteSymb, "", $textBr);
                                foreach($this->arFindProperties as $code => $val) {
                                    if($this->CheckFindProps($code, $val, $textBr))
                                        $this->parseCatalogProp($code, $val, $textBr);
                                }
                            }
                    }
                }
            }
            else if(strpos($vFind, ' dl') !== false) {
                foreach(pq($el)->find($vFind) as $prop) {
                    $text = pq($prop)->html();
                    $text = str_replace('</dd>', '<br>', $text);
                    $text = str_replace('</dt>', ' ', $text);
                    $text = strip_tags($text, '<br>');
                    $arTextBr = explode('<br>', $text);
                    if(!empty($arTextBr) && count($arTextBr) > 1) {
                        foreach($arTextBr as $textBr) {
                            foreach($this->arFindProperties as $code => $val) {
                                if($this->CheckFindProps($code, $val, $textBr)) {
                                    $this->parseCatalogProp($code, $val, $textBr);
                                }
                            }
                        }
                    }
                }
            }
            else {
                foreach(pq($el)->find($vFind) as $prop) {
                    $text = pq($prop)->html();
                    $text = str_replace($deleteSymb, "", $text);
                    foreach($this->arFindProperties as $code => $val) {
                        $text1 = $text;
                        if($this->arProperties[$code]["USER_TYPE"] != "HTML")
                            $text1 = strip_tags($text);
                        if($this->CheckFindProps($code, $val, $text1))
                            $this->parseCatalogProp($code, $val, $text1);
                    }
                }
            }
        }
        unset($arFind, $find, $vFind, $prop, $arTextBr, $textBr, $text, $text1);
        if(isset($deleteSymb))
            unset($deleteSymb);
    }
    
    protected function CheckFindProps($code, $val, $text) {
        $bool = false;
        if(isset($this->arDubleFindProperties[$code])) {
            foreach($this->arDubleFindProperties[$code] as $prop) {
                $v = $this->arFindProperties[$prop];
                if(strpos($text, $v) !== false) {
                    $bool = true;
                }
            }
            unset($v, $prop);
            if($bool)
                return false;
        }
        if(strpos($text, $val) !== false)
            return true;
        else
            return false;
    }
    
    protected function AllDoProps() {
        if($this->checkUniq() && (!$this->isUpdate || !$this->isUpdate["props"]))
            return false;
        $isElement = $this->checkUniq();
        if($isElement) {
            $obElement = new CIBlockElement;
            $rsProperties = $obElement->GetProperty($this->iblock_id, $isElement, "sort", "asc");
            while($arProperty = $rsProperties->Fetch()) {
                if(isset($this->arFields["PROPERTY_VALUES"][$arProperty["CODE"]]) || $arProperty["PROPERTY_TYPE"] == "F")
                    continue;
                $this->arFields["PROPERTY_VALUES"][$arProperty["ID"]][$arProperty['PROPERTY_VALUE_ID']] = array(
                    "VALUE" => $arProperty['VALUE'],
                    "DESCRIPTION" => $arProperty["DESCRIPTION"]
                );
            }
            unset($obElement, $rsProperties, $arProperty);
        }
        unset($isElement);
    }
    
    protected function parseCatalogFindProduct(&$el) {
        $arProperties = $this->arFindProduct;
        if(!$arProperties)
            return false;
        if($this->checkUniq() && (!$this->isUpdate || !$this->isUpdate["param"]))
            return false;
        $find = htmlspecialchars_decode($this->settings["catalog"]["selector_find_size"]);
        if($this->settings["catalog"]["catalog_delete_find_symb"]) {
            $deleteSymb = explode(",", $this->settings["catalog"]["catalog_delete_find_symb"]);
            foreach($deleteSymb as $i => &$symb) {
                $symb = trim($symb);
                $symb = htmlspecialcharsBack($symb);
                if(empty($symb)) {
                    unset($deleteSymb[$i]);
                    continue;
                }
                if($symb == "\\\\") {
                    $deleteSymb[$i] = ",";
                }
            }
        }
        foreach(pq($el)->find($find) as $prop) {
            $text = pq($prop)->html();
            $text = strip_tags($text);
            $text = str_replace($deleteSymb, "", $text);
            foreach($arProperties as $code => $val) {
                if(strpos($text, $val) !== false) {
                    $text = str_replace($val, "", $text);
                    $text = trim($text);
                    $text = str_replace(",", ".", $text);
                    $text = preg_replace("/\.{1}$/", "", $text);
                    $text = preg_replace('/[^0-9.]/', "", $text);
                    if(isset($this->settings["catalog"]["find_product_koef"][$code]) && !empty($this->settings["catalog"]["find_product_koef"][$code])) {
                        $text = $text * $this->settings["catalog"]["find_product_koef"][$code];
                    }
                    $this->arProduct[$code] = $text;
                }
            }
        }
        unset($prop, $text, $arProperties, $find, $val, $code);
        if(isset($deleteSymb))
            unset($deleteSymb);
    }
    
    protected function parseCatalogSelectorProduct(&$el) {
        $arProperties = $this->arSelectorProduct;
        if(!$arProperties)
            return false;
        if($this->checkUniq() && (!$this->isUpdate || !$this->isUpdate["param"]))
            return false;
        if($this->settings["catalog"]["catalog_delete_selector_symb"]) {
            $deleteSymb = explode(",", $this->settings["catalog"]["catalog_delete_selector_symb"]);
            foreach($deleteSymb as $i => &$symb) {
                $symb = trim($symb);
                $symb = htmlspecialcharsBack($symb);
                if(empty($symb)) {
                    unset($deleteSymb[$i]);
                    continue;
                }
                if($symb == "\\\\") {
                    $deleteSymb[$i] = ",";
                }
            }
        }
        foreach($arProperties as $code => $val) {
            $text = pq($el)->find(htmlspecialchars_decode($this->settings["catalog"]["selector_product"][$code]))->html();
            $text = strip_tags($text);
            $text = str_replace($deleteSymb, "", $text);
            $text = trim($text);
            $text = str_replace(",", ".", $text);
            $text = preg_replace("/\.{1}$/", "", $text);
            $text = preg_replace('/[^0-9.]/', "", $text);
            if(isset($this->settings["catalog"]["selector_product_koef"][$code]) && !empty($this->settings["catalog"]["selector_product_koef"][$code])) {
                $text = $text * $this->settings["catalog"]["selector_product_koef"][$code];
            }
            $this->arProduct[$code] = $text;
        }
        unset($text, $arProperties, $find, $val, $code, $symb);
        if(isset($deleteSymb))
            unset($deleteSymb);
    }
    
    public function parseCatalogDetailPicture(&$el)
    {
        if($this->checkUniq() && (!$this->isUpdate || (!$this->isUpdate["detail_img"] && (!$this->isUpdate["preview_img"] && !$this->settings["catalog"]["img_preview_from_detail"] != "Y"))))
            return false;
        if($this->settings["catalog"]["detail_picture"]) {
            $arSelPic = explode(",", $this->settings["catalog"]["detail_picture"]);
            foreach($arSelPic as $sel) {
                $sel = trim($sel);
                if(empty($sel))
                    continue;
                $ar = $this->GetArraySrcAttr($sel);
                if(!empty($ar["attr"])) {
                    $src = pq($el)->find($ar["path"])->attr($ar["attr"]);
                    $src = $this->parseSelectorStyle($ar["attr"], $src);
                }
                else if(empty($ar["attr"])) {
                    $src = pq($el)->find($ar["path"])->text();
                }
                $src = $this->parseCaralogFilterSrc($src);
                $src = $this->getCatalogLink($src);
                foreach(GetModuleEvents("shs.parser", "ParserDetailPicture", true) as $arEvent)
                    ExecuteModuleEventEx($arEvent, array(
                        &$this,
                        &$src
                    ));
                
                $this->arPhoto[$src] = 1;
                $this->arFields["DETAIL_PICTURE"] = $this->MakeFileArray($src);
                
                if(!self::CheckImage($this->arFields["DETAIL_PICTURE"]["tmp_name"])) {
                    unset($this->arFields["DETAIL_PICTURE"]["tmp_name"]);
                    unset($this->arPhoto[$src]);
                    continue;
                }
                
                $this->arrFilesTemp[] = $this->arFields["DETAIL_PICTURE"]["tmp_name"];
                if($this->settings["catalog"]["img_preview_from_detail"] == "Y") {
                    $this->arFields["PREVIEW_PICTURE"] = $this->arFields["DETAIL_PICTURE"];
                }
            }
            unset($arSelPic, $sel, $ar, $src);
        }
    }
    
    protected function parseCatalogDetailMorePhoto(&$el) {
        if($this->settings["catalog"]["more_image_props"]) {
            if($isElement = $this->checkUniq() && (!$this->isUpdate || !$this->isUpdate["more_img"]))
                return false;
            $isElement = $this->checkUniq();
            $code = $this->settings["catalog"]["more_image_props"];
            $ar = $this->GetArraySrcAttr($this->settings["catalog"]["more_image"]);
            $image = $ar["path"];
            $attr = $ar["attr"];
            $n = 0;
            foreach(pq($el)->find($image) as $img) {
                if(!empty($attr)) {
                    $src = pq($img)->attr($attr);
                    $src = $this->parseSelectorStyle($attr, $src);
                }
                else if(empty($attr)) {
                    $src = strip_tags(pq($img)->html());
                }
                $src = $this->parseCaralogFilterSrc($src);
                $src = $this->getCatalogLink($src);
                foreach(GetModuleEvents("shs.parser", "ParserDetailMorePhoto", true) as $arEvent) 
                    ExecuteModuleEventEx($arEvent, array(
                        &$this,
                        &$src
                    ));
                
                if(isset($this->arPhoto[$src]))
                    continue 1;
                
                if(empty($src))
                    return;
                
                $this->arPhoto[$src] = 1;
                $this->arFields["PROPERTY_VALUES"][$code]["n".$n]["VALUE"] = $this->MakeFileArray($src);
                $this->arrFilesTemp[] = $this->arFields["PROPERTY_VALUES"][$code]["n".$n]["VALUE"]["tmp_name"];
                $this->arFields["PROPERTY_VALUES"][$code]["n".$n]["DESCRIPTION"] = "";
                $n++;
            }
            unset($ar, $image, $attr, $n);
            if($isElement) {
                $obElement = new CIBlockElement;
                $rsProperties = $obElement->GetProperty($this->iblock_id, $isElement, "sort", "asc", Array("CODE" => $code));
                while($arProperty = $rsProperties->Fetch()) {
                    $this->arFields["PROPERTY_VALUES"][$code][$arProperty["PROPERTY_VALUE_ID"]] = array(
                        "tmp_name" => "",
                        "del" => "Y",
                    );
                }
                CIBlockElement::SetPropertyValueCode($isElement, $code, $this->arFields["PROPERTY_VALUES"][$code]);
                unset($obElement, $rsProperties, $arProperty, $this->arFields["PROPERTY_VALUES"][$code]);
            }
        }
    }
    
    protected function parseCatalogPriceDetail(&$el) {
        if($this->settings["catalog"]["detail_price"]) {
            if($this->checkUniq() && (!$this->isUpdate || !$this->isUpdate["price"]))
                return false;
            $price = htmlspecialchars_decode($this->settings["catalog"]["detail_price"]);
            $arr = $this->GetArraySrcAttr(trim($price));
            if(empty($arr["path"]) && !empty($arr["attr"])) {
                $price = pq($el)->attr($arr["attr"]);
            }
            else if(!empty($arr["path"]) && empty($arr["attr"])) {
                $price = strip_tags(pq($el)->find($arr["path"])->html());
            }
            else if(!empty($arr["path"]) && !empty($arr["attr"])) {
                $price = pq($el)->find($arr["path"])->attr($arr["attr"]);
            }
            $price = $this->parseCatalogPriceFormat($price);
            $this->arPrice["PRICE"] = $price;
            $this->arPrice["PRICE"] = trim($this->arPrice["PRICE"]);
            unset($price);
            if(!$this->arPrice["PRICE"]) {
                $this->errors[] = $this->arFields["NAME"]."[".$this->arFields["LINK"]."]".GetMessage("parser_error_price_notfound");
                unset($this->arPrice["PRICE"]);
                
                return false;
            }
            $this->arPrice["CATALOG_GROUP_ID"] = $this->settings["catalog"]["price_type"];
            $this->arPrice["CURRENCY"] = $this->settings["catalog"]["currency"];
        }
    }
    
    protected function parseCatalogAdittionalPriceDetail(&$el) {
        if($this->settings["prices_detail"] && !empty($this->settings["prices_detail"])) {
            if($this->checkUniq() && (!$this->isUpdate || !$this->isUpdate["price"]))
                return false;
            foreach($this->settings["prices_detail"] as $id_price => $price_arr) {
                $addit_price = array();
                $price = htmlspecialchars_decode($price_arr['value']);
                $price = $this->GetArraySrcAttr($price);
                if(empty($price["attr"]))
                    $price = strip_tags(pq($el)->find($price["path"])->html());
                else if(!empty($price["attr"]))
                    $price = trim(pq($el)->find($price["path"])->attr($price["attr"]));
                $price = $this->parseCatalogPriceFormat($price);
                $addit_price["PRICE"] = $price;
                $addit_price["PRICE"] = trim($addit_price["PRICE"]);
                if(!$addit_price["PRICE"]) {
                    $this->errors[] = $this->arFields["NAME"]."[".$this->arFields["LINK"]."] ".$price_arr['name'].GetMessage("parser_error_price_notfound");
                    unset($addit_price["PRICE"]);
                    
                    return false;
                }
                $addit_price["CATALOG_GROUP_ID"] = $id_price;
                $addit_price['CURRENCY'] = $this->settings['adittional_currency'][$id_price];
                $this->arAdditionalPrice[$id_price] = $addit_price;
            }
            unset($price, $addit_price, $price_arr, $id_price);
        }
    }
    
    protected function parseCatalogDescriptionDetail(&$el) {
        if($this->checkUniq() && (!$this->isUpdate || (!$this->isUpdate["detail_descr"] && (!$this->isUpdate["preview_descr"] && !$this->settings["catalog"]["text_preview_from_detail"] != "Y"))))
            return false;
        if($this->settings["catalog"]["detail_text_selector"]) {
            $arDetail = explode(",", $this->settings["catalog"]["detail_text_selector"]);
            $detail_text = "";
            if($arDetail && !empty($arDetail)) {
                foreach($arDetail as $detail) {
                    $detail = trim($detail);
                    if(!$detail)
                        continue 1;
                    foreach(pq($el)->find($detail." img") as $img) {
                        $src = pq($img)->attr("src");
                        $src = $this->parseCaralogFilterSrc($src);
                        $src = $this->getCatalogLink($src);
                        $this->parseCatalogSaveImgServer($img, $src);
                    }
                    if($this->bool_detail_delete_tag == "Y")
                        $detail_text .= strip_tags(pq($el)->find($detail)->html(), htmlspecialcharsBack($this->detail_delete_tag));
                    else
                        $detail_text .= pq($el)->find($detail)->html();
                }
                unset($src, $img);
            }
            unset($arDetail, $detail);
            $detail_text = trim($detail_text);
            if(isset($this->settings["loc"]["f_detail_text"]) && $this->settings["loc"]["f_detail_text"] == "Y")
                $detail_text = $this->locText($detail_text, $this->detail_text_type == "html" ? "html" : "plain");
            $this->arFields["DETAIL_TEXT"] = $this->convertDataCharset($detail_text);
            $this->arFields["DETAIL_TEXT_TYPE"] = $this->detail_text_type;
            if($this->settings["catalog"]["text_preview_from_detail"] == "Y") {
                $this->arFields["PREVIEW_TEXT"] = $this->arFields["DETAIL_TEXT"];
                $this->arFields["PREVIEW_TEXT_TYPE"] = $this->arFields["DETAIL_TEXT_TYPE"];
            }
        }
    }
    
    protected function parseCatalogSection() {
        if($this->checkUniq())
            return false;
        if(isset($this->section_array) && !empty($this->section_array)) {
            $IBLOCK_SECTION_ID = $this->GetCatalogSectionId();
            if($IBLOCK_SECTION_ID !== false) {
                $this->arFields["IBLOCK_SECTION_ID"] = $IBLOCK_SECTION_ID;
            }
            else {
                $this->arFields["IBLOCK_SECTION_ID"] = $this->section_id;
            }
            unset($IBLOCK_SECTION_ID);
        }
        else {
            $this->arFields["IBLOCK_SECTION_ID"] = $this->section_id;
        }
    }
    
    protected function GetCatalogSectionId() {
        if(file_exists($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/shs.parser/include/count_parser_section".$this->id.".txt")) {
            $section_id = trim(file_get_contents($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/shs.parser/include/count_parser_section".$this->id.".txt"));
            if(is_numeric($section_id)) {
                return $section_id;
            }
            else
                return false;
        }
        else
            return false;
    }
    
    protected function parseCatalogMeta() {
        if($this->checkUniq())
            return false;
        if($this->meta_description != "N" || $this->meta_keywords != "N") {
            foreach($this->detailHtml["meta"] as $meta) {
                if($this->meta_description != "N" && strtolower(pq($meta)->attr("name")) == "description") {
                    $meta_text = pq($meta)->attr("content");
                    if(!$meta_text)
                        $meta_text = pq($meta)->attr("value");
                    if(strtoupper(LANG_CHARSET) == "WINDOWS-1251")
                        $meta_text = mb_convert_encoding($meta_text, LANG_CHARSET, "utf-8");
                    $this->arFields["PROPERTY_VALUES"][$this->meta_description] = strip_tags($meta_text);
                }
                else if($this->meta_keywords != "N" && strtolower(pq($meta)->attr("name")) == "keywords") {
                    $meta_text = pq($meta)->attr("content");
                    if(!$meta_text)
                        $meta_text = pq($meta)->attr("value");
                    if(strtoupper(LANG_CHARSET) == "WINDOWS-1251")
                        $meta_text = mb_convert_encoding($meta_text, LANG_CHARSET, "utf-8");
                    $this->arFields["PROPERTY_VALUES"][$this->meta_keywords] = strip_tags($meta_text);
                }
            }
            unset($meta_text, $meta);
        }
        if($this->meta_title != "N") {
            $meta_title = pq($this->detailHtml["head:eq(0) title:eq(0)"])->text();
            $meta_title = strip_tags($meta_title);
            if(strtoupper(LANG_CHARSET) == "WINDOWS-1251")
                $meta_title = mb_convert_encoding($meta_title, LANG_CHARSET, "utf-8");
            $this->arFields["PROPERTY_VALUES"][$this->meta_title] = $meta_title;
            unset($meta_title);
        }
    }
    
    protected function parseCatalogFirstUrl() {
        if($this->checkUniq())
            return false;
        if($this->first_title != "N")
            $this->arFields["PROPERTY_VALUES"][$this->first_title] = $this->arFields["LINK"];
    }
    
    protected function parseCatalogDate() {
    }
    
    protected function parseCatalogAllFields() {
        if(isset($this->settings['catalog']['update']['activate']) && $this->settings['catalog']['update']['activate'] == 'Y')
            $this->arFields["ACTIVE"] = 'Y';
        
        if($this->checkUniq())
            return false;
        
        $this->arFields["IBLOCK_ID"] = $this->iblock_id;
        $this->arFields["ACTIVE"] = $this->active_element;
        
        if($this->code_element == "Y")
            $this->arFields["CODE"] = $this->getCodeElement($this->arFields["NAME"]);
        if($this->uniqFields["LINK"]) {
            $this->arFields["XML_ID"] = md5($this->arFields["NAME"].$this->arFields["LINK"]);
            if(empty($this->arFields["CODE"]))
                $this->arFields["CODE"] = $this->arFields["XML_ID"];
        }
        if($this->date_active == "NOW")
            $this->arFields["DATE_ACTIVE_FROM"] = ConvertTimeStamp(time() + CTimeZone::GetOffset(), "SHORT");
        else if($this->date_active == "NOW_TIME")
            $this->arFields["DATE_ACTIVE_FROM"] = ConvertTimeStamp(time() + CTimeZone::GetOffset(), "FULL");
    }
    
    protected function getCodeElement($name) {
        $CODE = CUtil::translit($name, "ru", array(
            "max_len" => $this->arrayIblock["FIELDS"]["CODE"]["DEFAULT_VALUE"]["TRANS_LEN"],
            "change_case" => $this->arrayIblock["FIELDS"]["CODE"]["DEFAULT_VALUE"]["TRANS_CASE"],
            "replace_space" => $this->arrayIblock["FIELDS"]["CODE"]["DEFAULT_VALUE"]["TRANS_SPACE"],
            "replace_other" => $this->arrayIblock["FIELDS"]["CODE"]["DEFAULT_VALUE"]["TRANS_OTHER"],
            "delete_repeat_replace" => $this->arrayIblock["FIELDS"]["CODE"]["DEFAULT_VALUE"]["TRANS_EAT"] == "Y" ? true : false,
        ));
        $arCodes = array();
        $rsCodeLike = CIBlockElement::GetList(array(), array(
            "IBLOCK_ID" => $this->arrayIblock['ID'],
            "CODE" => $CODE."%",
        ), false, false, array(
            "ID",
            "CODE"
        ));
        while($ar = $rsCodeLike->Fetch()) {
            $arCodes[$ar["CODE"]] = $ar["ID"];
        }
        unset($rsCodeLike, $ar);
        if(array_key_exists($CODE, $arCodes)) {
            $i = 1;
            while(array_key_exists($CODE."_".$i, $arCodes)) {
                $i++;
            }
            
            return $CODE."_".$i;
        }
        else {
            return $CODE;
        }
    }
    
    protected function SetCatalogElementsResultPlus() {
        if(file_exists(($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/shs.parser/include/count_parser".$this->id.".txt"))) {
            $file = file_get_contents($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/shs.parser/include/count_parser".$this->id.".txt");
            $arFile = explode("|", $file);
            $arFile[1]++;
            file_put_contents($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/shs.parser/include/count_parser".$this->id.".txt", implode('|', $arFile));
            unset($file, $arFile);
        }
    }
    
    protected function clearFilesTemp() {
        if(!isset($this->arrFilesTemp) || count($this->arrFilesTemp) == 0)
            return false;
        foreach($this->arrFilesTemp as $id => $path) {
            if(file_exists($path)) {
                unlink($path);
            }
        }
    }
    
    protected function AddElementCatalog() {
        if ($this->fix_utf8)  $this->arFields["NAME"] = iconv($this->encoding,SITE_CHARSET, $this->arFields["NAME"]);
        if($this->checkUniq() && !$this->isUpdate)
            return false;
        if($this->GetElementFilter() === false)
            return false;
        $el = new CIBlockElement;
        $isElement = $this->checkUniq();
        $this->boolUpdate = true;
        
        if($this->arFields['AVAILABLE_PREVIEW'] > 0)
            $this->arFields['AVAILABLE_DETAIL'] = $this->arFields['AVAILABLE_PREVIEW'];
        
        if(isset($this->arFields['DETAIL_TEXT_TYPE']) && empty($this->arFields['DETAIL_TEXT_TYPE']))
            $this->arFields['DETAIL_TEXT_TYPE'] = 'html';
        
        if(isset($this->arFields['PREVIEW_TEXT_TYPE']) && empty($this->arFields['PREVIEW_TEXT_TYPE']))
            $this->arFields['PREVIEW_TEXT_TYPE'] = 'html';
        
        if(!$isElement) {
            if($this->settings["catalog"]["update"]["add_element"] == "Y") {
                $this->errors[] = $this->arFields["NAME"]."[".$this->arFields["LINK"]."] - ".GetMessage("parser_error_id_not_add_element");
                
                return false;
            }
            $id = $el->Add($this->arFields, "N", $this->index_element, $this->resize_image);
            //if arFileds[AR_IBLOCK_SECTION_ID] is array and it count more than 1
            //we save element in few categories
            $this->elementByFewCategories($id);
            if($this->settings['smart_log']['enabled'] == 'Y')
                SmartLogs::saveOldValues($isElement, $this->settings['smart_log'], $this->iblock_id, $id);
            if(!$id) {
                $this->errors[] = $this->arFields["NAME"]."[".$this->arFields["LINK"]."] - ".$el->LAST_ERROR;
            }
            else {
                if(isset($this->arFields['PURCHASING_PRICE']) && !empty($this->arFields['PURCHASING_PRICE']))
                    CCatalogProduct::Add(array(
                        'ID' => $id,
                        'PURCHASING_PRICE' => $this->arFields['PURCHASING_PRICE'],
                        'PURCHASING_CURRENCY' => $this->arPrice["CURRENCY"]
                    ));
                $this->elementID = $id;
                $this->addTmp($id);
                $this->addSeoUniqYandex($this->arFields);
            }
        }
        else {
            $this->clearFieldsUpdate();
            $this->elementID = $isElement;
            if($this->settings['smart_log']['enabled'] == 'Y')
                SmartLogs::saveOldValues($isElement, $this->settings['smart_log'], $this->iblock_id, $this->elementID);
            CIBlockElement::SetPropertyValuesEx($isElement, $this->iblock_id, $this->arFields['PROPERTY_VALUES']);
            unset($this->arFields['PROPERTY_VALUES']);
            $el->Update($isElement, $this->arFields, false, true, $this->resize_image);
          
            if(isset($this->arFields['PURCHASING_PRICE']) && !empty($this->arFields['PURCHASING_PRICE']))
                CCatalogProduct::Add(array(
                    'ID' => $isElement,
                    'PURCHASING_PRICE' => $this->arFields['PURCHASING_PRICE'],
                    'PURCHASING_CURRENCY' => $this->arPrice["CURRENCY"]
                ));
            $this->arFields["NAME"] = $this->elementName;
            $this->addTmp($isElement);
            $this->elementByFewCategories($isElement);
        }
        unset($el, $isElement, $id);
    }
    
    protected function GetElementFilter() {
        if($this->settings["catalog"]["section_main_filter"] != "Y" && $this->settings["catalog"]["enable_props_filter"] != "Y") {
            return true;
        }
        if($this->settings["catalog"]["section_main_filter"] == "Y") {
            if(empty($this->settings["catalog"]["section_main"])) {
                $this->errors[] = GetMessage("parser_error_count_matches_categories");
                
                return false;
            }
            if(!in_array($this->arFields["IBLOCK_SECTION_ID"], $this->settings["catalog"]["section_main"])) {
                return false;
            }
        }
        if($this->settings["catalog"]["enable_props_filter"] == "Y") {
            if(isset($this->settings["props_filter_value"]) && count($this->settings["props_filter_value"]) > 0) {
                if(isset($this->propsFilter) && count($this->propsFilter) > 0) {
                    $count = 0;
                    foreach($this->propsFilter as $id => $val) {
                        if($val == "Y") {
                            $count++;
                            break;
                        }
                    }
                    if($count > 0)
                        return true;
                    else
                        return false;
                }
                else {
                    return false;
                }
            }
            else {
                return true;
            }
        }
    }
    
    /**
     * function add element in few categories, id of which saved in variable $this->arFields[AR_IBLOCK_SECTION_ID]
     * if it count more 1
     * @param int $id of element
     * */
    protected function elementByFewCategories($ID) {
        if(sizeof($this->arFields['AR_IBLOCK_SECTION_ID']) > 1) {
            CIBlockElement::SetElementSection($ID, $this->arFields['AR_IBLOCK_SECTION_ID']);
        }
    }
    
    protected function addTmp($ID) {
        if(
            $ID && $this->updateActive && isset($this->settings["catalog"]["uniq"]["action"]) && $this->settings["catalog"]["uniq"]["action"] != "N") {
            $arFields["PARSER_ID"] = $this->id;
            $arFields["PRODUCT_ID"] = $ID;
            if($this->tmp == "b_shs_parser_tmp" || !$this->tmp) {
                \Shs\Parser\ParserTmpTable::add($arFields);
            }
            else if($this->tmp == "b_shs_parser_tmp_old") {
                \Shs\Parser\ParserTmpOldTable::add($arFields);
            }
        }
    }
    
    protected function addSeoUniqYandex($arFields) {
        if(isset($this->settings["loc"]["uniq"]["domain"]) && !empty($this->settings["loc"]["uniq"]["domain"]) && isset($arFields["DETAIL_TEXT"]) && !empty($arFields["DETAIL_TEXT"]) && strlen($arFields["DETAIL_TEXT"]) >= 500) {
            $textContent = $arFields["DETAIL_TEXT"];
            $engine = new Engine\Yandex();
            $domain = $this->settings["loc"]["uniq"]["domain"];
            try {
                $res = $engine->addOriginalText($textContent, $domain);
            }
            catch(Engine\YandexException $e) {
                $this->errors[] = $e->getMessage();
            }
            unset($engine, $textContent, $res, $domain);
        }
    }
    
    protected function clearFieldsUpdate() {
        $this->arEmptyUpdate["PREVIEW_TEXT"] = trim($this->arEmptyUpdate["PREVIEW_TEXT"]);
        $this->arEmptyUpdate["DETAIL_TEXT"] = trim($this->arEmptyUpdate["DETAIL_TEXT"]);
        unset($this->arFields['IBLOCK_SECTION_ID']);
        if($this->checkUniq() && (!$this->isUpdate || !$this->isUpdate["props"])) {
            unset($this->arFields["PROPERTY_VALUES"]);
        }
        if($this->checkUniq() && (!$this->isUpdate || !$this->isUpdate["preview_descr"] || (in_array("PREVIEW_TEXT", $this->arSortUpdate) && !empty($this->arEmptyUpdate["PREVIEW_TEXT"])))) {
            unset($this->arFields["PREVIEW_TEXT"]);
            unset($this->arFields["PREVIEW_TEXT_TYPE"]);
        }
        if($this->checkUniq() && (!$this->isUpdate || !$this->isUpdate["detail_descr"] || (in_array("DETAIL_TEXT", $this->arSortUpdate) && !empty($this->arEmptyUpdate["DETAIL_TEXT"])))) {
            unset($this->arFields["DETAIL_TEXT"]);
            unset($this->arFields["DETAIL_TEXT_TYPE"]);
        }
        if($this->checkUniq() && (!$this->isUpdate || !$this->isUpdate["preview_img"] || (in_array("PREVIEW_PICTURE", $this->arSortUpdate) && !empty($this->arEmptyUpdate["PREVIEW_PICTURE"])))) {
            unset($this->arFields["PREVIEW_PICTURE"]);
        }
        if($this->checkUniq() && (!$this->isUpdate || !$this->isUpdate["detail_img"] || (in_array("DETAIL_PICTURE", $this->arSortUpdate) && !empty($this->arEmptyUpdate["DETAIL_PICTURE"])))) {
            unset($this->arFields["DETAIL_PICTURE"]);
        }
        $this->elementName = $this->arFields["NAME"];
        if($this->checkUniq() && (!$this->isUpdate || !$this->isUpdate["name"])) {
            unset($this->arFields["NAME"]);
        }
        if($this->checkUniq() && (!$this->isUpdate || !$this->isUpdate["price"])) {
            unset($this->arPrice);
        }
    }
    
    protected function AddElementOfferCatalog() {
        if($this->elementUpdate && !$this->isUpdate)
            return false;
        $el = new CIBlockElement;
        $isElement = $this->checkOfferUniq();
        if(!$isElement) {
            $this->arOfferFields["XML_ID"] = "offer#".md5($this->arFields["NAME"].$this->arFields["LINK"]);
            $this->arOfferFields["NAME"] = $this->arFields["NAME"];
            $this->arOfferFields["IBLOCK_ID"] = $this->offerArray["IBLOCK_ID"];
            $this->arOfferFields["PROPERTY_VALUES"][$this->offerArray["SKU_PROPERTY_ID"]] = $this->elementID;
            $id = $el->Add($this->arOfferFields, "N", $this->index_element, $this->resize_image);
            if(!$id) {
                $this->errors[] = GetMessage("parser_offer_name").$this->arOfferFields["NAME"]."[".$this->arFields["LINK"]."] - ".$el->LAST_ERROR;
            }
            else {
                $this->elementOfferID = $id;
                $this->addTmp($id);
                if(isset($this->arFields['PURCHASING_PRICE']) && !empty($this->arFields['PURCHASING_PRICE']))
                    CCatalogProduct::Add(array(
                        'ID' => $id,
                        'PURCHASING_PRICE' => $this->arFields['PURCHASING_PRICE'],
                        'PURCHASING_CURRENCY' => $this->arPrice["CURRENCY"]
                    ));
            }
        }
        else {
            $this->elementOfferID = $isElement;
            $this->addTmp($isElement);
        }
        unset($el, $id, $isElement);
    }
    
    protected function checkOfferUniq() {
        if(isset($this->elementOfferUpdate))
            return $this->elementOfferUpdate;
        $uniq = "offer#".md5($this->arFields["NAME"].$this->arFields["LINK"]);
        $isElement = CIBlockElement::GetList(Array(), array(
            "XML_ID" => $uniq,
            "IBLOCK_ID" => $this->offerArray["IBLOCK_ID"]
        ), false, Array("nTopCount" => 1), array("ID"))->Fetch();
        $this->elementOfferUpdate = $isElement["ID"];
        if($isElement)
            $result = $isElement["ID"];
        else
            $result = false;
        unset($uniq, $isElement);
        
        return $result;
    }
    
    protected function addProductPriceOffers() {
        if(isset($this->arOfferAll)) {
            if(isset($this->arOfferAll["FIELDS"]) && !empty($this->arOfferAll["FIELDS"])) {
                foreach($this->arOfferAll["FIELDS"] as $i => $field) {
                    $this->AddElementOfferCatalogTable($field);
                    $arPrice = $this->arOfferAll["PRICE"][$i];
                    $arAdditPrice = $this->arOfferAll["ADDIT_PRICE"][$i];
                    $arQuantity = $this->arOfferAll["QUANTITY"][$i];
                    $this->arPrice = $arPrice;
                    $this->arAdditionalPrice = $arAdditPrice;
                    $this->AddProductCatalogOffer($field);
                    $this->AddMeasureCatalogOffer($field);
                    $this->AddPriceCatalogOffer($arPrice, $field);
                    $this->AddQuantityCatalogOffer($arQuantity, $field);
                    if($this->settings['smart_log']['enabled'] == 'Y' && $this->elementOfferID) {
                        $arFields = array_merge($this->arOfferAll, array('AVAILABLE_PREVIEW' => $arQuantity["QUANTITY"]));
                        $this->settings['smart_log']['result_id'] = file_get_contents($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/shs.parser/include/result_id".$this->id.".txt");
                        SmartLogs::saveNewValuesOffer($this->elementOfferID, $this->settings["smart_log"], $arFields, $arPrice["PRICE"], $this->arProduct, true);
                    }
                    if(isset($this->elementOfferID))
                        unset($this->elementOfferID);
                    if(isset($this->elementOfferUpdate))
                        unset($this->elementOfferUpdate);
                    unset($arPrice, $arAdditPrice, $arQuantity, $field);
                }
            }
        }
    }
    
    protected function AddElementOfferCatalogTable($arFields) {
        if($this->checkOfferUniqTable($arFields) && !$this->isUpdate)
            return false;
        $el = new CIBlockElement;
        $isElement = $this->checkOfferUniqTable($arFields);
        $arFields["IBLOCK_ID"] = $this->iblockOffer;
        $arFields["PROPERTY_VALUES"][$this->offerArray["SKU_PROPERTY_ID"]] = $this->elementID;
        if(!$isElement) {
            $id = $el->Add($arFields, "N", $this->index_element, $this->resize_image);
            if(!$id) {
                $this->errors[] = GetMessage("parser_offer_name").$arFields["NAME"]."[".$this->arFields["LINK"]."] - ".$el->LAST_ERROR;
            }
            else {
                $this->elementOfferID = $id;
                $this->addTmp($id);
                if(isset($this->arFields['PURCHASING_PRICE']) && !empty($this->arFields['PURCHASING_PRICE']))
                    CCatalogProduct::Add(array(
                        'ID' => $id,
                        'PURCHASING_PRICE' => $this->arFields['PURCHASING_PRICE'],
                        'PURCHASING_CURRENCY' => $this->arPrice["CURRENCY"]
                    ));
                if($this->settings['smart_log']['enabled'] == 'Y')
                    SmartLogs::saveOldValuesOffer($isElement, $this->settings['smart_log'], $this->iblock_id, $id);
            }
        }
        else {
            $this->elementOfferID = $isElement;
            if($this->settings['smart_log']['enabled'] == 'Y')
                SmartLogs::saveOldValuesOffer($isElement, $this->settings['smart_log'], $this->iblock_id, $this->elementOfferID);
            $el->Update($isElement, $arFields);
            if(isset($this->arFields['PURCHASING_PRICE']) && !empty($this->arFields['PURCHASING_PRICE']))
                CCatalogProduct::Add(array(
                    'ID' => $isElement,
                    'PURCHASING_PRICE' => $this->arFields['PURCHASING_PRICE'],
                    'PURCHASING_CURRENCY' => $this->arPrice["CURRENCY"]
                ));
            $this->addTmp($isElement);
        }
        unset($el, $arFields, $isElement);
    }
    
    protected function checkOfferUniqTable($arFields = array()) {
        if(isset($this->elementOfferUpdate))
            return $this->elementOfferUpdate;
        $uniq = "offer#".md5($this->arFields["LINK"].$arFields["NAME"]);
        $isElement = CIBlockElement::GetList(Array(), array(
            "XML_ID" => $uniq,
            "IBLOCK_ID" => $this->offerArray["IBLOCK_ID"]
        ), false, Array("nTopCount" => 1), array("ID"))->Fetch();
        $this->elementOfferUpdate = $isElement["ID"];
        if($isElement)
            $result = $isElement["ID"];
        else
            $result = false;
        unset($uniq, $isElement);
        
        return $result;
    }
    
    protected function AddProductCatalogOffer($arFields) {
        if($this->elementOfferUpdate && (!$this->isUpdate || !$this->isUpdate["param"]))
            return false;
        $this->arProduct["MEASURE"] = $this->settings["catalog"]["measure"];
        $this->arProduct["VAT_ID"] = $this->settings["catalog"]["cat_vat_id"];
        $this->arProduct["VAT_INCLUDED"] = $this->settings["catalog"]["cat_vat_included"];
        $this->arProduct["ID"] = $this->elementOfferID;
        $isElement = $this->elementOfferUpdate;
        if(!$isElement) {
            if(!CCatalogProduct::Add($this->arProduct)) {
                $this->errors[] = $this->arFields["NAME"]." - ".$arFields["NAME"]."[".$this->arFields["LINK"]."] ".GetMessage("parser_error_add_product_offer");
            }
        }
        else {
            $this->UpdateProductCatalogOffer($isElement, $arFields);
        }
        unset($isElement);
    }
    
    protected function UpdateProductCatalogOffer($productID, $arFields) {
        if(!$productID) {
            $this->errors[] = $this->arFields["NAME"]."-".$arFields["NAME"]."[".$this->arFields["LINK"]."] ".GetMessage("parser_error_update_product_offer");
            
            return false;
        }
        CCatalogProduct::Update($productID, $this->arProduct);
    }
    
    protected function AddMeasureCatalogOffer() {
        if($this->elementOfferUpdate)
            return false;
        $info = CModule::CreateModuleObject('catalog');
        if(!CheckVersion("14.0.0", $info->MODULE_VERSION)) {
            if($this->settings["catalog"]["koef"] > 0) {
                $arMes = array(
                    "RATIO" => $this->settings["catalog"]["koef"],
                    "PRODUCT_ID" => $this->elementOfferID
                );
                $str_CAT_MEASURE_RATIO = 1;
                $CAT_MEASURE_RATIO_ID = 0;
                $db_CAT_MEASURE_RATIO = CCatalogMeasureRatio::getList(array(), array("PRODUCT_ID" => $this->elementOfferID));
                if($ar_CAT_MEASURE_RATIO = $db_CAT_MEASURE_RATIO->Fetch()) {
                    $str_CAT_MEASURE_RATIO = $ar_CAT_MEASURE_RATIO["RATIO"];
                    $CAT_MEASURE_RATIO_ID = $ar_CAT_MEASURE_RATIO["ID"];
                }
                if($CAT_MEASURE_RATIO_ID > 0) {
                    if(!CCatalogMeasureRatioAll::Update($CAT_MEASURE_RATIO_ID, $arMes)) {
                        $this->errors[] = $this->arFields["NAME"]."[".$this->arFields["LINK"]."] ".GetMessage("parser_error_ratio");
                    }
                }
                else {
                    if(!CCatalogMeasureRatio::add($arMes)) {
                        $this->errors[] = $this->arFields["NAME"]."[".$this->arFields["LINK"]."] ".GetMessage("parser_error_ratio");
                    }
                }
                unset($arMes, $info, $db_CAT_MEASURE_RATIO, $CAT_MEASURE_RATIO_ID, $str_CAT_MEASURE_RATIO);
            }
        }
    }
    
    protected function AddPriceCatalogOffer($arPrice, $arFields) {
        if($this->elementOfferUpdate && (!$this->isUpdate || !$this->isUpdate["price"]))
            return false;
        $isElement = $this->elementOfferUpdate;
        if($this->arPrice || strlen($this->arPrice["PRICE"]) > 0) {
            $this->arPrice["PRODUCT_ID"] = $this->elementOfferID;
            $this->ChangePrice();
            $this->ConvertCurrency();
            $this->arPrice["PRICE"] = $this->parseCatalogPriceOkrug($this->arPrice["PRICE"]);
            $obPrice = new CPrice();
            if(!$isElement) {
                if(!$obPrice->Add($this->arPrice)) {
                    $this->errors[] = $this->arFields["NAME"]." - ".$arFields["NAME"]."[".$this->arFields["LINK"]."] ".GetMessage("parser_error_add_price_offer").$obPrice->LAST_ERROR;
                }
            }
            else
                $this->UpdatePriceCatalog($isElement);
        }
        if(is_array($this->arAdditionalPrice) && !$isElement) {
            $this->ChangeAdittionalPrice();
            $this->ConvertCurrency();
            foreach($this->arAdditionalPrice as $id_price => $price) {
                $this->arAdditionalPrice[$id_price]['PRODUCT_ID'] = $this->elementOfferID;
                $this->arAdditionalPrice[$id_price]['PRICE'] = $this->parseCatalogPriceOkrug($this->arAdditionalPrice[$id_price]["PRICE"]);
            }
            $obPrice = new CPrice();
            foreach($this->arAdditionalPrice as $arPrice) {
                if(!$obPrice->Add($arPrice)) {
                    $this->errors[] = $this->arFields["NAME"]."[".$this->arFields["LINK"]."][".$arPrice['name']."] ".GetMessage("parser_error_add_price_offer").$obPrice->LAST_ERROR;
                }
            }
        }
        unset($isElement);
        unset($obPrice);
    }
    
    protected function ChangePrice() {
        if(is_array($this->settings["catalog"]["price_updown"]) && count($this->settings["catalog"]["price_updown"]) > 0) {
            foreach($this->settings["catalog"]["price_updown"] as $i => $val) {
                if($this->settings["catalog"]["price_updown"][$i] && $this->settings["catalog"]["price_value"][$i]) {
                    if($this->typeN == "catalog") {
                        if($this->settings["catalog"]["price_updown_section_dop"][$i] != "section_all") {
                            if($current_section = $this->GetCatalogSectionId()) {
                                if($current_section != $this->settings["catalog"]["price_updown_section_dop"][$i]) {
                                    continue 1;
                                }
                            }
                            else {
                                if($this->section_id != $this->settings["catalog"]["price_updown_section_dop"][$i]) {
                                    continue 1;
                                }
                            }
                        }
                    }
                    if($this->settings["catalog"]["price_terms"][$i] == "delta") {
                        if(empty($this->settings["catalog"]["price_terms_value"][$i]) && !empty($this->settings["catalog"]["price_terms_value_to"][$i])) {
                            if($this->arPrice["PRICE"] > $this->settings["catalog"]["price_terms_value_to"][$i])
                                continue;
                        }
                        if(!empty($this->settings["catalog"]["price_terms_value"][$i]) && empty($this->settings["catalog"]["price_terms_value_to"][$i])) {
                            if($this->arPrice["PRICE"] < $this->settings["catalog"]["price_terms_value"][$i])
                                continue;
                        }
                        if(!empty($this->settings["catalog"]["price_terms_value"][$i]) && !empty($this->settings["catalog"]["price_terms_value_to"][$i])) {
                            if($this->arPrice["PRICE"] < $this->settings["catalog"]["price_terms_value"][$i] || $this->arPrice["PRICE"] > $this->settings["catalog"]["price_terms_value_to"][$i])
                                continue;
                        }
                    }
                    if($this->settings["catalog"]["price_type_value"][$i] == "percent") {
                        $delta = $this->arPrice["PRICE"] * $this->settings["catalog"]["price_value"][$i] / 100;
                    }
                    else {
                        $delta = $this->settings["catalog"]["price_value"][$i];
                    }
                    if($this->settings["catalog"]["price_updown"][$i] == "up") {
                        $this->arPrice["PRICE"] += $delta;
                    }
                    else if($this->settings["catalog"]["price_updown"][$i] == "down") {
                        $this->arPrice["PRICE"] -= $delta;
                    }
                }
            }
        }
        else {
            if($this->settings["catalog"]["price_updown"] && $this->settings["catalog"]["price_value"]) {
                if($this->typeN == "catalog") {
                    if($this->settings["catalog"]["price_updown_section_dop"] != "section_all") {
                        if($current_section = $this->GetCatalogSectionId()) {
                            if($current_section != $this->settings["catalog"]["price_updown_section_dop"]) {
                                return false;
                            }
                        }
                        else {
                            if($this->section_id != $this->settings["catalog"]["price_updown_section_dop"]) {
                                return false;
                            }
                        }
                    }
                }
                if($this->settings["catalog"]["price_terms"] == "up" && $this->settings["catalog"]["price_terms_value"]) {
                    if($this->arPrice["PRICE"] < $this->settings["catalog"]["price_terms_value"])
                        return false;
                }
                if($this->settings["catalog"]["price_terms"] == "down" && $this->settings["catalog"]["price_terms_value"]) {
                    if($this->arPrice["PRICE"] > $this->settings["catalog"]["price_terms_value"])
                        return false;
                }
                if($this->settings["catalog"]["price_type_value"] == "percent") {
                    $delta = $this->arPrice["PRICE"] * $this->settings["catalog"]["price_value"] / 100;
                }
                else {
                    $delta = $this->settings["catalog"]["price_value"];
                }
                if($this->settings["catalog"]["price_updown"] == "up") {
                    $this->arPrice["PRICE"] += $delta;
                }
                else if($this->settings["catalog"]["price_updown"] == "down") {
                    $this->arPrice["PRICE"] -= $delta;
                }
            }
        }
    }
    
    protected function ConvertCurrency() {
        if($this->settings["catalog"]["convert_currency"]) {
            $this->arPrice["CURRENCY"] = $this->settings["catalog"]["convert_currency"];
            $this->arPrice["PRICE"] = CCurrencyRates::ConvertCurrency($this->arPrice["PRICE"], $this->settings["catalog"]["currency"], $this->settings["catalog"]["convert_currency"]);
            if(!empty($this->arAdditionalPrice)) {
                foreach($this->arAdditionalPrice as $id => $one_price) {
                    $this->arAdditionalPrice[$id]["CURRENCY"] = $this->settings["catalog"]["convert_currency"];
                    $this->arAdditionalPrice[$id]["PRICE"] = CCurrencyRates::ConvertCurrency($one_price["PRICE"], $one_price["CURRENCY"], $this->settings["catalog"]["convert_currency"]);
                }
            }
        }
    }
    
    public function parseCatalogPriceOkrug($price) {
        $price = trim($price);
        if($price) {
            if(isset($this->settings["catalog"]["price_okrug"])) {
                if($this->settings["catalog"]["price_okrug"] == "up") {
                    if(!isset($this->settings["catalog"]["price_okrug_delta"]) || !$this->settings["catalog"]["price_okrug_delta"])
                        $delta = 0;
                    else
                        $delta = $this->settings["catalog"]["price_okrug_delta"];
                    $price = round($price, $delta);
                }
                else if($this->settings["catalog"]["price_okrug"] == "ceil") {
                    $price = ceil($price);
                }
                else if($this->settings["catalog"]["price_okrug"] == "floor") {
                    $price = floor($price);
                }
            }
        }
        
        return $price;
    }
    
    protected function UpdatePriceCatalog($elementID) {
        if(!$elementID) {
            $this->errors[] = $this->arFields["NAME"]."[".$this->arFields["LINK"]."] ".GetMessage("parser_error_update_price");
            
            return false;
        }
        $res = CPrice::GetList(array(), array(
            "PRODUCT_ID" => $elementID,
            "CATALOG_GROUP_ID" => $this->arPrice["CATALOG_GROUP_ID"]
        ));
        if($arr = $res->Fetch()) {
            CPrice::Update($arr["ID"], $this->arPrice);
            unset($arr);
        }
        else {
            $obPrice = new CPrice();
            if(!$obPrice->Add($this->arPrice)) {
                $this->errors[] = $this->arFields["NAME"]."[".$this->arFields["LINK"]."] ".GetMessage("parser_error_add_price").$obPrice->LAST_ERROR;
            }
        }
        unset($res);
        if(is_array($this->arAdditionalPrice))
            foreach($this->arAdditionalPrice as $id_price => $price) {
                $res = CPrice::GetList(array(), array(
                    "PRODUCT_ID" => $elementID,
                    "CATALOG_GROUP_ID" => $price["CATALOG_GROUP_ID"]
                ));
                if($arr = $res->Fetch()) {
                    CPrice::Update($arr["ID"], $price);
                    unset($arr);
                }
                unset($res);
            }
    }
    
    protected function ChangeAdittionalPrice() {
        if(!is_array($this->arAdditionalPrice))
            return false;
        foreach($this->arAdditionalPrice as $id => $price) {
            if(is_array($this->settings["catalog"]["price_updown"]) && count($this->settings["catalog"]["price_updown"]) > 0) {
                foreach($this->settings["catalog"]["price_updown"] as $i => $val) {
                    if($this->settings["catalog"]["price_updown"][$i] && $this->settings["catalog"]["price_value"][$i]) {
                        if($this->typeN == "catalog") {
                            if($this->settings["catalog"]["price_updown_section_dop"][$i] != "section_all") {
                                if($current_section = $this->GetCatalogSectionId()) {
                                    if($current_section != $this->settings["catalog"]["price_updown_section_dop"][$i]) {
                                        continue 1;
                                    }
                                }
                                else {
                                    if($this->section_id != $this->settings["catalog"]["price_updown_section_dop"][$i]) {
                                        continue 1;
                                    }
                                }
                            }
                        }
                        if($this->settings["catalog"]["price_terms"][$i] == "delta") {
                            if(empty($this->settings["catalog"]["price_terms_value"][$i]) && !empty($this->settings["catalog"]["price_terms_value_to"][$i])) {
                                if($this->arAdditionalPrice[$id]["PRICE"] > $this->settings["catalog"]["price_terms_value_to"][$i])
                                    continue;
                            }
                            if(!empty($this->settings["catalog"]["price_terms_value"][$i]) && empty($this->settings["catalog"]["price_terms_value_to"][$i])) {
                                if($this->arAdditionalPrice[$id]["PRICE"] < $this->settings["catalog"]["price_terms_value"][$i])
                                    continue;
                            }
                            if(!empty($this->settings["catalog"]["price_terms_value"][$i]) && !empty($this->settings["catalog"]["price_terms_value_to"][$i])) {
                                if($this->arAdditionalPrice[$id]["PRICE"] < $this->settings["catalog"]["price_terms_value"][$i] || $this->arAdditionalPrice[$id]["PRICE"] > $this->settings["catalog"]["price_terms_value_to"][$i])
                                    continue;
                            }
                        }
                        if($this->settings["catalog"]["price_type_value"][$i] == "percent") {
                            $delta = $this->arAdditionalPrice[$id]["PRICE"] * $this->settings["catalog"]["price_value"][$i] / 100;
                        }
                        else {
                            $delta = $this->settings["catalog"]["price_value"][$i];
                        }
                        if($this->settings["catalog"]["price_updown"][$i] == "up") {
                            $this->arAdditionalPrice[$id]["PRICE"] += $delta;
                        }
                        else if($this->settings["catalog"]["price_updown"][$i] == "down") {
                            $this->arAdditionalPrice[$id]["PRICE"] -= $delta;
                        }
                    }
                }
            }
            else {
                if($this->settings["catalog"]["price_updown"] && $this->settings["catalog"]["price_value"]) {
                    if($this->typeN == "catalog") {
                        if($this->settings["catalog"]["price_updown_section_dop"] != "section_all") {
                            if($current_section = $this->GetCatalogSectionId()) {
                                if($current_section != $this->settings["catalog"]["price_updown_section_dop"]) {
                                    return false;
                                }
                            }
                            else {
                                if($this->section_id != $this->settings["catalog"]["price_updown_section_dop"]) {
                                    return false;
                                }
                            }
                        }
                    }
                    if($this->settings["catalog"]["price_terms"] == "up" && $this->settings["catalog"]["price_terms_value"]) {
                        if($this->arAdditionalPrice[$id]["PRICE"] < $this->settings["catalog"]["price_terms_value"])
                            return false;
                    }
                    if($this->settings["catalog"]["price_terms"] == "down" && $this->settings["catalog"]["price_terms_value"]) {
                        if($this->arAdditionalPrice[$id]["PRICE"] > $this->settings["catalog"]["price_terms_value"])
                            return false;
                    }
                    if($this->settings["catalog"]["price_type_value"] == "percent") {
                        $delta = $this->arAdditionalPrice[$id]["PRICE"] * $this->settings["catalog"]["price_value"] / 100;
                    }
                    else {
                        $delta = $this->settings["catalog"]["price_value"];
                    }
                    if($this->settings["catalog"]["price_updown"] == "up") {
                        $this->arAdditionalPrice[$id]["PRICE"] += $delta;
                    }
                    else if($this->settings["catalog"]["price_updown"] == "down") {
                        $this->arAdditionalPrice[$id]["PRICE"] -= $delta;
                    }
                }
            }
        }
    }
    
    protected function AddProductCatalog() {
        if($this->elementUpdate && (!$this->isUpdate || !$this->isUpdate["param"]))
            return false;
        $this->arProduct["MEASURE"] = $this->settings["catalog"]["measure"];
        $this->arProduct["VAT_ID"] = $this->settings["catalog"]["cat_vat_id"];
        $this->arProduct["VAT_INCLUDED"] = $this->settings["catalog"]["cat_vat_included"];
        $this->arProduct["ID"] = $this->elementID;
        $isElement = $this->elementUpdate;
        if(!$isElement) {
            if(!CCatalogProduct::Add($this->arProduct)) {
                $this->errors[] = $this->arFields["NAME"]."[".$this->arFields["LINK"]."] ".GetMessage("parser_error_add_product");
            }
        }
        else {
            $this->UpdateProductCatalog($isElement);
        }
    }
    
    protected function UpdateProductCatalog($productID) {
        if(!$productID) {
            $this->errors[] = $this->arFields["NAME"]."[".$this->arFields["LINK"]."] ".GetMessage("parser_error_update_product");
            
            return false;
        }
        CCatalogProduct::Update($productID, $this->arProduct);
    }
    
    protected function AddMeasureCatalog() {
        if($this->elementUpdate)
            return false;
        $info = CModule::CreateModuleObject('catalog');
        if(!CheckVersion("14.0.0", $info->MODULE_VERSION)) {
            if($this->settings["catalog"]["koef"] > 0) {
                $arMes = array(
                    "RATIO" => $this->settings["catalog"]["koef"],
                    "PRODUCT_ID" => $this->elementID
                );
                $str_CAT_MEASURE_RATIO = 1;
                $CAT_MEASURE_RATIO_ID = 0;
                $db_CAT_MEASURE_RATIO = CCatalogMeasureRatio::getList(array(), array("PRODUCT_ID" => $this->elementID));
                if($ar_CAT_MEASURE_RATIO = $db_CAT_MEASURE_RATIO->Fetch()) {
                    $str_CAT_MEASURE_RATIO = $ar_CAT_MEASURE_RATIO["RATIO"];
                    $CAT_MEASURE_RATIO_ID = $ar_CAT_MEASURE_RATIO["ID"];
                }
                if($CAT_MEASURE_RATIO_ID > 0) {
                    if(!CCatalogMeasureRatioAll::Update($CAT_MEASURE_RATIO_ID, $arMes)) {
                        $this->errors[] = $this->arFields["NAME"]."[".$this->arFields["LINK"]."] ".GetMessage("parser_error_ratio");
                    }
                }
                else {
                    if(!CCatalogMeasureRatio::add($arMes)) {
                        $this->errors[] = $this->arFields["NAME"]."[".$this->arFields["LINK"]."] ".GetMessage("parser_error_ratio");
                    }
                }
                unset($db_CAT_MEASURE_RATIO, $CAT_MEASURE_RATIO_ID, $str_CAT_MEASURE_RATIO, $arMes);
            }
        }
        unset($info);
    }
    
    protected function AddPriceCatalog() {
        if($this->elementUpdate && (!$this->isUpdate || !$this->isUpdate["price"]))
            return false;
        $isElement = $this->elementUpdate;
        if($this->arPrice || strlen($this->arPrice["PRICE"]) > 0) {
            $this->arPrice["PRODUCT_ID"] = $this->elementID;
            $this->ChangePrice();
            $this->ConvertCurrency();
            $this->arPrice["PRICE"] = $this->parseCatalogPriceOkrug($this->arPrice["PRICE"]);
            $obPrice = new CPrice();
            if(!$isElement) {
                if(!$obPrice->Add($this->arPrice)) {
                    $this->errors[] = $this->arFields["NAME"]."[".$this->arFields["LINK"]."] ".GetMessage("parser_error_add_price").$obPrice->LAST_ERROR;
                }
            }
            else
                $this->UpdatePriceCatalog($isElement);
        }
        if(is_array($this->arAdditionalPrice)) {
            if(!$isElement) {
                $this->ChangeAdittionalPrice();
                $this->ConvertCurrency();
                foreach($this->arAdditionalPrice as $id_price => $price) {
                    $this->arAdditionalPrice[$id_price]['PRICE'] = $this->parseCatalogPriceOkrug($this->arAdditionalPrice[$id_price]["PRICE"]);
                    $this->arAdditionalPrice[$id_price]["PRODUCT_ID"] = $this->elementID;
                }
                $obPrice = new CPrice();
                foreach($this->arAdditionalPrice as $arPrice) {
                    if(!$obPrice->Add($arPrice)) {
                        $this->errors[] = $this->arFields["NAME"]."[".$this->arFields["LINK"]."][".$arPrice['name']."] ".GetMessage("parser_error_add_price").$obPrice->LAST_ERROR;
                    }
                }
            }
            else
                $this->UpdateAdditionalPrice($isElement);
        }
        unset($isElement);
        unset($obPrice);
    }
    
    protected function UpdateAdditionalPrice($elementID) {
        if(!$elementID) {
            $this->errors[] = $this->arFields["NAME"]."[".$this->arFields["LINK"]."] ".GetMessage("parser_error_update_price");
            
            return false;
        }
        $this->ChangeAdittionalPrice();
        $this->ConvertCurrency();
        foreach($this->arAdditionalPrice as $id_price => $price) {
            $this->arAdditionalPrice[$id_price]['PRICE'] = $this->parseCatalogPriceOkrug($this->arAdditionalPrice[$id_price]["PRICE"]);
            $this->arAdditionalPrice[$id_price]["PRODUCT_ID"] = $this->elementID;
        }
        $res = CPrice::GetList(array(), array(
            "PRODUCT_ID" => $elementID,
            "CATALOG_GROUP_ID" => array_keys($this->arAdditionalPrice)
        ));
        $arAdditionalPrice = $this->arAdditionalPrice;
        while($arr = $res->fetch()) {
            CPrice::Update($arr["ID"], $arAdditionalPrice[$arr['CATALOG_GROUP_ID']]);
            unset($arAdditionalPrice[$arr['CATALOG_GROUP_ID']]);
            unset($arr);
        }
        foreach($arAdditionalPrice as $price) {
            $obPrice = new CPrice();
            if(!$obPrice->Add($price)) {
                $this->errors[] = $this->arFields["NAME"]."[".$this->arFields["LINK"]."] ".GetMessage("parser_error_add_price").$obPrice->LAST_ERROR;
            }
        }
        unset($res);
    }
    
    public function parseStore() {
        if(!$this->elementID || !isset($this->settings['store']['list']) || $this->settings['store']['list'] == '')
            return false;
        $storeId = intval($this->settings['store']['list']);
        $count = CCatalogProduct::GetByID($this->elementID);
        $count = $count['QUANTITY'];
        $this->updateProductStore($this->elementID, $storeId, $count);
        if(!empty($this->additionalStore))
            foreach($this->additionalStore as $storeId => $storeCount)
                $this->updateProductStore($this->elementID, $storeId, $storeCount);
        unset($storeId, $count, $storeCount);
        
        return true;
    }
    
    public function updateProductStore($productId, $storeId, $count) {
        if($productId == '' || $storeId == '')
            return false;
        $id = CCatalogStoreProduct::UpdateFromForm(array(
            "PRODUCT_ID" => $productId,
            "STORE_ID" => $storeId,
            "AMOUNT" => $count,
        ));
        $arInfo = CCatalogSKU::GetInfoByProductIBlock($this->iblock_id);
        if(is_array($arInfo)) {
            $rsOffers = CIBlockElement::GetList(array(), array(
                'IBLOCK_ID' => $arInfo['IBLOCK_ID'],
                'PROPERTY_'.$arInfo['SKU_PROPERTY_ID'] => $productId
            ));
            while($arOffer = $rsOffers->GetNext()) {
                $count = CCatalogProduct::GetByID($arOffer["ID"]);
                $count = $count['QUANTITY'];
                CCatalogStoreProduct::UpdateFromForm(array(
                    "PRODUCT_ID" => $arOffer["ID"],
                    "STORE_ID" => $storeId,
                    "AMOUNT" => $count,
                ));
            }
            unset($rsOffers, $arOffer, $count);
        }
        unset($productId, $storeId, $count, $id, $arInfo);
    }
    
    public function updateQuantity() {
        if(!$this->elementID)
            return false;
        switch($this->settings['store']['available_quantity']) {
            case 'from_stores':
                CCatalogProduct::Update($this->elementID, array(
                    'QUANTITY' => $this->getAmountAllStores($this->elementID),
                ));
                $arInfo = CCatalogSKU::GetInfoByProductIBlock($this->iblock_id);
                if(is_array($arInfo)) {
                    $rsOffers = CIBlockElement::GetList(array(), array(
                        'IBLOCK_ID' => $arInfo['IBLOCK_ID'],
                        'PROPERTY_'.$arInfo['SKU_PROPERTY_ID'] => $this->elementID
                    ));
                    while($arOffer = $rsOffers->GetNext()) {
                        CCatalogProduct::Update($arOffer["ID"], array(
                            "QUANTITY" => $this->getAmountAllStores($arOffer["ID"]),
                        ));
                    }
                }
                $this->arFields["AVAILABLE_PREVIEW"] = $count;
                unset($count, $arInfo);
                break;
        }
    }
    
    public function getAmountAllStores($product_id = null) {
        if($product_id == null)
            return false;
        if(CModule::IncludeModule('catalog')) {
            $arResult = CCatalogProduct::GetByID($product_id);
            $resStore = CCatalogStore::GetList(array(), array("ACTIVE" => "Y"), false, false, array(
                'ID',
                'ACTIVE'
            ));
            while($sklad = $resStore->Fetch()) {
                $stores[] = $sklad['ID'];
            }
            $res = CCatalogStoreProduct::GetList(array(), array(
                "PRODUCT_ID" => $product_id,
                "STORE_ID" => $stores
            ), false, false, array());
            while($arRes = $res->GetNext()) {
                $sum[] = $arRes['AMOUNT'];
            }
            unset($arResult, $resStore, $sklad, $stores);
            
            return ($sum != null) ? array_sum($sum) : 0;
        }
    }
    
    protected function clearHtml() {
        unset($this->html);
    }
    
    protected function parseCatalogPages() {
        global $zis;
        foreach($this->pagenavigation as $id => $page) {
            $this->clearHtml();
            if(isset($this->pagenavigationPrev[$page]) || isset($this->pagenavigationPrev[$id]) || empty($page))
                continue;
            $zis++;
            if($this->currentPage >= self::DEFAULT_DEBUG_LIST && $this->settings["catalog"]["mode"] == "debug")
                return;
            $this->connectCatalogPage($page);
            $this->parseCatalogNavigation($page);
            if($this->IsNumberPageNavigation() && $this->CheckPageNavigation($id)) {
                if($this->type_out != 'HL')
                    $this->parseCatalogProducts();
                else if($this->type_out == 'HL')
                    $this->parseCatalogProductsHL();
            }
            else if(!$this->IsNumberPageNavigation()) {
                if($this->type_out != 'HL')
                    $this->parseCatalogProducts();
                else if($this->type_out == 'HL')
                    $this->parseCatalogProductsHL();
            }
            $i++;
        }
        unset($id, $page);
        foreach($this->pagenavigationPrev as $i => $v) {
            foreach($this->pagenavigation as $i1 => $v1) {
                if($v1 == $v)
                    unset($this->pagenavigation[$i1]);
            }
        }
        unset($i, $v, $i1, $v1);
        if(count($this->pagenavigation) > 0) {
            $this->parseCatalogPages();
        }
    }
    
    protected function prepareRss() {
        $fileDir = $_SERVER['DOCUMENT_ROOT'].'/'.$this->rss;
        if(!is_dir($fileDir)) {
            return false;
        }
        $arFileDir = scandir($fileDir);
        $this->arFiles = array();
        foreach($arFileDir as $file) {
            if(is_file($_SERVER['DOCUMENT_ROOT'].'/'.$this->rss.$file))
                array_push($this->arFiles, $_SERVER['DOCUMENT_ROOT'].'/'.$this->rss.$file);
        }
    }
    
    protected function getUniqElementXls() {
        $this->uniqFields["NAME"] = "NAME";
        if($this->settings["catalog"]["uniq"]["prop"]) {
            unset($this->uniqFields["NAME"]);
            $prop = $this->settings["catalog"]["uniq"]["prop"];
            $this->uniqFields[$prop] = $prop;
        }
        if($this->settings["catalog"]["uniq"]["id"]) {
            unset($this->uniqFields["NAME"]);
            $this->uniqFields["LINK"] = "LINK";
        }
        if($this->settings["catalog"]["uniq"]["xml_id"]) {
            unset($this->uniqFields["NAME"]);
            $this->uniqFields["XML_ID"] = "XML_ID";
        }
        if($this->settings["catalog"]["uniq"]["name"]) {
            $this->uniqFields["NAME"] = "NAME";
        }
    }
    
    protected function isCatalogXlsCatalog() {
        $this->isOfferCatalog = false;
        $this->isOfferParsing = false;
        $this->iblockOffer = 0;
        if(CModule::IncludeModule('catalog') && ($this->iblock_id && CCatalog::GetList(Array("name" => "asc"), Array(
                    "ACTIVE" => "Y",
                    "ID" => $this->iblock_id
                ))->Fetch())) {
            if(((isset($this->settings["catalog"]["preview_price"]) && $this->settings["catalog"]["preview_price"] !== '') || (isset($this->settings["catalog"]["detail_price"]) && $this->settings["catalog"]["detail_price"] !== '') || (isset($this->settings["catalog"]["detail_count"]) && $this->settings["catalog"]["detail_count"] !== "") || (isset($this->settings["catalog"]["preview_count"]) && $this->settings["catalog"]["preview_count"] !== "") || (isset($this->settings["catalog"]["count_default"]) && $this->settings["catalog"]["count_default"] !== "")) || ((isset($this->catalog_detail_settings['settings']["catalog"]["detail_price"]) && $this->catalog_detail_settings['settings']["catalog"]["detail_price"] !== '') || (isset($this->catalog_detail_settings['settings']["catalog"]["detail_count"]) && $this->catalog_detail_settings['settings']["catalog"]["detail_count"] !== ""))) {
                $this->isCatalog = true;
            }
            else
                $this->isCatalog = false;
        }
        else $this->isCatalog = false;
        if(CModule::IncludeModule('catalog') && isset($this->settings["catalog"]["cat_vat_price_offer"]) && $this->settings["catalog"]["cat_vat_price_offer"] == "Y") {
            $arIblock = CCatalogSKU::GetInfoByIBlock($this->iblock_id);
            if(is_array($arIblock) && !empty($arIblock) && $arIblock["PRODUCT_IBLOCK_ID"] != 0 && $arIblock["SKU_PROPERTY_ID"] != 0) {
                $this->isOfferCatalog = true;
                $this->offerArray = $arIblock;
                $this->isCatalog = true;
            }
            else
                $this->isOfferCatalog = false;
        }
        if(CModule::IncludeModule('catalog') && isset($this->settings["offer"]["load"]) && $this->settings["offer"]["load"]) {
            if(!isset($this->settings["catalog"]["cat_vat_price_offer"]) || isset($this->settings["catalog"]["cat_vat_price_offer"]) && $this->settings["catalog"]["cat_vat_price_offer"] != "Y")
                $arIblock = CCatalogSKU::GetInfoByIBlock($this->iblock_id);
            if(is_array($arIblock) && !empty($arIblock) && $arIblock["PRODUCT_IBLOCK_ID"] != 0 && $arIblock["SKU_PROPERTY_ID"] != 0 && $arIblock["IBLOCK_ID"]) {
                $this->offerArray = $arIblock;
                $this->isCatalog = true;
                $this->isOfferParsing = true;
                if($arIblock["IBLOCK_ID"] && $arIblock["PRODUCT_IBLOCK_ID"])
                    $this->iblockOffer = $arIblock["IBLOCK_ID"];
            }
            else
                $this->isOfferParsing = false;
        }
        if(isset($arIblock))
            unset($arIblock);
    }
    
    public function getContentsArray($site = '', $port = 80, $path = '', $query = '') {
        if(!$this->typeN || $this->typeN == "rss") {
            return CIBlockRSS::FormatArray(CIBlockRSS::GetNewsEx($site, $port, $path, $query));
        }
        else if($this->typeN == "page") {
            $url = $site.$path;
            if($query)
                $url = $url."?".$query;
            $fileHtml = new FileGetHtml();
            $data = $fileHtml->file_get_html($url, $this->proxy, $this->auth, $this);
            $this->header_url = $url = $fileHtml->headerUrl;
            $this->DeleteCharsetHtml5($data);
            $html = phpQuery::newDocument($this->fix_utf8.$data, "text/html;charset=".LANG_CHARSET);
            $dom = htmlspecialcharsBack(trim($this->settings["page"]["selector"]));
            $href = htmlspecialcharsBack(trim($this->settings["page"]["href"]));
            $name = htmlspecialcharsBack(trim($this->settings["page"]["name"]));
            $href = $href ? $href : "a:eq(0)";
            $name = $name ? $name : $href;
            $this->base = $this->GetMetaBase($html);
            $i = 0;
            $site = $this->getUrlSite();
            foreach($html[$dom] as $val) {
                $strName = strip_tags(pq($val)->find($name)->html());
                if($name == "a:parent")
                    $strHref = $strName = strip_tags(pq($val)->html());
                else
                    $strName = strip_tags(pq($val)->find($name)->html());
                if($href == "a:parent")
                    $strHref = pq($val)->attr("href");
                else
                    $strHref = pq($val)->find($href)->attr("href");
                $strHref = $this->getPageRssLink($strHref, $path, $site);
                if(empty($strName))
                    $this->errors[] = GetMessage("parser_error_noname");
                if(empty($strHref))
                    $this->errors[] = GetMessage("parser_error_nohref");
                if(empty($strName) || empty($strHref))
                    continue;
                $arContent["item"][$i]["title"] = $strName;
                $arContent["item"][$i]["link"] = $strHref;
                $arContent["item"][$i]["description"] = pq($val)->html();
                $i++;
            }
            if($i > 0) {
                $arContent['title'] = $site;
                $arContent['link'] = $site;
                unset($url, $fileHtml, $data, $html, $dom, $href, $name, $site, $val, $strName, $strHref);
                
                return $arContent;
            }
            unset($url, $fileHtml, $data, $html, $dom, $href, $name, $site, $val, $strName, $strHref, $arContent);
        }
    }
    
    public function getUrlSite() {
        $this->header_url = strtolower($this->header_url);
        $site = str_replace(array(
            'http://',
            "https://",
            'www.',
            "HTTP://",
            "WWW."
        ), "", $this->header_url);
        $site = preg_replace('/\/(.)+/', '', $site);
        $arLevel = explode(".", $site);
        if(preg_match("/https:\//", $this->header_url)) {
            if(count($arLevel) == 2)
                return 'https://www.'.$site;
            else
                return 'https://'.$site;
        }
        else {
            if(count($arLevel) == 2)
                return 'http://www.'.$site;
            else
                return 'http://'.$site;
        }
    }
    
    protected function getPageRssLink($url, $path, $site) {
        $url = trim($url);
        $urlCatalog = $site.$path;
        if(empty($url))
            return false;
        else if(preg_match("/^\/{2}www/", $url)) {
            $url = preg_replace("/^\/{2}www/", "www", $url);
        }
        else if(preg_match("/^\//", $url)) {
            $url = $site.$url;
        }
        else if(!preg_match("/^\//", $url) && preg_match("/\/{1}$/", $urlCatalog)) {
            if($this->base)
                $url = $this->base.$url;
            else
                $url = $urlCatalog.$url;
        }
        else if(!preg_match("/^\?/", $url) && !preg_match("/^\//", $url) && !preg_match("/\/{1}$/", $urlCatalog)) {
            if($this->base) {
                if(!preg_match("/\/{1}$/", $this->base))
                    $this->base = $this->base."/";
                $url = $this->base.$url;
            }
            else {
                $uri = preg_replace('#/[^/]+$#', '', $urlCatalog);
                $url = $uri."/".$url;
            }
        }
        else if(preg_match("/\?/", $url) && preg_match("/\?/", $urlCatalog)) {
            if(preg_match("/^\?/", $url)) {
                $uri = preg_replace("/\?.+/", "", $urlCatalog);
                $url = $uri.$url;
            }
            else {
                $uri = preg_replace('#/[^/]+$#', '', $urlCatalog);
                $url = $uri."/".$url;
            }
        }
        
        return $url;
    }
    
    private function setContentIblock($arContent = array(), $iblock_id = false, $section_id = false, $detail_dom = "", $encoding = "utf-8") {
        $first = false;
        global $shs_preview, $shs_first, $DB;
        set_time_limit(0);
        $count = count($arContent['item']);
        $ci = 0;
        file_put_contents($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/shs.parser/include/count_parser".$this->id.".txt", $count."|".$ci);
        foreach($arContent['item'] as $i => $item) {
            $item['title'] = trim($item['title']);
            $this->link = $item['link'];
            $item['link'] = str_replace('http://', '', $item['link']);
            $this->convetCyrillic($item['link']);
            if(!isset($this->settings[$this->typeN]["uniq"]) || $this->settings[$this->typeN]["uniq"] == "name") {
                if($item['title'] && isset($this->settings['loc']["f_name"]) && $this->settings['loc']["f_name"] == "Y")
                    $item['title'] = $this->locText($item['title']);
                $isElement = CIBlockElement::GetList(Array(), array(
                    "NAME" => $item['title'],
                    "SECTION_ID" => $section_id,
                    "IBLOCK_ID" => $iblock_id
                ), false, Array("nTopCount" => 1), array("ID"))->Fetch();
            }
            else {
                $md5 = md5($item['link']);
                $isElement = CIBlockElement::GetList(Array(), array(
                    "XML_ID" => $md5,
                    "SECTION_ID" => $section_id,
                    "IBLOCK_ID" => $iblock_id
                ), false, Array("nTopCount" => 1), array("ID"))->Fetch();
            }
            $ci++;
            if($isElement && !self::TEST)
                continue;
            $first = true;
            $item['description'] = trim($item['description']);
            $fileHtml = new FileGetHtml();
            $this->date_public_text = $item["pubDate"];
            $data = $fileHtml->file_get_html(mb_ereg_replace("\n", "", $item['link']), $this->proxy, $this->auth, $this);
            $this->header_url = $fileHtml->headerUrl;
            if($this->first_url && strpos($this->header_url, $this->first_url) === false && strpos($item['link'], $this->first_url) == false)
                continue;
            $shs_first = true;
            $this->DeleteCharsetHtml5($data);
            $html = phpQuery::newDocument($this->fix_utf8.$data, "text/html;charset=".LANG_CHARSET);
            $shs_first = false;
            $this->first_title_text = $this->header_url;
            $this->getUrlSite();
            $DETAIL_TEXT = "";
            $this->text = "";
            $DETAIL_TEXT = $this->parserSelector($html, htmlspecialchars_decode(trim($detail_dom)));
            $el = new CIBlockElement;
            $shs_preview = true;
            if($this->preview_first_img == "Y")
                $PREVIEW_IMG = $this->parserFirstImg(phpQuery::newDocument($this->fix_utf8.$item['description']), "text/html;charset=".LANG_CHARSET);
            $shs_preview = false;
            if($this->detail_first_img == "Y")
                $DETAIL_IMG = $this->parserFirstImg(phpQuery::newDocument($this->fix_utf8.$DETAIL_TEXT), "text/html;charset=".LANG_CHARSET);
            $this->preview_delete_element = trim($this->preview_delete_element);
            $this->detail_delete_element = trim($this->detail_delete_element);
            $shs_preview = true;
            $preview_html = phpQuery::newDocument($this->fix_utf8.$item['description'], "text/html;charset=".LANG_CHARSET);
            $shs_preview = false;
            $detail_html = phpQuery::newDocument($this->fix_utf8.$DETAIL_TEXT, "text/html;charset=".LANG_CHARSET);
            if(!empty($this->preview_delete_element))
                $preview_html = $this->deleteElementStart($preview_html, htmlspecialchars_decode($this->preview_delete_element));
            if(!empty($this->detail_delete_element))
                $detail_html = $this->deleteElementStart($detail_html, htmlspecialchars_decode($this->detail_delete_element));
            if(!empty($this->preview_delete_attribute))
                $preview_html = $this->deleteAttributeStart($preview_html, htmlspecialchars_decode($this->preview_delete_attribute));
            if(!empty($this->detail_delete_attribute))
                $detail_html = $this->deleteAttributeStart($detail_html, htmlspecialchars_decode($this->detail_delete_attribute));
            $detail_html = $this->changeImgSrc($detail_html);
            $preview_html = $this->changeImgSrc($preview_html);
            if($this->preview_save_img == "Y")
                $item['description'] = $this->saveImgServer($preview_html);
            else
                $item['description'] = $preview_html->htmlOuter();
            if($this->detail_save_img == "Y")
                $DETAIL_TEXT = $this->saveImgServer($detail_html);
            else
                $DETAIL_TEXT = $detail_html->htmlOuter();
            $item['description'] = preg_replace("/\<meta(.)+\>{1}/", "", $item['description']);
            $DETAIL_TEXT = preg_replace("/\<meta(.)+\>{1}/", "", $DETAIL_TEXT);
            if($this->code_element == "Y")
                $code = CUtil::translit($item['title'], "ru", array(
                    "max_len" => 100,
                    "change_case" => 'L',
                    "replace_space" => '_',
                    "replace_other" => '_',
                    "delete_repeat_replace" => true,
                ));
            if($this->date_public_text)
                $unix = strtotime($this->date_public_text);
            if($this->date_active == "NOW")
                $date_from = ConvertTimeStamp(time() + CTimeZone::GetOffset(), "SHORT");
            else if($this->date_active == "NOW_TIME")
                $date_from = ConvertTimeStamp(time() + CTimeZone::GetOffset(), "FULL");
            else if($this->date_active == "PUBLIC" && $unix)
                $date_from = ConvertTimeStamp($unix, "FULL");
            if(!empty($this->preview_delete_tag) && $this->bool_preview_delete_tag == "Y")
                $item['description'] = strip_tags($item['description'], htmlspecialchars_decode($this->preview_delete_tag));
            else if($this->bool_preview_delete_tag == "Y")
                $item['description'] = strip_tags($item['description']);
            if(!empty($this->detail_delete_tag) && $this->bool_detail_delete_tag == "Y")
                $DETAIL_TEXT = strip_tags($DETAIL_TEXT, htmlspecialchars_decode($this->detail_delete_tag));
            else if($this->bool_detail_delete_tag == "Y")
                $DETAIL_TEXT = strip_tags($DETAIL_TEXT);
            $item['description'] = trim($item['description']);
            $DETAIL_TEXT = trim($DETAIL_TEXT);
            if($item['title'] && isset($this->settings['loc']["f_name"]) && $this->settings['loc']["f_name"] == "Y" && $this->settings[$this->typeN]["uniq"] == "url")
                $item['title'] = $this->locText($item['title']);
            if($item['description'] && isset($this->settings['loc']["f_preview_text"]) && $this->settings['loc']["f_preview_text"] == "Y")
                $item['description'] = $this->locText($item['description'], $this->preview_text_type == "html" ? "html" : "plain");
            if($DETAIL_TEXT && isset($this->settings['loc']["f_detail_text"]) && $this->settings['loc']["f_detail_text"] == "Y")
                $DETAIL_TEXT = $this->locText($DETAIL_TEXT, $this->detail_text_type == "html" ? "html" : "plain", true);
            $arLoadProductArray = Array(
                "MODIFIED_BY" => 1,
                "IBLOCK_SECTION_ID" => $this->section_id,
                "DATE_ACTIVE_FROM" => $date_from,
                "IBLOCK_ID" => $this->iblock_id,
                "NAME" => $item['title'],
                "ACTIVE" => $this->active_element == "Y" ? "Y" : "N",
                "PREVIEW_TEXT" => $item['description'],
                "PREVIEW_TEXT_TYPE" => $this->preview_text_type,
                "DETAIL_TEXT" => $DETAIL_TEXT,
                "DETAIL_TEXT_TYPE" => $this->detail_text_type,
                "CODE" => $code ? $code : ""
            );
            if(isset($md5)) {
                $arLoadProductArray["XML_ID"] = $md5;
                unset($md5);
            }
            if(empty($PREVIEW_IMG) && $this->preview_first_img == "Y")
                $PREVIEW_IMG = $this->filterSrc($this->parseImgFromRss($item));
            if($this->preview_first_img == "Y") {
                $this->convetCyrillic($PREVIEW_IMG);
                $arLoadProductArray['PREVIEW_PICTURE'] = $this->MakeFileArray($PREVIEW_IMG);
            }
            if($this->detail_first_img == "Y") {
                $this->convetCyrillic($DETAIL_IMG);
                $arLoadProductArray['DETAIL_PICTURE'] = $this->MakeFileArray($DETAIL_IMG);
            }
            if($this->date_public != "N" && $this->date_public_text) {
                $new_date = date($DB->DateFormatToPHP(FORMAT_DATETIME), $unix);
                $arLoadProductArray['PROPERTY_VALUES'][$this->date_public] = $new_date;
            }
            if($this->first_title != "N")
                $arLoadProductArray['PROPERTY_VALUES'][$this->first_title] = $this->first_title_text;
            if($this->meta_title != "N" && $this->meta_title_text) {
                if(isset($this->settings["loc"]["f_props"]) && $this->settings["loc"]["f_props"] == "Y")
                    $this->meta_title_text = $this->locText($this->meta_title_text);
                $arLoadProductArray['PROPERTY_VALUES'][$this->meta_title] = $this->meta_title_text;
            }
            if($this->meta_description != "N" && $this->meta_description) {
                if(isset($this->settings["loc"]["f_props"]) && $this->settings["loc"]["f_props"] == "Y")
                    $this->meta_description = $this->locText($this->meta_description);
                $arLoadProductArray['PROPERTY_VALUES'][$this->meta_description] = $this->meta_description_text;
            }
            if($this->meta_keywords != "N" && $this->meta_keywords) {
                if(isset($this->settings["loc"]["f_props"]) && $this->settings["loc"]["f_props"] == "Y")
                    $this->meta_keywords = $this->locText($this->meta_keywords);
                $arLoadProductArray['PROPERTY_VALUES'][$this->meta_keywords] = $this->meta_keywords_text;
            }
            $this->addSeoUniqYandex($arLoadProductArray);
            
            if($PRODUCT_ID = $el->Add($arLoadProductArray, false, $this->index_element == "Y" ? true : false, $this->resize_image == "Y" ? true : false))
                $elem[] = ' '.$PRODUCT_ID;
            else if(!$this->agent)
                $result[ERROR][] = $el->LAST_ERROR;
            $el = null;
            $isElement = null;
            unset($detail_html, $preview_html, $html, $fileHtml, $data, $unix);
            file_put_contents($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/shs.parser/include/count_parser".$this->id.".txt", $count."|".$ci);
            if(self::TEST)
                break;
            if($this->sleep && $this->sleep > 0)
                sleep($this->sleep);
            if(isset($arLoadProductArray))
                unset($arLoadProductArray);
        }
        unset($arContent, $count);
        unlink($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/shs.parser/include/count_parser".$this->id.".txt");
        if($elem && $first && !$this->agent)
            $result[SUCCESS][] = GetMessage("parser_pars_el_ok").' '.implode(',', $elem).' '.GetMessage("parser_pars_create_ok");
        else if(!$this->agent)
            $result[ERROR][] = GetMessage("parser_no");
        if(!$this->agent) {
            if(isset($result[SUCCESS]) && count($result[SUCCESS]) > 0)
                foreach($result['SUCCESS'] as $success)
                    CAdminMessage::ShowMessage(array(
                        "MESSAGE" => $success,
                        "TYPE" => "OK"
                    ));
            if(isset($result[ERROR]) && count($result[ERROR]) > 0)
                foreach($result['ERROR'] as $error)
                    CAdminMessage::ShowMessage($error);
        }
        
        return $result;
    }
    
    public function parserSelector(&$html, $selector, $nextSelector = 0) {
        global $shs_DOC_ENCODING;
        phpQuery::selectDocument($html);
        if($nextSelector == 0 && $this->meta_description != "N")
            foreach($html['meta'] as $meta) {
                if(strtolower(pq($meta)->attr("name")) == "description") {
                    $this->meta_description_text = pq($meta)->attr("content");
                    if(!$this->meta_description_text)
                        $this->meta_description_text = pq($meta)->attr("value");
                    if(strtoupper(LANG_CHARSET) == "WINDOWS-1251")
                        $this->meta_description_text = mb_convert_encoding($this->meta_description_text, LANG_CHARSET, "utf-8");
                    if($this->meta_description_text) {
                        $this->meta_description_text = strip_tags($this->meta_description_text);
                        break;
                    }
                }
            }
        if($nextSelector == 0 && $this->meta_keywords != "N")
            foreach($html['meta'] as $meta) {
                if(strtolower(pq($meta)->attr("name")) == "keywords") {
                    $this->meta_keywords_text = pq($meta)->attr("content");
                    if(!$this->meta_keywords_text)
                        $this->meta_keywords_text = pq($meta)->attr("value");
                    if(strtoupper(LANG_CHARSET) == "WINDOWS-1251")
                        $this->meta_keywords_text = mb_convert_encoding($this->meta_keywords_text, LANG_CHARSET, "utf-8");
                    if($this->meta_keywords_text) {
                        $this->meta_keywords_text = strip_tags($this->meta_keywords_text);
                        break;
                    }
                }
            }
        if($nextSelector == 0 && $this->meta_title != "N") {
            $this->meta_title_text = pq($html['title'])->text();
            $this->meta_title_text = strip_tags($this->meta_title_text);
            if(strtoupper(LANG_CHARSET) == "WINDOWS-1251") {
                $this->meta_title_text = mb_convert_encoding($this->meta_title_text, LANG_CHARSET, "utf-8");
            }
        }
        if(empty($selector))
            return $html->htmlOuter();
        else
            return '<meta http-equiv="Content-Type" content="text/html;charset='.LANG_CHARSET.'">'.pq($selector)->html();
    }
    
    public function parserFirstImg($html) {
        phpQuery::selectDocument($html);
        $site = $this->getUrlSite();
        foreach($html["img"] as $img) {
            $first_img = $this->filterSrc(pq($img)->attr("src"));
            if(!preg_match('/^http:/', $first_img) && !preg_match('/^www/', $first_img) && !preg_match('/^https:/', $first_img)) {
                if(preg_match("/^\/{1}/", $first_img))
                    $first_img = $site.$first_img;
                else
                    $first_img = $site."/".$first_img;
                $arWidth = getimagesize($first_img);
                if($arWidth[0] < 40)
                    continue;
                unset($site, $arWidth);
                
                return $first_img;
            }
            else {
                $arWidth = getimagesize($first_img);
                if($arWidth[0] < 40)
                    continue;
                unset($site, $arWidth);
                
                return $first_img;
            }
        }
        unset($site);
        
        return $first_img;
    }
    
    public function filterSrc($src) {
        $src = preg_replace('/#.+/', '', $src);
        $src = preg_replace('/\?.+/', '', $src);
        $src = str_replace('//', '/', $src);
        $src = str_replace('http:/', 'http://', $src);
        $src = str_replace('https:/', 'https://', $src);
        if(preg_match("/www\./", $src) || preg_match("/http:\//", $src) || preg_match("/https:\//", $src)) {
            if(preg_match("/https:\//", $src))
                $src = preg_replace("/^\/{2}/", "https://", $src);
            else if(preg_match("/http:\//", $src) || preg_match("/www\./", $src))
                $src = preg_replace("/^\/{2}/", "http://", $src);
        }
        if(preg_match("/www\./", $src) || preg_match("/http:\//", $src) || preg_match("/https:\//", $src)) {
            if(preg_match("/https:\//", $src))
                $src = preg_replace("/^\/{1}/", "https://", $src);
            else if(preg_match("/http:\//", $src) || preg_match("/www\./", $src))
                $src = preg_replace("/^\/{1}/", "http://", $src);
        }
        
        return $src;
    }
    
    public function deleteElementStart(&$html, $selector_delete_element) {
        phpQuery::selectDocument($html);
        $arElements = explode(',', $selector_delete_element);
        foreach($arElements as $selector) {
            if(empty($selector))
                continue;
            $selector = trim($selector);
            $html[$selector]->remove();
        }
        unset($arElements, $selector);
        
        return $html;
    }
    
    public function deleteAttributeStart(&$html, $selector_delete_attribute) {
        $arElements = explode(',', $selector_delete_attribute);
        foreach($arElements as $selector) {
            if(empty($selector))
                continue;
            preg_match('/\[[a-zA-Z]+\]$/', $selector, $attribute);
            $attributes = str_replace(array(
                ']',
                '['
            ), "", $attribute[0]);
            $selector = preg_replace('/\[[a-zA-Z]+\]$/', "", trim($selector));
            $this->deleteAttributes($html, trim($selector), $attributes);
        }
        unset($arElements, $selector, $attributes);
        
        return $html;
    }
    
    public function deleteAttributes(&$html, $selector, $attribute, $nextSelector = 0) {
        phpQuery::selectDocument($html);
        pq($selector)->removeAttr($attribute);
    }
    
    public function changeImgSrc($html) {
        phpQuery::selectDocument($html);
        $site = $this->getUrlSite();
        foreach($html["img"] as $img) {
            $src = $this->filterSrc(pq($img)->attr("src"));
            if(!preg_match('/^http:/', $img->getAttribute('src')) && !preg_match('/^https:/', $img->getAttribute('src')) && !preg_match('/^www/', $img->getAttribute('src')) && !preg_match('/^\/{2}/', $img->getAttribute('src'))) {
                if(preg_match("/^\/{1}/", $src))
                    $src = $site.$src;
                else
                    $src = $site."/".$src;
                $img->setAttribute('src', $src);
            }
            else {
                $img->setAttribute('src', $src);
            }
        }
        
        return $html;
    }
    
    public function saveImgServer($html) {
        foreach($html["img"] as $img) {
            $arImg = $this->MakeFileArray(pq($img)->attr("src"));
            $this->arrFilesTemp[] = $arImg["tmp_name"];
            if(isset($this->albumID) && $this->albumID)
                $this->addAlbumCollection($arImg, $img);
            else {
                $fid = CFile::SaveFile($arImg, "shs.parser");
                $img->setAttribute('src', CFile::GetPath($fid));
            }
        }
        unset($arImg);
        
        return $html->htmlOuter();
    }
    
    public function parseImgFromRss($arItem) {
        foreach($arItem as $item) {
            if(is_array($item))
                $preview = $this->parseImgFromRss($item);
            else if(preg_match("/^(http:)(.)+(jpg|JPG|gif|GIF|png|PNG|JPEG|jpeg)$/", $item, $match) || preg_match("/^(https:)(.)+(jpg|JPG|gif|GIF|png|PNG|JPEG|jpeg)$/", $item, $match)) {
                $preview = $match[0];
                break;
            }
        }
        unset($arItem, $item);
        
        return $preview;
    }
    
    public function GetAuthForm($check = false) {
        if(isset($this->settings[$this->typeN]["auth"]["type"]) && $this->settings[$this->typeN]["auth"]["type"] == "http") {
            return true;
        }
        if(isset($this->settings[$this->typeN]["auth"]["active"]) && $this->settings[$this->typeN]["auth"]["active"] != "Y" || !isset($this->settings[$this->typeN]["auth"]["active"]))
            return false;
        else if(isset($this->settings[$this->typeN]["auth"]["selector"]) && !$this->settings[$this->typeN]["auth"]["selector"]) {
            $this->errors[] = GetMessage("parser_auth_error_selector");
            
            return false;
        }
        $url = $this->settings[$this->typeN]["auth"]["url"] ? $this->settings[$this->typeN]["auth"]["url"] : $this->rss;
        $form = $this->settings[$this->typeN]["auth"]["selector"];
        $auth = new FileGetHtml();
        $data = $auth->file_get_html($url, $this->proxy, false, null, null);
        $this->urlCatalog = $auth->headerUrl;
        $this->urlSite = $this->getCatalogUrlSite();
        $this->CheckAuthForm($data, $form, $this->proxy);
        if($check && isset($_POST["auth"])) {
            if(isset($this->errors) && count($this->errors) > 0) {
                foreach($this->errors as $error)
                    CAdminMessage::ShowMessage($error);
            }
            if(isset($this->success) && count($this->success) > 0) {
                foreach($this->success as $success)
                    CAdminMessage::ShowMessage(array(
                        "MESSAGE" => $success,
                        "TYPE" => "OK"
                    ));
            }
        }
        unset($auth, $data, $url, $form);
    }
    
    public function CheckAuthform($data, $form, $proxy) {
        $this->html = phpQuery::newDocument($this->fix_utf8.$data, "text/html;charset=".LANG_CHARSET);
        $objForm = pq($this->html)->find($form);
        $url = $objForm->attr("action");
        $url = empty($url) ? $this->urlCatalog : $this->getCatalogLink($url);
        $login = trim($this->settings[$this->typeN]["auth"]["login"]);
        $password = trim($this->settings[$this->typeN]["auth"]["password"]);
        foreach($this->html[$form." input"] as $input) {
            $name = trim(pq($input)->attr("name"));
            $value = trim(pq($input)->attr("value"));
            $type = trim(pq($input)->attr("type"));
            if(isset($this->settings[$this->typeN]["auth"]["password_name"]) && !empty($this->settings[$this->typeN]["auth"]["password_name"]) && $name == $this->settings[$this->typeN]["auth"]["password_name"] || $type == "password") {
                $arInput[$name] = $password;
                continue;
            }
            else if(isset($this->settings[$this->typeN]["auth"]["login_name"]) && !empty($this->settings[$this->typeN]["auth"]["login_name"]) && $name == $this->settings[$this->typeN]["auth"]["login_name"] || $type == "text" || $type == "email") {
                $arInput[$name] = $login;
                continue;
            }
            $arInput[$name] = $value;
        }
        if(isset($arInput))
            $this->doAuth($url, $arInput, $proxy);
        else
            $this->errors[] = GetMessage("parser_auth_error_selector");
        unset($arInput, $objForm, $login, $password, $url, $input, $value, $name, $type);
    }
    
    protected function doAuth($url, $arInput, $proxy) {
        $auth = new FileGetHtml();
        $data = $auth->auth($url, $proxy, $arInput, true);
        $code = floor($auth->httpCode / 100);
        
        switch($code) {
            case 1:
            case 2:
            case 3:
                $this->AdminAuth($data);
                break;
            case 4:
                if(in_array($auth->httpCode, array(
                    400,
                    401,
                    403,
                    404
                )))
                    $this->errors[] = str_replace('#CODE#', $auth->httpCode, GetMessage("parser_auth_fail_".$auth->httpCode));
                else
                    $this->errors[] = str_replace('#CODE#', $auth->httpCode, GetMessage("parser_auth_fail_client"));
                break;
            case 5:
                $this->errors[] = str_replace('#CODE#', $auth->httpCode, GetMessage("parser_auth_fail_server"));
                break;
            default:
                $this->errors[] = str_replace('#CODE#', $auth->httpCode, GetMessage("parser_auth_fail"));
        }
        unset($auth, $data);
    }
    
    protected function AdminAuth($data) {
        $form = $this->settings[$this->typeN]["auth"]["selector"];
        $this->html = phpQuery::newDocument($this->fix_utf8.$data, "text/html;charset=".LANG_CHARSET);
        $passw = false;
        foreach($this->html[$form." input"] as $input) {
            $type = pq($input)->attr("type");
            if($type == "password")
                $passw = true;
        }
        if($passw)
            $this->errors[] = GetMessage("parser_auth_no");
        else
            $this->success[] = GetMessage("parser_auth_ok");
        unset($passw, $type, $form);
    }
    
    public function getDirectoryValues($arProp) {
        $nameTable = $arProp["USER_TYPE_SETTINGS"]["TABLE_NAME"];
        if(isset($this->arDirectory[$nameTable]))
            return false;
        $highBlock = \Bitrix\Highloadblock\HighloadBlockTable::getList(array("filter" => array('TABLE_NAME' => $nameTable)))->fetch();
        $entity = \Bitrix\Highloadblock\HighloadBlockTable::compileEntity($highBlock);
        $entityDataClass = $entity->getDataClass();
        $propEnums = $entityDataClass::getList(array(
            'select' => array("*"),
            'order' => array()
        ));
        while($oneEnum = $propEnums->fetch()) {
            $this->arDirectory[$nameTable][$oneEnum["UF_XML_ID"]] = $oneEnum["UF_XML_ID"];
        }
        unset($highBlock, $entity, $entityDataClass, $propEnums, $oneEnum);
    }
    
    public function deletePointUrl($url) {
        $count = substr_count($url, "../");
        if($count > 0)
            for($i = 0; $i < $count; $i++) {
                $url = preg_replace("/[-\w]+\/\.{2}\//", "", $url);
            }
        
        return $url;
    }
    
    public function deleteElements(&$html, $selector, $nextSelector = 0) {
        $arSelector = $this->arraySelector($selector);
        $n = 0;
        if(!isset($arSelector[$nextSelector])) {
            $html->outertext = "";
            unset($arSelector);
            
            return;
        }
        if(strpos($arSelector[$nextSelector], '[') !== false && preg_match("/\[[0-9]{1,3}\]/", $arSelector[$nextSelector])) {
            $sel = $arSelector[$nextSelector];
            $arSelector[$nextSelector] = preg_replace('/\[[0-9]{1,3}\]/', '', $sel);
            preg_match_all('/\[[0-9]{1,3}\]/', $sel, $matches);
            $n = str_replace(array(
                '[',
                ']'
            ), "", $matches[0][0]);
            $item = $html->find($arSelector[$nextSelector], $n);
            if(gettype($item) == "NULL") {
                unset($sel, $arSelector, $n, $item);
                
                return false;
            }
            $this->deleteElements($item, $selector, $nextSelector + 1);
        }
        else {
            foreach($html->find($arSelector[$nextSelector]) as $item)
                $this->deleteElements($item, $selector, $nextSelector + 1);
        }
        unset($arSelector, $n, $item);
    }
    
    public function arraySelector($selector, $debug = 0) {
        $bool = false;
        $selector = trim($selector);
        $arSel = explode(' ', $selector);
        $newArSel = array();
        $selStr = "";
        foreach($arSel as $i => $val) {
            if(preg_match('/\[/', $val) && preg_match('/\]/', $val) && !$bool)
                $newArSel[] = $val;
            else if(!preg_match('/\[/', $val) && !preg_match('/\]/', $val) && !$bool)
                $newArSel[] = $val;
            else if(preg_match('/\[/', $val) && !preg_match('/\]/', $val)) {
                $bool = true;
                $selStr .= $val;
            }
            else if(!preg_match('/\[/', $val) && !preg_match('/\]/', $val) && $bool) {
                $selStr .= " ".$val;
            }
            else if(preg_match('/\]/', $val) && $bool) {
                $selStr .= " ".$val;
                $bool = false;
                $newArSel[] = $selStr;
                $selStr = "";
            }
        }
        unset($selector, $$debug, $bool, $arSel, $selStr, $i, $val);
        
        return $newArSel;
    }
    
    public function get_codepage($text) {
        if(!empty($text)) {
            $utflower = 7;
            $utfupper = 5;
            $lowercase = 3;
            $uppercase = 1;
            $last_simb = 0;
            $charsets = array(
                'UTF-8' => 0,
                'CP1251' => 0,
                'KOI8-R' => 0,
                'IBM866' => 0,
                'ISO-8859-5' => 0,
                'MAC' => 0,
            );
            for($a = 0; $a < strlen($text); $a++) {
                $char = ord($text[$a]);
                // non-russian characters
                if($char < 128 || $char > 256)
                    continue;
                // UTF-8
                if(($last_simb == 208) && (($char > 143 && $char < 176) || $char == 129))
                    $charsets['UTF-8'] += ($utfupper * 2);
                if((($last_simb == 208) && (($char > 175 && $char < 192) || $char == 145)) || ($last_simb == 209 && $char > 127 && $char < 144))
                    $charsets['UTF-8'] += ($utflower * 2);
                // CP1251
                if(($char > 223 && $char < 256) || $char == 184)
                    $charsets['CP1251'] += $lowercase;
                if(($char > 191 && $char < 224) || $char == 168)
                    $charsets['CP1251'] += $uppercase;
                // KOI8-R
                if(($char > 191 && $char < 224) || $char == 163)
                    $charsets['KOI8-R'] += $lowercase;
                if(($char > 222 && $char < 256) || $char == 179)
                    $charsets['KOI8-R'] += $uppercase;
                // IBM866
                if(($char > 159 && $char < 176) || ($char > 223 && $char < 241))
                    $charsets['IBM866'] += $lowercase;
                if(($char > 127 && $char < 160) || $char == 241)
                    $charsets['IBM866'] += $uppercase;
                // ISO-8859-5
                if(($char > 207 && $char < 240) || $char == 161)
                    $charsets['ISO-8859-5'] += $lowercase;
                if(($char > 175 && $char < 208) || $char == 241)
                    $charsets['ISO-8859-5'] += $uppercase;
                // MAC
                if($char > 221 && $char < 255)
                    $charsets['MAC'] += $lowercase;
                if($char > 127 && $char < 160)
                    $charsets['MAC'] += $uppercase;
                $last_simb = $char;
            }
            arsort($charsets);
            unset($utflower, $utfupper, $lowercase, $uppercase, $last_simb, $a, $text, $char, $last_simb);
            
            return key($charsets);
        }
    }
    
    protected function parseAdditionalStoresPreview(&$el) {
        if(isset($this->settings['addit_stores_preview']) && !empty($this->settings['addit_stores'])) {
            if($this->checkUniqCsv() && (!$this->isUpdate || !$this->isUpdate["count"]))
                return false;
            foreach($this->settings['addit_stores'] as $id => $store) {
                $selector_count = trim($store['value']);
                if($selector_count != '') {
                    $count = htmlspecialchars_decode($selector_count);
                    $count = $this->GetArraySrcAttr($count);
                    $path = $count["path"];
                    $attr = $count["attr"];
                    if(empty($attr))
                        $count = strip_tags(pq($el)->find($path)->html());
                    else if(!empty($attr))
                        $count = trim(pq($el)->find($path)->attr($attr));
                    unset($path);
                    unset($attr);
                    $value = $this->findAvailabilityValue($count);
                    if($value)
                        $count = $value['count'];
                    unset($value);
                    $count = preg_replace('/[^0-9.]/', "", $count);
                }
                else {
                    $this->errors[] = $this->arFields["NAME"].'['.$store['name'].']'.GetMessage("parser_error_count_notfound_csv");
                    continue;
                }
                $this->additionalStore[$id] = intval($count);
                unset($store);
                unset($count);
                unset($selector_count);
            }
        }
    }
    
    protected function AddProductCatalogOffers() {
    }
    
    protected function parseCatalogProcurementPrice(&$el) {
        if(!isset($this->settings['catalog']["procurement_price"]) || empty($this->settings['catalog']["procurement_price"]))
            return false;
        if($this->checkUniq() && (!$this->isUpdate || !$this->isUpdate["price"]))
            return false;
        $price = htmlspecialchars_decode($this->settings["catalog"]["procurement_price"]);
        $price = $this->GetArraySrcAttr($price);
        if($price["attr"] && stripos($price["attr"], '=') != 0)
            $price = strip_tags(pq($el)->find($this->UtfParams($this->settings["catalog"]["procurement_price"]))->html());
        else if(empty($price["attr"]))
            $price = strip_tags(pq($el)->find($price["path"])->html());
        else if(!empty($price["attr"]))
            $price = trim(pq($el)->find($price["path"])->attr($price["attr"]));
        $price = $this->parseCatalogPriceFormat($price);
        $this->arFields['PURCHASING_PRICE'] = $price;
    }
    
    protected function searchCatalogNavigation() {
    }
}
?>