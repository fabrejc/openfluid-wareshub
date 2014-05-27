<?php 

include_once(__DIR__."/ManagementTools.php");

class ReportingTools extends ManagementTools
{
  
  function __construct($ConfigDir)
  {
    parent::__construct($ConfigDir);  
  }
  
  
  public function getReport()
  {
    $WaresTypes = array("simulator","observer","builderext");
    
    $Report = array();
    
    foreach ($WaresTypes as $Type)
    {
      $TypeKey = $Type."s";
      
      $DefsPath = $this->ActiveDefsPath."/".
          $this->WHSystemConfig["definitions"]["waresdefs-dir"]."/".
          $this->WHSystemConfig["general"][$TypeKey."-dir"];
            
      $Report[$TypeKey] = array();

      $Report[$TypeKey]["instanciated"] = array();
      $Report[$TypeKey]["uninstanciated"] = array();
      $Report[$TypeKey]["missing-apacheconf"] = array();
      $Report[$TypeKey]["missing-gitrepos"] = array();
      $Report[$TypeKey]["extra-apacheconf-files"] = array();
      $Report[$TypeKey]["extra-gitrepos-dirs"] = array();
      
      $DirHandle  = opendir($DefsPath);
      while (false !== ($FileName = readdir($DirHandle))) 
      {
        $CurrentFile = $DefsPath."/".$FileName;
        
        $PathParts = pathinfo($CurrentFile);
        
        //print_r($PathParts);
        
        if (is_file($CurrentFile) && 
            array_key_exists("extension",$PathParts) && 
            $PathParts["extension"] == "json")
        {
          $WareInfos = $this->getWareInfos($Type,$PathParts["filename"]);
          
          if (is_file($WareInfos["apache-conf-file"]) && is_dir($WareInfos["git-repos-path"]))
            array_push($Report[$TypeKey]["instanciated"],$WareInfos["ware-id"]);
          else if (!is_file($WareInfos["apache-conf-file"]) && !is_dir($WareInfos["git-repos-path"]))
            array_push($Report[$TypeKey]["uninstanciated"],$WareInfos["ware-id"]);
          else if (is_file($WareInfos["apache-conf-file"]) && !is_dir($WareInfos["git-repos-path"]))
            array_push($Report[$TypeKey]["missing-gitrepos"],$WareInfos["ware-id"]);
          else if (!is_file($WareInfos["apache-conf-file"]) && is_dir($WareInfos["git-repos-path"]))
            array_push($Report[$TypeKey]["missing-apacheconf"],$WareInfos["ware-id"]);
        }
      }
      
      // Search for extra apache config files, with no corresponding definition
      $ApacheConfigsPath = $this->ActiveDefsInstanceRootPath."/".
        $this->WHSystemConfig["instance"]["apache-conf-rootdir"]."/".
        $this->WHSystemConfig["general"][$TypeKey."-dir"];
      
      $DirHandle  = opendir($ApacheConfigsPath);
      while (false !== ($FileName = readdir($DirHandle)))
      {
        $CurrentFile = $ApacheConfigsPath."/".$FileName;
        $PathParts = pathinfo($CurrentFile);
        
        if (is_file($CurrentFile) && 
            array_key_exists("extension",$PathParts) &&
            $PathParts["extension"] == "conf")
        {
          $WareID = $PathParts["filename"];
          
          if (!is_file($DefsPath."/".$WareID.".json"))
            array_push($Report[$TypeKey]["extra-apacheconf-files"],$WareID.".conf");
        }          
      }
      
      // Search for extra git repositories, with no corresponding definition
      $WaresGitRootPath = $this->ActiveDefsInstanceRootPath."/".
          $this->WHSystemConfig["instance"]["wares-git-rootdir"]."/".
          $this->WHSystemConfig["general"][$TypeKey."-dir"];
      
      $DirHandle  = opendir($WaresGitRootPath);
      while (false !== ($FileName = readdir($DirHandle)))
      {
        $CurrentFile = $WaresGitRootPath."/".$FileName;
      
        if (is_dir($CurrentFile) && 
            $FileName != "." &&
            $FileName != "..")
        {
          $WareID = $FileName;      
          if (!is_file($DefsPath."/".$WareID.".json"))
            array_push($Report[$TypeKey]["extra-gitrepos-dirs"],$FileName);
        }
      }
      
    }
    
    return $Report;
  }
  
}

?>