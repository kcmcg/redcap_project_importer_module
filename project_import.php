<?php
$module->url = $_POST['url'];
$apiKey = $_POST['api_key'];

if($module->url && $apiKey) {
	$module->copyProject($apiKey);
}

?>
<form method="POST">
<table>
	<tbody>
		<tr>
			<td><span>URL of API to Pull From</span></td>
			<td><input type='text' name='url' value='<?=$module->escape($module->url)?>' /></td>
		</tr>
		<tr>
			<td><span>API Token to Import</span></td>
			<td><input type='text' name='api_key' value='<?=$module->escape($apiKey)?>' /></td>
		</tr>
		<tr>
			<td colspan=2><input type="submit" value="Submit" /></td>
		</tr>
	</tbody>
</table>
</form>

