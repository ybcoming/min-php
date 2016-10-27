/***
与:hover, querySelectorAll相关的元素，不可以用 dispaly:none;否则无法获取元素
**/

var	win = window,
	doc = win.document,
	body = doc.body,
	docElem = doc.documentElement;
	
var Min={};
var _$ = function(id){
	return "string" == typeof id ? document.getElementById(id) : id;
}

Min.cache = {

	cacheData : {},
	uuid:1,
	expando : 'cache' + ( +new Date() + "" ).slice( -8 ) ,
 
    data : function( elem, val, data ){
    if( typeof elem === 'string' ){
        if( val !== undefined ){
			this.cacheData[elem] = val;
	    }
		return this.cacheData[elem];
	}
	else if( typeof elem === 'object' ){
		// 如果是window、document将不添加自定义属性
		// window的索引是0 document索引为1
		var index = elem === window ? 0 : 
				elem.nodeType === 9 ? 1 : 
				elem[this.expando] ? elem[this.expando] : 
				(elem[this.expando] = ++this.uuid);
			
			if( this.cacheData[index] == undefined ) { 
				this.cacheData[index] = {} ;
			}
				
			if( data !== undefined ){
			// 将数据存入缓存中
				this.cacheData[index][val] = data;
			}
		// 返回DOM元素存储的数据
		return this.cacheData[index][val];
	}
},
 
removeData : function( elem, val ){
	if( typeof elem === 'string' ){
		delete this.cacheData[elem];
	}
	else if( typeof elem === 'object' ){
		var index = elem === window ? 0 :
				elem.nodeType === 9 ? 1 :
				elem[this.expando];
			
		if( index === undefined ) return;		
		// 检测对象是否为空
	
			// 删除DOM元素所有的缓存数据
			delteProp = function(){
				delete Min.cache.cacheData[index];
				if( index <= 1 ) return;
				try{
					// IE8及标准浏览器可以直接使用delete来删除属性
					delete elem[Min.cache.expando];
				}
				catch ( e ) {
					// IE6/IE7使用removeAttribute方法来删除属性(document会报错)
					elem.removeAttribute( Min.cache.expando );
				}
			};

		if( val ){
			// 只删除指定的数据
			delete this.cacheData[index][val];
		 
			if( Min.util.isEmptyObj( this.cacheData[index] ) ){
				delteProp();
			}
		}
		else{
			delteProp();
		}
	}
}

};

Min.UA = {
	
	kernel 		:   Min.cache.data('UA') || 
					Min.cache.data('UA', (function(){
						var ua = navigator.userAgent.toLowerCase();

						return   window.ActiveXObject ?  'ie':
				
								ua.indexOf("webkit") != -1 ? 'safari':

								ua.indexOf("gecko") != -1 ?  'gecko' :

								ua.indexOf("opera") != -1 ? 'opera' : 'ie';
					})()),

	belowIE8	:  !-[1,],
	
	isIE 		:  !!window.ActiveXObject,
	
	isIE6		:  !-[1,] && !window.XMLHttpRequest,

	isIE7 		:  !-[1,] && !!window.XMLHttpRequest && (!document.documentMode||document.documentMode == 7),
	
	isIE8 		:  !-[1,] && (document.documentMode==8)
	

};

Min.css = {

	addClass : function (c, node) {
        if(!node) return;
        if( !this.hasClass(c,node) ) node.className =  node.className + ' ' + c ;
    },

	removeClass : function (c, node) {
        if(this.hasClass(c,node)){
			var reg = new RegExp("(^|\\s+)" + c + "(\\s+|$)", "g");
			node.className =  node.className.replace(reg, ' ');
		}
    },

	hasClass : function (c, node) {
        if(!node || !node.className) return false;
		 return node.className.match(new RegExp('(\\s|^)' + c + '(\\s|$)'));  
    },
	setOpacity : function(n,m){
		n.style["opacity"] = n.style["-moz-opacity"] = n.style["-html-opacity"] = m;
		n.style["filter"] = "alpha(Opacity=" + m*100 + ")";
	},
	addCssByLink : function(url){
	
		var link=doc.createElement("link");
		link.setAttribute("rel", "stylesheet");
		link.setAttribute("type", "text/css");
		link.setAttribute("href", url);
		
		var heads = doc.getElementsByTagName("head");
		if(heads.length)
			heads[0].appendChild(link);
		else
			doc.documentElement.appendChild(link);
	}

};

Min.util = {

	trim : function (str) {
        return str.replace(/^\s+|\s+$/g,'');
    },
	sleep : function(m){
    	var startTime = new Date().getTime(); 
    	while (new Date().getTime() < startTime + m); 
	},
	isEmptyObj : function( obj ) {
		var name;
		for ( name in obj ) {
			return false;
		}
		return true;
	},
	getQueryString : function(name) {
		var reg = new RegExp('(^|&)' + name + '=([^&]*)(&|$)', 'i');
		var r = window.location.search.substr(1).match(reg);
		if (r != null) {
			return unescape(r[2]);
		}
		return null;
	},

	checkCapslock : function(event,obj){
		var e = event||window.event;
	 
		var keyCode  =  e.keyCode||e.which;  
		var isShift  =  e.shiftKey ||(keyCode  ==   16 ) || false ;
		var itag=Min.dom.next(obj);	
		if (
		((keyCode >=   65   &&  keyCode  <=   90 )  &&   !isShift)
		// Caps Lock 打开，且没有按住shift键
		|| ((keyCode >=   97   &&  keyCode  <=   122 )  &&  isShift)
		// Caps Lock 打开，且按住shift键
		){  
			itag.innerHTML="&#xe648;";
			itag.style.color="red";	  
		}else{ 
			itag.innerHTML="&#xe63a;" ;
			itag.removeAttribute("style");
		}
	},
	 changeCheckbox : function() {
		var mylabel = document.getElementById('only-stock');
		if (mylabel.innerHTML == "√"){
		   mylabel.innerHTML = "&nbsp;";
		   mylabel.removeAttribute('style');
	   } else{
		   mylabel.innerHTML = "√";
			 mylabel.setAttribute('style','border-color:#a10000');
		   }
	},
	
	 
	
	clone : function(obj){  
		var o;  
		if(typeof obj == "object"){  
			if(obj === null){  
				o = null;  
			}else{  
				if(obj instanceof Array){  
					o = [];  
					for(var i = 0, len = obj.length; i < len; i++){  
						o.push( this.clone(obj[i]));  
					}  
				}else{  
					o = {};  
					for(var k in obj){  
						o[k] = this.clone(obj[k]);  
					}  
				}  
			}  
		}else{  
			o = obj;  
		}  
		return o;  
	},
	 parseJSON : function( data ) {
        if ( !data ){
            return null;
        }

        // 标准浏览器可以直接使用原生的方法
        if( window.JSON && window.JSON.parse ){
            return window.JSON.parse( data );
        }
        
		var rValidtokens = /"[^"\\\n\r]*"|true|false|null|-?\d+(?:\.\d*)?(?:[eE][+\-]?\d+)?/g,    
    rValidescape = /\\(?:["\\\/bfnrt]|u[0-9a-fA-F]{4})/g,    
    rValidbraces = /(?:^|:|,)(?:\s*\[)+/g,
  
    rValidchars = /^[\],:{}\s]*$/; 
		
		
        if ( rValidchars.test( data.replace( rValidescape, '@' )
            .replace( rValidtokens, ']' )
            .replace( rValidbraces, '')) ) {

            return (new Function( 'return ' + data ))();
        }
    },
	htmlencode : function(s){  
		var div = document.createElement('div');  
		div.appendChild(document.createTextNode(s));  
		return div.innerHTML;  
	},  
	htmldecode :function (s){  
		var div = document.createElement('div');  
		div.innerHTML = s;  
		return div.innerText || div.textContent;  
	}  
	
	

};

Min.print ={

	debug : function(msg){
		if (console && console.log) {
			if(typeof msg == "String"){
				console.log(msg);
			}else{
				console.log( Min.util.clone(msg));
			}
		}else{
			alert(msg);
		}
	
	},
	
	log : function(msg){
		if (console && console.log) {
			console.log( msg);
		}else{
			alert(msg);
		}
	}
};

Min.dom = {

	next : function(element){
		var tmp = element.nextSibling;
		while( tmp!= null && tmp.nodeType!=1){
			tmp=tmp.nextSibling;
		}
		return tmp;
	},
	
	pre : function(element){
	
		var tmp = element.previousSibling;
		while( tmp!= null && tmp.nodeType!=1){
			tmp=tmp.previousSibling;
		}
		return tmp;
	},
	
	grand : function(element,level){
	
		if( level == 2 || level == undefined )
			return element.parentNode.parentNode;
		return this.grand(element.parentNode,level-1);
	},
	
	getBounds : function(e) {
		if (e.getBoundingClientRect) {
			var r = e.getBoundingClientRect(),
				wy = this.getScroll('top'),
				wx = this.getScroll('left'),
				ct  = document.documentElement.clientTop || document.body.clientTop || 0,
				cl  = document.documentElement.clientLeft || document.body.clientLeft || 0;
			return {
				'left': r.left + wx -cl,
				'top': r.top + wy -ct,
				'right': r.right + wx - cl,
				'bottom': r.bottom + wy - ct
			}
		} else {
			var left=0 , top=0, node = e;
			while (node) { 
				left += node.offsetLeft;
				top  += node.offsetTop; 
				node  = node.offsetParent; 
			};
			return {
				'right'  : left + e.offsetWidth, 
				'bottom' : top  + e.offsetHeight,
				'left' : left,
				'top' : top
			}
		}
	},
	capitalize : function( str ){
		var firstStr = str.charAt(0);
		return firstStr.toUpperCase() + str.replace( firstStr, '' );
	},
	getScroll : function( type ){
		var upType = this.capitalize( type );	
		return document.documentElement['scroll' + upType] || document.body['scroll' + upType];	
	},
	contains : function( a, b ){
        // 标准浏览器支持compareDocumentPosition
		if(b==null || b == undefined) return false;
        if( a.compareDocumentPosition ){
            return !!( a.compareDocumentPosition(b) & 16 );
        }
        // IE支持contains
        else if( a.contains ){
            return a !== b && a.contains( b );
        }

        return false;
    }
	
}

Min.obj = {

	extend : function (destination, source, override) {
		if (override === undefined) override = true;
		for (var property in source) {
			if (override || !(property in destination)) {
				destination[property] = source[property];
			}
		}
		return destination;
	},
	wrapper : function(me, parent) {
		var ins = function() { me.apply(this, arguments); };
		var subclass = function() {};
		subclass.prototype = parent.prototype;
		ins.prototype = new subclass;
		return ins;
	},
	methodReference : function(object, methodName) {	 
		var args= arguments[2]||[]; 
		return  function() {
			[].push.apply(arguments, args);
			return object[methodName].apply(object, arguments);
		}
	},
	imgLoad : function(a,b,c,d,e){
		e	= e || false;
		for(var args; args = a.shift();){
			var ok = (( Min.UA.belowIE8 && args.readyState ==='complete') || (!Min.UA.belowIE8 && args.complete == true ));
			if ( args.width == 0 || args.height == 0 ||  !ok ){
				if( typeof b == 'object' && b != null ){
					if( a.length == 0 ){
						c = c ||'init';
						Min.event.bind(args, "load", {handler: Min.obj.methodReference(b,c,d),once:true});
					}else{	
						Min.event.bind(args, "load", {handler:function(){Min.obj.imgLoad(a,b,c,d,true)},once:true});	
					}
				}
				return false;
			}
		}
		
		if( typeof b == 'object' && b != null && e== true ){
			c = c ||'init';
			setTimeout(Min.obj.methodReference(b,c,d),20);	
		}
		return true;
	},
	each :function( object, callback ) {
		if ( undefined === object.length ){
			for ( var name in object ) {
				if (false === callback( object[name], name, object )) break;
			}
		} else {
			for ( var i = 0, len = object.length; i < len; i++ ) {
				if (i in object) { if (false === callback( object[i], i, object )) break; }
			}
		}
	}
}



Min.ready = (function(){

	var eventQueue = [],
 
	isReady =false,

	isBind =false,
	
	ready = function(id,fn){
		
	if( id!=true) { if(!_$(id)) return ;}
		if (isReady) {
			fn.call(window);
		}else{	
			eventQueue.push(fn);
		}; 
		if (!isBind) {
			bindReady();
		}
	},
 
 
	bindReady = function(){
		if (isReady) return;
		if (isBind) return;
		isBind = true;
	 
		if (document.addEventListener) {
			document.addEventListener('DOMContentLoaded',function(){
					document.removeEventListener( 'DOMContentLoaded', arguments.callee, false );
					execFn();
				},false);
			window.addEventListener( "load", execFn, false );
		}
		else if (window.attachEvent) {
			document.attachEvent( 'onreadystatechange', function(){
					if( document.readyState === 'complete' ){
						document.detachEvent( 'onreadystatechange', arguments.callee );
						execFn();
					}
				});
			window.attachEvent( "onload", execFn );
			var top = false;
				 try {
					 top = window.frameElement == null && document.documentElement;
				 } catch(e) {}
				 if ( top && top.doScroll ) {
					doScroll();
				}
		};
	},
 
 
	doScroll = function(){
		try{
			document.documentElement.doScroll('left');
		}
		catch(error){
			return setTimeout(doScroll,5);
		};
		execFn();
	},
 
 
	execFn = function(){
		if (!isReady) {
			isReady = true;
			
			for (var i = 0,len = eventQueue.length; i < len; i++) {
			   try { 
					eventQueue[i].call(window);
			   }catch(e){}
			};
			delete eventQueue;
		};
	};
	
	return ready;

})();
 
if (typeof Array.prototype.forEach != "function") {
  Array.prototype.forEach = function (fn, context) {
    Min.obj.each( this, function(){ fn.apply(context, arguments); } );
  };
}
if (typeof Array.prototype.map != "function") {
  Array.prototype.map = function (fn, context) {
	var arr = [];
	Min.obj.each( this, function(){ 
		arr.push(fn.apply(context, arguments)); 
	});
	return arr;
  };
}
if (typeof Array.prototype.filter != "function") {
  Array.prototype.filter = function (fn, context) {
	var arr = [];
	Min.obj.each( this, function(item){
			fn.apply(context, arguments) && arr.push(item);
		});
	return arr;
  };
}
if (typeof Array.prototype.some != "function") {
  Array.prototype.some = function (fn, context) {
	var passed = false;
	Min.obj.each( this, function(){
		if ( fn.apply(context, arguments) ){ 
			passed = true; 
			return false; 
		};
	});
	return passed;
  };
}

if (typeof Array.prototype.every != "function") {
  Array.prototype.every = function (fn, context) {
	var passed = true;
	Min.obj.each( this, function(){
		if ( !fn.apply(context, arguments) ){ 
			passed = false; 
			return false;
		};
	});
	return passed;
  };
}
if (typeof Array.prototype.indexOf != "function") {
  Array.prototype.indexOf = function (elt, from) {
	var len = this.length;
	from = isNaN(from) ? 0
		: from < 0 ? Math.ceil(from) + len : Math.floor(from);
	for ( ; from < len; from++ ) {
		if ( this[from] === elt ) return from;
	}
	return -1;
  };
}

if (typeof Array.prototype.lastIndexOf != "function") {
  Array.prototype.lastIndexOf = function (elt, from) {
	var len = this.length;
	from = isNaN(from) || from >= len - 1 ? len - 1
		: from < 0 ? Math.ceil(from) + len : Math.floor(from);
	for ( ; from > -1; from-- ) {
		if ( this[from] === elt ) return from;
	}
	return -1;
  };
}

if (!Object.keys) {
		Object.keys = function(o) {
			if (o !== Object(o)) {
				throw new TypeError('Object.keys called on a non-object');
			}
			var k=[], p;
			for (p in o) {
				if (Object.prototype.hasOwnProperty.call(o,p)) {
					k.push(p);
				}
			}
			return k;
		};
	}




if (!document.querySelectorAll) {
	
    document.querySelectorAll = function (selectors) {
        var style = document.createElement('style'), elements = [], element;
        document.documentElement.firstChild.appendChild(style);
        document._qsa = [];

		 if (style.styleSheet) {   // for IE
            style.styleSheet.cssText = selectors + '{x-qsa:expression(document._qsa && document._qsa.push(this))}';
        } else {                // others
            var textnode = document.createTextNode(selectors + "{x-qsa:expression(document._qsa && document._qsa.push(this))}");
            style.appendChild(textnode);
        }

        window.scrollBy(0, 0);
        style.parentNode.removeChild(style);

        while (document._qsa.length) {
            element = document._qsa.shift();
            element.style.removeAttribute('x-qsa');
            elements.push(element);
        }
        document._qsa = null;
        return elements;
    };
}

if (!document.querySelector) {
    document.querySelector = function (selectors) {
        var elements = document.querySelectorAll(selectors);
        return (elements.length) ? elements[0] : null;
    };
}

// 用于在IE6和IE7浏览器中，支持Element.querySelectorAll方法

var query = (function (){ 
   var idAllocator = 10000;
    function qsaWorkerShim(element, selector) {	
		if(element == doc){
			return document.querySelectorAll(selector);
		}else{
			var needsID = element.id === "";
			if (needsID) {
				++idAllocator;
				element.id = "__qsa" + idAllocator;
			}
		}
        try {
            return document.querySelectorAll("#" + element.id + " " + selector);
        }
        finally {
            if (needsID) {
                element.id = "";
            }
        }
    }
    function qsaWorkerWrap(element, selector) {
        return element.querySelectorAll(selector);
    }
    // Return the one this browser wants to use
    return !!document.createElement('div').querySelectorAll ? qsaWorkerWrap : qsaWorkerShim;
})();
	
	
if (Min.UA.isIE6) try {
    document.execCommand("BackgroundImageCache", false, true)
} catch(e) {};

if (Min.UA.isIE8){
Min.css.addCssByLink('http://cdn.qi.com/public/css/ie8.css');
}









/*

method:
var method='document|getElementsByTagName|prototype|length|apply|call|';

var css='style|removeAttribute|href|width|height|offsetWidth|offsetHeight|offsetTop|offsetBottom|top|bottom|left|right|length';

var math='floor|ceil';

var event = '';

var arr ='push|';

dom='parentNode|';

string:
var a2= 'style|px|ul|li|img|relative|absolute|';
*/
