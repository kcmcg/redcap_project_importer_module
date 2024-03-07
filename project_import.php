<?php

$module->url = $_POST['url'];
$apiToken = $_POST['api_token'];

$output = $module->fetchProjectXml($apiToken);
$module->createProjectFromXml($output);

?>
<table>
	<tbody>
		<tr>
			<td><span>URL of API to Pull From</span></td>
			<td><input type='text' name='url' value='<?=$module->escape($module->url)?>' /></td>
		</tr>
		<tr>
			<td><span>API Token to Import</span></td>
			<td><input type='text' name='api_token' value='<?=$module->escape($apiToken)?>' /></td>
		</tr>
	</tbody>
</table>

