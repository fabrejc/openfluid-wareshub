<?php 


include_once(__DIR__."/WebGitTools.php");


abstract class BasePageLayout
{
  
  protected $WareType;
    
  protected static $WARETYPES = array("simulator","observer","builderext");

  protected static $SingularWareTypes = array(
      "simulator" => "simulator",
      "observer" => "observer",
      "builderext" => "builder-extension"
  );
  
  protected static $PluralWareTypes = array(
      "simulator" => "simulators",
      "observer" => "observers",
      "builderext" => "builder-extensions"
  );
  
  protected static $MutedIssuesIcons = array(
      "bug" => "glyphicon-exclamation-sign text-muted",
      "feature"  => "glyphicon-circle-arrow-up text-muted",
      "review" => "glyphicon-eye-open text-muted"
  );
  
  
  protected static $EnlightedIssuesIcons = array(
      "bug" => "glyphicon-exclamation-sign issue-color-bug",
      "feature"  => "glyphicon-circle-arrow-up issue-color-feature",
      "review" => "glyphicon-eye-open issue-color-review"
  );
  
  
  // =====================================================================
  // =====================================================================
    
  
  function __construct()
  {
    $this->WareID = "";
    $this->WareType = "";
    $this->WareBranch = "";
  }
   
  // =====================================================================
  // =====================================================================
  
  
  abstract public function getPageContent();
  
  
  // =====================================================================
  // =====================================================================
  
  
  public function setWareType($WareType)
  {
    $this->WareType = $WareType;
  }
  
  
  // =====================================================================
  // =====================================================================
        
  
  protected function getCompatibilityString($VersionsArray,$LongString = true)
  {
    $CompatVersions = "<i>unknown</i>";
    
    if (!empty($VersionsArray))
    {
      
      
      if (array_key_exists("openfluid-current-version",$_SESSION["wareshub"]["definitions-config"]) &&
          in_array($_SESSION["wareshub"]["definitions-config"]["openfluid-current-version"],$VersionsArray))
        $CompatVersions = $this->getCurrentBranchStarString();
      else
        $CompatVersions = $this->getBranchStarString();            
      
      $CompatVersions .= "&nbsp;".$VersionsArray[0];
      
      if (sizeof($VersionsArray) > 1)
      {
        if ($LongString)
          $CompatVersions .= " <span class='text-muted'>and ".implode(", ",array_slice($VersionsArray,1))."</span>";
        else
          $CompatVersions .= " <span class='text-muted'>and previous</span>";
      }
    } 

    return $CompatVersions;
  }
  

  // =====================================================================
  // =====================================================================
  
  
  protected function isBranchStar($BranchName)
  {
    $Pos = strpos($BranchName,"openfluid-");
    return ($Pos !== false && $Pos == 0 && preg_match("#(\d+\.\d+(\.\d+)*)$#", $BranchName, $MatchVersion));
  }
  
  
  // =====================================================================
  // =====================================================================

  
  protected function isCurrentVersionBranch($BranchName)
  {
    if (array_key_exists("openfluid-current-version",$_SESSION["wareshub"]["definitions-config"]) &&
        $BranchName == "openfluid-".$_SESSION["wareshub"]["definitions-config"]["openfluid-current-version"])
      return true;
    else
      return false;
  }
  
  
  // =====================================================================
  // =====================================================================
  
  
  protected function getCurrentBranchStarString()
  {
    return "&nbsp;<span class='glyphicon glyphicon-star branch-star'></span>";
  }

  
  // =====================================================================
  // =====================================================================
  
  
  protected function getBranchStarString()
  {
    return "&nbsp;<span class='glyphicon glyphicon-star-empty branch-star'></span>";
  }

  
  // =====================================================================
  // =====================================================================
      
  
  public function generatePage()
  {
    echo "
   <html>
     <head>
      <title>Wareshub</title>
      <meta http-equiv='content-type' content='text/html; charset=UTF-8'>
      <link rel='icon' type='image/png' href='data/images/favicon.png' />
      <link href='http://fonts.googleapis.com/css?family=Open+Sans:300,400|Open+Sans+Condensed:300,400' rel='stylesheet' type='text/css'>
      <link rel='stylesheet' href='//netdna.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap.min.css'>
      <link rel='stylesheet' href='//netdna.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap-theme.min.css'>
      <script src='//code.jquery.com/jquery-1.11.0.min.js'></script>
      <script src='//netdna.bootstrapcdn.com/bootstrap/3.1.1/js/bootstrap.min.js'></script>
    
      <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
      <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
      <!--[if lt IE 9]>
        <script src='https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js'></script>
        <script src='https://oss.maxcdn.com/libs/respond.js/1.3.0/respond.min.js'></script>
      <![endif]-->   

      <style  type='text/css'>
    ";

    echo file_get_contents($_SESSION["wareshub"]["dirs"]["system-web"]."/css/wareshub.css");
    
    echo "
      </style>  
    </head>
    <body>";
    
    echo "<div id='wrap'>";

    echo "
        <nav class='navbar navbar-topmenu' role='navigation'>
          <div class='container'><h3>".$_SESSION["wareshub"]["labels"]["defsset-title"]."</h3></div>
        </nav>
     ";
    
    $this->getPageContent();    
    
    echo "  <div id='push'></div>
          </div>";
    echo "

         <div id='footer'>
           <div class='container'>
             <p class='text-center'><br>".$_SESSION["wareshub"]["labels"]["defsset-footer"]."</p>
           </div>
         </div>
       </body>
     </html>
   ";
    
  }    
  
}


?>