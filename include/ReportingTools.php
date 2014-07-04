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
    $Report = array();
    
    foreach (static::$WARETYPES as $Type)
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

  
  // =====================================================================
  // =====================================================================
  
  
  public function getWebReport()
  {
    $Report = array();
    
    
    foreach (static::$WARETYPES as $Type)
    {
      $TypeKey = $Type."s";
    
      $DefsPath = $this->ActiveDefsPath."/".
          $this->WHSystemConfig["definitions"]["waresdefs-dir"]."/".
          $this->WHSystemConfig["general"][$TypeKey."-dir"];
    
      $Report[$TypeKey] = array();
    
      $DirHandle  = opendir($DefsPath);
      while (false !== ($FileName = readdir($DirHandle)))
      {
        $CurrentFile = $DefsPath."/".$FileName;
    
        $PathParts = pathinfo($CurrentFile);
    
        if (is_file($CurrentFile) &&
            array_key_exists("extension",$PathParts) &&
            $PathParts["extension"] == "json")
        {
          $WareInfos = $this->getWareInfos($Type,$PathParts["filename"]);
                    
          if (is_file($WareInfos["apache-conf-file"]) && is_dir($WareInfos["git-repos-path"]))
          {
            $ID = $WareInfos["ware-id"];
            
            // definition
            $WareDefinition = $this->loadandCheckDefinition($WareInfos);
            
            if (array_key_exists("webreporting",$WareDefinition[$ID]) &&
                $WareDefinition[$ID]["webreporting"] == "true")
            {
              $Report[$TypeKey][$ID] = array();
              $Report[$TypeKey][$ID]["branches"] = array();
              $Report[$TypeKey][$ID]["compat-versions"] = array();
              $Report[$TypeKey][$ID]["git-url-subdir"] = $WareInfos["git-url-subdir"];
              $Report[$TypeKey][$ID]["pdf-doc-url"] = "";
              $Report[$TypeKey][$ID]["definition"] = $WareDefinition[$ID];
              
              $this->processUsersGrants($Report[$TypeKey][$ID]["definition"]["users-ro"],
                                        $Report[$TypeKey][$ID]["definition"]["users-rw"]);
              
              
              // pdf doc
              if (is_file($WareInfos["pdfdoc-file"]))
              {
                $Report[$TypeKey][$ID]["pdfdoc-url-subfile"] = $WareInfos["pdfdoc-url-subfile"];
              }   

              
              // branches
              if (is_file($WareInfos["git-repos-path"]."/wareshub-data/gitstats.json"))
              {
                $Contents = file_get_contents($WareInfos["git-repos-path"]."/wareshub-data/gitstats.json");
                $Contents = utf8_encode($Contents);
                 
                $DecodedJSON = json_decode($Contents,true);
                 
                if (json_last_error() == JSON_ERROR_NONE)
                {
                  if (array_key_exists("branches",$DecodedJSON) &&
                      is_array($DecodedJSON["branches"]))
                  {
                    foreach ($DecodedJSON["branches"] as $Branch)
                    {
                      $Report[$TypeKey][$ID]["branches"][$Branch] = array();

                      $Pos = strpos($Branch,"openfluid-");
                      
                      if ($Pos !== false && $Pos == 0 &&
                          preg_match("#(\d+\.\d+(\.\d+)*)$#", $Branch, $MatchVersion))
                      {
                        array_push($Report[$TypeKey][$ID]["compat-versions"],$MatchVersion[0]);
                      }
                    }
                  }
                  
                  if (array_key_exists("committers",$DecodedJSON))
                    $Report[$TypeKey][$ID]["committers"] = $DecodedJSON["committers"];

                  if (array_key_exists("open-issues",$DecodedJSON))
                    $Report[$TypeKey][$ID]["open-issues"] = $DecodedJSON["open-issues"];
                  else
                  {
                    $Report[$TypeKey][$ID]["open-issues"] = array();
                    $Report[$TypeKey][$ID]["open-issues"]["bug"] = 0;
                    $Report[$TypeKey][$ID]["open-issues"]["feature"] = 0;
                    $Report[$TypeKey][$ID]["open-issues"]["review"] = 0;
                  }
                  
                }
              }
              rsort($Report[$TypeKey][$ID]["compat-versions"]);
            }
          }
        }    
      }
      ksort($Report[$TypeKey]);
    }
            
    return $Report;    
  }
  

  // =====================================================================
  // =====================================================================  
  
  
  public function getWebReportForBranch($WareType,$WareID,$Branch)
  {
    $Report = array();
    
    $WareInfos = $this->getWareInfos($WareType,$WareID);
    
    if (is_file($WareInfos["git-repos-path"]."/wareshub-data/".$Branch."/commits-history.json"))
    {
      $JSONContents = file_get_contents($WareInfos["git-repos-path"]."/wareshub-data/".$Branch."/commits-history.json");
      $JSONContents = utf8_encode($JSONContents);
       
      $DecodedJSON = json_decode($JSONContents,true);
             
      if (json_last_error() == JSON_ERROR_NONE)
      {
        $Report["commits-history"] = $DecodedJSON;
      }
    }
    
    if (is_file($WareInfos["git-repos-path"]."/wareshub-data/".$Branch."/wareshub.json"))
    {
      $JSONContents = file_get_contents($WareInfos["git-repos-path"]."/wareshub-data/".$Branch."/wareshub.json");
      $JSONContents = utf8_encode($JSONContents);
       
      $DecodedJSON = json_decode($JSONContents,true);
       
      if (json_last_error() == JSON_ERROR_NONE)
      {
        $Report["wareshub"] = $DecodedJSON;
        
        if (!array_key_exists("contacts",$Report["wareshub"]))
        {
          // contacts does not exists and is replaced by default
          $Report["wareshub"]["contacts"] = $this->ActiveDefsConfig["wares-contacts"];
        } 
        else
        {
          // remove empty contacts
          $Report["wareshub"]["contacts"] = array_filter($Report["wareshub"]["contacts"]);
          if(empty($Report["wareshub"]["contacts"]))
          {
            // contacts is empty and is replaced by default
            $Report["wareshub"]["contacts"] = $this->ActiveDefsConfig["wares-contacts"];
          }            
        } 
      }
    }
    
    return $Report;
  }	
  
}

?>