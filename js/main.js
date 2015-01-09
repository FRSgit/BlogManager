/* MAIN */
window.addEventListener('load', JsLoadFunc, false);

function JsLoadFunc(){
	var stylesArr = document.styleSheets,
		mainStyleSheet = document.preferredStyleSheetSet,
		el = document.createElement('div');
		el.id = "colorVer";
		el.innerHTML = "Other versions of page:",
		definedCookie = getCookie("stylesheetName");

	if(definedCookie!=""){
		changePageStyle(definedCookie,1);
		for(var i=0;i<stylesArr.length;i++){
			if(definedCookie!=stylesArr[i].title)
				el.innerHTML+=' <a href="#" onclick="changePageStyle(\''+stylesArr[i].title+'\');return false;">'+stylesArr[i].title+'</a>'
			else
				el.innerHTML+=' '+stylesArr[i].title;
		}
	}else{
		for(var i=0;i<stylesArr.length;i++){
			if(stylesArr[i].title==mainStyleSheet)
				el.innerHTML+=' '+stylesArr[i].title;
			else
				el.innerHTML+=' <a href="#" onclick="changePageStyle(\''+stylesArr[i].title+'\');return false;">'+stylesArr[i].title+'</a>'	
		}
	}
	document.getElementById("wrapper").appendChild(el);
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

/* ADDITIONAL FUNCTIONS */
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

    if(month<=0 && month>12)
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
			if(links[i].title == name){
				links[i].disabled = false;
				setCookie("stylesheetName", name);
				if(!firstTime)
					innerHTML+=' '+name;
			}else{
				links[i].disabled = true;
				if(!firstTime)
					innerHTML+=' <a href="#" onclick="changePageStyle(\''+links[i].title+'\');return false;">'+links[i].title+'</a>';
			}
		}
	}
	if(!firstTime){
		var menuEl = document.getElementById("colorVer");
		menuEl.innerHTML = innerHTML;
	}
}
