
function loadStats(option_param)
{
	var xhr=new XMLHttpRequest();
	document.getElementById('stats').innerHTML = "<img src='loading.gif' alt='loading...' />";
	xhr.open('get', 'ajax/ajax_get_stats.php?'+option_param);
	 
	xhr.onreadystatechange=function(){
		if(xhr.readyState === 4) { // if request done
			if(xhr.status === 200) { // if success
				document.getElementById('stats').innerHTML = xhr.responseText;
			} else {
				alert('Error: '+xhr.status+';'+xhr.responseText); // if error
			}
		}
	}
	 
	// Send the request to send-ajax-data.php
	xhr.send(null);
}
