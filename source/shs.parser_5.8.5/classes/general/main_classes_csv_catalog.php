<?php
class SotbitCsvCatalogParser extends SotbitXmlCatalogParser {
    
   // protected $catalog_detail_settings = array();
    
    public function __construct()
    {
        parent::__construct();          
        if($this->typeN=='xls_catalo' || $this->typeN=='xml_catalo'){   
            $this->setDetailSettings();                 
        }                    
    }    
}
 
?>