(function(a){a.fn.lwsReadForm=function(){var b={};function c(d,e){if(d.endsWith("[]")){d=d.substr(0,d.length-2);if(b[d]!=undefined){b[d].push(e)}else{b[d]=[e]}}else{b[d]=e}}a(this).find("span[data-name]").each(function(e,f){var d=a(f).data("name");if(d!=undefined&&d.length>0){c(d,a(f).html())}});a(this).find("input:not([type='radio'], [type='checkbox']), select, textarea").each(function(g,e){var f=a(e).attr("name");if(f!=undefined&&f.length>0){var d=a(e).prop("tagName");if(d=="select"){c(f,a(e).find("option:selected").val())}else{c(f,a(e).val())}}});a(this).find("input[type='radio']").each(function(f,d){var e=a(d).attr("name");if(a(d).prop("checked")&&e!=undefined&&e.length>0){c(e,a(d).val())}});a(this).find("input[type='checkbox']").each(function(f,d){var e=a(d).attr("name");if(e!=undefined&&e.length>0){c(e,(a(d).prop("checked"))?a(d).val():"")}});return b};a.fn.lwsWriteForm=function(e,c,d){function b(f,h,g){if(g!=undefined){f.data("value",((typeof h)=="string"?h:lwsBase64.fromObj(h))).trigger("change")}else{f.val(h).trigger("change")}}a(this).find("input:not([type='radio'], [type='checkbox']), select, textarea").each(function(h,f){var i=a(f).data("lw_name");var g=(i!=undefined?i:a(f).attr("name"));if(g!=undefined&&g.length>0){if(e[g]!=undefined){b(a(f),a("<div>").html(e[g]).text(),i)}else{if(c===true){b(a(f),"",i)}}}else{if(d===true){b(a(f),"",i)}}});a(this).find("span[data-name]").each(function(g,h){var f=a(h).data("name");if(f!=undefined&&f.length>0){if(e[f]!=undefined){a(h).html(e[f]).trigger("change")}else{if(c===true){a(h).text("").trigger("change")}}}else{if(d===true){a(h).text("").trigger("change")}}});a(this).find("input[type='radio']").each(function(h,f){var g=a(f).attr("name");if((g!=undefined&&g.length>0)||d===true){if(e[g]!=undefined||d===true||c===true){a(f).prop("checked",false)}}});a(this).find("input[type='radio']").each(function(h,f){var g=a(f).attr("name");if(g!=undefined&&g.length>0){if(e[g]!=undefined&&e[g]==a(f).val()){a(f).prop("checked",true).trigger("change")}}});a(this).find("input[type='checkbox']").each(function(h,f){var g=a(f).attr("name");if(g!=undefined&&g.length>0){if(e[g]!=undefined){a(f).prop("checked",e[g]==a(f).val()).trigger("change")}else{if(c===true){a(f).prop("checked",false).trigger("change")}}}else{if(d===true){a(f).prop("checked",false).trigger("change")}}});return a(this)};a.fn.lwsIsDark=function(c){if(typeof(c)==="undefined"){c="background-color"}var b=this.css(c).match(/\d+/g);if(b!=null){var d=(0.2126*b[0])+(0.7152*b[1])+(0.0722*b[2]);return(d<100)}else{return false}};a.fn.lwsMatchPattern=function(){var b=true;function c(e,f){var d=a(f);var g=new RegExp(d.data("pattern"),d.data("pattern-flags"));if(!g.test(d.val())){b=false;if(d.data("pattern-title")!=undefined&&d.data("pattern-title").length>0){alert(d.data("pattern-title"))}return false}}if(a(this).prop("tagName").toUpperCase()=="INPUT"){if(a(this).data("pattern")!=undefined){c(0,this)}}else{a(this).find("input[data-pattern]").each(c)}return b}})(jQuery);