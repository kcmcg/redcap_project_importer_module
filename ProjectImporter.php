<?php

namespace KCMcG\ProjectImporter;

class ProjectImporter extends \ExternalModules\AbstractExternalModule {
	public $url;
	private $apiKeys = [];
	## TODO Need to figure out what types of errors we can get and catch them
	public function copyProject($apiKey,$allowDuplicates = false) {
		$q = $this->queryLogs("SELECT created_project WHERE token = ?", [$apiKey]);
		if($row = db_fetch_assoc($q)) {
			var_dump($row);echo "<br />";
			$projectId = $row["project_id"];
			if(!$allowDuplicates) {
				return $projectId;
			}
		}
		
		$projectId = $this->copyProjectStructure($apiKey);

		echo "Project ID is: $projectId";
	}

	public function copyProjectStructure($apiKey) {
		$projectXml = $this->fetchProjectXml($apiKey);
		if($projectXml) {
			$projectId = $this->createProjectFromXml($projectXml);

			if($projectId) {
				$this->log("Project Created",["token" => $apiKey,"created_project" => $projectId]);
				return $projectId;
			}
		}
		return false;	
	}

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
		
		$finalUrl = curl_getinfo($ch)["url"];
		$newProjectString = strpos($finalUrl, "msg=newproject");
		$createProjectSuccess = $newProjectSting !== false;

		if($createProjectSuccess) {
			$projectId = false;
			## Find project ID of new project
			if(preg_match("/pid=([0-9]+)/", $finalUrl, $matches)) {
				$projectId = $matches[1];
			}
			return $projectId;
		}
		return false;
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

