<?
use Bitrix\Seo\Engine;
use Bitrix\Main\Text\Converter;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\IO\Path;
use \Bitrix\Highloadblock as HL;
use Bitrix\Main\Entity;

\Bitrix\Main\Loader::includeModule('seo');
\Bitrix\Main\Loader::includeModule('socialservices');
global $shs_IBLOCK_ID;
$tabControl->BeginNextTab();
$arType['reference'] = array('html', 'text');
$arType['reference_id'] = array('html', 'text');
$arMode['reference'] = array('debug', 'work');
$arMode['reference_id'] = array('debug', 'work');

$Hlist = HL\HighloadBlockTable::getList(array(
    'select'=>array('ID','NAME'),
));
$arIBlock = array();
while($hl = $Hlist->fetch()){
    $arIBlock['REFERENCE'][] = '['.$hl['ID'].'] '.$hl['NAME'];
    $arIBlock['REFERENCE_ID'][] = $hl['ID'];
}

$arProps = array();
if(isset($shs_IBLOCK_ID) && !empty($shs_IBLOCK_ID)){
    $hlblock = HL\HighloadBlockTable::getById($shs_IBLOCK_ID)->fetch();
    if($hlblock){
        $entity = HL\HighloadBlockTable::compileEntity($hlblock);
        $entity_data_class = $entity->getDataClass();
        $res = $DB->Query('SHOW COLUMNS FROM '.$hlblock['TABLE_NAME']);
        while($column = $res->fetch()){
            if(strpos($column['Field'], 'UF_')===false)
                continue;
            $q = CUserTypeEntity::GetList(array(),array(
                'ENTITY_ID' => 'HLBLOCK_'.$shs_IBLOCK_ID,
                'FIELD_NAME' => $column['Field'],
            ))->fetch();
            $arProps['REFERENCE'][] = str_replace('UF_','',$column['Field']);
            $arProps['REFERENCE_ID'][] = $column['Field'];
        }
    }
}

$arStore = array();
$arrAdditStores = array();
$arAmount = array();
$arAmount['reference']=array(GetMessage('parser_amount_from_file'));
$arAmount['reference_id']=array('from_file');
CModule::IncludeModule("fileman");

$arUpdate['reference'] = array(GetMessage("parser_update_N"), GetMessage("parser_update_Y"), GetMessage("parser_update_empty"));
$arUpdate['reference_id'] = array('N', 'Y', 'empty');

$arAction['reference'] = array(GetMessage("parser_action_N"), GetMessage("parser_action_D"));
$arAction['reference_id'] = array('N', 'D');


$arPriceTerms['reference'] = array(GetMessage("parser_price_terms_no"), GetMessage("parser_price_terms_delta"));
$arPriceTerms['reference_id'] = array('', 'delta');

$arPriceUpDown['reference'] = array(GetMessage("parser_price_updown_no"), GetMessage("parser_price_updown_up"), GetMessage("parser_price_updown_down"));
$arPriceUpDown['reference_id'] = array('', 'up', 'down');

$arPriceValue['reference'] = array(GetMessage("parser_price_percent"), GetMessage("parser_price_abs_value"));
$arPriceValue['reference_id'] = array('percent', 'value');

$arAuthType['reference'] = array(GetMessage("parser_auth_type_form"), GetMessage("parser_auth_type_http"));
$arAuthType['reference_id'] = array('form', 'http');

$arDopUrl["reference"][] = GetMessage("parser_section_all");
$arDopUrl["reference_id"][] = "section_all";

$hideCatalog = true;

$arrActionProps['REFERENCE'] = array(GetMessage("parser_action_props_delete"), GetMessage("parser_action_props_add_begin"), GetMessage("parser_action_props_add_end"), GetMessage("parser_action_props_to_lower"));
$arrActionProps['REFERENCE_ID'] = array("delete", "add_b", "add_e", "lower");


$disabled = false;
$disabledType = false;
$arrDate = ParseDateTime($shs_START_LAST_TIME_X, "YYYY.MM.DD HH:MI:SS");
if($shs_TYPE)$disabled  = 'disabled=""';
if($shs_TYPE_OUT)$disabledType  = 'disabled=""';
?>
    <tr>
        <td><?echo GetMessage("parser_type")?></td>
        <td><?=SelectBoxFromArray('TYPE', $arTypeParser, $shs_TYPE?$shs_TYPE:$_GET["type"], "", $disabled);?>
        <?if($disabled):?><input type="hidden" name="TYPE" value="<?=$shs_TYPE?>" /><?endif;?>
        </td>
    </tr>
    <tr>
        <td><?echo GetMessage("parser_type_out")?></td>
        <td><?=SelectBoxFromArray('TYPE_OUT', $arTypeOut, $shs_TYPE_OUT?$shs_TYPE_OUT:$_GET["type_out"], "", $disabledType);?>
        <?if($disabledType):?><input type="hidden" name="TYPE_OUT" value="<?=$shs_TYPE_OUT?>" /><?endif;?>
        </td>
    </tr>
    <tr>
        <td><?echo GetMessage("parser_mode")?></td>
        <td><?=SelectBoxFromArray('SETTINGS[catalog][mode]', $arMode, $shs_SETTINGS["catalog"]["mode"]?$shs_SETTINGS["catalog"]["mode"]:"debug", "", "");?></td>
    </tr>
    <tr>
        <td></td>
        <td>
            <?=BeginNote();?>
            <?echo GetMessage("parser_mode_descr")?>
            <?=EndNote();?>
        </td>
    </tr>
    <tr>
        <td width="40%"><?echo GetMessage("parser_act")?></td>
        <td width="60%"><input type="checkbox" name="ACTIVE" value="Y"<?if($shs_ACTIVE == "Y" || !$ID) echo " checked"?>>
        </td>
    </tr>
    <tr>
        <td><?echo GetMessage("parser_sort")?></td>
        <td><input type="text" name="SORT" value="<?echo !$ID?"100":$shs_SORT;?>" size="4"></td>
    </tr>
    <?if(isset($arCategory) && !empty($arCategory)):?>
    <tr>
        <td><?echo GetMessage("parser_category_title")?></td>
        <td><?=SelectBoxFromArray('CATEGORY_ID', $arCategory, isset($shs_CATEGORY_ID)?$shs_CATEGORY_ID:$parentID, GetMessage("parser_category_select"), "id='category' style='width:262px'");?></td>
    </tr>
    <?endif;?>
    <tr>
        <td><span class="required">*</span><?echo GetMessage("parser_name")?></td>
        <td><input type="text" name="NAME" value="<?echo $shs_NAME;?>" size="40" maxlength="250"></td>
    </tr>
    <tr>
        <td><span class="required">*</span><?echo GetMessage("parser_iblock_id_catalog")?></td>
        <td><?=SelectBoxFromArray('IBLOCK_ID', $arIBlock, $shs_IBLOCK_ID, GetMessage("parser_iblock_id"), "id='iblock' style='width:262px' ");?>
        </td>
    </tr>
    <tr>
        <td><span class="required">*</span><?echo GetMessage("parser_rss_catalog")?></td>
        <td><input type="text" name="RSS" value="<?echo $shs_RSS;?>" size="80" maxlength="500"></td>
    </tr>
    <tr>
        <td style="vertical-align:top"><?echo GetMessage("parser_url_dop")?></td>
        <td>
            <textarea name="SETTINGS[catalog][url_dop]" cols="65" rows="5"><?=$shs_SETTINGS["catalog"]["url_dop"]?></textarea>
        </td>
    </tr>
    <tr>
        <td></td>
        <td>
            <?=BeginNote();?>
            <?echo GetMessage("parser_url_dop_descr")?>
            <?=EndNote();?>
        </td>
    </tr>
    <tr>
        <td><?echo GetMessage("parser_encoding")?></td>
        <td><?=SelectBoxFromArray('ENCODING', $arEncoding, $shs_ENCODING);?></td>
    </tr>
    <tr>
        <td></td>
        <td>
            <?=BeginNote();?>
            <?echo GetMessage("parser_encoding_descr")?>
            <?=EndNote();?>
        </td>
    </tr>
    <tr>
        <td><?echo GetMessage("parser_step")?></td>
        <td><input type="text" name="SETTINGS[catalog][step]" value="<?echo $shs_SETTINGS["catalog"]["step"]?$shs_SETTINGS["catalog"]["step"]:30;?>" size="40" maxlength="250"></td>
    </tr>
    <tr>
        <td><?echo GetMessage("parser_start_last_time")?></td>
        <td><input type="text" disabled name="START_LAST_TIME_X" value="<?echo $arrDate[DD].'.'.$arrDate[MM].'.'.$arrDate[YYYY].' '.$arrDate[HH].':'.$arrDate[MI].':'.$arrDate[SS];?>" size="20"></td>
    </tr>
<?
//********************
//Auto params
//********************
$tabControl->BeginNextTab();
?>
    <tr class="heading">
        <td colspan="2"><?echo GetMessage("parser_standart_pagenavigation")?></td>
    </tr>
    <tr>
        <td class="field-name" width="40%"><?echo GetMessage("parser_pagenavigation_selector")?></td>
        <td width="60%"><input type="text" name="SETTINGS[catalog][pagenavigation_selector]" value="<?echo $shs_SETTINGS["catalog"]["pagenavigation_selector"];?>" size="40" maxlength="250"></td>
    </tr>
    <tr>
        <td></td>
        <td>
            <?=BeginNote();?>
            <?echo GetMessage("parser_pagenavigation_selector_descr")?>
            <?=EndNote();?>
        </td>
    </tr>
    <tr>
        <td><?echo GetMessage("parser_pagenavigation_one")?></td>
        <td><input type="text" name="SETTINGS[catalog][pagenavigation_one]" value="<?echo $shs_SETTINGS["catalog"]["pagenavigation_one"];?>" size="40" maxlength="250"></td>
    </tr>
    <tr>
        <td></td>
        <td>
            <?=BeginNote();?>
            <?echo GetMessage("parser_pagenavigation_one_descr")?>
            <?=EndNote();?>
        </td>
    </tr>
    <tr>
        <td><?echo GetMessage("parser_pagenavigation_delete")?></td>
        <td><input type="text" name="SETTINGS[catalog][pagenavigation_delete]" value="<?echo $shs_SETTINGS["catalog"]["pagenavigation_delete"];?>" size="40" maxlength="250"></td>
    </tr>
    <tr>
        <td><?echo GetMessage("parser_pagenavigation_begin")?></td>
        <td><input type="text" name="SETTINGS[catalog][pagenavigation_begin]" value="<?echo $shs_SETTINGS["catalog"]["pagenavigation_begin"];?>" size="5" maxlength="5"> <?echo GetMessage("parser_pagenavigation_end")?> <input type="text" name="SETTINGS[catalog][pagenavigation_end]" value="<?echo $shs_SETTINGS["catalog"]["pagenavigation_end"];?>" size="5" maxlength="5"></td>
    </tr>
    <tr>
        <td></td>
        <td>
            <?=BeginNote();?>
            <?echo GetMessage("parser_pagenavigation_begin_descr")?>
            <?=EndNote();?>
        </td>
    </tr>
    <tr class="heading">
        <td colspan="2"><?echo GetMessage("parser_work_pagenavigation")?></td>
    </tr>
    <tr>
        <td></td>
        <td>
            <?=BeginNote();?>
            <?echo GetMessage("parser_work_pagenavigation_descr")?>
            <?=EndNote();?>
        </td>
    </tr>
    <tr>
        <td><?echo GetMessage("parser_work_pagenavigation_var")?>:</td>
        <td><input type="text" name="SETTINGS[catalog][pagenavigation_var]" value="<?echo $shs_SETTINGS["catalog"]["pagenavigation_var"];?>" size="10" maxlength="250"></td>
    </tr>
    <tr>
        <td></td>
        <td>
            <?=BeginNote();?>
            <?echo GetMessage("parser_work_pagenavigation_var_descr")?>
            <?=EndNote();?>
        </td>
    </tr>
    <tr>
        <td><?echo GetMessage("parser_work_pagenavigation_var_step")?>:</td>
        <td><input type="text" name="SETTINGS[catalog][pagenavigation_var_step]" value="<?echo $shs_SETTINGS["catalog"]["pagenavigation_var_step"];?>" size="10" maxlength="250"></td>
    </tr>
    <tr>
        <td></td>
        <td>
            <?=BeginNote();?>
            <?echo GetMessage("parser_work_pagenavigation_var_step_descr")?>
            <?=EndNote();?>
        </td>
    </tr>
    <tr>
        <td><?echo GetMessage("parser_work_pagenavigation_other_var")?>:</td>
        <td><input type="text" name="SETTINGS[catalog][pagenavigation_other_var]" value="<?echo $shs_SETTINGS["catalog"]["pagenavigation_other_var"];?>" size="40" maxlength="250"></td>
    </tr>
    <tr>
        <td></td>
        <td>
            <?=BeginNote();?>
            <?echo GetMessage("parser_work_pagenavigation_other_var_descr")?>
            <?=EndNote();?>
        </td>
    </tr>
    <tr>
        <td><?echo GetMessage("parser_work_pagenavigation_page_count")?>:</td>
        <td><input type="text" name="SETTINGS[catalog][pagenavigation_page_count]" value="<?echo $shs_SETTINGS["catalog"]["pagenavigation_page_count"];?>" size="10" maxlength="250"></td>
    </tr>
    <tr>
        <td></td>
        <td>
            <?=BeginNote();?>
            <?echo GetMessage("parser_work_pagenavigation_page_count_descr")?>
            <?=EndNote();?>
        </td>
    </tr>
    
<?
$tabControl->BeginNextTab();
?>  <tr>
        <td class="field-name" width="40%"><?echo GetMessage("parser_selector_preview_catalog")?></td>
        <td width="60%"><input type="text" name="SETTINGS[catalog][selector]" value="<?echo $shs_SETTINGS["catalog"]["selector"];?>" size="40" maxlength="250"></td>
    </tr>
    <tr>
        <td></td>
        <td>
            <?=BeginNote();?>
            <?echo GetMessage("parser_selector_catalog_descr")?>
            <?=EndNote();?>
        </td>
    </tr>
    <tr>
        <td><?echo GetMessage("parser_href_catalog")?></td>
        <td><input type="text" name="SETTINGS[catalog][href]" value="<?echo $shs_SETTINGS["catalog"]["href"];?>" size="40" maxlength="250"></td>
    </tr>
    <tr>
        <td></td>
        <td>
            <?=BeginNote();?>
            <?echo GetMessage("parser_href_descr_catalog")?>
            <?=EndNote();?>
        </td>
    </tr>
    <tr class="heading">
        <td colspan="2"><?echo GetMessage("parser_hl_props")?></td>
    </tr>
    <tr>
        <td class="field-name" width="40%"><?echo GetMessage("parser_catalog_delete_symb")?></td>
        <td width="60%"><input type="text" name="SETTINGS[catalog][catalog_delete_selector_props_symb_preview]" value="<?echo $shs_SETTINGS["catalog"]["catalog_delete_selector_props_symb_preview"];?>" size="40" maxlength="250"></td>
    </tr>
    <tr>
        <td class="field-name" width="40%"></td>
        <td width="60%">
            <?=BeginNote();?>
            <?echo GetMessage("parser_delete_symb_descr")?>
            <?=EndNote();?>
        </td>
    </tr>
    <?php
    if(isset($shs_SETTINGS['properties']['preview']) && !empty($shs_SETTINGS['properties']['preview'])){
        foreach($shs_SETTINGS['properties']['preview'] as $id => $props){
        ?>
        <tr class="adittional_preview_props_id_<?php echo $id;?>">
            <td><?php echo GetMessage('parser_field').' '.$props['name'].'['.$id.']:';?></td>
            <td>
                <input type="text" name="SETTINGS[properties][preview][<?php echo $id;?>][value]" value="<?echo $props["value"];?>" size="40" maxlength="250">&nbsp;<a href="#" class="prev_prop_delete" data-prop-id="<?php echo $id;?>"><?php echo GetMessage('parser_delete');?></a>
                <input type="hidden" name="SETTINGS[properties][preview][<?php echo $id;?>][name]" value="<?echo $props["name"];?>">
            </td>
        </tr>
        <?php
        }
    }
    ?>
    <tr>
        <td colspan="2" align="center">
            <?=SelectBoxFromArray('arrProps', $arProps, "", GetMessage("shs_parser_select_prop"), "");?>
            <input type="submit" id="addPrevProp" name="refresh" value="<?=GetMessage("parser_add")?>">
        </td>
    </tr>
    <tr>
        <td></td>
        <td>
            <?=BeginNote();?>
            <?echo GetMessage("parser_prev_prop_descr")?>
            <?=EndNote();?>
        </td>
    </tr>
    <tr class="heading">
        <td colspan="2"></td>
    </tr>
    <tr>
        <td width="40%"><?echo GetMessage("parser_preview_delete_element")?></td>
        <td width="60%"><input size="40" maxlength="300" type="text" name="PREVIEW_DELETE_ELEMENT" value="<?=$shs_PREVIEW_DELETE_ELEMENT?>"></td>
    </tr>
    <tr>
        <td></td>
        <td>
            <?=BeginNote();?>
            <?echo GetMessage("parser_preview_delete_element_descr")?>
            <?=EndNote();?>
        </td>
    </tr>
    <tr>
        <td width="40%"><?echo GetMessage("parser_preview_delete_attribute")?></td>
        <td width="60%"><input size="40" maxlength="300" type="text" name="PREVIEW_DELETE_ATTRIBUTE" value="<?=$shs_PREVIEW_DELETE_ATTRIBUTE?>"></td>
    </tr>
    <tr>
        <td></td>
        <td>
            <?=BeginNote();?>
            <?echo GetMessage("parser_preview_delete_attribute_descr")?>
            <?=EndNote();?>
        </td>
    </tr>
<?
$tabControl->BeginNextTab();
?>
    <tr>
        <td class="field-name" width="40%"><?echo GetMessage("parser_selector_detail_catalog")?></td>
        <td width="60%"><input type="text" name="SELECTOR" value="<?echo $shs_SELECTOR;?>" size="40" maxlength="250"></td>
    </tr>

    <tr>
        <td></td>
        <td>
            <?=BeginNote();?>
            <?echo GetMessage("parser_selector_detail_catalog_descr")?>
            <?=EndNote();?>
        </td>
    </tr>
    <tr class="heading">
        <td colspan="2"><?echo GetMessage("parser_hl_props")?></td>
    </tr>
    <tr>
        <td class="field-name" width="40%"><?echo GetMessage("parser_catalog_delete_symb")?></td>
        <td width="60%"><input type="text" name="SETTINGS[catalog][catalog_delete_selector_props_symb]" value="<?echo $shs_SETTINGS["catalog"]["catalog_delete_selector_props_symb"];?>" size="40" maxlength="250"></td>
    </tr>
    <tr>
        <td class="field-name" width="40%"></td>
        <td width="60%">
            <?=BeginNote();?>
            <?echo GetMessage("parser_delete_symb_descr")?>
            <?=EndNote();?>
        </td>
    </tr>
    <?php
    if(isset($shs_SETTINGS['properties']['detail']) && !empty($shs_SETTINGS['properties']['detail'])){
        foreach($shs_SETTINGS['properties']['detail'] as $id => $props){
        ?>
        <tr class="adittional_detail_props_id_<?php echo $id;?>">
            <td><?php echo GetMessage('parser_field').' '.$props['name'].'['.$id.']:';?></td>
            <td>
                <input type="text" name="SETTINGS[properties][detail][<?php echo $id;?>][value]" value="<?echo $props["value"];?>" size="40" maxlength="250">&nbsp;<a href="#" class="prev_prop_delete" data-prop-id="<?php echo $id;?>"><?php echo GetMessage('parser_delete');?></a>
                <input type="hidden" name="SETTINGS[properties][detail][<?php echo $id;?>][name]" value="<?echo $props["name"];?>">
            </td>
        </tr>
        <?php
        }
    }
    ?>
    <tr>
        <td colspan="2" align="center">
            <?=SelectBoxFromArray('arrPropsDetail', $arProps, "", GetMessage("shs_parser_select_prop"), "");?>
            <input type="submit" id="addDetailProp" name="refresh" value="<?=GetMessage("parser_add")?>">
        </td>
    </tr>
    <tr>
        <td></td>
        <td>
            <?=BeginNote();?>
            <?echo GetMessage("parser_prev_prop_descr")?>
            <?=EndNote();?>
        </td>
    </tr>
    <tr class="heading">
        <td colspan="2"></td>
    </tr>
    <tr>
        <td width="40%"><?echo GetMessage("parser_detail_delete_element")?></td>
        <td width="60%"><input size="40" maxlength="300" type="text" name="DETAIL_DELETE_ELEMENT" value="<?=$shs_DETAIL_DELETE_ELEMENT?>"></td>
    </tr>
    <tr>
        <td></td>
        <td>
            <?=BeginNote();?>
            <?echo GetMessage("parser_detail_delete_element_descr")?>
            <?=EndNote();?>
        </td>
    </tr>
    <tr>
        <td width="40%"><?echo GetMessage("parser_detail_delete_attribute")?></td>
        <td width="60%"><input size="40" maxlength="300" type="text" name="DETAIL_DELETE_ATTRIBUTE" value="<?=$shs_DETAIL_DELETE_ATTRIBUTE?>"></td>
    </tr>
    <tr>
        <td></td>
        <td>
            <?=BeginNote();?>
            <?echo GetMessage("parser_detail_delete_attribute_descr")?>
            <?=EndNote();?>
        </td>
    </tr>

<?
$tabControl->BeginNextTab();
?>
    <tr class="heading" id="header_selector_prop">
        <td colspan="2"><?echo GetMessage("parser_default_fields")?></td>
    </tr>
    <?php
    if(isset($shs_SETTINGS['properties']['default']) && !empty($shs_SETTINGS['properties']['default'])){
        foreach($shs_SETTINGS['properties']['default'] as $id => $props){
        ?>
        <tr class="adittional_detail_props_id_<?php echo $id;?>">
            <td><?php echo GetMessage('parser_field').' '.$props['name'].'['.$id.']:';?></td>
            <td>
                <input type="text" name="SETTINGS[properties][default][<?php echo $id;?>][value]" value="<?echo $props["value"];?>" size="40" maxlength="250">&nbsp;<a href="#" class="prev_prop_delete" data-prop-id="<?php echo $id;?>"><?php echo GetMessage('parser_delete');?></a>
                <input type="hidden" name="SETTINGS[properties][default][<?php echo $id;?>][name]" value="<?echo $props["name"];?>">
            </td>
        </tr>
        <?php
        }
    }
    ?>
    <tr>
        <td colspan="2" align="center">
            <?=SelectBoxFromArray('arrPropDefault', $arProps, "", GetMessage("shs_parser_select_prop"), "");?>
            <input type="submit" id="loadPropDefaultHL" name="refresh" value="<?=GetMessage("shs_parser_select_prop_but")?>">
        </td>
    </tr>
    
    <tr class="heading">
        <td colspan="2"><?echo GetMessage("parser_filter_props")?></td>
    </tr>
    
    <tr>
        <td width="40%"><?echo GetMessage("parser_props_filter")?></td>
        <td width="60%"><input type="checkbox" name="SETTINGS[catalog][enable_props_filter]" value="Y"<?if($shs_SETTINGS["catalog"]["enable_props_filter"] == "Y") echo " checked"?>>
        </td>
    </tr>
    <?
    if(isset($shs_SETTINGS["props_filter_value"]) && count($shs_SETTINGS["props_filter_value"]) > 0)
    {
        foreach($shs_SETTINGS["props_filter_value"] as $id => $propsfilter)
        {
            foreach($propsfilter as $code => $val)
            {
            if(empty($code) || empty($val)) continue 1;
    ?>
        <tr>
            <td width="40%"><?=$code?>&nbsp;[<?=$code?>]</td>
            <td width="60%">
                <?=SelectBoxFromArray("SETTINGS[props_filter_circs][$id][$code]", $arrFilterCircs, $shs_SETTINGS["props_filter_circs"][$id][$code]?$shs_SETTINGS["props_filter_circs"][$id][$code]:"equally", "", "");?>
                <input type="text" size="40" data-code="<?=$code?>" name="SETTINGS[props_filter_value][<?=$id?>][<?=$code?>]" value="<?=$val?>">
                <a href="#" class="prop_delete">Delete</a>
            </td>
        </tr>
    <?
            }
         }
    }?>
    <tr>
        <td colspan="2" align="center">
            <?=SelectBoxFromArray('arrPropsFilter', $arProps, "", GetMessage("shs_parser_select_prop"), "");?>
            <input type="submit" id="loadFilterProps" name="refresh" value="<?=GetMessage("shs_parser_select_prop_but")?>">
        </td>
    </tr>
    <tr>
        <td class="field-name" width="40%"></td>
        <td width="60%">
            <?=BeginNote();?>
            <?echo GetMessage("parser_filter_props_descr")?>
            <?=EndNote();?>
        </td>
    </tr>
    
    <tr class="heading" id="header_find_prop">
        <td colspan="2"><?echo GetMessage("parser_find_props")?></td>
    </tr>
    <tr class="tr_find_prop">
        <td class="field-name" width="40%"><?echo GetMessage("parser_selector_find_props")?></td>
        <td width="60%"><input type="text" name="SETTINGS[catalog][selector_find_props]" value="<?echo $shs_SETTINGS["catalog"]["selector_find_props"];?>" size="40" maxlength="250"></td>
    </tr>
    <tr class="tr_find_prop">
        <td class="field-name" width="40%"></td>
        <td width="60%">
            <?=BeginNote();?>
            <?echo GetMessage("parser_selector_find_descr")?>
            <?=EndNote();?>
        </td>
    </tr>
    <tr class="tr_find_prop">
        <td class="field-name" width="40%"><?echo GetMessage("parser_catalog_delete_symb")?></td>
        <td width="60%"><input type="text" name="SETTINGS[catalog][catalog_delete_selector_find_props_symb]" value="<?echo $shs_SETTINGS["catalog"]["catalog_delete_selector_find_props_symb"];?>" size="40" maxlength="250"></td>
    </tr>
    <tr class="tr_find_prop">
        <td class="field-name" width="40%"></td>
        <td width="60%">
            <?=BeginNote();?>
            <?echo GetMessage("parser_delete_symb_descr")?>
            <?=EndNote();?>
        </td>
    </tr>
    <?php
    
    if(isset($shs_SETTINGS["catalog"]["find_prop"]) && !empty($shs_SETTINGS["catalog"]["find_prop"])){
        foreach($shs_SETTINGS["catalog"]["find_prop"] as $id => $props){
        ?>
        <tr class="dop_props1_id_<?php echo $id;?>">
            <td><?php echo GetMessage('parser_field').' '.$id.':';?></td>
            <td>
                <input type="text" name="SETTINGS[catalog][find_prop][<?php echo $id;?>]" value="<?echo $props;?>" size="40" maxlength="250">&nbsp;<a href="#" class="prev_prop_delete" data-prop-id="<?php echo $id;?>"><?php echo GetMessage('parser_delete');?></a>
            </td>
        </tr>
        <?php
        }
    }
    ?>
    <tr>
        <td colspan="2" align="center">
            <?=SelectBoxFromArray('arrPropDop1', $arProps, "", GetMessage("shs_parser_select_prop"), "");?>
            <input type="submit" id="loadDopPropHL1" name="refresh" value="<?=GetMessage("shs_parser_select_prop_but")?>">
        </td>
    </tr>
    
    <tr style="display:none">
        <td colspan="2"><input type="hidden" id="delete_find_prop" name="SETTINGS[catalog][delete_find_prop]" value="<?=$shs_SETTINGS["catalog"]["delete_find_prop"]?>" /></td>
    </tr>
    <tr class="tr_find_prop">
        <td class="field-name" width="40%"></td>
        <td width="60%">
            <?=BeginNote();?>
            <?echo GetMessage("parser_prop_detail_preview_descr")?><?echo GetMessage("parser_prop_detail_preview_descr_file")?>
            <?=EndNote();?>
        </td>
    </tr>
    <?php
      /*
    <tr class="heading" id="header_selector_prop">
        <td colspan="2"><?echo GetMessage("parser_selector_props_preview")?></td>
    </tr>
    <tr>
        <td class="field-name" width="40%"><?echo GetMessage("parser_catalog_delete_symb")?></td>
        <td width="60%"><input type="text" name="SETTINGS[catalog][catalog_delete_selector_props_symb_preview]" value="<?echo $shs_SETTINGS["catalog"]["catalog_delete_selector_props_symb_preview"];?>" size="40" maxlength="250"></td>
    </tr>
    <tr>
        <td class="field-name" width="40%"></td>
        <td width="60%">
            <?=BeginNote();?>
            <?echo GetMessage("parser_delete_symb_descr")?>
            <?=EndNote();?>
        </td>
    </tr>
    <?php
    if(isset($shs_SETTINGS["catalog"]["selector_prop_preview"]) && !empty($shs_SETTINGS["catalog"]["selector_prop_preview"])){
        foreach($shs_SETTINGS["catalog"]["selector_prop_preview"] as $id => $props){
        ?>
        <tr class="dop_props2_id_<?php echo $id;?>">
            <td><?php echo GetMessage('parser_field').' '.$id.':';?></td>
            <td>
                <input type="text" name="SETTINGS[catalog][selector_prop_preview][<?php echo $id;?>]" value="<?echo $props;?>" size="40" maxlength="250">&nbsp;<a href="#" class="prev_prop_delete" data-prop-id="<?php echo $id;?>"><?php echo GetMessage('parser_delete');?></a>
            </td>
        </tr>
        <?php
        }
    }
    ?>
    <tr>
        <td colspan="2" align="center">
            <?=SelectBoxFromArray('arrPropDop2', $arProps, "", GetMessage("shs_parser_select_prop"), "");?>
            <input type="submit" id="loadDopPropHL2" name="refresh" value="<?=GetMessage("shs_parser_select_prop_but")?>">
        </td>
    </tr>
    
    <tr class="tr_find_prop">
        <td class="field-name" width="40%"></td>
        <td width="60%">
            <?=BeginNote();?>
            <?echo GetMessage("parser_prop_detail_preview_descr")?>
            <?=EndNote();?>
        </td>
    </tr>
    */
    ?>
    <tr class="heading" id="header_find_prop">
        <td colspan="2"><?echo GetMessage("parser_find_props_preview")?></td>
    </tr>
    <tr class="tr_find_prop">
        <td class="field-name" width="40%"><?echo GetMessage("parser_selector_find_props")?></td>
        <td width="60%"><input type="text" name="SETTINGS[catalog][selector_find_props_preview]" value="<?echo $shs_SETTINGS["catalog"]["selector_find_props_preview"];?>" size="40" maxlength="250"></td>
    </tr>
    <tr class="tr_find_prop">
        <td class="field-name" width="40%"></td>
        <td width="60%">
            <?=BeginNote();?>
            <?echo GetMessage("parser_selector_find_descr")?>
            <?=EndNote();?>
        </td>
    </tr>
    <tr class="tr_find_prop">
        <td class="field-name" width="40%"><?echo GetMessage("parser_catalog_delete_symb")?></td>
        <td width="60%"><input type="text" name="SETTINGS[catalog][catalog_delete_selector_find_props_symb_preview]" value="<?echo $shs_SETTINGS["catalog"]["catalog_delete_selector_find_props_symb_preview"];?>" size="40" maxlength="250"></td>
    </tr>
    <tr class="tr_find_prop">
        <td class="field-name" width="40%"></td>
        <td width="60%">
            <?=BeginNote();?>
            <?echo GetMessage("parser_delete_symb_descr")?>
            <?=EndNote();?>
        </td>
    </tr>
    <?php
    
    if(isset($shs_SETTINGS["catalog"]["find_prop_preview"]) && !empty($shs_SETTINGS["catalog"]["find_prop_preview"])){
        foreach($shs_SETTINGS["catalog"]["find_prop_preview"] as $id => $props){
        ?>
        <tr class="dop_props3_id_<?php echo $id;?>">
            <td><?php echo GetMessage('parser_field').' '.$id.':';?></td>
            <td>
                <input type="text" name="SETTINGS[catalog][find_prop_preview][<?php echo $id;?>]" value="<?echo $props;?>" size="40" maxlength="250">&nbsp;<a href="#" class="prev_prop_delete" data-prop-id="<?php echo $id;?>"><?php echo GetMessage('parser_delete');?></a>
            </td>
        </tr>
        <?php
        }
    }
    ?>
    <tr>
        <td colspan="2" align="center">
            <?=SelectBoxFromArray('arrPropDop3', $arProps, "", GetMessage("shs_parser_select_prop"), "");?>
            <input type="submit" id="loadDopPropHL3" name="refresh" value="<?=GetMessage("shs_parser_select_prop_but")?>">
        </td>
    </tr>
    
    <tr class="tr_find_prop">
        <td class="field-name" width="40%"></td>
        <td width="60%">
            <?=BeginNote();?>
            <?echo GetMessage("parser_prop_detail_preview_descr")?>
            <?=EndNote();?>
        </td>
    </tr>
    <tr class="heading" id="header_find_prop">
        <td colspan="2"><?echo GetMessage("parser_add_delete_symb_props")?></td>
    </tr>
    <?php
    
    if(isset($shs_SETTINGS["catalog"]["action_props_val"]) && !empty($shs_SETTINGS["catalog"]["action_props_val"])){
        foreach($shs_SETTINGS["catalog"]["action_props_val"] as $id => $arVal){
            foreach($arVal as $i=>$val){
                $val = trim($val);
                if(!$val) continue 1;
                ?>
                <tr>
                    <td width="40%"><?php echo GetMessage('parser_field').' '.$id.':';?></td>
                    <td width="60%"><input type="text" size="40" name="SETTINGS[catalog][action_props_val][<?php echo $id; ?>][]" value="<?=$val?>">&nbsp; <?=SelectBoxFromArray('SETTINGS[catalog][action_props]['.$id.']['.$i.']', $arrActionProps, $shs_SETTINGS["catalog"]["action_props"][$id][$i], GetMessage("shs_parser_select_action_props"), "");?> <a class="find_delete" href="#"><?php echo GetMessage('parser_delete');?></a></td>
                </tr>
                <?php
            }
        }
    }
    ?>
    <tr>
        <td colspan="2" align="center">
            <?=SelectBoxFromArray('arrPropField', $arProps, "", GetMessage("shs_parser_select_prop"), "");?>
            <input type="submit" id="loadPropField" name="refresh" value="<?=GetMessage("shs_parser_select_prop_but")?>">
        </td>
    </tr>
    <tr class="tr_find_prop">
        <td class="field-name" width="40%"></td>
        <td width="60%">
            <?=BeginNote();?>
            <?echo GetMessage("shs_parser_action_props_descr")?>
            <?=EndNote();?>
        </td>
    </tr>
<?
//��� ���������
$tabControl->BeginNextTab();
?>
<!--
    <tr>
        <td width="40%"><?echo GetMessage("parser_active_element")?></td>
        <td width="60%"><input type="checkbox" name="ACTIVE_ELEMENT" value="Y"<?if($shs_ACTIVE_ELEMENT == "Y") echo " checked"?>></td>
    </tr>
    <tr>
        <td width="40%"><?echo GetMessage("parser_code_element")?></td>
        <td width="60%"><input type="checkbox" name="CODE_ELEMENT" value="Y"<?if($shs_CODE_ELEMENT == "Y") echo " checked"?>></td>
    </tr>
    <tr>
        <td width="40%"><?echo GetMessage("parser_index_element")?></td>
        <td width="60%"><input type="checkbox" name="INDEX_ELEMENT" value="Y"<?if($shs_INDEX_ELEMENT == "Y") echo " checked"?>></td>
    </tr>
    <tr>
        <td width="40%"><?echo GetMessage("parser_resize_image")?></td>
        <td width="60%"><input type="checkbox" name="RESIZE_IMAGE" value="Y"<?if($shs_RESIZE_IMAGE == "Y") echo " checked"?>></td>
    </tr>
    <tr>
        <td width="40%"><?echo GetMessage("parser_preview_from_detail")?></td>
        <td width="60%"><input type="checkbox" name="SETTINGS[catalog][img_preview_from_detail]" value="Y"<?if($shs_SETTINGS["catalog"]["img_preview_from_detail"] == "Y") echo " checked"?>></td>
    </tr>
    <tr>
        <td width="40%"><?echo GetMessage("parser_preview_from_detail_text")?></td>
        <td width="60%"><input type="checkbox" name="SETTINGS[catalog][text_preview_from_detail]" value="Y"<?if($shs_SETTINGS["catalog"]["text_preview_from_detail"] == "Y") echo " checked"?>></td>
    </tr>
-->
    <tr>
        <td width="40%"><?echo GetMessage("parser_404_error")?></td>
        <td width="60%"><input type="checkbox" name="SETTINGS[catalog][404]" value="Y"<?if($shs_SETTINGS["catalog"]["404"] == "Y") echo " checked"?>></td>
    </tr>
    <tr>
        <td></td>
        <td>
            <?=BeginNote();?>
            <?echo GetMessage("parser_404_descr")?>
            <?=EndNote();?>
        </td>
    </tr>
<!--
    <tr>
        <td width="40%"><?echo GetMessage("parser_date_active")?></td>
        <td width="60%"><input type="checkbox" name="DATE_ACTIVE" value="Y"<?if($shs_DATE_ACTIVE && $shs_DATE_ACTIVE != "N") echo " checked"?>> <?=SelectBoxFromArray('DATE_PROP_ACTIVE', $arrDateActive, $shs_DATE_ACTIVE, GetMessage("parser_date_type"), "id='prop-active' style='width:262px'");?></td>
    </tr>
-->
    <?/*?><tr>
        <td width="40%"><?echo GetMessage("parser_date_public")?></td>
        <td width="60%"><input type="checkbox" name="DATE_PUBLIC" value="Y"<?if($shs_DATE_PUBLIC && $shs_DATE_PUBLIC != "N") echo " checked"?>> <?=SelectBoxFromArray('DATE_PROP_PUBLIC', $arrProp, $shs_DATE_PUBLIC, GetMessage("parser_prop_id"), "id='prop-date' style='width:262px' class='prop-iblock'");?></td>
    </tr><?*/?>
    <tr>
        <td width="40%"><?echo GetMessage("parser_first_title")?></td>
        <td width="60%"><input type="checkbox" name="FIRST_TITLE" value="Y"<?if($shs_FIRST_TITLE && $shs_FIRST_TITLE != "N") echo " checked"?>> <?=SelectBoxFromArray('FIRST_PROP_TITLE', $arProps, $shs_FIRST_TITLE, GetMessage("parser_prop_id"), "id='prop-first' style='width:262px' class='prop-iblock'");?></td>
    </tr>
    <tr>
        <td width="40%"><?echo GetMessage("parser_meta_title")?></td>
        <td width="60%"><input type="checkbox" name="META_TITLE" value="Y"<?if($shs_META_TITLE && $shs_META_TITLE != "N") echo " checked"?>> <?=SelectBoxFromArray('META_PROP_TITLE', $arProps, $shs_META_TITLE, GetMessage("parser_prop_id"), "id='prop-title' style='width:262px' class='prop-iblock'");?></td>
    </tr>
    <tr>
        <td width="40%"><?echo GetMessage("parser_meta_description")?></td>
        <td width="60%"><input type="checkbox" name="META_DESCRIPTION" value="Y"<?if($shs_META_DESCRIPTION && $shs_META_DESCRIPTION != "N") echo " checked"?>> <?=SelectBoxFromArray('META_PROP_DESCRIPTION', $arProps, $shs_META_DESCRIPTION, GetMessage("parser_prop_id"), "id='prop-key' style='width:262px' class='prop-iblock'");?></td>
    </tr>
    <tr>
        <td width="40%"><?echo GetMessage("parser_meta_keywords")?></td>
        <td width="60%"><input type="checkbox" name="META_KEYWORDS" value="Y"<?if($shs_META_KEYWORDS && $shs_META_KEYWORDS != "N") echo " checked"?>> <?=SelectBoxFromArray('META_PROP_KEYWORDS', $arProps, $shs_META_KEYWORDS, GetMessage("parser_prop_id"), "id='prop-meta' style='width:262px' class='prop-iblock'");?></td>
    </tr>
    <tr class="heading">
        <td colspan="2"><?php echo GetMessage('parser_start_header');?></td>
    </tr>
    <tr>
        <td width="40%"><?echo GetMessage("parser_start_agent")?></td>
        <td width="60%"><input type="checkbox" name="START_AGENT" value="Y"<?if($shs_START_AGENT == "Y") echo " checked"?>></td>
    </tr>
    <tr>
        <td></td>
        <td>
            <?=BeginNote();?>
            <?echo GetMessage("parser_start_agent_descr")?>
            <?=EndNote();?>
        </td>
    </tr>
    <tr>
        <td width="40%"><?echo GetMessage("parser_time_agent")?></td>
        <td width="60%"><input type="text" size="40" name="TIME_AGENT" value="<?=$shs_TIME_AGENT?>"></td>
    </tr>
    <tr>
        <td width="40%"><?echo GetMessage("parser_sleep")?></td>
        <td width="60%"><input type="text" size="40" name="SETTINGS[catalog][sleep]" value="<?=$shs_SETTINGS["catalog"]["sleep"]?>"></td>
    </tr>
    <tr>
        <td></td>
        <td>
            <?=BeginNote();?>
            <?echo GetMessage("parser_sleep_descr")?>
            <?=EndNote();?>
        </td>
    </tr>
    <tr class="heading">
        <td colspan="2"><?php echo GetMessage('parser_proxy_header');?></td>
    </tr>
    <tr>
        <td width="40%"><?echo GetMessage("parser_proxy").':'?></td>
        <td width="60%">
            <input type="text" size="40" name="SETTINGS[catalog][proxy]" value="<?=$shs_SETTINGS["catalog"]["proxy"]?>">
            <input placeholder="username:password" type="text" size="30" name="SETTINGS[proxy][username_password]" value="<?=$shs_SETTINGS["proxy"]["username_password"]?>">
        </td>
    </tr>
    <?php
    if(isset($shs_SETTINGS['proxy']['servers']) && !empty($shs_SETTINGS['proxy']['servers'])){
    $i = 1;
        foreach($shs_SETTINGS['proxy']['servers'] as $id => $server){
            if(empty($server))
                continue;
            ?>
            <tr data-id="<?php echo $id?>">
                <td><?php echo GetMessage('parser_proxy').' '.$i.':';?></td>
                <td><input type="text"  size="40" name="SETTINGS[proxy][servers][<?php echo $id?>][ip]" value="<?php echo $server['ip'];?>"> <input placeholder="username:password" type="text" size="30" name="SETTINGS[proxy][servers][<?php echo $id?>][username_password]" value="<?=$server["username_password"]?>"> <a href="#" class="delete_proxy_server"><?php echo GetMessage('delete');?></a></td>
            </tr>
            <?php
            $i++;
        }
    }
    ?>
    <tr>
        <td colspan="2" align="center">
            <input type="submit" id="addProxyServer" name="refresh" value="<? echo GetMessage('add_proxy_server')?>">
        </td>
    </tr>
    <tr>
        <td></td>
        <td>
            <?=BeginNote();?>
            <?echo GetMessage("parser_proxy_username_descr")?>
            <?=EndNote();?>
        </td>
    </tr>
    <tr>
        <td width="40%"><?echo GetMessage("parser_madialibrary")?></td>
        <td width="60%"><?=SelectBoxFromArray('SETTINGS[madialibrary_id]', $arrLibrary, $shs_SETTINGS["madialibrary_id"], GetMessage("parser_no_select"), "");?></td>
    </tr>
    <?
    $tabControl->BeginNextTab();
    ?>
    <tr>
        <td width="40%"><?echo GetMessage("parser_uniq_update")?></td>
        <td width="60%"><input type="checkbox" name="SETTINGS[catalog][update][active]" value="Y"<?if($shs_SETTINGS["catalog"]["update"]["active"] == "Y") echo " checked"?>></td>
    </tr>
    
    <tr class="show_block_add_element" <?if(!isset($shs_SETTINGS["catalog"]["update"]["active"]) || ($shs_SETTINGS["catalog"]["update"]["active"] != "Y")):?>style="display: none"<?endif;?>>
        <td width="40%"><?echo GetMessage("parser_uniq_add_element")?></td>
        <td width="60%"><input type="checkbox" name="SETTINGS[catalog][update][add_element]" value="Y"<?if($shs_SETTINGS["catalog"]["update"]["add_element"] == "Y") echo " checked"?>></td>
    </tr>
    <tr class="show_block_add_element" <?if(!isset($shs_SETTINGS["catalog"]["update"]["active"]) || ($shs_SETTINGS["catalog"]["update"]["active"] != "Y")):?>style="display: none"<?endif;?>>
        <td></td>
        <td>
            <?=BeginNote();?>
            <?echo GetMessage("parser_uniq_add_element_descr")?>
            <?=EndNote();?>
        </td>
    </tr>
    <tr class="heading">
        <td colspan="2"><?echo GetMessage("parser_header_uniq")?></td>
    </tr>
    <!--
    <tr>
        <td width="40%"><?echo GetMessage("parser_uniq_name")?></td>
        <td width="60%"><input type="checkbox" name="SETTINGS[catalog][uniq][name]" value="Y"<?if($shs_SETTINGS["catalog"]["uniq"]["name"] == "Y") echo " checked"?>></td>
    </tr>
    -->
    <tr>
        <td width="40%"><?echo GetMessage("parser_uniq_prop")?></td>
        <td width="60%"><?=SelectBoxFromArray('SETTINGS[catalog][uniq][prop]', $arProps, $shs_SETTINGS["catalog"]["uniq"]["prop"], GetMessage("parser_prop_id"), "id='style='width:262px' class='prop-iblock'");?></td>
    </tr>
    <!--
    <tr>
        <td></td>
        <td>
            <?=BeginNote();?>
            <?echo GetMessage("parser_uniq_descr")?>
            <?=EndNote();?>
        </td>
    </tr>
    -->
    <tr class="heading">
        <td colspan="2"><?echo GetMessage("parser_header_uniq_field")?></td>
    </tr>
    <!--
    <tr>
        <td width="40%"><?echo GetMessage("parser_header_uniq_field_name")?></td>
        <td width="60%"><input type="checkbox" name="SETTINGS[catalog][update][name]" value="Y"<?if($shs_SETTINGS["catalog"]["update"]["name"] == "Y") echo " checked"?>></td>
    </tr>
    <tr>
        <td width="40%"><?echo GetMessage("parser_header_uniq_field_price")?></td>
        <td width="60%"><input type="checkbox" name="SETTINGS[catalog][update][price]" value="Y"<?if($shs_SETTINGS["catalog"]["update"]["price"] == "Y") echo " checked"?>></td>
    </tr>
    <tr>
        <td width="40%"><?echo GetMessage("parser_header_uniq_field_count")?></td>
        <td width="60%"><input type="checkbox" name="SETTINGS[catalog][update][count]" value="Y"<?if($shs_SETTINGS["catalog"]["update"]["count"] == "Y") echo " checked"?>></td>
    </tr>
    <tr>
        <td width="40%"><?echo GetMessage("parser_header_uniq_field_param")?></td>
        <td width="60%"><input type="checkbox" name="SETTINGS[catalog][update][param]" value="Y"<?if($shs_SETTINGS["catalog"]["update"]["param"] == "Y") echo " checked"?>></td>
    </tr>
    <tr>
        <td width="40%"><?echo GetMessage("parser_header_uniq_field_preview_descr")?></td>
        <td width="60%">
            <?=SelectBoxFromArray('SETTINGS[catalog][update][preview_descr]', $arUpdate, $shs_SETTINGS["catalog"]["update"]["preview_descr"], "", "");?>
            <?/*?><input type="checkbox" name="SETTINGS[catalog][update][preview_descr]" value="Y"<?if($shs_SETTINGS["catalog"]["update"]["preview_descr"] == "Y") echo " checked"?>><?*/?>
        </td>
    </tr>
    <tr>
        <td width="40%"><?echo GetMessage("parser_header_uniq_field_detail_descr")?></td>
        <td width="60%">
            <?=SelectBoxFromArray('SETTINGS[catalog][update][detail_descr]', $arUpdate, $shs_SETTINGS["catalog"]["update"]["detail_descr"], "", "");?>
        </td>
    </tr>
    <tr>
        <td width="40%"><?echo GetMessage("parser_header_uniq_field_preview_img")?></td>
        <td width="60%">
            <?=SelectBoxFromArray('SETTINGS[catalog][update][preview_img]', $arUpdate, $shs_SETTINGS["catalog"]["update"]["preview_img"], "", "");?>
        </td>
    </tr>
    <tr>
        <td width="40%"><?echo GetMessage("parser_header_uniq_field_detail_img")?></td>
        <td width="60%">
            <?=SelectBoxFromArray('SETTINGS[catalog][update][detail_img]', $arUpdate, $shs_SETTINGS["catalog"]["update"]["detail_img"], "", "");?>
        </td>
    </tr>
    <tr>
        <td width="40%"><?echo GetMessage("parser_header_uniq_field_more_img")?></td>
        <td width="60%"><input type="checkbox" name="SETTINGS[catalog][update][more_img]" value="Y"<?if($shs_SETTINGS["catalog"]["update"]["more_img"] == "Y") echo " checked"?>></td>
    </tr>
    <tr>
        <td width="40%"><?echo GetMessage("parser_header_uniq_field_props")?></td>
        <td width="60%"><input type="checkbox" name="SETTINGS[catalog][update][props]" value="Y"<?if($shs_SETTINGS["catalog"]["update"]["props"] == "Y") echo " checked"?>></td>
    </tr>
    -->
    <?php
    if(isset($shs_SETTINGS["catalog"]["update"]["props"]) && !empty($shs_SETTINGS["catalog"]["update"]["props"])){
        foreach($shs_SETTINGS["catalog"]["update"]["props"] as $prop => $val){
        ?>
        <tr class="update_prop_<?php echo $prop;?>">
            <td width="40%"><?php echo GetMessage("parser_field").' '.$prop; ?></td>
            <td width="60%"><input type="checkbox" name="SETTINGS[catalog][update][props][<?php echo $prop; ?>]" value="Y"<?if($shs_SETTINGS["catalog"]["update"]["props"][$prop] == "Y") echo " checked"?>>&nbsp;<a href="#" class="prev_prop_delete" data-prop-id="'+v+'"><?php echo GetMessage('parser_delete');?></a></td>
        </tr>
        <?php
        }
    }
    ?>
    <tr>
        <td colspan="2" align="center">
            <?=SelectBoxFromArray('arrPropUpdate', $arProps, "", GetMessage("shs_parser_select_prop"), "");?>
            <input type="submit" id="loadPropUpdate" name="refresh" value="<?=GetMessage("shs_parser_select_prop_but")?>">
        </td>
    </tr>
    <tr>
        <td></td>
        <td>
            <?=BeginNote();?>
            <?echo GetMessage("parser_header_uniq_field_descr")?>
            <?=EndNote();?>
        </td>
    </tr>
    <tr class="heading">
        <td colspan="2"><?echo GetMessage("parser_header_element_action")?></td>
    </tr>
    <tr>
        <td width="40%"><?echo GetMessage("parser_element_action")?></td>
        <td width="60%">
            <?=SelectBoxFromArray('SETTINGS[catalog][uniq][action]', $arAction, $shs_SETTINGS["catalog"]["uniq"]["action"], "", "");?>
        </td>
    </tr>
    <tr>
        <td></td>
        <td>
            <?=BeginNote();?>
            <?echo GetMessage("parser_element_action_descr")?>
            <?=EndNote();?>
        </td>
    </tr>
    <?
    $tabControl->BeginNextTab();
    ?>
    <tr>
        <td width="40%"><?echo GetMessage("parser_auth_type")?></td>
        <td width="60%">
            <?=SelectBoxFromArray('SETTINGS[catalog][auth][type]', $arAuthType, $shs_SETTINGS["catalog"]["auth"]["type"], "", "class='select_load'");?>
        </td>
    </tr>
    <tr>
        <td width="40%"><?echo GetMessage("parser_auth_active")?></td>
        <td width="60%"><input type="checkbox" name="SETTINGS[catalog][auth][active]" value="Y"<?if($shs_SETTINGS["catalog"]["auth"]["active"] == "Y") echo " checked"?>></td>
    </tr>
    <?if((isset($shs_SETTINGS["catalog"]["auth"]["type"]) && $shs_SETTINGS["catalog"]["auth"]["type"]=="form") || !isset($shs_SETTINGS["catalog"]["auth"]["type"])):?>
    <tr>
        <td width="40%"><?echo GetMessage("parser_auth_url")?></td>
        <td width="60%"><input type="text" size="40" name="SETTINGS[catalog][auth][url]" value="<?=$shs_SETTINGS["catalog"]["auth"]["url"]?>"></td>
    </tr>
    <tr>
        <td></td>
        <td>
            <?=BeginNote();?>
            <?echo GetMessage("parser_auth_url_descr")?>
            <?=EndNote();?>
        </td>
    </tr>
    <tr>
        <td width="40%"><?echo GetMessage("parser_auth_selector")?></td>
        <td width="60%"><input type="text" size="40" name="SETTINGS[catalog][auth][selector]" value="<?=$shs_SETTINGS["catalog"]["auth"]["selector"]?>"></td>
    </tr>
    <tr>
        <td width="40%"><?echo GetMessage("parser_auth_login")?></td>
        <td width="60%"><input type="text" size="40" name="SETTINGS[catalog][auth][login]" value="<?=$shs_SETTINGS["catalog"]["auth"]["login"]?>"> <?echo GetMessage("parser_auth_login_name")?> <input type="text" size="20" name="SETTINGS[catalog][auth][login_name]" value="<?=$shs_SETTINGS["catalog"]["auth"]["login_name"]?>"></td>
    </tr>
    <tr>
        <td width="40%"><?echo GetMessage("parser_auth_password")?></td>
        <td width="60%"><input type="text" size="40" name="SETTINGS[catalog][auth][password]" value="<?=$shs_SETTINGS["catalog"]["auth"]["password"]?>"> <?echo GetMessage("parser_auth_password_name")?> <input type="text" size="20" name="SETTINGS[catalog][auth][password_name]" value="<?=$shs_SETTINGS["catalog"]["auth"]["password_name"]?>"></td>
    </tr>
    <tr>
        <td></td>
        <td>
            <?=BeginNote();?>
            <?echo GetMessage("parser_auth_password_descr")?>
            <?=EndNote();?>
        </td>
    </tr>
    <?else:?>
    <tr>
        <td width="40%"><?echo GetMessage("parser_auth_login")?></td>
        <td width="60%"><input type="text" size="40" name="SETTINGS[catalog][auth][login]" value="<?=$shs_SETTINGS["catalog"]["auth"]["login"]?>"></td>
    </tr>
    <tr>
        <td width="40%"><?echo GetMessage("parser_auth_password")?></td>
        <td width="60%"><input type="text" size="40" name="SETTINGS[catalog][auth][password]" value="<?=$shs_SETTINGS["catalog"]["auth"]["password"]?>"></td>
    </tr>
    <?endif;?>
    <?if($shs_SETTINGS["catalog"]["auth"]["type"]=="form"):?>
    <tr>
        <td width="40%"></td>
        <td width="60%"><input type="button" size="40" id="auth" name="auth" data-href="<?=$APPLICATION->GetCurPageParam("auth=1", array("auth")); ?>" value="<?echo GetMessage('parser_auth_check')?>"></td>
    </tr>
    <?endif;?>
    <?
    $tabControl->BeginNextTab();
    ?>
    <tr>
        <td width="40%"><?echo GetMessage("parser_header_logs")?></td>
        <td width="60%"><input type="checkbox" name="SETTINGS[catalog][log]" value="Y"<?if($shs_SETTINGS["catalog"]["log"] == "Y") echo " checked"?>></td>
    </tr>
    <?
    $file_log = $_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/shs.parser/include/catalog_log_".htmlspecialcharsbx($_GET["ID"]).".txt";
    if(isset($_GET["ID"]) && file_exists($file_log)):?>
    <tr>
        <td width="40%"><?echo GetMessage("parser_header_logs_download")?></td>
        <td width="60%"><a href="<?=$APPLICATION->GetCurPageParam("log_ID=".htmlspecialcharsbx($_GET["ID"]), array("log_ID"));?>">catalog_log_<?=htmlspecialcharsbx($_GET["ID"])?>.txt  (<?=htmlspecialcharsbx(ceil(filesize($file_log)/1024))?> KB)</a></td>
    </tr>
    <?endif?>
    <tr>
        <td></td>
        <td>
            <?=BeginNote();?>
            <?echo GetMessage("parser_header_log_descr")?>
            <?=EndNote();?>
        </td>
    </tr>
    <!--
    <tr class="heading">
        <td colspan="2"><?echo GetMessage("parser_smart_logs_head")?></td>
    </tr>
    <tr>
        <td width="40%"><?echo GetMessage("parser_smart_logs")?></td>
        <td width="60%"><input type="checkbox" name="SETTINGS[smart_log][enabled]" value="Y"<?if($shs_SETTINGS["smart_log"]["enabled"] == "Y") echo " checked"?>></td>
    </tr>
    <tr>
        <td width="40%"><?echo GetMessage("parser_log_iteration")?></td>
        <td width="60%"><input type="text" size="40" name="SETTINGS[smart_log][iteration]" value="<?=$shs_SETTINGS["smart_log"]["iteration"]!=''?$shs_SETTINGS["smart_log"]["iteration"]:5?>"></td>
    </tr>
    <tr>
        <td width="40%"><?echo GetMessage("parser_log_save_props")?></td>
        <td width="60%"><input type="checkbox" size="40" name="SETTINGS[smart_log][settings][save_props]" value="Y"<?if($shs_SETTINGS["smart_log"]["settings"]["save_props"] == "Y") echo " checked"?>></td>
    </tr>
    <tr>
        <td width="40%"><?echo GetMessage("parser_log_save_price")?></td>
        <td width="60%"><input type="checkbox" size="40" name="SETTINGS[smart_log][settings][save_price]" value="Y"<?if($shs_SETTINGS["smart_log"]["settings"]["save_price"] == "Y") echo " checked"?>></td>
    </tr>
    <tr>
        <td width="40%"><?echo GetMessage("parser_log_save_count")?></td>
        <td width="60%"><input type="checkbox" size="40" name="SETTINGS[smart_log][settings][save_count]" value="Y"<?if($shs_SETTINGS["smart_log"]["settings"]["save_count"] == "Y") echo " checked"?>></td>
    </tr>
    <tr>
        <td width="40%"><?echo GetMessage("parser_log_save_descr")?></td>
        <td width="60%"><input type="checkbox" size="40" name="SETTINGS[smart_log][settings][save_descr]" value="Y"<?if($shs_SETTINGS["smart_log"]["settings"]["save_descr"] == "Y") echo " checked"?>></td>
    </tr>
    <tr>
        <td width="40%"><?echo GetMessage("parser_log_save_prev_descr")?></td>
        <td width="60%"><input type="checkbox" size="40" name="SETTINGS[smart_log][settings][save_prev_descr]" value="Y"<?if($shs_SETTINGS["smart_log"]["settings"]["save_prev_descr"] == "Y") echo " checked"?>></td>
    </tr>
    <tr>
        <td width="40%"><?echo GetMessage("parser_log_save_img")?></td>
        <td width="60%"><input type="checkbox" size="40" name="SETTINGS[smart_log][settings][save_img]" value="Y"<?if($shs_SETTINGS["smart_log"]["settings"]["save_img"] == "Y") echo " checked"?>></td>
    </tr>
    <tr>
        <td width="40%"><?echo GetMessage("parser_log_save_prev_img")?></td>
        <td width="60%"><input type="checkbox" size="40" name="SETTINGS[smart_log][settings][save_prev_img]" value="Y"<?if($shs_SETTINGS["smart_log"]["settings"]["save_prev_img"] == "Y") echo " checked"?>></td>
    </tr>
    <tr>
        <td width="40%"><?echo GetMessage("parser_log_save_addit_img")?></td>
        <td width="60%"><input type="checkbox" size="40" name="SETTINGS[smart_log][settings][save_addit_img]" value="Y"<?if($shs_SETTINGS["smart_log"]["settings"]["save_addit_img"] == "Y") echo " checked"?>></td>
    </tr>
    <tr>
        <td width="40%"><?echo GetMessage("parser_log_save_set_catalog")?></td>
        <td width="60%"><input type="checkbox" size="40" name="SETTINGS[smart_log][settings][save_set_catalog]" value="Y"<?if($shs_SETTINGS["smart_log"]["settings"]["save_set_catalog"] == "Y") echo " checked"?>></td>
    </tr>
    -->
    <?
    $tabControl->BeginNextTab();
    ?>
    <tr class="heading">
        <td colspan="2"><?echo GetMessage("parser_loc_type_head")?></td>
    </tr>
    <tr>
        <td width="40%"><?echo GetMessage("parser_loc_type")?>:</td>
        <td width="60%">
            <?=SelectBoxFromArray('SETTINGS[loc][type]', $arLocType, $shs_SETTINGS["loc"]["type"], "", "class='select_load'");?>
        </td>
    </tr>
    <?if(isset($shs_SETTINGS["loc"]["type"]) && $shs_SETTINGS["loc"]["type"]=="yandex"):?>
    <tr>
        <td width="40%"><?echo GetMessage("parser_loc_yandex_key")?>:</td>
        <td width="60%"><input type="text" size="40" name="SETTINGS[loc][yandex][key]" value="<?=$shs_SETTINGS["loc"]["yandex"]["key"]?>"></td>
    </tr>
    <tr>
        <td></td>
        <td>
            <?=BeginNote();?>
            <?echo GetMessage("parser_loc_yandex_key_descr")?>
            <?=EndNote();?>
        </td>
    </tr>
    <tr>
        <td width="40%"><?echo GetMessage("parser_loc_yandex_lang")?>:</td>
        <td width="60%"><input type="text" size="20" name="SETTINGS[loc][yandex][lang]" value="<?=$shs_SETTINGS["loc"]["yandex"]["lang"]?>"></td>
    </tr>
    <tr>
        <td></td>
        <td>
            <?=BeginNote();?>
            <?echo GetMessage("parser_loc_yandex_lang_descr")?>
            <?=EndNote();?>
        </td>
    </tr>
    <tr class="heading">
        <td colspan="2"><?echo GetMessage("parser_loc_fields")?></td>
    </tr>
    <tr>
        <td width="40%"><?echo GetMessage("parser_loc_fields_props")?>:</td>
        <td width="60%"><input type="checkbox" name="SETTINGS[loc][f_props]" value="Y"<?if($shs_SETTINGS["loc"]["f_props"] == "Y") echo " checked"?>></td>
    </tr>
    <?endif;
    $tabControl->BeginNextTab();
    ?>
    <tr class="heading">
        <td colspan="2"><?echo GetMessage("parser_video_2_0")?></td>
    </tr>
    <tr>
        <td align="center" colspan="2" width="100%">
            <?echo GetMessage("parser_video_2_0_text")?>
            <iframe width="800" height="500" src="https://www.youtube.com/embed/ej9bN2FgFls?list=PL2fR59TvIPXfA95wYKrG69YiG-nqFou9r" frameborder="0" allowfullscreen></iframe>
            <?echo GetMessage("parser_video_2_0_text1")?>
        </td>
    </tr>
    <tr class="heading">
        <td colspan="2"><?echo GetMessage("parser_video_catalog_descr")?></td>
    </tr>
    <tr>
        <td align="center" colspan="2" width="100%">
        <?echo GetMessage("parser_video_2_0_text0")?>
        <iframe width="800" height="500" src="//www.youtube.com/embed/vIMmjeo-xSg?list=PL2fR59TvIPXfB_XDmyp7pCnYoqQ-HhPXl" frameborder="0" allowfullscreen>
        </iframe></td>
    </tr>
<?
if(isset($_GET["log_ID"]) && isset($_GET["ID"])):
    if (ob_get_level()) {
      ob_end_clean();
    }
    $file = $file_log;
    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename=' . basename($file));
    header('Content-Transfer-Encoding: binary');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . filesize($file));
    readfile($file);
    exit();
endif;
$tabControl->BeginNextTab();  ?>
    <tr class="heading">
        <td colspan="2"><?echo GetMessage("parser_notification")?></td>
    </tr>
    <tr>
        <td width="40%"><?echo GetMessage("parser_notification_start")?>:</td>
        <td width="60%"><input type="checkbox" name="SETTINGS[notification][start]" value="Y"<?if($shs_SETTINGS["notification"]["start"] == "Y") echo " checked"?>></td>
    </tr>
    <tr>
        <td width="40%"><?echo GetMessage("parser_notification_end")?>:</td>
        <td width="60%"><input type="checkbox" name="SETTINGS[notification][end]" value="Y"<?if($shs_SETTINGS["notification"]["end"] == "Y") echo " checked"?>></td>
    </tr>
    <tr>
        <td width="40%"><?echo GetMessage("parser_notification_email")?>:</td>
        <td width="60%"><input type="text" size="40" name="SETTINGS[notification][email]" value="<?=!empty($shs_SETTINGS["notification"]["email"])?$shs_SETTINGS["notification"]["email"]:COption::GetOptionString("main", "email_from")?>"></td>
    </tr>
<?php
$tabControl->Buttons(
    array(
        "disabled"=>($POST_RIGHT<"W"),
        "back_url"=>"list_parser_admin.php?lang=".LANG,

    )
);
?>
<?echo bitrix_sessid_post();?>
<input type="hidden" name="lang" value="<?=LANG?>">
<?if($ID>0 && !$bCopy):?>
    <input type="hidden" name="ID" value="<?=htmlspecialcharsbx($ID)?>">
<?endif;?>
<input type="hidden" name="parent" value="<?=htmlspecialcharsbx($parentID)?>">
<?
$tabControl->End();
?>

<?
$tabControl->ShowWarnings("post_form", $message);
?>