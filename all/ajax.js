
var media_ids = new Array();
var valbum_ids = new Array();
var i = 0;

function generateThumbnailAjax(file_to_process, valbum_id) {
	var xhr=new XMLHttpRequest();
	xhr.open('get', 'generate_thumbnail.php?valbum_id='+valbum_id+'&media_id='+file_to_process);
	 
	xhr.onreadystatechange=function(){
		if(xhr.readyState === 4) { // if request done
			if(xhr.status === 200) { // if success
				i++;
				if (i < media_ids.length) {
					generateThumbnailAjax(media_ids[i], valbum_ids[i]);
				} else {
					location.reload(true);
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
