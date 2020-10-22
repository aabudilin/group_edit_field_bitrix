<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
require("class.php");
?>

<?
$ob = new GroupEdit(array("IBLOCK_ID" => 28));
$sections = $ob->getSections();
$props = $ob->getProperties();
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
	}
</style>

<h1>Групповое обновление свойств</h1>

<form action="<?=$_SERVER['REQUEST_URI']?>" method="post">
	<p>
		<select name="section">
			<option disabled selected>Выберите раздел</option>
			<?foreach($sections as $section):?>
				<option value="<?=$section['ID']?>"><?=$section['NAME']?></option>
			<?endforeach?>
		</select>
	</p>
	<p>
		<select name="field">
			<option disabled selected>Свойство</option>
			<?foreach($props as $prop):?>
				<option value="<?=$prop['CODE']?>"><?=$prop['NAME']?></option>
			<?endforeach?>
		</select>
	</p>
	<p><input type="text" name="value" placeholder="Новое значение свойства" /></p>
	<button type="submit" class="btn btn-primary">Обновить свойство</button>
</form>

<?php require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php"); ?>