var error_bordercolor = '#e4393c';
var code_url = 'http://www.' + site_domain + '/captcha/get.html?type=login_1&v=';
function show_error(msg){
	_$('login-msg').style.display='block';
	_$('login-name').style.cssText = 'margin-top:10px;';
	_$('login-msg').getElementsByTagName('span')[0].innerHTML =msg;
}

function check_empty(){

	var msg = '请输入';
	var name = _$('loginname');
	var pwd = _$('loginpwd');
	var has_error = false;
	var code = _$('logincode') ? _$('logincode').value : '';

	if(name.value ==''){
		msg += '帐户名';
		name.style.borderColor = error_bordercolor;
		Min.css.addClass('error',Min.dom.next(name));
		has_error = true;
	}
	
	if(pwd.value ==''){
		msg += '密码';
		pwd.style.borderColor = error_bordercolor;
		Min.css.addClass('error',Min.dom.next(pwd));
		has_error = true;
	}
	
	if( _$('logincode') && code =='' ){
		if(has_error){
			msg += '和';
		}
		msg += '验证码';
		_$('logincode').style.borderColor = error_bordercolor;
		_$('login-code').style.display='block';
		has_error = true;
	}
	if( has_error ) {
		show_error(msg)
	}
	return has_error;

}
Min.event.bind('loginname','focus',function(){
	this.removeAttribute('style');
	Min.css.removeClass('error',Min.dom.next(this));

});
Min.event.bind('loginpwd','focus',function(){
	this.removeAttribute('style');
	Min.css.removeClass('error',Min.dom.next(this));

});
Min.event.bind('loginsubmit','click', function(){	  
	
	if( check_empty() ) {
		return;
	}
	var sindex	= this.getAttribute('sindex');
	
	if( sindex == 0 ){
		
		this.setAttribute('sindex',1);
		this.style.cssText =" background:silver;";
		var name = _$('loginname'),
			pwd = _$('loginpwd'),
			code = _$('logincode') ? _$('logincode').value : '';
		minAjax({
			url:'http://www.' + site_domain + '/login.html', 
			type:'POST', 
			data:{
				name:name.value,
				pwd:pwd.value,
				captcha:code,
				csrf_token : _$('csrf_token').value
			},
			success: function(data){
				if(data.statusCode == 0 ){
					/* 
					var ReturnUrl = Min.util.getQueryString('ReturnUrl');
					var location = 'http://www.' + site_domain;
					if(ReturnUrl && '@^http[s]?://[a-z][a-z0-9]*\.qi\.com(?:/[a-zA-Z0-9]+)+\.html@'.test(ReturnUrl)){
						location = ReturnUrl; 
					} 
					*/
					var location = data.jumpurl || 'http://www.' + site_domain;
					window.location.href = location;
					
				} else {
					if (data.statusCode != 30207) {
						var a = _$('login-code');
						if(a) {
							a.getElementsByTagName('img')[0].src=code_url+new Date().getTime();
						}
						if( data.statusCode == 30202 || data.statusCode ==  30103 || data.statusCode ==  30102){
								
							if (a) {
								var ai = a.getElementsByTagName('i')[0], logincode = _$('logincode');
								ai.removeAttribute('style');
								ai.innerHTML='';
								logincode.value ='';
								//logincode.style.borderColor = error_bordercolor;
							
							} else {
								var div = document.createElement('div');
								div.id = "login-code";
								div.className = 'login-code';
								div.innerHTML = '<input id="logincode" type="text" class="logincode" name="logincode" tabindex="1" autocomplete="off"  placeholder="验证码">  <i class="icon-reg iconfont icon-white">&#xe619;</i> <img class="login-captcha"  src="'+code_url+new Date().getTime()+'"><span>换一张</span>';
								
								div.style.display='block';
								_$('login-form').insertBefore(div, _$('login-li'));
								
								login_checkcode();

							}	
							
						} 
						/*
						else if( data.code == 30201 || data.code == 30202 || data.code == 30208 ){
							var pwd =  _$('loginpwd');
							pwd.value ='';
							pwd.style.borderColor = error_bordercolor;
							Min.css.addClass('error',Min.dom.next(pwd));
							
						} 
						*/
						else if (data.statusCode == 500){
							data.message='系统忙，请重试 ';
						}
					}
					show_error(data.message);
					_$('loginsubmit').setAttribute('sindex',0);
					_$('loginsubmit').removeAttribute('style');
					
				}
			}
		});
		
		 
	}
});	

//          图片验证码 
var current_code = '';
function code_tag(show){
	var  code = _$('logincode'); itag = Min.dom.next(code);
	if(show == 1){
		itag.innerHTML="&#xe634;"
		//itag.style.display="inline"; 
		itag.style.color='green';
		code.removeAttribute('style');
	}else if(show == 2){
		itag.innerHTML="&#xe632;"
		//itag.style.display="inline"; 
		itag.style.color='red';
		code.style.borderColor = error_bordercolor;
	}else if(show == -1){
		itag.innerHTML=""
		itag.removeAttribute('style'); 
		code.removeAttribute('style');
	}
}

function code_check(){
	var code = _$('logincode').value;
	if(code == current_code) return;
	current_code = code;
	JSONP.get( 'http://www.' + site_domain + '/captcha/check.html', {captcha:code,type:'login'}, function(data){
			if(_$('logincode').value != code) return;
			 code_tag((data.statusCode==0?1:2))
		 }
	); 
}

function login_checkcode(){
	Min.event.bind('logincode','blur', function(){
		var code = this.value;
		if(code && code.length!=4) code_tag(0);
	});
	Min.event.bind('logincode','keyup',function(event){		 
		var code = this.value;
		if(code.length==4){
			code_check(); 
		}else if(code.length > 4){
			code_tag(0);
			current_code = code;
		}else{
			code_tag(-1);
			current_code = code;
		}
		return;
	});
}

function login_init(){

if( _$('autoLogin')&& _$('autoLogin').checked){
	_$('msg-warn').style.display="block";
}

Min.event.bind('autoLogin','click', function(){
	if(this.checked){
	_$('msg-warn').style.display="block";
	}else{
	_$('msg-warn').style.display="none";
	
	}
});
Min.event.bind('login-code','click',{handler:function(e){

	e.currentTarget.getElementsByTagName('IMG')[0].src=code_url+new Date().getTime();
	var ei = e.currentTarget.getElementsByTagName('i')[0];
	ei.removeAttribute('style');
	ei.innerHTML='';
	_$('logincode').value='';
	_$('logincode').focus();
	
},selector:'span,img'});
							
Min.event.bind('logincode','focus',function(){
	this.removeAttribute('style');	 
});

Min.event.bind('icon-reg','click',function(){
	_$('logincode').focus(); 
});
}

login_init();
if(_$('login-code')){
login_checkcode();
}
