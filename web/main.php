<?php


session_start();


$CurrentWareType="simulator";
if (isset($_REQUEST["waretype"]))
{
  $CurrentWareType=$_REQUEST["waretype"];
}

if (isset($_REQUEST["reset"]))
{
  session_destroy();
  header("Location: ".$_SERVER["SCRIPT_NAME"]."?waretype=".$CurrentWareType);
}


#######


$WHSytemRootPath = realpath(__DIR__."/..");
$DefsSetRootPath = realpath(dirname($_SERVER["SCRIPT_FILENAME"])."/..");

include_once($WHSytemRootPath."/include/ReportingTools.php");

$RTools = new ReportingTools($WHSytemRootPath."/config");
$RTools->setActiveDefinitionsSet($DefsSetRootPath);


if (!isset($_SESSION["wareshub"]))
{  
  $Report = $RTools->getWebReport();
  
  $_SESSION["wareshub"] = array();
  $_SESSION["wareshub"]["reporting"] = array();
  $_SESSION["wareshub"]["reporting"]["simulators"] = $Report["simulators"];
  $_SESSION["wareshub"]["reporting"]["observers"] = $Report["observers"];
  $_SESSION["wareshub"]["reporting"]["builderexts"] = $Report["builderexts"];
  $_SESSION["wareshub"]["dirs"] = array();
  $_SESSION["wareshub"]["dirs"]["system-root"] = $WHSytemRootPath;
  $_SESSION["wareshub"]["dirs"]["system-config"] = $WHSytemRootPath."/config";
  $_SESSION["wareshub"]["dirs"]["system-web"] = $WHSytemRootPath."/web";
  $_SESSION["wareshub"]["dirs"]["defsset-root"] = $DefsSetRootPath;  
  $_SESSION["wareshub"]["dirs"]["defsset-web"] = $DefsSetRootPath."/web";
  $_SESSION["wareshub"]["definitions-config"] = $RTools->getActiveDefsConfig();
  
  
  if (!isset($DefsSetTitle))
    $DefsSetTitle = "OpenFLUID-WaresHub";
  
  $_SESSION["wareshub"]["labels"]["defsset-title"] = $DefsSetTitle;
  
  if (!isset($DefsSetIntro))
    $DefsSetIntro = "This OpenFLUID WaresHub hosts simulators, observers and builder-extensions for OpenFLUID";
  
  $_SESSION["wareshub"]["labels"]["defsset-intro"] = $DefsSetIntro;
  
  if (!isset($DefsSetFooter))
    $DefsSetFooter = $DefsSetTitle;
  
  $_SESSION["wareshub"]["labels"]["defsset-footer"] = $DefsSetFooter;
  
  
  if (!isset($DefsSetGitHostname))
    $DefsSetGitHostname = $_SERVER["SERVER_ADDR"];
    
  $_SESSION["wareshub"]["url"]["defsset-githost"] = $DefsSetGitHostname; 
  
  if (isset($DefsSetGitProtocol))
    $_SESSION["wareshub"]["url"]["defsset-gitprotocol"] = $DefsSetGitProtocol;
}  


#######



$Page = NULL;


if (isset($_REQUEST["wareid"]))
{
  include_once(__DIR__."/include/WarePageLayout.php");
  
  $Page = new WarePageLayout();
  
  $Page->setWareType($CurrentWareType);
  $Page->setWareID($_REQUEST["wareid"]);
  $CurrentBranch = "";


  if (isset($_REQUEST["warebranch"]))
  {
    $CurrentBranch = $_REQUEST["warebranch"];
  }
  else
  {
    $WareData = $_SESSION["wareshub"]["reporting"][$_REQUEST["waretype"]."s"][$_REQUEST["wareid"]];



    if (!empty($WareData["compat-versions"]))
    {
      // take the higher compat version as default branch
      $CurrentBranch = "openfluid-".$WareData["compat-versions"][0];
      $Page->setWareBranch($CurrentBranch);
    }
    else if (!empty($WareData["branches"]))
    {
      // take the first branch
      reset($WareData["branches"]);
      $CurrentBranch = key($WareData["branches"]);
    }
  }

  if (! empty ( $CurrentBranch ))
  {
    if (empty ( $WareData ["branches"] [$CurrentBranch] ))
    {
      $_SESSION ["wareshub"] ["reporting"] [$_REQUEST ["waretype"] . "s"] [$_REQUEST ["wareid"]] ["branches"] [$CurrentBranch] = $RTools->getWebReportForBranch ( $_REQUEST ["waretype"], $_REQUEST ["wareid"], $CurrentBranch );
    }

    $Page->setWareBranch ( $CurrentBranch );
  }
}
else
{
  include_once(__DIR__."/include/MainPageLayout.php");
  
  $Page = new MainPageLayout();
  $Page->setWareType($CurrentWareType);
  
  if (isset($_REQUEST["search"]))
    $Page->setSearch($_REQUEST["search"]);
  
  
}
  

$Page->generatePage();



?>