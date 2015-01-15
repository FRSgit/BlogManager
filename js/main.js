/* MAIN */
window.addEventListener('load', JsLoadFunc, false);

function JsLoadFunc(){
	loadStylesheetsBtns();
	handleChat();
}

/* SubPages */
function wpis(){
	var dateInput = document.getElementsByName('postDate')[0],
		timeInput = document.getElementsByName('postTime')[0],
		anotherFile = document.getElementsByName('addAnotherFile')[0],
		now = new Date();
	if(dateInput&&timeInput&&anotherFile){
		if(!dateInput.value){
			var date = now.getFullYear()+"-"+twoDigits(now.getMonth()+1)+"-"+twoDigits(now.getDate());
			dateInput.value=date;
		}
		if(!timeInput.value){
			var time = twoDigits(now.getHours())+":"+twoDigits(now.getMinutes());
			timeInput.value=time;
		}

		dateInput.onchange = function(e){
			var parent = e.target.parentNode;
			if(!checkDate(e.target.value)){
				var now = new Date(),
					date = now.getFullYear()+"-"+twoDigits(now.getMonth()+1)+"-"+twoDigits(now.getDate());
				e.target.value=date;
				if(parent.getElementsByClassName("bad")[0] === undefined){
					var el = document.createElement('span');
					el.className = "bad"
					el.innerHTML = "Bad date format. Should be YYYY-MM-DD.";
					parent.insertBefore(el, parent.firstChild);
				}
			}else{
				var badEl = parent.getElementsByClassName("bad")[0];
				if(badEl !== undefined)
					parent.removeChild(badEl);
			}
		}

		timeInput.onchange = function(e){
			var parent = e.target.parentNode;
			if(!checkTime(e.target.value)){
				var now = new Date(),
					time = twoDigits(now.getHours())+":"+twoDigits(now.getMinutes());
				e.target.value=time;
				if(parent.getElementsByClassName("bad")[0] === undefined){
					var el = document.createElement('span');
					el.className = "bad";
					el.innerHTML = "Bad time format. Should be HH:MM.";
					parent.insertBefore(el, parent.firstChild);
				}
			}else{
				var badEl = parent.getElementsByClassName("bad")[0];
				if(badEl !== undefined)
					parent.removeChild(badEl);
			}
		}

		anotherFile.onclick = function(e){
			var parent = e.target.parentNode;
			if(document.getElementsByName("postAttach[]").length<MAX_ATTACHMENTS){
				var el = document.createElement('input');
					el.className = "block";
					el.name = "postAttach[]";
					el.type = "file";
					parent.insertBefore(el, e.target);
				if(document.getElementsByName("postAttach[]").length==MAX_ATTACHMENTS)
					parent.removeChild(e.target);
			}else
				alert("You have reached maximum amount of attachments("+MAX_ATTACHMENTS+")");

			return false;
		}
	}	
}

/* -- GENERATING BUTTONS TO HANDLE ALTERNATE STYLESHEETS -- */

function loadStylesheetsBtns(){
	var usedStyleSheet,
		stylesArr = document.styleSheets,
		el = document.createElement('div');
		el.id = "colorVer";
		el.innerHTML = "Other versions of page:",
		definedCookie = getCookie("stylesheetName");

	if(definedCookie!=""){
		changePageStyle(definedCookie,1);
		usedStyleSheet = definedCookie;
	}else
		usedStyleSheet = (typeof document.preferredStyleSheetSet != 'undefined')?document.preferredStyleSheetSet:document.preferredStylesheetSet;
	
	for(var i=0;i<stylesArr.length;i++){
		if(el.innerHTML.indexOf(stylesArr[i].title)==-1){
			if(usedStyleSheet==stylesArr[i].title)
				el.innerHTML+=' '+usedStyleSheet;
			else{
				el.innerHTML+=' <a href="#" onclick="changePageStyle(\''+stylesArr[i].title+'\');return false;">'
				+
				stylesArr[i].title+'</a>';
			}
		}	
	}
	document.getElementById("wrapper").appendChild(el);
}

/* -- COMMUNICATOR -- */

var lastChatDate,
	blockAjax,
	blockSend,
	defNamePlaceholder,
	defMsgPlaceholder,
	defTextareaValue,
	chatRequestLoader;

function handleChat(){
	document.getElementById("chatHide").style.height=0;
	if(!defTextareaValue) defTextareaValue = document.getElementById("msgsCont").value;
	if(!defNamePlaceholder) defNamePlaceholder = document.getElementById("chatName").placeholder;
	if(!defMsgPlaceholder) defMsgPlaceholder = document.getElementById("chatMsg").placeholder;
	document.getElementById("toggleChat").onchange = function(){
		toggleChatWindow();
	}
	
}

function toggleChatWindow(){
	var chat = document.getElementById("chatHide");
		if(document.getElementById("toggleChat").checked){
			chat.style.height=(document.getElementById("chatCont").offsetHeight+40)+"px";
			document.getElementById("chatToggleBtnIn").className="checked";
			initChat();
		}else{
			chat.style.height="0";
			document.getElementById("chatToggleBtnIn").className="";
			removeChat();
		}
}

/* CHAT VALIDATION */

function initChat(){
	var chatMsg = document.getElementById("chatMsg"),
		chatName = document.getElementById("chatName"),
		chatTextarea = document.getElementById("msgsCont");
	
	/* INIT VALUES */
	lastChatDate = 1;
	blockAjax = false;
	blockSend = false;

	chatTextarea.value = defTextareaValue;
	chatName.disabled = false;
	chatName.placeholder = defNamePlaceholder;
	chatMsg.disabled = false;
	chatMsg.placeholder = defMsgPlaceholder;
	chatEnableBtn();

	chatName.addEventListener("change",chatNameValidator);
	chatMsg.addEventListener("change",chatMsgValidator);
	document.getElementById("chatSend").addEventListener("click",chatSendMsg);
	document.addEventListener("keydown", chatEnterHandler);
	loadChatMsgs();
}

function removeChat(){
	var chatMsg = document.getElementById("chatMsg"),
		chatName = document.getElementById("chatName"),
		chatTextarea = document.getElementById("msgsCont");

	chatTextarea.value = "";
	blockSend = true;

	chatName.disabled = true;
	chatMsg.disabled = true;
	chatDisableBtn();

	if(chatRequestLoader){
		chatRequestLoader.removeEventListener("readystatechange", handleRequest);
		chatRequestLoader.abort();
	}
	chatName.removeEventListener("change",chatNameValidator);
	chatMsg.removeEventListener("change",chatMsgValidator);
	document.getElementById("chatSend").removeEventListener("click",chatSendMsg);
	document.removeEventListener("keydown", chatEnterHandler);
}

function restartChat(msg){
	document.getElementById("toggleChat").checked = false;
	document.getElementById("chatToggleBtnIn").className="";
	removeChat();

	document.getElementById("msgsCont").value=msg+"\nRestarting.";
	var tmpInterval = setInterval(function(){
	    document.getElementById("msgsCont").value += ".";
	},150);
	setTimeout(function(){
	    clearTimeout(tmpInterval);
	    document.getElementById("toggleChat").checked = true;
	    document.getElementById("chatToggleBtnIn").className="checked";
	    initChat();
	}, 8000);
}

function chatValidator(){
	var flag = true;

	if(!chatNameValidator())
	    flag = false;

	if(!chatMsgValidator())
	    flag = false;

	if(!flag)
	    return flag;

	chatEnableBtn();

	return flag;
}

function chatNameValidator(){
	var val = document.getElementById("chatName").value;
	if(!val.length||val.length>CHAT_MAXLENGTH_AUTHOR){
	    document.getElementById("chatName").parentNode.className = "bad";
	    document.getElementById("chatName").placeholder = "This field is required";
	    return false;
	}
	document.getElementById("chatName").parentNode.className = "";
	document.getElementById("chatName").placeholder = defNamePlaceholder;
	return true;
}

function chatMsgValidator(){
	var val = document.getElementById("chatMsg").value.trim();
	if(!val.length||val.length>CHAT_MAXLENGTH_MSG){
	    document.getElementById("chatMsg").parentNode.className = "bad";
	    document.getElementById("chatMsg").placeholder = "This field is required to a send message";
	    return false;
	}
	document.getElementById("chatMsg").parentNode.className = "";
	document.getElementById("chatMsg").placeholder = defMsgPlaceholder;
	return true;
}

function chatEnterHandler(e){
    if(e.keyCode===13)
        chatSendMsg();
}

function chatEnableBtn(){
	document.getElementById("chatSend").className="";
	document.getElementById("chatSend").disabled=false;
}

function chatDisableBtn(){
	document.getElementById("chatSend").className="disabled";
	document.getElementById("chatSend").disabled=true;
}

/* CHAT AJAX */
function loadChatMsgs(){
	if(!blockSend){
		chatRequestLoader = new XMLHttpRequest();
		blockSend = true;
		chatRequestLoader.addEventListener("readystatechange", handleRequest);
		chatRequestLoader.open('POST', './chatRead.php', true);
		chatRequestLoader.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
		chatRequestLoader.send('chatDate='+lastChatDate);
	} 
}

function chatSendMsg(author, content){
	if((author && content) || chatValidator()){
		if(typeof content==='undefined'){
			author = encodeHtml(document.getElementById("chatName").value);
			content = encodeHtml(document.getElementById("chatMsg").value.trim());
			document.getElementById("chatMsg").value = "";
		}
		if(!blockAjax){
			chatDisableBtn();
			document.getElementById("chatSend").removeEventListener("click",chatSendMsg);
			document.removeEventListener("keydown", chatEnterHandler);

			blockAjax = true;
			var httpRequest = new XMLHttpRequest();
			httpRequest.onreadystatechange = handleRequest; 
			httpRequest.open('POST', './chatSend.php', true);
			httpRequest.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
			httpRequest.send('chatAuthor="'+author+'"&chatContent="'+content+'"');
		}else
			setTimeout(chatSendMsg(author, content),600);
	}	
}

function handleRequest(e){
	var httpRequest = e.target;
	if(httpRequest.readyState === 4){
		if(httpRequest.status === 200){
			var response = JSON.parse(httpRequest.responseText);
			if(!response.error&&response.error!=""){
				if(response.msgAdd){
					blockAjax = false;
					document.getElementById("chatSend").addEventListener("click",chatSendMsg);
					document.addEventListener("keydown", chatEnterHandler);
					chatEnableBtn();
				}else {
					if(!response.noMsg){
						if(lastChatDate==1)
					    	msgsCont.value=decodeHtml(response.content.trim());
					    else if(response.content && response.content!="")
					    	msgsCont.value+="\n"+decodeHtml(response.content.trim());
						
						if(response.date && response.date!="")
							lastChatDate = response.date;
						else
							restartChat("Error!\n#2 Problem with loading the chat!\nWe'll try to restart chat now.\nIf problem still occurs please contact the administration.");
					}

					msgsCont.scrollTop = msgsCont.scrollHeight;
					blockSend = false;

					setTimeout(loadChatMsgs, CHAT_TIME_TO_REFRESH * 1000);
				}		
			}else
			    restartChat(response.error+"\nWe'll try to restart chat now.\nIf problem still occurs please contact the administration.");	
		}else
		    restartChat("Error!\n#1 Problem with loading the chat!\nWe'll try to restart chat now.\nIf problem still occurs please contact the administration.");
	}
};

/* ADDITIONAL FUNCTIONS */
function encodeHtml(html){
	return (html&&html!="")?encodeURI(encodeURIComponent(html)):"";
}

function decodeHtml(html) {
	if(html&&html!=""){
    		var txt = document.createElement("textarea");
    		txt.innerHTML = html;
    		return decodeURIComponent(txt.value);
	}else
		return "";
}

function setCookie(cname, cvalue, exdays) {
	exdays = typeof exdays!=='undefined'?exdays:9999;
    var d = new Date();
    d.setTime(d.getTime()+(exdays*24*60*60*1000));
    var expires = "expires="+d.toUTCString();
    document.cookie = cname+"="+cvalue+"; "+expires;
}

function getCookie(cname) {
    var name = cname+"=";
    var ca = document.cookie.split(';');
    for(var i=0;i<ca.length;i++) {
        var c = ca[i];
        while (c.charAt(0)==' ') c=c.substring(1);
        if (c.indexOf(name)==0) return c.substring(name.length,c.length);
    }
    return "";
} 

function twoDigits(n){
	return (n<10)?'0'+n:n;
}

function checkDate(date){
	if(!/^\d{4}-\d{2}-\d{2}$/.test(date))
        return false;

    var dateSplit = date.split("-"),
    	year =parseInt(dateSplit[0], 10),
    	month = parseInt(dateSplit[1], 10),
    	day = parseInt(dateSplit[2], 10);

    if(year<1500)
    	return false;

    if(month<=0 || month>12)
    	return false;

    var months = [ 31, (year%4==0)?29:28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31 ];
    return day>0 && day<=months[month-1];
}

function checkTime(time){
	if(!/^\d{2}:\d{2}$/.test(time))
        return false;

    var timeSplit = time.split(":"),
    	hour =parseInt(timeSplit[0], 10),
    	min = parseInt(timeSplit[1], 10);

    if(hour>23)
    	return false;

    return min>=0 && min<60;
}

function changePageStyle(name, firstTime){
	var links=document.getElementsByTagName("link"),
		innerHTML = 'Other versions of page:',
		firstTime = typeof firstTime!=='undefined'?firstTime:0;

	for (var i=0;i<links.length;i++){
		if(links[i].rel.indexOf( "stylesheet" )!=-1 && links[i].title && innerHTML.indexOf(links[i].title)==-1){
			links[i].disabled = true;
			if(links[i].title == name){
				links[i].disabled = false;
				setCookie("stylesheetName", name);
				if(!firstTime)
					innerHTML+=' '+name;
			}else if(!firstTime)
					innerHTML+=' <a href="#" onclick="changePageStyle(\''+links[i].title+'\');return false;">'+links[i].title+'</a>';
		} else if(innerHTML.indexOf(links[i].title) != -1)
			links[i].disabled = true;
	}
	if(!firstTime){
		var menuEl = document.getElementById("colorVer");
		menuEl.innerHTML = innerHTML;
	}
}
