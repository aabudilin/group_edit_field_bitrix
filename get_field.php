<?
require_once($_SERVER['DOCUMENT_ROOT']."/bitrix/modules/main/include/prolog_before.php");
\Bitrix\Main\Loader::includeModule('iblock');

$request = Bitrix\Main\Application::getInstance()->getContext()->getRequest();
$code = $request->get('code');

/*
	PROPERTY_TYPE - S - строка, N - число, F - файл, L - список, E - привязка к элементам, G - привязка к группам
	MULTIPLE - Множественность (Y|N)
*/

$properties = CIBlockProperty::GetList(Array("sort"=>"asc", "name"=>"asc"), Array("ACTIVE"=>"Y", "IBLOCK_ID"=>28, "CODE" => $code));
$prop_fields = $properties->Fetch();
echo json_encode($prop_fields);

?>
