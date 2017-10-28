<?php
if(!defined('ROOT')) exit('No direct script access allowed');

$uniLinks=[];

switch($_REQUEST['action']){
  case "fetchGrid":
    if(isset($_GET['dtuid']) && isset($_GET['refid'])) {
      if(isset($_SESSION['INFOVIEWTABLE']) && isset($_SESSION['INFOVIEWTABLE'][$_GET['dtuid']])) {
        $src=$_SESSION['INFOVIEWTABLE'][$_GET['dtuid']];
        if(isset($src['unilinks'])) {
          $uniLinks=$src['unilinks'];
        }
        
        $sql=_db()->_selectQ($src['table'],$src['cols']);
        if(is_array($src['where'])) {
          $sql->_where($src['where']);
        } else {
          $sql->_whereRAW(_replace($src['where']));
        }
        if(isset($src['orderby']) && strlen($src['orderby'])>0) {
          $sql->_orderBy($src['orderby']);
        }
        if(isset($_GET['page'])) {
          $pg=$_GET['page'];
        } else {
          $pg=0;
        }
        if(isset($_GET['limit'])) {
          $lt=$_GET['limit'];
        } else {
          $lt=100;
        }
        $sql->_limit($lt,$lt*$pg);
        
        $data=$sql->_GET();
//         var_dump(_db()->get_error());
        
        if($data) {
          //printServiceMsg($data);
          foreach($data as $nx=>$row) {
            echo "<tr>";
            foreach($row as $a=>$b) {
              $t=_ling($b);
              if(array_key_exists($a,$uniLinks)) {
                if(is_array($uniLinks[$a])) {
                  //"name"=>["type"=>"profile.customers","col"=>"id"]
                  if(isset($uniLinks[$a]['type']) && isset($uniLinks[$a]['col']) && isset($row[$uniLinks[$a]['col']])) {
                    echo "<td class='{$a}'><a href='#' class='unilink' data-type='{$uniLinks[$a]['type']}' data-hashid='{$row[$uniLinks[$a]['col']]}'>{$t}</a></td>";
                  } else {
                    echo "<td class='{$a}' data-name='{$a}' data-value='{$b}' data-error='unilink-error'>{$t}</td>";
                  }
                } else {
                  echo "<td class='{$a}'><a href='#' class='unilink' data-type='{$uniLinks[$a]}' data-hashid='{$b}'>{$t}</a></td>";
                }
              } else {
                echo "<td class='{$a}' data-name='{$a}' data-value='{$b}'>{$t}</td>";
              }
            }
            echo "</tr>";
          }
        } else {
          echo "<tr><td colspan=1000 align=center>No records found</td></tr>";
        }
      }
    } else {
      printServiceMsg("Request Method Error");
    }
    break;
  case "fetchSingle":
    if(isset($_GET['dtuid']) && isset($_GET['refid'])) {
      if(isset($_SESSION['INFOVIEWTABLE']) && isset($_SESSION['INFOVIEWTABLE'][$_GET['dtuid']])) {
        $src=$_SESSION['INFOVIEWTABLE'][$_GET['dtuid']];
        if(isset($src['unilinks'])) {
          $uniLinks=$src['unilinks'];
        }
        
        $sql=_db()->_selectQ($src['table'],$src['cols']);
        if(is_array($src['where'])) {
          $sql->_where($src['where']);
        } else {
          $sql->_whereRAW(_replace($src['where']));
        }
        if(isset($src['orderby']) && strlen($src['orderby'])>0) {
          $sql->_orderBy($src['orderby']);
        }
        $data=$sql->_GET();
//         var_dump(_db()->get_error());
        if($data) {
          foreach($data[0] as $a=>$b) {
            $t=toTitle(_ling($a));
            if(array_key_exists($a,$uniLinks)) {
              if(is_array($uniLinks[$a])) {
                if(isset($uniLinks[$a]['type']) && isset($uniLinks[$a]['col']) && isset($data[0][$uniLinks[$a]['col']])) {
                  echo "<tr><th width=30%>{$t}</th><td><a href='#' class='unilink' data-type='{$uniLinks[$a]}' data-hashid='{$data[0][$uniLinks[$a]['col']]}'>{$b}</a></td></tr>";
                } else {
                  echo "<tr><th width=30%>{$t}</th><td>{$b}</td></tr>";
                }
              } else {
                echo "<tr><th width=30%>{$t}</th><td><a href='#' class='unilink' data-type='{$uniLinks[$a]}' data-hashid='{$b}'>{$b}</a></td></tr>";
              }
            } else {
              echo "<tr><th width=30%>{$t}</th><td>{$b}</td></tr>";
            }
          }
        } else {
          echo "<tr><td colspan=1000 align=center>No records found</td></tr>";
        }
      }
    } else {
      printServiceMsg("Request Method Error");
    }
    break;
  case "createRecord":
    
    break;
}
?>