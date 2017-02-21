Min.event = {
 
	bind : function( elem, type, options ){
		elem = _$(elem);
		if(!elem) return;
		var events = Min.cache.data( elem, 'e' + type ) || Min.cache.data( elem, 'e' + type, [] );
		
		// 将事件函数添加到缓存中; 先进后出 
		options.handler = options.handler || options;
		if( events.indexOf(options) == -1 ){
			events.push( options );
		 }else{
			alert(" twice avoid!!!!!");
		 }
		// 同一事件类型只注册一次事件，防止重复注册
		if( events.length === 1 ){
			var eventHandler = this.eventHandler( elem );
			Min.cache.data( elem, type + 'Handler', eventHandler );

			if( elem.addEventListener ){
				elem.addEventListener( type, eventHandler, false );
			}
			else if( elem.attachEvent ){
				elem.attachEvent( 'on' + type, eventHandler );
			}
			 
		}
	},
	childBind : function(elem, type, options){
		elem = _$(elem);
		if(!elem) return;
		var tags = elem.getElementsByTagName(options.tag);
		
		for(var i=0 , tag; tag =tags[i++]; ){
			this.bind(tag,type,options);		
		}
	},
	unbind : function( elem, type, option ){
		elem = _$(elem);
		if( !elem ) return;
		
		var options = Min.cache.data( elem , 'e' + type );
		if( !options ) return;
		
		// 如果没有传入要删除的事件处理函数则删除该事件类型的缓存
		if( !option ){
			options = undefined;		
		}
		// 如果有具体的事件处理函数则只删除一个
		else{
			for( var i = options.length , fn ; fn = options[--i];){
			
				if( fn === option || fn.handler === option ){
					options.splice( i, 1 );
				}				
			}
		}		
		// 删除事件和缓存
		if( !options || !options.length ){
			var eventHandler = Min.cache.data( elem, type + 'Handler' );			
			if( elem.addEventListener ){
				elem.removeEventListener( type, eventHandler, false );
			}
			else if( elem.attachEvent ){
				elem.detachEvent( 'on' + type, eventHandler );
			}		
			Min.cache.removeData( elem, type + 'Handler' );
			Min.cache.removeData( elem, 'e' + type );
		}
	},
		
	// 依次执行事件绑定的函数
	eventHandler : function( elem ){
		return function( event ){

			event = Min.event.fixEvent( event || window.event );
			
			if( !event.currentTarget ){
                event.currentTarget =  elem;
            }	
			
			event.delegateTarget = elem;
			
			var type = event.type,
				orginalTarget = event.target,
				options = Min.cache.data( elem, 'e' + type );
			// option.p  只在绑定元素触发。  不触发冒泡事件
			for(var i=options.length, option; option = options[--i];){

				if( option.selector) {
				
					var target = orginalTarget;
					
					for( ; target !== elem; target = target.parentNode || elem ){
						
						if( Min.event.delegateFilter(target, option.selector) ){
							if( option.client != undefined && ( Min.dom.contains(target,event.relatedTarget) || target==event.relatedTarget ))
							break;
							
							event.delegateTarget = target;
							if ( option.once == true ){
								Min.event.unbind( elem, type,option);
							} 
					
							if( option.handler.call(target, event) === false ){
								event.preventDefault();
								event.stopPropagation();	
							}	
							if( option.p == true) break;
							
						}
						else if( option.p == true ){
							break;
						}						
					}      
				}else{
					
					if( option.client != undefined  && ( Min.dom.contains(elem,event.relatedTarget) || elem == event.relatedTarget ))
							continue;
				
					if( orginalTarget != elem && option.p == true ) continue;
					if ( option.once == true ){
						Min.event.unbind( elem, type,option);
					} 
					
					if( option.handler.call(elem, event) === false ){
						event.preventDefault();
                        event.stopPropagation();	
					}	 
				}
				
			}

		 
		}
	},
	
	delegateFilter : function( elem, selector ){
        var tagName,  name, index,
			className = elem.className,
			s = selector.split(',');
		for(var i=0,sel; sel=s[i++];){
			
			if( ~sel.indexOf('.') ){
			// class
				
				index = sel.indexOf( '.' );
				name = ' ' + sel.substring( index + 1 ) + ' ';    
				tagName = sel.substring( 0, index ).toUpperCase();
				if( (!tagName || elem.tagName === tagName) && (className && !!~(' ' + className + ' ').indexOf(name))) return true;
			}else if( ~sel.indexOf('#') ){
			// id
				index = sel.indexOf( '#' );
				name = sel.substring( index + 1 );    
				tagName = sel.substring( 0, index ).toUpperCase();
				if((!tagName || elem.tagName === tagName) && (elem.id === name))return true;  
			// tag				
			}else if( elem.tagName.toLowerCase() === sel) return true;
		}
		return false;
    },
	
	// 修复IE浏览器支持常见的标准事件的API
	fixEvent : function( e ){
		// 支持DOM 2级标准事件的浏览器无需做修复
		if ( e.target ) return e; 
		
		var event = {}, name;
		
		event.target = e.srcElement || document;
		
		if( event.target.nodeType === 3 ){
            event.target = event.target.parentNode;
        }
		
		event.preventDefault = function(){
			e.returnValue = false;
		};		
		event.stopPropagation = function(){
			e.cancelBubble = true;
		};
		if( !e.relatedTarget && e.fromElement ){
            event.relatedTarget = e.fromElement === event.target ? e.toElement : e.fromElement;
        }
		// IE6/7/8在原生的window.event中直接写入自定义属性
		// 会导致内存泄漏，所以采用复制的方式
		for( name in e ){
			event[name] = e[name];
		}				
		return event;
	},
	
	stopPropagation : function (event) {
        var e = event || window.event;
         if(e.stopPropagation){
			e.preventDefault();
			e.stopPropagation()
		 }else{
			e.cancelBubble = true;
			e.returnValue = false;
		}
    },
	
	getEventCoords : function(e) {
		e = e || window.event;
		var x = 0;
		var y = 0;
		if ( Min.UA.belowIE8 ) {
			y = e.clientY + Min.dom.getScroll('top');
			x = e.clientX + Min.dom.getScroll('left');
		} else {
			y = e.clientY + window.pageYOffset;
			x = e.clientX + window.pageXOffset;
		}
		return {
			'x': x,
			'y': y
		}
	},
	
	target : function(e){
	
		var target = e.target || e.srcElement || document;
		
		 return target.nodeType === 3 ? target.parentNode :target;

	},
	fireEvent : function( elem, type ){
        if( document.createEvent ){
            var event = document.createEvent( 'HTMLEvents' );
            event.initEvent( type, true, true );
            elem.dispatchEvent( event );
        }
        else{
            elem.fireEvent( 'on' + type );
        }    
    }

}
