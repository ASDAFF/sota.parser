<?
use Bitrix\Highloadblock as HL; 
use Bitrix\Main\Entity;

\Bitrix\Main\Loader::includeModule('highloadblock');  

class SotbitHLCatalogParser extends SotbitXlsParser {
  
    public function __construct()
    {
        parent::__construct();                      
    } 
    
    function getUniqElementHL(){
        if($this->settings["catalog"]["uniq"]["prop"])
        {
            unset($this->uniqFields);    
            $prop = $this->settings["catalog"]["uniq"]["prop"];
            $this->uniqFields[$prop] = $prop;
        }       
    }

    protected function isUpdateElementHL()
    {
        $this->updateActive = false;
        if($this->settings["catalog"]["update"]["active"])
        {
            unset($this->settings["catalog"]["update"]["active"]);
            $this->updateActive = true;
            foreach($this->settings["catalog"]["update"]["props"] as $id=>$val)
            {
                if($val=="Y") $this->isUpdate[$id] = $val;
            }
            if(!isset($this->isUpdate) || !$this->isUpdate) $this->isUpdate = false;
        } else $this->isUpdate = false;
    }
    
    protected function GetSortFieldsHL()
    {
        $this->arSortUpdate = array();
        $this->arEmptyUpdate = array();
        if($this->isUpdate)
        {
            foreach($this->isUpdate as $id=>$val)
            {
                if($val!="empty") continue;
                $this->arSortUpdate[] = $id;
            }
        }    
    }

    protected function getArrayHLblockHL()
    {                                                           
        $this->arrayIblock = array();
    }

    protected function CheckFieldsHL($settings)
    {   
        if(preg_match("/\D/", $settings["pagenavigation_begin"]) && $settings["pagenavigation_begin"]!="")
        {
            $this->errors[] = GetMessage("parser_error_pagenavigation_begin");
        }
        if(preg_match("/\D/", $settings["pagenavigation_end"]) && $settings["pagenavigation_end"]!="")
        {
            $this->errors[] = GetMessage("parser_error_pagenavigation_end");
        }
        if(preg_match("/\D/", $settings["step"]))
        {
            $this->errors[] = GetMessage("parser_error_step");
        }             
        
        global $DB;   
        $hlblock = HL\HighloadBlockTable::getById($this->iblock_id)->fetch();          
        $res = $DB->Query('SHOW COLUMNS FROM '.$hlblock['TABLE_NAME']);
        while($column = $res->fetch()){    
            if(strpos($column['Field'], 'UF_')===false)
                continue;                 
            $q = CUserTypeEntity::GetList(array(),array(
                'ENTITY_ID' => 'HLBLOCK_'.$this->iblock_id,
                'FIELD_NAME' => $column['Field'],
            ))->fetch();                                
             
            $this->arProperties[$column['Field']] = $q;     
        }      
        
        if(isset($this->arSelectorProduct))
            unset($this->arSelectorProduct);
        if(isset($this->arFindProduct))
            unset($this->arFindProduct);
        if(isset($this->arSelectorProperties))
            unset($this->arSelectorProperties);
        if(isset($this->arSelectorPropertiesOffer))
            unset($this->arSelectorPropertiesOffer);
        if(isset($this->arFindPropertiesOffer))
            unset($this->arFindPropertiesOffer);      
        if(isset($this->arDubleFindPropertiesOffer))
            unset($this->arDubleFindPropertiesOffer);   
            
        $this->arSelectorPropertiesPreview = $this->getSelectorPropertiesPreviewHL();     
        $this->arFindProperties = $this->getFindProperties();           
        $this->arSelectorProperties = $this->getSelectorPropertiesHL();
        $this->arDubleFindProperties = $this->getFindDubleProperties();             
        $this->arFindPropertiesPreview = $this->getFindPropertiesPreview();    
        $this->arDubleFindPropertiesPreview = $this->getFindDublePropertiesPreview();    
    }
    
    protected function getSelectorPropertiesPreviewHL()
    {
        if(isset($this->settings["properties"]["preview"]) && !empty($this->settings["properties"]["preview"]))
        {
            $arProps = false;
            foreach($this->settings["properties"]["preview"] as $i=>$prop)
            {
                $prop = trim($prop['value']);
                if(!empty($prop))
                {
                    $arProps[$i] = $prop;
                }
            }
            if(!$arProps) return false;
            return $arProps;    
        }
       
        return false;
    }

    protected function parseCatalogProductsHL()
    {
        $this->isCatalog = false;
        $hlblock = HL\HighloadBlockTable::getById($this->iblock_id)->fetch();
        $entity = HL\HighloadBlockTable::compileEntity($hlblock);
        $this->entityClass = $entity->getDataClass();
        
        $count = 0;
        
        $this->activeCurrentPage++;
        $this->SetCatalogElementsResult($this->activeCurrentPage);
        
        $element = htmlspecialchars_decode($this->settings["catalog"]["selector"]); 

        if($this->preview_delete_element)$this->deleteCatalogElement($this->preview_delete_element, $element, $this->html[$element]);
        if($this->preview_delete_attribute)$this->deleteCatalogAttribute($this->preview_delete_attribute, $element, $this->html[$element]);
        $i = 0;
        $ci = 0;                        
        
        $count = count($this->html[$element]);  

        if($this->settings["catalog"]["mode"]!="debug" && !$this->agent)
        {
            if($count>$this->settings["catalog"]["step"] && ($this->settings["catalog"]["mode"]!="debug" && !$this->agent))
                $countStep = $this->settings["catalog"]["step"];
            else{
                $this->stepStart = true;
                if($this->CheckOnePageNavigation() || $this->CheckAlonePageNavigation($this->currentPage)) $this->pagenavigation[$this->rss] = $this->rss;
                $this->SaveCurrentPage($this->pagenavigation);
                $this->SavePrevPage($this->sectionPage);
                $countStep = $count;
            }    
        }else{
            $countStep = $count;    
        }
            
        file_put_contents($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/shs.parser/include/count_parser".$this->id.".txt", $countStep."|".$ci);    
        
        if($count==0)
        {
            $this->errors[] = GetMessage("parser_error_selector_notfound")."[".$element."]";
            $this->clearFields();    
        }    

        foreach($this->html[$element] as $el)
        {      
            $ci++;
            if($this->StepContinue($ci, $count)) continue;
            if ($this->typeN == "xml") $debug_item = SotbitXmlParser::DEFAULT_DEBUG_ITEM;
            else $debug_item = self::DEFAULT_DEBUG_ITEM;
            if($i==$debug_item && $this->settings["catalog"]["mode"]=="debug") break;
            if($this->typeN=="catalog")
            {
               $this->parseCatalogProductElementHL($el); 
            }
            if($this->typeN=="xml")
            {
                $this->parseCatalogProductElementXmlHL($el);
            }
            $i++;
            file_put_contents($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/shs.parser/include/count_parser".$this->id.".txt", $countStep."|".$i);
            $this->CalculateStep($count); 
        }
        unlink($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/shs.parser/include/count_parser".$this->id.".txt"); 
    }
    
    protected function parseCatalogProductElementHL(&$el){
        $this->countItem++;
            
        if(!$this->parserCatalogPreviewHL($el))
        {                    
            $this->SaveCatalogError();
            $this->clearFields();
            return false;    
        }                                         

        $this->parserCatalogDetailHL();           
        
        $this->parseCatalogMetaHL();
        $this->parseCatalogFirstUrlHL();   

        $db_events = GetModuleEvents("shs.parser", "parserBeforeAddElementCatalog", true); //27.10.2015
        $error = false;
        foreach($db_events as $arEvent)
        {
            $bEventRes = ExecuteModuleEventEx($arEvent, array(&$this, &$el));
            if($bEventRes===false)
            {
                $error = true;
                break 1;
            }
        }

        if(!$error && !$error_isad) {
            $this->AddElementCatalogHL();   
            foreach(GetModuleEvents("shs.parser", "parserAfterAddElementCatalog", true) as $arEvent)
                ExecuteModuleEventEx($arEvent, array(&$this, &$el));   
        }                                                                      
        /*if($this->settings['smart_log']['enabled']=='Y' && $this->elementID) {        
            $this->settings['smart_log']['result_id'] = file_get_contents($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/shs.parser/include/result_id".$this->id.".txt");
            SmartLogs::saveNewValues($this->elementID, $this->settings["smart_log"], $this->arFields, isset($this->arPrice['PRICE'])?$this->arPrice['PRICE']:null, $this->arProduct);
        } */       

        $this->SetCatalogElementsResult();
        $this->clearFilesTemp();
        $this->clearFields();
    }
    
    protected function GetElementFilterHL()
    {
        if($this->settings["catalog"]["enable_props_filter"] != "Y"){
            return true;
        }
        
        if($this->settings["catalog"]["enable_props_filter"] == "Y") {        
            if(isset($this->settings["props_filter_value"]) && count($this->settings["props_filter_value"]) > 0) {
                if(isset($this->propsFilter) && count($this->propsFilter) > 0)
                {                                
                    $count = 0;                  
                    foreach($this->propsFilter as $id => $val)
                    {
                        if($val == "Y")
                        {
                            $count ++;
                            break;
                        } 
                    }
                    if($count > 0) return true;
                    else return false;
                    
                }
                else 
                {
                    return false;
                }    
            } else {
                return true;
            }
        }
    }

    protected function AddElementCatalogHL() {      
        if($this->checkUniqHL() && !$this->isUpdate) return false;   
        //if($this->GetElementFilterHL() === false) return false;            
        $isElement = $this->checkUniqHL();
        $entity_class = $this->entityClass;                                                   
        $this->boolUpdate = true;      
        if(!$isElement) {
            if($this->settings["catalog"]["update"]["add_element"] == "Y") {
                $this->errors[] = "[".$this->arFields["LINK"]."] - ".GetMessage("parser_error_id_not_add_element");
                return false;
            }                                                        
            $res = $entity_class::Add($this->arFields["FIELDS"]);    
            $id = $res->getId();                           
            /* if($this->settings['smart_log']['enabled']=='Y') {
                SmartLogs::saveOldValues($isElement,$this->settings['smart_log'], $this->iblock_id,$id);
            } */
            
            if(!$id)
            {   
                $err = $res->getErrorMessages();
                $this->errors[] = "[".$this->arFields["LINK"]."] - ".$err[0];            
            }else{
                $this->elementID = $id; 
                $this->addTmp($id);
                //$this->addSeoUniqYandex($this->arFields);     
            }
            unset($res); 
        } else {                       
            $this->clearFieldsUpdateHL();
            $this->elementID = $isElement;
            /*
            if($this->settings['smart_log']['enabled']=='Y') {
                SmartLogs::saveOldValues($isElement,$this->settings['smart_log'], $this->iblock_id ,$this->elementID);         
            } */
            if(!empty($this->arFields["FIELDS"])){
                $entity_class::Update($isElement, $this->arFields["FIELDS"]);
                $this->addTmp($isElement);
            }
        }            
    }
                           
    protected function clearFieldsUpdateHL()
    {   
        $arFields = $this->arFields["FIELDS"];
        unset($this->arFields["FIELDS"]);     
        $this->arFields["FIELDS"]= array();     
        if($this->checkUniqHL() && ($this->isUpdate))
        {     
            foreach($this->isUpdate as $upd=>$val){
                if(isset($arFields[$upd]))
                    $this->arFields["FIELDS"][$upd] = $arFields[$upd];
            }
        }                                          
        unset($arFields);   
    }

    protected function parseCatalogFirstUrlHL()
    {
        if($this->checkUniq()) return false;
        if($this->first_title!="N")
        {
            $this->arFields["FIELDS"][$this->first_title] = $this->arFields["LINK"];
        }
    }

    protected function parseCatalogMetaHL()
    {
        if($this->checkUniq()) return false;
        if($this->meta_description!="N" || $this->meta_keywords!="N")
        {
            foreach($this->detailHtml["meta"] as $meta)
            {
                if($this->meta_description!="N" && strtolower(pq($meta)->attr("name"))=="description")
                {
                    $meta_text = pq($meta)->attr("content");
                    if(!$meta_text)$meta_text = pq($meta)->attr("value");
                    if(strtoupper(LANG_CHARSET)=="WINDOWS-1251"/* && strtoupper(LANG_CHARSET)!=strtoupper($shs_DOC_ENCODING)*/) {
                        $meta_text = mb_convert_encoding($meta_text, LANG_CHARSET, "utf-8");
                    }
                    $this->arFields["PROPERTY_VALUES"][$this->meta_description] = strip_tags($meta_text);
                }elseif($this->meta_keywords!="N" && strtolower(pq($meta)->attr("name"))=="keywords")
                {
                    $meta_text = pq($meta)->attr("content");
                    if(!$meta_text)$meta_text = pq($meta)->attr("value");
                    if(strtoupper(LANG_CHARSET)=="WINDOWS-1251"/* && strtoupper(LANG_CHARSET)!=strtoupper($shs_DOC_ENCODING)*/) {
                        $meta_text = mb_convert_encoding($meta_text, LANG_CHARSET, "utf-8");
                    }
                    $this->arFields["FIELDS"][$this->meta_keywords] = strip_tags($meta_text);
                }
                unset($meta_text);
            }
        }

        if($this->meta_title!="N")
        {
            $meta_title = pq($this->detailHtml["head:eq(0) title:eq(0)"])->text();
            $meta_title = strip_tags($meta_title);
            if(strtoupper(LANG_CHARSET)=="WINDOWS-1251"/* && strtoupper(LANG_CHARSET)!=strtoupper($shs_DOC_ENCODING)*/) {
                $meta_title = mb_convert_encoding($meta_title, LANG_CHARSET, "utf-8");
            }
            $this->arFields["FIELDS"][$this->meta_title] = $meta_title;
        }   
    }

    protected function parserCatalogPreviewHL(&$el)
    {    
        foreach(GetModuleEvents("shs.parser", "parserCatalogPreview", true) as $arEvent)
            ExecuteModuleEventEx($arEvent, array($this->id, &$el, &$this->arFields));
            
        if(!$this->parseCatalogUrlPreview($el)) return false;   
        $this->parseCatalogPropertiesPreviewHL($el);              
        return true;
    }

    protected function parseCatalogPropertiesPreviewHL(&$el)
    {
        if($this->checkUniqHL() && !$this->isUpdate) return false;
        $this->parseCatalogSelectorPropertiesPreviewHL($el);
        $this->parseCatalogFindPropertiesPreviewHL($el);      
    }

    protected function parseCatalogFindPropertiesPreviewHL(&$el)
    {   
        $arProperties = $this->arFindPropertiesPreview;
        if(!$arProperties) return false;
        $find = htmlspecialchars_decode($this->settings["catalog"]["selector_find_props_preview"]);
        if($this->settings["catalog"]["catalog_delete_selector_find_props_symb_preview"])
        {
            $deleteSymb = explode(",", $this->settings["catalog"]["catalog_delete_selector_find_props_symb_preview"]);

            foreach($deleteSymb as $i=>&$symb)
            {
                $symb = trim($symb);
                $symb = htmlspecialcharsBack($symb);  
                if(empty($symb))
                {
                    unset($deleteSymb[$i]);
                    continue;
                }
                if($symb=="\\\\")
                {
                    $deleteSymb[$i] = ",";
                }

            }
        }
        $arFind = explode(",", $find);
        foreach($arFind as $vFind)
        {
            if(strpos($vFind, " br")!==false || strpos($vFind, "<br/>") || strpos($vFind, "<br />"))
            {
                $vFind = str_replace(array(" br", "<br/>", "<br />"), "", $vFind);
                $vFind = trim($vFind);
                $arBr = array("<br>", "<br/>", "<br />");
                
                foreach(pq($el)->find($vFind) as $prop)
                {
                    $text = pq($prop)->html();
                    $text = str_replace($arBr, "<br>", $text);
                    unset($arBr[1]);
                    unset($arBr[2]);
                    foreach($arBr as $br)
                    {
                        $arTextBr = explode($br, $text);
                        if(!empty($arTextBr) && count($arTextBr)>1)
                        {
                            foreach($arTextBr as $textBr)
                            {   
                                $textBr = strip_tags($textBr);
                                $textBr = str_replace($deleteSymb, "", $textBr); 
                                foreach($arProperties as $code=>$val)
                                {
                                    //if(preg_match("/".$val."/", $textBr))
                                    if($this->CheckFindPropsPreview($code, $val, $textBr))
                                    {
                                        $this->parseCatalogPropHL($code, $val, $textBr);
                                    }    
                                }
                                
                            }      
                        }
                    }
                    
                }
            }else
            {
                foreach(pq($el)->find($vFind) as $prop)
                {   
                    $text = pq($prop)->html();
                    //$text = strip_tags($text);
                    $text = str_replace($deleteSymb, "", $text);
                    $text1 = $text;
                    foreach($arProperties as $code=>$val)
                    {
                        //if(preg_match("/".$val."/", $text))
                        $text1 = $text;
                        $arProp = $this->arProperties[$code];         
                        $text1 = strip_tags($text);
                        if($this->CheckFindPropsPreview($code, $val, $text1))
                        {   
                            $this->parseCatalogPropHL($code, $val, $text1);
                        }
                    }

                }    
            }    
        }
    }

    protected function checkUniqHL()
    {                                
        if($this->elementUpdate) return $this->elementUpdate;
        if(!isset($this->arSortUpdate)) $this->arSortUpdate = array();
        $arFields = array();        
        if($this->settings["catalog"]["uniq"]["prop"])
        {
            $prop = $this->settings["catalog"]["uniq"]["prop"];
            if($this->arFields["FIELDS"][$prop])
                $arFields[$prop] = $this->arFields["FIELDS"][$prop];
        }
        if(count($arFields)==count($this->uniqFields)) {
            $entity_class = $this->entityClass;
            $isElement = $entity_class::getList(array(
                "select" => array("*"),
                "order" => array("ID" => "ASC"),                     
                "filter" => $arFields,
                "limit" => 1,
            ))->fetch();                                                                                                                                                                                      
        }
        $this->elementUpdate = $isElement["ID"];
        if($isElement)
        {                               
            $this->arEmptyUpdate = $isElement;
            return $isElement["ID"];
        }
        else return false;   
        return false;
    }

    protected function parseCatalogSelectorPropertiesPreviewHL(&$el)
    {
        $arProperties = $this->arSelectorPropertiesPreview;
        if(!$arProperties) return false;
        if($this->settings["catalog"]["catalog_delete_selector_props_symb_preview"])
        {
            $deleteSymb = explode(",", $this->settings["catalog"]["catalog_delete_selector_props_symb_preview"]);

            foreach($deleteSymb as $i=>&$symb)
            {
                $symb = trim($symb);
                $symb = htmlspecialcharsBack($symb);
                if(empty($symb))
                {
                    unset($deleteSymb[$i]);
                    continue;
                }
                if($symb=="\\\\")
                {
                    $deleteSymb[$i] = ",";
                }

            }
        }
 
        foreach($arProperties as $code=>$val)
        {      
            $arProp = $this->arProperties[$code];  
            
            $ar = $this->GetArraySrcAttr(htmlspecialchars_decode($this->settings["properties"]["preview"][$code]["value"]));
            $path = $ar["path"];
            $attr = $ar["attr"];
            if($attr)
                $text = pq($el)->find($path)->attr($attr);
            else
                $text = pq($el)->find($this->settings["properties"]["preview"][$code]["value"])->html();          
            $text = strip_tags($text);
            $text = str_replace($deleteSymb, "", $text);
            $this->parseCatalogPropHL($code, $val, $text);  
        }                       
    }
    
    public function parseCatalogPropHL($code, $val, $text)
    {                                                           
        $val = preg_quote($val, "/");
        $text = preg_replace("/(".$val.")/", "", $text, 1); 
        $val = trim($text);
                                                  
        $val = html_entity_decode($val);
                                                                                              
        $this->filterProps($code, $val);
        
        $arProp = $this->arProperties[$code];
                                                                                   
        if(isset($this->settings["loc"]["f_props"]) && $this->settings["loc"]["f_props"])
            $val = $this->locText($val, "plain");
        
        if($arProp["USER_TYPE_ID"]=="string" && $arProp["MULTIPLE"]!="Y")
        {
            $val = $this->actionFieldProps($code, $val);
            $this->arFields["FIELDS"][$code] = $val;
        }elseif($arProp["USER_TYPE_ID"]=="string" && $arProp["MULTIPLE"]=="Y")
        {
            $val = $this->actionFieldProps($code, $val);
            $this->arFields["FIELDS"][$code][] = $val;
        }
        elseif($arProp["USER_TYPE_ID"]=="double")
        {
            $val =  str_replace(",", ".", $val);
            $val = preg_replace("/\.{1}$/", "", $val);
            $val = preg_replace('/[^0-9.]/', "", $val);
            $this->arFields["FIELDS"][$code] = $val;    

        }
        elseif($arProp["USER_TYPE_ID"]=="integer")
        {
            $val =  str_replace(",", ".", $val);
            $val = preg_replace("/\.{1}$/", "", $val);
            $val = preg_replace('/[^0-9.]/', "", $val);
            $this->arFields["FIELDS"][$code] = intval($val);    

        }
        elseif($arProp["USER_TYPE_ID"]=="boolean" && $arProp["MULTIPLE"]=="Y")
        {   
            if($val == 'N' || $val == false || $val == 0)
                $this->arFields["FIELDS"][$code]["n0"] = 0;
            else 
                $this->arFields["FIELDS"][$code]["n0"] = $val;
        }
        elseif($arProp["USER_TYPE_ID"]=="boolean" && $arProp["MULTIPLE"]!="Y")
        {   
            if($val == 'N' || $val == false || $val == 0)
                $this->arFields["FIELDS"][$code] = 0;
            else 
                $this->arFields["FIELDS"][$code] = $val;
        }
        elseif($arProp["USER_TYPE_ID"]=="enumeration" && $arProp["MULTIPLE"]!="Y")
        {
            $this->arFields["FIELDS"][$code] = $this->CheckPropsLHL($arProp["ID"], $code, $val);
        }
        elseif($arProp["USER_TYPE_ID"]=="enumeration" && $arProp["MULTIPLE"]=="Y")
        {
            $this->arFields["FIELDS"][$code]["n0"] = $this->CheckPropsLHL($arProp["ID"], $code, $val);
        }
        elseif($arProp["USER_TYPE_ID"]=="iblock_element " && $arProp["MULTIPLE"]!="Y")
        {   
            $this->arFields["FIELDS"][$code] = $this->CheckPropsEHL($arProp, $code, $val);
        }   
        unset($arProp);
    }

    public function CheckPropsLHL($id, $code, $val)
    {         
        $res2 = CUserFieldEnum::GetList(array(),array(
            'USER_FIELD_ID' => $id,
            'VALUE' => $val,
                
        ));

        if ($arRes2 = $res2->Fetch())
        {
            $kz = $arRes2["ID"];
        }
        else
        {
            $tmpid = md5(uniqid(""));
            $obEnum = new CUserFieldEnum;
            $res = $obEnum->SetEnumValues($id, array(
                "n0" => array(
                    "VALUE" => $val,
                ),
            ));  
            if($res){
                $res2 = CUserFieldEnum::GetList(array(),array(
                    'USER_FIELD_ID' => $id,
                    'VALUE' => $val,   
                ));

                if ($arRes2 = $res2->Fetch())
                {
                    $kz = $arRes2["ID"];
                } else return null;
            } 
        }

        return $kz;
    }
    
    public function CheckPropsEHL($arProp, $code, $val)
    {
        $IBLOCK_ID = $arProp["SETTINGS"]["IBLOCK_ID"];

        $rsProp = CIBlockElement::GetList(Array(), array("IBLOCK_ID"=>$IBLOCK_ID, "%NAME"=>$val), false, false, array("ID", "NAME"));
        while($arIsProp = $rsProp->Fetch())
        {
            $arIsProp["NAME"] = mb_strtolower($arIsProp["NAME"], LANG_CHARSET); 
            $val0 = mb_strtolower($val, LANG_CHARSET);
            if($val0==$arIsProp["NAME"])
            {
                $isProp = $arIsProp["ID"];
            }
        }
        
        if($isProp) return $isProp;
        else{
            $codeText = CUtil::translit($val, "ru", array(
                        "max_len" => 100,
                        "change_case" => 'L', // 'L' - toLower, 'U' - toUpper, false - do not change
                        "replace_space" => '_',
                        "replace_other" => '_',
                        "delete_repeat_replace" => true,
            ));
            $arFields = array(
                "NAME"=>$val,
                "ACTIVE" => "Y",
                "IBLOCK_ID" => $IBLOCK_ID,
                "CODE" => $codeText
            );
            $el = new CIBlockElement;
            $id = $el->Add($arFields);
            if(!$id)
            {
                $this->errors[] = GetMessage("error_add_prop_e").$this->arFields["NAME"]."[".$this->arFields["LINK"]."] - ".$el->LAST_ERROR;
            }
            unset($el);
            return $id;
        }
    }

    protected function parserCatalogDetailHL()
    {
        if($this->checkUniqHL() && !$this->isUpdate) return false;
        
        foreach(GetModuleEvents("shs.parser", "parserCatalogDetailBefore", true) as $arEvent)
            ExecuteModuleEventEx($arEvent, array($this->id, &$el, &$this->arFields));
        $el = $this->parserCatalogDetailPage();
        foreach(GetModuleEvents("shs.parser", "parserCatalogDetail", true) as $arEvent)
            ExecuteModuleEventEx($arEvent, array($this->id, &$el, &$this->arFields));
        
        $this->parseCatalogPropertiesHL($el);  
    }

    protected function parseCatalogPropertiesHL(&$el)
    {
        if($this->checkUniqHL() && !$this->isUpdate) return false;
        $this->parseCatalogDefaultPropertiesHL($el);
        $this->parseCatalogSelectorPropertiesHL($el);
        $this->parseCatalogFindPropertiesHL($el);                    
    }  
    
    protected function parseCatalogDefaultPropertiesHL(&$el)
    {
        if(isset($this->settings["properties"]["default"]) && !empty($this->settings["properties"]["default"]))
        {
            foreach($this->settings["properties"]["default"] as $code=>$val)
            {
                $val = $val["value"];
                if($val) $this->parseCatalogDefaultPropHL($code, $val);
            }
        }    
    }
     
    public function parseCatalogDefaultPropHL($code, $val)
    {
        $val = trim($val);  
        $arProp = $this->arProperties[$code];
        if(empty($val)) return false;                                                                
        if($arProp["USER_TYPE_ID"]=="string" && $arProp["MULTIPLE"]!="Y")
        {
            $val = $this->actionFieldProps($code, $val);
            $this->arFields["FIELDS"][$code] = $val;
        }elseif($arProp["USER_TYPE_ID"]=="string" && $arProp["MULTIPLE"]=="Y")
        {
            $val = $this->actionFieldProps($code, $val);
            $this->arFields["FIELDS"][$code][] = $val;
        }
        elseif($arProp["USER_TYPE_ID"]=="double")
        {
            $val =  str_replace(",", ".", $val);
            $val = preg_replace("/\.{1}$/", "", $val);
            $val = preg_replace('/[^0-9.]/', "", $val);
            $this->arFields["FIELDS"][$code] = $val;  
        }
        elseif($arProp["USER_TYPE_ID"]=="integer")
        {
            $val =  str_replace(",", ".", $val);
            $val = preg_replace("/\.{1}$/", "", $val);
            $val = preg_replace('/[^0-9.]/', "", $val);
            $this->arFields["FIELDS"][$code] = intval($val);      
        }
        elseif($arProp["USER_TYPE_ID"]=="enumeration" && $arProp["MULTIPLE"]!="Y")
        {
            $this->arFields["FIELDS"][$code] = $this->CheckPropsLHL($arProp["ID"], $code, $val);
        }
        elseif($arProp["USER_TYPE_ID"]=="enumeration" && $arProp["MULTIPLE"]=="Y")
        {
            $this->arFields["FIELDS"][$code]["n0"] = $this->CheckPropsLHL($arProp["ID"], $code, $val);
        }
        elseif($arProp["USER_TYPE_ID"]=="iblock_element " && $arProp["MULTIPLE"]!="Y")
        {   
            $this->arFields["FIELDS"][$code] = $this->CheckPropsEHL($arProp, $code, $val);
        }
        elseif($arProp["USER_TYPE_ID"]=="iblock_element " && $arProp["MULTIPLE"]=="Y")
        {   
            $this->arFields["FIELDS"][$code]["n0"] = $this->CheckPropsEHL($arProp, $code, $val);
        } 
        elseif($arProp["USER_TYPE_ID"]=="boolean" && $arProp["MULTIPLE"]=="Y")
        {   
            if($val == 'N' || $val == '')
                $this->arFields["FIELDS"][$code]["n0"] = 0;
            else 
                $this->arFields["FIELDS"][$code]["n0"] = $val;
        }
        elseif($arProp["USER_TYPE_ID"]=="boolean" && $arProp["MULTIPLE"]!="Y")
        {   
            if($val == 'N' || $val == '')
                $this->arFields["FIELDS"][$code] = 0;
            else 
                $this->arFields["FIELDS"][$code] = $val;
        }                                    
        unset($arProp);                                          
    } 

    protected function getSelectorPropertiesHL()
    {
        if(isset($this->settings["properties"]["detail"]) && !empty($this->settings["properties"]["detail"]))
        {
            $arProps = false;
            foreach($this->settings["properties"]["detail"] as $i=>$prop)
            {                       
                $prop = $prop["value"];
                $prop = trim($prop);
                if($prop!='')
                {
                    $arProps[$i] = $prop;
                }
            }
            if(!$arProps) return false;
            return $arProps;    
        }
        
        return false;
    }

    protected function parseCatalogSelectorPropertiesHL(&$el)
    {                                             
        
        $arProperties = $this->arSelectorProperties;       
        if(!$arProperties) return false;
        if($this->settings["catalog"]["catalog_delete_selector_props_symb"])
        {
            $deleteSymb = explode(",", $this->settings["catalog"]["catalog_delete_selector_props_symb"]);

            foreach($deleteSymb as $i=>&$symb)
            {
                $symb = trim($symb);
                $symb = htmlspecialcharsBack($symb);
                if(empty($symb))
                {
                    unset($deleteSymb[$i]);
                    continue;
                }
                if($symb=="\\\\")
                {
                    $deleteSymb[$i] = ",";
                }

            }
        }
                                       
        foreach($arProperties as $code=>$val)
        {
            $arProp = $this->arProperties[$code];        
            if($arProp["USER_TYPE_ID"]=="file")
            {
                $this->parseCatalogPropFileHL($code, $el);
            }else{
                $ar = $this->GetArraySrcAttr(htmlspecialchars_decode($this->settings["properties"]["detail"][$code]["value"]));
                $path = $ar["path"];
                $attr = $ar["attr"];
                
                if($attr)
                    $text = pq($el)->find($path)->attr($attr);
                else
                    $text = pq($el)->find($path)->html();   
                $text = strip_tags($text);
                $text = str_replace($deleteSymb, "", $text);
                
                $this->parseCatalogPropHL($code, $val, $text);
            }  
        }
    }

    protected function parseCatalogFindPropertiesHL(&$el)
    {   
        $arProperties = $this->arFindProperties;
        if(!$arProperties) return false;
        $find = htmlspecialchars_decode($this->settings["catalog"]["selector_find_props"]);
        if($this->settings["catalog"]["catalog_delete_selector_find_props_symb"])
        {
            $deleteSymb = explode(",", $this->settings["catalog"]["catalog_delete_selector_find_props_symb"]);

            foreach($deleteSymb as $i=>&$symb)
            {
                $symb = trim($symb);
                $symb = htmlspecialcharsBack($symb);  
                if(empty($symb))
                {
                    unset($deleteSymb[$i]);
                    continue;
                }
                if($symb=="\\\\")
                {
                    $deleteSymb[$i] = ",";
                }

            }
        }
        $arFind = explode(",", $find);
        foreach($arFind as $vFind)
        {             
            if(strpos($vFind, " br")!==false || strpos($vFind, "<br/>") || strpos($vFind, "<br />"))
            {
                $vFind = str_replace(array(" br", "<br/>", "<br />"), "", $vFind);
                $vFind = trim($vFind);
                $arBr = array("<br>", "<br/>", "<br />");
                
                foreach(pq($el)->find($vFind) as $prop)
                {
                    $text = pq($prop)->html();
                    $text = str_replace($arBr, "<br>", $text);
                    unset($arBr[1]);
                    unset($arBr[2]);
                    foreach($arBr as $br)
                    {
                        $arTextBr = explode($br, $text);
                        if(!empty($arTextBr) && count($arTextBr)>1)
                        {
                            foreach($arTextBr as $textBr)
                            {   
                                $textBr = strip_tags($textBr);
                                $textBr = str_replace($deleteSymb, "", $textBr); 
                                foreach($arProperties as $code=>$val)
                                {
                                    //if(preg_match("/".$val."/", $textBr))
                                    if($this->CheckFindProps($code, $val, $textBr))
                                    {
                                        $this->parseCatalogPropHL($code, $val, $textBr);
                                    }    
                                }
                                
                            }      
                        }
                    }
                    
                }
            } elseif(strpos($vFind,' dl')!==false) {

                foreach(pq($el)->find($vFind) as $prop)
                {
                    $text = pq($prop)->html();
                    $text = str_replace('</dd>','<br>',$text);
                    $text = str_replace('</dt>',' ',$text);
                    $text = strip_tags($text ,'<br>');
                    $arTextBr = explode('<br>', $text);                                                          

                    if(!empty($arTextBr) && count($arTextBr)>1) {
                        foreach($arTextBr as $textBr)
                        {
                            foreach($arProperties as $code=>$val)
                            {           

                                if($this->CheckFindProps($code, $val, $textBr)) {
                                    $this->parseCatalogPropHL($code, $val, $textBr);
                                }
                            }
                        }
                    }
                }

            } else {
                foreach(pq($el)->find($vFind) as $prop)
                {
                    $text = pq($prop)->html();
                                                     
                    $text = str_replace($deleteSymb, "", $text);
                    
                    $text1 = $text;
                    foreach($arProperties as $code=>$val)
                    {                                         
                        $text1 = $text;
                        $arProp = $this->arProperties[$code];   
                        $text1 = strip_tags($text);
                            
                        if($this->CheckFindProps($code, $val, $text1))
                        {   
                            $this->parseCatalogPropHL($code, $val, $text1);
                        }
                    }
                }
            }
        }
    }

    protected function parseCatalogPropFileHL($code, $el)
    {                              
        if($this->checkUniqHL() && (!$this->isUpdate || !$this->isUpdate[$code])) return false;
        $ar = $this->GetArraySrcAttr($this->settings["properties"]["detail"][$code]["value"]);       
        $file = $ar["path"];
        $attr = $ar["attr"];              
        $isElement = $this->checkUniqHL();   
        foreach(pq($el)->find($file) as $f)
        {
            if(!empty($attr)) 
            {
                $src = pq($f)->attr($attr);   
            }
            elseif(empty($attr)) 
            {
                $src = pq($f)->html();
                $src = strip_tags(pq($f)->html());
            }
            $descr = strip_tags(pq($f)->html());     
            $src1 = $this->parseCaralogFilterSrc($src);            
            $src = $this->getCatalogLink($src1);     
            $fileId = $this->MakeFileArray($src);
            if($fileId['type']=='unknown'){        
                $src = $this->getCatalogLink($src1, false);     
                $fileId = $this->MakeFileArray($src);
            } 
            unset($src);                                       
            unset($src1);              
            if($this->arProperties[$code]['MULTIPLE']=='Y'){                
                $this->arFields["FIELDS"][$code][] = $fileId; 
            } else {
                $this->arFields["FIELDS"][$code] = $fileId;
            }
            unset($fileId);   
        }  
           
        if($isElement)
        {
            $arFiles = array(
                $code => $this->arFields["FIELDS"][$code]
            );                   
            unset($this->arFields["FIELDS"][$code]);    
            $entity_class = $this->entityClass;
            $entity_class::update($isElement, $arFiles);
            unset($entity_class);                                 
        }
    }  
}