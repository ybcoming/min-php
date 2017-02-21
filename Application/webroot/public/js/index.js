
function index_zoomin(a){
	clearInterval(a.timer);
	a.timer=setInterval(function(){
		var cw = a.width; 
		if(cw<170){
			a.width= cw+1;
		}else{ 
			clearInterval(a.timer);	
		}
	},10);
}
		
function index_zoomout(a){
	clearInterval(a.timer);
	a.timer=setInterval(function(){
		var cw=a.width;
		if(cw<171 && cw>160){
			a.width=cw-1;
		}else{
			clearInterval(a.timer)
		}
	},10);
}  
	
  Min.event.bind('hot','mouseover',{handler:function(e){
	index_zoomin(e.delegateTarget);
  },selector:'img.ad_floor_item_img'});
    
  Min.event.bind('hot','mouseout',{handler:function(e){
	index_zoomout(e.delegateTarget);
  },selector:'img.ad_floor_item_img'});
 /* 
function finddl(){
	var dls = doc.getElementsByTagName('dl');

	Min.obj.each(dls,function(a){

		if(a.parentNode.className=='goods' && a.getElementsByTagName('img').length>0 ){
	
			Min.event.bind(a,'mouseover',{handler:function(){
				
				var b = this.getElementsByTagName('img')[0];
				Min.css.addClass('opacity',b);
				setTimeout(function(){Min.css.removeClass('opacity',b);},120);
			},p:true});
			
			
			
		}
	});
}
finddl();

*/

Min.obj.each(query(doc,'.goods'),function(a){
Min.event.bind(a,'mouseover',{handler:function(){
				console.log('mouseenter');
				var b = this.getElementsByTagName('img')[0];
				Min.css.addClass('opacity',b);
				setTimeout(function(){Min.css.removeClass('opacity',b);},120);
			},client:'mouseenter',selector:'dl'});

/*			
Min.event.bind(a,'mouseout',{handler:function(){
				console.log('mouseleave');
				
			},p:true,client:'mouseleave',selector:'dl'});			
			
*/

}); 























/*

if (!document.querySelectorAll) {
    document.querySelectorAll = function (selector) {
        var doc = document,
            head = doc.documentElement.firstChild,
            styleTag = doc.createElement('STYLE');
        head.appendChild(styleTag);
        doc.__qsaels = [];

        if (styleTag.styleSheet) {   // for IE
            styleTag.styleSheet.cssText = selector + "{x:expression(document.__qsaels.push(this))}";
        } else {                // others
            var textnode = document.createTextNode(selector + "{x:expression(document.__qsaels.push(this))}");
            styleTag.appendChild(textnode);
        }
        window.scrollBy(0, 0);

        return doc.__qsaels;
    }
}

if (!document.querySelector) {
    document.querySelector = function (selectors) {
        var elements = document.querySelectorAll(selectors);
        return (elements.length) ? elements[0] : null;
    };
}

if (typeof HTMLElement != "undefined") {
    HTMLElement.prototype.querySelector = document.querySelector;
    HTMLElement.prototype.querySelectorAll = document.querySelectorAll;
}
else {
    var a = document.getElementsByTagName("*"), l = a.length, i;
    for (i = 0; i < l; i++) {
        a[i].querySelector = document.querySelector;
        a[i].querySelectorAll = document.querySelectorAll;
    }
}

alert(document.querySelectorAll('.goods dl').length);
alert(typeof HTMLElement != "undefined");

*/