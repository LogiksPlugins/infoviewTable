<?php
if(!defined('ROOT')) exit('No direct script access allowed');

$uniLinks=[];

include __DIR__."/api.php";

switch($_REQUEST['action']){
  case "fetchGrid":
    if(isset($_GET['dtuid']) && isset($_GET['refid'])) {
      if(isset($_SESSION['INFOVIEWTABLE']) && isset($_SESSION['INFOVIEWTABLE'][$_GET['dtuid']])) {
        $src=$_SESSION['INFOVIEWTABLE'][$_GET['dtuid']];
        if(isset($src['unilinks'])) {
          $uniLinks=$src['unilinks'];
        }

        $tbl1=current(explode(",",$src['table']));
        $sql=_db()->_selectQ($src['table'],$src['cols'],["{$tbl1}.blocked"=>'false']);
        if(is_array($src['where'])) {
          foreach($src['where'] as $a=>$b) {
            if($b=="RAW") {
              unset($src['where'][$a]);
              $src['where'][_replace($a)]=$b;
            } else {
              $src['where'][$a]=_replace($b);
            }
          }
          $sql->_where($src['where']);
        } else {
          $sql->_whereRAW(_replace($src['where']));
        }

        if(isset($_POST['filter']) && count($_POST['filter'])>0) {
          foreach($_POST['filter'] as $a=>$b) {
            if(is_string($b) && strlen($b)>0) {
              $sql->_where(["{$tbl1}.{$a}"=>$b]);
            }
          }
        }
        if(isset($_POST['search']) && count($_POST['search'])>0) {
          foreach($_POST['search'] as $a=>$b) {
            if(is_string($b) && strlen($b)>0) {
              $sql->_where(["{$tbl1}.{$a}"=>[clean($b),"LIKE"]]);
            }
          }
        }

        if(isset($src['orderby']) && strlen($src['orderby'])>0) {
          $sql->_orderBy($src['orderby']);
        } elseif(isset($src['orderBy']) && strlen($src['orderBy'])>0) {
          $sql->_orderBy($src['orderBy']);
        }

        if(isset($src['groupby']) && strlen($src['groupby'])>0) {
          $sql->_groupBy($src['groupby']);
        } elseif(isset($src['groupBy']) && strlen($src['groupBy'])>0) {
          $sql->_groupBy($src['groupBy']);
        }

        if(isset($_GET['sort'])) {
          $sql->_orderBy($_GET['sort']);
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
        
        $allowEdit=checkUserRoles($src['security']['module'],$src['security']['activity'],"EDIT");
        $allowDelete=checkUserRoles($src['security']['module'],$src['security']['activity'],"DELETE");

        if(isset($src['DEBUG']) && $src['DEBUG']) {
          echo "<tr><td colspan=1000>";
          echo $sql->_SQL();
          echo "</td></tr>";
        }
        
        $data=$sql->_GET();
//         var_dump(_db()->get_error());
        
        $colMap=[];
        foreach($src['columns'] as $c) {
          $k="";$v="";
          $c=explode(".",$c);
          $c=end($c);
          if(strpos($c," as ")>0) {
            $c=explode(" as ",$c);
            $k=$c[1];
            $v=$c[0];
          } else {
            $k=$v=$c;
          }
          $colMap[$k]=$v;
        }
        
        if($data && count($data)>0) {
          //printServiceMsg($data);
          if(!isset($src['hidden'])) $src['hidden'] = [];

          $rowKey=array_keys($data[0])[0];
          
          foreach($data as $nx=>$row) {
            echo "<tr data-refid='".md5($row[$rowKey])."'>";
            foreach($row as $a=>$b) {
              if($a==$rowKey) continue;
        
              $a1 = $a;
              $a=$colMap[$a];

              $clz = "";
              if(in_array($a, $src['hidden']) || in_array($a1, $src['hidden'])) $clz.="hidden d-none noshow";
              
              if (preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/",$b)) {
                  $t=_pDate($b);
              } else {
                  $t=_ling($b);
                
                  $t=str_replace("\\n","<br>\n",$t);
              }
              if(array_key_exists($a,$uniLinks)) {
                if(is_array($uniLinks[$a])) {
                  //"name"=>["type"=>"profile.customers","col"=>"id"]
                  if(isset($uniLinks[$a]['type']) && isset($uniLinks[$a]['col']) && isset($row[$uniLinks[$a]['col']])) {
                    if(isset($uniLinks[$a]['viewtext'])) {
                      $t = $uniLinks[$a]['viewtext'];
                    }
                    if($row[$uniLinks[$a]['col']]!=null && strlen($row[$uniLinks[$a]['col']])>0)
                      echo "<td class='{$a} $clz' data-name='{$a}' data-value='{$b}'><a href='#' class='unilink' data-type='{$uniLinks[$a]['type']}' data-hashid='{$row[$uniLinks[$a]['col']]}'>{$t}</a></td>";
                    else
                      echo "<td class='{$a} $clz' data-name='{$a}' data-value='{$b}'></td>";
                  } else {
                    echo "<td class='{$a} $clz' data-name='{$a}' data-value='{$b}' data-error='unilink-error'>{$t}</td>";
                  }
                } else {
                  echo "<td class='{$a} $clz' data-name='{$a}' data-value='{$b}'><a href='#' class='unilink' data-type='{$uniLinks[$a]}' data-hashid='{$b}'>{$t}</a></td>";
                }
              } else {
                echo "<td class='{$a} $clz' data-name='{$a}' data-value='{$b}'>{$t}</td>";
              }
            }
            
            echo "<td class='col-actions actions'>";
            if(isset($src['buttons']) && is_array($src['buttons'])) {
              echo getInfoViewTableActions($src['buttons']);
            }
            if($allowDelete) {
              echo "<i class='fa fa-trash mouseAction pull-right' onclick='deleteInfoRecord(this)'></i>";
            }
            if($allowEdit) {
              echo "<i class='fa fa-pencil mouseAction pull-right' onclick='editInfoRecord(this)'></i>";
            }
            echo "</td>";
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
        
        if(isset($src['DEBUG']) && $src['DEBUG']) {
          echo "<tr><td colspan=100>";
          echo $sql->_SQL();
          echo "</td></tr>";
        }
        
        $data=$sql->_GET();
//         var_dump(_db()->get_error());
        if($data) {
          if(!isset($src['hidden'])) $src['hidden'] = [];

          foreach($data[0] as $a=>$b) {
            if (preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/",$b)) {
                $b=_pDate($b);
            } else {
                $b=_ling($b);
            }
            $t=toTitle(_ling($a));

            $clz = "";
            if(in_array($a, $src['hidden'])) $clz .= "hidden d-none noshow";
            
            if(array_key_exists($a,$uniLinks)) {
              if(is_array($uniLinks[$a])) {
                if(isset($uniLinks[$a]['type']) && isset($uniLinks[$a]['col']) && isset($data[0][$uniLinks[$a]['col']])) {
                  echo "<tr class='{$clz}'><th width=30%>{$t}</th><td><a href='#' class='unilink' data-type='{$uniLinks[$a]}' data-hashid='{$data[0][$uniLinks[$a]['col']]}'>{$b}</a></td></tr>";
                } else {
                  echo "<tr class='{$clz}'><th width=30%>{$t}</th><td>{$b}</td></tr>";
                }
              } else {
                echo "<tr class='{$clz}'><th width=30%>{$t}</th><td><a href='#' class='unilink' data-type='{$uniLinks[$a]}' data-hashid='{$b}'>{$b}</a></td></tr>";
              }
            } else {
              echo "<tr class='{$clz}'><th width=30%>{$t}</th><td>{$b}</td></tr>";
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
  case "create-record":
    if(isset($_POST['dtuid'])) {
      if(isset($_SESSION['INFOVIEWTABLE']) && isset($_SESSION['INFOVIEWTABLE'][$_POST['dtuid']])) {
        $src=$_SESSION['INFOVIEWTABLE'][$_POST['dtuid']];
        unset($_POST['dtuid']);
        
        $allowCreate=checkUserRoles($src['security']['module'],$src['security']['activity'],"CREATE");
        if(!$allowCreate) {
          printServiceMsg("Error, Record Creation is not permitted for you");
          return;
        }
        
        $formConfig=$src['form'];
        if(strtolower($formConfig['source']['type'])=="sql") {
          
          $_REQUEST['REFID']=$src['refid'];
          $_REQUEST['REFHASH']=$src['refhash'];
          $_REQUEST['DATE']=date("Y-m-d");
          $_REQUEST['DATETIME']=date("Y-m-d H:i:s");
          
          if(!isset($formConfig['autofill'])) $formConfig['autofill']=[];
          foreach($formConfig['autofill'] as $key) {
            if(isset($defaultArr[$key])) {
              $_POST[$key]=$defaultArr[$key];
            }
          }

          if(!isset($formConfig['forcefill'])) $formConfig['forcefill']=[];
          foreach($formConfig['forcefill'] as $key=>$val) {
            $_POST[$key]=_replace($val);
          }
          
          if(!isset($formConfig['nofill'])) $formConfig['nofill']=[];
          foreach($formConfig['nofill'] as $key) {
            unset($cols[$key]);
          }
          
          $fData=array_merge($_POST, [
                    "guid"=>$_SESSION['SESS_GUID'],
                    "created_by"=>$_SESSION['SESS_USER_ID'],
                    "created_on"=>date("Y-m-d H:i:s"),
                    "edited_by"=>$_SESSION['SESS_USER_ID'],
                    "edited_on"=>date("Y-m-d H:i:s"),
                  ]);
          foreach($formConfig['fields'] as $a=>$b) {
            if(isset($fData[$a]) && isset($b['type'])) {
              switch(strtolower($b['type'])) {
                case "date":
                  $fData[$a]=processDate($fData[$a]);
                  break;
                case "datetime":
                  $fData[$a]=processDateTime($fData[$a]);
                  break;
              }
            }
          }
          
          $a=_db()->_insertQ1($formConfig['source']['table'],$fData)->_RUN();
          if($a) {
            if(!isset($_REQUEST['refid'])) {
              $_REQUEST['refid']=md5(_db()->get_insertID());
            }
            executeInfoviewTableHook("postsubmit",$src);
            printServiceMsg("Record Created Successully");
          } else {
            printServiceMsg("Error while creation"._db()->get_error());
          }
        } else {
          printServiceMsg("Source type not supported");
        }
      } else {
        printServiceMsg("Request Timed Out. Try reloading.");
      }
    } else {
      printServiceMsg("Request Method Error");
    }
    break;
   case "update-record":
    if(isset($_POST['dtuid']) && isset($_POST['refid'])) {
      if(isset($_SESSION['INFOVIEWTABLE']) && isset($_SESSION['INFOVIEWTABLE'][$_POST['dtuid']])) {
        $src=$_SESSION['INFOVIEWTABLE'][$_POST['dtuid']];
        unset($_POST['dtuid']);
        
        $refid=$_POST['refid'];
        unset($_POST['refid']);
        
        $allowEdit=checkUserRoles($src['security']['module'],$src['security']['activity'],"EDIT");
        if(!$allowEdit) {
          printServiceMsg("Error, Record Updation is not permitted for you");
          return;
        }
        
        $formConfig=$src['form'];
        if(strtolower($formConfig['source']['type'])=="sql") {
          
          $_REQUEST['REFID']=$src['refid'];
          $_REQUEST['REFHASH']=$src['refhash'];
          $_REQUEST['DATE']=date("Y-m-d");
          $_REQUEST['DATETIME']=date("Y-m-d H:i:s");
          
          if(!isset($formConfig['autofill'])) $formConfig['autofill']=[];
          foreach($formConfig['autofill'] as $key) {
            if(isset($defaultArr[$key])) {
              $_POST[$key]=$defaultArr[$key];
            }
          }

          if(!isset($formConfig['forcefill'])) $formConfig['forcefill']=[];
          foreach($formConfig['forcefill'] as $key=>$val) {
            $_POST[$key]=_replace($val);
          }
          
          if(!isset($formConfig['nofill'])) $formConfig['nofill']=[];
          foreach($formConfig['nofill'] as $key) {
            unset($cols[$key]);
          }
          
          $fData=array_merge($_POST, [
                    "edited_by"=>$_SESSION['SESS_USER_ID'],
                    "edited_on"=>date("Y-m-d H:i:s"),
                  ]);
          $where=[
            "md5(id)"=>$refid
          ];
          
          foreach($formConfig['fields'] as $a=>$b) {
            if(isset($fData[$a]) && isset($b['type'])) {
              switch(strtolower($b['type'])) {
                case "date":
                  $fData[$a]=processDate($fData[$a]);
                  break;
                case "datetime":
                  $fData[$a]=processDateTime($fData[$a]);
                  break;
              }
            }
          }
          
          $a=_db()->_updateQ($formConfig['source']['table'],$fData,$where)->_RUN();
          if($a) {
            executeInfoviewTableHook("postsubmit",$src);
            printServiceMsg("Record Updated Successully");
          } else {
            printServiceMsg("Error while creation"._db()->get_error());
          }
        } else {
          printServiceMsg("Source type not supported");
        }
      } else {
        printServiceMsg("Request Timed Out. Try reloading.");
      }
    } else {
      printServiceMsg("Request Method Error");
    }
    break;
  case "delete-record":
    if(isset($_POST['dtuid']) && isset($_POST['refid'])) {
      if(isset($_SESSION['INFOVIEWTABLE']) && isset($_SESSION['INFOVIEWTABLE'][$_POST['dtuid']])) {
        $src=$_SESSION['INFOVIEWTABLE'][$_POST['dtuid']];
        unset($_POST['dtuid']);
        
        $refid=$_POST['refid'];
        unset($_POST['refid']);
        
        $allowDelete=checkUserRoles($src['security']['module'],$src['security']['activity'],"DELETE");
        if(!$allowDelete) {
          printServiceMsg("Error, Record Deletion is not permitted for you");
          return;
        }
        
        $formConfig=$src['form'];
        if(strtolower($formConfig['source']['type'])=="sql") {
          
          $_REQUEST['REFID']=$src['refid'];
          $_REQUEST['REFHASH']=$src['refhash'];
          $_REQUEST['DATE']=date("Y-m-d");
          $_REQUEST['DATETIME']=date("Y-m-d H:i:s");
          
          $fData=[
                  "blocked"=>"true",
                  "edited_by"=>$_SESSION['SESS_USER_ID'],
                  "edited_on"=>date("Y-m-d H:i:s"),
                ];
          $where=[
            "md5(id)"=>$refid
          ];
          
          $a=_db()->_updateQ($formConfig['source']['table'],$fData,$where)->_RUN();
          if($a) {
            executeInfoviewTableHook("postsubmit",$src);
            printServiceMsg("Record Deleted Successully");
          } else {
            printServiceMsg("Error while creation"._db()->get_error());
          }
        } else {
          printServiceMsg("Source type not supported");
        }
      } else {
        printServiceMsg("Request Timed Out. Try reloading.");
      }
    } else {
      printServiceMsg("Request Method Error");
    }
    break;
}
function processDate($date) {
  $date=str_replace("/","-",$date);
  if (preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/",$date)) {//d-m-Y
      return $date;
  } elseif (preg_match("/^(0[0-9]|[1-2][0-9]|3[0-1])-(0[1-9]|1[0-2])-[0-9]{4}$/",$date)) {//Y-m-d
      return _date($date,"d-m-Y","Y-m-d");
  }
  return $date;
}
function processDateTime($datetime) {
  $dt=explode(" ",$datetime);
  if(count($dt)<=1) {
    $dt=explode("T",$datetime);
  }
  $dt[0]=processDate($dt[0]);
  return "{$dt[0]} {$dt[1]}";
}

function executeInfoviewTableHook($state,$formConfig) {
    if(!isset($formConfig['hooks']) || !is_array($formConfig['hooks'])) return false;
    $state=strtolower("infoview-".$state);

    if(isset($formConfig['hooks'][$state]) && is_array($formConfig['hooks'][$state])) {
      $postCFG=$formConfig['hooks'][$state];

      if(isset($postCFG['modules'])) {
        loadModules($postCFG['modules']);
      }
      if(isset($postCFG['api'])) {
        if(!is_array($postCFG['api'])) $postCFG['api']=explode(",",$postCFG['api']);
        foreach ($postCFG['api'] as $apiModule) {
          loadModuleLib($apiModule,'api');
        }
      }
      if(isset($postCFG['helpers'])) {
        loadHelpers($postCFG['helpers']);
      }
      if(isset($postCFG['method'])) {
        if(!is_array($postCFG['method'])) $postCFG['method']=explode(",",$postCFG['method']);
        foreach($postCFG['method'] as $m) call_user_func($m,$formConfig);
      }
      if(isset($postCFG['file'])) {
        if(!is_array($postCFG['file'])) $postCFG['file']=explode(",",$postCFG['file']);
        foreach($postCFG['file'] as $m) {
          if(file_exists($m)) include $m;
          elseif(file_exists(APPROOT.$m)) include APPROOT.$m;
        }
      }
    }
  }
?>
