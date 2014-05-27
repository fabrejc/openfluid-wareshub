<?php


include_once(__DIR__."/ConfigManager.php");


class ManagementTools
{

  protected $WHSystemPath = "";
  protected $WHSystemConfig = array();
  
  
  protected $ActiveDefsPath = "";
  protected $ActiveDefsInstanceRootPath = "";
  protected $ActiveDefsConfig = array();
  
  
  // =====================================================================
  // =====================================================================
  
  
  function __construct($ConfigDir)
  {
    $this->WHSystemPath = realpath(__DIR__."/..");
    $ConfigMan = new ConfigManager($ConfigDir."/config.json");
    
    if (file_exists($ConfigDir."/localconfig.json"))
      $ConfigMan->appendFiles($ConfigDir."/localconfig.json");
    
    $this->WHSystemConfig = $ConfigMan->getConfig();          
  }

  
  // =====================================================================
  // =====================================================================
  
  
  public function getWHSystemPath() 
  { return $this->WHSystemPath; }
  
  public function getWHSystemConfig() 
  { return $this->WHSystemConfig; }
  
  public function getActiveDefsPath()
  { return $this->ActiveDefsPath; }
  
  public function isActiveDefs()
  { return ($this->ActiveDefsPath != ""); }
  
  public function getActiveDefsInstanceRootPath()
  { return $this->ActiveDefsInstanceRootPath; }

  public function getActiveDefsConfig()
  { return $this->ActiveDefsConfig; }
  
    
  // =====================================================================
  // =====================================================================
  
  
  public function setActiveDefinitionsSet($SetPath)
  {
    $this->checkMinimalWHSystemConfig();
     
    $this->ActiveDefsPath = $SetPath;
     
    $ConfigMan = new ConfigManager($this->ActiveDefsPath.
        "/".$this->WHSystemConfig["definitions"]["config-dir"].
        "/config.json");
     
    $LocalConfigPath = $this->ActiveDefsPath.
    "/".$this->WHSystemConfig["definitions"]["config-dir"].
    "/localconfig.json";
     
    if (file_exists($LocalConfigPath))
      $ConfigMan->appendFiles($LocalConfigPath);
     
    $this->ActiveDefsConfig = $ConfigMan->getConfig();
  
    $this->checkMinimalActiveDefsConfig();
     
    $this->ActiveDefsInstanceRootPath = $this->ActiveDefsConfig["instance-path"];
  }

  
  // =====================================================================
  // =====================================================================
  
  
  public function checkMinimalWHSystemConfig()
  {
    if (!array_key_exists("general", $this->WHSystemConfig))
      throw new Exception("Missing section \"general\" in configuration");
  
    if (!array_key_exists("simulators-dir", $this->WHSystemConfig["general"]))
      throw new Exception("Missing item \"general/simulators-dir\" in configuration");
  
    if (!array_key_exists("observers-dir", $this->WHSystemConfig["general"]))
      throw new Exception("Missing item \"general/observers-dir\" in configuration");
  
    if (!array_key_exists("builderexts-dir", $this->WHSystemConfig["general"]))
      throw new Exception("Missing item \"general/builderexts-dir\" in configuration");
  
    if (!array_key_exists("templates-dir", $this->WHSystemConfig["general"]))
      throw new Exception("Missing item \"general/templates-dir\" in configuration");
  
  
    if (!array_key_exists("definitions", $this->WHSystemConfig))
      throw new Exception("Missing section \"definitions\" in configuration");
  
    if (!array_key_exists("config-file", $this->WHSystemConfig["definitions"]))
      throw new Exception("Missing item \"definitions/config-file\" in configuration");
  
    if (!array_key_exists("localconfig-file", $this->WHSystemConfig["definitions"]))
      throw new Exception("Missing item \"definitions/localconfig-file\" in configuration");
  
    if (!array_key_exists("waresdefs-dir", $this->WHSystemConfig["definitions"]))
      throw new Exception("Missing item \"definitions/waresdefs-dir\" in configuration");
  
    if (!array_key_exists("web-dir", $this->WHSystemConfig["definitions"]))
      throw new Exception("Missing item \"definitions/web-dir\" in configuration");
  
    if (!array_key_exists("templates-dir", $this->WHSystemConfig["definitions"]))
      throw new Exception("Missing item \"definitions/templates-dir\" in configuration");
  
  
    if (!array_key_exists("instance", $this->WHSystemConfig))
      throw new Exception("Missing section \"instance\" in configuration");
  
    if (!array_key_exists("wares-git-rootdir", $this->WHSystemConfig["instance"]))
      throw new Exception("Missing item \"instance/wares-git-rootdir\" in configuration");
  
    if (!array_key_exists("apache-conf-rootdir", $this->WHSystemConfig["instance"]))
      throw new Exception("Missing item \"instance/apache-conf-rootdir\" in configuration");
  
    if (!array_key_exists("apache-conf-mainfile", $this->WHSystemConfig["instance"]))
      throw new Exception("Missing item \"instance/apache-conf-mainfile\" in configuration");
  
  }
  
  
  // =====================================================================
  // =====================================================================
  
  
  public function checkMinimalActiveDefsConfig()
  {
    $this->checkMinimalWHSystemConfig();
  
    if (!array_key_exists("instance-path", $this->ActiveDefsConfig))
      throw new Exception("Missing item \"instance-path\" in configuration");
  
    if (!array_key_exists("url-web-subdir", $this->ActiveDefsConfig))
      throw new Exception("Missing item \"url-web-subdir\" in configuration");
  
    if (!array_key_exists("url-git-subdir", $this->ActiveDefsConfig))
      throw new Exception("Missing item \"url-git-subdir\" in configuration");
  
    if (!array_key_exists("git-core-path", $this->ActiveDefsConfig))
      throw new Exception("Missing item \"git-core-path\" in configuration");
  }

  
  // =====================================================================
  // =====================================================================
  
  
  protected function getTemplate($TplKey)
  {
    $TemplateToUse = $this->ActiveDefsPath."/".$this->WHSystemConfig["definitions"]["templates-dir"]."/".$this->WHSystemConfig["definitions"]["templates-files"][$TplKey];
  
    if (!is_file($TemplateToUse))
      $TemplateToUse = $this->WHSystemPath."/".$this->WHSystemConfig["general"]["templates-dir"]."/".$this->WHSystemConfig["general"]["templates-files"]["$TplKey"];
     
    if (!is_file($TemplateToUse))
      throw new Exception("Template file not found (".$TemplateToUse.")");
  
    return $TemplateToUse;
  }
  
  
  // =====================================================================
  // =====================================================================
  

  public function getWareInfos($WareType,$WareID)
  {
    $WareTypeSubdir = "";
     
    if ($WareType == "simulator")
    {
      $WareTypeSubdir = $this->WHSystemConfig["general"]["simulators-dir"];
    }
    else if ($WareType == "observer")
    {
      $WareTypeSubdir = $this->WHSystemConfig["general"]["observers-dir"];
    }
    else if ($WareType == "builderext")
    {
      $WareTypeSubdir = $this->WHSystemConfig["general"]["builderexts-dir"];
    }
    else
      throw new Exception("Unknown ware type (".$WareType.")");
  
     
    $Infos = array();
     
    $Infos["ware-id"] = $WareID;
     
    $Infos["ware-type"] = $WareType;
     
    $Infos["ware-type-subdir"] = $WareTypeSubdir;
     
    $Infos["definition-file"] = $this->ActiveDefsPath."/".
        $this->WHSystemConfig["definitions"]["waresdefs-dir"]."/".
        $WareTypeSubdir."/".
        $WareID.".json";
     
    $Infos["apache-conf-file"] = $this->ActiveDefsInstanceRootPath."/".
        $this->WHSystemConfig["instance"]["apache-conf-rootdir"]."/".
        $WareTypeSubdir."/".
        $WareID.".conf";
     
    $Infos["git-repos-path"] = $this->ActiveDefsInstanceRootPath."/".
        $this->WHSystemConfig["instance"]["wares-git-rootdir"]."/".
        $WareTypeSubdir."/".
        $WareID;
     
    $Infos["git-description-file"] = $Infos["git-repos-path"]."/description";
     
    return $Infos;
  }
   
  
  // =====================================================================
  // =====================================================================
  
   
  public function loadAndCheckDefinition($WareInfos)
  {
    $DefContents = file_get_contents($WareInfos["definition-file"]);
    $DefContents = utf8_encode($DefContents);
    $DefConfig = json_decode($DefContents,true);
  
    $WareID = $WareInfos["ware-id"];
     
    if (!array_key_exists($WareID,$DefConfig))
      throw new Exception("Ware ID error in definition file (".$WareInfos["definition-file"].")");
  
    if (!array_key_exists("users-ro",$DefConfig[$WareID]))
      throw new Exception("Missing read-only users in definition file (".$WareInfos["definition-file"].")");
  
    if (!is_array($DefConfig[$WareID]["users-ro"]))
      throw new Exception("Wrong format for read-only users in definition file (".$WareInfos["definition-file"].")");
     
    if (!array_key_exists("users-rw",$DefConfig[$WareID]))
      throw new Exception("Missing read-write users in definition file (".$WareInfos["definition-file"].")");
  
    if (!is_array($DefConfig[$WareID]["users-rw"]))
      throw new Exception("Wrong format for read-write users in definition file (".$WareInfos["definition-file"].")");
  
     
    return $DefConfig;
  }
  
}

?>
