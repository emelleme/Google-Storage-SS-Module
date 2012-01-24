<?php

	#doc
	#	classname:	Googlestorage
	#	scope:		PUBLIC
	#
	#/doc
	
	class GoogleStorage extends RestfulService
	{
		#	internal variables
		/**
		* @static
		* @var int $project_id
		*/
		static $project_id;
		
		/**
		* Set the type of Auth class the client should use.
		* @param int $projectid
		*/
		public function setProjectId($projectid) {
			self::$project_id = $projectid;
		}
		
		 /**
		* Get the Project Id.
		* @return int $project_id 
		*/
		public function getProjectId() {
			return self::$project_id;
		}
		
		#	Constructor
		function __construct ($expiry = 0)
		{
			# code...
			
			# Populate the necessary headers
			$config = DataObject::get_one('SiteConfig',false);
			//Check expiry of last token
			$currenttime = time();
			$isExpired = ($config->expiry < $currenttime) ? TRUE : FALSE;
			
			if ($isExpired)
			{
				# Token Expired. Refresh that bad boy.
				$client = new apiClient();
				$client->refreshToken($config->refresh_token);
				$client->authenticate();
				$newtoken = json_decode($client->getAccessToken());
				$config->expiry  = $currenttime + $newtoken->expires_in;
				$config->access_token = $newtoken->access_token;
				$config->write();
			}
			
			//Set Auth Header
			self::httpHeader('Authorization: OAuth '.$config->access_token);
			//Set Date Header
			$date = date(DATE_RFC1123);
			self::httpHeader('Date: '.$date);
			
			//Set Host Header
			self::httpHeader('Host: commondatastorage.googleapis.com');
			
			//Set the The Google Cloud Storage API version Header 
			self::httpHeader('x-goog-api-version: 2');
			
			//Other Required header is Content Length should be set at time of connect
			
			parent::__construct('commondatastorage.googleapis.com/', $expiry);
		}
		###	
		
		public function getBucket($name)
		{
			//List the objects in the given bucket
			$subUrl = $name;
			self::addAuthHeader();
			//Set the Content Length Header
			self::httpHeader('Content-Length: 0','GET',null);
			$req = self::request($subUrl);
    		return $req->getBody();
		}
		
		public function putObject($bucketName,$targetfile,$data,$public="FALSE")
		{
			//Put new object or replace existing
			$subUrl = $bucketName.'/'.$targetfile;
			self::addAuthHeader();
			//Set the Content Length Header
			$req = self::request($subUrl, $method = "PUT", $data);
    		return $req;
		}
		
		public function getObject($bucketName,$targetfile)
		{
			//Put new object or replace existing
			$subUrl = $bucketName.'/'.$targetfile;
			self::addAuthHeader();
			//Set the Content Length Header
			$req = self::request($subUrl, $method = "Get");
    		return $req;
		}
		
		private function addAuthHeader()
		{
			# Populate the necessary headers
			$config = DataObject::get_one('SiteConfig',false);
			//Check expiry of last token
			$currenttime = time();
			$isExpired = ($config->expiry < $currenttime) ? TRUE : FALSE;
			
			if ($isExpired)
			{
				# Token Expired. Refresh that bad boy.
				$client = new apiClient();
				$client->refreshToken($config->refresh_token);
				$client->authenticate();
				$newtoken = json_decode($client->getAccessToken());
				$config->expiry  = $currenttime + $newtoken->expires_in;
				$config->access_token = $newtoken->access_token;
				$config->write();
			}
			
			//Set Auth Header
			self::httpHeader('Authorization: OAuth '.$config->access_token);
		}
	
	}
	###

