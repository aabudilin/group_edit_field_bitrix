<?php
@define("PAGE_TYPE", "static");
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
require("GroupEdit.php");
?>

<?
$ob = new GroupEdit(array("IBLOCK_ID" => 28));
$sections = $ob->getSections();
$props = $ob->getProperties();

function left_margin($depth) {
	$margin = '';
	if ($depth == 2) $margin = '..';
	if ($depth == 3) $margin = '....';
	if ($depth == 4) $margin = '......';
	return $margin;
}

?>

<?
	if(isset($_POST['section'])) {
		//print_r($_POST);
		$ob->process($_POST);
		$log = $ob->getLog();
	}
?>

<?if (isset($log)):?>
	<p><b>Обновлено <?=$log['count']?> записей</b></p>
	<table class="table">
	<?foreach($log['items'] as $row):?>
		<tr>
			<?foreach($row as $item):?>
				<td><?=$item?></td>
			<?endforeach?>
		</tr>
	<?endforeach?>
	</table>
<?endif?>

<style>
	form select, input {
		border:solid 1px #dedede;
		padding:10px;
	}

	form input {
		width:100%;
		height:50px;
		margin-bottom:15px;
	}

	.bold {
		font-weight:bold;
	}
</style>

<h1>Групповое обновление свойств</h1>

<form action="<?=$_SERVER['REQUEST_URI']?>" method="post">
	<p>
		<select name="section">
			<option disabled selected>Выберите раздел</option>
			<?foreach($sections as $section):?>
				<option value="<?=$section['ID']?>" <?=($section['DEPTH_LEVEL'] == 1 ? 'class="bold"' : '')?>>
					<?=left_margin($section['DEPTH_LEVEL'])?>
					<?=$section['NAME']?>
				</option>
			<?endforeach?>
		</select>
	</p>
	<p>
		<select name="field" id="selectField">
			<option disabled selected>Свойство</option>
			<?foreach($props as $prop):?>
				<option value="<?=$prop['CODE']?>"><?=$prop['NAME']?></option>
			<?endforeach?>
		</select>
	</p>
	<p id="resultField"></p>
	<!--<input type="text" name="value" placeholder="Новое значение свойства" />-->
	<button type="submit" class="btn btn-primary">Обновить свойство</button>
</form>

<script>
	selectField.onchange = async () => {
		let code = selectField.value;
		let json = await getField(code);
		resultField.innerHTML = renderInput(json);
	}

	async function getField(code) {
		let url = '/manager/group_edit/get_field.php';
		let params = '?code=' + code;
		let response = await fetch(url + params);
		if (response.ok) {
			return await response.json();
		} else {
		  console.log("Ошибка HTTP: " + response.status);
		  return false;
		}
	}

	function renderInput(json) {
		let template = '<input type="text" name="value" placeholder="Новое значение свойства" />';
		let result = '';
		//if(json.PROPERTY_TYPE != 'L'){}
		if(json.MULTIPLE == 'Y'){
			template = '<input type="text" name="value[]" placeholder="Новое значение свойства" />';
			for(let i = 0; i < 5; i++) {
				result = result + template;
			}
		} else {
			result = template;
		}
		return result;
	}
</script>

<?php require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php"); ?>
