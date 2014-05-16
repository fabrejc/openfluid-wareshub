<?php 


include_once(__DIR__."/ConfigManager.php");


class CentralMachine
{
  private $WHSystemPath = "";
  private $WHSystemConfig = array();
  
  
  private $ActiveDefsPath = "";
  private $ActiveDefsInstanceRootPath = "";
  private $ActiveDefsConfig = array();

  
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
  
   
  private function showErrorAndExit($Msg)
  {
    echo "$Msg\n";
    exit(255);
  } 
   

  // =====================================================================
  // =====================================================================
  
   
  private function checkExpectedArgs($Args,$Count)
  {
    if (sizeof($Args) < $Count)
      $this->showErrorAndExit("Missing arguments for command. Aborting.");
  }
  
  
  // =====================================================================
  // =====================================================================
  
   
  private function checkMinimalWHSystemConfig()
  {
    if (!array_key_exists("general", $this->WHSystemConfig))
      $this->showErrorAndExit("Missing section \"general\" in configuration. Aborting.");
    
    if (!array_key_exists("simulators-dir", $this->WHSystemConfig["general"]))
      $this->showErrorAndExit("Missing item \"general/simulators-dir\" in configuration. Aborting.");
    
    if (!array_key_exists("observers-dir", $this->WHSystemConfig["general"]))
     $this->showErrorAndExit("Missing item \"general/observers-dir\" in configuration. Aborting.");
    
    if (!array_key_exists("builderexts-dir", $this->WHSystemConfig["general"]))
     $this->showErrorAndExit("Missing item \"general/builderexts-dir\" in configuration. Aborting.");
    
    if (!array_key_exists("templates-dir", $this->WHSystemConfig["general"]))
     $this->showErrorAndExit("Missing item \"general/templates-dir\" in configuration. Aborting.");

    
    if (!array_key_exists("definitions", $this->WHSystemConfig))
      $this->showErrorAndExit("Missing section \"definitions\" in configuration. Aborting.");        
    
    if (!array_key_exists("config-file", $this->WHSystemConfig["definitions"]))
      $this->showErrorAndExit("Missing item \"definitions/config-file\" in configuration. Aborting.");
    
    if (!array_key_exists("localconfig-file", $this->WHSystemConfig["definitions"]))
      $this->showErrorAndExit("Missing item \"definitions/localconfig-file\" in configuration. Aborting.");    
    
    if (!array_key_exists("waresdefs-dir", $this->WHSystemConfig["definitions"]))
      $this->showErrorAndExit("Missing item \"definitions/waresdefs-dir\" in configuration. Aborting.");
    
    if (!array_key_exists("web-dir", $this->WHSystemConfig["definitions"]))
      $this->showErrorAndExit("Missing item \"definitions/web-dir\" in configuration. Aborting.");    
    
    if (!array_key_exists("templates-dir", $this->WHSystemConfig["definitions"]))
      $this->showErrorAndExit("Missing item \"definitions/templates-dir\" in configuration. Aborting.");    

    
    if (!array_key_exists("instance", $this->WHSystemConfig))
      $this->showErrorAndExit("Missing section \"instance\" in configuration. Aborting.");
    
    if (!array_key_exists("wares-git-rootdir", $this->WHSystemConfig["instance"]))
      $this->showErrorAndExit("Missing item \"instance/wares-git-rootdir\" in configuration. Aborting.");

    if (!array_key_exists("apache-conf-rootdir", $this->WHSystemConfig["instance"]))
      $this->showErrorAndExit("Missing item \"instance/apache-conf-rootdir\" in configuration. Aborting.");

    if (!array_key_exists("apache-conf-mainfile", $this->WHSystemConfig["instance"]))
      $this->showErrorAndExit("Missing item \"instance/apache-conf-mainfile\" in configuration. Aborting.");
    
    
    if (!array_key_exists("repositories", $this->WHSystemConfig["definitions"]))
     $this->showErrorAndExit("Missing section \"definitions/repositories\" in configuration. Aborting.");    
  }  
  
  
  // =====================================================================
  // =====================================================================

  
  private function checkMinimalActiveDefsConfig()
  {
    $this->checkMinimalWHSystemConfig();

    if (!array_key_exists("instance-path", $this->ActiveDefsConfig))
      $this->showErrorAndExit("Missing item \"instance-path\" in configuration. Aborting.");    
    
    if (!array_key_exists("url-web-subdir", $this->ActiveDefsConfig))
      $this->showErrorAndExit("Missing item \"url-web-subdir\" in configuration. Aborting.");    
    
    if (!array_key_exists("url-git-subdir", $this->ActiveDefsConfig))
      $this->showErrorAndExit("Missing item \"url-git-subdir\" in configuration. Aborting.");
    
    if (!array_key_exists("git-core-path", $this->ActiveDefsConfig))
      $this->showErrorAndExit("Missing item \"git-core-path\" in configuration. Aborting.");
  }
  
  
  // =====================================================================
  // =====================================================================
  
  private function getTemplate($TplKey)
  {  
    $TemplateToUse = $this->ActiveDefsPath."/".$this->WHSystemConfig["definitions"]["templates-dir"]."/".$this->WHSystemConfig["definitions"]["templates-files"][$TplKey];
  
    if (!is_file($TemplateToUse))
      $TemplateToUse = $this->WHSystemPath."/".$this->WHSystemConfig["general"]["templates-dir"]."/".$this->WHSystemConfig["general"]["templates-files"]["$TplKey"];
   
    if (!is_file($TemplateToUse))
      $this->showErrorAndExit("Template file not found (".$TemplateToUse."). Aborting.");
    
    return $TemplateToUse;
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
  
  
  function setActiveDefsRepository($Name)
  {
    $this->checkMinimalWHSystemConfig(); 
   
    if (array_key_exists($Name,$this->WHSystemConfig["definitions"]["repositories"]))
    {
     $this->ActiveDefsPath = $this->WHSystemConfig["definitions"]["repositories"][$Name];
     
     $ConfigMan = new ConfigManager($this->ActiveDefsPath.
                                    "/".$this->WHSystemConfig["definitions"]["config-dir"].
                                    "/config.json");
     
     if (file_exists($this->ActiveDefsPath."/localconfig.json"))
       $ConfigMan->appendFiles($this->ActiveDefsPath.
                               "/".$this->WHSystemConfig["definitions"]["config-dir"].
                               "/localconfig.json");
     
     $this->ActiveDefsConfig = $ConfigMan->getConfig();

     $this->checkMinimalActiveDefsConfig();
     
     $this->ActiveDefsInstanceRootPath = $this->ActiveDefsConfig["instance-path"];
     
    }
    else
      $this->showErrorAndExit("Unknow definitions repository named \"$Name\". Aborting.");      
  }
   

  // =====================================================================
  // =====================================================================
  
  
  function processWHSystemCommand($Args)
  {    
    $this->checkExpectedArgs($Args,1);
    
    if ($Args[0] == "displayconfig")
      print_r($this->WHSystemConfig);
    else if ($Args[0] == "checkconfig")
    {
     $this->checkMinimalWHSystemConfig();
     echo "System configuration is OK\n";
    }
    else      
     $this->showErrorAndExit("unknown command \"$Args[0]\". Aborting.");
  }

  
  // =====================================================================
  // =====================================================================
  
   
  function processActiveDefsCommand($Args)
  {
    $this->checkExpectedArgs($Args,1);
    
    if ($Args[0] == "displayconfig")
     print_r($this->ActiveDefsConfig);
    else if ($Args[0] == "checkconfig")
    {
      $this->checkMinimalActiveDefsConfig();
      echo "Definitions configuration is OK\n";     
    }
    else if ($Args[0] == "initinstance")
     $this->processInitInstance(array_slice($Args,1));
    else if ($Args[0] == "updateinstance")
     $this->processUpdateInstance(array_slice($Args,1));    
    else if ($Args[0] == "createdef")
     $this->processCreateDef(array_slice($Args,1));
    else if ($Args[0] == "createware")
     $this->processCreateWare(array_slice($Args,1));
    else if ($Args[0] == "updateware")
     $this->processUpdateWare(array_slice($Args,1));
    else 
     $this->showErrorAndExit("unknown command \"$Args[0]\". Aborting.");
   }
   

   // =====================================================================
   // =====================================================================
   
   
   private function processInitInstance($Args)
   {
     echo "== Creating instance in $this->ActiveDefsInstanceRootPath\n";
     
     $TemplateToUse = $this->getTemplate("main-apache");

     echo "Apache template: $TemplateToUse\n";
          
     // creation of directory stucture 
     
     if (is_dir($this->ActiveDefsInstanceRootPath))
       $this->showErrorAndExit("The instance directory already exists (".$this->ActiveDefsInstanceRootPath."). Aborting.");
     
     if (!mkdir($this->ActiveDefsInstanceRootPath))
       $this->showErrorAndExit("Unable to create instance directory (".$this->ActiveDefsInstanceRootPath."). Aborting.");
     
     $WaresDir = $this->ActiveDefsInstanceRootPath."/".
                 $this->WHSystemConfig["instance"]["wares-git-rootdir"];
     
     if (!mkdir($WaresDir))
       $this->showErrorAndExit("Unable to create directory for wares (".$WaresDir."). Aborting.");
     
     $DirToCreate = $WaresDir."/".$this->WHSystemConfig["general"]["simulators-dir"];
     if (!mkdir($DirToCreate))
       $this->showErrorAndExit("Unable to create directory for simulators (".$DirToCreate."). Aborting.");
     
     $DirToCreate = $WaresDir."/".$this->WHSystemConfig["general"]["observers-dir"];
     if (!mkdir($DirToCreate))
       $this->showErrorAndExit("Unable to create directory for observers (".$DirToCreate."). Aborting.");
     
     $DirToCreate = $WaresDir."/".$this->WHSystemConfig["general"]["builderexts-dir"];
     if (!mkdir($DirToCreate))
       $this->showErrorAndExit("Unable to create directory for builder-extensions (".$DirToCreate."). Aborting.");
     
     $ApacheDir = $this->ActiveDefsInstanceRootPath."/".$this->WHSystemConfig["instance"]["apache-conf-rootdir"];
     if (!mkdir($ApacheDir))
       $this->showErrorAndExit("Unable to create directory for apache (".$ApacheDir."). Aborting.");
      
     $DirToCreate = $ApacheDir."/".$this->WHSystemConfig["general"]["simulators-dir"];
     if (!mkdir($DirToCreate))
       $this->showErrorAndExit("Unable to create directory for simulators (".$DirToCreate."). Aborting.");
      
     $DirToCreate = $ApacheDir."/".$this->WHSystemConfig["general"]["observers-dir"];
     if (!mkdir($DirToCreate))
       $this->showErrorAndExit("Unable to create directory for observers (".$DirToCreate."). Aborting.");
      
     $DirToCreate = $ApacheDir."/".$this->WHSystemConfig["general"]["builderexts-dir"];
     if (!mkdir($DirToCreate))
       $this->showErrorAndExit("Unable to create directory for builder-extensions (".$DirToCreate."). Aborting.");
     
     
     // creation of apache config file from template

     $InstanceApacheMainFile = $this->ActiveDefsInstanceRootPath."/".
                               $this->WHSystemConfig["instance"]["apache-conf-rootdir"]."/".
                               $this->WHSystemConfig["instance"]["apache-conf-mainfile"];
     $this->processTemplate($TemplateToUse,$InstanceApacheMainFile);

     echo "== Completed\n\n";
      
     echo "Do not forget to reload apache configuration :\n";
     echo "   sudo service apache2 reload\n";
      
     echo "\n";
     
   }
   
   
   // =====================================================================
   // =====================================================================

   
   private function processUpdateInstance($Args)
   {
     echo "== Updating main apache configuration\n";
     
     $TemplateToUse = $this->ActiveDefsPath."/".$this->WHSystemConfig["definitions"]["templates-dir"]."/".$this->WHSystemConfig["definitions"]["templates-files"]["main-apache"];

     echo "Apache template: $TemplateToUse\n";
     
     if (!is_file($TemplateToUse))
       $TemplateToUse = $this->WHSystemPath."/".$this->WHSystemConfig["general"]["templates-dir"]."/".$this->WHSystemConfig["general"]["templates-files"]["main-apache"];
     
     if (!is_file($TemplateToUse))
       $this->showErrorAndExit("Template file not found (".$TemplateToUse."). Aborting.");
     
     $InstanceApacheMainFile = $this->ActiveDefsInstanceRootPath."/".
         $this->WHSystemConfig["instance"]["apache-conf-rootdir"]."/".
         $this->WHSystemConfig["instance"]["apache-conf-mainfile"];
     $this->processTemplate($TemplateToUse,$InstanceApacheMainFile);
        
     echo "== Completed\n\n";
     
     echo "Do not forget to reload apache configuration :\n";
     echo "   sudo service apache2 reload\n";
     
     echo "\n";
   }
    
   // =====================================================================
   // =====================================================================

   
   private function getWareInfos($WareType,$WareID)
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
       $this->showErrorAndExit("Unknown ware type (".$WareType."). Aborting.");

     
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
   
   
   private function processCreateDef($Args)
   {
     $this->checkExpectedArgs($Args,2);      
     
     $WareInfos = $this->getWareInfos($Args[0],$Args[1]);
     
     echo "== Creating definition for $Args[0] \"$Args[1]\"\n";
     
     $TemplateToUse = $this->getTemplate("ware-def");
     
     echo "Definition template: $TemplateToUse\n";
     
     $DestFile = "";
     $ExtraRepl = array();
     $ExtraRepl["@@OFWHUB_WARE_ID@@"] = $Args[1];
     
     if (is_file($WareInfos["definition-file"]))
       $this->showErrorAndExit("Ware definition already exists (".$WareInfos["definition-file"]."). Aborting.");
     
     $this->processTemplate($TemplateToUse,$WareInfos["definition-file"],$ExtraRepl);

     echo "== Completed\n";
     
     echo "\n";
   }
   
   
   // =====================================================================
   // =====================================================================   

   
   private function loadAndCheckDefinition($WareInfos)
   {
     $DefContents = file_get_contents($WareInfos["definition-file"]);
     $DefContents = utf8_encode($DefContents);
     $DefConfig = json_decode($DefContents,true);
      
     $WareID = $WareInfos["ware-id"];
     
     if (!array_key_exists($WareID,$DefConfig))
       $this->showErrorAndExit("Ware ID error in definition file (".$WareInfos["definition-file"]."). Aborting.");

     if (!array_key_exists("users-ro",$DefConfig[$WareID]))
       $this->showErrorAndExit("Missing read-only users in definition file (".$WareInfos["definition-file"]."). Aborting.");

     if (!is_array($DefConfig[$WareID]["users-ro"]))
         $this->showErrorAndExit("Wrong format for read-only users in definition file (".$WareInfos["definition-file"]."). Aborting.");
     
     if (!array_key_exists("users-rw",$DefConfig[$WareID]))
       $this->showErrorAndExit("Missing read-write users in definition file (".$WareInfos["definition-file"]."). Aborting.");

     if (!is_array($DefConfig[$WareID]["users-rw"]))
       $this->showErrorAndExit("Wrong format for read-write users in definition file (".$WareInfos["definition-file"]."). Aborting.");

     
     return $DefConfig;
   }
   
   
   // =====================================================================
   // =====================================================================
     
   
   
   private function updateDescription($WareInfos,$DefConfig)
   {
     if (array_key_exists("description",$DefConfig[$WareInfos["ware-id"]]))
       file_put_contents($WareInfos["git-description-file"],$DefConfig[$WareInfos["ware-id"]]["description"]);      
   }
   
   
   // =====================================================================
   // =====================================================================
   
   
   private function updateUsers($WareInfos,$DefConfig,&$ExtraRepl)
   {
     $WareID = $WareInfos["ware-id"];
     
     if (in_array("*",$DefConfig[$WareID]["users-rw"]))
     {
       $ExtraRepl["@@OFWHUB_WARE_ROUSERS_STRING@@"] = "\t# Warning: everybody is granted to read";
       $ExtraRepl["@@OFWHUB_WARE_RWUSERS_STRING@@"] = "\t# Warning: everybody is granted to write";
     }
     else
     {
       $RWUsers = array_unique($DefConfig[$WareID]["users-rw"]);
       
       // add of global rw users
       if (array_key_exists("wares-users-rw",$this->ActiveDefsConfig));
         $RWUsers = array_unique(array_merge($RWUsers,$this->ActiveDefsConfig["wares-users-rw"]));
       
       $ROUsers = array_unique($DefConfig[$WareID]["users-ro"]);
       
       // add of global ro users
       if (array_key_exists("wares-users-ro",$this->ActiveDefsConfig));
         $ROUsers = array_unique(array_merge($ROUsers,$this->ActiveDefsConfig["wares-users-ro"]));
        
       $ROUsers = array_unique(array_merge($ROUsers,$RWUsers));
             
       if (in_array("*",$DefConfig[$WareID]["users-ro"]))
         $ExtraRepl["@@OFWHUB_WARE_ROUSERS_STRING@@"] = "\t# Warning: everybody is granted to read";
       else
         $ExtraRepl["@@OFWHUB_WARE_ROUSERS_STRING@@"] = 
           "\t# Read only authorized users\n\t<Limit GET HEAD OPTIONS PROPFIND>\n\t\tRequire user ".implode(" ",$ROUsers)."\n\t</Limit>";
       
       $ExtraRepl["@@OFWHUB_WARE_RWUSERS_STRING@@"] =
         "\t# Read-write authorized users\n\t<LimitExcept GET HEAD OPTIONS PROPFIND>\n\t\tRequire user ".implode(" ",$RWUsers)."\n\t</LimitExcept>";
     }      
   }
   
   
   // =====================================================================
   // =====================================================================
   
   
   private function updateMailinglist($WareInfos,$DefConfig)
   {
     $WareID = $WareInfos["ware-id"];
     
     if (array_key_exists("mailinglist",$DefConfig[$WareID]) &&
         is_array($DefConfig[$WareID]["mailinglist"]) &&
         !empty($DefConfig[$WareID]["mailinglist"]))
     {
             
       if (!file_exists($WareInfos["git-repos-path"]."/hooks/post-receive") &&
           array_key_exists("git-emailhook-path",$this->ActiveDefsConfig))
       {
         symlink($this->ActiveDefsConfig["git-emailhook-path"],$WareInfos["git-repos-path"]."/hooks/post-receive");
       }

       $GitCommand = "git";
       if (array_key_exists("git-tool-command", $this->ActiveDefsConfig))
         $GitCommand = $this->ActiveDefsConfig["git-tool-command"];

       $CWD = getcwd();

       chdir($WareInfos["git-repos-path"]);

       exec("$GitCommand config hooks.mailinglist \"".implode(" ",$DefConfig[$WareID]["mailinglist"])."\"");
       exec("$GitCommand config hooks.emailprefix \"[".$this->ActiveDefsConfig["name"]."] $WareID - \"");
       exec("$GitCommand config hooks.showrev \"\"");
       exec("$GitCommand config hooks.announcelist \"\"");

       chdir($CWD);
        
     }
   }
   
   
   // =====================================================================
   // =====================================================================
   
   
   private function processCreateWare($Args)
   {
     $this->checkExpectedArgs($Args,2);      
     
     echo "== Creating $Args[0] \"$Args[1]\"\n";
     
     $WareInfos = $this->getWareInfos($Args[0],$Args[1]);
     
     $TemplateToUse = $this->getTemplate("ware-apache");
    
     $ExtraRepl = array();
     $ExtraRepl["@@OFWHUB_WARE_ID@@"] = $Args[1];
     
     $ExtraRepl["@@OFWHUB_WARE_ROUSERS_STRING@@"] = "";
     $ExtraRepl["@@OFWHUB_WARE_RWUSERS_STRING@@"] = "";
     
     $ExtraRepl["@@OFWHUB_WARES_WARETYPESUBDIR@@"] = $WareInfos["ware-type-subdir"];
     
     
     if (!is_file($WareInfos["definition-file"]))
       $this->showErrorAndExit("Ware definition does not exist (".$Infos["definition-file"]."). Aborting.");
              
     if (is_dir($WareInfos["git-repos-path"]))
       $this->showErrorAndExit("Git repository path already exists (".$WareInfos["git-repos-path"]."). Aborting.");
     

     // read definition    
     $DefConfig = $this->loadAndCheckDefinition($WareInfos);
     echo "Definition file: ${WareInfos["definition-file"]}\n";
     
          
     // create git repository

     echo "-- Creating git repository\n";
     
     $GitCommand = "git";
     if (array_key_exists("git-tool-command", $this->ActiveDefsConfig))
       $GitCommand = $this->ActiveDefsConfig["git-tool-command"];
     
     echo exec("$GitCommand --bare init ".$WareInfos["git-repos-path"])."\n";        
     
     // update git description
     echo "-- Updating git description\n";
     $this->updateDescription($WareInfos,$DefConfig);
     
     // update mailinglist
     echo "-- Updating git mailinglist\n";
     $this->updateMailinglist($WareInfos,$DefConfig);     
     
     echo "-- Creating apache configuration\n";
     
     echo "Apache template: $TemplateToUse\n";

     // update users
     $this->updateUsers($WareInfos,$DefConfig,$ExtraRepl);
      
     // create apache config file
     $this->processTemplate($TemplateToUse,$WareInfos["apache-conf-file"],$ExtraRepl);
     
     echo "== Completed\n\n";
     
     echo "Do not forget to fix the files owner :\n";
     echo "   sudo chgrp www-data ${WareInfos["git-repos-path"]} -R\n";
     echo "And reload apache configuration :\n";
     echo "   sudo service apache2 reload\n";
     
     echo "\n";     
   }
   
   
   // =====================================================================
   // =====================================================================
   
   
   private function processUpdateWare($Args)
   {
     $RestartApache = false;
     
     $this->checkExpectedArgs($Args,3);      
     
     $WareInfos = $this->getWareInfos($Args[0],$Args[1]);

     if (!is_file($WareInfos["definition-file"]))
       $this->showErrorAndExit("Ware definition does not exist (".$Infos["definition-file"]."). Aborting.");
      
     echo "== Updating $Args[0] \"$Args[1]\"\n";
     
     $DefConfig = $this->loadAndCheckDefinition($WareInfos);
     echo "Using definition file : ${WareInfos["definition-file"]}\n";      
     

     if ($Args[2] == "users" || $Args[2] == "allsettings")
     {
       echo "-- Updating users\n";
       
       $ExtraRepl = array();
       $ExtraRepl["@@OFWHUB_WARE_ID@@"] = $Args[1];
        
       $ExtraRepl["@@OFWHUB_WARE_ROUSERS_STRING@@"] = "";
       $ExtraRepl["@@OFWHUB_WARE_RWUSERS_STRING@@"] = "";
        
       $ExtraRepl["@@OFWHUB_WARES_WARETYPESUBDIR@@"] = $WareInfos["ware-type-subdir"];
               
       $this->updateUsers($WareInfos,$DefConfig,$ExtraRepl);

       $TemplateToUse = $this->getTemplate("ware-apache");
       $this->processTemplate($TemplateToUse,$WareInfos["apache-conf-file"],$ExtraRepl);
       
       $RestartApache = true;
        
     }
     
     if ($Args[2] == "description" || $Args[2] == "allsettings")
     {
       echo "-- Updating description\n";
       $this->updateDescription($WareInfos,$DefConfig);
     }
     
     if ($Args[2] == "mailinglist" || $Args[2] == "allsettings")
     {
       echo "-- Updating mailinglist\n";
       $this->updateMailinglist($WareInfos,$DefConfig);
     }
     
     echo "== Completed\n\n";
     
     
     if ($RestartApache)
     {
       echo "Do not forget to reload apache configuration :\n";
       echo "   sudo service apache2 reload\n";
       echo "\n";
     }
         
   } 
}

?>
