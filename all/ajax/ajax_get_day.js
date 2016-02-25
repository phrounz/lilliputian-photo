
function loadDay(valbum_id, day)
{
	var xhr=new XMLHttpRequest();
	document.getElementById('day-'+day).innerHTML = document.getElementById('day-'+day).innerHTML+"<img src='loading.gif' alt='loading...' />";
	xhr.open('get', 'ajax/ajax_get_day.php?valbum_id='+valbum_id+'&day='+day);
	 
	xhr.onreadystatechange=function(){
		if(xhr.readyState === 4) { // if request done
			if(xhr.status === 200) { // if success
				document.getElementById('day-'+day).innerHTML = xhr.responseText;
			} else {
				alert('Error: '+xhr.status+';'+xhr.responseText); // if error
			}
		}
	}
	 
	// Send the request to send-ajax-data.php
	xhr.send(null);
}
