<?php 

include_once(__DIR__."/BasePageLayout.php");
include_once($GLOBALS["WHSytemRootPath"]."/include/ReportingTools.php");

class AdminPageLayout extends BasePageLayout
{
  
  function __construct()
  {
    if (!$this->isUserAdmin())
    {
      header("Location: ".$_SERVER["SCRIPT_NAME"]);
    }
    else
    {
      if (isset($_REQUEST["dloadreport"]))
      {
        $this->generateWaresCSVData($_REQUEST["dloadreport"]);        
        die();
      }
      
      $this->HTMLExtraScripts = "<script type='text/javascript' src='https://www.google.com/jsapi'></script>";
    }
  }

  
  // =====================================================================
  // =====================================================================
  
  
  function printCompatibilityStats()
  {
    echo "
           <h3>Overview of compatibility with OpenFLUID versions</h3>
           <br/>
           <div class='row'>";
     
    foreach (static::$WARETYPES as $WareType)
    {
      $WareInfos = $_SESSION["wareshub"]["reporting"][$WareType."s"];
      $HigherVersions = array();
      foreach ($WareInfos as $WareID => $WareData)
      {
        if (empty($WareData["compat-versions"]))
        {
          if (!array_key_exists("unknown",$HigherVersions))
            $HigherVersions["unknown"] = 0;
          $HigherVersions["unknown"] = $HigherVersions["unknown"]+1;
        }
        else
        {
          $HVer = $WareData["compat-versions"][0];
          if (!array_key_exists($HVer,$HigherVersions))
            $HigherVersions[$HVer] = 0;
          $HigherVersions[$HVer] = $HigherVersions[$HVer]+1;
        }
      }
       
      ksort($HigherVersions);
       
      echo "
      <script type='text/javascript'>
      google.load('visualization', '1', {packages:['corechart']});
      google.setOnLoadCallback(drawChart".$WareType.");
      function drawChart".$WareType."() {
        var data = google.visualization.arrayToDataTable([
          ['Version', 'Count'],
      ";
       
      foreach ($HigherVersions as $key => $value)
      {
        echo "['$key',$value],\n";
      }
       
      echo "
      ]);
    
         var options = {
           chartArea : {'width': '90%', 'height': '90%'},
         };
    
         var chart = new google.visualization.PieChart(document.getElementById('chart_div_".$WareType."'));
          chart.draw(data, options);
       }
       </script>
       <div class='col-md-4'><b>".$WareType."s</b><div id='chart_div_".$WareType."'></div></div>
              ";
    }
     echo "</div>";    
  }
  

  // =====================================================================
  // =====================================================================
  
  
  function generateWaresCSVData($WareType)
  {
    header("Content-type: text/csv");
    header("Content-Disposition: attachment; filename=".$WareType."-data.csv");
    
    $WareInfos = $_SESSION["wareshub"]["reporting"][$WareType];

    $RTools = new ReportingTools($GLOBALS["WHSytemRootPath"]."/config");
    $RTools->setActiveDefinitionsSet($GLOBALS["DefsSetRootPath"]);
    $SingularWareType = rtrim($WareType,"s");
    
    
    echo "ID;compat-version;pdf;contacts;users-ro;users-rw\n";

    foreach ($WareInfos as $WareID => $WareData)
    {
      echo "$WareID;";

      if (empty($WareData["compat-versions"]))
        echo "unknown;";
      else
        echo $WareData["compat-versions"][0].";";
      
      if (empty($WareData["pdfdoc-url-subfile"]))
        echo "no;";
      else
        echo "yes;";
      
      
      if (!empty($WareData["compat-versions"]))
      {
        // take the higher compat version as default branch
        $CurrentBranch = "openfluid-".$WareData["compat-versions"][0];

        $BranchInfos = $RTools->getWebReportForBranch($SingularWareType,$WareID,$CurrentBranch);
        if (array_key_exists("wareshub",$BranchInfos))
          echo implode("|",$BranchInfos["wareshub"]["contacts"]).";";
        else
          echo "none;";
      }
      else
        echo "none;";
        
              
      echo implode("|",$WareData["definition"]["users-ro"]).";".implode("|",$WareData["definition"]["users-rw"]);
      echo "\n";        
      
    }
    
    unset($RTools);
    
  }
  
    
  // =====================================================================
  // =====================================================================
    
  
  function printWaresDataLinks()
  {
    echo "<h3>Detailled wares data as CSV files</h3>
           <br/>";
    
    echo "<ul>";
    foreach (static::$WARETYPES as $WareType)
    {
      echo "<li><a href='".$_SERVER["SCRIPT_NAME"]."?admin=1&dloadreport=".$WareType."s'>".$WareType."s</li>";
    }
    echo "</ul>";
  }
  
  
  // =====================================================================
  // =====================================================================
  
  
  public function getPageContent()
  {    
     echo "<div class='jumbotron'>
           <div class='container'>";

     echo "<h4>Administration board</h4>
           &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href='".$_SERVER["SCRIPT_NAME"]."'><span class='glyphicon glyphicon-list'></span>&nbsp;&nbsp;back to main page</a>";
     
     echo "</div></div>";
     
     echo "<div class='container'>";
     
     $this->printCompatibilityStats();
     
     $this->printWaresDataLinks();
     
     echo "</div>";
  }
}



?>