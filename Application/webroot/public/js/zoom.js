function MagicZoom(settings) {

	if( _$(settings['sc']) == null) return;
	this.lcsize = {w:390,h:390};
    this.positionX = 0;
    this.positionY = 0;
	this.index = -1;
	this.z={};
	this.baseuri = '';
    this.inited = false;
	this.showing = false;
	this.scroll = new scrollSlide(settings);
	MagicZoom.helper.zooms.push(this);

	this.initPopup();
    Min.event.bind(this.scroll.mc, "mousemove",Min.obj.methodReference(this, "mousemove"));
};

MagicZoom.prototype.mousemove = function(e) {

	if( this.scroll.inited == false ) return;
	if( this.scroll.currentHover.getAttribute('loaded') != "2" ) return;
	
	if(this.inited == false){
		this.init();
	}
    this.z = e ? Min.event.getEventCoords(e) : this.z;
	 
	var	m = this.container.getElementsByTagName('img')[0],
		r = Min.dom.getBounds(m),
		popupSizeX = parseInt(this.pup.style.width),
		popupSizeY = parseInt(this.pup.style.height);
		
    this.positionX = this.z.x - r.left - m.clientLeft;
    this.positionY = this.z.y - r.top - m.clientTop;

    if ((this.positionX + popupSizeX / 2) >=  m.width) {
        this.positionX =  m.width -  popupSizeX / 2
    }
    if ((this.positionY +  popupSizeY / 2) >=  m.height) {
        this.positionY =  m.height -  popupSizeY / 2
    }
    if ((this.positionX -  popupSizeX / 2) <= 0) {
        this.positionX =  popupSizeX / 2
    }
    if ((this.positionY -  popupSizeY / 2) <= 0) {
        this.positionY = popupSizeY / 2
    }

	if (this.z.x > (r.left + m.width + m.clientLeft ) || this.z.x < ( r.left + m.clientLeft) || this.z.y > (r.top + m.height + m.clientTop ) || this.z.y < (r.top + m.clientTop)  ) {
        this.hiderect();
    }else{
		this.showrect();
    }
	
};
MagicZoom.prototype.showrect = function() {

	var mimage = this.container.getElementsByTagName('img')[0],
		lcont =  this.container.getElementsByTagName('div')[0],
		limage;
		
	var tag = mimage, smallY=0, smallX=0;
	     while (tag != _$(this.scroll.mc) ) {
            smallY += tag.offsetTop;
            smallX += tag.offsetLeft;
            tag = tag.offsetParent
        }
		
	var left = this.positionX  - parseInt(this.pup.style.width) / 2,
		top = this.positionY   - parseInt(this.pup.style.height) / 2;
    this.pup.style.left = left + smallX + mimage.clientLeft + 'px';
    this.pup.style.top =  top + smallY + mimage.clientTop + 'px';
	if(this.showing == false){
		this.pup.style.visibility = "visible";
		lcont.style.display = 'block';
		lcont.style.visibility = 'visible';
		lcont.style.left = '';
		this.showing = true;
		Min.event.bind(this.scroll.mc, "mouseout", {handler:Min.obj.methodReference(this,'hiderect'),once:true} );
	}
	if( limage = this.container.getElementsByTagName('img')[1]){
		limage.style.display = 'block';
		limage.style.visibility = 'visible';
		var perX = left * (limage.width/ mimage.width),
			perY = top * (limage.height/mimage.height);
			limage.style.left = ( - perX) + 'px';
			limage.style.top = ( - perY) + 'px';
	}

};
MagicZoom.prototype.hiderect = function(e) {
	var mimage = this.container.getElementsByTagName('img')[0],
		lcont  = this.container.getElementsByTagName('div')[0];
	lcont.style.left = '-10000px';
	lcont.style.visibility = 'hidden';
	this.pup.style.visibility = "hidden"
	this.showing = false;
	this.inited = false;
};
MagicZoom.prototype.recalculatePopupDimensions = function() {

	var mi = this.container.getElementsByTagName('img')[0],
		li = this.container.getElementsByTagName('img')[1],
		popupSizeX = this.lcsize.w * mi.width / li.width,
		popupSizeY = this.lcsize.h * mi.height / li.height; 

    this.pup.style.width =  Math.min( popupSizeX , mi.width) + 'px';
    this.pup.style.height = Math.min( popupSizeY , mi.height) + 'px';
	
};
MagicZoom.prototype.initPopup = function() {

    this.pup = document.createElement("DIV");
    this.pup.className = 'MagicZoomPup';
	var mcont = _$(this.scroll.mc);
    mcont.appendChild(this.pup);
    mcont.unselectable = "on";
    mcont.style.MozUserSelect = "none";
    mcont.onselectstart = MagicZoom.helper.ia;
    mcont.oncontextmenu = MagicZoom.helper.ia;
};

MagicZoom.prototype.init = function() {

	if(this.inited == true) return;
	this.inited = true;
	var index = this.scroll.currentHover.getAttribute('data-index');
	if( this.index == index ) return;
	this.index = index;
	this.container =  _$(this.scroll.mc).getElementsByTagName('li')[index];
	var lcloaded = this.container.getAttribute('lcloaded')||false;

	if(!lcloaded){
		var lcont  = document.createElement("DIV");
			lcont.className = "MagicZoomBigImageCont";

			this.container.appendChild(lcont);
			var inner = (Min.UA.isIE6?'<iframe style="left:0px;top:0px;position:absolute;filter:alpha(opacity=0);width:100%;height:100%;z-index:1;" frameborder="0"></iframe>':'')+'<div style="overflow: hidden;"></div>';
			lcont.innerHTML= inner;
			this.container.setAttribute('lcloaded','1');
		
		var newImage = document.createElement("IMG");
			newImage.src = this.scroll.currentHover.getAttribute("bi");
			newImage.style.position ="relative";
		if(!Min.obj.imgLoad([newImage])){
				var self = this;
			Min.event.bind( newImage,'load',{ handler:function(){
				lcont.getElementsByTagName('div')[0].appendChild(this);
				lcont.parentNode.setAttribute('lcloaded',"2");
				self.scroll.currentHover.removeAttribute('bi');
				if(self.scroll.currentHover.getAttribute('data-index') == index){
					 
					self.recalculatePopupDimensions();
					self.mousemove();
				}
			} });
		}else{
			lcont.getElementsByTagName('div')[0].appendChild(newImage);
			this.container.setAttribute('lcloaded',"2");
			this.scroll.currentHover.removeAttribute('bi');
		}
	}
	var newload = this.container.getAttribute('lcloaded');
	if( newload == "2"){
		this.recalculatePopupDimensions();
	}else{
		var mimage = this.container.getElementsByTagName('img')[0];
			this.pup.style.width = mimage.width/2 + "px";
			this.pup.style.height = mimage.height/2 + "px";
	}
	 
};

MagicZoom.helper = {
	zooms : [],
	ia : function(){
		return false;
	}
}

