<?php
if(!defined('ROOT')) exit('No direct script access allowed');

$colsArr=explode(",",$_ENV['INFOVIEW']['cols']);

$_ENV['INFOVIEW']['columns']=$colsArr;

if(!isset($_ENV['INFOVIEW']['hidden'])) $_ENV['INFOVIEW']['hidden']=[];

$_SESSION['INFOVIEWTABLE'][$dtuid]=$_ENV['INFOVIEW'];
// printArray($_ENV['INFOVIEW']);
?>
<style>
  .infoTableView thead th {
    cursor: pointer;
  }
  .infoTableView thead .btn {
    color: #999;
    padding: 2px;padding-left: 5px;padding-right: 5px;
    border: 0px;
  }
  .infoTableView .info-pagination .fa {
    padding: 10px;
    cursor: pointer;
  }
  .infoTableView .info-pagination select {
    width: 80px;
    margin: auto;
  }
</style>
<div id='infoviewTable_<?=$dtuid?>' class="row infoTableView infoTableGrid infoOnOpen" 
      data-dcode='<?=$dcode?>' data-dtuid='<?=$dtuid?>' data-page=0 data-limit=20 data-ui="grid">
    <div class='col-md-12 table-responsive infoview-table'>
        <?php
          if(isset($_ENV['INFOVIEW']['actions']) && is_array($_ENV['INFOVIEW']['actions'])) {
            echo "<div class='form-actions text-right'>";
            echo getInfoViewActions($_ENV['INFOVIEW']['actions']);
            echo "</div>";
          }
        ?>
        <table class="table table-bordered table-pagination">
            <thead>
                <tr>
                    <?php
                    foreach($colsArr as $n=>$v) {
                          $xtras="";
                          $clz="";
                      
                          $k=explode(".",$v);
                          $k=explode(" as ",end($k));
                          $k1=_ling(end($k));
                          if($n==0) {
                            $xtras="width=100px";
                            continue;
                          } else {
                            
                          }
                          
                          $nm=end($k);//$k[0]
                          if(in_array($nm, $_ENV['INFOVIEW']['hidden']) || in_array(end($k), $_ENV['INFOVIEW']['hidden'])) $clz.="hidden d-none noshow";
                        ?>
                        <th name='<?=$nm?>' class='<?=$clz?>' <?=$xtras?> onclick='sortInfoviewByColumn(this)'><?=toTitle($k1)?></th>
                        <?php
                    }
                    ?>
                    <th class='actions'>
                      <button class="btn btn-default fa fa-filter fa-2x" onclick="showInfoviewFilters(this)"></button>
                    </th>
                </tr>
                <tr class='filters hidden'>
                    <?php
                    if(isset($_ENV['INFOVIEW']['filter']) && $_ENV['INFOVIEW']['filter']) {
                      if($_ENV['INFOVIEW']['filter']===true) {
                        foreach($colsArr as $n=>$v) {
                          $xtras="";
                          $clz="";

                          $k=explode(".",$v);
                          $k=explode(" as ",end($k));
                          $k1=_ling(end($k));
                          if($n==0) {
                            $xtras="width=100px";
                            continue;
                          } else {
                            
                          }
                          
                          $nm=end($k);//$k[0]
                          if(in_array($nm, $_ENV['INFOVIEW']['hidden']) || in_array(end($k), $_ENV['INFOVIEW']['hidden'])) $clz.="hidden d-none noshow";
                          ?>
                          <th class='<?=$clz?>' <?=$xtras?> >
                            <input type='text' class='form-control' name='<?=$nm?>' />
                          </th>
                          <?php
                        }
                      } elseif(is_array($_ENV['INFOVIEW']['filter'])) {
                        foreach($colsArr as $n=>$v) {
                          $xtras="";
                          $clz="";

                          $k=explode(".",$v);
                          $k=explode(" as ",end($k));
                          $k1=_ling(end($k));
                          if($n==0) {
                            $xtras="width=100px";
                            continue;
                          } else {
                            
                          }
                          
                          $nm=end($k);//$k[0]
                          if(in_array($nm, $_ENV['INFOVIEW']['hidden']) || in_array(end($k), $_ENV['INFOVIEW']['hidden'])) $clz.="hidden d-none noshow";

                          if(isset($_ENV['INFOVIEW']['filter'][$nm])) {
                            $fieldInfo = $_ENV['INFOVIEW']['filter'][$nm];
                          } else {
                            $fieldInfo = [];
                          }
                          $fieldHTML = getInfoFilterField($nm, $fieldInfo);
                          ?>
                          <th class='<?=$clz?>' <?=$xtras?> >
                            <?=$fieldHTML?>
                          </th>
                          <?php
                        }
                      }
                    }
                    ?>
                    <th class='actions'>
                      <button class="btn btn-default fa fa-search fa-2x" onclick="loadInfoTableInline(this)"></button>
                    </th>
                </tr>
            </thead>
            <tbody>
              
            </tbody>
            <tfoot class='info-pagination'>
              <?php
                if(!isset($_ENV['INFOVIEW']['navigation']) || $_ENV['INFOVIEW']['navigation']) {
              ?>
              <tr>
                <td colspan="10000">
                  <div class="row">
                    <div class="col-md-4 text-left">
                      <i class="fa fa-chevron-left" onclick="showInfoviewTablePrev(this)"></i>
                    </div>
                    <div class="col-md-4 text-center">
                      <select class='form-control' onchange="changeInfoviewPageLimit(this)">
                        <option value='20'>20</option>
                        <option value='50'>50</option>
                        <option value='100'>100</option>
                      </select>
                    </div>
                    <div class="col-md-4 text-right">
                      <i class="fa fa-chevron-right" onclick="showInfoviewTableNext(this)"></i>
                    </div>
                  </div>
                </td>
              </tr>
              <?php
              }
              ?>
            </tfoot>
        </table>
        <table class="table table-condensed">
          <tfoot class='info-form'>
              <?php
                if(checkUserRoles($_ENV['INFOVIEW']['security']['module'],$_ENV['INFOVIEW']['security']['activity'],"CREATE")) {
                  echo generateInfoTableForm($_ENV['INFOVIEW'],$dtuid);
                }
              ?>
          </tfoot>
        </table>
        <?php
          if(isset($_ENV['INFOVIEW']['footactions']) && is_array($_ENV['INFOVIEW']['footactions'])) {
            echo "<div class='form-actions text-right'>";
            echo getInfoViewActions($_ENV['INFOVIEW']['footactions']);
            echo "</div>";
          }
        ?>
    </div>
</div>
