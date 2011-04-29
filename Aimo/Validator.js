/**
 * Aimo Framework
 *
 * LICENSE
 *
 * This is an opensoure software.Used for client side validate
 * @version    $Id$
 * @author     Jackie(jackie@aimosft.cn)
 * @copyright  Copyright (c) 2010 Aimosoft Studio. (http://www.aimosoft.cn)
 */

var Aimo_Validator={
	NotEmpty:function(val){
		if(val=='') return false
		return true;
	},
	StrictNotEmpty:function(val){
		if(val==='') return false
		return true;
	},
	Numeric:function(val){
		return /^\d+$/.test(val);
	},
	Equal:function(val,to){
		if(val == to){
			return true;
		}else{
			return false;
		}
	},
	Max:function(val ,max){
		if(parseInt(val) >= max){
			return true;
		}else{
			return false;
		}
	},
	Min:function(val,min){
		if(parseInt(val) <= min){
			return true;
		}else{
			return false;
		}
	},
	Range:function(val,min,max){
        var value = parseInt(val);
        if (value >= min && value <= max) {
            return true;
        }else{
            return false;
        }
	},
	LenMax:function(val,max){
		var length = val.length;
        if (length > max) {
            return false;
        }else {
            return true;
        }
	},
	LenMin:function(val,min){
		var length = val.length;
        if (length < min) {
            return false;
        }else {
            return true;
        }	
	},
	LenRange:function(val,min,max){
	    var length = val.length;
        if (length >= min && length <= max) {
            return true;
        }else {
            return false;
        }    
	},
	Regex:function(val,pattern){
		return pattern.test(val);
	},
	ArrayRange:function(val,min,max){
		if(!(Object.prototype.toString.call(val) === '[object Array]')){
			return false;
		}
		if(val.length >= min && val.length <= max){
			return true;
		}else{
			return false;
		}
	},
	ArrayNotEmpty:function(val){
		if(!(Object.prototype.toString.call(val) === '[object Array]')){
			return false;
		}
		if (val.length > 0) {
            return true;  
        }else {
            return false;
        }
	},
	ErrorMessage : [],
	ErrorItem : [],

	/*fm:the form object to validate,mode how to prompt the error msg*/
	Validate:function(theForm,mode){
		this.ErrorItem.length = 0;
		this.ErrorMessage.length = 0;
		var obj   = theForm || event.srcElement;
		var ElNum = obj.elements.length;
		var MultiEl = {};
		var result = true;
		for(var i=0;i< ElNum;i++){
			
			with(obj.elements[i]){
				var Type = getAttribute("type");
				var name = getAttribute("name");
				var validators = null;
				var msg        = null;
				var val		   = null;
				if(Type == 'checkbox'){
					if(MultiEl[name]) continue;
					MultiEl[name] = true;
					var onEl = this.parentEl(this.parentEl(obj.elements[i]));
					var tmp = onEl.getAttribute("validators");
					if(tmp == null || tmp=='') continue;
					validators = tmp.split("&&");
					val = "[";
					var com = "";
					var boxEl = obj.elements[name];
					for(var i=0;i< boxEl.length;i++){
						if(boxEl[i].checked == true){
							val += com+"'"+boxEl[i].value+"'";
						}
						com = ",";
					}
					val +="]";
					msg   = onEl.getAttribute("msg");
					
				}else if(Type == 'radio'){
					if(MultiEl[name]) continue;
					MultiEl[name] = true;
					var onEl = this.parentEl(this.parentEl(obj.elements[i]));
					var tmp = onEl.getAttribute("validators");
					if(tmp == null || tmp=='') continue;
					validators = tmp.split("&&");
					 val = "\'\'";
					
					var boxEl = obj.elements[name];
					for(var i=0;i< boxEl.length;i++){
						if(boxEl[i].checked == true){
							val = boxEl[i].value;
							break;
						}
					}
					msg   = onEl.getAttribute("msg");					

				}else{
					var onEl = obj.elements[i];
					var tmp = getAttribute("validators");
					if(tmp == null || tmp=='') continue;
					validators = tmp.split("&&");
					val		   = "\'"+obj.elements[i].value+"\'";
					msg		   = getAttribute("msg");	
				}
				 
				for(var j = 0;j < validators.length;j++){
					var func = 'var ret = this.';
					var posBegin  = validators[j].indexOf("(")+1;
					if(posBegin ==0){
						func+=validators[j]+"("+val+")";
					}else{
						func+=validators[j].replace("(","("+val+",");
					}					
					eval(func);
					if(!ret){
						result = ret;
					}

				}
				if(!ret){
					this.AddError(onEl,msg);
				}
			}
		}
	  if(this.ErrorMessage.length > 1){
			mode = mode || 1;
			var errCount = this.ErrorItem.length;
			switch(mode){
			case 2 :
				for(var i=0;i<errCount;i++){
					this.ErrorItem[i].style.color = "red";
				}
			case 1 :
				alert(this.ErrorMessage.join("\n"));
				break;
			case 3 :
				for(var i=0;i<errCount;i++){
				this.ClearState(this.ErrorItem[i]);	
				try{
					var span = document.createElement("SPAN");
					span.id = "AIMOFRAMEWORK_ERROR";
					span.style.color = "red";
						this.parentEl(this.ErrorItem[i]).appendChild(span);
						span.innerHTML = this.ErrorMessage[i].replace(/\d+:/,"*");
					}
					catch(e){alert(e.description);}
				}
				break;
			default :
				alert(this.ErrorMessage.join("\n"));
				break;
			}
			return false;
		}
		return result;
	},
	ClearState : function(elem){
		with(elem){
			if(style.color == "red")
				style.color = "";
			var parent = this.parentEl(elem);
			var errorSpan = this.getErrorSpan(parent);
			if(errorSpan) parent.removeChild(errorSpan);
		}
	},
	AddError : function(obj, str){
		this.ErrorItem[this.ErrorItem.length] = obj;
		this.ErrorMessage[this.ErrorMessage.length] = this.ErrorMessage.length + ":" + str;
	},
	parentEl:function(el){
		var parent =el.parentNode;
		while(parent && parent.nodeType!=1)
		{
			parent = parent.nextSibling;
		}
		return parent;
	},
	getErrorSpan:function(el){
		var childs = el.childNodes;
		var spanEl = null;
		for(var i=0;i<childs.length;i++){
			if(childs[i].nodeType ==1 && childs[i].nodeName=='SPAN' && childs[i].id=='AIMOFRAMEWORK_ERROR' )
				spanEl = childs[i];
		}
		return spanEl;
	}
};

