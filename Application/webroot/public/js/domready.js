var site_domain = 'a.com';
// 顶栏 初始化
Min.ready('shortcut-login', function(){

	var nickname = getCookie('nickname');
	var login = getCookie('logged');

	if( nickname && /^[A-Za-z0-9_\-\u4e00-\u9fa5]+$/.test(nickname) ){
	  
	  var a = _$('shortcut-regfree').getElementsByTagName('a')[0];
	  a.innerHTML='您好,&nbsp;&nbsp;'+nickname+'&nbsp;...';
	  a.href = 'http://account.' + site_domain + '/view.html';
	}
	 
	if(null === login ){

			JSONP.get( 'http://passport.' + site_domain + '/login/islogged.html', {}, function(data){
			 
				 if( data.status == 1 ) { 
					_$('shortcut-login').innerHTML='<a href ="http://passport.' + site_domain + '/logout.html " target="_blank" >退出</a>';
				 } 
			 });
	}else if( '1' === login){
		 
		_$('shortcut-login').innerHTML='<a href ="http://passport.' + site_domain + '/logout.html " target="_blank" >退出</a>';
	}

});
 // 首页幻灯片 1
Min.ready('slidebox', function(){
	var settings = {
			sc:'slidenabox',
			mc:'slidebox',
			autoplayer:3000
		};
  new scrollSlide(settings);
});  

// 首页SLIDEMENU
Min.ready('slidemenu', function(){
new slideMenu('slidemenu',10,15);
});


// 首页 floor 幻灯片 

Min.ready('floor-slidebox-1',function(){
	 var settings = {
			sc:'floor-slidenabox-1',
			mc:'floor-slidebox-1',
			resize :{y:true,w:240,h:330},
			autoplayer:3000,
			process :true
		};
new scrollSlide(settings);
	var settings2 = {
			sc:'floor-slidenabox-2',
			mc:'floor-slidebox-2',
			resize :{y:true,w:240,h:330},
			autoplayer:3000,
			process :true
		};
new scrollSlide(settings2);
});

// 商品放大 zoom
Min.ready( 'tsImgSCon',function(){
		var settings = {
			sc:'tsImgSCon',
			mc:'MagicZoom',
			resize :{y:true,w:390,h:390}
		};
        new MagicZoom(settings);
});

// 忽略
if( false && Min.UA.isIE6){
 Min.event.bind(document,'mouseover',{handler:function(e){
	var c  = e.delegateTarget.className;
	cs = c.split(' ');
	for(var i= 0 ,len =cs.length; i<len;i++){
		if(cs[i]!='' && cs[i]!= 'Hovmark' ){
			Min.css.addClass('d-hover',e.delegateTarget);
			break;
		}
	}
	Min.css.addClass('hover',e.delegateTarget);

	Min.event.bind(e.delegateTarget,'mouseout',{handler:function(e){
		Min.css.removeClass('d-hover',this);
		Min.css.removeClass('hover',this);	
 },once:true});

 },selector:'.Hovmark,li.menu_right,button.ui-cart'});
  
}   

// ie6 hover
if(Min.UA.isIE6){
window.CSSHover=(function(){var m=/(^|\s)((([^a]([^ ]+)?)|(a([^#.][^ ]+)+)):(hover|active|focus))/i;var n=/(.*?)\:(hover|active|focus)/i;var o=/[^:]+:([a-z\-]+).*/i;var p=/(\.([a-z0-9_\-]+):[a-z]+)|(:[a-z]+)/gi;var q=/\.([a-z0-9_\-]*on(hover|active|focus))/i;var s=/msie (5|6|7)/i;var t=/backcompat/i;var u={index:0,list:['text-kashida','text-kashida-space','text-justify'],get:function(){return this.list[(this.index++)%this.list.length]}};var v=function(c){return c.replace(/-(.)/mg,function(a,b){return b.toUpperCase()})};var w={elements:[],callbacks:{},init:function(){if(!s.test(navigator.userAgent)&&!t.test(window.document.compatMode)){return}var a=window.document.styleSheets,l=a.length;   for(var i=0;i<l;i++){this.parseStylesheet(a[i])}},parseStylesheet:function(a){if(a.imports){try{var b=a.imports;var l=b.length;for(var i=0;i<l;i++){this.parseStylesheet(a.imports[i])}}catch(securityException){}}try{var c=a.rules;var r=c.length;for(var j=0;j<r;j++){this.parseCSSRule(c[j],a)}}catch(someException){}},parseCSSRule:function(a,b){var c=a.selectorText;if(m.test(c)){var d=a.style.cssText;var e=n.exec(c)[1];var f=c.replace(o,'on$1');var g=c.replace(p,'.$2'+f);var h=q.exec(g)[1];var i=e+h;if(!this.callbacks[i]){var j=u.get();var k=v(j);b.addRule(e,j+':expression(CSSHover(this, "'+f+'", "'+h+'", "'+k+'"))');this.callbacks[i]=true}b.addRule(g,d)}},patch:function(a,b,c,d){try{var f=a.parentNode.currentStyle[d];a.style[d]=f}catch(e){a.runtimeStyle[d]=''}if(!a.csshover){a.csshover=[]}if(!a.csshover[c]){a.csshover[c]=true;var g=new CSSHoverElement(a,b,c);this.elements.push(g)}return b},unload:function(){try{var l=this.elements.length;for(var i=0;i<l;i++){this.elements[i].unload()}this.elements=[];this.callbacks={}}catch(e){}}};var x={onhover:{activator:'onmouseenter',deactivator:'onmouseleave'},onactive:{activator:'onmousedown',deactivator:'onmouseup'},onfocus:{activator:'onfocus',deactivator:'onblur'}};function CSSHoverElement(a,b,c){this.node=a;this.type=b;var d=new RegExp('(^|\\s)'+c+'(\\s|$)','g');this.activator=function(){a.className+=' '+c};this.deactivator=function(){a.className=a.className.replace(d,' ')};a.attachEvent(x[b].activator,this.activator);a.attachEvent(x[b].deactivator,this.deactivator)}CSSHoverElement.prototype={unload:function(){this.node.detachEvent(x[this.type].activator,this.activator);this.node.detachEvent(x[this.type].deactivator,this.deactivator);this.activator=null;this.deactivator=null;this.node=null;this.type=null}};window.attachEvent('onbeforeunload',function(){w.unload()});return function(a,b,c,d){if(a){return w.patch(a,b,c,d)}else{w.init()}}})();
Min.ready(true,function(){
CSSHover();
});
}


Min.ready('cart-content',function(){
var uls = _$('cart-content').getElementsByTagName('ul');
Min.obj.each(uls,function(a,b){
	if( b%2==1){
         a.style.backgroundColor="#f6f6f6";
    }
})
});

Min.ready('ck-address',function(){

Min.event.bind('ck-address','click',{handler:function(){

	var uls = _$('ck-address').getElementsByTagName('li');
	Min.obj.each(uls,function(a,b){
	 
	
		if(a.className == 'selected'){
			a.className='';
			 return false;
		}
	});
	this.className='selected';
	this.getElementsByTagName('input')[0].checked ='checked';
	
},selector:'li'});

var uls = _$('ck-address').getElementsByTagName('li');

});

Min.ready('region-current',function(){

	Min.event.bind('region-list','click' ,{handler:function(e){
	
		_$('region-select').style.left = '-1px';
	
		var p = Min.dom.grand(e.target,4), q = Min.dom.next(p);
		if(q){
			Min.css.addClass('on',q);
			p.getElementsByTagName('LABEL')[0].innerHTML = this.innerHTML;
			q.getElementsByTagName('LABEL')[0].innerHTML = '超市';
			Min.css.removeClass('on',p);
		}else{
			shop = Min.dom.grand(e.target,2).getElementsByTagName('DT')[0].innerHTML;
			p.getElementsByTagName('LABEL')[0].innerHTML = shop + ' - '+ this.innerHTML;
			_$("region-select").removeAttribute('style');
			_$("region-current").className='';
			setCookie('region',this.innerHTML);
			window.location.reload();
		}
	
	},selector:'span'});
	
	Min.event.bind('region-used','click',{handler:function(){
	
			setCookie('region',this.innerHTML);
			window.location.reload();
	
	},selector:'span'});
	
	Min.event.bind('region-list','click',{handler:function(){
		Min.obj.each(this.parentNode.parentNode.getElementsByTagName('li'),function(a){
			a.className='';
		});
		this.parentNode.className='on';
	
	},selector:'label'});
	 
	Min.event.bind('region-close','click',function(){
		_$("region-current").className='';
		_$('region-select').removeAttribute('style');;
	
	});
	 
	Min.event.bind('region-current','mouseover',{handler:function(e){
		e.currentTarget.className='region-current-hover';
	},client:'mouseenter'});
	Min.event.bind('region-current','mouseout',{handler:function(e){
		e.currentTarget.className='';
	},client :'mouseleave'});
	 
});