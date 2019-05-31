$(function() {
  initInfoviewTableUI();
});
function initInfoviewTableUI() {
  $(".infoviewContainerTabs").find(".infoTableView").each(function() {
    src=$(this).closest(".tab-pane").attr("id");
    $(".infoviewContainerTabs").find(".nav.nav-tabs").find("a[href='#"+src+"']").attr("data-onshowncallback","loadInfoTableCallback");
  });
}

function loadInfoTableCallback(srcTag) {
  srcPane=$($(srcTag).attr("href"),".infoviewContainerTabs").find(".infoTableView");
  cmd=srcPane.data('cmd');
  if(typeof window[cmd]=='function') {
    window[cmd](srcPane);
  } else {
    $(srcPane).find("thead .filters").find("select,input").val("");
    loadInfoTable(srcPane);
  }
}
function loadInfoTableFirst(src) {
  if($(src).find("table.table tbody").children().length<=0) {
    loadInfoTable(src);
  }
}
function loadInfoTableInline(src) {
  loadInfoTable($(src).closest(".infoTableView"));
}
function loadInfoTable(src) {
  ui=$(src).data("ui");
  cmd="fetchGrid";
  if(ui=="single") {
    cmd="fetchSingle";
  }

  //$(src).find("table.table tbody").append("<tr><td colspan=1000 align=center><div class='ajaxloading ajaxloading5'></div></td></tr>");
  $(src).find("table.table tbody").html("<tr><td colspan=1000 align=center><div class='ajaxloading ajaxloading5'></div></td></tr>");

  lx=_service("infoviewTable",cmd,"table")+"&dtuid="+$(src).data("dtuid")+"&refid="+$(src).data("dcode");
  lx+="&page="+$(src).data("page");
  lx+="&limit="+$(src).data("limit");
  if($(src).data("sort")!=null) {
    lx+="&sort="+$(src).data("sort");
  }
  // $(src).find("table.table tbody").load(lx,function(ans) {
  //       initInfoViewTableActions($(src).find("table.table tbody"));
  //   });
  qData = $(src).find("table.table thead .filters").find("input[name],select[name]").filter(function(a,b) {
      if($(b).val()!=null && $(b).val().length>0) return true; else return false;
    }).serialize();

  processAJAXPostQuery(lx, qData, function(htmlData) {
    $(src).find("table.table tbody").html(htmlData);
    if($(src).find("table.table tbody tr[data-refid]").length<=0) {
      pg = $(srcPane).data("page");
      pg = parseInt(pg);
      if(isNaN(pg)) pg = 0;
      else pg--;
      if(pg<0) pg = 0;

      $(src).data("page",pg);
    }
    initInfoViewTableActions($(src).find("table.table tbody"));
  });
}
function initInfoViewTableActions(refDiv) {
  //refDivPane = $(refDiv).closest(".infoTableView");//".infoviewContainer"
  $("*[cmd]",refDiv).click(function(e) {
      cmd=$(this).attr("cmd");
      cmdOriginalX=cmd;
      cmd=cmd.split("@");
      cmd=cmd[0];
      src=this;
      
      parentObject=$(src).closest(".infoTableView");
      if(parentObject.length<=0) {
        parentObject=$(src).closest(".infoviewContainer");
      }
      hash=parentObject.data('dcode');
      gkey=parentObject.data('dtuid');
      title=$(src).text();
      if(title==null || title.length<=0) {
        title=$(src).attr("title");
      }
      if(title==null || title.length<=0) {
        title="Dialog";
      }
      
      switch(cmd) {
            case "forms":case "reports":case "infoview":
              cmdX=cmdOriginalX.split("@");
              if(cmdX[1]!=null) {
                cmdX[1]=cmdX[1].replace("{hashid}",hash).replace("{gkey}",gkey);

                lgksOverlayFrame(_link("modules/"+cmd+"/"+cmdX[1]),title,function() {
                      hideLoader();
                    });
              }
            break;
            case "page":
              cmdX=cmdOriginalX.split("@");
              if(cmdX[1]!=null) {
                cmdX[1]=cmdX[1].replace("{hashid}",hash).replace("{gkey}",gkey);
                window.location=_link("modules/"+cmdX[1]);
              }
              break;
            case "module":case "popup":
              cmdX=cmdOriginalX.split("@");
              if(cmdX[1]!=null) {
                cmdX[1]=cmdX[1].replace("{hashid}",hash).replace("{gkey}",gkey);

                if(cmd=="module" || cmd=="modules") {
                  top.openLinkFrame(title,_link("modules/"+cmdX[1]),true);
                } else {
                  lgksOverlayFrame(_link("popup/"+cmdX[1]),title,function() {
                      hideLoader();
                    });
                }
              }
            break;
            default:
              if(typeof window[cmd]=="function") {
                window[cmd](src);
              } else {
                console.warn("Report CMD not found : "+cmd);
              }
        }
      });
}
function reloadInfoTable(src) {
  $(src).find("table.table tbody").html("");
  $(src).data("page",0);
  $(src).find(".info-form").find("input[name=refid]").detach();
  loadInfoTable(src);
}
function loadMore(src) {
  nx=$(src).data("page");
  nx=parseInt(nx);
  nx++;
  $(src).data("page",nx);
  loadInfoTable(src);
}
function submitInfoForm(src) {
  frm=$(src).closest(".info-form");
  q=["dtuid="+frm.find(">tr").data("refhash")];err=false;
  $("input[name],select[name],textarea[name]",frm).each(function(a,b) {
    if($(this).attr("required")!=null && ($(this).attr("required")===true || $(this).attr("required").length>1)) {
      if($(this).val()==null || $(this).val().length<=0) {
        err=$(this).attr("name").replace("_"," ")+" can not be empty";
      }
    }
    q.push($(this).attr("name")+"="+encodeURIComponent($(this).val()));
  });
//  console.log(q,frm);
  if(err===false) {
    if(frm.find("input[name=refid]").length>0) {
      lx=_service("infoviewTable","update-record");
    } else {
      lx=_service("infoviewTable","create-record");
    }
    processAJAXPostQuery(lx,q.join("&"),function(ans) {
      ans=ans.Data;
      if(ans.toLowerCase().indexOf('error')>=0) {
        lgksToast(ans);
      } else {
        $("input[name],select[name],textarea[name]",frm).each(function() {
          $(this).val($(this).data('value'));
        });
        reloadInfoTable($(src).closest(".infoTableView"));
      }
    },"json");
  } else {
    lgksToast(err);
  }
}
function editInfoRecord(src) {
  frm=$(src).closest(".infoview-table").find(".info-form");
  tr=$(src).closest("tr");
  tr.find('td[data-name]').each(function() {
    nm=$(this).data("name");
    $("input[name='"+nm+"'],select[name='"+nm+"'],textarea[name='"+nm+"']").val($(this).text());
  });
  frm.find("input[name=refid]").detach();
  frm.append("<input type='hidden' name='refid' value='"+tr.data("refid")+"' />");
}
function deleteInfoRecord(src) {
  lgksConfirm("Sure about deleting the record","Delete !!!",function(ans) {
    if(ans) {
        frm=$(src).closest(".infoview-table").find(".info-form");
        tr=$(src).closest("tr");
        q=["dtuid="+frm.closest(".infoTableView").data("dtuid")];
        q.push("refid="+tr.data("refid"));
        processAJAXPostQuery(_service("infoviewTable","delete-record"),q.join("&"),function(ans) {
             ans=ans.Data;
            if(ans.toLowerCase().indexOf('error')>=0) {
              lgksToast(ans);
            } else {
              tr.detach();
              //reloadInfoTable($(src).closest(".infoTableView"));
            }
        },"json");
    }
  });
}
function showInfoviewFilters(src) {
  $(src).closest("thead").find(".filters").toggleClass("hidden");
}
function showInfoviewTablePrev(src) {
  srcPane = $(src).closest(".infoTableView");

  pg = $(srcPane).data("page");
  pg = parseInt(pg);
  if(isNaN(pg)) pg = 0;
  else pg--;
  if(pg<0) pg = 0;

  $(srcPane).data("page",pg);
  loadInfoTableInline(src);
}
function showInfoviewTableNext(src) {
  srcPane = $(src).closest(".infoTableView");

  // nx = $(srcPane).find("tbody").children();
  pg = $(srcPane).data("page");
  pg = parseInt(pg);
  if(isNaN(pg)) pg = 0;
  else pg++;
  
  $(srcPane).data("page",pg);
  loadInfoTableInline(src);
}
function changeInfoviewPageLimit(src) {
  srcPane = $(src).closest(".infoTableView");
  $(srcPane).data("limit", $(src).val());
  loadInfoTableInline(src);
}
function sortInfoviewByColumn(src) {
  srcPane = $(src).closest(".infoTableView");
  if($(src).find(".fa").length>0) {
    if($(src).find(".fa-caret-up").length>0) {
      $(src).find(".fa-caret-up").detach();
      $(src).append('<i class="fa fa-caret-down pull-right"></i>');
      $(srcPane).data("sort",$(src).attr("name")+" ASC");
    } else {
      $(src).find(".fa-caret-down").detach();
      $(src).append('<i class="fa fa-caret-up pull-right"></i>');
      $(srcPane).data("sort",$(src).attr("name")+" DESC");
    }
  } else {
    $(src).closest("tr").find(".fa-caret-up").detach();
    $(src).closest("tr").find(".fa-caret-down").detach();
    $(src).append('<i class="fa fa-caret-up pull-right"></i>');
    $(srcPane).data("sort",$(src).attr("name")+" DESC");
  }
  loadInfoTableInline(src);
}