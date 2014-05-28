<?php 

class WaresHubLayout
{
  private $WareID;
  private $WareType;
  
  private static $WARETYPES = array("simulator","observer","builderext");
  
  function __construct()
  {
    $this->WareID = "";
    $this->WareType = "";
  }
   
  
  // =====================================================================
  // =====================================================================
  
  
  public function setWareType($WareType)
  {
    $this->WareType = $WareType;    
  }
  
  
  // =====================================================================
  // =====================================================================
  
  
  public function setWareID($WareID)
  {
    $this->WareID = $WareID;
  }
  
  
  // =====================================================================
  // =====================================================================
  
  
  private function getCompatibilityString($VersionsArray)
  {
    $CompatVersions = "<i>unknown</i>";
    
    if (!empty($VersionsArray))
      $CompatVersions = implode(", ",$VersionsArray); 

    return $CompatVersions;
  }
  
  
  // =====================================================================
  // =====================================================================
  
  
  private function isHTTPs()
  {
    return strtolower(substr($_SERVER["SERVER_PROTOCOL"],0,5))=='https'? true : false;
  }
  
  
  private function getGitURL($URLSubDir)
  {
    $Protocol = "http://";
    
    if ($this->isHTTPs())
      $Protocol = "https://";
    
    return $Protocol.$_SERVER["SERVER_NAME"]."/".$URLSubDir;
  }
  
  // =====================================================================
  // =====================================================================
  
  
  private function generateHomeContent()
  {
    if (!in_array($this->WareType,static::$WARETYPES))
      $this->WareType = "simulator";
    
    $TypeKey = $this->WareType."s";
    
    echo "<div class='jumbotron'>
      <div class='container'>
    ";
    
    echo $_SESSION["wareshub"]["labels"]["defsset-intro"];
    echo "<br/>";      
    echo "&nbsp;&nbsp;<a href='".$_SERVER["SCRIPT_NAME"]."?reset=1'><span class='glyphicon glyphicon-refresh'></span>&nbsp;Reload informations</a>";
    
    echo "
        <br/><br/>        
        <div style='margin-left: 100px;'>        
        <ul class='nav nav-pills'>
    ";
    
    foreach (static::$WARETYPES as $PillType)
    {
      echo "<li";
      if ($PillType == $this->WareType) echo " class='active'";
      echo ">";
      echo "<a href='".$_SERVER["SCRIPT_NAME"]."?waretype=${PillType}'>";
      echo ucfirst("${PillType}s");
      echo "  <span class='badge'>".sizeof($_SESSION["wareshub"]["reporting"][$PillType."s"])."</span>";      
      echo "</a></li>";
    }
    
    echo "</ul>
    
        </div>
        ";
    
    
    echo "</div></div>";
    
    echo "<div class='container'>";
    
    $WareCount = sizeof($_SESSION["wareshub"]["reporting"][$TypeKey]);
    
    if ($WareCount == 0)
    {
      echo "<i>There is no $this->WareType available"; 
    }
    else
    {
      $WareTypeInfos = $_SESSION["wareshub"]["reporting"][$TypeKey];
      
      echo "<table class='table'>";
      echo "<tr><th>ID</th><th>OpenFLUID compatibility</th></tr>";
      
      foreach ($WareTypeInfos as $WareID => $WareData)
      {        
        
        echo "<tr>
                <td><a href='".$_SERVER["SCRIPT_NAME"]."?waretype=".$this->WareType."&wareid=".$WareID."'>$WareID</a></td>
                <td>".$this->getCompatibilityString($WareData["compat-versions"])."</td>
              </tr>";
      }
      
      echo "</table>";
    }
    
    echo "</div>";
  }
  
  
  // =====================================================================
  // =====================================================================
  
  
  private function generateWareContent()
  {
    $TypeKey = $this->WareType."s";
    $WareData = $_SESSION["wareshub"]["reporting"][$TypeKey][$this->WareID];
    
    echo "<div class='container'>";
    
    echo "<br/>";
    
    echo "<h3>
            <a href='".$_SERVER["SCRIPT_NAME"]."'><span class='glyphicon glyphicon-th-list'></span></a>&nbsp;&nbsp;/
                <a href='".$_SERVER["SCRIPT_NAME"]."?waretype=".$this->WareType."'>".$TypeKey."</a>&nbsp;/
            ".$this->WareID."</h3><br/>";
    
    echo "OpenFLUID compatibility versions: ".$this->getCompatibilityString($WareData["compat-versions"])."<br/>";
    echo "URL for git clone: ".$this->getGitURL($WareData["git-url-subdir"])."<br/>";
    
    echo "</div>";
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
    
    if (!empty($this->WareID))
      $this->generateWareContent();
    else
      $this->generateHomeContent();
    
    
    echo "</div>";
    echo "</body>";    
  }    
  
}

?>