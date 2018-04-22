<?php
 
use Bitrix\Main\Loader;
use Bitrix\Main\EventManager; 
use Bitrix\Main\CDBResult;
use Bitrix\Crm;
use Bitrix\Crm\UserField\UserFieldHistory;
use Bitrix\Crm\UserField;

require dirname(__FILE__)."/lang/".LANGUAGE_ID."/handlers.php";

Loader::includeModule("crm");
$eventManager = \Bitrix\Main\EventManager::getInstance();
 
//page start

 
//обработчик для класса MyCurledType - собственный тип пользовательского поля "Привязка к элементам инф. блоков с сортировкой"

class MyCurledType extends CUserTypeIBlockElement
{
	// инициализация пользовательского свойства для главного модуля
   public function GetUserTypeDescription()
   {
      return array(
         "USER_TYPE_ID" => "c_ibel",
         "CLASS_NAME" => "MyCurledType",
         "DESCRIPTION" => GetMessage("USER_TYPE_IBEL_SORT_DESCRIPTION"),
         "BASE_TYPE" => "int",
      );
   }	

   //  здесь добавляем новое свойство поля - поле элемента инфоблока для сортировки
   function GetSettingsHTML($arUserField = false, $arHtmlControl, $bVarsFromForm)
	{
		$result = '';

		if($bVarsFromForm)
			$iblock_id = $GLOBALS[$arHtmlControl["NAME"]]["IBLOCK_ID"];
		elseif(is_array($arUserField))
			$iblock_id = $arUserField["SETTINGS"]["IBLOCK_ID"];
		else
			$iblock_id = "";
		
		if($bVarsFromForm)
			$iblock_sort = $GLOBALS[$arHtmlControl["NAME"]]["IBLOCK_SORT"];
		elseif(is_array($arUserField))
			$iblock_sort = $arUserField["SETTINGS"]["IBLOCK_SORT"];
		else
			$iblock_sort = "";
		
		if(CModule::IncludeModule('iblock'))
		{
			$result .= '
			<tr>
				<td>'.GetMessage("USER_TYPE_IBEL_DISPLAY").':</td>
				<td>
					'.
					GetIBlockDropDownList($iblock_id, $arHtmlControl["NAME"].'[IBLOCK_TYPE_ID]', $arHtmlControl["NAME"].'[IBLOCK_ID]', false, 'class="adm-detail-iblock-types"', 'class="adm-detail-iblock-list"')
					.'
				</td>
			</tr>
			';
		}
		else
		{
			$result .= '
			<tr>
				<td>'.GetMessage("USER_TYPE_IBEL_DISPLAY").':</td>
				<td>
					<input type="text" size="6" name="'.$arHtmlControl["NAME"].'[IBLOCK_ID]" value="'.htmlspecialcharsbx($value).'">
				</td>
			</tr>
			';
		}
		// начало добавленного в эту функцию кода
		// добавлен один select для выбора поля сортировки
		
		$result .= '
			<tr>
				<td>'.GetMessage("USER_TYPE_IBEL_SORT_DISPLAY").':</td>
				<td>';
		$result .= '<select  class="'.$iblock_sort.' '.$iblock_id.'" name="'.$arHtmlControl["NAME"].'[IBLOCK_SORT]" class="adm-detail-iblock-sort" id="'.$arHtmlControl["NAME"].'[IBLOCK_SORT]">'."\n";
		$result .= '<option value="0">'.GetMessage("USER_TYPE_IBEL_SORT_ANY").'</option>'."\n";
		$result .= '<option value="ID"'.($iblock_sort=="ID"? ' selected': '').'>'.GetMessage("USER_TYPE_IBEL_SORT_BY_ID").'</option>'."\n";
		$result .= '<option value="NAME"'.($iblock_sort=="NAME"? ' selected': '').'>'.GetMessage("USER_TYPE_IBEL_SORT_BY_NAME").'</option>'."\n";
		$result .= '<option value="SORT"'.($iblock_sort=="SORT"? ' selected': '').'>'.GetMessage("USER_TYPE_IBEL_SORT_BY_SORT").'</option>'."\n";
		$result .= "</select></td>
				</tr>\n";
		// конец добавленного кода
		
		if($bVarsFromForm)
			$ACTIVE_FILTER = $GLOBALS[$arHtmlControl["NAME"]]["ACTIVE_FILTER"] === "Y"? "Y": "N";
		elseif(is_array($arUserField))
			$ACTIVE_FILTER = $arUserField["SETTINGS"]["ACTIVE_FILTER"] === "Y"? "Y": "N";
		else
			$ACTIVE_FILTER = "N";

		if($bVarsFromForm)
			$value = $GLOBALS[$arHtmlControl["NAME"]]["DEFAULT_VALUE"];
		elseif(is_array($arUserField))
			$value = $arUserField["SETTINGS"]["DEFAULT_VALUE"];
		else
			$value = "";
		if(($iblock_id > 0) && CModule::IncludeModule('iblock'))
		{
			$result .= '
			<tr>
				<td>'.GetMessage("USER_TYPE_IBEL_DEFAULT_VALUE").':</td>
				<td>
					<select name="'.$arHtmlControl["NAME"].'[DEFAULT_VALUE]" size="5">
						<option value="">'.GetMessage("IBLOCK_VALUE_ANY").'</option>
			';

			$arFilter = Array("IBLOCK_ID"=>$iblock_id);
			if($ACTIVE_FILTER === "Y")
				$arFilter["ACTIVE"] = "Y";
			
			// здесь изменен вызов getlist, задана сортировка по добавленному выше полю сортировки
			// по умолчанию сортировка по id
			$arSort = array("ID" => "ASC");
			if($iblock_sort=="NAME") $arSort = array("NAME" => "ASC");
			if($iblock_sort=="SORT") $arSort = array("SORT" => "ASC");
			$rs = CIBlockElement::GetList(
				// было так:
				//array("NAME" => "ASC", "ID" => "ASC"),
				$arSort,
				$arFilter,
				false,
				false,
				array("ID", "NAME")
			);
			while($ar = $rs->GetNext())
				$result .= '<option value="'.$ar["ID"].'"'.($ar["ID"]==$value? " selected": "").'>'.$ar["NAME"].'</option>';

			$result .= '</select>';
		}
		else
		{
			$result .= '
			<tr>
				<td>'.GetMessage("USER_TYPE_IBEL_DEFAULT_VALUE").':</td>
				<td>
					<input type="text" size="8" name="'.$arHtmlControl["NAME"].'[DEFAULT_VALUE]" value="'.htmlspecialcharsbx($value).'">
				</td>
			</tr>
			';
		}

		if($bVarsFromForm)
			$value = $GLOBALS[$arHtmlControl["NAME"]]["DISPLAY"];
		elseif(is_array($arUserField))
			$value = $arUserField["SETTINGS"]["DISPLAY"];
		else
			$value = "LIST";
		$result .= '
		<tr>
			<td class="adm-detail-valign-top">'.GetMessage("USER_TYPE_ENUM_DISPLAY").':</td>
			<td>
				<label><input type="radio" name="'.$arHtmlControl["NAME"].'[DISPLAY]" value="LIST" '.("LIST"==$value? 'checked="checked"': '').'>'.GetMessage("USER_TYPE_IBEL_LIST").'</label><br>
				<label><input type="radio" name="'.$arHtmlControl["NAME"].'[DISPLAY]" value="CHECKBOX" '.("CHECKBOX"==$value? 'checked="checked"': '').'>'.GetMessage("USER_TYPE_IBEL_CHECKBOX").'</label><br>
			</td>
		</tr>
		';

		if($bVarsFromForm)
			$value = intval($GLOBALS[$arHtmlControl["NAME"]]["LIST_HEIGHT"]);
		elseif(is_array($arUserField))
			$value = intval($arUserField["SETTINGS"]["LIST_HEIGHT"]);
		else
			$value = 5;
		$result .= '
		<tr>
			<td>'.GetMessage("USER_TYPE_IBEL_LIST_HEIGHT").':</td>
			<td>
				<input type="text" name="'.$arHtmlControl["NAME"].'[LIST_HEIGHT]" size="10" value="'.$value.'">
			</td>
		</tr>
		';

		$result .= '
		<tr>
			<td>'.GetMessage("USER_TYPE_IBEL_ACTIVE_FILTER").':</td>
			<td>
				<input type="checkbox" name="'.$arHtmlControl["NAME"].'[ACTIVE_FILTER]" value="Y" '.($ACTIVE_FILTER=="Y"? 'checked="checked"': '').'>
			</td>
		</tr>
		';

		return $result;
	}
	
	function GetList($arUserField)
	{
		$rsElement = false;
		if(CModule::IncludeModule('iblock'))
		{
			$obElement = new CIBlockElementEnumMy;
			$rsElement = $obElement->GetTreeList($arUserField["SETTINGS"]["IBLOCK_ID"], $arUserField["SETTINGS"]["ACTIVE_FILTER"], $arUserField["SETTINGS"]["IBLOCK_SORT"]);
		}
		return $rsElement;
	}
	
	function PrepareSettings($arUserField)
	{
		$height = intval($arUserField["SETTINGS"]["LIST_HEIGHT"]);
		$disp = $arUserField["SETTINGS"]["DISPLAY"];
		if($disp!="CHECKBOX" && $disp!="LIST")
			$disp = "LIST";
		$iblock_id = intval($arUserField["SETTINGS"]["IBLOCK_ID"]);
		if($iblock_id <= 0)
			$iblock_id = "";
		$element_id = intval($arUserField["SETTINGS"]["DEFAULT_VALUE"]);
		if($element_id <= 0)
			$element_id = "";
		//добавлена переменная для сортировки
		// по умолчанию сортировка по sort
		$iblock_sort = $arUserField["SETTINGS"]["IBLOCK_SORT"];
		if($iblock_sort!="ID" && $iblock_sort!="NAME")
			$iblock_sort = "SORT";

		$active_filter = $arUserField["SETTINGS"]["ACTIVE_FILTER"] === "Y"? "Y": "N";

		return array(
			"DISPLAY" => $disp,
			"LIST_HEIGHT" => ($height < 1? 1: $height),
			"IBLOCK_ID" => $iblock_id,
			"IBLOCK_SORT" => $iblock_sort,
			"DEFAULT_VALUE" => $element_id,
			"ACTIVE_FILTER" => $active_filter,
		);
	}
	
	function ConvertToDB($arProperty, $value)
    {
        if(strlen($value["VALUE"])>0)
        {
            $value["VALUE"] = intval($value["VALUE"]);
        }
        return $value;
    }
	
	// редактирование свойства в форме (главный модуль)
 function GetEditFormHTML($arUserField, $arHtmlControl)
 {
  return self::getEditHTML($arHtmlControl['NAME'], $arHtmlControl['VALUE'], false);
 }

 // редактирование свойства в списке (главный модуль)
 function GetAdminListEditHTML($arUserField, $arHtmlControl)
 {
  return self::getViewHTML($arHtmlControl['NAME'], $arHtmlControl['VALUE'], true);
 }

 // представление свойства в списке (главный модуль, инфоблок)
 function GetAdminListViewHTML($arProperty, $value, $strHTMLControlName)
 {
  return self::getViewHTML($strHTMLControlName['VALUE'], $value['VALUE']);
 }
}

class CIBlockElementEnumMy extends CIBlockElementEnum
{
	function GetTreeList($IBLOCK_ID, $ACTIVE_FILTER="N", $IBLOCK_SORT="NAME")
	{
		$rs = false;
		if(CModule::IncludeModule('iblock'))
		{
			$arFilter = Array("IBLOCK_ID"=>$IBLOCK_ID);
			if($ACTIVE_FILTER === "Y")
				$arFilter["ACTIVE"] = "Y";
			
			// здесь тоже изменен вызов getlist, задана сортировка по добавленному выше полю сортировки
			// по умолчанию сортировка по id
            $arSort = array("ID" => "ASC");
			if($IBLOCK_SORT === "NAME")
				$arSort = array("NAME" => "ASC");
			if($IBLOCK_SORT === "SORT")
				$arSort = array("SORT" => "ASC");
			
			$rs = CIBlockElement::GetList(
				$arSort,
				$arFilter,
				false,
				false,
				array("ID", "NAME")
			);
			if($rs)
			{
				$rs = new CIBlockElementEnum($rs);
			}
		}
		return $rs;
	}
	function GetNext($bTextHtmlAuto=true, $use_tilda=true)
	{
		$r = parent::GetNext($bTextHtmlAuto, $use_tilda);
		if($r)
			$r["VALUE"] = $r["NAME"];

		return $r;
	}
}

class CCrmFieldsMy extends CCrmFields
{
	private $sUFEntityID = '';

	protected $cUFM = null;

	protected $cdb = null;

	private $arUFList = array();

	private $arEntityType = array();

	private $arFieldType = array();

	private $arErrors = array();

	private $bError = false;
	
	public static function GetFieldTypes()
	{
		//'Disk File' is disabled due to GUI issues (see CCrmDocument::GetDocumentFieldTypes)
		$arFieldType = Array(
			'string' 		=> array( 'ID' =>'string', 'NAME' => GetMessage('CRM_FIELDS_TYPE_S')),
			'integer'		=> array( 'ID' =>'integer', 'NAME' => GetMessage('CRM_FIELDS_TYPE_I')),
			'double'		=> array( 'ID' =>'double', 'NAME' => GetMessage('CRM_FIELDS_TYPE_D')),
			'boolean'		=> array( 'ID' =>'boolean', 'NAME' => GetMessage('CRM_FIELDS_TYPE_B')),
			'datetime'		=> array( 'ID' =>'datetime', 'NAME' => GetMessage('CRM_FIELDS_TYPE_DT')),
			'date'			=> array( 'ID' =>'date', 'NAME' => GetMessage('CRM_FIELDS_TYPE_DATE')),
			'money' 		=> array( 'ID' =>'money', 'NAME' => GetMessage('CRM_FIELDS_TYPE_MONEY')),
			'url' 			=> array( 'ID' =>'url', 'NAME' => GetMessage('CRM_FIELDS_TYPE_URL')),
			'address'		=> array( 'ID' =>'address', 'NAME' => GetMessage('CRM_FIELDS_TYPE_ADDRESS')),
			'enumeration' 	=> array( 'ID' =>'enumeration', 'NAME' => GetMessage('CRM_FIELDS_TYPE_E')),
			'file'			=> array( 'ID' =>'file', 'NAME' => GetMessage('CRM_FIELDS_TYPE_F')),
			'employee'		=> array( 'ID' =>'employee', 'NAME' => GetMessage('CRM_FIELDS_TYPE_EM')),
			'crm_status'	=> array( 'ID' =>'crm_status', 'NAME' => GetMessage('CRM_FIELDS_TYPE_CRM_STATUS')),
			'iblock_section'=> array( 'ID' =>'iblock_section', 'NAME' => GetMessage('CRM_FIELDS_TYPE_IBLOCK_SECTION')),
			'iblock_element'=> array( 'ID' =>'iblock_element', 'NAME' => GetMessage('CRM_FIELDS_TYPE_IBLOCK_ELEMENT')),
			'c_ibel'        => array( 'ID' =>'c_ibel', 'NAME' => GetMessage('CRM_FIELDS_TYPE_IBLOCK_SORT')),
			'crm'			=> array( 'ID' =>'crm', 'NAME' => GetMessage('CRM_FIELDS_TYPE_CRM_ELEMENT'))
			//'disk_file'	=> array( 'ID' =>'disk_file', 'NAME' => GetMessage('CRM_FIELDS_TYPE_DISK_FILE')),
		);
		return $arFieldType;
	}
	
	public static function GetAdditionalFields($entityType, $fieldValue = Array())
	{
		$arFields = Array();
		switch ($entityType)
		{
			case 'string':
				$arFields[] = array(
					'id' => 'ROWS',
					'name' => GetMessage('CRM_FIELDS_TEXT_ROW_COUNT'),
					'type' => 'text',
				);
				$arFields[] = array(
					'id' => 'DEFAULT_VALUE',
					'name' => GetMessage('CRM_FIELDS_DEFAULT_VALUE'),
					'type' => 'text',
				);
				break;
			case 'url':
			case 'money':
				$arFields[] = array(
					'id' => 'DEFAULT_VALUE',
					'name' => GetMessage('CRM_FIELDS_DEFAULT_VALUE'),
					'type' => 'text',
				);
				break;
			case 'integer':
			case 'double':
				$arFields[] = array(
					'id' => 'DEFAULT_VALUE',
					'name' => GetMessage('CRM_FIELDS_DEFAULT_VALUE'),
					'type' => 'text',
				);
			break;

			case 'boolean':
				$arFields[] = array(
					'id' => 'B_DEFAULT_VALUE',
					'name' => GetMessage('CRM_FIELDS_TYPE_B_VALUE'),
					'type' => 'list',
					'items' => array(
						'1' => GetMessage('CRM_FIELDS_TYPE_B_VALUE_YES'),
						'0' => GetMessage('CRM_FIELDS_TYPE_B_VALUE_NO')
					),
				);
				$arFields[] = array(
					'id' => 'B_DISPLAY',
					'name' => GetMessage('CRM_FIELDS_TYPE_B_DISPLAY'),
					'type' => 'list',
					'items' => array(
						'CHECKBOX' 	=> GetMessage('CRM_FIELDS_TYPE_B_DISPLAY_CHECKBOX'),
						'RADIO'		=> GetMessage('CRM_FIELDS_TYPE_B_DISPLAY_RADIO'),
						'DROPDOWN' 	=> GetMessage('CRM_FIELDS_TYPE_B_DISPLAY_DROPDOWN'),
					),
				);
			break;

			case 'datetime':
			case 'date':
			{
				$arFields[] = array(
					'id' => 'DT_TYPE',
					'name' => GetMessage('CRM_FIELDS_TYPE_DT_TYPE'),
					'type' => 'list',
					'items' => array(
						'NONE' 	=> GetMessage('CRM_FIELDS_TYPE_DT_TYPE_NONE'),
						'NOW'	=> GetMessage($entityType === 'datetime'
							? 'CRM_FIELDS_TYPE_DT_TYPE_NOW' : 'CRM_FIELDS_TYPE_DATE_TYPE_NOW'),
						'FIXED' => GetMessage('CRM_FIELDS_TYPE_DT_TYPE_FIXED'),
					),
				);

				if($entityType === 'datetime')
				{
					$arFields[] = array(
						'id' => 'DT_DEFAULT_VALUE',
						'name' => GetMessage('CRM_FIELDS_TYPE_DT_FIXED'),
						'type' => 'date',
						'params' => array('size' => 25)
					);
				}
				else
				{
					$arFields[] = array(
						'id' => 'DT_DEFAULT_VALUE',
						'name' => GetMessage('CRM_FIELDS_TYPE_DT_FIXED'),
						'type' => 'date_short',
						'params' => array('size' => 10)
					);
				}
			}
			break;

			case 'enumeration':
				$arFields[] = array(
					'id' => 'E_DISPLAY',
					'name' => GetMessage('CRM_FIELDS_TYPE_E_DISPLAY'),
					'type' => 'list',
					'items' => array(
						'LIST' => GetMessage('CRM_FIELDS_TYPE_E_DISPLAY_LIST'),
						'UI' => GetMessage('CRM_FIELDS_TYPE_E_DISPLAY_UI'),
						'CHECKBOX' => GetMessage('CRM_FIELDS_TYPE_E_DISPLAY_CHECKBOX'),
					),
				);
				$arFields[] = array(
					'id' => 'E_LIST_HEIGHT',
					'name' => GetMessage('CRM_FIELDS_TYPE_E_LIST_HEIGHT'),
					'type' => 'text',
				);
				$arFields[] = array(
					'id' => 'E_CAPTION_NO_VALUE',
					'name' => GetMessage('CRM_FIELDS_TYPE_E_CAPTION_NO_VALUE'),
					'type' => 'text',
				);
			break;

			case 'iblock_section':
				$id = isset($fieldValue['IB_IBLOCK_ID'])? $fieldValue['IB_IBLOCK_ID']: 0;
				$bActiveFilter = isset($fieldValue['IB_ACTIVE_FILTER']) && $fieldValue['IB_ACTIVE_FILTER'] == 'Y'? 'Y': 'N';

				$arFields[] = array(
					'id' => 'IB_IBLOCK_TYPE_ID',
					'name' => GetMessage('CRM_FIELDS_TYPE_IB_IBLOCK_TYPE_ID'),
					'type' => 'custom',
					'value' => GetIBlockDropDownList($id, 'IB_IBLOCK_TYPE_ID', 'IB_IBLOCK_ID')
				);

				$arFilter = Array("IBLOCK_ID"=>$id);
				if($bActiveFilter === "Y")
					$arFilter["GLOBAL_ACTIVE"] = "Y";

				$rs = CIBlockElement::GetList(
					array("SORT" => "DESC", "NAME"=>"ASC"),
					$arFilter,
					false,
					false,
					array("ID", "NAME")
				);
				$rsSections = CIBlockSection::GetList(
					Array("left_margin"=>"asc"),
					$arFilter,
					false,
					array("ID", "DEPTH_LEVEL", "NAME")
				);
				$arDefault = Array(''=>GetMessage('CRM_FIELDS_TYPE_IB_DEFAULT_VALUE_ANY'));
				while($arSection = $rsSections->GetNext())
					$arDefaul[$arSection["ID"]] = str_repeat("&nbsp;.&nbsp;", $arSection["DEPTH_LEVEL"]).$arSection["NAME"];

				$arFields[] = array(
					'id' => 'IB_DEFAULT_VALUE',
					'name' => GetMessage('CRM_FIELDS_TYPE_IB_DEFAULT_VALUE'),
					'items' => $arDefault,
					'type' => 'list',
				);

				$arFields[] = array(
					'id' => 'IB_DISPLAY',
					'name' => GetMessage('CRM_FIELDS_TYPE_IB_DISPLAY'),
					'type' => 'list',
					'items' => array(
						'LIST'		=> GetMessage('CRM_FIELDS_TYPE_IB_DISPLAY_LIST'),
						'CHECKBOX' 	=> GetMessage('CRM_FIELDS_TYPE_IB_DISPLAY_CHECKBOX'),
					),
				);
				$arFields[] = array(
					'id' => 'IB_LIST_HEIGHT',
					'name' => GetMessage('CRM_FIELDS_TYPE_IB_LIST_HEIGHT'),
					'type' => 'text',
				);
				$arFields[] = array(
					'id' => 'IB_ACTIVE_FILTER',
					'name' => GetMessage('CRM_FIELDS_TYPE_IB_ACTIVE_FILTER'),
					'type' => 'checkbox',
				);
			break;

			case 'c_ibel':
				$id = isset($fieldValue['IB_IBLOCK_ID'])? $fieldValue['IB_IBLOCK_ID']: 0;
				$bActiveFilter = isset($fieldValue['IB_ACTIVE_FILTER']) && $fieldValue['IB_ACTIVE_FILTER'] == 'Y'? 'Y': 'N';

				$arFields[] = array(
					'id' => 'IB_IBLOCK_TYPE_ID',
					'name' => GetMessage('CRM_FIELDS_TYPE_IB_IBLOCK_TYPE_ID'),
					'type' => 'custom',
					'value' => GetIBlockDropDownList($id, 'IB_IBLOCK_TYPE_ID', 'IB_IBLOCK_ID')
				);

				$arFilter = Array("IBLOCK_ID"=>$id);
				if($bActiveFilter === "Y")
					$arFilter["ACTIVE"] = "Y";

				$rs = CIBlockElement::GetList(
					array("SORT" => "DESC", "NAME"=>"ASC"),
					$arFilter,
					false,
					false,
					array("ID", "NAME")
				);

				$arDefault = Array(''=>GetMessage('CRM_FIELDS_TYPE_IB_DEFAULT_VALUE_ANY'));
				while($ar = $rs->GetNext())
					$arDefault[$ar["ID"]] = $ar["NAME"];

				$arFields[] = array(
					'id' => 'IB_DEFAULT_VALUE',
					'name' => GetMessage('CRM_FIELDS_TYPE_IB_DEFAULT_VALUE'),
					'items' => $arDefault,
					'type' => 'list',
				);

				$arFields[] = array(
					'id' => 'IB_DISPLAY',
					'name' => GetMessage('CRM_FIELDS_TYPE_IB_DISPLAY'),
					'type' => 'list',
					'items' => array(
						'LIST'		=> GetMessage('CRM_FIELDS_TYPE_IB_DISPLAY_LIST'),
						'CHECKBOX' 	=> GetMessage('CRM_FIELDS_TYPE_IB_DISPLAY_CHECKBOX'),
					),
				);
				
				$arFields[] = array(
					'id' => 'IB_IBLOCK_SORT',
					'name' => GetMessage('CRM_FIELDS_TYPE_IB_IBLOCK_SORT'),
					'type' => 'list',
					'items' => array(
						'ID'		=> GetMessage('CRM_FIELDS_TYPE_IB_IBLOCK_SORT_ID'),
						'NAME' 	=> GetMessage('CRM_FIELDS_TYPE_IB_IBLOCK_SORT_NAME'),
						'SORT' 	=> GetMessage('CRM_FIELDS_TYPE_IB_IBLOCK_SORT_SORT'),						
					),
				);
				
				$arFields[] = array(
					'id' => 'IB_LIST_HEIGHT',
					'name' => GetMessage('CRM_FIELDS_TYPE_IB_LIST_HEIGHT'),
					'type' => 'text',
				);
				$arFields[] = array(
					'id' => 'IB_ACTIVE_FILTER',
					'name' => GetMessage('CRM_FIELDS_TYPE_IB_ACTIVE_FILTER'),
					'type' => 'checkbox',
				);

			break;
			
			case 'iblock_element':
				$id = isset($fieldValue['IB_IBLOCK_ID'])? $fieldValue['IB_IBLOCK_ID']: 0;
				$bActiveFilter = isset($fieldValue['IB_ACTIVE_FILTER']) && $fieldValue['IB_ACTIVE_FILTER'] == 'Y'? 'Y': 'N';

				$arFields[] = array(
					'id' => 'IB_IBLOCK_TYPE_ID',
					'name' => GetMessage('CRM_FIELDS_TYPE_IB_IBLOCK_TYPE_ID'),
					'type' => 'custom',
					'value' => GetIBlockDropDownList($id, 'IB_IBLOCK_TYPE_ID', 'IB_IBLOCK_ID')
				);

				$arFilter = Array("IBLOCK_ID"=>$id);
				if($bActiveFilter === "Y")
					$arFilter["ACTIVE"] = "Y";

				$rs = CIBlockElement::GetList(
					array("SORT" => "DESC", "NAME"=>"ASC"),
					$arFilter,
					false,
					false,
					array("ID", "NAME")
				);

				$arDefault = Array(''=>GetMessage('CRM_FIELDS_TYPE_IB_DEFAULT_VALUE_ANY'));
				while($ar = $rs->GetNext())
					$arDefault[$ar["ID"]] = $ar["NAME"];

				$arFields[] = array(
					'id' => 'IB_DEFAULT_VALUE',
					'name' => GetMessage('CRM_FIELDS_TYPE_IB_DEFAULT_VALUE'),
					'items' => $arDefault,
					'type' => 'list',
				);

				$arFields[] = array(
					'id' => 'IB_DISPLAY',
					'name' => GetMessage('CRM_FIELDS_TYPE_IB_DISPLAY'),
					'type' => 'list',
					'items' => array(
						'LIST'		=> GetMessage('CRM_FIELDS_TYPE_IB_DISPLAY_LIST'),
						'CHECKBOX' 	=> GetMessage('CRM_FIELDS_TYPE_IB_DISPLAY_CHECKBOX'),
					),
				);
				$arFields[] = array(
					'id' => 'IB_LIST_HEIGHT',
					'name' => GetMessage('CRM_FIELDS_TYPE_IB_LIST_HEIGHT'),
					'type' => 'text',
				);
				$arFields[] = array(
					'id' => 'IB_ACTIVE_FILTER',
					'name' => GetMessage('CRM_FIELDS_TYPE_IB_ACTIVE_FILTER'),
					'type' => 'checkbox',
				);

			break;

			case 'crm_status':

				$arItems = Array();
				$ar = CCrmStatus::GetEntityTypes();
				foreach ($ar as $data)
					$arItems[$data['ID']] = $data['NAME'];

				$arFields[] = array(
					'id' => 'ENTITY_TYPE',
					'name' => GetMessage('CRM_FIELDS_TYPE_CRM_STATUS_ENTITY_TYPE'),
					'type' => 'list',
					'items' => $arItems,
				);
			break;

			case 'crm':
				$entityTypeLead = isset($fieldValue['ENTITY_TYPE_LEAD']) && $fieldValue['ENTITY_TYPE_LEAD'] == 'Y'? 'Y': 'N';
				$entityTypeContact = isset($fieldValue['ENTITY_TYPE_CONTACT']) && $fieldValue['ENTITY_TYPE_CONTACT'] == 'Y'? 'Y': 'N';
				$entityTypeCompany = isset($fieldValue['ENTITY_TYPE_COMPANY']) && $fieldValue['ENTITY_TYPE_COMPANY'] == 'Y'? 'Y': 'N';
				$entityTypeDeal = isset($fieldValue['ENTITY_TYPE_DEAL']) && $fieldValue['ENTITY_TYPE_DEAL'] == 'Y'? 'Y': 'N';

				$sVal = '
					<input type="checkbox" name="ENTITY_TYPE_LEAD" value="Y" '.($entityTypeLead=="Y"? 'checked="checked"': '').'> '.GetMessage('USER_TYPE_CRM_ENTITY_TYPE_LEAD').' <br/>
					<input type="checkbox" name="ENTITY_TYPE_CONTACT" value="Y" '.($entityTypeContact=="Y"? 'checked="checked"': '').'> '.GetMessage('USER_TYPE_CRM_ENTITY_TYPE_CONTACT').'<br/>
					<input type="checkbox" name="ENTITY_TYPE_COMPANY" value="Y" '.($entityTypeCompany=="Y"? 'checked="checked"': '').'> '.GetMessage('USER_TYPE_CRM_ENTITY_TYPE_COMPANY').'<br/>
					<input type="checkbox" name="ENTITY_TYPE_DEAL" value="Y" '.($entityTypeDeal=="Y"? 'checked="checked"': '').'> '.GetMessage('USER_TYPE_CRM_ENTITY_TYPE_DEAL').'<br/>
				';

				$arFields[] = array(
					'id' => 'ENTITY_TYPE',
					'name' => GetMessage('CRM_FIELDS_TYPE_CRM_ELEMENT_ENTITY_TYPE'),
					'type' => 'custom',
					'value' => $sVal
				);
			break;
		}
		return $arFields;
	}
}

class CCrmUserTypeMy extends CCrmUserType
{
	public function PrepareListFilterValues(array &$arFilterFields, array $arFilter = null, $sFormName = 'form1', $bVarsFromForm = true)
	{
		global $APPLICATION;
		$arUserFields = $this->GetAbstractFields();
		foreach($arFilterFields as &$arField)
		{
			$fieldID = $arField['id'];
			if(!isset($arUserFields[$fieldID]))
			{
				continue;
			}

			$arUserField = $arUserFields[$fieldID];
			if($arUserField['USER_TYPE']['USER_TYPE_ID'] === 'employee')
			{
				continue;
			}

			if ($arUserField['USER_TYPE']['BASE_TYPE'] == 'enum' ||
				$arUserField['USER_TYPE']['USER_TYPE_ID'] == 'iblock_element' || $arUserField['USER_TYPE']['USER_TYPE_ID'] == 'iblock_section' || $arUserField['USER_TYPE']['USER_TYPE_ID'] == 'c_ibel')
			{
				// Fix #29649. Allow user to add not multiple fields with height 1 item.
				if($arUserField['MULTIPLE'] !== 'Y'
					&& isset($arUserField['SETTINGS']['LIST_HEIGHT'])
					&& intval($arUserField['SETTINGS']['LIST_HEIGHT']) > 1)
				{
					$arUserField['MULTIPLE'] = 'Y';
				}

				//as the system presets the filter can not work with the field names containing []
				if ($arUserField['SETTINGS']['DISPLAY'] == 'CHECKBOX')
					$arUserField['SETTINGS']['DISPLAY'] = '';
			}

			$params = array(
				'arUserField' => $arUserField,
				'arFilter' => $arFilter,
				'bVarsFromForm' => $bVarsFromForm,
				'form_name' => 'filter_'.$sFormName,
				'bShowNotSelected' => true
			);

			$userType = $arUserField['USER_TYPE']['USER_TYPE_ID'];
			$templateName = $userType;
			if($userType === 'date')
			{
				$templateName = 'datetime';
				$params['bShowTime'] = false;
			}

			ob_start();
			$APPLICATION->IncludeComponent(
				'bitrix:crm.field.filter',
				$templateName,
				$params,
				false,
				array('HIDE_ICONS' => true)
			);
			$sVal = ob_get_contents();
			ob_end_clean();

			$arField['value'] = $sVal;
		}
		unset($field);
	}
	
	public function PrepareListFilterFields(&$arFilterFields, &$arFilterLogic)
	{
		$arUserFields = $this->GetAbstractFields();
		foreach($arUserFields as $FIELD_NAME => $arUserField)
		{
			if ($arUserField['SHOW_FILTER'] === 'N' || $arUserField['USER_TYPE']['BASE_TYPE'] === 'file')
			{
				continue;
			}

			$ID = $arUserField['ID'];
			$typeID = $arUserField['USER_TYPE']['USER_TYPE_ID'];
			$isMultiple = isset($arUserField['MULTIPLE']) && $arUserField['MULTIPLE'] === 'Y';

			if($typeID === 'employee')
			{
				$arFilterFields[] = array(
					'id' => $FIELD_NAME,
					'name' => $arUserField['LIST_FILTER_LABEL'],
					'type' => 'custom_entity',
					'selector' => array(
						'TYPE' => 'user',
						'DATA' => array('ID' => strtolower($FIELD_NAME), 'FIELD_ID' => $FIELD_NAME)
					)
				);
				continue;
			}
			elseif($typeID === 'string' || $typeID === 'url' || $typeID === 'address' || $typeID === 'money')
			{
				$arFilterFields[] = array(
					'id' => $FIELD_NAME,
					'name' => $arUserField['LIST_FILTER_LABEL'],
					'type' => 'text'
				);
				continue;
			}
			elseif($typeID === 'integer' || $typeID === 'double')
			{
				$arFilterFields[] = array(
					'id' => $FIELD_NAME,
					'name' => $arUserField['LIST_FILTER_LABEL'],
					'type' => 'number'
				);
				continue;
			}
			elseif($typeID === 'boolean')
			{
				$arFilterFields[] = array(
					'id' => $FIELD_NAME,
					'name' => $arUserField['LIST_FILTER_LABEL'],
					'type' => 'checkbox',
					'valueType' => 'numeric'
				);
				continue;
			}
			elseif($typeID === 'datetime' || $typeID === 'date')
			{
				$arFilterFields[] = array(
					'id' => $FIELD_NAME,
					'name' => $arUserField['LIST_FILTER_LABEL'],
					'type' => 'date',
					'time' => $typeID === 'datetime'
				);
				continue;
			}
			elseif($typeID === 'enumeration')
			{
				$enumEntity = new \CUserFieldEnum();
				$dbResultEnum = $enumEntity->GetList(
					array('SORT' => 'ASC'),
					array('USER_FIELD_ID' => $ID)
				);

				$listItems = array();
				while($enum = $dbResultEnum->Fetch())
				{
					$listItems[$enum['ID']] = $enum['VALUE'];
				}

				$arFilterFields[] = array(
					'id' => $FIELD_NAME,
					'name' => $arUserField['LIST_FILTER_LABEL'],
					'type' => 'list',
					'params' => array('multiple' => 'Y'),
					'items' => $listItems
				);
				continue;
			}
			elseif($typeID === 'iblock_element')
			{
				$listItems = array();
				$enity = new CUserTypeIBlockElement();
				$dbResult = $enity->GetList($arUserField);
				if(is_object($dbResult))
				{
					$qty = 0;
					$limit = 200;

					while($ary = $dbResult->Fetch())
					{
						$listItems[$ary['ID']] = $ary['NAME'];
						$qty++;
						if($qty === $limit)
						{
							break;
						}
					}
				}

				$arFilterFields[] = array(
					'id' => $FIELD_NAME,
					'name' => $arUserField['LIST_FILTER_LABEL'],
					'type' => 'list',
					'params' => array('multiple' => 'Y'),
					'items' => $listItems
				);
				continue;
			}
			elseif($typeID === 'c_ibel')
			{
				$listItems = array();
				$enity = new CUserTypeIBlockElement();
				$dbResult = $enity->GetList($arUserField);
				if(is_object($dbResult))
				{
					$qty = 0;
					$limit = 200;

					while($ary = $dbResult->Fetch())
					{
						$listItems[$ary['ID']] = $ary['NAME'];
						$qty++;
						if($qty === $limit)
						{
							break;
						}
					}
				}

				$arFilterFields[] = array(
					'id' => $FIELD_NAME,
					'name' => $arUserField['LIST_FILTER_LABEL'],
					'type' => 'list',
					'params' => array('multiple' => 'Y'),
					'items' => $listItems
				);
				continue;
			}
			elseif($typeID === 'iblock_section')
			{
				$listItems = array();
				$enity = new CUserTypeIBlockSection();
				$dbResult = $enity->GetList($arUserField);

				if(is_object($dbResult))
				{
					$qty = 0;
					$limit = 200;

					while($ary = $dbResult->Fetch())
					{
						$listItems[$ary['ID']] = isset($ary['DEPTH_LEVEL']) && $ary['DEPTH_LEVEL']  > 1
							? str_repeat('. ', ($ary['DEPTH_LEVEL'] - 1)).$ary['NAME'] : $ary['NAME'];
						$qty++;
						if($qty === $limit)
						{
							break;
						}
					}
				}

				$arFilterFields[] = array(
					'id' => $FIELD_NAME,
					'name' => $arUserField['LIST_FILTER_LABEL'],
					'type' => 'list',
					'params' => array('multiple' => 'Y'),
					'items' => $listItems
				);
				continue;
			}
			elseif($typeID === 'crm')
			{
				$settings = isset($arUserField['SETTINGS']) && is_array($arUserField['SETTINGS'])
					? $arUserField['SETTINGS'] : array();

				$entityTypeNames = array();
				$supportedEntityTypeNames = array(
					CCrmOwnerType::LeadName,
					CCrmOwnerType::DealName,
					CCrmOwnerType::ContactName,
					CCrmOwnerType::CompanyName
				);
				foreach($supportedEntityTypeNames as $entityTypeName)
				{
					if(isset($settings[$entityTypeName]) && $settings[$entityTypeName] === 'Y')
					{
						$entityTypeNames[] = $entityTypeName;
					}
				}

				$arFilterFields[] = array(
					'id' => $FIELD_NAME,
					'name' => $arUserField['LIST_FILTER_LABEL'],
					'type' => 'custom_entity',
					'selector' => array(
						'TYPE' => 'crm_entity',
						'DATA' => array(
							'ID' => strtolower($FIELD_NAME),
							'FIELD_ID' => $FIELD_NAME,
							'ENTITY_TYPE_NAMES' => $entityTypeNames,
							'IS_MULTIPLE' => $isMultiple
						)
					)
				);
				continue;
			}
			elseif($typeID === 'crm_status')
			{
				$listItems = array();
				if(isset($arUserField['SETTINGS'])
					&& is_array($arUserField['SETTINGS'])
					&& isset($arUserField['SETTINGS']['ENTITY_TYPE'])
				)
				{
					$entityType = $arUserField['SETTINGS']['ENTITY_TYPE'];
					if($entityType !== '')
					{
						$listItems = CCrmStatus::GetStatusList($entityType);
					}
				}

				$arFilterFields[] = array(
					'id' => $FIELD_NAME,
					'name' => $arUserField['LIST_FILTER_LABEL'],
					'type' => 'list',
					'params' => array('multiple' => 'Y'),
					'items' => $listItems
				);
				continue;
			}

			$arFilterFields[] = array(
				'id' => $FIELD_NAME,
				'name' => htmlspecialcharsex($arUserField['LIST_FILTER_LABEL']),
				'type' => 'custom',
				'value' => ''
			);

			// Fix issue #49771 - do not treat 'crm' type values as strings. To suppress filtration by LIKE.
			// Fix issue #56844 - do not treat 'crm_status' type values as strings. To suppress filtration by LIKE.
			if ($arUserField['USER_TYPE']['BASE_TYPE'] == 'string' && $arUserField['USER_TYPE']['USER_TYPE_ID'] !== 'crm' && $arUserField['USER_TYPE']['USER_TYPE_ID'] !== 'crm_status')
				$arFilterLogic[] = $FIELD_NAME;
		}
	}
	
	public function ListAddEnumFieldsValue($arParams, &$arValue, &$arReplaceValue, $delimiter = '<br />', $textonly = false, $arOptions = array())
	{
		$arUserFields = $this->GetAbstractFields();
		$bSecondLoop = false;
		$arValuePrepare = array();

		if(!is_array($arOptions))
		{
			$arOptions = array();
		}

		// The first loop to collect all the data fields
		foreach($arUserFields as $FIELD_NAME => &$arUserField)
		{
			$isMultiple = $arUserField['MULTIPLE'] == 'Y';
			foreach ($arValue as $ID => $data)
			{
				if(!$isMultiple)
				{
					$isEmpty = !isset($arValue[$ID][$FIELD_NAME]) && $arUserField['USER_TYPE']['USER_TYPE_ID'] != 'boolean';
				}
				else
				{
					$isEmpty = !isset($arValue[$ID][$FIELD_NAME]) || $arValue[$ID][$FIELD_NAME] === false;
				}

				if($isEmpty)
				{
					continue;
				}

				if ($arUserField['USER_TYPE']['USER_TYPE_ID'] == 'boolean')
				{
					if (isset($arValue[$ID][$FIELD_NAME]))
						$arValue[$ID][$FIELD_NAME] == ($arValue[$ID][$FIELD_NAME] == 1 || $arValue[$ID][$FIELD_NAME] == 'Y' ? 'Y' : 'N');

					$arVal = $arValue[$ID][$FIELD_NAME];
					if (!is_array($arVal))
						$arVal = array($arVal);

					foreach ($arVal as $val)
					{
						$val = (string)$val;

						if (strlen($val) <= 0)
						{
							//Empty value is always 'N' (not default field value)
							$val = 'N';
						}

						$arReplaceValue[$ID][$FIELD_NAME] .= (!empty($arReplaceValue[$ID][$FIELD_NAME]) ? $delimiter : '').($val == 1 ? GetMessage('MAIN_YES') : GetMessage('MAIN_NO'));
						if ($isMultiple)
						{
							$arValue[$ID][$FIELD_NAME][] = ($val == 1 || $val == 'Y') ? 'Y' : 'N';
						}
						else
						{
							$arValue[$ID][$FIELD_NAME] = ($val == 1 || $val == 'Y') ? 'Y' : 'N';
						}
					}
				}
				elseif ($arUserField['USER_TYPE']['USER_TYPE_ID'] == 'crm_status')
				{
					$ar = CCrmStatus::GetStatusList($arUserField['SETTINGS']['ENTITY_TYPE']);
					$arReplaceValue[$ID][$FIELD_NAME] = isset($ar[$arValue[$ID][$FIELD_NAME]])? $ar[$arValue[$ID][$FIELD_NAME]]: '';
				}
				elseif ($arUserField['USER_TYPE']['USER_TYPE_ID'] == 'crm')
				{
					$arParams['CRM_ENTITY_TYPE'] = Array();
					if ($arUserField['SETTINGS']['LEAD'] == 'Y')
						$arParams['CRM_ENTITY_TYPE'][] = 'LEAD';
					if ($arUserField['SETTINGS']['CONTACT'] == 'Y')
						$arParams['CRM_ENTITY_TYPE'][] = 'CONTACT';
					if ($arUserField['SETTINGS']['COMPANY'] == 'Y')
						$arParams['CRM_ENTITY_TYPE'][] = 'COMPANY';
					if ($arUserField['SETTINGS']['DEAL'] == 'Y')
						$arParams['CRM_ENTITY_TYPE'][] = 'DEAL';

					$arParams['CRM_PREFIX'] = false;
					if (count($arParams['CRM_ENTITY_TYPE']) > 1)
						$arParams['CRM_PREFIX'] = true;

					$bSecondLoop = true;
					$arVal = $arValue[$ID][$FIELD_NAME];
					if (!is_array($arVal))
						$arVal = array($arVal);

					foreach ($arVal as $value)
					{
						if($arParams['CRM_PREFIX'])
						{
							$ar = explode('_', $value);
							$arValuePrepare[$arUserField['USER_TYPE']['USER_TYPE_ID']][CUserTypeCrm::GetLongEntityType($ar[0])][] = intval($ar[1]);
							$arValuePrepare[$arUserField['USER_TYPE']['USER_TYPE_ID']]['FIELD'][$ID][$FIELD_NAME][CUserTypeCrm::GetLongEntityType($ar[0])][intval($ar[1])] = intval($ar[1]);
						}
						else
						{
							if (is_numeric($value))
							{
								$arValuePrepare[$arUserField['USER_TYPE']['USER_TYPE_ID']][$arParams['CRM_ENTITY_TYPE'][0]][] = $value;
								$arValuePrepare[$arUserField['USER_TYPE']['USER_TYPE_ID']]['FIELD'][$ID][$FIELD_NAME][$arParams['CRM_ENTITY_TYPE'][0]][$value] = $value;
							}
							else
							{
								$ar = explode('_', $value);
								$arValuePrepare[$arUserField['USER_TYPE']['USER_TYPE_ID']][CUserTypeCrm::GetLongEntityType($ar[0])][] = intval($ar[1]);
								$arValuePrepare[$arUserField['USER_TYPE']['USER_TYPE_ID']]['FIELD'][$ID][$FIELD_NAME][CUserTypeCrm::GetLongEntityType($ar[0])][intval($ar[1])] = intval($ar[1]);
							}
						}
					}
					$arReplaceValue[$ID][$FIELD_NAME] = '';
				}
				elseif ($arUserField['USER_TYPE']['USER_TYPE_ID'] == 'file'
					|| $arUserField['USER_TYPE']['USER_TYPE_ID'] == 'employee'
					|| $arUserField['USER_TYPE']['USER_TYPE_ID'] == 'iblock_element'
					|| $arUserField['USER_TYPE']['USER_TYPE_ID'] == 'c_ibel'
					|| $arUserField['USER_TYPE']['USER_TYPE_ID'] == 'enumeration'
					|| $arUserField['USER_TYPE']['USER_TYPE_ID'] == 'iblock_section')
				{
					$bSecondLoop = true;
					$arVal = $arValue[$ID][$FIELD_NAME];
					$arReplaceValue[$ID][$FIELD_NAME] = '';

					if (!is_array($arVal))
						$arVal = array($arVal);

					foreach ($arVal as $value)
					{
						if($value === '' || $value <= 0)
						{
							continue;
						}
						$arValuePrepare[$arUserField['USER_TYPE']['USER_TYPE_ID']]['FIELD'][$ID][$FIELD_NAME][$value] = $value;
						$arValuePrepare[$arUserField['USER_TYPE']['USER_TYPE_ID']]['ID'][] = $value;
					}
				}
				elseif(!$textonly
					&& ($arUserField['USER_TYPE']['USER_TYPE_ID'] === 'address'
						|| $arUserField['USER_TYPE']['USER_TYPE_ID'] === 'money'
						|| $arUserField['USER_TYPE']['USER_TYPE_ID'] === 'url'))
				{
					if($isMultiple)
					{
						$value = array();
						if(is_array($arValue[$ID][$FIELD_NAME]))
						{
							$valueCount = count($arValue[$ID][$FIELD_NAME]);
							for($i = 0; $i < $valueCount; $i++)
							{
								$value[] = htmlspecialcharsback($arValue[$ID][$FIELD_NAME][$i]);
							}
						}
					}
					else
					{
						$value = htmlspecialcharsback($arValue[$ID][$FIELD_NAME]);
					}

					$arReplaceValue[$ID][$FIELD_NAME] = $this->cUFM->GetPublicView(
						array_merge(
							$arUserField,
							array('ENTITY_VALUE_ID' => $ID, 'VALUE' => $value)
						)
					);
				}
				else if ($isMultiple && is_array($arValue[$ID][$FIELD_NAME]))
				{
					array_walk($arValue[$ID][$FIELD_NAME], create_function('&$v',  '$v = htmlspecialcharsbx($v);'));
					$arReplaceValue[$ID][$FIELD_NAME] = implode($delimiter, $arValue[$ID][$FIELD_NAME]);
				}
			}
		}
		unset($arUserField);

		// The second loop for special field
		if($bSecondLoop)
		{
			$arValueReplace = Array();
			$arList = Array();
			foreach($arValuePrepare as $KEY => $VALUE)
			{
				// collect multi data
				if ($KEY == 'iblock_section')
				{
					$dbRes = CIBlockSection::GetList(array('left_margin' => 'asc'), array('ID' => $VALUE['ID']), false);
					while ($arRes = $dbRes->Fetch())
						$arList[$KEY][$arRes['ID']] = $arRes;
				}
				elseif ($KEY == 'file')
				{
					$dbRes = CFile::GetList(Array(), array('@ID' => implode(',', $VALUE['ID'])));
					while ($arRes = $dbRes->Fetch())
						$arList[$KEY][$arRes['ID']] = $arRes;
				}
				elseif ($KEY == 'iblock_element')
				{
					$dbRes = CIBlockElement::GetList(array('SORT' => 'DESC', 'NAME' => 'ASC'), array('ID' => $VALUE['ID']), false);
					while ($arRes = $dbRes->Fetch())
						$arList[$KEY][$arRes['ID']] = $arRes;
				}
				elseif ($KEY == 'c_ibel')
				{
					$dbRes = CIBlockElement::GetList(array('SORT' => 'DESC', 'NAME' => 'ASC'), array('ID' => $VALUE['ID']), false);
					while ($arRes = $dbRes->Fetch())
						$arList[$KEY][$arRes['ID']] = $arRes;
				}
				elseif ($KEY == 'employee')
				{
					$dbRes = CUser::GetList($by = 'last_name', $order = 'asc', array('ID' => implode('|', $VALUE['ID'])));
					while ($arRes = $dbRes->Fetch())
						$arList[$KEY][$arRes['ID']] = $arRes;
				}
				elseif ($KEY == 'enumeration')
				{
					foreach ($VALUE['ID'] as $___value)
					{
						$rsEnum = CUserFieldEnum::GetList(array(), array('ID' => $___value));
						while ($arRes = $rsEnum->Fetch())
							$arList[$KEY][$arRes['ID']] = $arRes;
					}
				}
				elseif ($KEY == 'crm')
				{
					if (isset($VALUE['LEAD']) && !empty($VALUE['LEAD']))
					{
						$dbRes = CCrmLead::GetListEx(array('TITLE' => 'ASC', 'LAST_NAME' => 'ASC', 'NAME' => 'ASC'), array('ID' => $VALUE['LEAD']));
						while ($arRes = $dbRes->Fetch())
							$arList[$KEY]['LEAD'][$arRes['ID']] = $arRes;
					}
					if (isset($VALUE['CONTACT']) && !empty($VALUE['CONTACT']))
					{
						$dbRes = CCrmContact::GetListEx(array('LAST_NAME' => 'ASC', 'NAME' => 'ASC'), array('=ID' => $VALUE['CONTACT']));
						while ($arRes = $dbRes->Fetch())
							$arList[$KEY]['CONTACT'][$arRes['ID']] = $arRes;
					}
					if (isset($VALUE['COMPANY']) && !empty($VALUE['COMPANY']))
					{
						$dbRes = CCrmCompany::GetListEx(array('TITLE' => 'ASC'), array('ID' => $VALUE['COMPANY']));
						while ($arRes = $dbRes->Fetch())
							$arList[$KEY]['COMPANY'][$arRes['ID']] = $arRes;
					}
					if (isset($VALUE['DEAL']) && !empty($VALUE['DEAL']))
					{
						$dbRes = CCrmDeal::GetListEx(array('TITLE' => 'ASC'), array('ID' => $VALUE['DEAL']));
						while ($arRes = $dbRes->Fetch())
							$arList[$KEY]['DEAL'][$arRes['ID']] = $arRes;
					}
				}

				// assemble multi data
				foreach ($VALUE['FIELD'] as $ID => $arFIELD_NAME)
				{
					foreach ($arFIELD_NAME as $FIELD_NAME => $FIELD_VALUE)
					{
						foreach ($FIELD_VALUE as $FIELD_VALUE_NAME => $FIELD_VALUE_ID)
						{
							if ($KEY == 'iblock_section')
							{
								$sname = htmlspecialcharsbx($arList[$KEY][$FIELD_VALUE_ID]['NAME']);
								$arReplaceValue[$ID][$FIELD_NAME] .= (!empty($arReplaceValue[$ID][$FIELD_NAME]) ? $delimiter : '').$sname;
							}
							if ($KEY == 'iblock_element')
							{
								$sname = htmlspecialcharsbx($arList[$KEY][$FIELD_VALUE_ID]['NAME']);
								if(!$textonly)
								{
									$surl = GetIBlockElementLinkById($arList[$KEY][$FIELD_VALUE_ID]['ID']);
									if ($surl && strlen($surl) > 0)
									{
										$sname = '<a href="'.$surl.'">'.$sname.'</a>';
									}
								}
								$arReplaceValue[$ID][$FIELD_NAME] .= (!empty($arReplaceValue[$ID][$FIELD_NAME]) ? $delimiter : '').$sname;
							}
							if ($KEY == 'c_ibel')
							{
								$sname = htmlspecialcharsbx($arList[$KEY][$FIELD_VALUE_ID]['NAME']);
								if(!$textonly)
								{
									$surl = GetIBlockElementLinkById($arList[$KEY][$FIELD_VALUE_ID]['ID']);
									if ($surl && strlen($surl) > 0)
									{
										$sname = '<a href="'.$surl.'">'.$sname.'</a>';
									}
								}
								$arReplaceValue[$ID][$FIELD_NAME] .= (!empty($arReplaceValue[$ID][$FIELD_NAME]) ? $delimiter : '').$sname;
							}
							else if ($KEY == 'employee')
							{
								$sname = '';
								if(is_array($arList[$KEY][$FIELD_VALUE_ID]))
								{
									$sname = CUser::FormatName(CSite::GetNameFormat(false), $arList[$KEY][$FIELD_VALUE_ID], false, true);
									if(!$textonly)
									{
										$ar['PATH_TO_USER_PROFILE'] = CComponentEngine::MakePathFromTemplate(COption::GetOptionString('crm', 'path_to_user_profile'), array('user_id' => $arList[$KEY][$FIELD_VALUE_ID]['ID']));
										$sname = 	'<a href="'.$ar['PATH_TO_USER_PROFILE'].'" id="balloon_'.$arParams['GRID_ID'].'_'.$arList[$KEY][$FIELD_VALUE_ID]['ID'].'">'.$sname.'</a>'.
											'<script type="text/javascript">BX.tooltip('.$arList[$KEY][$FIELD_VALUE_ID]['ID'].', "balloon_'.$arParams['GRID_ID'].'_'.$arList[$KEY][$FIELD_VALUE_ID]['ID'].'", "");</script>';
									}
								}
								$arReplaceValue[$ID][$FIELD_NAME] .= (!empty($arReplaceValue[$ID][$FIELD_NAME]) ? $delimiter : '').$sname;
							}
							else if ($KEY == 'enumeration')
							{
								$arReplaceValue[$ID][$FIELD_NAME] .= (!empty($arReplaceValue[$ID][$FIELD_NAME]) ? $delimiter : '').htmlspecialcharsbx($arList[$KEY][$FIELD_VALUE_ID]['VALUE']);
							}
							else if ($KEY == 'file')
							{
								$fileInfo = $arList[$KEY][$FIELD_VALUE_ID];
								if($textonly)
								{
									$fileUrl = CFile::GetFileSRC($fileInfo);
								}
								else
								{
									$fileUrlTemplate = isset($arOptions['FILE_URL_TEMPLATE'])
										? $arOptions['FILE_URL_TEMPLATE'] : '';

									$fileUrl = $fileUrlTemplate === ''
										? CFile::GetFileSRC($fileInfo)
										: CComponentEngine::MakePathFromTemplate(
											$fileUrlTemplate,
											array('owner_id' => $ID, 'field_name' => $FIELD_NAME, 'file_id' => $fileInfo['ID'])
										);
								}

								$sname = $textonly ? $fileUrl : '<a href="'.htmlspecialcharsbx($fileUrl).'" target="_blank">'.htmlspecialcharsbx($arList[$KEY][$FIELD_VALUE_ID]['FILE_NAME']).'</a>';
								$arReplaceValue[$ID][$FIELD_NAME] .= (!empty($arReplaceValue[$ID][$FIELD_NAME]) ? $delimiter : '').$sname;
							}
							else if ($KEY == 'crm')
							{
								foreach($FIELD_VALUE_ID as $CID)
								{
									$link = '';
									$title = '';
									$prefix = '';
									if ($FIELD_VALUE_NAME == 'LEAD')
									{
										$link = CComponentEngine::MakePathFromTemplate(COption::GetOptionString('crm', 'path_to_lead_show'), array('lead_id' => $CID));
										$title = $arList[$KEY]['LEAD'][$CID]['TITLE'];
										$prefix = 'L';
									}
									elseif ($FIELD_VALUE_NAME == 'CONTACT')
									{
										$link = CComponentEngine::MakePathFromTemplate(COption::GetOptionString('crm', 'path_to_contact_show'), array('contact_id' => $CID));
										if(isset($arList[$KEY]['CONTACT'][$CID]))
										{
											$title = CCrmContact::PrepareFormattedName($arList[$KEY]['CONTACT'][$CID]);
										}
										$prefix = 'C';
									}
									elseif ($FIELD_VALUE_NAME == 'COMPANY')
									{
										$link = CComponentEngine::MakePathFromTemplate(COption::GetOptionString('crm', 'path_to_company_show'), array('company_id' => $CID));
										$title = $arList[$KEY]['COMPANY'][$CID]['TITLE'];
										$prefix = 'CO';
									}
									elseif ($FIELD_VALUE_NAME == 'DEAL')
									{
										$link = CComponentEngine::MakePathFromTemplate(COption::GetOptionString('crm', 'path_to_deal_show'), array('deal_id' => $CID));
										$title = $arList[$KEY]['DEAL'][$CID]['TITLE'];
										$prefix = 'D';
									}

									$sname = htmlspecialcharsbx($title);
									if(!$textonly)
									{
										$tooltip = '<script type="text/javascript">BX.tooltip('.$CID.', "balloon_'.$ID.'_'.$FIELD_NAME.'_'.$FIELD_VALUE_NAME.'_'.$CID.'", "/bitrix/components/bitrix/crm.'.strtolower($FIELD_VALUE_NAME).'.show/card.ajax.php", "crm_balloon'.($FIELD_VALUE_NAME == 'LEAD' || $FIELD_VALUE_NAME == 'DEAL' || $FIELD_VALUE_NAME == 'QUOTE' ? '_no_photo': '_'.strtolower($FIELD_VALUE_NAME)).'", true);</script>';
										$sname = '<a href="'.$link.'" target="_blank" id="balloon_'.$ID.'_'.$FIELD_NAME.'_'.$FIELD_VALUE_NAME.'_'.$CID.'">'.$sname.'</a>'.$tooltip;
									}
									else
									{
										$sname = "[$prefix]$sname";
									}
									$arReplaceValue[$ID][$FIELD_NAME] .= (!empty($arReplaceValue[$ID][$FIELD_NAME]) ? $delimiter : '').$sname;
								}
							}
						}
					}
				}
			}
		}
	}


}

AddEventHandler("main", "OnUserTypeBuildList", array("MyCurledType", "GetUserTypeDescription"));
