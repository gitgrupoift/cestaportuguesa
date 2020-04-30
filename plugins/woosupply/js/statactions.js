(function(a){a.fn.lwsWooSupplySortTableResetSortClass=function(){var b=this;b.find(".lws-woosupply-stat-th-sort").each(function(){a(this).removeClass("lws-icon-select_arrow_down lws-icon-select_arrow_up lws-ws-stat-icon-hidden lws-ws-stat-icon-sel");a(this).addClass("lws-icon-select_arrow_up lws-ws-stat-icon-hidden")});return b};a.fn.lwsWooSupplySortTable=function(e,b){var q=a(this);var n=0;var c=true;var h=0;var l=false;var d="asc";var f=(b=="num"||b=="price");var g=q.find("tbody");var k=q.find("thead");if(g.length<=0||k.length<=0){console.log("Expect a table structure with <thead> and <tbody>");return false}var r=g.find("tr");if(r.length<=1){return"none"}while(c){c=false;var p=g.find("tr").first();var j=p.next("tr");while(j.length>0){l=false;var o=a(p.find("td")[e]).text();var m=a(j.find("td")[e]).text();if(d=="asc"){if((f&&parseFloat(o)>parseFloat(m))||(!f&&o.toLowerCase()>m.toLowerCase())){l=true;break}}else{if(d=="desc"){if((f&&parseFloat(o)<parseFloat(m))||(!f&&o.toLowerCase()<m.toLowerCase())){l=true;break}}}p=j;j=p.next("tr")}if(l){j.detach().insertBefore(p);c=true;h++}else{if(h==0&&d=="asc"){d="desc";c=true}}}return d};lwsWooSupplyFormatData=function(d,c,b){if(c=="price"){d=Number(d).toFixed(2)+b}return d};a.fn.lwsWooSupplyRefreshTable=function(f,h,j){var k=this;var b=k.find("tbody");var d=k.find("thead");var e=k.data("chart-id")!=undefined?a("#"+k.data("chart-id")):[];var g=k.data("currency")!=undefined?k.data("currency"):"";if(b.length<=0||d.length<=0){console.log("Expect a table structure with <thead> and <tbody>")}else{b.removeClass("lws-woosupply-table-refresh-error");b.addClass("lws-woosupply-table-refresh-loading");b.empty();var c={action:f,source:k.data("source"),min:h,max:j};a.getJSON(lws_ajax_url,c,function(n){e.lwsWooSupplyDrawChart(n,g,h,j);var p=e.closest(".lws-woosupply-stat-container").outerWidth();var m=(p-330)+"px";e.find("#lws-wsstat-chart").css("width",m);var o=(Array.isArray(n)||n.body==undefined||!Array.isArray(n.body));var l=d.find("th");a.each(o?n:n.body,function(q,s){var r=a("<tr>",{"class":s.order_type});l.each(function(){var u=a(this);var w=u.data("column");var t=u.data("sorttype");var v=a("<td>",{"class":w});v.html(s[w]!=undefined?w=="order_id"?("<a target='_new' href='"+s.order_url+"'>"+s[w]+"</a>"):lwsWooSupplyFormatData(s[w],t,g):"&nbsp;");v.appendTo(r)});r.appendTo(b)});if(!o&&n.foot!=undefined){a.each(k.find("tfoot").find("td[data-column]"),function(r,q){if(n.foot[q.data("column")]!=undefined){q.html(n.foot[q.data("column")])}})}}).fail(function(){console.log("error on table data loading");b.addClass("lws-woosupply-table-refresh-error")}).always(function(){b.removeClass("lws-woosupply-table-refresh-loading")})}return k};a.fn.lwsWooSupplyPeriodShift=function(b){var f=this.data("min-id")!=undefined?a("#"+this.data("min-id")):[];var c=this.data("max-id")!=undefined?a("#"+this.data("max-id")):[];if(f.length>0&&c.length>0){var e=new Date(f.val());var d=new Date(c.val());diff=Math.abs(d-e)*(b==false?-1:1);var e=new Date(e.valueOf()+diff);var d=new Date(d.valueOf()+diff);if(e<=d){f.val(e.toISOString().split("T")[0]);c.val(d.toISOString().split("T")[0])}else{f.val(d.toISOString().split("T")[0]);c.val(e.toISOString().split("T")[0])}c.trigger("change")}return this};a.fn.lwsWooSupplyMonthShift=function(f){var b=this.data("min-id")!=undefined?a("#"+this.data("min-id")):[];var e=this.data("max-id")!=undefined?a("#"+this.data("max-id")):[];if(b.length>0&&e.length>0){var c=0;var k=new Date(b.val());k.setHours(0);var g=f==false?-1:1;var l=k.getMonth()+g;if(l>11){l=0;c=1}if(l<0){l=11;c=-1}var d=k.getFullYear()+c;var h=k.getTimezoneOffset()/60;var m=new Date(d,l,1,0-h,0,0);var j=new Date(d,l+1,0,0-h,0,0);b.val(m.toISOString().split("T")[0]);e.val(j.toISOString().split("T")[0]);e.trigger("change")}return this};a.fn.lwsWooSupplyDrawChart=function(k,p,c,j){var s=new Array("January","February","March","April","May","June","July","August","September","October","November","December");var r=new Date(c);var d=new Date(j).getDate();var e=new Array();var m=new Array();var q=new Array();var o=new Array();for(let i=0;i<d;i++){e[i]=String("00"+(i+1)).slice(-2);m[i]=0;q[i]=0;o[i]=0}for(let i=0;i<k.length;i++){var l=new Date(k[i]["order_date"]).getDate()-1;if(k[i]["invoice"]!=undefined&&k[i]["invoice"]!=""){m[l]=Number(k[i]["invoice"])}else{m[l]+=(k[i]["total"]!=undefined)?Number(k[i]["total"]):0}q[l]+=(k[i]["total_sales"]!=undefined)?Number(k[i]["total_sales"]):0}var b=0;for(let i=0;i<d;i++){b+=q[i]-m[i];o[i]=Math.round(b*100)/100}var g=s[r.getMonth()]+" "+r.getFullYear();var h=this;var p="€";var f=h.find("#lws-wsstat-chart");var n=new Chart(f,{type:"bar",data:{labels:e,datasets:[{label:"Margin",type:"line",data:o,borderColor:"rgb(247,147,30)",backgroundColor:"transparent",yAxisID:"right-y-axis"},{label:"Supply Orders",data:m,backgroundColor:"rgb(158,0,93)",yAxisID:"left-y-axis"},{label:"Customer Orders",data:q,backgroundColor:"rgb(63,169,245)",yAxisID:"left-y-axis"}]},options:{scales:{yAxes:[{id:"left-y-axis",type:"linear",position:"left",gridLines:{display:true},scaleLabel:{display:true,labelString:"Orders Amounts"},ticks:{callback:function(v,u,t){return v+p}}},{id:"right-y-axis",type:"linear",position:"right",gridLines:{display:false},scaleLabel:{display:true,labelString:"Margin"},ticks:{callback:function(v,u,t){return v+p}}}]},title:{display:true,text:g},legend:{position:"bottom"}}})}})(jQuery);jQuery(function(a){a("table.lws_woosupply_statistics_table_sortable").each(function(){a(this).lwsWooSupplySortTableResetSortClass()});a("table.lws_woosupply_statistics_table_sortable th").mouseover(function(){var b=a(this).find(".lws-woosupply-stat-th-sort");if(b.hasClass("lws-ws-stat-icon-sel")){b.addClass("lws-ws-stat-icon-hover");b.hasClass("lws-icon-select_arrow_up")?b.addClass("lws-icon-select_arrow_down").removeClass("lws-icon-select_arrow_up"):b.addClass("lws-icon-select_arrow_up").removeClass("lws-icon-select_arrow_down")}else{b.addClass("lws-ws-stat-icon-hover").removeClass("lws-ws-stat-icon-hidden")}});a("table.lws_woosupply_statistics_table_sortable th").mouseout(function(){var b=a(this).find(".lws-woosupply-stat-th-sort");if(b.hasClass("lws-ws-stat-icon-sel")){if(b.hasClass("outTemp")){b.removeClass("outTemp")}else{b.hasClass("lws-icon-select_arrow_up")?b.addClass("lws-icon-select_arrow_down").removeClass("lws-icon-select_arrow_up"):b.addClass("lws-icon-select_arrow_up").removeClass("lws-icon-select_arrow_down")}b.removeClass("lws-ws-stat-icon-hover")}else{b.addClass("lws-ws-stat-icon-hidden").removeClass("lws-ws-stat-icon-hover")}});a("table.lws_woosupply_statistics_table_sortable th").click(function(){var c=a(this).closest("table");var b=c.lwsWooSupplySortTable(a(this).index(),a(this).data("sorttype"));c.lwsWooSupplySortTableResetSortClass();var d=a(this).find(".lws-woosupply-stat-th-sort");if(b=="desc"){d.removeClass("lws-icon-select_arrow_up").addClass("lws-icon-select_arrow_down")}d.removeClass("lws-ws-stat-icon-hidden").addClass("lws-ws-stat-icon-sel outTemp")});a("table.lws_woosupply_statistics_ajax_source").each(function(){var c=a(this);if(c.data("ajax")!=undefined){var d=c.data("min-id")!=undefined?a("#"+c.data("min-id")):[];var b=c.data("max-id")!=undefined?a("#"+c.data("max-id")):[];if(d.length>0&&b.length>0){c.lwsWooSupplyRefreshTable(c.data("ajax"),d.val(),b.val());d.change(function(){c.trigger("refresh")});b.change(function(){c.trigger("refresh")});c.on("refresh",function(){c.lwsWooSupplyRefreshTable(c.data("ajax"),a("#"+c.data("min-id")).val(),a("#"+c.data("max-id")).val());if(c.hasClass("lws_woosupply_statistics_table_sortable")){c.lwsWooSupplySortTableResetSortClass()}})}}});a(".lws_woosupply_stats_period_next").click(function(){a(this).lwsWooSupplyMonthShift(true)});a(".lws_woosupply_stats_period_previous").click(function(){a(this).lwsWooSupplyMonthShift(false)})});