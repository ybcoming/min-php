
var pwd_tips = '建议使用字母、数字或符号两种及以上组合，6-20个字符';
	// 密码
function isPwd(str) {
	return true;
	return !/[\s'"\\]/.test(str);
	return /^[a-zA-Z0-9`~!@#$%^&*()_+-=\[\]{}|:;,./<>?]+$/.test(str);
}
function Pwd_OK(str){
	return str.length > 5 && str.length <21;
	return !/[\s'"\\]/.test(str) && str.length > 5 && str.length <21;
	return /^[a-zA-Z0-9`~!@#$%^&*()_+-=\[\]{}|:;,./<>?]{6,}$/.test(str);
}
function betweenLength(str, _min, _max) {
    return (str.length >= _min && str.length <= _max);
}
function strong_settimer(obj,width,tag){
	obj.className="tips-s";
	var itag = obj.getElementsByTagName('em')[0];
	if( !itag ){
		obj.innerHTML='密码强度：'+tag+'&nbsp;&nbsp;<i class="iconfont icon-pwd">&#xe661;</i><em style="left:92px;"></em>';
		itag = obj.getElementsByTagName('em')[0];
	}
	clearInterval(itag.timer);	

	itag.timer=setInterval(function(){
		var cw=parseInt(itag.style.left);
		if(cw-width>0){
			itag.style.left= parseInt(cw-1)+'px';
		}else if(cw-width<0){
			itag.style.left= parseInt(cw+1)+'px';
		}
		else{		
				clearInterval(itag.timer);	
				if(itag.parentNode) itag.parentNode.innerHTML='密码强度：'+tag+'&nbsp;&nbsp;<i class="iconfont icon-pwd" >&#xe661;</i><em style="left:'+cw+'px" ></em>';		 
			}
		  
		},15);

}

function pwdstrong(pwd){
	var strong = 0;
	if(/[a-z]/.test(pwd)){ strong++;}
	if( /[\d]/.test(pwd)){ strong++;} 
	if(/[A-Z]/.test(pwd)){ strong++;}
	if(/[`\~\!@#\$%\^&\*\(\)_\+\-\=\[\]{}|:;,\.\/\<\>\?'"\\]+/.test(pwd)){ strong ++;} 	
	
	if(pwd.length>6){ strong++;} 
	console.log(strong);
	return strong;
}
function setstrong(strong,obj){
	
	if(strong<3){	
		strong_settimer(obj,102,'弱');		 
	}else if(strong>3){
		strong_settimer(obj,154,'强');
	}else{ 
		strong_settimer(obj,126,'中');
	}
}
function setError(obj,msg,name){
	obj.innerHTML=msg;	
	obj.className=name;
}


_$('regpwd').onkeyup =function(e){

	var itag  = Min.dom.next(this);
	var error = Min.dom.next(itag);
	var flag = 0;
	if(this.value==''){	
		setError(error,pwd_tips,'tips');
		this.removeAttribute('style');
		return;
	}
	var pwd1 =_$('regpwd1');
	if( isPwd(this.value) == false || this.value.length < 6 ){	
		flag =1;
		pwd1.setAttribute('disabled','disabled');
		this.removeAttribute('style');
		var tmp= Min.dom.next(pwd1);
		tmp.removeAttribute('style');
		tmp.innerHTML = "&#xe63a;";
		tmp.style.background="none";
		setError(_$('regpwd1-error'),'','hide');
		pwd1.removeAttribute('style');
		pwd1.style.background="#e8e8e8"; 
		
		if(isPwd(this.value) == false){
			setError( error,'密码中含有非法字符，请输入字母、数字或标点（空格、引号、反斜线\除外）','errors');
			this.style.borderColor="red";
			// flag=2;
		}else if( this.value.length < 6 ){
				setError(error,pwd_tips,'tips');
		}
	}else{
		pwd1.removeAttribute('disabled');
		pwd1.removeAttribute('style');
		Min.dom.next(pwd1).innerHTML = "&#xe63a;";
		Min.dom.next(pwd1).style.background = "white";
		
	}
	if(flag !=1){
		this.removeAttribute('style');
		var strong = pwdstrong(this.value); 
		setstrong(strong,error);
	}
	
	
	if(pwd1.value != ''){
		var itag1  = Min.dom.next(pwd1);
		var error1 = Min.dom.next(itag1);
		if(flag==0){
			if(this.value == pwd1.value){
				setError( error,'','hide');
				itag.innerHTML="&#xe634;"
				itag.style.color="#7ABD54";
			
				setError(error1,'','hide');
				itag1.innerHTML="&#xe634;"
				itag1.style.color="#7ABD54";
				pwd1.removeAttribute('style');
			}else{
				setError(error1,'两次密码不相同，请重新输入','errors');
				itag1.innerHTML="&#xe63a;"
				itag1.style.color="silver";
				pwd1.style.borderColor="red";
			 
			}
		}
	}
	

}
_$('regpwd1').onkeyup =function(e){
	var pwd =_$('regpwd').value;
	var len= pwd.length;
	if( pwd ==''  ){
		this.value=''; 
		this.setAttribute('disabled','disabled'); 
		Min.dom.next(this).style.background = "none"; 
		_$('regpwd1-error').className='hide';
		_$('regpwd').focus();
		return true;
	}

	if((this.value.length<len && pwd.substring(0,this.value.length)!= this.value) || this.value.length>len || (this.value.length==len &&  this.value!= pwd )	){
		setError(_$('regpwd1-error'),'两次密码不相同，请重新输入','errors');
		return;
	}else if( this.value == pwd ) {
			setError(_$('regpwd1-error'),'','hide');
			itag = Min.dom.next(this);
			itag.innerHTML="&#xe634;"
			itag.style.color="#7ABD54";
	}else{
		Min.print.log(456);
		setError(_$('regpwd1-error'),'请再次输入密码','tips');
		itag = Min.dom.next(this);
			itag.innerHTML="&#xe63a;"
			itag.removeAttribute('style');
		return;
	}

}

_$('regpwd').onfocus=function(){
	var tips=_$('regpwd-error');
	 
	if(Pwd_OK(this.value) == true){
		 var strong = pwdstrong(this.value); 
			setstrong(strong,tips);
	}else if(  this.value=='' || isPwd(this.value) == true){
		setError( tips,pwd_tips,'tips');
		this.removeAttribute('style');
		var itag=Min.dom.next(this);
		itag.innerHTML="&#xe63a;"
		itag.removeAttribute('style');
	}
};

_$('regpwd').onblur=function(){

	if(this.value==''){
		setError(_$('regpwd-error'),'','hide');
		return;
	}
 
	if(isPwd(this.value)==false){
		this.style.borderColor="#f00";
	}else if(this.value.length < 6){
		setError(_$('regpwd-error'),'请输入6-20位密码','errors');
		this.style.borderColor="#f00";
		var itag=Min.dom.next(this);
		itag.innerHTML="&#xe63a;"
		itag.removeAttribute('style');
		return;
	}else{
		setError(_$('regpwd-error'),'','hide');
		var itag=Min.dom.next(this);
		itag.innerHTML="&#xe634;"
		itag.style.color="#7ABD54";
	}

}

	
	//确认密码
	_$('regpwd1').onfocus=function(){
	Min.print.log(123);
		var tips=_$('regpwd1-error');
		tips.innerHTML='请再次输入密码';
		tips.className="tips";
		this.removeAttribute('style');
		var itag=Min.dom.next(this);
		itag.innerHTML="&#xe63a;"
		itag.removeAttribute('style');	 
	};
	_$('regpwd1').onblur = function(){ 
		if(this.value==''){
			setError(_$('regpwd1-error'),'','hide');
			return;
		}
		var pwd=_$('regpwd').value;
		if( pwd != this.value ){
			setError(_$('regpwd1-error'),'两次输入密码不相同，请重新输入','errors');
			this.style.borderColor="red";
		}
	
	}
 
// 手机
var current_phone = '';

function phone_format(phone){
	var tips=_$('regphone-error');
	
	if(phone.length != 11 && /^1[\d]*$/.test(phone)){
		setError(tips,'请输入11位手机号码','errors');
		return false;
	}
	if(/^(13|15|18|14|17)[\d]{9}$/.test(phone)==false){
		setError(tips,'手机号码格式错误','errors');
		return false
	}
	return true;
}

function phone_check(phone){

	if(phone == current_phone) return;
		current_phone = phone;
	JSONP.get( 'http://www.' + site_domain + '/account/phone.html', {phone:phone}, function(data){
				
				if(_$('regphone').value != phone) return;
				
				if(data.statusCode == 2){
					itag = Min.dom.next(_$('regphone'));
					itag.innerHTML="&#xe634;"
					itag.style.color="#7ABD54";
					setError(_$('regphone-error'),'','hide'); 
				}else if(data.statusCode == 1){
					setError(_$('regphone-error'),'此号码已被注册','errors');
					_$('regphone').style.borderColor = error_bordercolor;
				}
			 }
		); 
}

_$('regphone').onfocus=function(){
	if(this.value == ''){
		setError(_$('regphone-error'),'请输入11位手机号码','tips');
		this.removeAttribute('style');
	}
};
_$('regphone').onblur=function(){
	
	var name = this.value;
	if (name=='') {
		setError(_$('regphone-error'),'','hide');
		return true;
	}
	if(phone_format(name)){
		itag = Min.dom.next(this);
		itag.innerHTML="&#xe634;"
		itag.style.color="#7ABD54";
		setError(_$('regphone-error'),'','hide'); 
		return;
	}else{ 
		this.style.borderColor="#f00";	 
	}
};

_$('regphone').onkeyup = function(){
	
	var itag = Min.dom.next(this);
	if( (this.value.length != 11 && /^1[\d]*$/.test(this.value)) || this.value == '' ){
		setError(_$('regphone-error'),'请输入11位手机号码','tips');
		this.removeAttribute('style');
		itag.innerHTML="&#xe619;"
		itag.removeAttribute("style");
		current_phone = this.value;
		return ;	 
	}
	 if(/^(13|15|18|14|17)[\d]{9}$/.test(this.value)==false){
		setError(_$('regphone-error'),'手机号码格式错误','errors');
		current_phone = this.value;
	}else{
		//phone_check(this.value);
	}
}	
Min.event.bind('regphone','paste',  function(e){
	
	var pastedText = undefined;
     if (window.clipboardData && window.clipboardData.getData) { // IE
            pastedText = window.clipboardData.getData('Text');
    } else {
            pastedText = e.clipboardData.getData('text/plain');//e.originalEvent.clipboardData.getData('Text');//e.clipboardData.getData('text/plain');
          }
	//Min.print.log(pastedText);
	
	var itag = Min.dom.next(this);
	if( (pastedText.length != 11 && /^1[\d]*$/.test(pastedText)) || pastedText == '' ){
		setError(_$('regphone-error'),'请输入11位手机号码','tips');
		this.removeAttribute('style');
		itag.innerHTML="&#xe619;"
		itag.removeAttribute("style");
		current_phone = pastedText;
		return ;	 
	}
	 if(/^(13|15|18|14|17)[\d]{9}$/.test(pastedText)==false){
		setError(_$('regphone-error'),'手机号码格式错误','errors');
		current_phone = pastedText;
	}else{
		//phone_check(pastedText);
	}
});	


//          图片验证码 

Min.event.bind('icon-reg','click',function(){
	_$('regcode').focus(); 
});

var current_code = '';
function code_tag(show){
	var  code = _$('regcode'); itag = Min.dom.next(code);
	code.setAttribute('status',show);
	if(show == 1){
		itag.innerHTML="&#xe634;"
	//	itag.style.display="inline"; 
		itag.style.color="#7ABD54";
		code.removeAttribute('style');		
	}else if(show == 2){
		if(code.value != ''){
		itag.innerHTML="&#xe632;"
	//	itag.style.display="inline"; 
		itag.style.color='red';
		}
		code.style.borderColor = error_bordercolor;
	}else if(show == -1){
		itag.innerHTML="&#xe60f;"
		itag.removeAttribute('style');
		code.removeAttribute('style');		
	}
}

function code_check(){
 
	var code = _$('regcode').value;
 
	if(code == current_code) return;
	current_code = code;
		setError(_$('regcode-error'),'','hide');
		 
	JSONP.get( 'http://www.' + site_domain + '/captcha/check.html', {captcha:code,type:'reg'}, function(data){
			if(_$('regcode').value != code) return;
			 if( data.statusCode == 0 ) { 
				code_tag(1);
			 } else {
				code_tag(2);
				setError(_$('regcode-error'),data.message,'errors');
			 }
		 }
	); 
}

Min.event.bind('regcode','blur', function(){
	var code = this.value;
	if(code && code.length!=4){
		 code_tag(2);
	}
});
	
Min.event.bind('regcode','keyup',function(event){		 
		var code = this.value;

		if(code.length == 4){
			code_check(); 
		}else if(code.length >4){
			current_code = code;
			code_tag(2);
		}else{
			current_code = code;
			setError(_$('regcode-error'),'','hide');
			code_tag(-1);
		}
		return;
});
	
Min.event.bind('reg-code','click',{handler:function(e){
	e.currentTarget.getElementsByTagName('IMG')[0].src='http://www.' + site_domain + '/captcha/get.html?type=reg_1_2&v='+new Date().getTime();
	e.currentTarget.getElementsByTagName('i')[0].removeAttribute('style');
	e.currentTarget.getElementsByTagName('i')[0].innerHTML='';
	_$('regcode').value='';
	_$('regcode').focus();
},selector:'em,img'});

Min.event.bind('regcode','focus',function(){
	if(this.value ==''){
		this.removeAttribute('style');	
		setError(_$('regcode-error'),'','hide');
	}
});


// 手机验证码

var delayTime = 120;

Min.event.bind('getcode','click',function(e){
	
	var phone = _$('regphone').value, code = _$('regcode').value;
	
	if(code == '') { 
		code_tag(2);
		setError(_$('regcode-error'),'请输入验证码','errors');
	}else if(code.length != 4) { 
		code_tag(2);
		setError(_$('regcode-error'),'验证码错误，请重新输入','errors');
	}
	
	if(phone == '') {
		_$('regphone').style.borderColor = error_bordercolor;
		setError(_$('regphone-error'),'请输入手机号码','errors');
		return;
	}
	
	if( false == phone_format(phone) ||  4 != code.length )  return false;
	
	
	var sindex	= this.getAttribute('sindex');
	
	if(sindex == 0){
	
	this.setAttribute("sindex", 1);

	minAjax({
		url:'http://www.' + site_domain + '/regist/send.html', 
		type:'POST', 
		data:{
			phone:phone,
			captcha:code,
			csrf_token:this.getAttribute('token')
		},
		success: function(data){
			if(data.statusCode == 0) {
				setTimeout(countDown, 1000);
			}else{
			
				_$('getcode').setAttribute("sindex", 0);
				switch(data.statusCode){
					case 30207:
					case 30112:
					case 500:
						_$('error_message').innerHTML= data.message || '系统忙，请重试';
						_$('reg-msg').style.visibility="visible";
						break;
					case 30120: // 手机号码错误
					case 30205:
						_$('regphone').style.borderColor = error_bordercolor; 
						setError(_$('regphone-error'),data.message,'errors');
						break;
					case 30102: // 验证码错误
						code_tag(2);
						setError(_$('regcode-error'),data.message,'errors');
					break;
				}
			}
		},
		fail:function(){
			_$('getcode').setAttribute("sindex", 0);
			_$('error_message').innerHTML= '您的网络出现问题';
			_$('reg-msg').style.visibility="visible";
		
		}
	});
	}
});
function countDown() {
     delayTime--;
	 var code = _$('getcode');
    
    code.innerHTML = '发送成功(' +delayTime +')';
    if (delayTime == 1) {
        delayTime = 120;
        code.setAttribute("sindex", 0);
        code.innerHTML = "获取短信验证码";
    } else {
        setTimeout(countDown, 1000);
    }
}

Min.event.bind('regmcode','focus',function(e){
	this.removeAttribute('style');	
	setError(_$('regmcode-error'),'','hide');
});

Min.event.bind('regsubmit','click',function(){

var phone = _$('regphone').value, code = _$('regcode').value, mcode = _$('regmcode').value,
	pwd = _$('regpwd').value, pwd1 = _$('regpwd1').value, token = _$('csrf_token').value;
	
	
	if(code == '') { 
		code_tag(2);
		setError(_$('regcode-error'),'请输入图片验证码','errors');
	}
	
	if(mcode == '') { 
		setError(_$('regmcode-error'),'请输入短信验证码','errors');
		_$('regmcode').style.borderColor = error_bordercolor;
	}
	
	if(phone == '') {
		_$('regphone').style.borderColor = error_bordercolor;
		setError(_$('regphone-error'),'请输入手机号码','errors');
		 
	}
	if(pwd ==''){
		_$('regpwd').style.borderColor = error_bordercolor;
		setError(_$('regpwd-error'),'请输入密码','errors');
	}
	if(pwd1 ==''){
		_$('regpwd1').style.borderColor = error_bordercolor;
		setError(_$('regpwd1-error'),'请确认密码','errors');
	}
 
	
	if(pwd!= pwd1){
		_$('regpwd1').style.borderColor = error_bordercolor;
		setError(_$('regpwd1-error'),'两次输入密码不相同','errors');
	}
	
	if( false == phone_format(phone) ||  4 != code.length || 6 != mcode.length || !Pwd_OK(pwd) || pwd != pwd1 )  return false;
	
	
	var sindex	= this.getAttribute('sindex');
	
	if(sindex == 0){
	
	this.setAttribute("sindex", 1);

	minAjax({
		url:'http://www.' + site_domain + '/regist.html', 
		type:'POST', 
		data:{
			phone:phone,
			captcha:code,
			smscode:mcode,
			pwd:pwd,
			repwd:pwd1,
			csrf_token:token
		},
		success: function(data){
			if(data.statusCode == 0) {
				window.location.href = 'http://www.' + site_domain;
			}else{
				_$('regsubmit').setAttribute("sindex", 0);
				switch(data.statusCode){
					case 30120: // 手机号码错误
					case 30205: 
						_$('regphone').style.borderColor = error_bordercolor; 
						setError(_$('regphone-error'),data.message,'errors');
						break;
					case 30102: // 验证码错误
						code_tag(2);
						setError(_$('regcode-error'),data.message,'errors');
					break;
					case 30110:
					case 30111:
					case 30114:
						setError(_$('regmcode-error'),data.message,'errors');
						_$('regmcode').style.borderColor = error_bordercolor;
					case 30203:
						_$('regpwd1').style.borderColor = error_bordercolor;
						setError(_$('regpwd1-error'),'两次输入密码不相同','errors');
						break;	
					case 500:
					default:
						_$('error_message').innerHTML= data.message || '系统忙，请重试';
						_$('reg-msg').style.visibility= "visible";
						break;
				}
			}
		},
		fail:function(){
			_$('regsubmit').setAttribute("sindex", 0);
			_$('error_message').innerHTML= "网络异常，请重试";
			_$('reg-msg').style.visibility="visible";
		
		}
	});
	}



});