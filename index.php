<?php
if(!defined('ROOT')) exit('No direct script access allowed');

//To be used in infoview
//$slug=_slug("module/uitype/subcat");

include_once __DIR__."/api.php";

if(isset($_ENV['INFOVIEW']) && isset($_ENV['INFOVIEW']['config']) && isset($_ENV['INFOVIEW-REFHASH'])) {

  if(isset($_ENV['INFOVIEW']['vmode'])) $_ENV['INFOVIEW']['config']['vmode']=$_ENV['INFOVIEW']['vmode'];
  else $_ENV['INFOVIEW']['config']['vmode']="view";

  if(isset($_ENV['INFOVIEW']['security'])) {
    $_ENV['INFOVIEW']['config']['security']=$_ENV['INFOVIEW']['security'];
  }
  
  $_ENV['INFOVIEW']=$_ENV['INFOVIEW']['config'];
  if(!isset($_ENV['INFOVIEW']['type'])) $_ENV['INFOVIEW']['type']="sql";
  if(!isset($_ENV['INFOVIEW']['uimode'])) $_ENV['INFOVIEW']['uimode']="grid";

  $duidArr=array_map(function($a) {
    if(is_array($a)) return "";
    else return $a;
  },$_ENV['INFOVIEW']);

  $dtuid=md5(microtime().implode(".",$duidArr));
  $dcode=$_ENV['INFOVIEW-REFHASH'];
  $_REQUEST['REFID']=$dcode;
  $_ENV['INFOVIEW']['refid']=$_ENV['INFOVIEW-REFID'];
  $_ENV['INFOVIEW']['refhash']=$_ENV['INFOVIEW-REFHASH'];
  
  if(isset($_SESSION['INFOVIEW'][$_ENV['FORMKEY']])) {
    if(isset($_SESSION['INFOVIEW'][$_ENV['FORMKEY']]['hooks'])) {
      $_ENV['INFOVIEW']['hooks']=$_SESSION['INFOVIEW'][$_ENV['FORMKEY']]['hooks'];
    }
    if(isset($_SESSION['INFOVIEW'][$_ENV['FORMKEY']]['srckey'])) {
      $_ENV['INFOVIEW']['srckey']=$_SESSION['INFOVIEW'][$_ENV['FORMKEY']]['srckey'];
    }
  }
  
  $slugs=_slug("a/srcfile/refid/subcat/subtype/code");
  foreach($slugs as $a=>$b) {
    $_REQUEST[$a]=$b;
  }

  switch(strtolower($_ENV['INFOVIEW']['type'])) {
    case "sql":
      $f=__DIR__."/ui/sql_{$_ENV['INFOVIEW']['uimode']}.php";
      if(file_exists($f) && isset($_ENV['INFOVIEW']['table']) && isset($_ENV['INFOVIEW']['cols']) && isset($_ENV['INFOVIEW']['where'])) {
    		if(!is_array($_ENV['INFOVIEW']['cols'])) {
    			$_ENV['INFOVIEW']['cols']=_replace($_ENV['INFOVIEW']['cols']);
    		} else {
    			foreach($_ENV['INFOVIEW']['cols'] as $a=>$b) {
    				$_ENV['INFOVIEW']['cols'][$a]=_replace($b);
    			}
    		}
    		if(!is_array($_ENV['INFOVIEW']['where'])) {
    			$_ENV['INFOVIEW']['where']=_replace($_ENV['INFOVIEW']['where']);
    		} else {
    			foreach($_ENV['INFOVIEW']['where'] as $a=>$b) {
            if($b=="RAW") {
              unset($_ENV['INFOVIEW']['where'][$a]);
              $_ENV['INFOVIEW']['where'][_replace($a)]=$b;
            } else {
              $_ENV['INFOVIEW']['where'][$a]=_replace($b);
            }
    			}
    		}
        include $f;
      } else {
        echo "<h1 align=center>Sorry, defination error.</h1>";
      }
      break;
    case "php":
      include __DIR__."/ui/php.php";
      break;
    case "file":
      if(file_exists(APPROOT.$_ENV['INFOVIEW']['file'])) {
        include APPROOT.$_ENV['INFOVIEW']['file'];
      } else {
        echo "<h1 align=center>Sorry, source file is not found.</h1>";
      }
      break;
    default:
      echo "<h1 align=center>Sorry, configuration is not supported.</h1>";
  }
} else {
  echo "<h1 align=center>Sorry, configuration is not correct.</h1>";
}
if(!isset($_ENV['INFOVIEWLOADED'])) {
  $_ENV['INFOVIEWLOADED']=true;

  echo _css("infoviewTable");
  echo _js("infoviewTable");
}
?>
