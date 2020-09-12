<?php
namespace Bitrix\Shs\Helper;

use Bitrix\Main,
    Bitrix\Main\Localization\Loc,
    Bitrix\Main\Entity;
Loc::loadMessages(__FILE__);

/**
 * Class ParserResultTable
 *
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> PARSER_ID int optional
 * <li> START_LAST_TIME datetime optional
 * <li> END_LAST_TIME datetime optional
 * </ul>
 *
 * @package Bitrix\Shs
 **/

class Export
{
    protected $prefix = 'parser_export_';
    protected $errors = array();

    public function checkData(array $data, $step)
    {
        if($step == 1)
        {
            $arData = array_filter(array_intersect_key($data, array_flip(array('SITE_API', 'LOGIN', 'PASSWORD'))), function($v){return trim($v);});

            if(count($arData) != 3)
                return false;

            return true;
        }
        elseif($step=2)
        {
            $arData = array_filter(array_intersect_key($data, array_flip(array('SITE_API', 'LOGIN', 'PASSWORD', 'IBLOCK'))), function($v){return trim($v);});

            if(count($arData) != 4)
                return false;

            return true;
        }
        elseif($step==3)
        {
            $arData = array_filter($data, function($v){return trim($v);});

            if(count($arData != count($data)))
                return false;

            return true;
        }

        return false;
    }
    
    public function checkPage($href)
    {
        if(!function_exists('\curl_init'))
        {
            $this->errors[] = Loc::getMessage($this->prefix.'not_exists_curl');
            return false;
        }

        if(empty($href))
        {
            $this->errors[] = Loc::getMessage($this->prefix.'empty_href');
            return false;
        }

        if(strpos($href, '/api/parser/') === false)
            $href .= '/api/parser/index.php';

        $curl = \curl_init();
        curl_setopt($curl, CURLOPT_URL, $href);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, '');
        curl_setopt($curl, CURLOPT_HEADER, 1);
        curl_setopt($curl, CURLINFO_HEADER_OUT, 1);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_exec($curl);

        $code = curl_getinfo( $curl, CURLINFO_HTTP_CODE );
        $code_group = floor($code / 100);

        switch($code_group)
        {
            case 1:
            case 2:
            case 3:
                break;
            case 4:
                if(in_array($code, array(400, 401, 403, 404)))
                    $this->errors[] = str_replace('#CODE#', $code, Loc::getMessage("parser_auth_fail_".$code));
                else
                    $this->errors[] = str_replace('#CODE#', $code, Loc::getMessage("parser_auth_fail_client"));

                return false;
            case 5:
                $this->errors[] = str_replace('#CODE#', $code, Loc::getMessage("parser_auth_fail_server"));
                return false;
            default:
                $this->errors[] = str_replace('#CODE#', $code, Loc::getMessage("parser_auth_fail"));
                return false;
        }

        return true;
    }

    public function getData($href, array $params = array())
    {
        if(!function_exists('\curl_init'))
        {
            $this->errors[] = Loc::getMessage($this->prefix.'not_exists_curl');
            return false;
        }

        if(empty($href))
        {
            $this->errors[] = Loc::getMessage($this->prefix.'empty_href');
            return false;
        }

        if(strpos($href, '/api/parser/') === false)
            $href .= '/api/parser/index.php';

        $file = $params['export_file'];
        unset($params['export_file']);
        
        $curl = \curl_init();
        curl_setopt($curl, CURLOPT_URL, $href);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, array('query' => base64_encode(serialize($params)), 'file' => $file));
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLINFO_HEADER_OUT, 1);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $data = (array)json_decode(curl_exec($curl));

        if(empty($data))
        {
            $this->errors[] = 'bad request';
            return false;
        }

        if(isset($data['error']))
        {
            $this->errors[] = $data['error_message'];

            if(!isset($data['ID']))
                return false;
        }

        $code = curl_getinfo( $curl, CURLINFO_HTTP_CODE );
        $code_group = floor($code / 100);

        switch($code_group)
        {
            case 1:
            case 2:
            case 3:
                break;
            case 4:
                if(in_array($code, array(400, 401, 403, 404)))
                    $this->errors[] = str_replace('#CODE#', $code, Loc::getMessage("parser_auth_fail_".$code));
                else
                    $this->errors[] = str_replace('#CODE#', $code, Loc::getMessage("parser_auth_fail_client"));

                return false;
            case 5:
                $this->errors[] = str_replace('#CODE#', $code, Loc::getMessage("parser_auth_fail_server"));
                return false;
            default:
                $this->errors[] = str_replace('#CODE#', $code, Loc::getMessage("parser_auth_fail"));
                return false;
        }

        return $data;
    }

    public function getErrors()
    {
        return $this->errors;
    }

    public function collectPrices($settings)
    {
        $result = array();

        if(!\Bitrix\Main\Loader::includeModule('catalog') )
            return $result;

        if(!empty($settings['prices_preview']))
            foreach($settings['prices_preview'] as $priceID => $price)
                $result[] = $priceID;

        if(!empty($settings['prices_detail']))
            foreach($settings['prices_detail'] as $priceID => $price)
                $result[] = $priceID;

        if(!empty($settings['adittional_currency']))
            foreach($settings['adittional_currency'] as $priceID => $price)
                $result[] = $priceID;

        if(!empty($settings['catalog']['price_type']))
            $result[] = $settings['catalog']['price_type'];

        $result = array_unique($result);

        if(!empty($result))
        {
            $res = \Bitrix\Catalog\GroupTable ::getList(array('filter' => array('ID' => $result), 'select' => array('ID', 'NAME')));
            $result = array();

            while($arPrice = $res->fetch())
            {
                $result[$arPrice['ID']] = '['.$arPrice['ID'].'] '.$arPrice['NAME'];
            }

            return $result;
        }

        return array();
    }

    public function collectSections($parser)
    {
        $result = array();

        if(!empty($parser['SECTION_ID']))
            $result[] = $parser['SECTION_ID'];

        if(!empty($parser['SETTINGS']['catalog']['section_dop']))
            foreach($parser['SETTINGS']['catalog']['section_dop'] as $section_id)
                $result[] = $section_id;

        $result = array_unique($result);

        if(!empty($result))
        {
            if(!\Bitrix\Main\Loader::includeModule('iblock') )
                return array();

            $res = \Bitrix\Iblock\SectionTable::getList(array('filter' => array('ID' => $result), 'select' => array('ID', 'NAME')));
            $result = array();

            while($arSection = $res ->  fetch())
            {
                $result[$arSection['ID']] = '['.$arSection['ID'].'] '.$arSection['NAME'];
            }

            return $result;
        }
        else
            return array();

        return array();
    }

    public function collectProperties($settings, $iblockId)
    {
        $result = array();

        if(!empty($settings['catalog']['more_image_props']))
            $result[] = $settings['catalog']['more_image_props'];

        if(!empty($settings['catalog']['default_prop']))
            foreach($settings['catalog']['default_prop'] as $propId => $val)
                $result[] = $propId;

        if(!empty($settings['catalog']['selector_prop']))
            foreach($settings['catalog']['selector_prop'] as $propId => $val)
                $result[] = $propId;

        if(!empty($settings['catalog']['find_prop']))
            foreach($settings['catalog']['find_prop'] as $propId => $val)
                $result[] = $propId;

        if(!empty($settings['catalog']['selector_prop_preview']))
            foreach($settings['catalog']['selector_prop_preview'] as $propId => $val)
                $result[] = $propId;

        if(!empty($settings['catalog']['find_prop_preview']))
            foreach($settings['catalog']['find_prop_preview'] as $propId => $val)
                $result[] = $propId;

        if(!empty($settings['catalog']['action_props_val']))
            foreach($settings['catalog']['action_props_val'] as $propId => $val)
                $result[] = $propId;

        if(!empty($settings['catalog']['uniq']['prop']))
            $result[] = $settings['catalog']['uniq']['prop'];

        if(!empty($settings['props_filter_circs']))
            foreach($settings['props_filter_circs'] as $propId => $val)
                $result[] = $propId;

        if(!empty($settings['props_filter_value']))
            foreach($settings['props_filter_value'] as $arFilter)
                foreach($arFilter as $propId => $val)
                    $result[] = $propId;

        $result = array_unique($result);

        if(!empty($result))
        {
            $res = \Bitrix\Iblock\PropertyTable::getList(array('filter' => array('CODE' => $result, 'IBLOCK_ID' => $iblockId), 'select' => array('ID', 'NAME', 'CODE', 'IBLOCK_ID')));
            $result = array();

            while($arProperty = $res->fetch())
            {
                $result[$arProperty['ID']] = '['.$arProperty['CODE'].'] '.$arProperty['NAME'];
            }

            return $result;
        }

        return array();
    }

    public function initIgnoreList(array $data)
    {
        $data = array_merge($data, array_diff_key(array(
            'PRICE' => array(),
            'PROPERTY' => array(),
            'SECTION' => array()
        ), $data));

        return array(
            'PRICE' => array_intersect($data['PRICE'], array('0' => 'ignore')),
            'SECTION' => array_intersect($data['SECTION'], array('0' => 'ignore')),
            'PROPERTY' => array_intersect($data['PROPERTY'], array('0' => 'ignore')),
        );
    }

    public function initCreateList(array $data)
    {
        $data = array_merge($data, array_diff_key(array(
            'PRICE' => array(),
            'PROPERTY' => array(),
            'SECTION' => array()
        ), $data));

        return array(
            'PRICE' => array_intersect($data['PRICE'], array('0' => 'create')),
            'SECTION' => array_intersect($data['SECTION'], array('0' => 'create')),
            'PROPERTY' => array_intersect($data['PROPERTY'], array('0' => 'create')),
        );
    }

    public function compliteCreateList(array $data, $IBLOCK_ID, $IBLOCK_ID_TO)
    {
        return array(
            'PRICE' => $this->getPriceList(array_keys($data['PRICE'])),
            'SECTION' => $this->getSectionList(array_keys($data['SECTION']), $IBLOCK_ID, $IBLOCK_ID_TO),
            'PROPERTY' => $this->getPropertyList(array_keys($data['PROPERTY']), $IBLOCK_ID, $IBLOCK_ID_TO),
        );
    }

    protected function getPriceList(array $ID)
    {
        if(empty($ID))
            return array();
        
       $ar = \Bitrix\Catalog\GroupTable::getList(array('filter'=>array('ID' => $ID), 'select' => array('ID', 'NAME', 'XML_ID')))->fetchAll();

       if(empty($ar))
           return array();
       
       return array_column($ar, NULL, 'ID');
    }

    protected function getSectionList(array $ID, $IBLOCK_ID, $IBLOCK_ID_TO)
    {
        if(empty($ID))
            return array();

        $ar = \Bitrix\Iblock\SectionTable::getList(array('filter'=>array('ID' => $ID, 'IBLOCK_ID' => $IBLOCK_ID), 'select' => array('ID', 'NAME', 'IBLOCK_ID', 'ACTIVE', 'XML_ID', 'DESCRIPTION', 'DESCRIPTION_TYPE', 'CODE')))->fetchAll();

        $result =  array();

        foreach($ar as $section)
        {
            $section['IBLOCK_ID'] = $IBLOCK_ID_TO;
            $result[$section['ID']] = $section;
        }

        return $result;
    }

    protected function getPropertyList(array $ID, $IBLOCK_ID, $IBLOCK_ID_TO)
    {
        if(empty($ID))
            return array();

        $ar = \Bitrix\Iblock\PropertyTable::getList(array('filter'=>array('ID' => $ID, 'IBLOCK_ID' => $IBLOCK_ID)))->fetchAll();

        $result =  array();

        foreach($ar as $property)
        {
            $property['IBLOCK_ID'] = $IBLOCK_ID_TO;
            $result[$property['CODE']] = $property;
        }

        return $result;
    }

    public function correctByIgnoreList($parser, $ignoreList)
    {
        //clear sections
        $parser['SECTION_ID'] = $this->valueByIgnoreList('SECTION',  $parser['SECTION_ID'], $ignoreList);

        if(is_array($parser['SETTINGS']['catalog']['section_dop']))
            foreach($parser['SETTINGS']['catalog']['section_dop'] as $key => $val)
            {
                if(empty($this->valueByIgnoreList('SECTION', $val, $ignoreList)))
                {
                    unset($parser['SETTINGS']['catalog']['section_dop'][$key]);
                    unset($parser['SETTINGS']['catalog']['rss_dop'][$key]);
                }
            }

        //clear prices
        $parser['SETTINGS']['catalog']['price_type'] = $this->valueByIgnoreList('PRICE',  $parser['SETTINGS']['catalog']['price_type'], $ignoreList);

        if(is_array($parser['SETTINGS']['prices_preview']))
            foreach($parser['SETTINGS']['prices_preview'] as $key => $val)
                if(empty($this->valueByIgnoreList('PRICE',  $key, $ignoreList)))
                    unset($parser['SETTINGS']['prices_preview'][$key]);

        if(is_array($parser['SETTINGS']['prices_detail']))
            foreach($parser['SETTINGS']['prices_detail'] as $key => $val)
                if(empty($this->valueByIgnoreList('PRICE',  $key, $ignoreList)))
                    unset($parser['SETTINGS']['prices_detail'][$key]);

        if(is_array($parser['SETTINGS']['adittional_currency']))
            foreach($parser['SETTINGS']['adittional_currency'] as $key => $val)
                if(empty($this->valueByIgnoreList('PRICE',  $key, $ignoreList)))
                    unset($parser['SETTINGS']['adittional_currency'][$key]);

        //clear property
        $res = \Bitrix\Iblock\PropertyTable::getList(array('filter'=>array('IBLOCK_ID' => $parser['IBLOCK_ID'], '!CODE' => false), 'select' => array('ID', 'CODE')));
        $propertyList = array();

        while($arProp = $res -> fetch())
            $propertyList[$arProp['CODE']] = $arProp['ID'];

        if(isset($parser["SETTINGS"]['catalog']['uniq']['prop']))
        {
            unset($parser["SETTINGS"]['catalog']['uniq']);
        }

        if(is_array($parser['SETTINGS']['props_filter_value']))
            foreach($parser['SETTINGS']['props_filter_value'] as $index => $ar)
            {
                foreach($ar as $propCode => $val)
                {
                    if(empty($this->valueByIgnoreList('PROPERTY', $propertyList[$propCode], $ignoreList)))
                    {
                        unset($parser['SETTINGS']['props_filter_value'][$index][$propCode]);
                        unset($parser['SETTINGS']['props_filter_circs'][$index][$propCode]);
                    }
                }

                if(empty($parser['SETTINGS']['props_filter_value'][$index]))
                {
                    unset($parser['SETTINGS']['props_filter_value'][$index]);
                    unset($parser['SETTINGS']['props_filter_circs'][$index]);
                }
            }

        if(empty($this->valueByIgnoreList('PROPERTY', $propertyList[$parser['SETTINGS']['catalog']['more_image_props']], $ignoreList)))
            $parser['SETTINGS']['catalog']['more_image_props'] = '';

        if(is_array($parser['SETTINGS']['catalog']['default_prop']))
            foreach($parser['SETTINGS']['catalog']['default_prop'] as $code => $val)
                if(empty($this->valueByIgnoreList('PROPERTY', $propertyList[$code], $ignoreList)))
                    unset($parser['SETTINGS']['catalog']['default_prop'][$code]);

        if(is_array($parser['SETTINGS']['catalog']['selector_prop']))
            foreach($parser['SETTINGS']['catalog']['selector_prop'] as $code => $val)
                if(empty($this->valueByIgnoreList('PROPERTY', $propertyList[$code], $ignoreList)))
                    unset($parser['SETTINGS']['catalog']['selector_prop'][$code]);

        if(is_array($parser['SETTINGS']['catalog']['find_prop']))
            foreach($parser['SETTINGS']['catalog']['find_prop'] as $code => $val)
                if(empty($this->valueByIgnoreList('PROPERTY', $propertyList[$code], $ignoreList)))
                    unset($parser['SETTINGS']['catalog']['find_prop'][$code]);

        if(is_array($parser['SETTINGS']['catalog']['selector_prop_preview']))
            foreach($parser['SETTINGS']['catalog']['selector_prop_preview'] as $code => $val)
                if(empty($this->valueByIgnoreList('PROPERTY', $propertyList[$code], $ignoreList)))
                    unset($parser['SETTINGS']['catalog']['selector_prop_preview'][$code]);

        if(is_array($parser['SETTINGS']['catalog']['find_prop_preview']))
            foreach($parser['SETTINGS']['catalog']['find_prop_preview'] as $code => $val)
                if(empty($this->valueByIgnoreList('PROPERTY', $propertyList[$code], $ignoreList)))
                    unset($parser['SETTINGS']['catalog']['find_prop_preview'][$code]);

        if(is_array($parser['SETTINGS']['catalog']['action_props_val']))
            foreach($parser['SETTINGS']['catalog']['action_props_val'] as $code => $val)
                if(empty($this->valueByIgnoreList('PROPERTY', $propertyList[$code], $ignoreList)))
                    unset($parser['SETTINGS']['catalog']['action_props_val'][$code]);

        if(is_array($parser['SETTINGS']['catalog']['action_props']))
            foreach($parser['SETTINGS']['catalog']['action_props'] as $code => $val)
                if(empty($this->valueByIgnoreList('PROPERTY', $propertyList[$code], $ignoreList)))
                    unset($parser['SETTINGS']['catalog']['action_props'][$code]);

        return $parser;
    }

    protected function valueByIgnoreList($type, $id, $ignoreList)
    {
        return isset($ignoreList[$type][$id]) ? '' : $id;
    }
    
    public function run($href, $parser, $crealeList, $params)
    {
        if(empty($href) || empty($parser))
        {
            $this->errors[] = 'bad params';
            return false;
        }

        $newParams = array('action'=>'export',
            'createlist'=> $crealeList,
            'parser' => $parser
            );

        $file = $_SERVER['DOCUMENT_ROOT'].$parser['RSS'];

        if(file_exists($file))
        {
            $cfile = curl_file_create($file, mime_content_type($file), 'export_file');
            $newParams['export_file'] = $cfile;
        }

        $data = $this->getData($href, array_merge($params, $newParams));

        return $data;
    }

    public function changeSettings($parser, $changeSettings)
    {
        $changeSettings = array_merge($changeSettings, array_diff_key(array(
            'PRICE' => array(),
            'PROPERTY' => array(),
            'SECTION' => array()
        ), $changeSettings));


        $changeSettings = array(
            'PRICE' => array_diff($changeSettings['PRICE'], array('ignore','create')),
            'SECTION' => array_diff($changeSettings['SECTION'], array('ignore','create')),
            'PROPERTY' => array_diff($changeSettings['PROPERTY'], array('ignore','create')),
        );

        if(isset($changeSettings['SECTION'][$parser['SECTION_ID']]))
        $parser['SECTION_ID'] = $changeSettings['SECTION'][$parser['SECTION_ID']];

        if(is_array($parser['SETTINGS']['catalog']['section_dop']))
            foreach($parser['SETTINGS']['catalog']['section_dop'] as $key => $val)
                if(isset($changeSettings['SECTION'][$val]))
                    $parser['SETTINGS']['catalog']['section_dop'][$key] = $changeSettings['SECTION'][$val];

        //clear prices
        if(isset($changeSettings['PRICE'][$parser['SETTINGS']['catalog']['price_type']]))
            $parser['SETTINGS']['catalog']['price_type'] = $changeSettings['PRICE'][$parser['SETTINGS']['catalog']['price_type']];
            
        if(is_array($parser['SETTINGS']['prices_preview']))
            foreach($parser['SETTINGS']['prices_preview'] as $key => $val)
                if(isset($changeSettings['PRICE'][$key]))
                {
                    unset($parser['SETTINGS']['prices_preview'][$key]);
                    $parser['SETTINGS']['prices_preview'][$changeSettings['PRICE'][$key]] = $val;
                }

        if(is_array($parser['SETTINGS']['prices_detail']))
            foreach($parser['SETTINGS']['prices_detail'] as $key => $val)
                if(isset($changeSettings['PRICE'][$key]))
                {
                    unset($parser['SETTINGS']['prices_detail'][$key]);
                    $parser['SETTINGS']['prices_detail'][$changeSettings['PRICE'][$key]] = $val;
                }

        if(is_array($parser['SETTINGS']['adittional_currency']))
            foreach($parser['SETTINGS']['adittional_currency'] as $key => $val)
                if(isset($changeSettings['PRICE'][$key]))
                {
                    unset($parser['SETTINGS']['adittional_currency'][$key]);
                    $parser['SETTINGS']['adittional_currency'][$changeSettings['PRICE'][$key]] = $val;
                }

        //clear property
        $res = \Bitrix\Iblock\PropertyTable::getList(array('filter'=>array('IBLOCK_ID' => $parser['IBLOCK_ID'], '!CODE' => false), 'select' => array('ID', 'CODE')));
        $propertyList = array();

        while($arProp = $res -> fetch())
            $propertyList[$arProp['CODE']] = $arProp['ID'];
        
        if(isset($parser["SETTINGS"]['catalog']['uniq']['prop']))
        {

        }

        if(is_array($parser['SETTINGS']['props_filter_value']))
            foreach($parser['SETTINGS']['props_filter_value'] as $index => $ar)
            {
                foreach($ar as $propCode => $val)
                {
                    if(isset($changeSettings['PROPERTY'][$propertyList[$propCode]]))
                    {
                        unset($parser['SETTINGS']['props_filter_value'][$index][$propCode]);
                        $circs = $parser['SETTINGS']['props_filter_circs'][$index][$propCode];
                        unset($parser['SETTINGS']['props_filter_circs'][$index][$propCode]);
                        $parser['SETTINGS']['props_filter_value'][$index][$changeSettings['PROPERTY'][$propertyList[$propCode]]] = $val;
                        $parser['SETTINGS']['props_filter_circs'][$index][$changeSettings['PROPERTY'][$propertyList[$propCode]]] = $circs;
                    }
                }
            }

        if(isset($changeSettings['PROPERTY'][$propertyList[$parser['SETTINGS']['catalog']['more_image_props']]]))
            $parser['SETTINGS']['catalog']['more_image_props'] = $changeSettings['PROPERTY'][$propertyList[$parser['catalog']['more_image_props']]];

        if(is_array($parser['SETTINGS']['catalog']['default_prop']))
            foreach($parser['SETTINGS']['catalog']['default_prop'] as $code => $val)
                if(isset($changeSettings['PROPERTY'][$propertyList[$code]]))
                {
                    unset($parser['SETTINGS']['catalog']['default_prop'][$code]);
                    $parser['SETTINGS']['catalog']['more_image_props'][$changeSettings['PROPERTY'][$propertyList[$code]]] = $val;
                }

        if(is_array($parser['SETTINGS']['catalog']['selector_prop']))
            foreach($parser['SETTINGS']['catalog']['selector_prop'] as $code => $val)
                if(isset($changeSettings['PROPERTY'][$propertyList[$code]]))
                {
                    unset($parser['SETTINGS']['catalog']['selector_prop'][$code]);
                    $parser['SETTINGS']['catalog']['selector_prop'][$changeSettings['PROPERTY'][$propertyList[$code]]] = $val;
                }

        if(is_array($parser['SETTINGS']['catalog']['find_prop']))
            foreach($parser['SETTINGS']['catalog']['find_prop'] as $code => $val)
                if(isset($changeSettings['PROPERTY'][$propertyList[$code]]))
                {
                    unset($parser['SETTINGS']['catalog']['find_prop'][$code]);
                    $parser['SETTINGS']['catalog']['find_prop'][$changeSettings['PROPERTY'][$propertyList[$code]]] = $val;
                }

        if(is_array($parser['SETTINGS']['catalog']['selector_prop_preview']))
            foreach($parser['SETTINGS']['catalog']['selector_prop_preview'] as $code => $val)
                if(isset($changeSettings['PROPERTY'][$propertyList[$code]]))
                {
                    unset($parser['SETTINGS']['catalog']['selector_prop_preview'][$code]);
                    $parser['SETTINGS']['catalog']['selector_prop_preview'][$changeSettings['PROPERTY'][$propertyList[$code]]] = $val;
                }

        if(is_array($parser['SETTINGS']['catalog']['find_prop_preview']))
            foreach($parser['SETTINGS']['catalog']['find_prop_preview'] as $code => $val)
                if(isset($changeSettings['PROPERTY'][$propertyList[$code]]))
                {
                    unset($parser['SETTINGS']['catalog']['find_prop_preview'][$code]);
                    $parser['SETTINGS']['catalog']['find_prop_preview'][$changeSettings['PROPERTY'][$propertyList[$code]]] = $val;
                }

        if(is_array($parser['SETTINGS']['catalog']['action_props_val']))
            foreach($parser['SETTINGS']['catalog']['action_props_val'] as $code => $val)
                if(isset($changeSettings['PROPERTY'][$propertyList[$code]]))
                {
                    unset($parser['SETTINGS']['catalog']['action_props_val'][$code]);
                    $parser['SETTINGS']['catalog']['action_props_val'][$changeSettings['PROPERTY'][$propertyList[$code]]] = $val;
                }

        if(is_array($parser['SETTINGS']['catalog']['action_props']))
            foreach($parser['SETTINGS']['catalog']['action_props'] as $code => $val)
                if(isset($changeSettings['PROPERTY'][$propertyList[$code]]))
                {
                    unset($parser['SETTINGS']['catalog']['action_props'][$code]);
                    $parser['SETTINGS']['catalog']['action_props'][$changeSettings['PROPERTY'][$propertyList[$code]]] = $val;
                }

        return $parser;
    }
}