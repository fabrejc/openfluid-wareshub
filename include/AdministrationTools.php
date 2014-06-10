<?php 

include_once(__DIR__."/ReportingTools.php");

class AdministrationTools extends ReportingTools
{
  
  function __construct($ConfigDir)
  {
    parent::__construct($ConfigDir);  
  }  
  
  
  // =====================================================================
  // =====================================================================
  
  
  private function processTemplate($TplFile, $DestFile, $ExtraRepl = array())
  {
    $Replacements = array();
  
    $Replacements["@@OFWHUB_GITCORE_PATH@@"] = $this->ActiveDefsConfig["git-core-path"];
  
    $Replacements["@@OFWHUB_WARES_ROOTPATH@@"] = $this->ActiveDefsInstanceRootPath."/".
        $this->WHSystemConfig["instance"]["wares-git-rootdir"];
    $Replacements["@@OFWHUB_WARES_URLSUBDIR@@"] = $this->ActiveDefsConfig["url-git-subdir"];
    $Replacements["@@OFWHUB_WARES_SIMSUBDIR@@"] = $this->WHSystemConfig["general"]["simulators-dir"];
    $Replacements["@@OFWHUB_WARES_OBSSUBDIR@@"] = $this->WHSystemConfig["general"]["observers-dir"];
    $Replacements["@@OFWHUB_WARES_BEXTSUBDIR@@"] = $this->WHSystemConfig["general"]["builderexts-dir"];
  
    $Replacements["@@OFWHUB_APACHE_ROOTPATH@@"] = $this->ActiveDefsInstanceRootPath."/".
        $this->WHSystemConfig["instance"]["apache-conf-rootdir"];
  
    $Replacements["@@OFWHUB_WEB_ROOTPATH@@"] = $this->ActiveDefsPath."/".$this->WHSystemConfig["definitions"]["web-dir"];
    $Replacements["@@OFWHUB_WEB_URLSUBDIR@@"] = $this->ActiveDefsConfig["url-web-subdir"];
  
    $Replacements = array_replace($Replacements,$ExtraRepl);
  
    $Contents = file_get_contents($TplFile);
  
    $Contents = strtr($Contents,$Replacements);
  
    file_put_contents($DestFile,$Contents);
  }
  
  
  // =====================================================================
  // =====================================================================
  
  
  public function initInstance()
  {
    $TemplateToUse = $this->getTemplate("main-apache");

    // creation of directory stucture
     
    if (is_dir($this->ActiveDefsInstanceRootPath))
      throw new Exception("The instance directory already exists (".$this->ActiveDefsInstanceRootPath.")");
     
    if (!mkdir($this->ActiveDefsInstanceRootPath))
      throw new Exception("Unable to create instance directory (".$this->ActiveDefsInstanceRootPath.")");
     
    $WaresDir = $this->ActiveDefsInstanceRootPath."/".
        $this->WHSystemConfig["instance"]["wares-git-rootdir"];
     
    if (!mkdir($WaresDir))
      throw new Exception("Unable to create directory for wares (".$WaresDir.")");
     
    $DirToCreate = $WaresDir."/".$this->WHSystemConfig["general"]["simulators-dir"];
    if (!mkdir($DirToCreate))
      throw new Exception("Unable to create directory for simulators (".$DirToCreate.")");
     
    $DirToCreate = $WaresDir."/".$this->WHSystemConfig["general"]["observers-dir"];
    if (!mkdir($DirToCreate))
      throw new Exception("Unable to create directory for observers (".$DirToCreate.")");
     
    $DirToCreate = $WaresDir."/".$this->WHSystemConfig["general"]["builderexts-dir"];
    if (!mkdir($DirToCreate))
      throw new Exception("Unable to create directory for builder-extensions (".$DirToCreate.")");
     
    $ApacheDir = $this->ActiveDefsInstanceRootPath."/".$this->WHSystemConfig["instance"]["apache-conf-rootdir"];
    if (!mkdir($ApacheDir))
      throw new Exception("Unable to create directory for apache (".$ApacheDir.")");

    $DirToCreate = $ApacheDir."/".$this->WHSystemConfig["general"]["simulators-dir"];
    if (!mkdir($DirToCreate))
      throw new Exception("Unable to create directory for simulators (".$DirToCreate.")");

    $DirToCreate = $ApacheDir."/".$this->WHSystemConfig["general"]["observers-dir"];
    if (!mkdir($DirToCreate))
      throw new Exception("Unable to create directory for observers (".$DirToCreate.")");

    $DirToCreate = $ApacheDir."/".$this->WHSystemConfig["general"]["builderexts-dir"];
    if (!mkdir($DirToCreate))
      throw new Exception("Unable to create directory for builder-extensions (".$DirToCreate.")");
     
     
    // creation of apache config file from template

    $InstanceApacheMainFile = $this->ActiveDefsInstanceRootPath."/".
        $this->WHSystemConfig["instance"]["apache-conf-rootdir"]."/".
        $this->WHSystemConfig["instance"]["apache-conf-mainfile"];
    $this->processTemplate($TemplateToUse,$InstanceApacheMainFile);

  }
  
  
  // =====================================================================
  // =====================================================================
  
  
  public function updateInstance()
  {
    $TemplateToUse = $this->ActiveDefsPath."/".$this->WHSystemConfig["definitions"]["templates-dir"]."/".$this->WHSystemConfig["definitions"]["templates-files"]["main-apache"];
    
    if (!is_file($TemplateToUse))
      $TemplateToUse = $this->WHSystemPath."/".
      $this->WHSystemConfig["general"]["templates-dir"]."/".
      $this->WHSystemConfig["general"]["templates-files"]["main-apache"];
     
    if (!is_file($TemplateToUse))
      throw new Exception("Template file not found (".$TemplateToUse."). ");
         
    $InstanceApacheMainFile = $this->ActiveDefsInstanceRootPath."/".
        $this->WHSystemConfig["instance"]["apache-conf-rootdir"]."/".
        $this->WHSystemConfig["instance"]["apache-conf-mainfile"];
    
    $this->processTemplate($TemplateToUse,$InstanceApacheMainFile);

  }
  

  // =====================================================================
  // =====================================================================
  
  
  public function createDefinition($WareType,$WareID)
  {
    $WareInfos = $this->getWareInfos($WareType,$WareID);

    $TemplateToUse = $this->getTemplate("ware-def");
     
    $DestFile = "";
    $ExtraRepl = array();
    $ExtraRepl["@@OFWHUB_WARE_ID@@"] = $WareID;
     
    if (is_file($WareInfos["definition-file"]))
      throw new Exception("Ware definition already exists (".$WareInfos["definition-file"].")");
     
    $this->processTemplate($TemplateToUse,$WareInfos["definition-file"],$ExtraRepl);

  }

  // =====================================================================
  // =====================================================================
   
  
  public function updateDescription($WareInfos,$DefConfig)
  {
    file_put_contents($WareInfos["git-description-file"],$WareInfos["ware-id"]);
  }
   
   
  // =====================================================================
  // =====================================================================
   
   
  public function updateUsers($WareInfos,$DefConfig,&$ExtraRepl)
  {
    $WareID = $WareInfos["ware-id"];
     
    if (in_array("*",$DefConfig[$WareID]["users-rw"]))
    {
      $ExtraRepl["@@OFWHUB_WARE_ROUSERS_STRING@@"] = 
      "\t# Warning: everybody is granted to read\n\tRequire valid-user";
      $ExtraRepl["@@OFWHUB_WARE_RWUSERS_STRING@@"] = 
      "\t# Warning: everybody is granted to write\n\tRequire valid-user";
    }
    else
    {
      $RWUsers = $DefConfig[$WareID]["users-rw"];
      $ROUsers = $DefConfig[$WareID]["users-ro"];
      
      $this->processUsersGrants($ROUsers,$RWUsers);
       
      if (in_array("*",$ROUsers))
        $ExtraRepl["@@OFWHUB_WARE_ROUSERS_STRING@@"] = 
          "\t# Warning: everybody is granted to read\n\tRequire valid-user";
      else
        $ExtraRepl["@@OFWHUB_WARE_ROUSERS_STRING@@"] =
          "\t# Read only authorized users\n\tRequire user ".implode(" ",$ROUsers);
       
      $ExtraRepl["@@OFWHUB_WARE_RWUSERS_STRING@@"] =
        "\t# Read-write authorized users\n\tRequire user ".implode(" ",$RWUsers);
    }
  }
   
  
  // =====================================================================
  // =====================================================================
  

  public function updateWareApacheConfig($WareInfos,$DefConfig)
  {
    $ExtraRepl = array();
    $ExtraRepl["@@OFWHUB_WARE_ID@@"] = $WareInfos["ware-id"];
    
    $ExtraRepl["@@OFWHUB_WARE_ROUSERS_STRING@@"] = "";
    $ExtraRepl["@@OFWHUB_WARE_RWUSERS_STRING@@"] = "";
    
    $ExtraRepl["@@OFWHUB_WARES_WARETYPESUBDIR@@"] = $WareInfos["ware-type-subdir"];
     
    $this->updateUsers($WareInfos,$DefConfig,$ExtraRepl);
    
    $TemplateToUse = $this->getTemplate("ware-apache");
    $this->processTemplate($TemplateToUse,$WareInfos["apache-conf-file"],$ExtraRepl);    
  }
    
  
  // =====================================================================
  // =====================================================================
   
   
  public function updateMailinglist($WareInfos,$DefConfig)
  {
    $WareID = $WareInfos["ware-id"];

    $MailingList = array();

    if (array_key_exists("mailinglist",$DefConfig[$WareID]) &&
        is_array($DefConfig[$WareID]["mailinglist"]) &&
        !empty($DefConfig[$WareID]["mailinglist"]))
    {
      $MailingList = array_unique($DefConfig[$WareID]["mailinglist"]);
    }

    // add of global mailinglist config
    if (array_key_exists("wares-mailinglist",$this->ActiveDefsConfig) &&
        is_array($this->ActiveDefsConfig["wares-mailinglist"]) &&
        !empty($this->ActiveDefsConfig["wares-mailinglist"]))
    {
      $MailingList = array_unique(array_merge($MailingList,$this->ActiveDefsConfig["wares-mailinglist"]));
    }

    
    $GitCommand = "git";
    if (array_key_exists("git-tool-command", $this->ActiveDefsConfig))
      $GitCommand = $this->ActiveDefsConfig["git-tool-command"];

    $CWD = getcwd();

    chdir($WareInfos["git-repos-path"]);

    exec("$GitCommand config hooks.mailinglist \"".implode(",",$MailingList)."\"");
    exec("$GitCommand config hooks.emailprefix \"[".$this->ActiveDefsConfig["name"]."] \"");
    exec("$GitCommand config hooks.showrev \"\"");
    exec("$GitCommand config hooks.announcelist \"\"");

    chdir($CWD);
  }
   
   
  // =====================================================================
  // =====================================================================
   
   
  public function updateHooks($WareInfos,$DefConfig)
  {
    $HookSource = $this->ActiveDefsPath."/".$this->WHSystemConfig["definitions"]["githooks-dir"]."/post-receive";
    $HookTarget = $WareInfos["git-repos-path"]."/hooks/post-receive";
     
    if (file_exists($HookTarget))
      unlink($HookTarget);
  
    if (file_exists($HookSource))
      symlink($HookSource,$HookTarget);
  } 

  
  // =====================================================================
  // =====================================================================
      
  
  public function createWareGitRepository($WareInfos)
  {
    $GitCommand = "git";
    if (array_key_exists("git-tool-command", $this->ActiveDefsConfig))
      $GitCommand = $this->ActiveDefsConfig["git-tool-command"];
     
    return exec("$GitCommand --bare init ".$WareInfos["git-repos-path"]);    
  }  
  
}

?>