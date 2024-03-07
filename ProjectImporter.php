<?php

namespace KCMcG\ProjectImporter;

class ProjectImporter extends \ExternalModules\AbstractExternalModule {
	public $url;
	private $apiKeys = [];

	public function fetchProjectXml($apiKey) {
		$requestBody = [
			"content" => "project_xml",
			"token" => $apiKey,
			"exportSurveyFields" => true,
			"exportDataAccessGroups" => true,
			"returnMetadataOnly" => true,
			"exportFiles" => false
		];
		
		$requestBody = http_build_query($requestBody);
		
		$ch = $this->buildCurlCall($requestBody);

		$output = curl_exec($ch);

		curl_close($ch);

		return $output;
		//echo "<pre>";var_dump($output);echo "</pre>";
	}

	public function createProjectFromXml($xmlOutput) {
		$selfUrl = APP_PATH_WEBROOT_FULL.substr(APP_PATH_WEBROOT,1)."ProjectGeneral/create_project.php";
		//$selfUrl = APP_PATH_WEBROOT_FULL."plugins/test/postReceiver.php";

		$csrfToken = $this->getCSRFToken();

		$xmlOutput = 'data://application/octet-stream;base64,' . base64_encode($xmlOutput);
		$xmlFileUpload = new \CURLFile($xmlOutput, 'text/plain', 'ProjectToImport.xml');
		$postData = [
			'odm' => $xmlFileUpload,
			'app_title' => "Test Import Project from XML",
			'redcap_csrf_token' => $csrfToken
		];
		$ch = $this->buildMinCurlCall($selfUrl);
		curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: multipart/form-data']);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
		curl_setopt($ch, CURLOPT_COOKIE, session_name() . '=' . session_id());

		$output = curl_exec($ch);
	var_dump($output);
	}
	
	public function buildMinCurlCall($url) {
		$ch = curl_init();
		
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
		curl_setopt($ch, CURLOPT_VERBOSE, 0);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_AUTOREFERER, true);
		curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
		curl_setopt($ch, CURLOPT_FRESH_CONNECT, 1);

		return $ch;
	}
	public function buildCurlCall($requestBody) {
		$ch = $this->buildMinCurlCall($this->url);
		
		curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-type: application/x-www-form-urlencoded']);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
		curl_setopt($ch, CURLOPT_POSTFIELDS, $requestBody);

		return $ch;
	}
}

