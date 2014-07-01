<?php 

include_once(__DIR__."/BasePageLayout.php");


class MainPageLayout extends BasePageLayout
{


  function __construct()
  {
  }


  public function getPageContent()
  {
    if (! in_array ( $this->WareType, static::$WARETYPES ))
      $this->WareType = "simulator";

    $TypeKey = $this->WareType . "s";

    echo "<div class='jumbotron'>
        <div class='container'>
        ";

    echo $_SESSION ["wareshub"] ["labels"] ["defsset-intro"];
    echo "<br/>";
    echo "&nbsp;&nbsp;<a href='" . $_SERVER ["SCRIPT_NAME"] . "?reset=1'><span class='glyphicon glyphicon-refresh'></span>&nbsp;Reload informations</a>";

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
      echo ucfirst ( "${PillType}s" );
      echo "  <span class='badge'>" . sizeof ( $_SESSION ["wareshub"] ["reporting"] [$PillType . "s"] ) . "</span>";
      echo "</a></li>";
    }

    echo "</ul>

      </div>
    ";

    echo "</div></div>";

    echo "<div class='container'>";

    $WareCount = sizeof ( $_SESSION ["wareshub"] ["reporting"] [$TypeKey] );

    if ($WareCount == 0)
    {
      echo "<i>There is no $this->WareType available";
    }
    else
    {
      $WareTypeInfos = $_SESSION ["wareshub"] ["reporting"] [$TypeKey];

      echo "<table class='table' wdith='100%'>";
      echo "<tr><th width='60%'>ID</th><th width='20%'>Doc</th><th>OpenFLUID compatibility</th></tr>";

      foreach ( $WareTypeInfos as $WareID => $WareData )
      {
        echo "<tr>
            <td><a href='" . $_SERVER ["SCRIPT_NAME"] . "?waretype=" . $this->WareType . "&wareid=" . $WareID . "'>$WareID</a>";

        if (array_key_exists("shortdesc",$WareData["definition"]) && !empty($WareData["definition"]["shortdesc"]))
        {
          echo "<div class='mainshortdesc'><span class='text-muted'>" . $WareData ["definition"] ["shortdesc"] . "</span></div>";
        }

        echo "  </td><td>";

        if (!empty($WareData["pdfdoc-url-subfile"]))
        {
          echo "<a href='".WebGitTools::getPDFURL($_SESSION["wareshub"]["url"]["defsset-githost"],$WareData["pdfdoc-url-subfile"])."'>
              <span class='glyphicon glyphicon-file'></span>&nbsp;PDF
              </a>";
        }
        else echo "&nbsp;";

        echo "<td>" . $this->getCompatibilityString($WareData["compat-versions"],false)."</td>
            </tr>";
      }

      echo "</table>";
    }

    echo "</div>";
  }

}



?>