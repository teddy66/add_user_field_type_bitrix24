<?php
 
use Bitrix\Main\Loader;
use Bitrix\Main\EventManager; 
use Bitrix\Main\CDBResult;
use Bitrix\Crm;

require dirname(__FILE__)."/lang/".LANGUAGE_ID."/handlers.php";

Loader::includeModule('crm');
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
			'c_ibel'        => array( 'ID' =>'c_ibel', 'NAME' => "Привязка к элементам инф. блоков с сортировкой"),
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
					'name' => "Порядок сортировки",
					'type' => 'list',
					'items' => array(
						'ID'		=> GetMessage('CRM_FIELDS_TYPE_IB_DISPLAY_LIST'),
						'NAME' 	=> GetMessage('CRM_FIELDS_TYPE_IB_DISPLAY_CHECKBOX'),
						'SORT' 	=> GetMessage('CRM_FIELDS_TYPE_IB_DISPLAY_CHECKBOX'),						
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

AddEventHandler("main", "OnUserTypeBuildList", array("MyCurledType", "GetUserTypeDescription"));
