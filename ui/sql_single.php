<?php
if(!defined('ROOT')) exit('No direct script access allowed');

$_SESSION['INFOVIEWTABLE'][$dtuid]=$_ENV['INFOVIEW'];
?>
<div id='dataTable_<?=$dtuid?>' class="row infoTableView infoTableSingle infoOnOpen" 
      data-dcode='<?=$dcode?>' data-dtuid='<?=$dtuid?>' data-page=-1 data-limit=20 data-ui="single">
    <div class='col-md-12 table-responsive'>
        <?php
          if(isset($_ENV['INFOVIEW']['buttons']) && is_array($_ENV['INFOVIEW']['buttons'])) {
            echo "<div class='form-actions text-right'>";
            echo getInfoViewActions($_ENV['INFOVIEW']['buttons']);
            echo "</div>";
          }
        ?>
        <table class="table table-bordered">
            <tbody>
              
            </tbody>
        </table>
    </div>
</div>
