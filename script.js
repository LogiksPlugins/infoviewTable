$(".infoviewContainerTabs").find(".infoTableView").each(function() {
  src=$(this).closest(".tab-pane").attr("id");
  $(".infoviewContainerTabs").find(".nav.nav-tabs").find("a[href='#"+src+"']").attr("data-onshowncallback","loadInfoTableCallback");
});

function loadInfoTableCallback(srcTag) {
  srcPane=$($(srcTag).attr("href"),".infoviewContainerTabs").find(".infoTableView");
  loadInfoTable(srcPane);
}
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