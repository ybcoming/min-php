function slideMenu(sm,mt,s){
	this.sm =sm;
	this.t = mt;
	this.sp =s;
	this.load();
};

slideMenu.prototype.load = function(e){
	var m =_$(this.sm),
		imgs = m.getElementsByTagName('img');
		m.getElementsByTagName('li')[1].className = "on";
	[1,2,5].forEach(function(a){
		imgs[a].src = imgs[a].getAttribute('data-rel');
		imgs[a].removeAttribute('data-rel');
	});
	
	Min.obj.imgLoad([imgs[1],imgs[2],imgs[5]],this,'init',undefined,true);	
}
slideMenu.prototype.init = function(){

	var m =_$(this.sm) , len;
	var lis = m.getElementsByTagName('li'), l = lis.length;
	this.tw = 0;
	m.getElementsByTagName("ul")[0].className="sm";
	
	for(var i=0,j;j=lis[i++];){
		len = j.clientWidth;
		this.tw += len;
		j.style.width = len + 'px';
	}
	
	if(len*l>this.tw){
		this.st=len;
		this.ot=(this.tw-len)/(l-1);
	}else{
		this.ot = len;
		this.st = this.tw-len*(l-1);
	} 
	
	
	var imgs = m.getElementsByTagName('img');

	[0,3,4].forEach(function(a){
		imgs[a].src = imgs[a].getAttribute('data-rel');
		imgs[a].removeAttribute('data-rel');
	});
	
	Min.event.bind( m ,'mouseover',{ handler:Min.obj.methodReference(this,'run'),selector:'li'});
}
slideMenu.prototype.run = function(e){
	clearInterval(this.timer);
	this.timer = setInterval(Min.obj.methodReference(this,'slide',[{delegateTarget:e.delegateTarget}]),this.t);
};
slideMenu.prototype.slide = function(e){

	var s = e.delegateTarget,
		cw = parseInt(s.style.width);

	if(cw < this.st){	
		var owt = 0, sa = _$(this.sm).getElementsByTagName('li');
		for(var i=0,o;o=sa[i++];){
			if( o != s ){
				o.className="";
				var oi=0 , ow=parseInt(o.style.width);
				if(ow>this.ot){
					oi=Math.floor((ow-this.ot)/this.sp); 
					oi=(oi>0)?oi:1; 
					o.style.width=parseInt(ow-oi)+'px';	
				}
				owt = owt+ow-oi;
			}
		}	
		s.className="on";
		s.style.width=parseInt(this.tw-owt)+'px';
 
	}else{
		clearInterval(this.timer)
	}
};
