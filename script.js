$('.infoviewContainerTabs a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
  src=$($(e.target).attr("href"),".infoviewContainerTabs").find(".infoTableView");
  loadInfoTable(src);
})
function loadInfoTableFirst(src) {
  if($(src).find("table.table tbody").children().length<=0) {
    loadInfoTable(src);
  }
}
function loadInfoTable(src) {
  ui=$(src).data("ui");
  cmd="fetchGrid";
  if(ui=="single") {
    cmd="fetchSingle";
  }

  $(src).find("table.table tbody").append("<tr><td colspan=1000 align=center><div class='ajaxloading ajaxloading5'></div></td></tr>");

  lx=_service("infoviewTable",cmd,"table")+"&dtuid="+$(src).data("dtuid")+"&refid="+$(src).data("dcode")+
      "&page="+$(src).data("page")+"&limit="+$(src).data("limit");
  $(src).find("table.table tbody").load(lx,function(ans) {
//       console.log("LOADED");
  });
}
function reloadInfoTable(src) {
  $(src).find("table.table tbody").html("");
  $(src).data("page",0);
  loadInfoTable(src);
}
function loadMore(src) {
  nx=$(src).data("page");
  nx=parseInt(nx);
  nx++;
  $(src).data("page",nx);
  loadInfoTable(src);
}