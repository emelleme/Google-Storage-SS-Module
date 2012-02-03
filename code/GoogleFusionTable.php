<?php

	#doc
	#	classname:	GoogleFusionTable
	#	scope:		PUBLIC
	#
	#/doc
	
	class GoogleFusionTable extends RestfulService
	{
		#	internal variables
		/**
		* @static
		* @var string $fusionUrl
		*/
		static $fusionUrl = 'https://www.google.com/fusiontables/api/query?';
		
		/**
		* @static
		* @var int $table_id
		*/
		static $table_id;
		
		/**
		* Set the type of Auth class the client should use.
		* @param int $tableid
		*/
		public function setTableId($tableid) {
			self::$table_id = $tableid;
		}
		
		 /**
		* Get the Table Id.
		* @return int $table_id 
		*/
		public function getTableId() {
			return self::$table_id;
		}
		
		#	Constructor
		function __construct ($expiry=0)
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
			
			parent::__construct(self::$fusionUrl, $expiry);
		}
		###
		
		public function showTables($encid='true')
		{
			$subUrl = 'sql=SHOW+TABLES&encid='.$encid;
			$req = self::request($subUrl, $method = "Get");
    		return $req;
		}
		
		public function select($column='ROWID',$tableid='',$where='',$order='')
		{
			//Get Table ID
			$tableid = self::getTableId();
			$subUrl = 'sql=SELECT+'.$column.'+FROM+'.$tableid;
			$subUrl = ($where!='') ? $subUrl.'+WHERE+'.$where : $subUrl;
			$subUrl = ($order!='') ? $subUrl.'+ORDER+BY+'.$order : $subUrl;
			$req = self::request($subUrl, $method = "Get");
    		return $req;
		}
		
		public function insert($columns='',$values='')
		{
			//Get Table ID
			$tableid = self::getTableId();
			$subUrl = 'sql=INSERT+INTO+'.$tableid;
			$subUrl = ($columns!='') ? $subUrl.'+('.implode(",",$columns).')' : $subUrl;
			$subUrl = ($values!='') ? $subUrl.'+VALUES+'.'(\''.implode("','",$values).'\')' : $subUrl;
			$req = self::request($subUrl, $method = "POST");
    		return $req;
		}	
	
	}
	###

