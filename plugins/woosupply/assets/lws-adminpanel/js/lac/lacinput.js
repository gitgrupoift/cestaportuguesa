(function(a){a.widget("lws.lac_input",{options:{classe:"",name:"",placeholder:"",delay:300,minsearch:1,minoption:2,minlength:1},_create:function(){this._setOptions();this._createStructure();this.currentIndex=-1;this.resLoad={};this.resLoad.target=this;this.resLoad.fn=this._ajaxLoading;this.resChange={};this.resChange.target=this;this.resChange.fn=this._resultChanged;this._manageModel();a(this.element).prop("autocomplete","off");this.resList=a(this.element).lac_model("getSource");this._setResList(this.resList);this.container.on("click",".lac-input-showmore",this._bind(this._showMore,this));this.container.on("click",".lac-input-item",this._bind(this._selectItem,this));this.container.on("blur",".lac-input-wrapper",this._bind(this._manageFocus,this));this.container.on("keydown",this._bind(this._manageKeys,this));var b=this;var d;var c=[];this.container.keyup(function(e,f){c[0]=e.key;c[1]=e.keyCode;sentData=[c];d&&clearTimeout(d);d=setTimeout(b._bindD(b._manageSearch,b,sentData),b.options.delay)})},_bind:function(b,c){return function(){return b.apply(c,arguments)}},_bindD:function(b,c,d){return function(){return b.apply(c,d)}},_setOptions:function(){if(this.element.data("placeholder")!=undefined){this.options.placeholder=this.element.data("placeholder")}if(this.element.data("required")!=undefined){this.options.required=this.element.data("required")}if(this.element.data("delay")!=undefined){this.options.delay=this.element.data("delay")}if(this.element.data("class")!=undefined){this.options.classe=this.element.data("class")}if(this.element.data("name")!=undefined){this.options.name=this.element.data("name")}if(this.element.data("shared")!=undefined){this.options.shared=this.element.data("shared")}},_createStructure:function(){$reqborder=(this.options.required)?"lws-reqinpborder":"";this.container=a("<div>",{"class":"lac-input-wrapper "+$reqborder,tabIndex:"-1"}).append(a("<div>",{"class":"lac-input-showmore lws-icon-eye-plus"})).append(a("<div>",{"class":"lac-input-list","data-open":false})).append(a("<div>",{"class":"lac-input-error"})).insertAfter(this.element);if(this.element.css("display")=="none"){this.container.hide()}this.element.addClass("lac-input-text").detach().prependTo(this.container);this.showMoreButton=this.container.find(".lac-input-showmore");this.selectList=this.container.find(".lac-input-list");this.textInput=this.container.find(".lac-input-text").addClass(this.options.classe);if(this.options.required){this.textInput.lws_required()}},_manageModel:function(){if(this.options.shared){if(a("#sha-"+this.options.shared).length){this.model=a("#sha-"+this.options.shared)}else{this.model=a("<input>",{id:"sha-"+this.options.shared,type:"hidden"}).appendTo(a("body"))}}else{this.model=this.element}a(this.model).lac_model({mode:"research",origin:this})},_recursiveList:function(d){var b=[];for(var c in d){if(d[c].group!=undefined){retour=this._recursiveList(d[c].group);if(retour.length>0){if(retour[0][0].className!="lac-input-optgroup"){var e=a("<div>",{"class":"lac-input-optgroup","data-value":d[c].value,html:d[c].label});b.push(e)}b=a.merge(b,retour)}}else{this.selectIndex+=1;var e=a("<div>",{"class":"lac-input-item lac-item-"+this.selectIndex,"data-value":d[c].value,"data-label":d[c].label,"data-index":this.selectIndex});if(d[c].html!=undefined){e.html(d[c].html)}else{e.text(d[c].label)}b.push(e)}}return b},_setResList:function(c){this.selectList.empty();this.currentIndex=-1;this.selectIndex=-1;var d=this._recursiveList(c);for(var b=0;b<d.length;b++){d[b].appendTo(this.selectList)}this.container.find(".lac-input-item").removeClass("lac-highlighted");this.textInput.outerWidth(this.selectList.outerWidth())},_selectItem:function(b,c){item=a(b.currentTarget);this.textInput.val(item.data("label"));this.currentIndex=item.data("index");this._closeList()},_showMore:function(b,c){a(this.model).lac_model("showMore",this)},_openList:function(){if(this.showMore){this.showMoreButton.show()}this.selectList.data("open",true);this.selectList.show()},_closeList:function(){this.showMoreButton.hide();this.selectList.data("open",false);this.selectList.hide()},_manageFocus:function(b,c){if(a(b.originalEvent.relatedTarget).closest(".lac-input-wrapper").length>0){return}this._closeList()},_showError:function(b){this.container.find(".lac-select-error").html(b).show().delay(1000).fadeOut(500)},_manageSearch:function(b){if(/[a-zA-Z0-9-_ ]/.test(String.fromCharCode(b[1]))){a(this.element).lac_model("research",this.textInput.val(),this)}},_manageKeys:function(d,e){if(d.key=="ArrowDown"){this.container.find(".lac-item-"+this.currentIndex).removeClass("lac-highlighted");this.currentIndex=(this.currentIndex+1>this.selectIndex)?0:this.currentIndex+1;var c=this.container.find(".lac-item-"+this.currentIndex);c.addClass("lac-highlighted");this.textInput.val(c.data("label"));this.selectList.scrollTop(Math.floor(c.position().top)+Math.floor(this.selectList.scrollTop()));if(!this.selectList.data("open")){this._openList()}}if(d.key=="ArrowUp"){this.container.find(".lac-item-"+this.currentIndex).removeClass("lac-highlighted");this.currentIndex=(this.currentIndex-1<0)?this.selectIndex:this.currentIndex-1;var c=this.container.find(".lac-item-"+this.currentIndex);c.addClass("lac-highlighted");this.textInput.val(c.data("label"));this.selectList.scrollTop(Math.floor(c.position().top)+Math.floor(this.selectList.scrollTop()));if(!this.selectList.data("open")){this._openList()}}if(d.key=="Enter"){this.currentIndex=-1;this.textInput.trigger("blur")}if(d.key=="Backspace"||d.key=="Delete"||d.key=="Suppr"){this._closeList();this.currentIndex=-1;var b=this;setTimeout(function(){if(b.textInput.val().length==0){b.resList=a(b.model).lac_model("getSource");b._setResList(b.resList)}},50)}},_ajaxLoading:function(){this.textInput.addClass("lac-loading")},_resultChanged:function(b){this.textInput.removeClass("lac-loading");if(b[1]!="ok"){this.resList=b[0];this._setResList(this.resList);this.showMore=b[2];if(!this.showMore){this.showMoreButton.hide()}this._showError(b[1])}else{if(!jQuery.isEmptyObject(b[0])){this.resList=b[0];this._setResList(this.resList);this.showMore=b[2];if(!this.showMore){this.showMoreButton.hide()}this._openList()}else{this.resList=[];this._closeList();this.selectList.empty()}}}})})(jQuery);jQuery(function(a){a(".lac_input").lac_input()});