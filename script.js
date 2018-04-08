$(".infoviewContainerTabs").find(".infoTableView").each(function() {
  src=$(this).closest(".tab-pane").attr("id");
  $(".infoviewContainerTabs").find(".nav.nav-tabs").find("a[href='#"+src+"']").attr("data-onshowncallback","loadInfoTableCallback");
});

function loadInfoTableCallback(srcTag) {
  srcPane=$($(srcTag).attr("href"),".infoviewContainerTabs").find(".infoTableView");
  cmd=srcPane.data('cmd');
  if(typeof window[cmd]=='function') {
    window[cmd](srcPane);
  } else {
    loadInfoTable(srcPane);
  }
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
        err=$(this).attr("name")+" can not be empty";
      }
    }
    q.push($(this).attr("name")+"="+encodeURIComponent($(this).val()));
  });
// 	console.log(q,frm);
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
		  q=["dtuid="+frm.closest(".infoTableView").data("refhash")];
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
