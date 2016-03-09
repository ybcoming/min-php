function scrollSlide(setting){
	
	this.sc = setting['sc'];
	var container  =  _$(this.sc);
	if(container == null) return;
	this.mc = setting['mc'];		
	this.inited = false;
	this.muststop = false;
	this.clicked = 1;
	this.resize = setting['resize'] || {y:false};
	this.autoplayer = setting['autoplayer']|| 0 ;
	this.process = setting['process'] || false;
	
	// sc settings
	var	lis = container.getElementsByTagName('li'),
		len = lis.length;
	this.currentHover = lis[len-1];
	var	conwidth = container.offsetWidth;
	this.showsize = Math.floor(conwidth/this.currentHover.offsetWidth);
		
	this.row = Math.ceil(len/this.showsize); 	
	this.currentRow = 0;
	if(this.row > 1){
		container.getElementsByTagName("ul")[0].style.width = this.row*conwidth  + "px"; 
	}
	container.style.left = (container.parentNode.offsetWidth - conwidth)/2 + "px";
	container.style.visibility ="visible";
	var inner = Array();
	Min.obj.each(lis,function(a,b){
		a.setAttribute('data-index',b);
		inner[b] = '<li><a '+ (a.getAttribute('href') ? 'href="'+ a.getAttribute('href') + '"  target="_blank"' :'') + ' style="position:relative"></a></li>';
		a.removeAttribute('href');
	});
	
 // mc setting
	var mcontainer = _$(this.mc);	
		mcontainer.innerHTML = "<ul>"+ inner.join("")+"</ul>";
		mcontainer.style.position ='relative';

	this.slideNext();
};

scrollSlide.prototype.mcmouseover = function(){
 this.muststop = true;
 this.clicked++;
 this.stop();
}

scrollSlide.prototype.scrollLeft = function(){
	if( this.currentRow>0)
	{
		this.currentRow--;
		_$(this.sc).getElementsByTagName("ul")[0].style.marginLeft="-"+this.currentRow*(_$(this.sc).offsetWidth)+"px";
	}
};

scrollSlide.prototype.scrollRight = function(e){
	if(this.currentRow + 1 < this.row)
	{
		this.currentRow++;
		_$(this.sc).getElementsByTagName("ul")[0].style.marginLeft="-"+this.currentRow*(_$(this.sc).offsetWidth)+"px";
		var k = e.currentTarget.getAttribute('data-index')|| 0 ;
		if( k < this.currentRow ){
			e.currentTarget.setAttribute('data-index', this.currentRow);
			this.initsc();
		}
		
	}
};
scrollSlide.prototype.slidePre = function(e){

	this.stop();
	var container  =  _$(this.sc).getElementsByTagName('li'),
		len = container.length;
		
	e.delegateTarget = Min.dom.pre(this.currentHover)||container[len-1];
	this.clicked++;
	this.replaceSlide(e);
	setTimeout(Min.obj.methodReference(this,'run'),1000);

};
scrollSlide.prototype.slideNext = function(e){

	this.stop();
	var t = Min.dom.next(this.currentHover)|| _$(this.sc).getElementsByTagName('li')[0];
	
	if(e){
		 this.clicked++; 
		 e.delegateTarget = t; 
	}else{ 
		e = {delegateTarget:t};
	}
	if(this.replaceSlide(e)){
		e ? setTimeout(Min.obj.methodReference(this,'run'),2000) :  this.run();
	}
};
scrollSlide.prototype.replaceSlide = function(e){

	var ael = e.delegateTarget;
	if(ael == this.currentHover){
		Min.event.stopPropagation(e);
		return false;
	}
	var index = ael.getAttribute('data-index'),
		pre_index = this.currentHover.getAttribute('data-index'),
		lis = _$(this.mc).getElementsByTagName('li'),
		loaded = ael.getAttribute('loaded')|| false;
	
	if( !loaded ){ 
		ael.setAttribute('loaded',"1");
		var newImage = document.createElement("IMG");
			newImage.src = ael.getAttribute("mi");
		if(!Min.obj.imgLoad([newImage])){
			var self = this,
				times = this.clicked;
			Min.event.bind( newImage,'load',{handler:function(){
				ael.setAttribute('loaded',"2");
				ael.removeAttribute('mi');
				lis[index].getElementsByTagName('a')[0].appendChild(this);
				self.tsScrollResizeHd(this);
				
				if(self.currentHover == ael){
					if(self.muststop || self._autoplayer == 0){return;}
					self.run();
					return;
				}else{
					if(self.clicked == times){
						self.slideNext();
					}
				}
			},once:true});

		}else{
			ael.setAttribute('loaded',"2");
			lis[index].getElementsByTagName('a')[0].appendChild(newImage);
			ael.removeAttribute('mi');
			
			this.tsScrollResizeHd(newImage);
		}
	}

	var newloade = ael.getAttribute('loaded') ;
	if (e.type == "click" || e.type == "mouseover" || newloade== "2"){
	/*
		lis[pre_index].style.zIndex =0;
		lis[pre_index].style.position ="";
		Min.css.setOpacity(lis[pre_index],0);
		lis[index].style.zIndex = 2;
		lis[index].style.position ="relative";
		Min.css.setOpacity(lis[index],1);
		*/
		lis[pre_index].style.visibility="";
		lis[index].style.visibility="visible";
		this.currentHover.className='';
		ael.className="tsSelectImg";
		this.currentHover = ael;
		
		 if(this.process == true){
		 clearInterval(this.process_run);
		 var span =ael.getElementsByTagName('span')[0];
			 span.style.width = '0%';
		this.process_run = setInterval( Min.obj.methodReference(this,'progress'), Math.floor((this.autoplayer-1000)/25));
		}
		if(this.inited == false){
			this.init();
		}
	}
	if( newloade =="2"){
		return true ;
	}else{ 
		return false;
	}
  
};
scrollSlide.prototype.progress = function(){
	var wt = parseInt(this.currentHover.getElementsByTagName('span')[0].style.width);
	if(wt<100){
		this.currentHover.getElementsByTagName('span')[0].style.width = (wt+4)+'%';
	}else{
		clearInterval(this.process_run);
	}	
}
scrollSlide.prototype.init = function(){

	if(this.inited == true)	return;
	
	this.inited = true;
	var sc = _$(this.sc),
		mc = _$(this.mc);
	
	Min.event.bind(Min.dom.pre(sc),'click',Min.obj.methodReference(this, 'scrollLeft'));
	Min.event.bind(Min.dom.next(sc),'click',Min.obj.methodReference(this,'scrollRight'));
	Min.event.bind(Min.dom.pre(mc),'click',Min.obj.methodReference(this,'slidePre'));
	Min.event.bind(Min.dom.next(mc),'click',Min.obj.methodReference(this,'slideNext'));
	Min.event.bind(sc,'mouseover',{handler:Min.obj.methodReference(this, "navHover"),selector:'li'});
	if(this.autoplayer>0){
		Min.event.bind(mc,'mouseover',Min.obj.methodReference( this , 'mcmouseover'));
		Min.event.bind(mc,'mouseout',Min.obj.methodReference( this , 'navOut'));
		Min.event.bind(sc, 'mouseout', {handler: Min.obj.methodReference(this, "navOut")});
	}
	if(Min.dom.pre(sc)){
		this.initsc();
	}
};
scrollSlide.prototype.initsc = function(){

	var lis = _$(this.sc).getElementsByTagName('li'),
		start = this.currentRow * this.showsize,
		len = lis.length,j,m,src;
	for(var i = 0 ; i<this.showsize ; i++){		
		m = document.createElement("IMG");
		src = lis[start].getAttribute('si')|| false;
		if(src == false) continue;
		m.src = src; 
		if(!Min.obj.imgLoad([m])){
			m.setAttribute('data-index',start);
			Min.event.bind( m,'load',{ handler:function(){
				var index = this.getAttribute("data-index");
					lis[index].appendChild(this);
					lis[index].removeAttribute('si');
				this.removeAttribute("data-index");
			},once:true});
		} else{
			lis[start].appendChild(m);
			lis[start].removeAttribute('si');
		}
		start++;
		if(start == len) break;
	}
}
scrollSlide.prototype.stop = function(e){
	if(this.autoplayer>0){
	   clearInterval(this._autoplay);
	}		
};
scrollSlide.prototype.run = function(e){
	if(this.autoplayer>0){
		clearInterval(this._autoplay);
	    this._autoplay = setInterval(Min.obj.methodReference(this,'slideNext'),this.autoplayer);
	}		
};
scrollSlide.prototype.navHover = function(e){
	this.stop();
	this.clicked++;
	this.muststop = true;
	this.replaceSlide(e);	
};
scrollSlide.prototype.navOut = function(e){
	this.muststop = false;
	if(this.currentHover.getAttribute('loaded') == "2"){
		this.run();
	}
};
scrollSlide.prototype.tsScrollResizeHd = function(Timage){
	Timage.removeAttribute('height');
	Timage.removeAttribute('width');
	var maxWidth	= this.resize.w;
	var maxHeight	= this.resize.h;
	var Ratio = 1;
	var w = Timage.width;
	var h = Timage.height;
	var wRatio = maxWidth / w;
	var hRatio = maxHeight / h;
	if (maxWidth == 0 && maxHeight == 0) {
		Ratio = 1;
	} else if (maxWidth == 0) {
		if (hRatio < 1) Ratio = hRatio;
	} else if (maxHeight == 0) {
		if (wRatio < 1) Ratio = wRatio;
	} else if (wRatio < 1 || hRatio < 1) {
		Ratio = (wRatio <= hRatio ? wRatio: hRatio);
	}
	if (Ratio < 1) {
		w = w * Ratio;
		h = h * Ratio;
	}

	if(h%2!=0) h=h-1;
	var  tsImgsBox = Timage.parentNode; 
	Timage.height = h ;Timage.width = w;
	tsImgsBox.style.width = Timage.offsetWidth+'px';
	if(Timage.height<maxHeight){
		var TopBottom=(maxHeight-Timage.height)/2;
		 tsImgsBox.style.paddingTop=Math.floor(TopBottom)+"px";
		 tsImgsBox.style.paddingBottom=Math.ceil(TopBottom)+"px";
	} else {
	 	 tsImgsBox.style.paddingTop="0px";
		 tsImgsBox.style.paddingBottom="0px";
	}
	 
};
