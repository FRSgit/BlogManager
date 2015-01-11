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
		if(usedStyleSheet==stylesArr[i].title)
			el.innerHTML+=' '+usedStyleSheet;
		else
			el.innerHTML+=' <a href="#" onclick="changePageStyle(\''+stylesArr[i].title+'\');return false;">'+stylesArr[i].title+'</a>'
	}
	document.getElementById("wrapper").appendChild(el);
}

/* -- COMMUNICATOR -- */

var lastChatDate,
	blockAjax = false;

function handleChat(){
	document.getElementById("chat").style.bottom="-"+document.getElementById("chat").offsetHeight+"px";
	lastChatDate = 1; // default init value
	document.getElementById("toggleChat").onchange = function(){
		toggleChatWindow();
	}
	
}

function toggleChatWindow(){
	var chat = document.getElementById("chat"),
		btmVal = "-"+chat.offsetHeight+"px";

		if(chat.style.bottom==btmVal){
			chat.style.bottom="0";
			document.getElementById("chatToggleBtnIn").className="checked";
			loadChatMsgs();
			initChatValidation();
		}else{
			chat.style.bottom=btmVal;
			document.getElementById("chatToggleBtnIn").className="";
			removeChatValidation();
		}
}

/* CHAT VALIDATION */

function initChatValidation(){
	chatNameValidator();
	document.getElementById("chatName").addEventListener("change",chatValidator);
	document.getElementById("chatMsg").addEventListener("change",chatValidator);
	document.getElementById("chatSend").addEventListener("click",chatSendMsg);
	document.addEventListener("keydown", chatEnterHandler);
}

function removeChatValidation(){
	document.getElementById("chatName").removeEventListener("change",chatValidator);
	document.getElementById("chatMsg").removeEventListener("change",chatValidator);
	document.getElementById("chatSend").removeEventListener("click",chatSendMsg);
	document.removeEventListener("keydown", chatEnterHandler);
}

function chatValidator(){
	if(!chatNameValidator()){
		document.getElementById("chatName").parentNode.className="bad";
		chatDisableBtn();
		return false;
	}else
		document.getElementById("chatName").parentNode.className="";

	if(!chatMsgValidator()){
		document.getElementById("chatMsg").parentNode.className="bad";
		document.getElementById("chatSend").className="disabled";
		document.getElementById("chatSend").disabled=true;
		return false;
	}else
		document.getElementById("chatMsg").parentNode.className="";

	chatEnableBtn();

	return true;
}

function chatNameValidator(){
	var val = document.getElementById("chatName").value;
	return !(document.getElementById("chatMsg").disabled=(!val.length||val.length>30)?true:false);
}

function chatMsgValidator(){
	var val = document.getElementById("chatMsg").value;
	return (!val.length||val.length>400)?false:true;
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
	if(!blockAjax){
		blockAjax = true;
		var httpRequest = new XMLHttpRequest();
		httpRequest.onreadystatechange = handleRequest;
		httpRequest.open('POST', './chat.php', true);
		httpRequest.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
		httpRequest.send('chatDate='+lastChatDate);
	}else
		setTimeout(loadChatMsgs,4000);
}

function chatSendMsg(content){
	if(chatValidator()){
		content = (typeof content!=='undefined'&&typeof content==='string')?content:encodeHtml(document.getElementById("chatMsg").value);
		document.getElementById("chatMsg").value = "";
		if(!blockAjax){
			blockAjax = true;
			var httpRequest = new XMLHttpRequest(),
				author = encodeHtml(document.getElementById("chatName").value);
			
			httpRequest.onreadystatechange = handleRequest; 
			httpRequest.open('POST', './chat.php', true);
			httpRequest.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
			httpRequest.send('chatDate='+lastChatDate+'&chatAuthor="'+author+'"&chatContent="'+content+'"');
		}else{
			setTimeout(chatSendMsg(content),300);
		}
	}	
}

function handleRequest(e){
	var httpRequest = e.target;
	if(httpRequest.readyState === 4){
		if(httpRequest.status === 200){
			var response = JSON.parse(httpRequest.responseText),
				msgsCont = document.getElementById("msgsCont");
			if(!response.error&&response.error!=""){
			    if(lastChatDate==1){
			    	msgsCont.value=decodeResponse(response.content);
			    	msgsCont.scrollTop = msgsCont.scrollHeight;
			    }else if(response.content&&response.content!=""){
			    	msgsCont.value+="\n"+decodeResponse(response.content);
			    	msgsCont.scrollTop = msgsCont.scrollHeight;
			    }

				lastChatDate=response.date;
				blockAjax = false;
				if(!response.addMsg)
					setTimeout(loadChatMsgs,4000);
			}else
				msgsCont.value = response.error;
		}else 
		    document.getElementById("msgsCont").value="Error!\nProblem with loading the chat!\nReload page and try again. If problem still occurs please contact the administration.";
	}
};

function decodeResponse(r){
	if(r&&r!=""){
		return decodeHtml(r.trim());
	}else
		return "";
}

/* ADDITIONAL FUNCTIONS */
function encodeHtml(html){
	return (html&&html!="")?encodeURI(encodeURIComponent(html)):"";
}

function decodeHtml(html) {
    var txt = document.createElement("textarea");
    txt.innerHTML = html;
    return decodeURIComponent(txt.value);
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
		if(links[i].rel.indexOf( "stylesheet" )!=-1 && links[i].title){
			links[i].disabled = true;
			if(links[i].title == name){
				links[i].disabled = false;
				setCookie("stylesheetName", name);
				if(!firstTime)
					innerHTML+=' '+name;
			}else if(!firstTime)
					innerHTML+=' <a href="#" onclick="changePageStyle(\''+links[i].title+'\');return false;">'+links[i].title+'</a>';
		}
	}
	if(!firstTime){
		var menuEl = document.getElementById("colorVer");
		menuEl.innerHTML = innerHTML;
	}
}
