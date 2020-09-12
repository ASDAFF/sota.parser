<?
//check ajax-request
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
$query = base64_decode($_POST['query']);
$_POST = unserialize($query);

if(empty($_POST))
    LocalRedirect("/");

if(!empty($_POST) && isset($_POST['login']) && isset($_POST['password']))
{
    global $USER;
    if (!is_object($USER)) $USER = new CUser;
    $arAuthResult = $USER->Login($_POST['login'], $_POST['password'], "Y");
    if($arAuthResult !== true)
        exit(json_encode(array('error' => true, 'error_message' => $arAuthResult['MESSAGE'])));
}
else
{
    exit(json_encode(array('error' => true, 'error_message' => 'bad request')));
}

if(isset($_POST['get']) && $_POST['get'] == 'iblocks')
{
    require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
    $result = \Bitrix\Iblock\IblockTable::getList( array(
        'select' => array(
            'ID',
            'NAME'
        ),
        'filter' => array()
    ) );
    
    $Iblocks = array();
    
    while ( $Iblock = $result->fetch() )
    {
        $Iblocks['REFERENCE_ID'][] = $Iblock['ID'];
        $Iblocks['REFERENCE'][] = '[' . $Iblock['ID'] . '] ' . $Iblock['NAME'];
    }
    
    exit(json_encode(array('iblocks' => $Iblocks)));
}

if(isset($_POST['get']) && $_POST['get'] == 'data' && isset($_POST['iblock']) && !empty($_POST['iblock']))
{
    require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
    $sections = array();
    $res = \Bitrix\Iblock\SectionTable::getList( array(
        'select' => array(
            'ID',
            'NAME',
        ),
        'filter' => array('IBLOCK_ID' => intval($_POST['iblock']))
    ) );
    while ( $section = $res->fetch() )
    {
        $sections['REFERENCE_ID'][] = $section['ID'];
        $sections['REFERENCE'][] = $section['NAME'];
    }
    
    $res = \CIBlockProperty::GetList(Array("sort"=>"asc", "name"=>"asc"), Array("ACTIVE"=>"Y", "IBLOCK_ID"=>intval($_POST['iblock']), '!CODE' => false, '!CODE' => ''), false, false, array('ID', 'NAME', 'CODE'));
    $properties = array();
    
    while($arProperty = $res -> fetch())
    {
        $properties['REFERENCE_ID'][] = $arProperty['CODE'];
        $properties['REFERENCE'][] = '['.$arProperty['CODE'].'] '.$arProperty['NAME'];
    }
    
    $res = \Bitrix\Catalog\GroupTable ::getList(array('select' => array('ID', 'NAME')));
    $prices = array();
    
    while($arPrice = $res->fetch())
    {
        $prices['REFERENCE_ID'][] = $arPrice['ID'];
        $prices['REFERENCE'][] = '['.$arPrice['ID'].'] '.$arPrice['NAME'];
    }
    
    $result = \Bitrix\Iblock\IblockTable::getList( array(
        'select' => array(
            'ID',
            'NAME'
        ),
        'filter' => array()
    ) );
    
    $Iblocks = array();
    
    while ( $Iblock = $result->fetch() )
    {
        $Iblocks['REFERENCE_ID'][] = $Iblock['ID'];
        $Iblocks['REFERENCE'][] = '[' . $Iblock['ID'] . '] ' . $Iblock['NAME'];
    }
    
    exit(json_encode(array('sections' => $sections, 'properties' => $properties, 'prices' => $prices, 'iblocks' => $Iblocks)));
}

if(isset($_POST['action']) && $_POST['action'] == 'export' && !empty($_POST['parser']))
{
    $parser = $_POST['parser'];
    $createList = $_POST['createlist'];
    $ignoreList = array(
        'PRICE' => array(),
        'SECTION' => array(),
        'PROPERTY' => array()
    );
    
    $changeSettings = array(
        'PRICE' => array(),
        'SECTION' => array(),
        'PROPERTY' => array()
    );
    
    if(isset($_FILES['file']) && !empty($_FILES['file']))
    {
        $ext = end(explode('/', $_FILES['file']['type']));
        $new_file_name = $_SERVER['DOCUMENT_ROOT'].'/upload/'.$_FILES['file']['name'].'('.date('d-m-Y_H:i:s').').'.$ext;
        if(move_uploaded_file($_FILES['file']['tmp_name'], $new_file_name))
            $parser['RSS'] = str_replace($_SERVER['DOCUMENT_ROOT'], '', $new_file_name);
    }
    
    global $USER, $APPLICATION;
    if (!is_object($USER)) $USER = new CUser;
    $arAuthResult = $USER->Login($_POST['login'], $_POST['password'], "Y");
    
    if($arAuthResult !== true)
        exit(json_encode(array('error' => true, 'error_message' => $arAuthResult['MESSAGE'])));
    
    $APPLICATION->arAuthResult = $arAuthResult;
    
    require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/shs.parser/lib/helper/export.php");
    $export = new \Bitrix\Shs\Helper\Export();
    $errors = array();
    if($createList['PRICE'])
    {
        foreach($createList['PRICE'] as $id => $arPrice)
        {
            if(isset($arPrice['ID']))
                unset($arPrice['ID']);
            
            $res = \Bitrix\Catalog\GroupTable::add($arPrice);
            
            if(!$res->isSuccess())
            {
                $errors['PRICE'] = array_merge($errors['PRICE'], $res->getErrorMessages());
                $ignoreList['PRICE'][$id] = $arPrice;
                continue;
            }
            
            $changeSettings['PRICE'][$id] = $res->getId();
        }
    }
    
    if($createList['SECTION'])
    {
        foreach($createList['SECTION'] as $id => $arSection)
        {
            if(isset($arSection['ID']))
                unset($arSection['ID']);
            
            if(!isset($arSection['CODE']) || empty($arSection['CODE']))
                $arSection['CODE'] = Cutil::translit($arSection['NAME'],"ru",array("replace_space"=>"-","replace_other"=>"-"));
            
            $bs = new CIBlockSection;
            $ID = $bs->Add($arSection);
            
            if(!$ID)
            {
                $errors['SECTION'][] = $bs->LAST_ERROR;
                $ignoreList['SECTION'][$id] = $arSection;
                continue;
            }
            
            $changeSettings['SECTION'][$id] = $ID;
        }
    }
    
    if($createList['PROPERTY'])
    {
        foreach($createList['PROPERTY'] as $id => $arProperty)
        {
            if(isset($arProperty['ID']))
                unset($arProperty['ID']);
            
            if(!isset($arProperty['CODE']) || empty($arProperty['CODE']))
                $arProperty['CODE'] = Cutil::translit($arProperty['NAME'],"ru",array("replace_space"=>"-","replace_other"=>"-"));
            
            $ibp = new CIBlockProperty;
            $ID = $ibp->Add($arProperty);
            
            if(!$ID)
            {
                $errors['PROPERTY'][] = $ibp->LAST_ERROR;
                $ignoreList['PROPERTY'][$id] = $arSection;
                continue;
            }
            
            $changeSettings['PROPERTY'][$id] = $ID;
        }
    }
    
    $parser = $export->correctByIgnoreList($parser, $ignoreList);
    $parser = $export->changeSettings($parser, $changeSettings);
    
    CModule::includeModule('shs.parser');
    
    $obParser = new ShsParserContent();
    RssContentParser::sotbitParserSetSettings($parser['SETTINGS']);
    
    if(isset($parser['ID']))
        unset($parser['ID']);
    
    $parser['SETTINGS'] = base64_encode(serialize($parser['SETTINGS']));
    
    $ID = $obParser->Add($parser);
    
    if(!empty($errors))
    {
        $errors['PRICE'] = implode('<br>', $errors['PRICE']);
        $errors['SECTION'] = implode('<br>', $errors['SECTION']);
        $errors['PROPERTY'] = implode('<br>', $errors['PROPERTY']);
    }
    
    exit(json_encode(array('error' => !$ID || !empty($errors), 'error_message' => implode('<br>', $errors), 'ID' => $ID)));
}

exit(json_encode(array('error' => true, 'error_description' => 'did nothing')));
?>