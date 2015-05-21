
var media_ids_to_process = new Array();
var i = 0;

function generateThumbnailAjax(file_to_process, valbum_id){
	var xhr=new XMLHttpRequest();
	xhr.open('get', 'generate_thumbnail.php?valbum_id='+valbum_id+'&media_id='+file_to_process);
	 
	xhr.onreadystatechange=function(){
		if(xhr.readyState === 4){ // if request done
			if(xhr.status === 200){ // if success
				i++;
				if (i < media_ids_to_process.length)
				{
					generateThumbnailAjax(media_ids_to_process[i], valbum_id);
				}
				else
				{
					location.reload(true);
				}
				document.getElementById('main').innerHTML = 
					"<h2 class='fullscreen_overlay'><br />...Please wait, thumbnail "+i+"/"+media_ids_to_process.length+" loaded...</h2>";
			}else{
				alert('Error: '+xhr.status); // if error
			}
		}
	}
	 
	// Send the request to send-ajax-data.php
	xhr.send(null);
}

