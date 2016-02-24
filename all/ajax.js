
var media_ids = new Array();
var valbum_ids = new Array();
var i = 0;

function generateThumbnailAjaxGenerator() {
	
	document.getElementById('main').innerHTML = 
		"<h2 class='fullscreen_overlay'><br />...Please wait, getting list (this may take some minutes)...</h2>";
	 
	var xhr=new XMLHttpRequest();
	xhr.open('get', 'generate_thumbnail_js.php');
	
	xhr.onreadystatechange=function(){
		if(xhr.readyState === 4) { // if request done
			if(xhr.status === 200) { // if success
				eval(xhr.responseText);
				if (media_ids.length > 0) {
					generateThumbnailAjax_(media_ids[0], valbum_ids[0]);
				} else {
					alert("Ok, nothing to do!");
					window.location="?";
				}
			} else {
				alert('Error: '+xhr.status); // if error
			}
		}
	}
	 
	// Send the request to send-ajax-data.php
	xhr.send(null);
}


function generateThumbnailAjax_(file_to_process, valbum_id) {
	var xhr=new XMLHttpRequest();
	xhr.open('get', 'generate_thumbnail.php?valbum_id='+valbum_id+'&media_id='+file_to_process);
	 
	xhr.onreadystatechange=function(){
		if(xhr.readyState === 4) { // if request done
			if(xhr.status === 200) { // if success
				i++;
				if (i < media_ids.length) {
					generateThumbnailAjax_(media_ids[i], valbum_ids[i]);
				} else {
					alert('Ok, finished!');
					window.location="?";
				}
				document.getElementById('main').innerHTML = 
					"<h2 class='fullscreen_overlay'><br />...Please wait, thumbnail/reduced image "+i+"/"+media_ids.length+" loaded...<br />(virtual album number: "+valbum_id+" - picture: "+file_to_process+"</h2>";
			} else {
				alert('Error: '+xhr.status); // if error
			}
		}
	}
	 
	// Send the request to send-ajax-data.php
	xhr.send(null);
}
