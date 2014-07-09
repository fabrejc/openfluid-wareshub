<?php 

include_once(__DIR__."/BasePageLayout.php");


class MainPageLayout extends BasePageLayout
{
  
  private $Search;
  
  
  function __construct()
  {
    $this->Search = "";
  }

  
  // =====================================================================
  // =====================================================================
  
  
  public function setSearch($Str)
  {
    $this->Search = $Str;
  } 
  
  
  // =====================================================================
  // =====================================================================
  

  public function getPageContent()
  {
    if (! in_array ( $this->WareType, static::$WARETYPES ))
      $this->WareType = "simulator";

    $TypeKey = $this->WareType . "s";

    echo "<div class='jumbotron'>
        <div class='container'>
        ";

    echo $_SESSION["wareshub"]["labels"]["defsset-intro"];
    echo "<br/>";
    echo "&nbsp;&nbsp;<a href='".$_SERVER ["SCRIPT_NAME"]."?reset=1&waretype=".$this->WareType."'><span class='glyphicon glyphicon-refresh'></span>&nbsp;Reload informations</a>";

    echo "
        <br/><br/>
        <div style='margin-left: 100px;'>
        <ul class='nav nav-pills'>
        ";

    foreach ( static::$WARETYPES as $PillType )
    {
      echo "<li";
      if ($PillType == $this->WareType)
        echo " class='active'";
      echo ">";
      echo "<a href='" . $_SERVER ["SCRIPT_NAME"] . "?waretype=${PillType}'>";
      echo ucfirst(static::$PluralWareTypes[$PillType]);
      echo "  <span class='badge'>" . sizeof ( $_SESSION ["wareshub"] ["reporting"] [$PillType . "s"] ) . "</span>";
      echo "</a></li>";
    }

    echo "</ul>

      </div>
    ";

    echo "</div></div>";

    
    $WareCount = sizeof ( $_SESSION ["wareshub"] ["reporting"] [$TypeKey] );
    
    
    
    if ($WareCount > 0)
    {
      echo "<div class='container'>";

      $SearchedValue="";
      if (!empty($this->Search))
        $SearchedValue="value='".$this->Search."'";
      
      echo "
        <form class='form-inline pull-right search-bar' role='form'>
        <div class='form-group'>
          <input type='text' class='form-control input-sm' id='search' name='search' $SearchedValue placeholder='Searched terms'>
          <button type='submit' class='btn btn-default btn-sm'>Search</button>
          <input type='hidden' name='waretype' value='".$this->WareType."'>  
        </div>
        </form>
      ";     
      
      echo "</div>";
    }

    
    echo "<div class='container'>";

    if ($WareCount == 0)
    {
      echo "<br><br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<i>There is no ".static::$SingularWareTypes[$this->WareType]." available";
    }
    else
    {
      $IsNotFoundMessage = true;
      $WareTypeInfos = $_SESSION ["wareshub"] ["reporting"] [$TypeKey];

      echo "<table class='table' wdith='100%'>";
      echo "<tr><th width='60%'>ID</th><th width='20%'>Doc</th><th>OpenFLUID compatibility</th></tr>";

      foreach ( $WareTypeInfos as $WareID => $WareData )
      {
        $OKToDisplay = true;
        if (!empty($this->Search))
        {
          $OKToDisplay = false;
          
          if (strpos($WareID,$this->Search) !== false)
            $OKToDisplay = true;
          elseif (array_key_exists("shortdesc",$WareData["definition"]) && 
                  !empty($WareData["definition"]["shortdesc"]) &&
                   strpos($WareData["definition"]["shortdesc"],$this->Search) !== false)
              $OKToDisplay = true;
        }
        
        
        if ($OKToDisplay)
        {
          $IsNotFoundMessage = false;
          
          echo "<tr>
              <td><a href='" . $_SERVER ["SCRIPT_NAME"] . "?waretype=" . $this->WareType . "&wareid=" . $WareID . "'>$WareID</a>";
  
          if (array_key_exists("shortdesc",$WareData["definition"]) && !empty($WareData["definition"]["shortdesc"]))
          {
            echo "<div class='mainshortdesc'><span class='text-muted'>" . $WareData ["definition"] ["shortdesc"] . "</span></div>";
          }
          
          if (array_key_exists("open-issues",$WareData))
          {
            echo "<div class='mainopenissues'>";
            
            $IssuesTypes = array("bug","feature","review");
            
            foreach ($IssuesTypes as $IssueType)
            {
              if ($WareData["open-issues"][$IssueType] > 0)
                echo "<span class='glyphicon ".self::$EnlightedIssuesIcons[$IssueType]."'></span>";
              else
                echo "<span class='glyphicon ".self::$MutedIssuesIcons[$IssueType]."'></span>";             
                
              echo "<span class='text-muted'>&nbsp;".$WareData["open-issues"][$IssueType]."</span>&nbsp&nbsp&nbsp&nbsp";
            }
            
            echo "</div>";
          }
          
          echo "  </td><td>";
  
          if (!empty($WareData["pdfdoc-url-subfile"]))
          {
            echo "<a href='".WebGitTools::getPDFURL($_SESSION["wareshub"]["url"]["defsset-gitprotocol"],$_SESSION["wareshub"]["url"]["defsset-githost"],$WareData["pdfdoc-url-subfile"])."'>
                <span class='glyphicon glyphicon-file'></span>&nbsp;PDF
                </a>";
          }
          else echo "&nbsp;";
  
          echo "<td>" . $this->getCompatibilityString($WareData["compat-versions"],false)."</td>
              </tr>";
        }
        
      }

      echo "</table>";
      
      if ($IsNotFoundMessage && !empty($this->Search))
        echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<i>No ".static::$SingularWareTypes[$this->WareType]." found with terms \"".$this->Search."\".";
      
    }

    echo "</div>";
  }

}



?>