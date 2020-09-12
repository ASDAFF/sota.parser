<?php
class SotbitXlsCatalogParser extends SotbitHLCatalogParser {
    
    public $catalog_detail_settings = array();
    
    public function __construct()
    {
        parent::__construct();
        if($this->typeN=='xls_catalo' || $this->typeN=='xml_catalo'){
            $this->setDetailSettings();
        }
    }

    protected function CheckFieldsXlsCatalog($settings)
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
        
        if(is_array($settings["price_updown"]))
        {
            foreach($settings["price_updown"] as $i=>$val)
            {
                if($settings["price_updown"][$i])
                {
                    if($settings["price_terms"][$i] && !self::isFloat($settings["price_terms_value"][$i]))
                    {
                        $this->errors[] = GetMessage("parser_error_price_terms_value");
                    }
                    if($settings["price_terms"][$i] && !self::isFloat($settings["price_terms_value_to"][$i]))
                    {
                        $this->errors[] = GetMessage("parser_error_price_terms_value");
                    }
                    if($settings["price_updown"][$i] && !self::isFloat($settings["price_value"][$i]))
                    {
                        $this->errors[] = GetMessage("parser_error_price_value");
                    }
                }
            }
        }

        $properties = CIBlockProperty::GetList(Array("sort"=>"asc", "name"=>"asc"), Array("ACTIVE"=>"Y", "IBLOCK_ID"=>$this->iblock_id));
        while ($prop_fields = $properties->GetNext())
        {
            $this->arProperties[$prop_fields["CODE"]] = $prop_fields;
        }
        
        if($this->iblockOffer)
        {
            $properties = CIBlockProperty::GetList(Array("sort"=>"asc", "name"=>"asc"), Array("ACTIVE"=>"Y", "IBLOCK_ID"=>$this->iblockOffer));
            while ($prop_fields = $properties->GetNext())
            {
                $this->arPropertiesOffer[$prop_fields["CODE"]] = $prop_fields;
            }
        }

        $this->arSelectorProduct = $this->getSelectorProduct();
        $this->arFindProduct = $this->getFindProduct();
        $this->arSelectorProperties = $this->getSelectorProperties();
        $this->arSelectorPropertiesOfferDetail = $this->getSelectorPropertiesOfferDetail();
        $this->arFindProperties = $this->getFindProperties();
        $this->arFindPropertiesOfferDetail = $this->getFindPropertiesOfferDetail();
        $this->arDubleFindProperties = $this->getFindDubleProperties();
        $this->arDubleFindPropertiesOffer = $this->getFindDublePropertiesOffer();
        
        $this->arSelectorPropertiesPreview = $this->getSelectorPropertiesPreview();
        $this->arFindPropertiesPreview = $this->getFindPropertiesPreview();
        $this->arDubleFindPropertiesPreview = $this->getFindDublePropertiesPreview();
        //printr($this->ArDubleFindProperties);
    }
    
    protected function getFindPropertiesOfferDetail()
    {
        if(isset($this->catalog_detail_settings['settings']["offer"]["find_prop"]) && !empty($this->catalog_detail_settings['settings']["offer"]["find_prop"]))
        {
            foreach($this->catalog_detail_settings['settings']["offer"]["find_prop"] as $i=>$prop)
            {
                $prop = trim($prop);
                if(!empty($prop))
                {
                    $arProps[$i] = $prop;
                }
            }
            if(!isset($arProps)) return false;
            return $arProps;
        }
        return false;
    }
    
    protected function getSelectorPropertiesOfferDetail()
    {
        if(isset($this->catalog_detail_settings['settings']["offer"]["selector_prop"]) && !empty($this->catalog_detail_settings['settings']["offer"]["selector_prop"]))
        {
            $arProps = false;
            foreach($this->catalog_detail_settings['settings']["offer"]["selector_prop"] as $i=>$prop)
            {
                $prop = trim($prop);
                if(!empty($prop))
                {
                    $arProps[$i] = $prop;
                }
            }
            if(!$arProps) return false;
            return $arProps;
        }
        elseif(isset($this->catalog_detail_settings['settings']["offer"]["selector_prop_more"]) && !empty($this->catalog_detail_settings['settings']["offer"]["selector_prop_more"]))
        {
            $arProps = false;
            foreach($this->catalog_detail_settings['settings']["offer"]["selector_prop_more"] as $i=>$prop)
            {
                $prop = trim($prop);
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
    
    protected function setDetailSettings(){
        if(isset($this->settings['catalog']['parser_id_detail'])){
            $p = new ShsParserContent();
            $arFilter = array(
                'ID'=>intval($this->settings['catalog']['parser_id_detail']),
            );
            $this->catalog_detail_settings = $p->GetList(array(),$arFilter)->fetch();
            $set = $p->GetSettingsById($this->settings['catalog']['parser_id_detail'])->fetch();
            $this->catalog_detail_settings['settings'] = unserialize(base64_decode($set["SETTINGS"]));
            
            $this->detail_delete_element = $this->catalog_detail_settings['DETAIL_DELETE_ELEMENT'];
            $this->detail_dom = htmlspecialchars_decode($this->catalog_detail_settings['SELECTOR']);
            $this->detail_delete_attribute = $this->catalog_detail_settings['DETAIL_DELETE_ATTRIBUTE'];
            $this->settings["catalog"]["detail_name"] = $this->catalog_detail_settings['settings']["catalog"]["detail_name"];
            //$this->settings["catalog"]["selector_prop"] = $this->catalog_detail_settings['settings']["catalog"]["selector_prop"];
            $this->settings["catalog"]["catalog_delete_selector_find_props_symb"] = $this->catalog_detail_settings['settings']["catalog"]["catalog_delete_selector_find_props_symb"];
            $this->settings["catalog"]["selector_find_props"] = $this->catalog_detail_settings['settings']["catalog"]["selector_find_props"];
            $this->settings["catalog"]["catalog_delete_find_symb"] = $this->catalog_detail_settings['settings']["catalog"]["catalog_delete_find_symb"];
        }
    }

    protected function parseXlsCatalogCatalog()
    {
        set_time_limit(0);
        parent::ClearAjaxFiles();
        parent::DeleteLog();
        parent::checkActionBegin();
        $this->arUrl = array();
        if(isset($this->settings["catalog"]["url_dop"]) && !empty($this->settings["catalog"]["url_dop"]))$this->arUrl = explode("\r\n", $this->settings["catalog"]["url_dop"]);
        
        $this->arUrl = array_merge(array($this->rss), $this->arUrl);
        $this->arUrlSave = $this->arUrl;
        
        if(!$this->PageFromFileXls()) return false;
        parent::CalculateStep();
        if($this->settings["catalog"]["mode"]!="debug" && !$this->agent) $this->arUrlSave = array($this->rss);
        else $this->arUrlSave = $this->arUrl;
        
        foreach($this->arUrlSave as $rss):
            $rss = trim($rss);
            if(empty($rss)) continue;
            $this->rss = $rss;
            parent::convetCyrillic($this->rss);
            parent::connectCatalogPage($this->rss);
            
            if(!$this->agent && $this->settings["catalog"]["mode"]!="debug" && isset($this->errors) && count($this->errors)>0)
            {
                parent::SaveLog();
                unlink($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/shs.parser/include/count_parser_catalog_step".$this->id.".txt");
                unlink($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/shs.parser/include/count_parser_copy_page".$this->id.".txt");
                return false;
            }
            $n = $this->currentPage;
            $this->parseCatalogXlsCatalogProducts();
            
            if($this->settings["catalog"]["mode"]!="debug" && !$this->agent)
            {
                $this->stepStart = true;
                parent::SavePrevPage($this->rss);
            }
            
            parent::SaveCurrentPage($this->pagenavigation);
            if($this->stepStart)
            {
                if(file_exists($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/shs.parser/include/count_parser_catalog_step".$this->id.".txt"))
                    unlink($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/shs.parser/include/count_parser_catalog_step".$this->id.".txt");
                parent::DeleteCopyPage();
            }
            if( (!$this->CheckOnePageNavigation() && $this->agent) ||
                (!$this->CheckOnePageNavigation() && !$this->agent &&
                $this->settings["catalog"]["mode"]=="debug"))
                    parent::parseCatalogPages();
                    
            if($this->settings['smart_log']['enabled']=='Y'){
                $this->settings['smart_log']['result_id'] = file_get_contents($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/shs.parser/include/result_id".$this->id.".txt");
                $this->settings['smart_log']['result_id'] = \Bitrix\Shs\ParserResultTable::updateEndTime($this->settings['smart_log']['result_id']);
            }
            
//            if($this->CheckOnePageNavigation() && $this->stepStart)
//            {
//                if(parent::IsEndSectionUrl()) parent::ClearBufferStop();
//                else parent::ClearBufferStep();
//                return false;
//            }
        endforeach;
        
        parent::checkActionAgent($this->agent);
        
        if($this->agent || $this->settings["catalog"]["mode"]=="debug"){
            foreach(GetModuleEvents("shs.parser", "EndPars", true) as $arEvent)
                ExecuteModuleEventEx($arEvent, array($this->id));
        }
    }
    
    protected function parseCatalogXlsCatalogProducts()
    {
        $count = 0;
        
        $this->activeCurrentPage++;
        $this->SetCatalogElementsResult($this->activeCurrentPage);
        
        $i = 0;
        $ci = 0;
        $debug_item = self::DEFAULT_DEBUG_ITEM;

        if(!$this->ValidateUrl($this->rss)) {
            $this->rss = $_SERVER["DOCUMENT_ROOT"].'/'.$this->rss;
        } else{
            $auth = isset($this->settings["xls_catalo"]["auth"]["active"])?true:false;
            $gets = new FileGetHtml();
            $ext = pathinfo($this->rss);
            $this->rss = $gets->file_get_image($this->rss,$this->proxy,$auth,false,$_SERVER["DOCUMENT_ROOT"].'/upload/parser_id'.$this->id.'.'.$ext['extension']);
        }
        
        $catalogMaxLevel = (int)$this->settings['max_level_catalog'];
        $arCatalogStyles = array();
        
        foreach($this->settings['catalog_level'] as $keyList => $rows){
            foreach($rows as $keyRow => $lvl){
                if(empty($lvl))
                    continue;
                $propetiesIndex = array();
                $styleHash='';
                foreach($this->settings['catalog_level_p']['property']['lvl_'.$lvl] as $index => $val){
                    if(!empty($val)){
                        $propetiesIndex[$val] = $index;
                        if($val=='CATALOG_NAME'){
                            $styleCell = $this->getOneCellStyle($keyList, $keyRow, $index);
                            $styleHash = $this->getStyleHash($styleCell);
                        }
                    }
                }
                if(empty($styleHash)){
                    $this->errors[] = GetMessage("parser_catalog_name_not_found", array('#LVL#'=>$lvl));
                    $this->clearFields();
                }
                
                $arCatalogStyles[$styleHash] = array(
                    'list' => $keyList,
                    'row' => $keyRow,
                    'prop_index' => $propetiesIndex,
                    'level' => $lvl,
                );
            }
        }
        
        $objReader = PHPExcel_IOFactory::createReaderForFile($this->rss);
        $p=array();
        $selfobj = new SotbitXlsParserStatic($p);
        
        $spreadsheetInfo = $objReader->listWorksheetInfo($this->rss);
        
        $listCount = count($spreadsheetInfo);
        
        $arCatalogs = array();
        $arWorksheets = array();
        
        $parent_id=1;
        $arParentsCatalogId = array();
        for($key=0;$key<$listCount;$key++){
            if(!isset($this->settings['list']['load'][$key])){
                continue;
            }
            $startRow=$this->settings['list'][$key]['first_item']?:0;
            $startRow++;
            $totalRows = $spreadsheetInfo[$key]['totalRows'];
            $chunkSize = ($this->settings["catalog"]["step"]!='')?(int)$this->settings["catalog"]["step"]:30;
            $chunkFilter = new ChunkXmlFilter();
            $arLines = array();
            while($startRow<=$totalRows){
       
                $objReader = PHPExcel_IOFactory::createReaderForFile($this->rss);
        
                $chunkFilter->setRows($startRow,$chunkSize);
                $objReader->setReadFilter($chunkFilter);
        
                if($this->settings['catalog']['load_style']!=='Y') {
                    $objReader->setReadDataOnly(true);
                }
                $this->efile = $objReader->load($this->rss);
                $this->efile->setActiveSheetIndex($key);
                $worksheet = $this->efile->getActiveSheet();
                
                
                $this->saveCurrentList($key);
                $columns_count = PHPExcel_Cell::columnIndexFromString($worksheet->getHighestDataColumn());
                $rows_count = $worksheet->getHighestDataRow();

                $cntLines = $emptyLines = 0;
                for ($row = $startRow; ($row <= $rows_count); $row++) {
                    $arLine = array();
                    $bEmpty = true;
                    for ($column = 0; $column < $columns_count; $column++) {
                        $val = $worksheet->getCellByColumnAndRow($column, $row);
                        $valText = $selfobj->getCalculatedValue($val);
                        if(strlen(trim($valText)) > 0) $bEmpty = false;
                    
                        $curLine = array('VALUE' => $valText);
                        if($this->settings['catalog']['load_style']=="Y") {
                            $curLine['STYLE'] = SotbitXlsParserUtils::getCellStyle($val);
                        }
                        $arLine[] = $curLine;
                    }
                    
                    $isCategoryRow = false;
                    foreach($arCatalogStyles as $key4=>$styleLvl){
                        $index = $styleLvl['prop_index']['CATALOG_NAME'];
                        $hash = $this->getStyleHash($arLine[$index]['STYLE']);
                        if(isset($arCatalogStyles[$hash])){
                            $name = $arLine[$index]['VALUE'];
                            $level = $arCatalogStyles[$hash]['level'];
                            $isCategoryRow = true;
                            break;
                        }
                    }
                    
                    if($this->settings['catalog']['load_style']=="Y" && $this->settings['create_catalog'] && $isCategoryRow){
                        $arParentsCatalogId[$level] = $parent_id;
                        $catalog = array();
                        $catalog['id'] = $parent_id;
                        $catalog['name'] = $name;
                        $catalog['parent_id'] = $arParentsCatalogId[$level-1]?:0;
                        $arCatalogs[$parent_id] = $catalog;
                        $parent_id++;
                    } else{
                        $arLine['parent_id']=$parent_id-1;
                        $arLines[$row] = $arLine;
                        $cntLines++;
                    }
                    if($bEmpty){
                        $emptyLines++;
                    }
                }
                //get images
                if($this->settings['image_file']['enable']=='Y'){
                    $drawCollection = $worksheet->getDrawingCollection();
                    if($drawCollection)
                    {
                        foreach($drawCollection as $drawItem)
                        {
                            if ($drawItem instanceof PHPExcel_Worksheet_MemoryDrawing) {
                                $image = $drawItem->getImageResource();
                                //$renderingFunction = $image->getRenderingFunction();
                                $cell = $worksheet->getCell($drawItem->getCoordinates());
                                $colIndex = PHPExcel_Cell::columnIndexFromString($cell->getColumn());
                                $rowIndex = $cell->getRow();
                                $arLines[$rowIndex][$colIndex-1]['resource'] = $image;
                                $arLines[$rowIndex][$colIndex-1]['to'] = $drawItem->getIndexedFilename();
                                $arLines[$rowIndex][$colIndex-1]['function'] = $drawItem->getRenderingFunction();
                            }
                        }
                    }
                }
            
                $arCells = explode(':', $worksheet->getSelectedCells());
                $heghestRow = intval(preg_replace('/\D+/', '', end($arCells)));
                if(is_callable(array($worksheet, 'getRealHighestRow'))) $heghestRow = intval($worksheet->getRealHighestRow());
                elseif($worksheet->getHighestDataRow() > $heghestRow) $heghestRow = intval($worksheet->getHighestDataRow());
                
                $this->efile->__destruct();
            
                unset($objReader);
                unset($objPHPExcel);
                $startRow+=$chunkSize;
            }
            $arWorksheets[] = array(
                'title' => self::getCorrectCalculatedValue($worksheet->GetTitle()),
                'show_more' => ($row < $rows_count - 1),
                'lines_count' => $heghestRow,
                'lines' => $arLines
                );
        }
        
        $arRes=array();
        foreach($arWorksheets as $key=>$list){
            $arRes = array_merge($arRes,$list['lines']);
        }
        
        if ($this->settings["create_catalog"] == "Y" && $this->settings["catalog"]["load_style"] == "Y")
        {
            if ($this->parseCatalogSectionXls($arCatalogs) === false)
            {
                parent::SaveLog();
                return false;
            }
        }
        
        $count=count($arRes);
        
        if($this->settings["catalog"]["mode"]!="debug" && !$this->agent)
        {
            if($count > $this->settings["catalog"]["step"] && ($this->settings["catalog"]["mode"]!="debug" && !$this->agent))
                $countStep = $this->settings["catalog"]["step"];
            else{
                $this->stepStart = true;
                if($this->CheckOnePageNavigation() || $this->CheckAlonePageNavigation($this->currentPage))
                    $this->pagenavigation[$this->rss] = $this->rss;
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
            $this->errors[] = GetMessage("parser_error_empty")."[".$this->rss."]";
            $this->clearFields();
        }
        
        foreach($arRes as $el){
            if(file_exists($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/shs.parser/include/stop_parser_".$this->id.".txt")) {
                throw new RuntimeException("stop");
            }
            
            $ci++;
            
            if($i==$debug_item && $this->settings["catalog"]["mode"]=="debug")
                break;
            
            $this->parseProductElementXlsCatalog($el);
            
            $i++;
            file_put_contents($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/shs.parser/include/count_parser".$this->id.".txt", $countStep."|".$i);
            
            if($i >= $countStep) {
                $i = 0;
            }
        }
        unlink($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/shs.parser/include/count_parser".$this->id.".txt");
    }
    
    protected function parseProductElementXlsCatalog(&$el)
    {
        $this->countItem++;
        if(!$this->parserCatalogPreviewXls($el))
        {
            parent::SaveCatalogError();
            parent::clearFields();
            return false;
        }

        if (($this->settings["create_catalog"] != "Y" || $this->settings["catalog"]["load_style"] != "Y") && $this->settings["catalog"]["section_by_name"] != "Y")
            $this->parseCatalogSection();
            
        parent::parseCatalogDate();
        $this->parseCatalogAllFieldsCsv();
        
        $this->parseDetailPage($el);

        $db_events = GetModuleEvents("shs.parser", "parserBeforeAddElementXLS", true);
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
        
        if(!$error && !$error_isad)
        {
            parent::AddElementCatalog();
            foreach(GetModuleEvents("shs.parser", "parserAfterAddElementXLS", true) as $arEvent)
                ExecuteModuleEventEx($arEvent, array(&$this, &$el));
        }

        if($this->isCatalog && $this->elementID)
        {
            if($this->isOfferCatalog && !$this->boolOffer)
            {
                parent::AddElementOfferCatalog();
                $this->elementID = $this->elementOfferID;
                $this->elementUpdate = $this->elementOfferUpdate;
            }
            if($this->boolOffer)
            {
                parent::addProductPriceOffers();
            }else{
                
                parent::AddProductCatalog();
                parent::AddMeasureCatalog();
                parent::AddPriceCatalog();
                $this->addAvailable();
            }
            
            $this->parseAdditionalStoresXLS($el);
            $this->parseStore();
            $this->updateQuantity();
            
        }
        
        if($this->settings['smart_log']['enabled']=='Y') {
            $this->settings['smart_log']['result_id'] = file_get_contents($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/shs.parser/include/result_id".$this->id.".txt");
            SmartLogs::saveNewValues($this->elementID, $this->settings["smart_log"], $this->arFields, isset($this->arPrice['PRICE'])?$this->arPrice['PRICE']:null, $this->arProduct);
        }
        
        parent::SetCatalogElementsResult();
        parent::clearFilesTemp();
        parent::clearFields();
    }
    
    protected function parseDetailPage(&$el)
    {
        if($this->checkUniqCsv() && !$this->isUpdate) return false;
        if($this->settings['catalog']['detail_link'] !== '' || !intval($this->settings['catalog']['detail_link'])){
            $index = intval($this->settings['catalog']['detail_link']);
            if(!isset($el[$index]) && !empty($el[$index]['VALUE'])){
                return false;
            }
            $this->arFields["LINK"] = trim($el[$index]['VALUE']);
        
            foreach(GetModuleEvents("shs.parser", "parserCatalogDetailBefore", true) as $arEvent)
                ExecuteModuleEventEx($arEvent, array($this->id, &$el, &$this->arFields));
            $el_detail = $this->getDetailPage();
            
            $this->parseCatalogDetailName($el_detail);
            $this->parseXlsCatalogProperties($el_detail);
            $this->parseCatalogDetailPictureDetail($el_detail);
            $this->parseCatalogDetailMorePhotoDetail($el_detail);
            if($this->isCatalog)$this->parseCatalogDetailPriceDetail($el_detail);
            if($this->isCatalog)$this->parseCatalogDetailAdittionalPriceDetail($el_detail);
            if($this->isCatalog)$this->ParseCatalogDetailAvailableDetail($el_detail);
            $this->parseCatalogDetailDescriptionDetail($el_detail);
            $this->parseDetailAdditionalStores($el_detail);
            $this->parserOffersDetail($el_detail);
            
            foreach(GetModuleEvents("shs.parser", "parserCatalogDetail", true) as $arEvent)
                ExecuteModuleEventEx($arEvent, array($this->id, &$el, &$this->arFields, $el_detail));
            unset($el_detail);
        }
    }
    
    protected function parserOffersDetail($el, $check="")
    {
        if($this->catalog_detail_settings['settings']["offer"]["preview_or_detail"] == $check)
        {
            $this->boolOffer = (isset($this->arOfferAll) && !empty($this->arOfferAll))?true:false;
            
            if($this->catalog_detail_settings['settings']["offer"]["load"]=="table"
               && $this->isOfferParsing
               && isset($this->catalog_detail_settings['settings']["offer"]["selector"])
               && $this->catalog_detail_settings['settings']["offer"]["selector"]
               && isset($this->catalog_detail_settings['settings']["offer"]["selector_item"])
               && $this->catalog_detail_settings['settings']["offer"]["selector_item"])
            {
               $offerItem = $this->catalog_detail_settings['settings']["offer"]["selector"]." ".$this->catalog_detail_settings['settings']["offer"]["selector_item"];
               $this->parserHeadTableOfferDetail($el);
               foreach(pq($el)->find($offerItem) as $offer)
               {
                    $this->boolOffer = true;
                    if($this->parseOfferNameDetail($offer))
                    {
                        $this->parseOfferPriceDetail($offer);
                        $this->parseOfferAdditionalPriceDetail($offer);
                        $this->parseOfferQuantityDetail($offer);
                        $this->parseOfferPropsDetail($offer);
                        if(!$this->parseOfferGetUniqDetail())
                        {
                            $this->deleteOfferFields();;
                            continue 1;
                        }

                    }else
                        continue 1;

                    $this->arOfferAll["FIELDS"][] = $this->arOffer;
                    $this->arOfferAll["PRICE"][] = $this->arPriceOffer;
                    $this->arOfferAll["ADDIT_PRICE"][] = $this->arAdditionalPriceOffer;
                    $this->arOfferAll["QUANTITY"][] = $this->arOfferQuantity;
                    $this->deleteOfferFields();
               }
            }elseif($this->catalog_detail_settings['settings']["offer"]["load"]=="one"
                    && $this->isOfferParsing
                    && isset($this->catalog_detail_settings['settings']["offer"]["one"]["selector"])
                    && $this->catalog_detail_settings['settings']["offer"]["one"]["selector"])
            {
                $offerItem = trim($this->catalog_detail_settings['settings']["offer"]["one"]["selector"]);
                $arr = $this->GetArraySrcAttr($offerItem);
                $path = $arr["path"];
                $attr = $arr["attr"];
                
                foreach(pq($el)->find($path) as $offer)
                {
                    $this->boolOffer = true;
                    if($this->parseOfferNameDetail($offer))
                    {
                        $this->parseOfferDetailImgDetail($offer);                                   //////////////////////////////
                        $this->parseOfferPriceDetail($offer);
                        $this->parseOfferAdditionalPriceDetail($offer);
                        $this->parseOfferQuantityDetail($offer);
                        $this->parseOfferPropsDetail($offer);
                        
                        if(!$this->parseOfferGetUniqDetail())
                        {
                            $this->deleteOfferFields();;
                            continue 1;
                        }

                    }else
                        continue 1;
                        
                    $this->arOfferAll["FIELDS"][] = $this->arOffer;
                    $this->arOfferAll["PRICE"][] = $this->arPriceOffer;
                    $this->arOfferAll["ADDIT_PRICE"][] = $this->arAdditionalPriceOffer;
                    $this->arOfferAll["QUANTITY"][] = $this->arOfferQuantity;
                    $this->deleteOfferFields();
                }
            }
            elseif($this->catalog_detail_settings['settings']["offer"]["load"]=="more"
                   && $this->isOfferParsing
                   && isset($this->catalog_detail_settings['settings']["offer"]["selector_prop_more"])
                   && (count($this->catalog_detail_settings['settings']["offer"]["selector_prop_more"]) > 0))
                {
                if (isset($this->catalog_detail_settings['settings']["offer"]["catalog_offer_selector_table"]) && ($this->catalog_detail_settings['settings']["offer"]["catalog_offer_selector_table"] != '')) {
                    foreach (pq($el)->find($this->catalog_detail_settings['settings']["offer"]["catalog_offer_selector_table"]) as $val) {
                        $allOfferProps = $this->parseOffersSelectorPropMoreDetail($val);
                        if (($allOfferProps !== false) && is_array($allOfferProps)) {
                            $nm = 0;
                            $arRes = array();
                            $count = count($allOfferProps);

                            foreach ($allOfferProps as $code => $props) {
                                $nm++;

                                foreach ($props as $id => $valProps) {
                                    $val = $valProps["value"];
                                    $arTemp[] = $valProps;

                                    $this->funcX($val, $nm, $allOfferProps, $arRes, $arTemp, $count);
                                }
                                break 1;
                            }
                            $this->parseAllOffersMorePropsDetail($arRes);
                        }
                    }
                } else {
                    $allOfferProps = $this->parseOffersSelectorPropMoreDetail($el);
                    if (($allOfferProps !== false) && is_array($allOfferProps)) {
                        $nm = 0;
                        $arRes = array();
                        $count = count($allOfferProps);

                        foreach ($allOfferProps as $code => $props) {
                            $nm++;

                            foreach ($props as $id => $valProps) {
                                $val = $valProps["value"];
                                $arTemp[] = $valProps;
                                $this->funcX($val, $nm, $allOfferProps, $arRes, $arTemp, $count);
                            }
                            break 1;
                        }
                        $this->parseAllOffersMorePropsDetail($arRes);
                    }
                }
            }
        
            foreach(GetModuleEvents("shs.parser", "parserAfterParsingOffersDetail", true) as $arEvent)
                ExecuteModuleEventEx($arEvent, array(&$this, &$el));//mark
        }
    }
    
    protected function parseAllOffersMorePropsDetail($offers)
    {
        if(empty($offers) || !is_array($offers)) return false;
        foreach($offers as $id => $offer)
        {
            $this->boolOffer = true;
            if($this->parseOfferNameDetail($offer))
            {
                $this->parseOfferPriceDetail($offer);
                $this->parseOfferQuantityDetail($offer);
                $this->parseOfferPropsDetail($offer);
                if(!$this->parseOfferGetUniqDetail())
                {
                    $this->deleteOfferFields();;
                    continue 1;
                }
            }else
                continue 1;

            $this->arOfferAll["FIELDS"][] = $this->arOffer;
            $this->arOfferAll["PRICE"][] = $this->arPriceOffer;
            $this->arOfferAll["QUANTITY"][] = $this->arOfferQuantity;
            
            $this->deleteOfferFields();
        }
    }
    
    protected function parseOffersSelectorPropMoreDetail($el)
    {
        $deleteSymb = $this->getOfferDeleteSelectorDetail();
        $deleteSymbRegular = $this->getOfferDeleteSelectorRegularDetail();
        
        $offerPropsAll = $this->arSelectorPropertiesOfferDetail;
        if(empty($offerPropsAll)) return false;
        $arrPropsAll = array();
        
        foreach($offerPropsAll as $code => $selector)
        {
            if(empty($selector)) continue 1;
            $arProp = $this->arPropertiesOffer[$code];
            
            $arr = $this->GetArraySrcAttr($selector);
            $path = $arr["path"];
            $attr = $arr["attr"];

            $item = 0;
            if(!empty($path))
            {
                foreach(pq($el)->find($path) as $valProps)
                {
                    if(!empty($path) && empty($attr))
                    {
                        $arrPropsAll[$code][$item]["value"] = trim(pq($valProps)->html());
                    }
                    elseif(!empty($path) && !empty($attr))
                    {
                        $arrPropsAll[$code][$item]["value"] = pq($valProps)->find($path)->attr($attr);
                    }
                    
                    if($arProp["USER_TYPE"]!="HTML")
                    {
                        $arrPropsAll[$code][$item]["value"] = strip_tags($arrPropsAll[$code][$item]["value"]);
                    }
                    
                    $arrPropsAll[$code][$item]["value"] = str_replace($deleteSymb, "", $arrPropsAll[$code][$item]["value"]);
                    $arrPropsAll[$code][$item]["value"] = preg_replace($deleteSymbRegular, "", $arrPropsAll[$code][$item]["value"]);
                    
                    $arrPropsAll[$code][$item]["code"] = $code;
                    $item ++;
                }
            }
            else
            {
                $arrPropsAll[$code][$item]["value"] = pq($el)->attr($attr);
                
                if($arProp["USER_TYPE"]!="HTML")
                {
                    $arrPropsAll[$code][$item]["value"] = strip_tags($arrPropsAll[$code][$item]["value"]);
                }
                
                $arrPropsAll[$code][$item]["value"] = str_replace($deleteSymb, "", $arrPropsAll[$code][$item]["value"]);
                $arrPropsAll[$code][$item]["value"] = preg_replace($deleteSymbRegular, "", $arrPropsAll[$code][$item]["value"]);
                $arrPropsAll[$code][$item]["code"] = $code;
            }

        }

        if(!isset($arrPropsAll) || empty($arrPropsAll)) return false;
        
        return $arrPropsAll;
    }

    protected function parseOfferGetUniqDetail()
    {
        if(isset($this->catalog_detail_settings['settings']["offer"]["add_name"]) && !empty($this->catalog_detail_settings['settings']["offer"]["add_name"]))
        {
            $strV = "";
            $bool = true;
            foreach($this->catalog_detail_settings['settings']["offer"]["add_name"] as $v)
            {
                if(isset($this->arOffer["PROPERTY_VALUES"][$v]))
                {
                    
                    if(is_array($this->arOffer["PROPERTY_VALUES"][$v]))
                    {
                        
                        foreach($this->arOffer["PROPERTY_VALUES"][$v] as $val)
                        {
                            if($bool) $strV .= $val;
                            else $strV .= " / ".$val;
                            $bool = false;
                        }
                    } else {
                        $val = $this->arOffer["PROPERTY_VALUES"][$v];
                        if($bool) $strV .= $val;
                        else $strV .= " / ".$val;
                        $bool = false;
                    }
                }
            }
            
            if(!isset($this->arOffer["NAME"]))
                $this->arOffer["NAME"] = "";
            if($strV)
                $strV =  " (".$strV.")";
            
            if($this->typeN == "xls_catalo")
                $this->arOffer["NAME"] .= $strV;

            if(!$this->arOffer["NAME"])
            {
                $this->errors[] = GetMessage("parser_error_name_notfound_offer");
                return false;
            }
        }
        if($this->arOffer["NAME"])
        {
            $this->arOffer["XML_ID"] = "offer#".md5($this->arFields["LINK"].$this->arOffer["NAME"]);
        }
        return true;
    }
    
    protected function getOfferDeleteSelectorRegularDetail()
    {
        $deleteSymb = array();
        if($this->catalog_detail_settings['settings']["offer"]["catalog_delete_selector_props_symb"])
        {
            $deleteSymb = explode("||", $this->catalog_detail_settings['settings']["offer"]["catalog_delete_selector_props_symb"]);
            
            foreach($deleteSymb as $i=>&$symb)
            {
                $symb = trim($symb);
                $symb = htmlspecialcharsBack($symb);
                if(empty($symb))
                {
                    unset($deleteSymb[$i]);
                    continue;
                }
                
                if(!preg_match("/^\//", $symb) || !preg_match("/\/$/", $symb))
                {
                    unset($deleteSymb[$i]);
                    continue;
                }
            }
        }

        return $deleteSymb;
    }
    
    protected function getOfferDeleteFindRegularDetail()
    {
        $deleteSymb = array();
        if($this->catalog_detail_settings['settings']["offer"]["catalog_delete_selector_find_props_symb"])
        {
            $deleteSymb = explode("||", $this->catalog_detail_settings['settings']["offer"]["catalog_delete_selector_find_props_symb"]);

            foreach($deleteSymb as $i=>&$symb)
            {
                $symb = trim($symb);
                $symb = htmlspecialcharsBack($symb);
                if(empty($symb))
                {
                    unset($deleteSymb[$i]);
                    continue;
                }
                
                if(!preg_match("/^\//", $symb) || !preg_match("/\/$/", $symb))
                {
                    unset($deleteSymb[$i]);
                    continue;
                }
            }
        }
        
        return $deleteSymb;
    }
    
    protected function parseOfferPropsDetail($offer, $nameOffer=false)
    {
        if($this->checkUniq() && !$this->isUpdate) return false;
        if(isset($this->catalog_detail_settings['settings']["offer"]["selector_prop"]) && !empty($this->catalog_detail_settings['settings']["offer"]["selector_prop"]))
        {
            $deleteSymb = $this->getOfferDeleteSelectorDetail();
            $deleteSymbRegular = $this->getOfferDeleteSelectorRegularDetail();
            
            $arProperties = $this->arSelectorPropertiesOfferDetail;
            
            foreach($arProperties as $code=>$val)
            {
                $arProp = $this->arPropertiesOffer[$code];
                if($arProp["PROPERTY_TYPE"]=="F")
                {
                    $this->parseCatalogPropFile($code, $offer);
                }else{
                    $arr = $this->GetArraySrcAttr($this->catalog_detail_settings['settings']["offer"]["selector_prop"][$code]);
                    if (empty($arr["path"]) && !empty($arr["attr"]))
                    {
                        $text = trim(pq($offer)->attr($arr["attr"]));
                    }
                    else{
                        if(empty($arr["attr"])){
                            $text = trim(strip_tags(pq($offer)->find($arr["path"])->html()));
                        }
                        elseif (!empty($arr["attr"]))
                        {
                            $text = trim(pq($offer)->find($arr["path"])->attr($arr["attr"]));
                        }
                    }
                    
                    if($arProp["USER_TYPE"]!="HTML")
                        $text = strip_tags($text);
                    $text = str_replace($deleteSymb, "", $text);
                    $text = preg_replace($deleteSymbRegular, "", $text);
                    $this->parseCatalogPropOffer($code, $val, $text);
                }
            }
        }
        if(isset($this->catalog_detail_settings['settings']["offer"]["find_prop"]) && !empty($this->catalog_detail_settings['settings']["offer"]["find_prop"]))
        {
            $deleteSymb = $this->getOfferDeleteFindDetail();
            $deleteSymbRegular = $this->getOfferDeleteFindRegularDetail();
            
            $arProperties = $this->arFindPropertiesOfferDetail;
            foreach($arProperties as $code=>$val)
            {
                $arProp = $this->arPropertiesOffer[$code];
                if(isset($this->tableHeaderNumber[$val]))
                {
                    $n = $this->tableHeaderNumber[$val];
                    $text = pq($offer)->find($this->catalog_detail_settings['settings']["offer"]["selector_item_td"].":eq(".$n.")");
                    $text = str_replace($deleteSymb, "", $text);
                    $text = preg_replace($deleteSymbRegular, "", $text);
                    
                    $text1 = $text;
                    if($arProp["USER_TYPE"]!="HTML")
                        $text1 = strip_tags($text);
                    if($this->CheckFindPropsOffer($code, $val, $text1))
                    {
                        $this->parseCatalogPropOffer($code, $val, $text1);
                    }
                }
            }
        }
        
        if(isset($this->catalog_detail_settings['settings']["offer"]["one"]["selector"]) && !empty($this->catalog_detail_settings['settings']["offer"]["one"]["selector"]) && isset($this->catalog_detail_settings['settings']["offer"]["add_name"]) && !empty($this->catalog_detail_settings['settings']["offer"]["add_name"]))
        {
            $arProperties = $this->catalog_detail_settings['settings']["offer"]["add_name"];
            $deleteSymb = $this->getOfferDeleteSelectorDetail();
            $deleteSymbRegular = $this->getOfferDeleteSelectorRegularDetail();
            
            $arr = $this->GetArraySrcAttr(trim($this->catalog_detail_settings['settings']["offer"]["one"]["selector"]));
            $path = $arr["path"];
            $attr = $arr["attr"];
            
            foreach($arProperties as $code)
            {
                if($nameOffer === false)
                {
                    if(!empty($path))
                    {
                        if(empty($attr)) $text = pq($offer)->html();
                        elseif(!empty($attr)) $text = pq($offer)->attr($attr);
                    }
                }
                elseif($nameOffer !== false)
                {
                    $text = $nameOffer;
                }
                $text = str_replace($deleteSymb, "", $text);
                $text = preg_replace($deleteSymbRegular, "", $text);
                $text1 = $text;
                $text1 = strip_tags($text);
                $this->parseCatalogPropOffer($code, "", $text1);
                break 1;
            }
        }
        
        if(isset($this->catalog_detail_settings['settings']["offer"]["selector_prop_more"]) && !empty($this->catalog_detail_settings['settings']["offer"]["selector_prop_more"]))
        {
            if(!empty($offer) && is_array($offer))
            {
                $deleteSymb = $this->getOfferDeleteSelectorDetail();
                $deleteSymbRegular = $this->getOfferDeleteSelectorRegularDetail();
                
                $arProperties = $this->arSelectorPropertiesOfferDetail;
                
                foreach($offer as $props)
                {
                    if(array_key_exists($props["code"], $arProperties))
                    {
                        $arProp = $this->arPropertiesOffer[$props["code"]];
                        $text = $props["value"];
                        
                        if($arProp["USER_TYPE"]!="HTML")
                        {
                            $text = strip_tags($text);
                        }
                        
                        $text = str_replace($deleteSymb, "", $text);
                        $text = preg_replace($deleteSymbRegular, "", $text);
                        $this->parseCatalogPropOffer($props["code"], $arProperties[$props["code"]], $text);
                    }
                }
            }
        }
    }
    
    protected function parseOfferDetailImgDetail($offer)
    {
         if(isset($this->catalog_detail_settings['settings']["offer"]["one"]["selector"]) && !empty($this->catalog_detail_settings['settings']["offer"]["one"]["selector"]) && isset($this->catalog_detail_settings['settings']["offer"]["add_name"]) && !empty($this->catalog_detail_settings['settings']["offer"]["add_name"]) && isset($this->catalog_detail_settings['settings']["offer"]["one"]["detail_img"]) && !empty($this->catalog_detail_settings['settings']["offer"]["one"]["detail_img"]))
         {
             $arr = $this->GetArraySrcAttr(trim($this->catalog_detail_settings['settings']["offer"]["one"]["detail_img"]));
             if(empty($arr["path"]) && !empty($arr["attr"]))
             {
                 $src = pq($offer)->attr($arr["attr"]);
             }
             elseif(!empty($arr["path"]) && empty($arr["attr"]))
             {
                 $src = pq($offer)->find($arr["path"])->html();
             }
             elseif(!empty($arr["path"]) && !empty($arr["attr"]))
             {
                $src = pq($offer)->find($arr["path"])->attr($arr["attr"]);
             }
             $src = strip_tags($src);
             
             $src = $this->parseCaralogFilterSrc($src);
             $src = $this->getCatalogLink($src);

             $this->arOffer["DETAIL_PICTURE"] = $this->MakeFileArray($src);
             $this->arrFilesTemp[] = $this->arOffer["DETAIL_PICTURE"]["tmp_name"];
         }
    }
    
    
    protected function GetQuantityDetail()
    {
        if(isset($this->arFields["AVAILABLE_DETAIL"]) && is_numeric($this->arFields["AVAILABLE_DETAIL"]))
        {
            return $this->arFields["AVAILABLE_DETAIL"];
        }
        elseif(isset($this->arFields["AVAILABLE_PREVIEW"]) && is_numeric($this->arFields["AVAILABLE_PREVIEW"]))
        {
            return $this->arFields["AVAILABLE_PREVIEW"];
        }
        else
        {
            if(is_numeric($this->catalog_detail_settings['settings']["catalog"]["count_default"]))
            {
                return intval($this->catalog_detail_settings['settings']["catalog"]["count_default"]);
            }
            else return false;
        }
        return false;
    }
    
    protected function parseOfferQuantityDetail($offer)
    {
        if(parent::checkUniq() && (!$this->isUpdate || !$this->isUpdate["count"])) return false;
        if(isset($this->catalog_detail_settings['settings']["offer"]["selector_quantity"]) && $this->catalog_detail_settings['settings']["offer"]["selector_quantity"])
        {
            $arr = parent::GetArraySrcAttr($this->catalog_detail_settings['settings']["offer"]["selector_quantity"]);
            if (empty($arr["path"]) && !empty($arr["attr"]))
            {
                $quantity = trim(pq($offer)->attr($arr["attr"]));
            }
            else{
                if(empty($arr["attr"])){
                    $quantity = trim(strip_tags(pq($offer)->find($arr["path"])->html()));
                }
                elseif (!empty($arr["attr"]))
                {
                    $quantity = trim(pq($offer)->find($arr["path"])->attr($arr["attr"]));
                }
            }
            $quantity = trim(strip_tags($quantity));
            $value = $this->findAvailabilityValueDetail($quantity);
            if($value && isset($value['count'])){
                $quantity = $value['count'];
            } elseif(is_numeric($value)) {
                $quantity = $value;
            }
            $quantity = preg_replace('/[^0-9.]/', "", $quantity);

            if(is_numeric($quantity))
            {
                $quantity = intval($quantity);
                if($quantity == 0)
                {
                    $this->arOfferQuantity["QUANTITY"] = 0;
                }else
                {
                    $this->arOfferQuantity["QUANTITY"] = $quantity;
                }
            }
            
        }elseif(isset($this->catalog_detail_settings['settings']["offer"]["find_quantity"]) && $this->catalog_detail_settings['settings']["offer"]["find_quantity"])
        {
            if(isset($this->catalog_detail_settings['settings']["offer"]["selector_item_td"]) && $this->catalog_detail_settings['settings']["offer"]["selector_item_td"])
            {
                $name = $this->catalog_detail_settings['settings']["offer"]["find_quantity"];
                if(isset($this->tableHeaderNumber[$name]))
                {
                    $n = $this->tableHeaderNumber[$name];
                    $quantity = pq($offer)->find($this->catalog_detail_settings['settings']["offer"]["selector_item_td"].":eq(".$n.")");
                    $quantity = trim(strip_tags($price));
                    $value = $this->findAvailabilityValueDetail($quantity);
                    if($value && isset($value['count'])){
                        $quantity = $value['count'];
                    } elseif(is_numeric($value)) {
                        $quantity = $value;
                    }
                    $quantity = preg_replace('/[^0-9.]/', "", $quantity);
                    if(is_numeric($quantity))
                    {
                        $quantity = intval($quantity);
                        if($quantity == 0)
                        {
                            $this->arOfferQuantity["QUANTITY"] = 0;
                        }else
                        {
                            $this->arOfferQuantity["QUANTITY"] = $quantity;
                        }
                    }
                }
            }
        }elseif(isset($this->catalog_detail_settings['settings']["offer"]["one"]["quantity"]) && $this->catalog_detail_settings['settings']["offer"]["one"]["quantity"])
        {
            $attr = $this->catalog_detail_settings['settings']["offer"]["one"]["quantity"];
            $quantity = pq($offer)->attr($attr);
            $quantity = trim(strip_tags($quantity));
            $value = $this->findAvailabilityValueDetail($quantity);
            if($value && isset($value['count'])){
                $quantity = $value['count'];
            } elseif(is_numeric($value)) {
                $quantity = $value;
            }
            $quantity = preg_replace('/[^0-9.]/', "", $quantity);
            
            if(is_numeric($quantity))
            {
                $quantity = intval($quantity);
                if($quantity == 0)
                {
                    if($this->arOfferQuantity["QUANTITY"]==0)
                    $this->arOfferQuantity["QUANTITY"] = 0;
                }else
                {
                    $this->arOfferQuantity["QUANTITY"] = $quantity;
                }
            }
        }
        
        if(!isset($this->arOfferQuantity["QUANTITY"]))
        {
            $quantity = $this->GetQuantityDetail();
            if(is_numeric($quantity))
            {
                $this->arOfferQuantity["QUANTITY"] = $quantity;
            }else
            {
                return false;
            }
        }
        return true;
    }
    
    protected function parseOfferAdditionalPriceDetail($offer)
    {
        if($this->checkUniq() && (!$this->isUpdate || !$this->isUpdate["price"])) return false;
        $this->arAdditionalPriceOffer = array();
        if(isset($this->catalog_detail_settings['settings']["offer"]["selector_additional_prices"]) && !empty($this->catalog_detail_settings['settings']["offer"]["selector_additional_prices"]))
        {
            foreach($this->catalog_detail_settings['settings']["offer"]["selector_additional_prices"] as $id_price => $price1){
                if($price1['value']==='')
                    continue;
                    
                $arr_price = array();
                
                $arr = $this->GetArraySrcAttr($price1['value']);
                if (empty($arr["path"]) && !empty($arr["attr"]))
                {
                    $price = trim(pq($offer)->attr($arr["attr"]));
                } else {
                    if(empty($arr["attr"])){
                        $price = trim(strip_tags(pq($offer)->find($arr["path"])->html()));
                    } elseif (!empty($arr["attr"]))
                    {
                        $price = trim(pq($offer)->find($arr["path"])->attr($arr["attr"]));
                    }
                }
                $price = $this->parseCatalogPriceFormat($price);
                //$price = $this->parseCatalogPriceOkrug($price);
                $arr_price["PRICE"] = $price;
                $arr_price["CATALOG_GROUP_ID"] = $id_price;
                $arr_price['CURRENCY'] = $this->catalog_detail_settings['settings']['adittional_currency'][$id_price];
                $this->arAdditionalPriceOffer[$id_price] = $arr_price;
            }
        }elseif(isset($this->catalog_detail_settings['settings']["offer"]["find_additional_price"]) && !empty($this->catalog_detail_settings['settings']["offer"]["find_additional_price"]))
        {
            if(isset($this->catalog_detail_settings['settings']["offer"]["selector_item_td"]) && $this->catalog_detail_settings['settings']["offer"]["selector_item_td"])
            {
                foreach($this->catalog_detail_settings['settings']["offer"]["find_additional_price"] as $id_price => $price1){
                    if($price1['value']==='')
                        continue;
                    
                    $arr_price = array();
                    $name = $price1['value'];
                    if(isset($this->tableHeaderNumber[$name]))
                    {
                        $n = $this->tableHeaderNumber[$name];
                        $price = pq($offer)->find($this->catalog_detail_settings['settings']["offer"]["selector_item_td"].":eq(".$n.")");
                        $price = trim(strip_tags($price));
                        $price = $this->parseCatalogPriceFormat($price);
                        //$price = $this->parseCatalogPriceOkrug($price);
                        $arr_price["PRICE"] = $price;
                        $arr_price["CATALOG_GROUP_ID"] = $id_price;
                        $arr_price['CURRENCY'] = $this->catalog_detail_settings['settings']['adittional_currency'][$id_price];
                        $this->arAdditionalPriceOffer[$id_price] = $arr_price;
                    }
                }
            }
        } elseif(isset($this->catalog_detail_settings['settings']["offer"]["one"]["price"]) && $this->catalog_detail_settings['settings']["offer"]["one"]["price"])
        {
            $attr = $this->catalog_detail_settings['settings']["offer"]["one"]["price"];
            $price = pq($offer)->attr($attr);
            $price = trim(strip_tags($price));
            $this->arPriceOffer["PRICE"] = $price;
            $this->arPriceOffer["CATALOG_GROUP_ID"] = $this->catalog_detail_settings['settings']["catalog"]["price_type"];
            $this->arPriceOffer["CURRENCY"] = $this->catalog_detail_settings['settings']["catalog"]["currency"];
        }
        
        if(isset($this->arAdditionalPrice) && !empty($this->arAdditionalPrice) && (!isset($this->arAdditionalPriceOffer) || empty($this->arAdditionalPriceOffer)))
        {
             $this->arAdditionalPriceOffer = $this->arAdditionalPrice;
        }
        
        if(!isset($this->arAdditionalPriceOffer))
        {
            $this->errors[] = GetMessage("parser_error_name_notfound_offer");
            return false;
        }
        return true;
    }
    
    protected function parseOfferPriceDetail($offer)
    {
        if($this->checkUniq() && (!$this->isUpdate || !$this->isUpdate["price"])) return false;
        if(isset($this->catalog_detail_settings['settings']["offer"]["selector_price"]) && $this->catalog_detail_settings['settings']["offer"]["selector_price"])
        {
            $arr = $this->GetArraySrcAttr($this->catalog_detail_settings['settings']["offer"]["selector_price"]);
            if (empty($arr["path"]) && !empty($arr["attr"]))
            {
                $price = trim(pq($offer)->attr($arr["attr"]));
            } else {
                if(empty($arr["attr"])){
                    $price = trim(strip_tags(pq($offer)->find($arr["path"])->html()));
                } elseif (!empty($arr["attr"]))
                {
                    $price = trim(pq($offer)->find($arr["path"])->attr($arr["attr"]));
                }
            }
            $price = $this->parseCatalogPriceFormat($price);
            //$price = $this->parseCatalogPriceOkrug($price);
            $this->arPriceOffer["PRICE"] = $price;
            $this->arPriceOffer["CATALOG_GROUP_ID"] = $this->catalog_detail_settings['settings']["catalog"]["price_type"];
            $this->arPriceOffer["CURRENCY"] = $this->catalog_detail_settings['settings']["catalog"]["currency"];
            
        }elseif(isset($this->catalog_detail_settings['settings']["offer"]["find_price"]) && $this->catalog_detail_settings['settings']["offer"]["find_price"])
        {
            if(isset($this->catalog_detail_settings['settings']["offer"]["selector_item_td"]) && $this->catalog_detail_settings['settings']["offer"]["selector_item_td"])
            {
                $name = $this->catalog_detail_settings['settings']["offer"]["find_price"];
                if(isset($this->tableHeaderNumber[$name]))
                {
                    $n = $this->tableHeaderNumber[$name];
                    $price = pq($offer)->find($this->catalog_detail_settings['settings']["offer"]["selector_item_td"].":eq(".$n.")");
                    $price = trim(strip_tags($price));
                    $price = $this->parseCatalogPriceFormat($price);
                    //$price = $this->parseCatalogPriceOkrug($price);
                    $this->arPriceOffer["PRICE"] = $price;
                    $this->arPriceOffer["CATALOG_GROUP_ID"] = $this->catalog_detail_settings['settings']["catalog"]["price_type"];
                    $this->arPriceOffer["CURRENCY"] = $this->catalog_detail_settings['settings']["catalog"]["currency"];
                }
            }
        }elseif(isset($this->catalog_detail_settings['settings']["offer"]["one"]["price"]) && $this->catalog_detail_settings['settings']["offer"]["one"]["price"])
        {
            $attr = $this->catalog_detail_settings['settings']["offer"]["one"]["price"];
            $price = pq($offer)->attr($attr);
            $price = trim(strip_tags($price));
            $this->arPriceOffer["PRICE"] = $price;
            $this->arPriceOffer["CATALOG_GROUP_ID"] = $this->catalog_detail_settings['settings']["catalog"]["price_type"];
            $this->arPriceOffer["CURRENCY"] = $this->catalog_detail_settings['settings']["catalog"]["currency"];
        }
        
        if(isset($this->arPrice["PRICE"]) && !empty($this->arPrice["PRICE"]) && (!isset($this->arPriceOffer["PRICE"]) || empty($this->arPriceOffer["PRICE"])))
        {
             $this->arPriceOffer["PRICE"] = $this->arPrice["PRICE"];
             $this->arPriceOffer["CATALOG_GROUP_ID"] = $this->catalog_detail_settings['settings']["catalog"]["price_type"];
             $this->arPriceOffer["CURRENCY"] = $this->catalog_detail_settings['settings']["catalog"]["currency"];
        }
        
        if(!isset($this->arPriceOffer["PRICE"]))
        {
            $this->errors[] = GetMessage("parser_error_name_notfound_offer");
            return false;
        }
        return true;
    }
    
    protected function parseOfferNameDetail($offer)
    {
        if(isset($this->catalog_detail_settings['settings']["offer"]["selector_name"]) && $this->catalog_detail_settings['settings']["offer"]["selector_name"])
        {
            $arr = $this->GetArraySrcAttr($this->catalog_detail_settings['settings']["offer"]["selector_name"]);
            if (empty($arr["path"]) && !empty($arr["attr"]))
            {
                $name = trim(pq($offer)->attr($arr["attr"]));
            }
            else{
                if(empty($arr["attr"])){
                    $name = trim(strip_tags(pq($offer)->find($arr["path"])->html()));
                }
                elseif(!empty($arr["attr"]))
                {
                    $name = trim(pq($offer)->find($arr["path"])->attr($arr["attr"]));
                }
            }
            
            $deleteSymb = $this->getOfferDeleteSelectorDetail();
            $name = str_replace($deleteSymb, "", $name);
            $this->arOffer["NAME"] = htmlspecialchars_decode($name);
            if(isset($this->settings["loc"]["f_name"]) && $this->settings["loc"]["f_name"]=="Y")
            {
                $this->arOffer["NAME"] = $this->locText($this->arOffer["NAME"]);
            }
        }elseif(isset($this->catalog_detail_settings['settings']["offer"]["find_name"]) && $this->catalog_detail_settings['settings']["offer"]["find_name"])
        {
            if(isset($this->catalog_detail_settings['settings']["offer"]["selector_item_td"]) && $this->catalog_detail_settings['settings']["offer"]["selector_item_td"])
            {
                $deleteSymb = $this->getOfferDeleteFindDetail();
                $name = $this->catalog_detail_settings['settings']["offer"]["find_name"];
                
                if(isset($this->tableHeaderNumber[$name]))
                {
                    $n = $this->tableHeaderNumber[$name];
                    $name = pq($offer)->find($this->catalog_detail_settings['settings']["offer"]["selector_item_td"].":eq(".$n.")");
                    $this->arOffer["NAME"] = htmlspecialchars_decode(trim(strip_tags($name)));
                    $this->arOffer["NAME"] = str_replace($deleteSymb, "", $this->arOffer["NAME"]);
                    if(isset($this->settings["loc"]["f_name"]) && $this->settings["loc"]["f_name"]=="Y")
                    {
                        $this->arOffer["NAME"] = $this->locText($this->arOffer["NAME"]);
                    }
                    
                }
            }
        }elseif(
          isset($this->catalog_detail_settings['settings']["offer"]["selector_prop_more"])
          && $this->catalog_detail_settings['settings']["offer"]["selector_prop_more"]
          && (!isset($this->catalog_detail_settings['settings']["offer"]["add_name"]) || empty($this->catalog_detail_settings['settings']["offer"]["add_name"])))
        {
            if(!empty($offer) && is_array($offer))
            {
                 if(isset($this->catalog_detail_settings['settings']["offer"]["add_offer_name_more"]) && !empty($this->catalog_detail_settings['settings']["offer"]["add_offer_name_more"]))
                {
                    $arName = explode("|", trim(str_replace(" ", "", $this->catalog_detail_settings['settings']["offer"]["add_offer_name_more"])));
                }else
                {
                    if(isset($this->catalog_detail_settings['settings']["offer"]["selector_prop_more"]) && count($this->catalog_detail_settings['settings']["offer"]["selector_prop_more"] > 0))
                    {
                        foreach($this->catalog_detail_settings['settings']["offer"]["selector_prop_more"] as $code => $value)
                        {
                            if(empty($code)) continue 1;
                            $arName[] = $code;
                        }
                    }
                    else return false;
                }
                $this->arOffer["NAME"] = "";
                foreach($arName as $code)
                {
                    if(empty($code)) continue 1;
                    
                    foreach($offer as $val)
                    {
                        if($val["code"] == $code)
                        {
                            if($this->arOffer["NAME"] != "")
                            {
                                $this->arOffer["NAME"] = $this->arOffer["NAME"]." / ".$val["value"];
                            }
                            else
                            {
                                $this->arOffer["NAME"] = $val["value"];
                            }
                        }
                    }
                }
                $this->arOffer["NAME"] = trim(str_replace("  ", " ", $this->arOffer["NAME"]));
                $this->arOffer["NAME"] = htmlspecialchars_decode(trim(strip_tags($this->arOffer["NAME"])));
                $this->arOffer["NAME"] = $this->arFields["NAME"]." (".$this->arOffer["NAME"].")";
                
                if(isset($this->settings["loc"]["f_name"]) && $this->settings["loc"]["f_name"]=="Y")
                {
                    $this->arOffer["NAME"] = $this->locText($this->arOffer["NAME"]);
                }
            }
        }
        
        if(!isset($this->arOffer["NAME"]) && (!isset($this->catalog_detail_settings['settings']["offer"]["add_name"]) || empty($this->catalog_detail_settings['settings']["offer"]["add_name"])))
        {
            $this->errors[] = GetMessage("parser_error_name_notfound_offer");
            return false;
        }elseif(!isset($this->arOffer["NAME"]))
            $this->arOffer["NAME"] = $this->arFields["NAME"];
        return true;
    }

    protected function getOfferDeleteFindDetail()
    {
        $deleteSymb = array();
        
        if($this->catalog_detail_settings['settings']["offer"]["catalog_delete_selector_find_props_symb"])
        {
            $deleteSymb = explode("||", $this->catalog_detail_settings['settings']["offer"]["catalog_delete_selector_find_props_symb"]);

            foreach($deleteSymb as $i=>&$symb)
            {
                $symb = trim($symb);
                $symb = htmlspecialcharsBack($symb);
                if(empty($symb))
                {
                    unset($deleteSymb[$i]);
                    continue;
                }
                
                if(preg_match("/^\//", $symb) && preg_match("/\/$/", $symb))
                {
                    unset($deleteSymb[$i]);
                    continue;
                }
            }

        }
        
        return $deleteSymb;
    }
    
    protected function getOfferDeleteSelectorDetail()
    {
        $deleteSymb = array();
        if($this->catalog_detail_settings['settings']["offer"]["catalog_delete_selector_props_symb"])
        {
            $deleteSymb = explode("||", $this->catalog_detail_settings['settings']["offer"]["catalog_delete_selector_props_symb"]);

            foreach($deleteSymb as $i=>&$symb)
            {
                $symb = trim($symb);
                $symb = htmlspecialcharsBack($symb);
                if(empty($symb))
                {
                    unset($deleteSymb[$i]);
                    continue;
                }
                if(preg_match("/^\//", $symb) && preg_match("/\/$/", $symb))
                {
                    unset($deleteSymb[$i]);
                    continue;
                }
            }
            
        }

        return $deleteSymb;
    }
    
    protected function parserHeadTableOfferDetail($el)
    {
        if(isset($this->catalog_detail_settings['settings']["offer"]["selector"])
           && $this->catalog_detail_settings['settings']["offer"]["selector"]
           && isset($this->catalog_detail_settings['settings']["offer"]["selector_head"])
           && $this->catalog_detail_settings['settings']["offer"]["selector_head"]
           && isset($this->catalog_detail_settings['settings']["offer"]["selector_head_th"])
           && $this->catalog_detail_settings['settings']["offer"]["selector_head_th"])
        {
            $offerHead = $this->catalog_detail_settings['settings']["offer"]["selector"]." ".$this->catalog_detail_settings['settings']["offer"]["selector_head"]." ".$this->catalog_detail_settings['settings']["offer"]["selector_head_th"];
            $i = 0;
            foreach(pq($el)->find($offerHead) as $head)
            {
                $textHead = trim(strip_tags(pq($head)->html()));
                
                $this->tableHeaderNumber[$textHead] = $i;
                $i++;
            }
        }
    }
    
    protected function parseDetailAdditionalStores(&$el){
        if(isset($this->catalog_detail_settings['settings']['addit_stores']) && !empty($this->catalog_detail_settings['settings']['addit_stores'])){
            if($this->checkUniqCsv() && (!$this->isUpdate || !$this->isUpdate["count"])) return false;
            foreach($this->catalog_detail_settings['settings']['addit_stores'] as $id => $store){
                $selector_count = trim($store['value']);
                if($selector_count!=''){
                    
                    $count = htmlspecialchars_decode($selector_count);
                    $count = $this->GetArraySrcAttr($count);
                    $path = $count["path"];
                    $attr = $count["attr"];
                
                    if (empty($attr))
                        $count = strip_tags(pq($el)->find($path)->html());
                    elseif(!empty($attr))
                        $count = trim(pq($el)->find($path)->attr($attr));
                        
                    $value = $this->findAvailabilityValueDetail($count);
                    if($value && isset($value['count'])){
                        $count = $value['count'];
                    } elseif(is_numeric($value)) {
                        $count = $value;
                    }
                    $count = preg_replace('/[^0-9.]/', "", $count);
                } else {
                    $this->errors[] = $this->arFields["NAME"].'['.$store['name'].']'.GetMessage("parser_error_count_notfound_csv");
                    continue;
                }
                $this->additionalStore[$id] = $count;
            }
        }
    }

    protected function parseCatalogDetailDescriptionDetail(&$el)
    {
        if($this->checkUniqCsv() && (!$this->isUpdate || (!$this->isUpdate["detail_descr"] && (!$this->isUpdate["preview_descr"] && !$this->settings["catalog"]["text_preview_from_detail"]!="Y")))) return false;
        if($this->catalog_detail_settings['settings']["catalog"]["detail_text_selector"])
        {
            $detail = $this->catalog_detail_settings['settings']["catalog"]["detail_text_selector"];
            $arDetail = explode(",", $detail);
            $detail_text = "";
            if($arDetail && !empty($arDetail))
            {
                foreach($arDetail as $detail)
                {
                    $detail = trim($detail);
                    if(!$detail) continue 1;

                    foreach(pq($el)->find($detail." img") as $img)
                    {
                        $src = pq($img)->attr("src");
                        $src = $this->parseCaralogFilterSrc($src);
                        $src = $this->getCatalogLink($src);
                        $this->parseCatalogSaveImgServer($img, $src);
                        unset($src);
                    }

                    if($this->catalog_detail_settings['BOOL_DETAIL_DELETE_TAG']=="Y") $detail_text .= strip_tags(pq($el)->find($detail)->html(), htmlspecialcharsBack($this->catalog_detail_settings['DETAIL_DELETE_TAG']));
                    else $detail_text .= pq($el)->find($detail)->html();
                }
            }
            $detail_text = trim($detail_text);
            if(isset($this->settings["loc"]["f_detail_text"]) && $this->settings["loc"]["f_detail_text"]=="Y")
            {
                $detail_text = $this->locText($detail_text, $this->detail_text_type=="html"?"html":"plain");
            }
            $this->arFields["DETAIL_TEXT"] = $detail_text;
            $this->arFields["DETAIL_TEXT_TYPE"] = $this->detail_text_type;
            if($this->settings["catalog"]["text_preview_from_detail"]=="Y")
            {
                $this->arFields["PREVIEW_TEXT"] = $this->arFields["DETAIL_TEXT"];
                $this->arFields["PREVIEW_TEXT_TYPE"] = $this->arFields["DETAIL_TEXT_TYPE"];
            }
            unset($detail_text);
            unset($detail);
            unset($arDetail);
        }
    }
    
    public function findAvailabilityValueDetail($value){
        if(isset($this->catalog_detail_settings['settings']['availability']['list']) && !empty($this->catalog_detail_settings['settings']['availability']['list'])){
            foreach($this->catalog_detail_settings['settings']['availability']['list'] as $i => $av){
                if($av['text']==$value){
                    return $av;
                }
            }
            return $value;
        } else {
            return $value;
        }
    }
    
    protected function ParseCatalogDetailAvailableDetail(&$el)
    {
        if(!empty($this->catalog_detail_settings['settings']["catalog"]["detail_count"]))
        {
            if(parent::checkUniqCsv() && (!$this->isUpdate || !$this->isUpdate["count"])) return false;
            $arr = parent::GetArraySrcAttr($this->catalog_detail_settings['settings']["catalog"]["detail_count"]);
            $path = $arr["path"];
            $attr = $arr["attr"];
            if(empty($attr))
            {
                $available = pq($el)->find($path)->html();
            }
            elseif(!empty($attr))
            {
                $available = pq($el)->find($path)->attr($attr);
            }
            elseif(empty($path) && !empty($attr))
            {
                $available = pq($el)->attr($attr);
            }
            
            $available = trim(strip_tags($available));
            $value = $this->findAvailabilityValueDetail($available);
            if($value && isset($value['count'])){
                $available = $value['count'];
            } elseif(is_numeric($value)) {
                $available = $value;
            }
            $available = preg_replace('/[^0-9.]/', "", $available);
            if(is_numeric($available))
            {
                $available = intval($available);
                if($available == 0){
                    if($this->arFields["AVAILABLE_DETAIL"]==0)
                        $this->arFields["AVAILABLE_DETAIL"] = 0;
                } else {
                    $this->arFields["AVAILABLE_DETAIL"] = $available;
                }
            }
            unset($available);
            unset($path);
            unset($attr);
            unset($value);
        } elseif(is_numeric($this->catalog_detail_settings['settings']["catalog"]["count_default"])){
            $this->arFields["AVAILABLE_DETAIL"] = intval($this->catalog_detail_settings['settings']["catalog"]["count_default"]);
        }
    }

    protected function parseCatalogDetailAdittionalPriceDetail(&$el)
    {
        if($this->catalog_detail_settings['settings']["prices_detail"] && !empty($this->catalog_detail_settings['settings']["prices_detail"]))
        {
            if($this->checkUniqCsv() && (!$this->isUpdate || !$this->isUpdate["price"])) return false;
            foreach($this->catalog_detail_settings['settings']["prices_detail"] as $id_price => $price_arr){
                $addit_price = array();
                
                $price = htmlspecialchars_decode($price_arr['value']);
                $price = $this->GetArraySrcAttr($price);
                $path = $price["path"];
                $attr = $price["attr"];
                
                if (empty($attr))
                    $price = strip_tags(pq($el)->find($path)->html());
                elseif(!empty($attr))
                    $price = trim(pq($el)->find($path)->attr($attr));
                $price = $this->parseCatalogPriceFormat($price);
                //$price = $this->parseCatalogPriceOkrug($price);

                $addit_price["PRICE"] = $price;
                $addit_price["PRICE"] = trim($addit_price["PRICE"]);
                if(!$addit_price["PRICE"])
                {
                    $this->errors[] = $this->arFields["NAME"]."[".$this->arFields["LINK"]."] ".$price_arr['name'].GetMessage("parser_error_price_notfound");
                    unset($addit_price["PRICE"]);
                    unset($path);
                    unset($attr);
                    unset($price);
                    return false;
                }
                $addit_price["CATALOG_GROUP_ID"] = $id_price;
                $addit_price['CURRENCY'] = $this->catalog_detail_settings['settings']['adittional_currency'][$id_price];
                $this->arAdditionalPrice[$id_price] = $addit_price;
                unset($path);
                unset($attr);
                unset($price);
                unset($addit_price);
            }
        }
    }

    protected function parseCatalogDetailPriceDetail(&$el)
    {
        if($this->catalog_detail_settings['settings']["catalog"]["detail_price"])
        {
            if($this->checkUniqCsv() && (!$this->isUpdate || !$this->isUpdate["price"])) return false;
            $price = htmlspecialchars_decode($this->catalog_detail_settings['settings']["catalog"]["detail_price"]);
            $price = strip_tags(pq($el)->find($price)->html());
            $price = $this->parseCatalogPriceFormat($price);
            //$price = $this->parseCatalogPriceOkrug($price);
            $price = trim($price);
            if($price!==''){
                $this->arPrice["PRICE"] = $price;
                if(!$this->arPrice["PRICE"])
                {
                    $this->errors[] = $this->arFields["NAME"]."[".$this->arFields["LINK"]."]".GetMessage("parser_error_price_notfound");
                    if($this->arPrice["PRICE"]=='')
                        unset($this->arPrice["PRICE"]);
                    return false;
                }
                $this->arPrice["CATALOG_GROUP_ID"] = $this->catalog_detail_settings['settings']["catalog"]["price_type"];
                $this->arPrice["CURRENCY"] = $this->catalog_detail_settings['settings']["catalog"]["currency"];
            } else {
                $this->errors[] = $this->arFields["NAME"]."[".$this->arFields["LINK"]."]".GetMessage("parser_error_price_notfound");
            }
            unset($price);
        }
    }

    protected function parseCatalogDetailMorePhotoDetail(&$el)
    {
        if($this->catalog_detail_settings['settings']["catalog"]["more_image_props"])
        {
            if($this->checkUniqCsv() && (!$this->isUpdate || !$this->isUpdate["more_img"])) return false;
            $code = $this->catalog_detail_settings['settings']["catalog"]["more_image_props"];
            $ar = $this->GetArraySrcAttr($this->catalog_detail_settings['settings']["catalog"]["more_image"]);
            $image = $ar["path"];
            $attr = $ar["attr"];
            $n = 0;

            $isElement = $this->checkUniqCsv();
            foreach(pq($el)->find($image) as $img)
            {
                if(!empty($attr))
                {
                    $src = pq($img)->attr($attr);
                    $src = $this->parseSelectorStyle($attr, $src);
                }
                elseif(empty($attr))
                {
                    $src = strip_tags(pq($img)->html());
                }
                $src = $this->parseCaralogFilterSrc($src);
                $src = $this->getCatalogLink($src);
                foreach(GetModuleEvents("shs.parser", "ParserDetailMorePhoto", true) as $arEvent) //27.10.2015
                    ExecuteModuleEventEx($arEvent, array(&$this, &$src));
                if(isset($this->arPhoto[$src])) continue 1;
                $this->arPhoto[$src] = 1;
                $this->arFields["PROPERTY_VALUES"][$code]["n".$n]["VALUE"] = $this->MakeFileArray($src);
                $this->arrFilesTemp[] = $this->arFields["PROPERTY_VALUES"][$code]["n".$n]["VALUE"]["tmp_name"];
                $this->arFields["PROPERTY_VALUES"][$code]["n".$n]["DESCRIPTION"] = "";
                $n++;
            }
            if($isElement)
            {
                $arImages = $this->arFields["PROPERTY_VALUES"][$code];
                unset($this->arFields["PROPERTY_VALUES"][$code]);
                $obElement = new CIBlockElement;
                $rsProperties = $obElement->GetProperty($this->iblock_id, $isElement, "sort", "asc",  Array("CODE"=>$code));
                while($arProperty = $rsProperties->Fetch())
                {
                    $arImages[$arProperty["PROPERTY_VALUE_ID"]] = array(
                        "tmp_name" => "",
                        "del" => "Y",
                    );
                }
                CIBlockElement::SetPropertyValueCode($isElement, $code, $arImages);
                unset($arProperty);
                unset($rsProperties);
                unset($obElement);
                unset($arImages);
            }
            unset($n);
            unset($ar);
            unset($code);
            unset($image);
            unset($attr);
        }
    }

    public function parseCatalogDetailPictureDetail(&$el)
    {
        if($this->checkUniqCsv() && (!$this->isUpdate || (!$this->isUpdate["detail_img"] && (!$this->isUpdate["preview_img"] && !$this->settings["catalog"]["img_preview_from_detail"]!="Y")))) return false;
        if($this->catalog_detail_settings['settings']["catalog"]["detail_picture"])
        {
            $arSelPic = explode(",", $this->catalog_detail_settings['settings']["catalog"]["detail_picture"]);

            foreach($arSelPic as $sel)
            {
                $sel = trim($sel);
                if(empty($sel)) continue;
                $ar = $this->GetArraySrcAttr($sel);
                $img = $ar["path"];
                $attr = $ar["attr"];
                if(!empty($attr))
                {
                    $src = pq($el)->find($img)->attr($attr);
                    $src = $this->parseSelectorStyle($attr, $src);
                }
                elseif(empty($attr))
                {
                    $src = pq($el)->find($img)->text();
                }
                
                $src = $this->parseCaralogFilterSrc($src);
                $src = $this->getCatalogLink($src);
                foreach(GetModuleEvents("shs.parser", "ParserDetailPicture", true) as $arEvent) //27.10.2015
                    ExecuteModuleEventEx($arEvent, array(&$this, &$src));

                if(!self::CheckImage($src)) continue;
                $this->arPhoto[$src] = 1;

                $this->arFields["DETAIL_PICTURE"] = $this->MakeFileArray($src);
                
                $this->arrFilesTemp[] = $this->arFields["DETAIL_PICTURE"]["tmp_name"];

                if($this->settings["catalog"]["img_preview_from_detail"]=="Y")
                {
                    $this->arFields["PREVIEW_PICTURE"] = $this->arFields["DETAIL_PICTURE"];
                }
                unset($src);
                unset($ar);
                unset($img);
                unset($attr);
            }
            unset($arSelPic);
        }
    }
    
    protected function parseCatalogDetailName(&$el)
    {
        if($this->detail_delete_element)$this->deleteCatalogElement($this->detail_delete_element, $this->detail_dom, $this->detailHtml[$this->detail_dom]);
        if($this->detail_delete_attribute)$this->deleteCatalogAttribute($this->detail_delete_attribute, $this->detail_dom, $this->detailHtml[$this->detail_dom]);
        
        if(!isset($this->catalog_detail_settings['settings']["catalog"]["detail_name"]) || !$this->catalog_detail_settings['settings']["catalog"]["detail_name"]) return false;
        $name = htmlspecialchars_decode($this->catalog_detail_settings['settings']["catalog"]["detail_name"]);
        $this->arFields["NAME"] = htmlspecialchars_decode(trim(strip_tags(pq($el)->find($name)->html())));
        if($this->arFields["NAME"])
        {
            $this->arFields["NAME"] = $this->actionFieldProps("SOTBIT_PARSER_NAME_E", $this->arFields["NAME"]);
            if(isset($this->settings["loc"]["f_name"]) && $this->settings["loc"]["f_name"]=="Y")
            {
                $this->arFields["NAME"] = $this->locText($this->arFields["NAME"]);
            }
            if($this->code_element=="Y")
            {
                $this->arFields["CODE"] = $this->getCodeElement($this->arFields["NAME"]);
            }
        }
        unset($name);
        if(!$this->arFields["NAME"])
        {
            $this->errors[] = GetMessage("parser_error_name_notfound");
            return false;
        }
    }
    
    protected function parseXlsCatalogProperties(&$el)
    {
        if($this->checkUniqCsv() && !$this->isUpdate) return false;
        $this->parseCatalogDetailSelectorProperties($el);
        $this->parseCatalogFindProperties($el);
        $this->AllDoProps();
        if($this->isCatalog)$this->parseCatalogFindDetailProduct($el);
        if($this->isCatalog)$this->parseCatalogSelectorDetailProduct($el);
    }

    protected function parseCatalogDetailSelectorProperties(&$el)
    {
        $arProperties = $this->getSelectorPropertiesDetail();
        
        if(!$arProperties) return false;
        if($this->catalog_detail_settings['settings']["catalog"]["catalog_delete_selector_props_symb"])
        {
            $deleteSymb = explode(",", $this->catalog_detail_settings['settings']["catalog"]["catalog_delete_selector_props_symb"]);

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
            if($arProp["PROPERTY_TYPE"]=="F")
            {
                $this->parseCatalogPropFile($code, $el);
            }else{
                $ar = $this->GetArraySrcAttr(htmlspecialchars_decode($this->catalog_detail_settings['settings']["catalog"]["selector_prop"][$code]));
                $path = $ar["path"];
                $attr = $ar["attr"];
                
                if($attr)
                    $text = pq($el)->find($path)->attr($attr);
                else
                    $text = pq($el)->find($path)->html();
                //var_dump(pq($el)->find('h1')->html());
                if($arProp["USER_TYPE"]!="HTML")
                    $text = strip_tags($text);
                $text = str_replace($deleteSymb, "", $text);
                
                $this->parseCatalogProp($code, $val, $text);
            }

        }
    }

    protected function getSelectorPropertiesDetail()
    {
        if(isset($this->catalog_detail_settings['settings']["catalog"]["selector_prop"]) && !empty($this->catalog_detail_settings['settings']["catalog"]["selector_prop"]))
        {
            $arProps = false;
            foreach($this->catalog_detail_settings['settings']["catalog"]["selector_prop"] as $i=>$prop)
            {
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
    
    protected function parseCatalogSelectorDetailProduct(&$el){
        $arProperties = $this->getSelectorDetailProduct();
        if(!$arProperties) return false;
        if($this->checkUniqCsv() && (!$this->isUpdate || !$this->isUpdate["param"])) return false;
        if($this->catalog_detail_settings['settings']["catalog"]["catalog_delete_selector_symb"])
        {
            $deleteSymb = explode(",", $this->catalog_detail_settings['settings']["catalog"]["catalog_delete_selector_symb"]);

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
            $text = pq($el)->find(htmlspecialchars_decode($this->catalog_detail_settings['settings']["catalog"]["selector_product"][$code]))->html();
            $text = strip_tags($text);
            $text = str_replace($deleteSymb, "", $text);
            $text = trim($text);
            
            $text =  str_replace(",", ".", $text);
            $text = preg_replace("/\.{1}$/", "", $text);
            $text = preg_replace('/[^0-9.]/', "", $text);
            
            if(isset($this->catalog_detail_settings['settings']["catalog"]["selector_product_koef"][$code]) && !empty($this->catalog_detail_settings['settings']["catalog"]["selector_product_koef"][$code]))
            {
                $text = $text*$this->catalog_detail_settings['settings']["catalog"]["selector_product_koef"][$code];
            }
            
            $this->arProduct[$code] = $text;
            unset($text);
        }
        unset($arProperties);
        unset($deleteSymb);
    }

    protected function parseCatalogFindDetailProduct(&$el)
    {
        $arProperties = $this->getFindProductDetail();
        if(!$arProperties) return false;
        if($this->checkUniqCsv() && (!$this->isUpdate || !$this->isUpdate["param"])) return false;
        $find = htmlspecialchars_decode($this->catalog_detail_settings['settings']["catalog"]["selector_find_size"]);
        if($this->catalog_detail_settings['settings']["catalog"]["catalog_delete_find_symb"])
        {
            $deleteSymb = explode(",", $this->catalog_detail_settings['settings']["catalog"]["catalog_delete_find_symb"]);

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

        foreach(pq($el)->find($find) as $prop)
        {
            $text = pq($prop)->html();
            $text = strip_tags($text);
            $text = str_replace($deleteSymb, "", $text);
            foreach($arProperties as $code=>$val)
            {
                //if(preg_match("/".$val."/", $text))
                if(strpos($text, $val)!==false)
                {
                    $text = str_replace($val, "", $text);
                    $text = trim($text);
                    
                    $text =  str_replace(",", ".", $text);
                    $text = preg_replace("/\.{1}$/", "", $text);
                    $text = preg_replace('/[^0-9.]/', "", $text);
                    
                    if(isset($this->catalog_detail_settings['settings']["catalog"]["find_product_koef"][$code]) && !empty($this->catalog_detail_settings['settings']["catalog"]["find_product_koef"][$code]))
                    {
                        $text = $text*$this->catalog_detail_settings['settings']["catalog"]["find_product_koef"][$code];
                    }
                    
                    $this->arProduct[$code] = $text;
                }
            }
            unset($text);
        }
        unset($deleteSymb);
        unset($arProperties);
    }

    public function getSelectorDetailProduct()
    {
        if(isset($this->catalog_detail_settings['settings']["catalog"]["selector_product"]) && !empty($this->catalog_detail_settings['settings']["catalog"]["selector_product"]))foreach($this->catalog_detail_settings['settings']["catalog"]["selector_product"] as $i=>$prop)
        {
            $prop = trim($prop);
            if(!empty($prop))
            {
                $arProps[$i] = $prop;
            }
        }
        if(!$arProps) return false;
        return $arProps;
    }

    public function getFindProductDetail()
    {
        if(isset($this->catalog_detail_settings['settings']["catalog"]["find_product"]) && !empty($this->catalog_detail_settings['settings']["catalog"]["find_product"]))foreach($this->catalog_detail_settings['settings']["catalog"]["find_product"] as $i=>$prop)
        {
            $prop = trim($prop);
            if(!empty($prop))
            {
                $arProps[$i] = $prop;
            }
        }
        if(!$arProps) return false;
        return $arProps;
    }
    
    protected function getDetailPage()
    {
        $this->catalogSleep();
        $this->detailFileHtml = new FileGetHtml();
        $this->detailPage = $this->fileHtml->file_get_html($this->arFields["LINK"], $this->proxy, $this->auth, $this);

        foreach(GetModuleEvents("shs.parser", "parserCatalogDetailPageAfter", true) as $arEvent)
            ExecuteModuleEventEx($arEvent, array(&$this));
            
        $this->DeleteCharsetHtml5($this->detailPage);
        $this->detailHttpCode = $this->fileHtml->httpCode;
        if($this->detailHttpCode!=200 && $this->detailHttpCode!=301 && $this->detailHttpCode!=302 && $this->detailHttpCode!=303)
        {
            $this->errors[] = $this->arFields["NAME"]."[".$this->arFields["LINK"]."]".GetMessage("parser_error_connect")."[".$this->detailHttpCode."]";
        }
        $this->detailHtml = phpQuery::newDocument($this->detailPage, "text/html;charset=".LANG_CHARSET);
        $this->base = $this->GetMetaBase($this->detailHtml);
        $details = $this->detailHtml[$this->catalog_detail_settings['SELECTOR']];
        
        foreach( $details as $k => $detail) {
            return $detail;
        }
        $this->errors[] = GetMessage("parser_error_selecto_detail_notfound");
    }
}

?>