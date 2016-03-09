/** 
 * easyDialog v2.2
 * Url : http://stylechen.com/easydialog-v2.0.html
 * Author : chenmnkken@gmail.com
 * Date : 2012-04-22
 */


var	Dialog = function(){};

Dialog.prototype = {
	// 参数设置
	getOptions : function( arg ){
		var i,
			options = {},
			// 默认参数
			defaults = {
				container:   null,			// string / object   弹处层内容的id或内容模板
				overlay:     true,			// boolean  		 是否添加遮罩层
				drag:	     true,			// boolean  		 是否绑定拖拽事件
				fixed: 	     true,			// boolean  		 是否静止定位
				follow:      null,			// string / object   是否跟随自定义元素来定位
				followX:     0,				// number   		 相对于自定义元素的X坐标的偏移
				followY:     0,				// number  		     相对于自定义元素的Y坐标的偏移
				autoClose:   0,				// number            自动关闭弹出层的时间
				lock:        false,			// boolean           是否允许ESC键来关闭弹出层
				callback:    null			// function          关闭弹出层后执行的回调函数
				/** 
				 *  container为object时的参数格式
				 *	container : {
				 *		header : '弹出层标题',
				 *		content : '弹出层内容',
				 *		yesFn : function(){},	    // 确定按钮的回调函数
				 *		noFn : function(){} / true,	// 取消按钮的回调函数
				 *		yesText : '确定',		    // 确定按钮的文本，默认为‘确定’
				 *		noText : '取消' 		    // 取消按钮的文本，默认为‘取消’		
				 *	}		
				 */
			};
		
		for( i in defaults ){
			options[i] = arg[i] !== undefined ? arg[i] : defaults[i];
		}
		Min.cache.data( 'ed_options', options );
		return options;
	},
		
	// 防止IE6模拟fixed时出现抖动
	setBodyBg : function(){
		if( body.currentStyle.backgroundAttachment !== 'fixed' ){
			body.style.backgroundImage = 'url(about:blank)';
			body.style.backgroundAttachment = 'fixed';
		}
	},
	
	// 防止IE6的select穿透
	appendIframe : function(elem){
		elem.innerHTML = '<iframe style="position:absolute;left:0;top:0;width:100%;height:100%;z-index:-1;border:0 none;filter:alpha(opacity=0)"></iframe>';
	},
	
	/**
	 * 设置元素跟随定位
	 * @param { Object } 跟随的DOM元素
	 * @param { String / Object } 被跟随的DOM元素
	 * @param { Number } 相对于被跟随元素的X轴的偏移
	 * @param { Number } 相对于被跟随元素的Y轴的偏移
	 */
	setFollow : function( elem, follow, x, y ){
		follow = typeof follow === 'string' ? document.getElementById( follow ) : follow;
		var style = elem.style,
			offsets = Min.dom.getBounds(follow);
		style.position = 'absolute';			
		style.left = offsets.left + x + 'px';
		style.top = offsets.top + y + 'px';
	},
	
	/**
	 * 设置元素固定(fixed) / 绝对(absolute)定位
	 * @param { Object } DOM元素
	 * @param { Boolean } true : fixed, fasle : absolute
	 */
	setPosition : function( elem, fixed ){
		var style = elem.style;
 
		style.position = Min.UA.isIE6 ? 'absolute' : fixed ? 'fixed' : 'absolute';
		var docWidth = document.documentElement.clientWidth,
			docHeight = document.documentElement.clientHeight,
			eWidth = elem.offsetWidth,
			eHeight = elem.offsetHeight,
			widthOverflow = eWidth > docWidth,
			heigthOverflow = eHeight > docHeight;
		
		elem.style.marginLeft = '-' + (widthOverflow ? docWidth/2 : eWidth/2) + 'px';
		elem.style.marginTop = '-' + (heigthOverflow ? docHeight/2 : eHeight/2) + 'px';
 
		
		if( fixed ){

			if( Min.UA.isIE6 ){
				style.setExpression( 'top','fuckIE6=document.documentElement.scrollTop+document.documentElement.clientHeight/2+"px"' );
			}
			else{
				style.top = '50%';
			}
			style.left = '50%';
		}
		else{
			if( Min.UA.isIE6 ){
				style.removeExpression( 'top' );
			}
			style.top = document.documentElement.clientHeight/2 + Min.dom.getScroll( 'top' ) + 'px';
			style.left = document.documentElement.clientWidth/2 + Min.dom.getScroll( 'left' ) + 'px';
		}
	},
	
	/**
	 * 创建遮罩层
	 * @return { Object } 遮罩层 
	 */
	createOverlay : function(){
		var overlay = document.createElement('div'),
			style = overlay.style;
			
		style.cssText = 'margin:0;padding:0;border:none;width:100%;height:100%;background:#333;opacity:0.6;filter:alpha(opacity=60);z-index:9999;position:fixed;top:0;left:0;';
		
		// IE6模拟fixed
		if(Min.UA.isIE6){
			body.style.height = '100%';
			style.position = 'absolute';
			style.setExpression('top','fuckIE6=document.documentElement.scrollTop+"px"');
		}
		
		overlay.id = 'overlay';
		return overlay;
	},

	/**
	 * 创建弹出层
	 * @return { Object } 弹出层 
	 */
	createDialogBox : function(){
		var dialogBox = document.createElement('div');		
		dialogBox.style.cssText = 'margin:0;padding:0;border:none;z-index:10000;';
		dialogBox.id = 'easyDialogBox';		
		return dialogBox;
	},
	
	/**
	 * 创建默认的弹出层内容模板
	 * @param { Object } 模板参数
	 * @return { Object } 弹出层内容模板
	 */
	createDialogWrap : function( tmpl ){
		// 弹出层标题
		var header = tmpl.header ? 
			'<h4 class="easyDialog_title" id="easyDialogTitle"><a href="javascript:void(0)" title="关闭窗口" class="close_btn" id="closeBtn">&times;</a>' + tmpl.header + '</h4>' :
			'',
			// 确定按钮
			yesBtn = typeof tmpl.yesFn === 'function' ? 
				'<button class="btn_highlight" id="easyDialogYesBtn">' + ( typeof tmpl.yesText === 'string' ? tmpl.yesText : '确定' ) + '</button>' :
				'',
			// 取消按钮	
			noBtn = typeof tmpl.noFn === 'function' || tmpl.noFn === true ? 
				'<button class="btn_normal" id="easyDialogNoBtn">' + ( typeof tmpl.noText === 'string' ? tmpl.noText : '取消' ) + '</button>' :
				'',			
			// footer
			footer = yesBtn === '' && noBtn === '' ? '' :
				'<div class="easyDialog_footer">' + noBtn + yesBtn + '</div>',
				
			content = tmpl.url === undefined ? tmpl.content : '<iframe width="100%" height="'+ tmpl.height + 'px" frameborder="0" style="border:none 0;" allowtransparency="true" id="easyDialogIframe" scrolling="no"  src="' + tmpl.url + '"></iframe>',
			
			cover = tmpl.url === undefined ? '' :'<div id="coverIframe" style="position: absolute; height: 100%; width: 100%; display: none; background-color:#fff; opacity: 0.5;">&nbsp;</div> ',
			
			dialogTmpl = [
			header,
			'<div class="easyDialog_content" style="position: relative;">',
				cover,
				'<div class="easyDialog_text" >' , content , '</div>',
				footer,
			'</div>'
			].join(''),

			dialogWrap = document.getElementById( 'easyDialogWrapper' ),
			rScript = /<[\/]*script[\s\S]*?>/ig;
			
		if( !dialogWrap ){
			dialogWrap = document.createElement( 'div' );
			dialogWrap.id = 'easyDialogWrapper';
			dialogWrap.className = 'easyDialog_wrapper';
		}
		dialogWrap.innerHTML = dialogTmpl.replace( rScript, '' );		
		return dialogWrap;
	}		
};

/**
 * 拖拽效果
 * @param { Object } 触发拖拽的DOM元素
 * @param { Object } 要进行拖拽的DOM元素
 */
Dialog.drag = function( target, moveElem ){
	// 清除文本选择
	var	clearSelect = 'getSelection' in win ? function(){
		win.getSelection().removeAllRanges();
		} : function(){
			try{
				document.selection.empty();
			}
			catch( e ){};
		},

		isDown = false,
		newElem = Min.UA.belowIE8 ? target : doc,
		fixed = moveElem.style.position === 'fixed',
		_fixed = Min.cache.data( 'ed_options' ).fixed;
	
	// mousedown
	var down = function( e ){
		isDown = true;
		var scrollTop = Min.dom.getScroll( 'top' ),
			scrollLeft = Min.dom.getScroll( 'left' ),
			edgeLeft = fixed ? 0 : scrollLeft,
			edgeTop = fixed ? 0 : scrollTop,
			offsets = Min.dom.getBounds(moveElem);
		
		Min.cache.data( 'ed_dragData', {
			x : e.clientX - offsets.left  + ( fixed ? scrollLeft : 0 ),	
			y : e.clientY - offsets.top + ( fixed ? scrollTop : 0 ),			
			// 设置上下左右4个临界点的位置
			// 固定定位的临界点 = 当前屏的宽、高(下、右要减去元素本身的宽度或高度)
			// 绝对定位的临界点 = 当前屏的宽、高 + 滚动条卷起部分(下、右要减去元素本身的宽度或高度)
			el : edgeLeft,	// 左临界点
			et : edgeTop,  // 上临界点
			er : edgeLeft + document.documentElement.clientWidth - moveElem.offsetWidth,  // 右临界点
			eb : edgeTop + document.documentElement.clientHeight -30//- moveElem.offsetHeight +30 // 下临界点
		});
		if (!Min.UA.isIE && _$('coverIframe') ) { //非ie浏览器下在拖拽时用一个层遮住iframe，以免光标移入iframe失去拖拽响应
			_$('coverIframe').style.display = "";
				}
		if( Min.UA.belowIE8 ){
			// IE6如果是模拟fixed在mousedown的时候先删除模拟，节省性能
			if( Min.UA.isIE6 && _fixed ){
				moveElem.style.removeExpression( 'top' );
			}
			target.setCapture();
		}
		
		Min.event.bind( newElem, 'mousemove', move );
		Min.event.bind( newElem, 'mouseup', up );
		
		if(Min.UA.belowIE8){
			Min.event.bind( target, 'losecapture', up );
		}
		
		e.stopPropagation();
		e.preventDefault();
		
	};
	
	Min.event.bind( target, 'mousedown', down );
	
	// mousemove
	var move = function( e ){
		if( !isDown ) return;
		clearSelect();
		var dragData = Min.cache.data( 'ed_dragData' ),
			left = e.clientX - dragData.x,
			top = e.clientY - dragData.y,
			style = moveElem.style;
		
		// 设置上下左右的临界点以防止元素溢出当前屏
		style.marginLeft = style.marginTop = '0px';
		style.left = left + 'px';
		style.top = top + 'px';
		e.stopPropagation();
	};
	
	// mouseup
	var up = function( e ){
		isDown = false;
		
		var dragData = Min.cache.data( 'ed_dragData' ),
			left = e.clientX - dragData.x,
			top = e.clientY - dragData.y,
			
			et = dragData.et,
			er = dragData.er,
			eb = dragData.eb,
			el = dragData.el,
		
			style = moveElem.style;
		
		// 设置上下左右的临界点以防止元素溢出当前屏
		style.marginLeft = style.marginTop = '0px';
		style.left = ( left <= el ? el : (left >= er ? er : left) ) + 'px';
		style.top = ( top <= et ? et : (top >= eb ? eb : top) ) + 'px';
		e.stopPropagation();
		
		
		
		if( Min.UA.belowIE8 ){
			Min.event.unbind( target, 'losecapture', arguments.callee );
		}
		Min.event.unbind( newElem, 'mousemove', move );
		Min.event.unbind( newElem, 'mouseup', arguments.callee );	

		if (!Min.UA.isIE && _$('coverIframe') ) { //非ie浏览器下在拖拽时用一个层遮住iframe，以免光标移入iframe失去拖拽响应
			_$('coverIframe').style.display = "none";
		}
		if( Min.UA.belowIE8 ){
			target.releaseCapture();
			// IE6如果是模拟fixed在mouseup的时候要重新设置模拟
			if( Min.UA.isIE6 && _fixed ){
				var top = parseInt( moveElem.style.top ) - Min.dom.getScroll( 'top' );
				moveElem.style.setExpression('top',"fuckIE6=document.documentElement.scrollTop+" + top + '+"px"');
			}
		}
		e.stopPropagation();
	};
};



var easyDialog = {

	timer:undefined,	
	escClose : function( e ){
		if( e.keyCode === 27 ){
			easyDialog.close();
		}
	},	
	clearTimer :function(){
		if( easyDialog.timer ){
			clearTimeout( easyDialog.timer );
			easyDialog.timer = undefined;
		}
	},
	open : function(){
		var $ = new Dialog(),
			options = $.getOptions( arguments[0] || {} ),	// 获取参数
			docWidth = document.documentElement.clientWidth,
			docHeight = document.documentElement.clientHeight,
			overlay,
			dialogBox,
			dialogWrap,
			boxChild;
			
		easyDialog.clearTimer();
		
		// ------------------------------------------------------
		// ---------------------插入遮罩层-----------------------
		// ------------------------------------------------------
		
		// 如果页面中已经缓存遮罩层，直接显示
		if( options.overlay ){
			overlay = document.getElementById( 'overlay' );			
			if( !overlay ){
				overlay = $.createOverlay();
				body.appendChild( overlay );
				if( Min.UA.isIE6 ){
					$.appendIframe( overlay );
				}
			}
			overlay.style.display = 'block';
		}
		
		if(Min.UA.isIE6){
			$.setBodyBg();
		}
		
		// ------------------------------------------------------
		// ---------------------插入弹出层-----------------------
		// ------------------------------------------------------
		
		// 如果页面中已经缓存弹出层，直接显示
		dialogBox = document.getElementById( 'easyDialogBox' );
		if( !dialogBox ){
			dialogBox = $.createDialogBox();
			body.appendChild( dialogBox );
		}
		
		if( options.follow ){
			var follow = function(){
				$.setFollow( dialogBox, options.follow, options.followX, options.followY );
			};
			
			follow();
			
			Min.event.bind( win, 'resize', follow );
			Min.cache.data( 'ed_follow', follow );
			if( overlay ){
				overlay.style.display = 'none';
			}
			options.fixed = false;
		}
		else{
			$.setPosition( dialogBox, options.fixed );
		}
		dialogBox.style.display = 'block';
				
		// ------------------------------------------------------
		// -------------------插入弹出层内容---------------------
		// ------------------------------------------------------
		
		// 判断弹出层内容是否已经缓存过
		dialogWrap = typeof options.container === 'string' ? 
			document.getElementById( options.container ) : 
			$.createDialogWrap( options.container );
		
		boxChild = dialogBox.getElementsByTagName('*')[0];
		
		if( !boxChild ){
			dialogBox.appendChild( dialogWrap );
		}
		else if( boxChild && dialogWrap !== boxChild ){
			boxChild.style.display = 'none';
			body.appendChild( boxChild );
			dialogBox.appendChild( dialogWrap );
		}
		
		dialogWrap.style.display = 'block';
		
		var eWidth = dialogWrap.offsetWidth,
			eHeight = dialogWrap.offsetHeight,
			widthOverflow = eWidth > docWidth,
			heigthOverflow = eHeight > docHeight;
		
		// 强制去掉自定义弹出层内容的margin	
		dialogWrap.style.marginTop = dialogWrap.style.marginRight = dialogWrap.style.marginBottom = dialogWrap.style.marginLeft = '0px';	
		
		// 居中定位
		
		if( !options.follow ){			
			dialogBox.style.marginLeft = '-' + (widthOverflow ? docWidth/2 : eWidth/2) + 'px';
			dialogBox.style.marginTop = '-' + (heigthOverflow ? docHeight/2 : eHeight/2) + 'px';			
		}
		else{
			dialogBox.style.marginLeft = dialogBox.style.marginTop = '0px';
		}
				
		// 防止select穿透固定宽度和高度
		if( Min.UA.isIE6 && !options.overlay ){
			dialogBox.style.width = eWidth + 'px';
			dialogBox.style.height = eHeight + 'px';
		}
		
		// ------------------------------------------------------
		// --------------------绑定相关事件----------------------
		// ------------------------------------------------------
		var closeBtn = document.getElementById( 'closeBtn' ),
			dialogTitle = document.getElementById( 'easyDialogTitle' ),
			dialogYesBtn = document.getElementById('easyDialogYesBtn'),
			dialogNoBtn = document.getElementById('easyDialogNoBtn');		

		// 绑定确定按钮的回调函数
		if( dialogYesBtn ){
			Min.event.bind( dialogYesBtn, 'click', function( event ){
				if( options.container.yesFn.call(easyDialog, event) !== false ){
					easyDialog.close();
				}
			});
		}
		
		// 绑定取消按钮的回调函数
		if( dialogNoBtn ){
			var noCallback = function( event ){
				if( options.container.noFn === true || options.container.noFn.call(easyDialog, event) !== false ){
					easyDialog.close();
				}
			};
			Min.event.bind( dialogNoBtn, 'click', noCallback );
			// 如果取消按钮有回调函数 关闭按钮也绑定同样的回调函数
			if( closeBtn ){
				Min.event.bind( closeBtn, 'click', noCallback );
			}
		}			
		// 关闭按钮绑定事件	
		else if( closeBtn ){
			Min.event.bind( closeBtn, 'click', easyDialog.close );
		}
		
		// ESC键关闭弹出层
		if( !options.lock ){
			Min.event.bind( doc, 'keyup', easyDialog.escClose );
		}
		// 自动关闭弹出层
		if( options.autoClose && typeof options.autoClose === 'number' ){
			timer = setTimeout( easyDialog.close, options.autoClose );
		}		
		// 绑定拖拽(如果弹出层内容的宽度或高度溢出将不绑定拖拽)
		//if( options.drag && dialogTitle && !widthOverflow && !heigthOverflow ){
		if( options.drag && dialogTitle ){
			dialogTitle.style.cursor = 'move';
			Dialog.drag( dialogTitle, dialogBox );
		}
		
		// 确保弹出层绝对定位时放大缩小窗口也可以垂直居中显示
		
		//if( !options.follow && !options.fixed ){
		if( !options.follow  ){
			var resize = function(){
				
				$.setPosition( dialogBox, options.fixed );
			};
			// 如果弹出层内容的宽度或高度溢出将不绑定resize事件
			//if( !widthOverflow && !heigthOverflow ){
				Min.event.bind( win, 'resize', resize );
			//}
			Min.cache.data( 'ed_resize', resize );
		}
		
		// 缓存相关元素以便关闭弹出层的时候进行操作
		Min.cache.data( 'ed_dialogElements', {
			overlay : overlay,
			dialogBox : dialogBox,
			closeBtn : closeBtn,
			dialogTitle : dialogTitle,
			dialogYesBtn : dialogYesBtn,
			dialogNoBtn : dialogNoBtn			
		});
	},
	
	close : function(){
		var options = Min.cache.data( 'ed_options' ),
			elements = Min.cache.data( 'ed_dialogElements' );
			 
		easyDialog.clearTimer();
		//	隐藏遮罩层
		if( options.overlay && elements.overlay ){
			elements.overlay.style.display = 'none';
		}
		// 隐藏弹出层
		elements.dialogBox.style.display = 'none';
		// IE6清除CSS表达式
		if( Min.UA.isIE6 ){
			elements.dialogBox.style.removeExpression( 'top' );
		}
		
		// ------------------------------------------------------
		// --------------------删除相关事件----------------------
		// ------------------------------------------------------
		if( elements.closeBtn ){
			Min.event.unbind( elements.closeBtn, 'click' );
		}

		if( elements.dialogTitle ){
			Min.event.unbind( elements.dialogTitle, 'mousedown' );
		}
		
		if( elements.dialogYesBtn ){
			Min.event.unbind( elements.dialogYesBtn, 'click' );
		}
		
		if( elements.dialogNoBtn ){
			Min.event.unbind( elements.dialogNoBtn, 'click' );
		}
		
		//if( !options.follow && !options.fixed ){
		if( !options.follow  ){
			Min.event.unbind( win, 'resize', Min.cache.data('ed_resize') );
			Min.cache.removeData( 'ed_resize' );
		}
		
		if( options.follow ){
			Min.event.unbind( win, 'resize', Min.cache.data('ed_follow') );
			Min.cache.removeData( 'ed_follow' );
		}
		
		if( !options.lock ){
			Min.event.unbind( doc, 'keyup', easyDialog.escClose );
		}
		// 执行callback
		if(typeof options.callback === 'function'){
			options.callback.call( easyDialog );
		}
		// 清除缓存
		Min.cache.removeData( 'ed_options' );
		Min.cache.removeData( 'ed_dialogElements' );
	}

};

 

