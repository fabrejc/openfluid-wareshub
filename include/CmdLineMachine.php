<?php 


include_once(__DIR__."/AdministrationTools.php");


class CmdLineMachine
{

  private $AdminTools;


  function __construct($ConfigDir)
  {
    try
    {
      $this->AdminTools = new AdministrationTools($ConfigDir);
    }
    catch (Exception $E)
    {
      $this->showErrorAndExit($E->getMessage());
    }
     
  }


  // =====================================================================
  // =====================================================================

   
  private function showErrorAndExit($Msg)
  {
    echo "$Msg. Aborting.\n";
    exit(255);
  }
   

  // =====================================================================
  // =====================================================================

   
  private function checkExpectedArgs($Args,$Count)
  {
    print_r($Args);
    
    if (sizeof($Args) < $Count)
      $this->showErrorAndExit("Missing arguments for command");
  }


  // =====================================================================
  // =====================================================================


  public function setActiveDefinitionsSet($SetPath)
  {
    try
    {
      $this->AdminTools->setActiveDefinitionsSet($SetPath);
    }
    catch (Exception $E)
    {
      $this->showErrorAndExit($E->getMessage());
    }
  }


  // =====================================================================
  // =====================================================================


  function processWHSystemCommand($Args)
  {
    $this->checkExpectedArgs($Args,1);

    if ($Args[0] == "showconfig")
      print_r($this->AdminTools->getWHSystemConfig());
    else if ($Args[0] == "checkconfig")
    {
      try
      {
        $this->AdminTools->checkMinimalWHSystemConfig();
      }
      catch (Exception $E)
      {
        $this->showErrorAndExit($E->getMessage());
      }
      echo "System configuration is OK\n";
    }
    else
      $this->showErrorAndExit("unknown command \"$Args[0]\"");
  }


  // =====================================================================
  // =====================================================================

   
  function processActiveDefsCommand($Args)
  {
    $this->checkExpectedArgs($Args,1);

    try
    {
      if ($Args[0] == "showconfig")
        print_r($this->AdminTools->getActiveDefsConfig());
      else if ($Args[0] == "checkconfig")
      {
        $this->AdminTools->checkMinimalActiveDefsConfig();
        echo "Definitions configuration is OK\n";
      }
      else if ($Args[0] == "initinstance")
        $this->processInitInstance(array_slice($Args,1));
      else if ($Args[0] == "updateinstance")
        $this->processUpdateInstance(array_slice($Args,1));
      else if ($Args[0] == "showreport")
        $this->processShowReport();      
      else if ($Args[0] == "createdef")
        $this->processCreateDef(array_slice($Args,1));
      else if ($Args[0] == "createware")
        $this->processCreateWare(array_slice($Args,1));
      else if ($Args[0] == "updateware")
        $this->processUpdateWare(array_slice($Args,1));
      else
        $this->showErrorAndExit("unknown command \"$Args[0]\"");
    }
    catch (Exception $E)
    {
      $this->showErrorAndExit($E->getMessage());
    }
  }
   

  // =====================================================================
  // =====================================================================
   
   
  private function processInitInstance($Args)
  {
    echo "== Creating instance in ".$this->AdminTools->getActiveDefsInstanceRootPath()."\n";
     
     
    try
    {
      $this->AdminTools->initInstance();
    }
    catch (Exception $E)
    {
      $this->showErrorAndExit($E->getMessage());
    }

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

    try
    {
      $this->AdminTools->updateInstance();
    }
    catch (Exception $E)
    {
      $this->showErrorAndExit($E->getMessage());
    }

    echo "== Completed\n\n";
     
    echo "Do not forget to reload apache configuration :\n";
    echo "   sudo service apache2 reload\n";
     
    echo "\n";
  }

   
  // =====================================================================
  // =====================================================================
   

  private function processShowReport()
  {
    try
    {
      $Report = $this->AdminTools->getReport();
      
      $WaresTypes = array("simulator","observer","builderext");
      
      foreach ($WaresTypes as $Type)
      {
        $TypeKey = $Type."s";
        
        echo $TypeKey."\n";
        echo "  - ".sizeof($Report[$TypeKey]["instanciated"])." instanciated\n";
        echo "  - ".sizeof($Report[$TypeKey]["uninstanciated"])." uninstanciated\n";
        
        if (!empty($Report[$TypeKey]["missing-apacheconf"]))
        {
          echo "  - /!\ missing apache configuration file\n";
          
          foreach ($Report[$TypeKey]["missing-apacheconf"] as $WareID)
            echo "          $WareID\n";
        }

        if (!empty($Report[$TypeKey]["missing-gitrepos"]))
        {
          echo "  - /!\ missing git repository\n";
        
          foreach ($Report[$TypeKey]["missing-gitrepos"] as $WareID)
            echo "          $WareID\n";
        }        
        
        if (!empty($Report[$TypeKey]["extra-apacheconf-files"]))
        {
          echo "  - /!\ unknown apache configuration file(s)\n";
        
          foreach ($Report[$TypeKey]["extra-apacheconf-files"] as $WareID)
            echo "          $WareID\n";
        }
        
        if (!empty($Report[$TypeKey]["extra-gitrepos-dirs"]))
        {
          echo "  - /!\ unknown git repository(ies)\n";
        
          foreach ($Report[$TypeKey]["extra-gitrepos-dirs"] as $WareID)
            echo "          $WareID\n";
        }        
        
      }
    }
    catch (Exception $E)
    {
      $this->showErrorAndExit($E->getMessage());
    }    
  }
  
  
  // =====================================================================
  // =====================================================================
  
   
  private function processCreateDef($Args)
  {
    $this->checkExpectedArgs($Args,2);

    echo "== Creating definition for $Args[0] \"$Args[1]\"\n";
     
    try
    {
      $this->AdminTools->createDefinition($Args[0],$Args[1]);
    }
    catch (Exception $E)
    {
      $this->showErrorAndExit($E->getMessage());
    }
     
    echo "== Completed\n";
     
    echo "\n";
  }
      
   
  // =====================================================================
  // =====================================================================


  private function processCreateWare($Args)
  {
    $this->checkExpectedArgs($Args,2);

    echo "== Creating $Args[0] \"$Args[1]\"\n";

    $WareInfos = $this->AdminTools->getWareInfos($Args[0],$Args[1]);

    if (!is_file($WareInfos["definition-file"]))
      $this->showErrorAndExit("Ware definition does not exist (".$WareInfos["definition-file"].")");

    if (is_dir($WareInfos["git-repos-path"]))
      $this->showErrorAndExit("Git repository path already exists (".$WareInfos["git-repos-path"].")");


    try
    {
      // read definition
      $DefConfig = $this->AdminTools->loadAndCheckDefinition($WareInfos);
      echo "Definition file: ${WareInfos["definition-file"]}\n";


      // create git repository
      echo "-- Creating git repository\n";
      $this->AdminTools->createWareGitRepository($WareInfos);

      // update git description
      echo "-- Updating git description\n";
      $this->AdminTools->updateDescription($WareInfos,$DefConfig);

      // update mailinglist
      echo "-- Updating git mailinglist\n";
      $this->AdminTools->updateMailinglist($WareInfos,$DefConfig);

      // update hooks
      echo "-- Updating git hooks\n";
      $this->AdminTools->updateHooks($WareInfos,$DefConfig);

      echo "-- Creating apache configuration\n";
      // update users in apache config
      $this->AdminTools->updateWareApacheConfig($WareInfos,$DefConfig);

      echo "== Completed\n\n";

      echo "Do not forget to fix the files owner :\n";
      echo "   sudo chgrp www-data ${WareInfos["git-repos-path"]} -R\n";
      echo "And reload apache configuration :\n";
      echo "   sudo service apache2 reload\n";
      echo "\n";
    }
    catch (Exception $E)
    {
      $this->showErrorAndExit($E->getMessage());
    }
  }


  // =====================================================================
  // =====================================================================
  
  
  private function processUpdateWare($Args)
  {
    $RestartApache = false;
     
    $this->checkExpectedArgs($Args,3);

    $WareType = $Args[0];
    $Action = $Args[2];
    $TypeKey = $WareType."s";
    
    $WaresToProcess = array();
    
    if ($Args[1] == "...")
    {
      $WHSystemConfig = $this->AdminTools->getWHSystemConfig();
      
      $DefsPath = $this->AdminTools->getActiveDefsPath()."/".
          $WHSystemConfig["definitions"]["waresdefs-dir"]."/".
          $WHSystemConfig["general"][$TypeKey."-dir"];
      
      $DirHandle  = opendir($DefsPath);
      while (false !== ($FileName = readdir($DirHandle)))
      {
        $CurrentFile = $DefsPath."/".$FileName;
      
        $PathParts = pathinfo($CurrentFile);
      
        if (is_file($CurrentFile) &&
            array_key_exists("extension",$PathParts) &&
            $PathParts["extension"] == "json")
        {
          array_push($WaresToProcess,$PathParts["filename"]);
        }
      }      
    }
    else
    {
      array_push($WaresToProcess,$Args[1]);
    }
    
    foreach ($WaresToProcess as $WareID)
    {

      $WareInfos = $this->AdminTools->getWareInfos($WareType,$WareID);

      if (!is_file($WareInfos["definition-file"]))
        $this->showErrorAndExit("Ware definition does not exist (".$WareInfos["definition-file"].")");

      if (is_file($WareInfos["apache-conf-file"]) && is_dir($WareInfos["git-repos-path"]))
      {
      
        echo "== Updating $WareType \"$WareID\"\n";
         
        $DefConfig = $this->AdminTools->loadAndCheckDefinition($WareInfos);
        echo "Using definition file : ${WareInfos["definition-file"]}\n";
         
        try
        {
           
          if ($Action == "users" || $Action == "allsettings")
          {
            echo "-- Updating users\n";
            $this->AdminTools->updateWareApacheConfig($WareInfos,$DefConfig);
            $RestartApache = true;
          }
           
          if ($Action == "description" || $Action == "allsettings")
          {
            echo "-- Updating git description\n";
            $this->AdminTools->updateDescription($WareInfos,$DefConfig);
          }
           
          if ($Action == "mailinglist" || $Action == "allsettings")
          {
            echo "-- Updating git mailinglist\n";
            $this->AdminTools->updateMailinglist($WareInfos,$DefConfig);
          }
           
          if ($Action == "hooks" || $Action == "allsettings")
          {
            echo "-- Updating git hooks\n";
            $this->AdminTools->updateHooks($WareInfos,$DefConfig);
          }

        }
        catch (Exception $E)
        {
          $this->showErrorAndExit($E->getMessage());
        }
         
        echo "== Completed\n\n";
      }
    }
     
    if ($RestartApache)
    {
      echo "Do not forget to reload apache configuration :\n";
      echo "   sudo service apache2 reload\n";
      echo "\n";
    }
     
  }
}

?>
