<?php
class SotbitXmlCatalogParser extends SotbitXlsCatalogParser {
    
    //public $catalog_detail_settings = array();
    
    public function __construct()
    {
        parent::__construct(); 
        if($this->typeN=='xls_catalo' || $this->typeN=='xml_catalo'){   
            $this->setDetailSettings();                 
        }
    } 
    
    protected function parseDetailPageXml(&$el)
    {
        if($this->checkUniq() && !$this->isUpdate) return false;
        if($this->settings['catalog']['detail_link'] !== ''){
            $selector = $this->settings['catalog']['detail_link'];   
            $detailPage = htmlspecialchars_decode($selector);    
            $detailPage = $this->GetArraySrcAttr($detailPage); 
            $path = $detailPage["path"];
            $attr = $detailPage["attr"];
        
            if (empty($attr)) 
                $detailPage = strip_tags(pq($el)->find($path)->html());
            elseif(!empty($attr)) 
                $detailPage = trim(pq($el)->find($path)->attr($attr)); 
              
            if(!isset($detailPage) && empty($detailPage)){
                return false;
            }
            $this->arFields["LINK"] = trim($detailPage);              
        
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
            unset($el_detail);
            
            foreach(GetModuleEvents("shs.parser", "parserCatalogDetail", true) as $arEvent)
                ExecuteModuleEventEx($arEvent, array($this->id, &$el, &$this->arFields)); 
        }         
    }    
}
 
?>