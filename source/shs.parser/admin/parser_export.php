<?php
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Shs\Helper\Export;

require_once ($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/shs.parser/include.php");

if(!empty($_POST))
{
    require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/shs.parser/lib/helper/export.php");
    $errors = array();
    
    if(isset($_POST['export']))
    {
        $export = new Export();

        $ignoreList = $export->initIgnoreList(array_intersect_key($_POST, array_flip(array('PRICE', 'SECTION', 'PROPERTY'))));
        $createList = $export->initCreateList(array_intersect_key($_POST, array_flip(array('PRICE', 'SECTION', 'PROPERTY'))));

        $parser = ShsParserContent::GetByID($_POST['PARSER'])->fetch();
        $parser['SETTINGS'] = unserialize(base64_decode($parser['SETTINGS']));

        $parser_prices = $export->collectPrices($parser['SETTINGS']);
        $parser_sections = $export->collectSections($parser);
        $parser_properties = $export->collectProperties($parser['SETTINGS'], $parser['IBLOCK_ID']);

        $createList = $export->compliteCreateList($createList, $parser['IBLOCK_ID'], $_POST['IBLOCK']);
        $parser = $export->correctByIgnoreList($parser, $ignoreList);
        $parser = $export->changeSettings($parser, array_intersect_key($_POST, array_flip(array('PRICE', 'SECTION', 'PROPERTY'))));
        $parser['IBLOCK_ID'] = intval($_POST['IBLOCK']);
        if(!($dataRun = $export->run($_POST['SITE_API'], $parser, $createList, array('login'=> $_POST['LOGIN'], 'password' => $_POST['PASSWORD']))))
        {
            $errors = array_merge($errors, $export->getErrors());
            exit('end');
        }

        if(!empty($export->getErrors()))
            $errors = array_merge($errors, $export->getErrors());

        $_POST['action'] = 'select_iblock';
    }

    switch($_POST['action'])
    {
        case 'select_iblock':
            $export = new Export();
            if(!($checkedData = $export->checkData($_POST, 1)))
            {
                $errors[] = 'неверно заполнены данные';
                break;
            }
            if(!($checkedPage = $export->checkPage($_POST['SITE_API'])))
            {
                $errors = array_merge($errors, $export->getErrors());
                break;
            }
            if(!($data = $export->getData($_POST['SITE_API'], array('iblock' => $_POST["IBLOCK"], 'get' => 'data', 'login' => $_POST['LOGIN'], 'password'=>$_POST['PASSWORD']))))
            {
                $errors = array_merge($errors, $export->getErrors());
                break;
            }

            $default = array(
                'ignore' => GetMessage('parser_export_ignore_item'),
                'create' => GetMessage('parser_export_create_item'),
            );

            $data['prices'] = (array)$data['prices'];

            $data['prices']['REFERENCE'] = array_merge(array_values($default), is_array($data['prices']['REFERENCE']) ? $data['prices']['REFERENCE'] : array());
            $data['prices']['REFERENCE_ID'] = array_merge(array_keys($default), is_array($data['prices']['REFERENCE_ID']) ? $data['prices']['REFERENCE_ID'] : array());

            $data['sections'] = (array)$data['sections'];
            $data['sections']['REFERENCE'] = array_merge(array_values($default), is_array($data['sections']['REFERENCE']) ? $data['sections']['REFERENCE'] : array());
            $data['sections']['REFERENCE_ID'] = array_merge(array_keys($default), is_array($data['sections']['REFERENCE_ID']) ? $data['sections']['REFERENCE_ID'] : array());

            $data['properties'] = (array)$data['properties'];
            $data['properties']['REFERENCE'] = array_merge(array_values($default), is_array($data['properties']['REFERENCE']) ? $data['properties']['REFERENCE'] : array());
            $data['properties']['REFERENCE_ID'] = array_merge(array_keys($default), is_array($data['properties']['REFERENCE_ID']) ? $data['properties']['REFERENCE_ID'] : array());

        case 'connection':

            $export = new Export();

            if(!$checkedData && !$export->checkData($_POST, 1))
            {
                $errors[] = 'неверно заполнены данные';
                break;
            }

            if(!$checkedPage && !$export->checkPage($_POST['SITE_API']))
            {
                $errors = array_merge($errors, $export->getErrors());
                break;
            }

            if(!$data && !($data = $export->getData($_POST['SITE_API'], array('get'=>'iblocks', 'login'=>$_POST['LOGIN'], 'password' => $_POST['PASSWORD']))))
            {
                $errors = array_merge($errors, $export->getErrors());
                break;
            }

            break;
    }
}

$parserList = array();

$parser = new ShsParserContent;
$res = $parser->getListShort();
while($arParser = $res -> fetch())
{
    $parserList['REFERENCE_ID'][] = $arParser['ID'];
    $parserList['REFERENCE'][] = "[$arParser[ID]] $arParser[NAME]";
}

if(isset($_POST['connect']))
    $connect = $_POST['connect'];

if(!empty($_POST['PARSER']) && (isset($data['properties']) || isset($data['prices']) || isset($data['sections'])))
{
    $parser = ShsParserContent::GetByID($_POST['PARSER'])->fetch();
    $parser['SETTINGS'] = unserialize(base64_decode($parser['SETTINGS']));
    $export = new Export();
    $parser_prices = $export->collectPrices($parser['SETTINGS']);
    $parser_sections = $export->collectSections($parser);
    $parser_properties = $export->collectProperties($parser['SETTINGS'], $parser['IBLOCK_ID']);
}

require_once ($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin.php');
IncludeModuleLangFile( __FILE__ );
$id_module ='shs.parser';
$aMenu = array();
$context = new CAdminContextMenu($aMenu);
$context->Show();

$aTabs = array(
    array("DIV" => "edit1", "TAB" => GetMessage("parser_tab_export"), "ICON"=>"main_user_edit", "TITLE"=>GetMessage("parser_tab_export")));

$tabControl = new CAdminForm("tabControl", $aTabs);
$tabControl->Begin( array (
    "FORM_ACTION" => $APPLICATION->GetCurPage()
) );

if(isset($dataRun['ID']))
    CAdminMessage::ShowMessage(array("MESSAGE"=>GetMessage("shs.parser_PARSER_SAVED").' '.$dataRun['ID'], "TYPE"=>"OK"));

if(!empty($errors))
    CAdminMessage::ShowMessage(implode('<br>', $errors));

$tabControl->BeginNextFormTab();

$tabControl->BeginCustomField( "GROUP_PARSER", Loc::getMessage( $id_module . '_GROUP_PARSER' ), false );
?>
<tr class="heading">
    <td colspan="2"><?=Loc::getMessage($id_module . '_GROUP_PARSER') ?></td>
</tr>
<?
$tabControl->EndCustomField( "GROUP_PARSER" );
$tabControl->BeginCustomField( "PARSER", $propertyName, false );
?>
<tr id="tr_PARSER" class="tabcontent">
    <td width="40%"><?=Loc::getMessage($id_module.'_PARSER');?></td>
    </td>
    <td width="60%">
        <?=SelectBoxFromArray ( 'PARSER', $parserList, isset($_POST['PARSER']) ? $_POST['PARSER'] : $_GET['ID'], '', 'style="min-width:320px;"', false, '' )?>
    </td>
</tr>
<?
$tabControl->EndCustomField( "PARSER" );

$tabControl->BeginCustomField( "GROUP_LOGIN", Loc::getMessage( $id_module . '_GROUP_LOGIN' ), false );
?>
<tr class="heading">
    <td colspan="2"><?=Loc::getMessage($id_module . '_GROUP_LOGIN') ?></td>
</tr>
<?
$tabControl->EndCustomField( "GROUP_LOGIN" );
$tabControl->AddEditField( "SITE_API", Loc::getMessage( $id_module . "_SITE_API" ), true, isset($data['iblocks']) ? array('disabled' => "disabled") : false, isset($_POST['SITE_API']) ? $_POST['SITE_API'] : '' );
$tabControl->AddEditField( "LOGIN", Loc::getMessage( $id_module . "_LOGIN" ), true, false, isset($_POST['LOGIN']) ? $_POST['LOGIN'] : '' );
//$tabControl->AddEditField( "PASSWORD", Loc::getMessage( $id_module . "_PASSWORD" ), true, false, isset($_POST['PASSWORD']) ? $_POST['PASSWORD'] : '' );
$tabControl->BeginCustomField( "PASSWORD", Loc::getMessage( $id_module . '_PASSWORD' ), false );
?>
<tr id="tr_PASSWORD" class="tabcontent ChangeValue">
    <td width="40%"><?=Loc::getMessage($id_module.'_PASSWORD');?></td>
    </td>
    <td width="60%">
        <input type="password" name="PASSWORD" value="<?=isset($_POST['PASSWORD']) ? $_POST['PASSWORD'] : '', ''?>" id="PASSWORD">
    </td>
</tr>
<?
$tabControl->EndCustomField( "PASSWORD" );
$tabControl->BeginCustomField( "CONNECT", Loc::getMessage( $id_module . '_CONNECT' ), false );
?>
<tr id="tr_CONNECT" class="tabcontent ChangeValue">
    <td colspan="2" style="text-align: center;"><button class="adm-btn adm-btn-green" name="connection" id="connect"><?=Loc::getMessage( $id_module . '_CONNECT' )?></button> </td>
</tr>
<?
$tabControl->EndCustomField( "CONNECT" );

if(isset($data['iblocks']))
{
    $tabControl->BeginCustomField( "GROUP_IBLOCK", Loc::getMessage( $id_module . '_GROUP_IBLOCK_TYPE' ), false );
    ?>
    <tr class="heading">
        <td colspan="2"><?=Loc::getMessage($id_module . '_GROUP_IBLOCK') ?></td>
    </tr>
    <?
    $tabControl->EndCustomField( "GROUP_IBLOCK" );
    $tabControl->BeginCustomField( "IBLOCK", Loc::getMessage( $id_module . '_IBLOCK' ), false );
    ?>
    <tr id="tr_IBLOCK" class="tabcontent">
        <td width="40%"><?=Loc::getMessage($id_module.'_IBLOCK')?></td>
        </td>
        <td width="60%">
            <?=SelectBoxFromArray ( 'IBLOCK', isset($data['iblocks']) ? (array)$data['iblocks'] : '', isset($_POST['IBLOCK'])? $_POST['IBLOCK'] : '', '', 'style="min-width:320px;"', false, '' )?>
        </td>
    </tr>
    <?
    $tabControl->EndCustomField( "IBLOCK" );
    $tabControl->BeginCustomField( "SELECT_IBLOCK", Loc::getMessage( $id_module . '_SELECT_IBLOCK' ), false );
    ?>
    <tr id="tr_SELECT_IBLOCK" class="tabcontent ChangeValue">
        <td colspan="2" style="text-align: center;"><button class="adm-btn adm-btn-green" name="select_iblock" id="iblock_button"><?=Loc::getMessage( $id_module . '_SELECT_IBLOCK' )?></button> </td>
    </tr>
    <?
    $tabControl->EndCustomField( "SELECT_IBLOCK" );
}

if(isset($data['properties']) || isset($data['prices']) || isset($data['sections']))
{
    if(!empty($parser_prices))
    {
        $tabControl->BeginCustomField("GROUP_PRICES", Loc::getMessage($id_module.'_GROUP_PRICES_TYPE'), false);
        ?>
        <tr class="heading">
            <td colspan="2"><?=Loc::getMessage($id_module.'_GROUP_PRICES')?></td>
        </tr>
        <?
        $tabControl->EndCustomField("GROUP_PRICES");

        foreach($parser_prices as $priceId => $priceName)
        {
            $tabControl->BeginCustomField("PRICE[$priceId]", $priceName, false);
            ?>
            <tr id="tr_PRICE_<?=$priceId?>" class="tabcontent">
                <td width="40%"><?=$priceName;?>:</td>
                </td>
                <td width="60%">
                    <?=SelectBoxFromArray('PRICE['.$priceId.']', (array)$data['prices'], isset($_POST['PRICE'][$priceId])? $_POST['PRICE'][$priceId] : 'ignore', '', 'style="min-width:320px;"', false, '')?>
                </td>
            </tr>
            <?
            $tabControl->EndCustomField("PRICE[$priceId]");
        }
    }

    if(!empty($parser_sections))
    {
        $tabControl->BeginCustomField("GROUP_SECTIONS", Loc::getMessage($id_module.'_GROUP_SECTIONS_TYPE'), false);
        ?>
        <tr class="heading">
            <td colspan="2"><?=Loc::getMessage($id_module.'_GROUP_SECTIONS')?></td>
        </tr>
        <?
        $tabControl->EndCustomField("GROUP_SECTIONS");

        foreach($parser_sections as $sectionId => $sectionName)
        {
            $tabControl->BeginCustomField("SECTION[$sectionId]", $sectionName, false);
            ?>
            <tr id="tr_SECTION_<?=$sectionId?>" class="tabcontent">
                <td width="40%"><?=$sectionName;?>:</td>
                </td>
                <td width="60%">
                    <?=SelectBoxFromArray('SECTION['.$sectionId.']', (array)$data['sections'], isset($_POST['SECTION'][$sectionId])? $_POST['SECTION'][$sectionId] : 'ignore', '', 'style="min-width:320px;"', false, '')?>
                </td>
            </tr>
            <?
            $tabControl->EndCustomField("SECTION[$sectionId]");
        }
    }

    if(!empty($parser_properties))
    {
//        echo '<pre>';
//        print_r($_POST);
//        print_r($data['properties']);
//        exit();

        
        $tabControl->BeginCustomField("GROUP_PROPERTIES", Loc::getMessage($id_module.'_GROUP_PROPERTIES_TYPE'), false);
        ?>
        <tr class="heading">
            <td colspan="2"><?=Loc::getMessage($id_module.'_GROUP_PROPERTIES')?></td>
        </tr>
        <?
        $tabControl->EndCustomField("GROUP_PROPERTIES");

        foreach($parser_properties as $propertyId => $propertyName)
        {
            $tabControl->BeginCustomField("PROPERTY[$propertyId]", $propertyName, false);
            ?>
            <tr id="tr_PORPERTY_<?=$propertyId?>" class="tabcontent">
                <td width="40%"><?=$propertyName;?>:</td>
                </td>
                <td width="60%">
                    <?=SelectBoxFromArray('PROPERTY['.$propertyId.']', (array)$data['properties'], isset($_POST['PROPERTY'][$propertyId])? $_POST['PROPERTY'][$propertyId] : 'ignore', '', 'style="min-width:320px;"', false, '')?>
                </td>
            </tr>
            <?
            $tabControl->EndCustomField("PROPERTY[$propertyId]");
        }
    }
}

$tabControl->Buttons(array(
    "back_url" => $_REQUEST["back_url"],
    "btnApply" => false, // не показывать кнопку применить
    "btnSave" => false,  // не показывать кнопку сохранить
), '<input type="submit" name="export" class="adm-btn adm-btn-green export" value="'.Loc::getMessage($id_module.'_EXPORT_BUTTON_NAME').'">');

$tabControl->Show();

CUtil::InitJSCore(array('ajax', 'ls', 'jquery'));
?>
<script>
    $(document).ready(function(){

        <?if(isset($data['iblocks'])):?>
            $('input[name="SITE_API"], input[name="LOGIN"], input[name="PASSWORD"]').each(function(){
                $(this).closest('form').append('<input type="hidden" name="'+$(this).attr('name')+'" value="'+$(this).val()+'">');
            });
            $('input[type="text"][name="SITE_API"], input[type="text"][name="LOGIN"], input[type="password"][name="PASSWORD"]').attr('disabled', 'disabled');
            $('#connect').hide();
        <?endif;?>

        <?if(isset($data['properties']) || isset($data['prices']) || isset($data['sections'])):?>
            $('#IBLOCK').closest('form').append('<input type="hidden" name="'+$('#IBLOCK').attr('name')+'" value="'+$('#IBLOCK').val()+'">');
            $('#IBLOCK').attr('disabled', 'disabled');
            $('#iblock_button').hide();

            $('#PARSER').closest('form').append('<input type="hidden" name="'+$('#PARSER').attr('name')+'" value="'+$('#PARSER').val()+'">');
            $('#PARSER').attr('disabled', 'disabled');
        <?endif;?>

        $('#connect').click(function(e){
            e.preventDefault();

            $('#tabControl_form').append('<input type="hidden" name="action" value="connection">');
            $('#tabControl_form').submit();

        });

        $('#iblock_button').click(function(e){
            e.preventDefault();
            $('#tabControl_form').append('<input type="hidden" name="action" value="select_iblock">');
            $('#tabControl_form').submit();
        });

        

    });
</script>
<style>
    #tabControl_form .adm-btn.adm-btn-green:active
    {
        height: 29px !important;
    }
</style>
<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");?>

