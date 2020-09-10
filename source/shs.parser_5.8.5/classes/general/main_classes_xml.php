<?

use Bitrix\Main\Entity;
use Bitrix\Main\IO\Path;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Text\Converter;
use Bitrix\Sale\TradingPlatform\Ebay\CategoryTable;
use Bitrix\Seo\Engine;

\Bitrix\Main\Loader::includeModule('seo');
\Bitrix\Main\Loader::includeModule('socialservices');

class SotbitXmlParser extends SotbitContentParser {
    /*public $id = false;
    public $rss;
    public $typeN;
    public $active;
    public $iblock_id;
    public $section_id;
    public $detail_dom;
    public $encoding;
    public $preview_delete_tag="";
    public $bool_preview_delete_tag="";
    public $detail_delete_tag="";
    public $bool_detail_delete_tag="";
    public $preview_first_img="";
    public $detail_first_img="";
    public $preview_save_img="";
    public $detail_save_img="";
    public $text = "";
    public $site = "";
    public $link = "";
    public $preview_delete_element="";
    public $detail_delete_element="";
    public $preview_delete_attribute="";
    public $detail_delete_attribute="";
    public $index_element="";
    public $resize_image="";
    public $meta_description="";
    public $meta_keywords="";
    public $meta_description_text="";
    public $meta_keywords_text="";
    public $agent = false;
    public $active_element = "Y";
    public $header_url;
    public $settings;
    public $countPage = 0;
    public $countItem = 0;
    public $stepStart = false;
    public $countSection = 0;

    public $page;*/
    const TEST = 0;
    const DEFAULT_DEBUG_ITEM = 3;
    protected $xmlfirst;
    protected $catalog_ids;
    
    public function __construct() {
        parent::__construct();
        $this->xmlfirst = true;
    }
    
    protected function parseXmlCatalog() {
        set_time_limit(0);
        parent::ClearAjaxFiles();
        parent::DeleteLog();
        parent::checkActionBegin();
        $this->arUrl = array();
        
        if(isset($this->settings["catalog"]["url_dop"]) && !empty($this->settings["catalog"]["url_dop"]))
            $this->arUrl = explode("\r\n", $this->settings["catalog"]["url_dop"]);
        
        $this->arUrl = array_merge(array($this->rss), $this->arUrl);
        $this->arUrlSave = $this->arUrl;
        
        if(isset($this->arFiles) && !empty($this->arFiles)) {
            $this->arUrlSave = $this->arFiles;
            $this->arUrl = $this->arFiles;
        }
        
        if($this->settings["part_xml"] == "Y") {
            $this->newXmlUrl();
            unset($this->arrOffersNewXml);
            $this->arUrl = $this->arUrlSave;
            $this->rss = $this->arUrlSave[0];
            //file_put_contents($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/shs.parser/include/".$this->id."/xml_explode.txt",$this->rss." \n");
            if(!is_dir($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/shs.parser/include/".$this->id))
                mkdir($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/shs.parser/include/".$this->id, 0755, true);
            foreach($this->arUrl as $rss)
                file_put_contents($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/shs.parser/include/".$this->id."/xml_explode.txt", $rss."\n", FILE_APPEND);
        }
        if(!$this->PageFromFileXml()) {
            return false;
        }
        parent::CalculateStep();
        if($this->settings["catalog"]["mode"] != "debug" && !$this->agent && $this->type_out != 'HL')
            $this->arUrlSave = array($this->rss);
            
        $this->arUrlSave = $this->arUrl;
        
        foreach($this->arUrlSave as $rss):
            if(!empty($this->errors))
                break;
        
            $rss = trim($rss);
            
            if(empty($rss))
                continue;
            
            $this->rss = $rss;
            parent::convetCyrillic($this->rss);
            parent::connectCatalogPage($this->rss);
            if(!$this->agent && $this->settings["catalog"]["mode"] != "debug" && isset($this->errors) && count($this->errors) > 0) {
                parent::SaveLog();
                unlink($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/shs.parser/include/count_parser_catalog_step".$this->id.".txt");
                unlink($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/shs.parser/include/count_parser_copy_page".$this->id.".txt");
                return false;
            }
            if($this->parseCatalogSectionXml($this->rss) === false && $this->type_out != 'HL') {
                if($this->settings["catalog"]["add_parser_section"] == "Y") {
                    parent::SaveLog();
                    return false;
                }
            }
            $n = $this->currentPage;
            if($this->type_out != 'HL') {
                parent::parseCatalogProducts();
            }
            else if($this->type_out == 'HL') {
                self::parseCatalogProductsHL();
                continue;
            }
            if($this->settings["catalog"]["mode"] != "debug" && !$this->agent) {
                //                $this->stepStart = true;
                parent::SavePrevPage($this->rss);
            }
            parent::SaveCurrentPage($this->pagenavigation);
            if($this->stepStart) {
                if(file_exists($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/shs.parser/include/count_parser_catalog_step".$this->id.".txt"))
                    unlink($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/shs.parser/include/count_parser_catalog_step".$this->id.".txt");
                parent::DeleteCopyPage();
            }
            if((!parent::CheckOnePageNavigation() && $this->agent) || (!parent::CheckOnePageNavigation() && !$this->agent && $this->settings["catalog"]["mode"] == "debug"))
                parent::parseCatalogPages();
            if($this->settings['smart_log']['enabled'] == 'Y') {
                $this->settings['smart_log']['result_id'] = file_get_contents($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/shs.parser/include/result_id".$this->id.".txt");
                $this->settings['smart_log']['result_id'] = \Bitrix\Shs\ParserResultTable::updateEndTime($this->settings['smart_log']['result_id']);
            }
//            if(parent::CheckOnePageNavigation() && $this->stepStart) {
//                if(parent::IsEndSectionUrl()) {
//                    parent::ClearBufferStop();
//                }
//                else parent::ClearBufferStep();
//
//                return false;
//            }
        endforeach;
    
        if($this->type_out == 'HL') {
            parent::ClearBufferStop();
        }
        parent::checkActionAgent($this->agent);
        if($this->agent || $this->settings["catalog"]["mode"] == "debug") {
            foreach(GetModuleEvents("shs.parser", "EndPars", true) as $arEvent)
                ExecuteModuleEventEx($arEvent, array($this->id));
        }
    }
    
    protected function newXmlUrl() {
        if(count($this->arUrlSave) <= 0)
            return false;
        $newUrlXml = array();
        foreach($this->arUrlSave as $url) {
            if(strlen($url) == 0)
                continue 1;
            gc_collect_cycles();
            parent::connectCatalogPage($url, true);
            //$this->page = file_get_contents($url);
            if(isset($this->page) && strlen($this->page) > 0) {
                $offerSelector = explode(' ', $this->settings['catalog']['selector']);
                $offerSelector = $offerSelector[count($offerSelector) - 1] ?: 'offer';
                //                preg_match_all("/\<".$offerSelector."([\s\S]*)?\>([\s\S]*)?\<\/".$offerSelector."([\s\S]*)?\>/i", $this->page, $out, PREG_PATTERN_ORDER);
                $out = preg_split("/\<".$offerSelector."([\s\S]*)?\>([\s\S]*)?\<\/".$offerSelector."([\w]*)?\>/i", $this->page);
                if(count($out) != 2 || empty($out[0]) || empty($out[1]))
                    throw new RuntimeException('Parse xml exception: can not parse xml');
                $offerContainer = explode(' ', $this->settings['catalog']['selector']);
                $offerContainer = $offerContainer[0] ?: 'offers';
                $this->XmlHeader = $out[0].'<'.$offerContainer.'>';
                //                $this->XmlFooter = '</'.$offerContainer.'>'.$out[1];
                $this->XmlFooter = $out[1];
                $out = preg_split('/<'.$offerSelector.'/i', $this->page);
                unset($this->page);
                for($i = 0; $i < count($out); $i++)
                    if(strpos($out[$i], $offerSelector) !== false)
                        $out[$i] = '<'.$offerSelector.((strpos($out[$i], '>' !== 0)) ? ' ' : '').$out[$i];
                    else
                        $out[$i] = '';
                $this->arrOffersNewXml = &$out;
                if(isset($this->arrOffersNewXml) && count($this->arrOffersNewXml) > 0) {
                    if(!isset($this->XmlHeader) || !isset($this->XmlFooter) || (strlen($this->XmlFooter) == 0) || (strlen($this->XmlHeader) == 0)) {
                        continue 1;
                    }
                    if(!$this->startNewFolder()) {
                        break 1;
                    }
                    $countOffers = count($this->arrOffersNewXml);
                    $count = 2000;
                    for($index = 0; $index <= intval($countOffers / $count); $index++) {
                        //write header
                        if(!$this->WriteNewXmlFile($this->XmlHeader, $url, $index, "HEADER"))
                            continue 1;
                        for($i = $index * $count; $i <= $index * $count + $count; $i++) {
                            //need write preg_match
                            if(!empty($this->arrOffersNewXml[$i]) && preg_match("/\<".$offerSelector."([\s\S]*)?\>([\s\S]*)?\<\/".$offerSelector.">/i", $this->arrOffersNewXml[$i], $match))
                                if(!$this->WriteNewXmlFile($match[0], $url, $index)) {
                                    break 1;
                                }
                        }
                        //write footer
                        if($file = $this->WriteNewXmlFile($this->XmlFooter, $url, $index, "FOOTER")) {
                            if(!in_array($file, $newUrlXml)) {
                                $newUrlXml[] = trim($file, '/');
                            }
                        }
                    }
                }
            }
            if(isset($this->page))
                unset($this->page);
        }
        if(count($newUrlXml) > 0) {
            $this->arUrlSave = $newUrlXml;
            unset($newUrlXml);
        }
        if(!$this->agent && $this->settings["catalog"]["mode"] != "debug" && file_exists($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/shs.parser/include/catalog_parser_copy_url".$this->id.".txt")) {
            unlink($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/shs.parser/include/catalog_parser_copy_url".$this->id.".txt");
        }
        if(!$this->agent && $this->settings["catalog"]["mode"] != "debug" && file_exists($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/shs.parser/include/catalog_parser_copy_page".$this->id.".txt")) {
            unlink($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/shs.parser/include/catalog_parser_copy_page".$this->id.".txt");
        }
    }
    
    protected function startNewFolder() {
        $dir = $_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/shs.parser/include/".$this->id;
        if(!is_dir($dir)) {
            if(!mkdir($dir, 0775)) {
                //вывесть ошибка о создании папки
                return false;
            }
        }
        
        return true;
    }
    
    protected function WriteNewXmlFile($srt, $link, $id, $cursor = false) {
        if((strlen($srt) == 0) && (strlen($link) == 0) && (strlen($id) == 0))
            return false;
        $file = $_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/shs.parser/include/".$this->id."/".md5($link)."_".$id.".xml";
        if($cursor == false) {
            if(file_exists($file)) {
                if(!file_put_contents($file, $srt."\n", FILE_APPEND)) {
                    return false;
                }
            }
            else {
                return false;
            }
        }
        else if($cursor == "HEADER") {
            if(!file_put_contents($file, $srt."\n")) {
                return false;
            }
        }
        else if($cursor == "FOOTER") {
            if(!file_put_contents($file, $srt, FILE_APPEND)) {
                unlink($file);
                
                return false;
            }
        }
        
        return $file;
    }
    
    protected function PageFromFileXml() {
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
        if(!$_GET["begin"] && !$prevPage)
            return true;
        if(isset($arPrevPage) && is_array($arPrevPage))
            foreach($arPrevPage as $i => $p) {
                $p = trim($p);
                if(empty($p))
                    continue;
                $this->pagenavigationPrev[$p] = $p;
            }
        if(isset($arCurrentPage) && is_array($arCurrentPage))
            foreach($arCurrentPage as $p) {
                $p = trim($p);
                if(empty($p))
                    continue;
                $this->pagenavigation[$p] = $p;
            }
        if(isset($this->pagenavigationPrev) && is_array($this->pagenavigationPrev))
            foreach($this->pagenavigationPrev as $i => $v) {
                foreach($this->pagenavigation as $i1 => $v1) {
                    if($v1 == $v)
                        unset($this->pagenavigation[$i1]);
                }
            }
        $isContinue = false;
        if(isset($this->pagenavigation) && is_array($this->pagenavigation))
            foreach($this->pagenavigation as $p) {
                $isContinue = true;
                $this->rss = $p;
                break;
            }
        if(!$isContinue && !empty($this->pagenavigationPrev) && $this->IsEndSectionUrl()) {
            if($this->IsEndSectionUrl()) {
                $this->ClearBufferStop();
            }
            else $this->ClearBufferStep();
            
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
    
    protected function parseCatalogSectionXml($pageHref)            //parse section
    {
        phpQuery::unloadDocuments();
        gc_collect_cycles();
        $this->html = phpQuery::newDocument($this->page);
        $this->base = $this->GetMetaBase($this->html);
        if($this->settings["catalog"]["add_parser_section"] != "Y") {
            return false;
        }
        if(empty($this->settings['catalog']['selector_category'])) {
            $this->errors[] = GetMessage("parser_no_selector_category");
            
            return false;
        }
        $arr = $this->GetArrSectionXml();
        if($arr !== false) {
            $new_section_arr = $this->GetTreeArrSectionXml($arr);
        }
        if(is_array($new_section_arr)) {
            if($this->settings["catalog"]["field_id_category"] == "EXT_FIELD") {
                $this->AddExtFieldSection();
            }
            /*foreach($new_section_arr as $key=>&$value)
            {
                $startFunc = $key;
                foreach($value as $k => &$parentSec)
                {
                    $value[$k]["parentId"] = 0;
                }
                break;
            }*/
            foreach($new_section_arr as $parent_id => $cat)
                $this->addAllSectionXml($new_section_arr, $parent_id, $arr);
            CIBlockSection::ReSort($this->iblock_id);
        }
    }
    
    protected function GetArrSectionXml() {
        if(empty($this->settings['catalog']['selector_category']))
            return false;
        $arr_section = $this->html->find($this->settings['catalog']['selector_category']);
        $arr = array();
        $arr_id = parent::GetArraySrcAttr($this->settings['catalog']["attr_id_category"]);
        $arr_name = parent::GetArraySrcAttr($this->settings['catalog']["attr_category"]);
        $arr_parent = parent::GetArraySrcAttr($this->settings['catalog']["attr_id_parrent_category"]);
        $arr_description = parent::GetArraySrcAttr($this->settings['catalog']["description_category"]);
        $arr_pic = parent::GetArraySrcAttr($this->settings['catalog']["pic_category"]);
        foreach($arr_section as $el_section) {
            if($arr_id['path'] == "") {
                $section_id = trim(pq($el_section)->attr($arr_id['attr']));
            }
            else {
                if(!empty($arr_id['attr']))
                    $section_id = trim(pq($el_section)->find($arr_id['path'])->attr($arr_id['attr']));
                else if(empty($arr_id['attr'])) {
                    $section_id = trim(pq($el_section)->find($arr_id['path'])->html());
                    $section_id = trim(strip_tags($section_id));
                }
            }
            if(empty($section_id)) {
                continue 1;
            }
            if($arr_parent["path"] == "") {
                $parentId = trim(pq($el_section)->attr($arr_parent["attr"]));
            }
            else {
                if(!empty($arr_parent["attr"]))
                    $parentId = trim(pq($el_section)->find($arr_parent["path"])->attr($arr_parent["attr"]));
                else if(empty($arr_parent["attr"])) {
                    $parentId = trim(pq($el_section)->find($arr_parent["path"])->text());
                    $parentId = trim(strip_tags($parentId));
                }
            }
            if(!isset($parentId) || empty($parentId)) {
                $parentId = 0;
            }
            if(empty($this->settings['catalog']["attr_category"])) {
                $arr[$section_id]['text'] = iconv('UTF-8', SITE_CHARSET, strip_tags(trim(pq($el_section)->text())));
            }
            else if(!empty($this->settings['catalog']["attr_category"])) {
                if(!empty($arr_name['attr'])) {
                    $arr[$section_id]['text'] = iconv('UTF-8', SITE_CHARSET, trim(pq($el_section)->find($arr_name["path"])->attr($arr_name["attr"])));
                }
                else if(empty($arr_name['attr'])) {
                    $arr[$section_id]['text'] = iconv('UTF-8', SITE_CHARSET, strip_tags(trim(pq($el_section)->find($arr_name["path"])->text())));
                }
            }
            if(!empty($this->settings['catalog']["description_category"])) {
                if(!empty($arr_description['attr'])) {
                    $arr[$section_id]['description'] = trim(pq($el_section)->find($arr_description["path"])->attr($arr_description["attr"]));
                }
                else if(empty($arr_description['attr'])) {
                    $arr[$section_id]['description'] = iconv('UTF-8', SITE_CHARSET, strip_tags(trim(pq($el_section)->find($arr_description["path"])->html())));
                }
            }
            if(!empty($this->settings['catalog']["pic_category"])) {
                if(!empty($arr_pic['attr'])) {
                    $src = trim(pq($el_section)->find($arr_pic["path"])->attr($arr_pic["attr"]));
                }
                else if(empty($arr_pic['attr'])) {
                    $src = strip_tags(trim(pq($el_section)->find($arr_pic["path"])->html()));
                }
                $src = $this->parseCaralogFilterSrc($src);
                $src = $this->getCatalogLink($src);
                foreach(GetModuleEvents("shs.parser", "ParserCategoryPicture", true) as $arEvent) //05.06.2017
                    ExecuteModuleEventEx($arEvent, array(
                        &$this,
                        &$src
                    ));
                if(self::CheckImage($src)) {
                    $arImg = $this->MakeFileArray($src);
                    $arr[$section_id]['pic'] = $arImg;
                    $this->arrFilesTemp[] = $arr[$section_id]['pic']["tmp_name"];
                }
            }
            if(!empty($this->settings['catalog']["description_category_type"])) {
                $arr[$section_id]['description_type'] = strip_tags(trim($this->settings['catalog']["description_category_type"]));
            }
            if(is_array($this->settings['catalog']["fields_category"]) && !empty($this->settings['catalog']["fields_category"])) {
                foreach($this->settings['catalog']["fields_category"] as $key => $val) {
                    $fields_arr = parent::GetArraySrcAttr($val);
                    if(!empty($fields_arr['attr'])) {
                        $value = trim(pq($el_section)->find($fields_arr["path"])->attr($fields_arr["attr"]));
                    }
                    else if(empty($fields_arr['attr'])) {
                        $value = strip_tags(trim(pq($el_section)->find($fields_arr["path"])->html()));
                    }
                    $arr[$section_id]['user_fields'][$key] = $this->parseUserFields($key, $value);
                }
            }
            $arr[$section_id]['parentId'] = $parentId;
        }
        ksort($arr);
        
        return $arr;
    }
    
    protected function parseUserFields($key, $value) {
        $return = '';
        $res = CUserTypeEntity::GetList(array(), array(
            'ENTITY_ID' => 'IBLOCK_'.$this->iblock_id.'_SECTION',
            'FIELD_NAME' => $key
        ))->fetch();
        if(!empty($res)) {
            switch($res['USER_TYPE_ID']) {
                case 'string':
                    $return = $value;
                    break;
                case 'integer':
                    $value = str_replace(",", ".", $value);
                    $value = preg_replace("/\.{1}$/", "", $value);
                    $value = preg_replace('/[^0-9.]/', "", $value);
                    $return = intval($value);
                    break;
                case 'boolean':
                    if($value == 'Y' || $value == 'true' || $value == 'yes' || $value === true) {
                        $return = 'Y';
                    }
                    else {
                        $return = 'N';
                    }
                    break;
                case 'enumeration':
                    $resE = CUserFieldEnum::GetList(array(), array(
                        'USER_FIELD_ID' => $res['ID'],
                        'VALUE' => $value
                    ))->fetch();
                    if(!empty($resE)) {
                        $return = $resE['ID'];
                    }
                    else {
                        $arFields = array(
                            'USER_FIELD_ID' => $res['ID'],
                            'VALUE' => $value,
                            'DEF' => 'N',
                            'SORT' => 100,
                            'XML_ID' => CUtil::translit($value, 'ru', array(
                                "replace_space" => "-",
                                "replace_other" => "-"
                            )),
                        );
                        global $DB;
                        $DB->Add("b_user_field_enum", $arFields);
                        $resE = CUserFieldEnum::GetList(array(), array(
                            'USER_FIELD_ID' => $key,
                            'VALUE' => $value
                        ))->fetch();
                        if($resE) {
                            $return = $resE['ID'];
                        }
                    }
                    break;
            }
        }
        
        return $return;
    }
    
    protected function GetTreeArrSectionXml($arr) {
        if(!is_array($arr))
            return false;
        $new_section_arr = array();
        /* foreach ($arr as $key => $value)
         {
             if (!isset($new_section_arr[$value['parentId']]) || empty($new_section_arr[$value['parentId']]))
             {
                 $new_section_arr[$value['parentId']]['text'] = $arr[$value['parentId']]['text'];
             }
         }*/
        foreach($arr as $k => $v) {
            $new_section_arr[$v['parentId']][$k]['text'] = $v['text'];
            $new_section_arr[$v['parentId']][$k]['parentId'] = $v['parentId'];
            $new_section_arr[$v['parentId']][$k]['description'] = $v['description'];
            $new_section_arr[$v['parentId']][$k]['description_type'] = $v['description_type'];
            $new_section_arr[$v['parentId']][$k]['pic'] = $v['pic'];
            $new_section_arr[$v['parentId']][$k]['user_fields'] = $v['user_fields'];
        }
        
        return $new_section_arr;
    }
    
    protected function AddExtFieldSection() {
        $arFields = Array(
            "ENTITY_ID" => "IBLOCK_".$this->iblock_id."_SECTION",
            "FIELD_NAME" => "UF_SHS_PARSER",
            "USER_TYPE_ID" => "string",
            "EDIT_FORM_LABEL" => Array(
                "ru" => GetMessage("EDIT_FORM_LABEL_RU"),
                "en" => GetMessage("EDIT_FORM_LABEL_EN")
            )
        );
        $obUserField = new CUserTypeEntity;
        $obUserField->Add($arFields);
    }
    
    protected function addAllSectionXml($cats, $parent_id, $arr) {
        /*if($parent_id != 0)
        {
            if($this->issetSectionCatalog($parent_id) === false)
            {
                if($arr[$parent_id]["parentId"] == 0)
                {
                    $parent_section = $this->section_id;
                }
                else
                {
                    $parent_section = $this->issetSectionCatalog($arr[$parent_id]["parentId"]);
                }
                 $this->addSectionXlm(array("id_section" => $parent_id, "text_section" => $arr[$parent_id]["text"], "id_parrent" => $parent_section,'description'=>$arr[$parent_id]['description'],'description_type'=>$arr[$parent_id]['description_type'],'pic'=>$arr[$parent_id]['pic'],'user_fields'=>$arr[$parent_id]['user_fields'],));
            }
        }*/
        if(is_array($cats) && isset($cats[$parent_id])) {
            foreach($cats[$parent_id] as $id => $cat) {
                // проверять по имени, а не по ад???
                $id_sec = $this->issetSectionCatalog($id);
                if($id_sec === false) {
                    if($cat["parentId"] == 0) {
                        $parent_sec = $this->section_id;
                    }
                    else {
                        $parent_sec = $this->issetSectionCatalog($parent_id);
                    }
                    $this->addSectionXlm(array(
                        "id_section" => $id,
                        "text_section" => $cat["text"],
                        "id_parrent" => $parent_sec,
                        'description' => $cat['description'],
                        'description_type' => $cat['description_type'],
                        'pic' => $cat['pic'],
                        'user_fields' => $cat['user_fields'],
                    ));
                }
                else {
                    if($cat["parentId"] == 0) {
                        $parent_sec = $this->section_id;
                    }
                    else {
                        $parent_sec = $this->issetSectionCatalog($parent_id);
                    }
                    $this->UpdateSectionXml($id_sec, array(
                        "id_section" => $id,
                        "text_section" => $cat["text"],
                        "id_parrent" => $parent_sec,
                        'description' => $cat['description'],
                        'description_type' => $cat['description_type'],
                        'pic' => $cat['pic'],
                        'user_fields' => $cat['user_fields'],
                    ));
                }
                $this->addAllSectionXml($cats, $id, $arr);
            }
        }
        else {
            return null;
        }
        /*foreach ($arr as $id => $val)
        {
            $section_title =  $arr[$id]['text'];
            if (empty($section_title) && ($id != 0))
            {
                continue;
            }
            elseif ($id == 0) $parrent_section = $this->section_id;
            elseif ($id != 0) $parrent_section = $this->issetSectionCatalog($id);

            if (($this->issetSectionCatalog($id) === false) && ($id != 0))
            {
                $this->addSectionXlm(array("id_section" => $id, "text_section" => $section_title, "id_parrent" => $parrent_section));
            }
            foreach ($val as $key => $text)
            {
                if($key !== "text")
                {
                   if (($this->issetSectionCatalog($key) === false) && $parrent_section !== false)
                   {
                       $this->addSectionXlm(array("id_section" => $key, "text_section" => $text["text"], "id_parrent" => $parrent_section));
                   }
                }
            }
        }*/
    }
    
    /**
     * check exists category with $settings
     * @param $settings by which need find Category
     * @return ID category if it exist, and false if it isn't exist
     * */
    protected function issetSectionCatalog($settings) {
        if(empty($settings))
            return false;
        $arFilter = $this->GetArrFilterSection($settings);
        if(!is_array($arFilter))
            return false;
        $db_section = CIBlockSection::GetList(Array("SORT" => "ASC"), $arFilter, false, Array(
            "ID",
            "XML_ID",
            "UF_SHS_PARSER"
        ), false);
        $id_section = $db_section->Fetch();
        if($id_section)
            return $id_section["ID"];
        else
            return false;
    }
    
    protected function GetArrFilterSection($settings) {
        $arFilter["IBLOCK_ID"] = $this->iblock_id;
        if(isset($this->settings["catalog"]["uniq_category"]["dop_fields_category"]) && !empty($this->settings["catalog"]["uniq_category"]["dop_fields_category"])) {
            $strFilter = "shs_";
            if($this->settings["catalog"]["uniq_category"]["dop_fields_category"] == "id_category") {
                $strFilter = $strFilter.$settings;
            }
            else if($this->settings["catalog"]["uniq_category"]["dop_fields_category"] == "link_xml_file") {
                $strFilter = $strFilter.md5($this->rss)."_".$settings;
            }
            else if($this->settings["catalog"]["uniq_category"]["dop_fields_category"] == "id_parser") {
                $strFilter = $strFilter.$this->id."_".$settings;
            }
        }
        else {
            $strFilter = "shs_".md5($this->rss)."_".$settings;
        }
        if($this->settings["catalog"]["field_id_category"] == "XML_ID") {
            $arFilter['=XML_ID'] = $strFilter;
        }
        else if($this->settings["catalog"]["field_id_category"] == "EXT_FIELD") {
            $arFilter['=UF_SHS_PARSER'] = $strFilter;
        }
        
        return $arFilter;
    }
    
    protected function addSectionXlm($settings) {
        if(empty($settings) || !is_array($settings))
            return false;
        $index_category = ($this->settings["catalog"]["index_category"] == "Y") ? true : false;
        $new_section = new CIBlockSection;
        $arFields = $this->GetArrFieldsSection($settings);
        $ID = $new_section->Add($arFields, false, $index_category);
        if($ID !== false) {
            $this->countSection++;
        }
        else if($ID === false) {
            $this->errors[] = GetMessage("parser_error_add_category").$settings["id_section"]." - ".$new_section->LAST_ERROR;
        }
    }
    
    protected function GetArrFieldsSection($settings) {
        $code = "";
        if($this->settings["catalog"]["code_category"] == "Y") {
            $code = $this->GetCodeSection($settings["text_section"]);
        }
        $arFields = Array(
            "ACTIVE" => "Y",
            "CODE" => $code,
            "IBLOCK_SECTION_ID" => $settings["id_parrent"],
            "IBLOCK_ID" => $this->iblock_id,
            "NAME" => $this->convertDataCharset($settings["text_section"]),
            "DESCRIPTION" => $this->convertDataCharset($settings["description"]),
            "DESCRIPTION_TYPE" => $settings["description_type"],
            "PICTURE" => $settings["pic"],
        );
        if(isset($settings['user_fields'])) {
            foreach($settings['user_fields'] as $key => $val) {
                $arFields[$key] = $val;
            }
        }
        //string for write fields
        if(isset($this->settings["catalog"]["uniq_category"]["dop_fields_category"]) && !empty($this->settings["catalog"]["uniq_category"]["dop_fields_category"])) {
            $strFilter = "shs_";
            if($this->settings["catalog"]["uniq_category"]["dop_fields_category"] == "id_category") {
                $strFilter = $strFilter.$settings["id_section"];
            }
            else if($this->settings["catalog"]["uniq_category"]["dop_fields_category"] == "link_xml_file") {
                $strFilter = $strFilter.md5($this->rss)."_".$settings["id_section"];
            }
            else if($this->settings["catalog"]["uniq_category"]["dop_fields_category"] == "id_parser") {
                $strFilter = $strFilter.$this->id."_".$settings["id_section"];
            }
        }
        else {
            $strFilter = "shs_".md5($this->rss)."_".$settings["id_section"];
        }
        if($this->settings["catalog"]["field_id_category"] == "XML_ID") {
            $arFields["XML_ID"] = $strFilter;
        }
        else if($this->settings["catalog"]["field_id_category"] == "EXT_FIELD") {
            $arFields["UF_SHS_PARSER"] = $strFilter;
        }
        
        return $arFields;
    }
    
    protected function GetCodeSection($settings) {
        $settings = $this->convertDataCharset($settings);
        $arFieldCode = $this->arrayIblock["FIELDS"]["SECTION_CODE"]["DEFAULT_VALUE"];
        $code = CUtil::translit($settings, "ru", array(
            "max_len" => $arFieldCode["TRANS_LEN"],
            "change_case" => $arFieldCode["TRANS_CASE"],
            "replace_space" => $arFieldCode["TRANS_SPACE"],
            "replace_other" => $arFieldCode["TRANS_OTHER"],
            "delete_repeat_replace" => $arFieldCode["TRANS_EAT"] == "Y" ? true : false,
        ));
        $db_section = CIBlockSection::GetList(Array("SORT" => "ASC"), array(
            "%CODE" => $code,
            "IBLOCK_ID" => $this->iblock_id
        ), false, Array(
            "ID",
            "CODE"
        ), false);
        while($ar = $db_section->Fetch()) {
            $arCodes[$ar["CODE"]] = $ar["ID"];
        }
        if(!empty($arCodes) && array_key_exists($code, $arCodes)) {
            $i = 1;
            while(array_key_exists($code."_".$i, $arCodes)) {
                $i++;
            }
            
            return $code."_".$i;
        }
        else {
            return $code;
        }
    }
    
    protected function UpdateSectionXml($id_sec, $settings) {
        if(empty($settings) || !is_array($settings) || empty($id_sec))
            return false;
        $index_category = ($this->settings["catalog"]["index_category"] == "Y") ? true : false;
        $new_section = new CIBlockSection;
        $arFields = $this->GetArrFieldsSection($settings);
        $arFieldsNew = Array("NAME" => $arFields["NAME"]);
        if($this->settings['catalog']['update']['descr_category'] == 'Y') {
            $arFieldsNew['DESCRIPTION'] = $arFields['DESCRIPTION'];
            $arFieldsNew['DESCRIPTION_TYPE'] = $arFields['DESCRIPTION_TYPE'];
        }
        if($this->settings['catalog']['update']['pic_category'] == 'Y') {
            $arFieldsNew['PICTURE'] = $arFields['PICTURE'];
        }
        if($this->settings['catalog']['update']['field_category'] == 'Y') {
            foreach($arFields as $key => $val) {
                if(strpos($key, 'UF_') === 0)
                    $arFieldsNew[$key] = $val;
            }
        }
        $ID = $new_section->Update($id_sec, $arFieldsNew, false, $index_category);
        if($ID !== false) {
            $this->countSection++;
        }
        else if($ID === false) {
            $this->errors[] = $new_section->LAST_ERROR;
        }
    }
    
    protected function parseCatalogProductsHL() {
        $this->parserFields();
        $count = 0;
        $i = 0;
        $ci = 0;
        $this->activeCurrentPage++;
        $this->SetCatalogElementsResult($this->activeCurrentPage);
        $this->product_xml_id = false;
        $element = htmlspecialchars_decode($this->settings["catalog"]["selector"]);
        if($this->preview_delete_element)
            $this->deleteCatalogElement($this->preview_delete_element, $element, $this->html[$element]);
        if($this->preview_delete_attribute)
            $this->deleteCatalogAttribute($this->preview_delete_attribute, $element, $this->html[$element]);
        $count = count($this->html[$element]);
      
        if($this->settings["catalog"]["mode"] != "debug" && !$this->agent) {
            if($count > $this->settings["catalog"]["step"] && ($this->settings["catalog"]["mode"] != "debug" && !$this->agent))
                $countStep = $this->settings["catalog"]["step"];
            else {
                $this->stepStart = true;
                if($this->CheckOnePageNavigation() || $this->CheckAlonePageNavigation($this->currentPage))
                    $this->pagenavigation[$this->rss] = $this->rss;
                parent::SaveCurrentPage($this->pagenavigation);
                $this->SavePrevPage($this->sectionPage);
                $countStep = $count;
            }
        }
        else {
            $countStep = $count;
        }
        if($this->type_out == 'HL')
            $countStep = $count;
        file_put_contents($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/shs.parser/include/count_parser".$this->id.".txt", $countStep."|".$ci);
        if($count == 0) {
            $this->errors[] = GetMessage("parser_error_selector_notfound")."[".$element."]";
            $this->clearFields();
        }
        foreach($this->html[$element] as $product) {
            if(!empty($this->highloadFields))
                $this->fillHLField($product);
            $this->saveHlElement($product);
            $i++;
            file_put_contents($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/shs.parser/include/count_parser".$this->id.".txt", $countStep."|".$countStep % $i);
            $this->CalculateStep($count);
            unset($el);
        }
        unlink($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/shs.parser/include/count_parser".$this->id.".txt");
        if(isset($count))
            unset($count);
        if(isset($countStep))
            unset($countStep);
        if(isset($element))
            unset($element);
    }
    
    protected function parserFields() {
        if(empty($this->settings['highload']))
            return;
        foreach($this->settings['highload'] as $key => $val) {
            if(empty($val))
                continue;
            $this->highloadFields[$key] = trim(str_replace($this->settings['catalog']['selector'], '', $val));
        }
    }
    
    protected function fillHLField($el) {
        foreach($this->highloadFields as $key => $val)
            $this->highloadFieldValues[$key] = pq($el)->find($val)->text();
    }
    
    protected function saveHlElement($el) {
        $id = $this->checkUniqElement();
        if($id)
            $this->updateHlElement($id, $el);
        else
            $this->addHlElement($el);
        sleep(1);
    }
    
    protected function checkUniqElement() {
        if(!isset($this->settings['catalog']['update']['active']) || $this->settings['catalog']['update']['active'] != 'Y')
            return false;
        if(empty($this->settings['catalog']['uniq']['fields']))
            throw new InvalidArgumentException(GetMessage('empty_uniq_fields'));
        if(empty($this->highload_class))
            throw new InvalidArgumentException(GetMessage('empty_highload_class'));
        $arFilter = array();
        foreach($this->settings['catalog']['uniq']['fields'] as $column)
            $arFilter[$column] = $this->highloadFieldValues[$column];
        
        $hl_class = $this->highload_class;
        $arHl = $hl_class::getList(array(
            'select' => array('ID'),
            'filter' => $arFilter,
            'cache' => array('ttl' => 86400)
            //day
        ))->fetch();
        
        return boolval($arHl['ID']) === false ? false : $arHl['ID'];
    }
    
    protected function updateHlElement($id, $el) {
        $hl_class = $this->highload_class;
        $result = $hl_class::update($id, $this->highloadFieldValues);
        if($result->isSuccess()) {
            $this->elementID = $id;
            parent::SetCatalogElementsResult();
        }
        
        return true;
    }
    
    protected function addHlElement($el) {
        if(isset($this->settings['catalog']['update']['add_element']) && $this->settings['catalog']['update']['add_element'] == 'Y')
            return false;
        $this->errors = array();
        $db_events = GetModuleEvents("shs.parser", "parserBeforeAddElementHL", true); //27.10.2015
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
            unset($arEvent);
        }
        unset($db_events);
        try {
            $hl_class = $this->highload_class;
            $this->elementID = $hl_class::add($this->highloadFieldValues);
        }
        catch(Bitrix\Main\ArgumentException $e) {
            CAdminMessage::ShowMessage($e->getMessage());
            $this->errors[] = $e->getMessage();
        }
        parent::SetCatalogElementsResult();
        
        return true;
    }
    
    protected function deleteStrReplace($arr) {
        if(count($arr) <= 0)
            return false;
        $iter = 0;
        $item = 0;
        $count = 2000;
        //        $count = 10;
        $newArr = array();
        foreach($arr as $id => &$val) {
            if(strlen($val) <= 0) {
                unset($arr[$id]);
                continue;
            }
            $offerSelector = explode(' ', $this->settings['catalog']['selector']);
            $offerSelector = $offerSelector[0] ?: 'offers';
            $val = preg_replace("/<".$offerSelector."(.*?)>/", "", $val);
            if($item == $count) {
                $iter++;
                $item = 0;
            }
            $newArr[$iter][$item] = $val;
            $item++;
        }
        if(count($newArr) > 0) {
            $this->parseXmlHeader($this->page, $arr);
            $this->parseXmlFooter($this->page, $arr);
            $this->arrOffersNewXml = $newArr;
            unset($newArr);
        }
    }
    
    protected function parseXmlHeader($page, $arr) {
        if((strlen($page) > 0) && (count($arr) > 0)) {
            $arrH = explode($arr[0], $page);
            $this->XmlHeader = $arrH[0];
        }
    }
    
    protected function parseXmlFooter($page, $arr) {
        if((strlen($page) > 0) && (count($arr) > 0)) {
            $l = count($arr) - 1;
            $arrF = explode($arr[$l], $page);
            $l = count($arrF) - 1;
            $this->XmlFooter = $arrF[$l];
        }
    }
    
    protected function parseCatalogProductElementXml(&$el) {
        $this->countItem++;
       
        if(!$this->parserCatalogPreviewXml($el)) {
            parent::SaveCatalogError();
            parent::clearFields();
            
            return false;
        }
    
        if($this->settings["catalog"]["add_parser_section"] != "Y" && empty($this->settings['catalog']['section_main']))
            parent::parseCatalogSection();
        
        parent::parseCatalogDate();
        parent::parseCatalogAllFields();
        
        if($this->typeN == 'xml_catalo') {
            $this->parseDetailPageXml($el);
        }
       
        switch($this->typeN) {
            case 'xml':
                $db_events = GetModuleEvents("shs.parser", "parserBeforeAddElementXml", true);
                break;
            case 'xml_catalo':
                $db_events = GetModuleEvents("shs.parser", "parserBeforeAddElementXmlCatalog", true);
                break;
        }
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
        if(!$error && !$error_isad) {
            parent::AddElementCatalog();
            foreach(GetModuleEvents("shs.parser", "parserAfterAddElementXml", true) as $arEvent)
                ExecuteModuleEventEx($arEvent, array(
                    &$this,
                    &$el
                ));
        }
        if($this->isCatalog && $this->elementID) {
            if($this->isOfferCatalog && !$this->boolOffer) {
                parent::AddElementOfferCatalog();
                $this->elementID = $this->elementOfferID;
                $this->elementUpdate = $this->elementOfferUpdate;
            }
            if($this->boolOffer) {
                parent::addProductPriceOffers();
            }
            else {
                parent::AddProductCatalog();
                parent::AddMeasureCatalog();
                parent::AddPriceCatalog();
                $this->addAvailable();
            }
            $this->parseStore();
            $this->updateQuantity();
        }/*else{
            $this->AddElementOfferCatalog();
            $this->AddProductCatalog();
            $this->AddMeasureCatalog();
            $this->AddPriceCatalog();
        }*/
        if($this->settings['smart_log']['enabled'] == 'Y') {
            $this->settings['smart_log']['result_id'] = file_get_contents($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/shs.parser/include/result_id".$this->id.".txt");
            SmartLogs::saveNewValues($this->elementID, $this->settings["smart_log"], $this->arFields, isset($this->arPrice['PRICE']) ? $this->arPrice['PRICE'] : null, $this->arProduct);
        }
        parent::SetCatalogElementsResult();
        parent::clearFilesTemp();
        parent::clearFields();
    }
    
    protected function parserCatalogPreviewXml(&$el) {
        if(!$this->parseCatalogIdElement($el))
            return false;
        self::parseCatalogNamePreview($el);
        //$this->parseCatalogPropertiesPreview($el);
        if($this->isCatalog)
            parent::parseCatalogPricePreview($el);
        if($this->isCatalog)
            parent::parseCatalogAdditionalPricesPreview($el);
        if($this->isCatalog)
            parent::parseCatalogProcurementPrice($el);
        if($this->isCatalog)
            $this->ParseCatalogAvailablePreview($el);
        if($this->settings["catalog"]["add_parser_section"] == "Y" || (count($this->settings["catalog"]["id_category_main"]) > 0))
            $this->parseCatalogParrentSection($el);
       
        parent::parseCatalogPreviewPicturePreview($el);
        parent::parseCatalogDetailPicture($el);
    
        $this->parseCatalogDescriptionPreview($el);
        $this->parseCatalogDescriptionXml($el);
        $this->parseAdditionalStores($el);
       
        parent::parseCatalogDetailMorePhoto($el);
        $this->parseCatalogPropertiesXml($el);
        $this->parseCategoryId($el);
        foreach(GetModuleEvents("shs.parser", "parserBeforeOffers", true) as $arEvent)
            ExecuteModuleEventEx($arEvent, array(
                &$this,
                &$el
            ));
        $this->parserOffersXml($el);
        
        return true;
    }
    
    protected function parseCatalogIdElement($el)                   //parse id element
    {
        $id_element = $this->settings["catalog"]["id_selector"];
        $ar = parent::GetArraySrcAttr($id_element);
        $selector = $ar["path"];
        $attr = $ar["attr"];
        if(empty($id_element))
            return false;
        if($selector == "") {
            $p = trim(pq($el)->attr($attr));
        }
        else {
            if(!empty($attr)) {
                $p = trim(pq($el)->find($selector)->attr($attr));
            }
            else if(empty($attr)) {
                $p = strip_tags((pq($el)->find($selector)->html()));
            }
        }
        if(!$p) {
            $this->errors[] = GetMessage("parser_error_id_element_notfound");
            
            return false;
        }
        $this->arFields["LINK"] = $p;
        
        return true;
    }
    
    protected function parseCatalogNamePreview($el) {
        if(isset($this->settings["catalog"]["detail_name"]) && $this->settings["catalog"]["detail_name"])
            return false;
        $name = $this->settings["catalog"]["name"] ? $this->settings["catalog"]["name"] : $this->settings["catalog"]["href"];
        $name = htmlspecialchars_decode($name);
        $ar = $this->GetArraySrcAttr($name);
        if(empty($ar["attr"]))
            $this->arFields["NAME"] = iconv('UTF-8', SITE_CHARSET, trim(strip_tags($this->stripCdata(pq($el)->find($ar["path"])->text()))));
        else if(!empty($ar["attr"]))
            $this->arFields["NAME"] = iconv('UTF-8', SITE_CHARSET, trim(htmlspecialchars(trim(strip_tags(pq($el)->find($ar["path"])->attr($ar["attr"]))))));
        if($this->arFields["NAME"]) {
            $this->arFields["NAME"] = $this->actionFieldProps("SOTBIT_PARSER_NAME_E", $this->arFields["NAME"]);
            if(isset($this->settings["loc"]["f_name"]) && $this->settings["loc"]["f_name"] == "Y")
                $this->arFields["NAME"] = $this->locText($this->arFields["NAME"]);
        }
        //$this->arFields["NAME"] = $this->convertDataCharset($this->arFields["NAME"]);
        unset($name, $ar);
        if(!$this->arFields["NAME"]) {
            $this->errors[] = GetMessage("parser_error_name_notfound");
            
            return false;
        }
    }
    
    protected function ParseCatalogAvailablePreview(&$el) {
        if(!empty($this->settings["catalog"]["preview_count"])) {
            if(parent::checkUniq() && (!$this->isUpdate || !$this->isUpdate["count"]))
                return false;
            $arr = parent::GetArraySrcAttr($this->settings["catalog"]["preview_count"]);
            $path = $arr["path"];
            $attr = $arr["attr"];
            if(empty($attr)) {
                $available = pq($el)->find($path)->html();
            }
            else if(!empty($attr)) {
                $available = pq($el)->find($path)->attr($attr);
            }
            else if(empty($path) && !empty($attr)) {
                $available = pq($el)->attr($attr);
            }
            $available = trim(strip_tags($available));
            $value = $this->findAvailabilityValue($available);
            if(isset($value['count'])) {
                $available = $value['count'];
            }
            $available = preg_replace('/[^0-9.]/', "", $available);
            if(is_numeric($available)) {
                $this->arFields["AVAILABLE_PREVIEW"] = intval($available);
            }
        }
        else if(is_numeric($this->settings["catalog"]["count_default"])) {
            $this->arFields["AVAILABLE_PREVIEW"] = intval($this->settings["catalog"]["count_default"]);
        }
    }
    
    public function findAvailabilityValue($value) {
        if(isset($this->settings['availability']['list']) && !empty($this->settings['availability']['list'])) {
            foreach($this->settings['availability']['list'] as $i => $av) {
                if(strcmp($av['text'], $value) == 0) {
                    return $av;
                }
            }
            
            return $value;
        }
        else {
            return $value;
        }
    }
    
    /**
     * Parse parent section if add_parser_section = Y and haven't empty "id_section" field
     * @input one record from xml file
     * @return int id_section
     * */
    protected function parseCatalogParrentSection(&$el)             //parse parent section
    {
        //        if($this->checkUniq())
        //            return false;
//        if(($this->settings["catalog"]["add_parser_section"] == "N") || ($this->settings["catalog"]["add_parser_section"] == "N" && (count($this->settings["catalog"]["id_category_main"]) <= 0) || empty($this->settings["catalog"]["id_section"])))
//            return false;
        $ar = parent::GetArraySrcAttr($this->settings["catalog"]["id_section"]);
        
        $selector = $ar["path"];
        $attr = $ar["attr"];
        $parrent_id = array();
        if($selector != '' && $attr != '') {
            $parrent_id[] = trim(pq($el)->find($selector)->attr(trim($attr)));
        }
        else if($selector == "") {
            $arAttr = explode(',', $attr);
            foreach($arAttr as $atr)
                $parrent_id[] = trim(pq($el)->attr(trim($atr)));
        }
        else {
            $arSelector = explode(',', $selector);
            foreach($arSelector as $select) {
                if(empty($attr))
                    $parrent_id[] = trim(pq($el)->find(trim($select))->text());
                else if(!empty($attr))
                    $parrent_id[] = trim(pq($el)->find(trim($select))->attr());
            }
        }
        //$parrent_id = implode(',', $parrent4_id);
        /*$parrent_id = trim($this->settings["catalog"]["id_section"]);
        $parrent_id = trim(pq($el)->find($parrent_id)->text());*/
        if(empty($parrent_id))
            return false;
        else if(!empty($parrent_id)) {
            //id_category_main - "Сопоставить с существующими категориями инфоблока" когда указывает привязку разделов
            $checked_id = array();
            if(($this->settings["catalog"]["add_parser_section"] == "Y") && count($this->settings["catalog"]["id_category_main"]) <= 0) // установлего что можно создавать разделы и кол-во "Сопоставить с существующими категориями инфоблока" = 0
            {
                foreach($parrent_id as $id)
                    if(($p_id = $this->issetSectionCatalog(trim($id))) !== false)
                        $checked_id[] = $p_id;
            }
            else {
                //$parrent_id = $this->issetMainSectionCatalog($parrent_id);
                foreach($parrent_id as $id)
                    if(($p_id = $this->issetMainSectionCatalog(trim($id))) !== false)
                        $checked_id[] = $p_id;
            }
            $parrent_id = $checked_id;
            unset($checked_id);
        }
        if($parrent_id !== false) {
            // If we have array of parrent_id and there is count more than 1,
            // we save array in variable, in future we save ids by CIBlockElement::SetElementSection
            if(is_array($parrent_id)) {
                if(count($parrent_id) > 1) {
                    //same actions for save groups of element
                    $this->arFields["AR_IBLOCK_SECTION_ID"] = $parrent_id;
                }
                $parrent_id = $parrent_id[0];
            }
            $this->arFields["IBLOCK_SECTION_ID"] = $parrent_id;
        }
        /* кусок кода который был ранее
        elseif (!empty($parrent_id))
        {
           //проверяет наличие категорий
           //узнать что за настройка admin_tr_rss_dop
           if(($this->settings["catalog"]["add_parser_section"] == "Y") && count($this->settings["catalog"]["id_category_main"]) <= 0) // ���� ����� ������� ��������� ����� ������� � ��� ����� ��������
           {
               $parrent_id = $this->issetSectionCatalog($parrent_id);
           }
           else
           {
               $parrent_id = $this->issetMainSectionCatalog($parrent_id);
           }
        }
        if($parrent_id !== false)
        {
            $this->arFields["IBLOCK_SECTION_ID"] = $parrent_id;
        }
        /*else
        {
            $this->arFields["IBLOCK_SECTION_ID"] = $this->section_id;
        }*/
    }
    
    /**
     * check id section with "match with existcategories of infoblock"
     * @param id category
     * @return id category of infoblock or false if we don't have matches
     */
    protected function issetMainSectionCatalog($parrent_id) {
        if(empty($parrent_id))
            return false;
        if(count($this->settings["catalog"]["id_category_main"]) <= 0 || count($this->settings["catalog"]["section_main"]) <= 0)
            return false;
        $idSec = $this->settings["catalog"]["id_category_main"];
        $idMainSec = $this->settings["catalog"]["section_main"];
        if(in_array($parrent_id, $idSec)) {
            foreach($idSec as $id => &$val) {
                $val = trim($val);
                if(empty($val))
                    continue 1;
                if($val == $parrent_id) {
                    $returnVal = $idMainSec[$id];
                    break;
                }
            }
            if(isset($returnVal) && !empty($returnVal))
                return $returnVal;
            else
                return false;
        }
        else
            return false;
    }
    
    protected function parseCatalogDescriptionPreview(&$el) {
        if($this->settings["catalog"]["preview_text_selector"]/* && $this->settings["catalog"]["text_preview_from_detail"] != "Y"*/) {
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
    
    /**
     * find description in xml object and save to $this->arFields["DETAIL_TEXT"]
     * @param xml element
     *
     * */
    protected function parseCatalogDescriptionXml(&$el) {
        if(parent::checkUniq() && (!$this->isUpdate || (!$this->isUpdate["detail_descr"] && (!$this->isUpdate["preview_descr"] && !$this->settings["catalog"]["text_preview_from_detail"] != "Y"))))
            return false;
        if($this->settings["catalog"]["detail_text_selector"]) {
            $detail = $this->settings["catalog"]["detail_text_selector"];
            $arDetail = explode(",", $detail);
            $detail_text = "";
            if($arDetail && !empty($arDetail)) {
                foreach($arDetail as $detail) {
                    $detail = trim($detail);
                    if(!$detail)
                        continue 1;
                    foreach(pq($el)->find($detail." img") as $img) {
                        $src = pq($img)->attr("src");
                        $src = parent::parseCaralogFilterSrc($src);
                        $src = parent::getCatalogLink($src);
                        parent::parseCatalogSaveImgServer($img, $src);
                    }
                    $arr = parent::GetArraySrcAttr($detail);
                    $path = $arr["path"];
                    $attr = $arr["attr"];
                    if(empty($attr))
                        $detail_text .= pq($el)->find($path)->html();
                    else if(!empty($attr))
                        $detail_text .= pq($el)->find($path)->attr($attr);
                }
            }
            $detail_text = trim($detail_text);
            if(isset($this->settings["loc"]["f_detail_text"]) && $this->settings["loc"]["f_detail_text"] == "Y") {
                $detail_text = parent::locText($detail_text, $this->detail_text_type == "html" ? "html" : "plain");
            }
            $this->arFields["DETAIL_TEXT"] = $detail_text;
            $this->arFields["DETAIL_TEXT_TYPE"] = $this->detail_text_type;
            if($this->settings["catalog"]["text_preview_from_detail"] == "Y" && empty($this->arFields["PREVIEW_TEXT"])) {
                $this->arFields["PREVIEW_TEXT"] = $this->arFields["DETAIL_TEXT"];
                $this->arFields["PREVIEW_TEXT_TYPE"] = $this->arFields["DETAIL_TEXT_TYPE"];
            }
        }
    }
    
    protected function parseCatalogPropertiesXml(&$el) {
        if(parent::checkUniq() && !$this->isUpdate)
            return false;
        parent::parseCatalogDefaultProperties($el);
        $this->propsFilter = array();
        parent::parseCatalogSelectorProperties($el);
        $this->parseCatalogAutoProps($el);
        $this->parseCatalogAutoPropsAdd();
        parent::AllDoProps();
        if($this->isCatalog)
            parent::parseCatalogFindProduct($el);
        if($this->isCatalog)
            parent::parseCatalogSelectorProduct($el);
    }
    
    protected function parseCatalogAutoProps(&$el) {
        if(parent::checkUniq() && (!$this->isUpdate || !$this->isUpdate["props"]))
            return false;
        if(($this->settings["catalog"]["add_auto_props"] != "Y") || empty($this->settings["catalog"]["selector_find_props"]) || empty($this->settings["catalog"]["attr_auto_props"]))
            return false;
        $props = $this->settings["catalog"]["selector_find_props"];
        $props_name = parent::GetArraySrcAttr($this->settings["catalog"]["attr_auto_props"]);
        $arr_val = parent::GetArraySrcAttr($this->settings["catalog"]["selector_attr_value_auto_props"]);
        $this->arPropertiesParseAuto = array();
        foreach(pq($el)->find($props) as $property) {
            $isset_props = false;
            if(empty($props_name["path"])) {
                $name = trim(pq($property)->attr($props_name["attr"])); //name props
            }
            else {
                if(empty($props_name["attr"])) {
                    $name = trim(strip_tags(pq($property)->find($props_name["path"])->html()));
                }
                else if(!empty($props_name["attr"])) {
                    $name = trim(pq($property)->find($props_name["path"])->attr($props_name["attr"]));
                }
            }
            if(empty($name))
                continue 1;
            $name = $this->convertDataCharset($name);
            if(empty($arr_val["path"]) && empty($arr_val["attr"])) {
                $value = trim(pq($property)->html());
            }
            else if(empty($arr_val["path"]) && !empty($arr_val["attr"])) {
                $value = trim(pq($property)->attr($arr_val["attr"]));
            }
            else {
                if(empty($arr_val["attr"])) {
                    $value = strip_tags(trim(pq($property)->find($arr_val["path"])->html()));
                }
                else if(!empty($arr_val["attr"])) {
                    $value = trim(pq($property)->find($arr_val["path"])->attr($arr_val["attr"]));
                }
            }
            $value = $this->convertDataCharset($value);
            $isset_props = $this->issetPropsForName($name);
            if(!$isset_props) {
                $error = $this->addAutoProps($name);
                if($error !== false) {
                    $db_props = CIBlockProperty::GetByID($error, $this->iblock_id, false);
                    $code_props = $db_props->GetNext();
                    if(!isset($this->arProperties[$code_props["CODE"]]) || empty($this->arProperties[$code_props["CODE"]])) {
                        $this->arProperties[$code_props["CODE"]] = $code_props;
                    }
                    if(!isset($this->arPropertiesParseAuto[$code_props["CODE"]]) || empty($this->arPropertiesParseAuto[$code_props["CODE"]])) {
                        $this->arPropertiesParseAuto[$code_props["CODE"]] = $value;
                    }
                }
            }
            if($isset_props) {
                foreach($isset_props as $code => $props) {
                    if(!isset($this->arProperties[$code]) || empty($this->arProperties[$code])) {
                        $this->arProperties[$code] = $props;
                    }
                    if(!isset($this->arPropertiesParseAuto[$code]) || empty($this->arPropertiesParseAuto[$code])) {
                        $this->arPropertiesParseAuto[$code] = $value;
                    }
                }
            }
        }
    }
    
    protected function issetPropsForName($name)                     //search props for name
    {
        if(empty($name))
            return true;
        $property = CIBlockProperty::GetList(Array(
            "sort" => "asc",
            "name" => "asc"
        ), Array(
            "ACTIVE" => "Y",
            "IBLOCK_ID" => $this->iblock_id,
            "?PROPERTY_TYPE" => "S || N || L",
            "NAME" => $name
        ));
        $prors = array();
        while($prop = $property->Fetch()) {
            $prors[$prop["CODE"]] = $prop;
        }
        if($property) {
            return $prors;
        }
        if(!$property)
            return false;
    }
    
    protected function addAutoProps($setting)                       //add auto props
    {
        if(empty($setting))
            return false;
        $CODE = strtoupper(CUtil::translit($setting, "ru", array(
            "max_len" => 100,
            "change_case" => 'S',
            // 'L' - toLower, 'U' - toUpper, false - do not change
            "replace_space" => '_',
            "replace_other" => '_',
            "delete_repeat_replace" => true,
        )));
        $arFields = Array(
            "NAME" => $setting,
            "ACTIVE" => "Y",
            "SORT" => "100",
            "CODE" => $CODE,
            "PROPERTY_TYPE" => $this->settings["catalog"]["type_auto_props"],
            "IBLOCK_ID" => $this->iblock_id
        );
        $ibp = new CIBlockProperty;
        $PropID = $ibp->Add($arFields);
        if($PropID)
            return $PropID;
        else {
            $this->errors[] = "[".$setting."]".$ibp->LAST_ERROR;
            
            return false;
        }
    }
    
    protected function parseCatalogAutoPropsAdd() {
        $arProperties = $this->arPropertiesParseAuto;
        if(!$arProperties)
            return false;
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
        foreach($arProperties as $code => $value) {
            $arProp = $this->arProperties[$code];
            if((($arProp["PROPERTY_TYPE"] == "S") || ($arProp["PROPERTY_TYPE"] == "L") || ($arProp["PROPERTY_TYPE"] == "N"))) // && !empty($arProp["USER_TYPE_SETTINGS"]["TABLE_NAME"]) && intval($arProp["USER_TYPE_SETTINGS"]["TABLE_NAME"]) > 0)
            {
                $text = $value;
                if($arProp["USER_TYPE"] != "HTML")
                    $text = strip_tags($value);
                $text = str_replace($deleteSymb, "", $text);
                $this->parseCatalogPropAuto($code, $text);
            }
        }
    }
    
    public function parseCatalogPropAuto($code, $val) {
        if(empty($code))
            return false;
        $val = html_entity_decode($val);
        parent::filterProps($code, $val);
        $arProp = $this->arProperties[$code];
        //$default = $this->settings["catalog"]["default_prop"][$code];
        if($arProp["PROPERTY_TYPE"] != "N" && isset($this->settings["loc"]["f_props"]) && $this->settings["loc"]["f_props"])
            $val = parent::locText($val, $arProp["USER_TYPE"] == "HTML" ? "html" : "plain");
        if($arProp["USER_TYPE"] == "HTML" && $arProp["MULTIPLE"] != "Y") {
            $this->arFields["PROPERTY_VALUES"][$code] = Array(
                "VALUE" => Array(
                    "TEXT" => $val,
                    "TYPE" => "html"
                )
            );
        }
        else if($arProp["USER_TYPE"] == "HTML" && $arProp["MULTIPLE"] == "Y") {
            $this->arFields["PROPERTY_VALUES"][$code]["n0"] = Array(
                "VALUE" => Array(
                    "TEXT" => $val,
                    "TYPE" => "html"
                )
            );
        }
        else if($arProp["PROPERTY_TYPE"] == "S" && $arProp["MULTIPLE"] != "Y" && $arProp["USER_TYPE"] == "directory") {
            $this->arFields["PROPERTY_VALUES"][$code] = parent::CheckPropsDirectory($arProp, $code, $val);;
        }
        else if($arProp["PROPERTY_TYPE"] == "S" && $arProp["MULTIPLE"] == "Y" && $arProp["USER_TYPE"] == "directory") {
            $this->arFields["PROPERTY_VALUES"][$code]["n0"] = parent::CheckPropsDirectory($arProp, $code, $val);;
        }
        else if($arProp["PROPERTY_TYPE"] == "S" && $arProp["MULTIPLE"] != "Y") {
            $val = parent::actionFieldProps($code, $val);
            $this->arFields["PROPERTY_VALUES"][$code] = $val;
        }
        else if($arProp["PROPERTY_TYPE"] == "S" && $arProp["MULTIPLE"] == "Y") {
            $val = parent::actionFieldProps($code, $val);
            $this->arFields["PROPERTY_VALUES"][$code]["n0"] = $val;
        }
        else if($arProp["PROPERTY_TYPE"] == "N") {
            $val = str_replace(",", ".", $val);
            $val = preg_replace("/\.{1}$/", "", $val);
            $val = preg_replace('/[^0-9.]/', "", $val);
            $this->arFields["PROPERTY_VALUES"][$code] = $val;
        }
        else if($arProp["PROPERTY_TYPE"] == "L" && $arProp["MULTIPLE"] != "Y") {
            $this->arFields["PROPERTY_VALUES"][$code] = parent::CheckPropsL($arProp["ID"], $code, $val);
        }
        else if($arProp["PROPERTY_TYPE"] == "L" && $arProp["MULTIPLE"] == "Y") {
            $this->arFields["PROPERTY_VALUES"][$code]["n0"] = parent::CheckPropsL($arProp["ID"], $code, $val);
        }
        else if($arProp["PROPERTY_TYPE"] == "E" && $arProp["MULTIPLE"] != "Y") {
            $this->arFields["PROPERTY_VALUES"][$code] = parent::CheckPropsE($arProp, $code, $val);
        }
        else if($arProp["PROPERTY_TYPE"] == "E" && $arProp["MULTIPLE"] == "Y") {
            $this->arFields["PROPERTY_VALUES"][$code]["n0"] = parent::CheckPropsE($arProp, $code, $val);
        }
    }
    
    protected function parseCategoryId($el) {
    }
    
    protected function parserOffersXml($el) {
        $this->boolOffer = false;
        if($this->settings["offer"]["load"] == "table" && $this->isOfferParsing && isset($this->settings["offer"]["selector_item"]) && $this->settings["offer"]["selector_item"]) {
            $this->parserOffersXmlTable($el);
        }
        else if($this->settings["offer"]["load"] == "one" && $this->isOfferParsing && isset($this->settings["offer"]["one"]["selector"]) && $this->settings["offer"]["one"]["selector"]) {
            $this->parserOffersOne($el);
        }
    }
    
    protected function parserOffersXmlTable(&$el) {
        $offerItem = $this->settings["offer"]["selector_item"];
        if("CatalogSelector" == $offerItem) {//��������� �������
            $objectparser = pq($el);
        }
        else {
            $objectparser = pq($el)->find($offerItem);
        }
        foreach($objectparser as $offer) {
            $this->boolOffer = true;
            if(parent::parseOfferName($offer)) {
                parent::parseOfferPrice($offer);
                parent::parseOfferAdditionalPrice($offer);
                $this->parseOfferQuantity($offer);
                foreach(GetModuleEvents("shs.parser", "OnBeforeOfferPropertySave", true) as $arEvent)
                    ExecuteModuleEventEx($arEvent, array(
                        &$this,
                        &$offer
                    ));
                $this->parseOfferPropsXml($offer);
                if(!parent::parseOfferGetUniq()) {
                    parent::deleteOfferFields();;
                    continue 1;
                }
            }
            else
                continue 1;
            $this->arOfferAll["FIELDS"][] = $this->arOffer;
            $this->arOfferAll["PRICE"][] = $this->arPriceOffer;
            $this->arOfferAll["ADDIT_PRICE"][] = $this->arAdditionalPriceOffer;
            $this->arOfferAll["QUANTITY"][] = $this->arOfferQuantity;
            parent::deleteOfferFields();
        }
    }
    
    protected function parseOfferQuantity($offer) {
        if(parent::checkUniq() && (!$this->isUpdate || !$this->isUpdate["count"]))
            return false;
        if(isset($this->settings["offer"]["selector_quantity"]) && $this->settings["offer"]["selector_quantity"]) {
            $arr = parent::GetArraySrcAttr($this->settings["offer"]["selector_quantity"]);
            if(empty($arr["path"]) && !empty($arr["attr"])) {
                $quantity = trim(pq($offer)->attr($arr["attr"]));
            }
            else {
                if(empty($arr["attr"])) {
                    $quantity = trim(strip_tags(pq($offer)->find($arr["path"])->html()));
                }
                else if(!empty($arr["attr"])) {
                    $quantity = trim(pq($offer)->find($arr["path"])->attr($arr["attr"]));
                }
            }
            $quantity = trim(strip_tags($quantity));
            $value = $this->findAvailabilityValue($quantity);
            if($value && isset($value['count'])) {
                $quantity = $value['count'];
            }
            else if(is_numeric($value)) {
                $quantity = $value;
            }
            $quantity = preg_replace('/[^0-9.]/', "", $quantity);
            if(is_numeric($quantity)) {
                $quantity = intval($quantity);
                if($quantity == 0) {
                    $this->arOfferQuantity["QUANTITY"] = 0;
                }
                else {
                    $this->arOfferQuantity["QUANTITY"] = $quantity;
                }
            }
        }
        else if(isset($this->settings["offer"]["find_quantity"]) && $this->settings["offer"]["find_quantity"]) {
            if(isset($this->settings["offer"]["selector_item_td"]) && $this->settings["offer"]["selector_item_td"]) {
                $name = $this->settings["offer"]["find_quantity"];
                if(isset($this->tableHeaderNumber[$name])) {
                    $n = $this->tableHeaderNumber[$name];
                    $quantity = pq($offer)->find($this->settings["offer"]["selector_item_td"].":eq(".$n.")");
                    $quantity = trim(strip_tags($price));
                    $value = $this->findAvailabilityValue($quantity);
                    if($value && isset($value['count'])) {
                        $quantity = $value['count'];
                    }
                    else if(is_numeric($value)) {
                        $quantity = $value;
                    }
                    $quantity = preg_replace('/[^0-9.]/', "", $quantity);
                    if(is_numeric($quantity)) {
                        $this->arOfferQuantity["QUANTITY"] = intval($quantity);
                    }
                }
            }
        }
        else if(isset($this->settings["offer"]["one"]["quantity"]) && $this->settings["offer"]["one"]["quantity"]) {
            $attr = $this->settings["offer"]["one"]["quantity"];
            $quantity = pq($offer)->attr($attr);
            $quantity = trim(strip_tags($quantity));
            $value = $this->findAvailabilityValue($quantity);
            if($value && isset($value['count'])) {
                $quantity = $value['count'];
            }
            else if(is_numeric($value)) {
                $quantity = $value;
            }
            $quantity = preg_replace('/[^0-9.]/', "", $quantity);
            if(is_numeric($quantity)) {
                $this->arOfferQuantity["QUANTITY"] = intval($quantity);
            }
        }
        if(!isset($this->arOfferQuantity["QUANTITY"])) {
            $quantity = $this->GetQuantity();
            if(is_numeric($quantity)) {
                $this->arOfferQuantity["QUANTITY"] = $quantity;
            }
            else {
                return false;
            }
        }
        
        return true;
    }
    
    protected function GetQuantity() {
        if(isset($this->arFields["AVAILABLE_DETAIL"]) && is_numeric($this->arFields["AVAILABLE_DETAIL"])) {
            return $this->arFields["AVAILABLE_DETAIL"];
        }
        else if(isset($this->arFields["AVAILABLE_PREVIEW"]) && is_numeric($this->arFields["AVAILABLE_PREVIEW"])) {
            return $this->arFields["AVAILABLE_PREVIEW"];
        }
        else {
            if(is_numeric($this->settings["catalog"]["count_default"])) {
                return intval($this->settings["catalog"]["count_default"]);
            }
            else return false;
        }
        
        return false;
    }
    
    protected function parseOfferPropsXml($offer) {
        if(parent::checkUniq() && !$this->isUpdate)
            return false;
        if(isset($this->settings["offer"]["selector_prop"]) && !empty($this->settings["offer"]["selector_item"])) {
            $deleteSymb = parent::getOfferDeleteSelector();
            $deleteSymbRegular = parent::getOfferDeleteSelectorRegular();
            $arProperties = $this->arSelectorPropertiesOffer;
            foreach($arProperties as $code => $val) {
                $arProp = $this->arPropertiesOffer[$code];
                if($arProp["PROPERTY_TYPE"] == "F") {
                    parent::parseCatalogPropFile($code, $offer);
                }
                else {
                    $arr = parent::GetArraySrcAttr($this->settings["offer"]["selector_prop"][$code]);
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
                    parent::parseCatalogPropOffer($code, $val, $text);
                }
            }
        }
    }
    
    protected function parserOffersOne(&$el) {
        $offerItem = trim($this->settings["offer"]["one"]["selector"]);
        foreach(pq($el)->find($offerItem) as $offer) {
            if(empty($this->settings["offer"]["one"]["separator"])) {
                $this->boolOffer = true;
                if($this->parseOfferNameOneXml($offer)) {
                    parent::parseOfferPrice($offer);
                    parent::parseOfferAdditionalPrice($offer);
                    $this->parseOfferQuantity($offer);
                    parent::parseOfferProps($offer);
                    if(!parent::parseOfferGetUniq()) {
                        parent::deleteOfferFields();;
                        continue 1;
                    }
                }
                else
                    continue 1;
                $this->arOfferAll["FIELDS"][] = $this->arOffer;
                $this->arOfferAll["PRICE"][] = $this->arPriceOffer;
                $this->arOfferAll["QUANTITY"][] = $this->arOfferQuantity;
                parent::deleteOfferFields();
            }
            else if(!empty($this->settings["offer"]["one"]["separator"])) {
                if(is_array($arrOffers = $this->GetArrayNameOffes($offer))) {
                    foreach($arrOffers as $nameOffer) {
                        if(empty($nameOffer))
                            continue 1;
                        $this->boolOffer = true;
                        if($this->parseOfferNameOneXml($offer, $nameOffer)) {
                            parent::parseOfferPrice($offer);
                            parent::parseOfferAdditionalPrice($offer);
                            $this->parseOfferQuantity($offer);
                            parent::parseOfferProps($offer, $nameOffer);
                            if(!parent::parseOfferGetUniq()) {
                                parent::deleteOfferFields();;
                                continue 1;
                            }
                        }
                        else
                            continue 1;
                        $this->arOfferAll["FIELDS"][] = $this->arOffer;
                        $this->arOfferAll["PRICE"][] = $this->arPriceOffer;
                        $this->arOfferAll["QUANTITY"][] = $this->arOfferQuantity;
                        parent::deleteOfferFields();
                    }
                }
            }
        }
    }
    
    protected function parseOfferNameOneXml($offer, $nameOffer = false) {
        if(!empty($this->settings["offer"]["one"]["selector"]) && !empty($this->settings["offer"]["add_name"])) {
            if($nameOffer === false) {
                $arr = parent::GetArraySrcAttr($this->settings["offer"]["selector_name"]);
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
            }
            else if($nameOffer !== false) {
                $name = $nameOffer;
            }
            $deleteSymb = parent::getOfferDeleteSelector();
            $name = str_replace($deleteSymb, "", $name);
            $this->arOffer["NAME"] = htmlspecialchars_decode($name);
            if(isset($this->settings["loc"]["f_name"]) && $this->settings["loc"]["f_name"] == "Y") {
                $this->arOffer["NAME"] = parent::locText($this->arOffer["NAME"]);
            }
        }
        if(!isset($this->arOffer["NAME"]) && (!isset($this->settings["offer"]["add_name"]) || empty($this->settings["offer"]["add_name"]))) {
            $this->errors[] = GetMessage("parser_error_name_notfound_offer");
            
            return false;
        }
        else if(!isset($this->arOffer["NAME"]))
            $this->arOffer["NAME"] = $this->arFields["NAME"];
        
        return true;
    }
    
    protected function GetArrayNameOffes($offer) {
        $arr = parent::GetArraySrcAttr($this->settings["offer"]["selector_name"]);
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
        $arrName = explode($this->settings["offer"]["one"]["separator"], $name);
        for($i = 0; $i < count($arrName); $i++) {
            $arrName[$i] = trim($arrName[$i]);
        }
        
        return $arrName;
    }
    
    protected function addAvailable() {
        if($this->elementUpdate && (!$this->isUpdate || !$this->isUpdate["count"]))
            return false;
        //if(!isset($this->arFields["AVAILABLE_DETAIL"]) && !isset($this->arFields["AVAILABLE_PREVIEW"])) return false;
        $isElement = $this->elementUpdate;
        $QUANTITY = $this->GetQuantity();
        if(is_numeric($QUANTITY)) {
            if(!$isElement)                                 //add QUANTITY
            {
                $q = CCatalogProduct::Add(array(
                    "ID" => $this->elementID,
                    "QUANTITY" => $QUANTITY
                ));
                if($q === false) {
                    $this->errors[] = $this->arFields["NAME"]."[".$this->arFields["LINK"]."] ".GetMessage("parser_error_add_quantity");
                }
            }
            else {                                               //update QUANTITY
                $q = CCatalogProduct::Update($this->elementID, array("QUANTITY" => $QUANTITY));
                if($q === false) {
                    $this->errors[] = $this->arFields["NAME"]."[".$this->arFields["LINK"]."] ".GetMessage("parser_error_add_quantity");
                }
            }
        }
    }
    
    protected function ValidateUrl($url) {
        if(preg_match("/^(http|https)?(:\/\/)?([A-Z0-9][A-Z0-9_-]*(?:\..[A-Z0-9][A-Z0-9_-]*)+):?(d+)?\/?/Diu", $url)) //�������� �����������
        {
            return true;
        }
        else {
            return false;
        }
    }
    
    protected function ValidateFtpUrl($url) {
        if(preg_match("/^(ftp|ftps)?(:\/\/)?([a-zA-Z0-9_-]+:[a-zA-Z0-9_-]+@)?([A-Z0-9][A-Z0-9_-]*(?:\..[A-Z0-9][A-Z0-9_-]*)+):?(d+)?\/?/Diu", $url)) //�������� �����������
        {
            return true;
        }
        else {
            return false;
        }
    }
    
    protected function ParseCatalogAvailableDetail(&$el) {
        if(!empty($this->settings["catalog"]["detail_count"])) {
            if(parent::checkUniq() && (!$this->isUpdate || !$this->isUpdate["count"]))
                return false;
            $arr = parent::GetArraySrcAttr($this->settings["catalog"]["detail_count"]);
            $path = $arr["path"];
            $attr = $arr["attr"];
            if(empty($attr)) {
                $available = pq($el)->find($path)->html();
            }
            else if(!empty($attr)) {
                $available = pq($el)->find($path)->attr($attr);
            }
            else if(empty($path) && !empty($attr)) {
                $available = pq($el)->attr($attr);
            }
            $available = trim(strip_tags($available));
            $value = $this->findAvailabilityValue($available);
            if($value && isset($value['count'])) {
                $available = $value['count'];
            }
            else if(is_numeric($value)) {
                $available = $value;
            }
            $available = preg_replace('/[^0-9.]/', "", $available);
            if(is_numeric($available)/*&& strlen($available)>0*/) {
                $available = intval($available);
                if($available == 0) {
                    $this->arFields["AVAILABLE_DETAIL"] = 0;
                }
                else {
                    $this->arFields["AVAILABLE_DETAIL"] = $available;
                }
            }
        }
        else if(is_numeric($this->settings["catalog"]["count_default"])) {
            $this->arFields["AVAILABLE_DETAIL"] = intval($this->settings["catalog"]["count_default"]);
        }
    }
    
    protected function AddQuantityCatalogOffer($arQuantity, $arFields) {
        if($this->elementOfferUpdate && (!$this->isUpdate || !$this->isUpdate["count"]))
            return false;
        //if(!$this->arPrice || !$this->arPrice["PRICE"]) return false;
        $isElement = $this->elementOfferUpdate;
        /*$this->arPrice["PRODUCT_ID"] = $this->elementOfferID;
        $this->ChangePrice();
        $this->ConvertCurrency();*/
        //$obPrice = new CPrice();
        if(!$isElement) {
            $q = CCatalogProduct::Add(array(
                "ID" => $this->elementOfferID,
                "QUANTITY" => $arQuantity["QUANTITY"]
            ));
            if($q === false) {
                $this->errors[] = $this->arFields["NAME"]." - ".$arFields["NAME"]."[".$this->arFields["LINK"]."] ".GetMessage("parser_error_add_price_offer");
            }
        }
        else {
            $q = CCatalogProduct::Update($this->elementOfferID, $arQuantity);
            if($q === false) {
                $this->errors[] = $this->arFields["NAME"]." - ".$arFields["NAME"]."[".$this->arFields["LINK"]."] ".GetMessage("parser_error_add_price_offer");
            }
        }
    }
}

?>