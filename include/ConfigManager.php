<?php 


class ConfigManager
{
  
  private $ConfigData;	
	
  
  // =====================================================================
  // =====================================================================

  
  function __construct()
  {
  	$this->clearConfig();
  	$this->processFiles(func_get_args());
  }
    
  
  // =====================================================================
  // =====================================================================
  
  
  private function processFiles($FileList)
  { 	
  	for ($i=0;$i<sizeof($FileList);$i++)
  	{
  	  $Contents = file_get_contents($FileList[$i]);
  	  $Contents = utf8_encode($Contents);  	   	 
  	  
  	  $DecodedJSON = json_decode($Contents,true);
  	  
  	  if (json_last_error() != JSON_ERROR_NONE)
  	    throw new Exception("Wrong json file (".$FileList[$i].")");
  	  
      $this->ConfigData = array_replace_recursive($this->ConfigData,$DecodedJSON);  	  
  	}
  }
  
    
  // =====================================================================
  // =====================================================================

  
  public function appendFiles()
  {
  	$this->processFiles(func_get_args());
  }
  
  
  // =====================================================================
  // =====================================================================
  
  
  public function getConfig()
  {
  	return $this->ConfigData;
  }

  
  // =====================================================================
  // =====================================================================
  
  
  public function clearConfig()
  {
  	$this->ConfigData = array();
  }  
}


?>