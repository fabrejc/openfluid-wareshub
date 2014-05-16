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
  	  
      $this->ConfigData = array_replace_recursive($this->ConfigData,json_decode($Contents,true));  	  
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