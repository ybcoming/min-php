/*      product detail      */
function menuFixed(id){ 
	var obj = _$(id); 
	var _getHeight = Min.dom.getBounds(obj); 
	window.onscroll = function(){ 
		changePos(obj,_getHeight.top); 
	} 
} 
function changePos(obj,height){ 
	var shop_id = _$('product-shop-id'),
	 	scrollTop = Min.dom.getScroll('top');
	
	if(scrollTop < height){ 
		Min.css.removeClass('fixed',obj);  
		Min.css.removeClass('fixed',shop_id);
		_$('pnt-add-to-cart').style.display="none";
	}else{ 
		 
		_$('pnt-add-to-cart').style.display="block"; 
		Min.css.addClass('fixed',obj); 
		Min.css.addClass('fixed',shop_id);
	} 
} 


if( _$('product-nav-tab')){
 if(!Min.UA.isIE6) 
 menuFixed('product-nav-tab');

var navs= _$('product-nav-tab').getElementsByTagName('li');
var navs_length= navs.length;
for(var i=0;i<navs_length;i++){

	navs[i].onclick=function(){

		if(this.getAttribute('for')=='product-comment'){
				_$('product-comment-title').style.display="none";
				}else{
				_$('product-comment-title').removeAttribute('style');
				}
		
		for(var k=0;k<navs_length;k++){

			var id= navs[k].getAttribute('for');
			if(id){

				if( this == navs[k] ){
					_$(id).style.display="block";
					this.className="pnt-selected";
					}else{
					_$(id).style.display="none";
					navs[k].className='';
				}
				_$('product-comment').style.display="block";
			}
		}
		window.location.hash="product-desc";
		window.location = window.location;
 }


}
 
 }
 
 
 
 /* product list */
 
 
 
function heremore(obj){
var target=Min.dom.pre(obj);
var height=parseInt(target.clientHeight);
var width= parseInt(target.offsetWidth);
target.style.height=2*height+'px';

target.style.width=width+20+'px';
target.style.overflow="auto";
obj.setAttribute('abcd','show');
}


function showmore(obj){
var status =obj.parentNode.getAttribute('abcd');
if(status=='show') return;
heremore(obj.parentNode);
obj.innerHTML='取消<i class="icon-right iconfont">&#xe66c;</i>';
obj.removeAttribute('onclick');
obj.onclick=function(){hidemore(obj)};
}

function hidemore(obj){

var status = obj.parentNode.getAttribute("abcd");
if(status=='hide') return;
var target = Min.dom.pre(obj.parentNode);
target.scrollTop=0;
target.style.cssText = '';
target.style.overflow="hidden";
obj.innerHTML='更多<i class="icon-right iconfont">&#xe66c;</i>';
obj.parentNode.setAttribute('abcd','hide');
obj.onclick=function(){showmore(obj)};
}

function selectmore(obj){
//显示列表
var pre=obj.parentNode;
var status =pre.getAttribute('abcd');
if(status =='show') {
hidemore(Min.dom.pre(obj));
}
heremore(pre);
//关闭更多按钮 Onclick 事件

//显示BUTTON
 var bigdiv = document.createElement("DIV");
 bigdiv.className="multi-select-button";
 var subspan = document.createElement("span");
 subspan.innerHTML="取消";
 subspan.onclick =function(){hide_selectmore(this)};
 bigdiv.appendChild(subspan);
 obj.parentNode.parentNode.appendChild(bigdiv);
 obj.removeAttribute('onclick');
 obj.onclick=function(){return;};
 
 //显示border:
 
 //obj.parentNode.parentNode.parentNode.style.border="2px solid silver";
 
}


 function hide_selectmore(obj){

 var tmp= Min.dom.pre(obj.parentNode).getElementsByTagName('em')[0];

 tmp.onclick=function(){selectmore(this)};
 
 obj.parentNode.parentNode.parentNode.removeAttribute('style');
  hidemore(Min.dom.pre(obj.parentNode).getElementsByTagName('span')[0]);
 obj.parentNode.parentNode.removeChild(obj.parentNode);
 }
 

 
 