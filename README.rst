.. image:: https://raw.githubusercontent.com/phrounz/lilliputian-photo/master/all/logo.png
It started as a personal need to share my photographies and videos, in order to replace Google+. See also other stuff my `web site <http://www.volatiledove.com>`_.

Everything is made by me, except the code of thumbnail image generation based on code by zubrag.com (see `here <http://www.zubrag.com/scripts/website-thumbnail-generator.php>`_ and  `here <http://www.zubrag.com/forum/index.php/board,13.0.html>`_).

What it looks like :
 * `administration: <https://raw.githubusercontent.com/phrounz/lilliputian-photo/master/screenshots/admin.jpg>`_
 * `user albums list page <https://raw.githubusercontent.com/phrounz/lilliputian-photo/master/screenshots/user_list.jpg>`_
 * `user album page <https://raw.githubusercontent.com/phrounz/lilliputian-photo/master/screenshots/user_album.jpg>`_
 *  `user media page <https://raw.githubusercontent.com/phrounz/lilliputian-photo/master/screenshots/user_media.jpg>`_

Installation for noobs :
 * Copy all the files in all/ on your web server, for example with an `FTP client <https://filezilla-project.org>`_.
  * If this is not bound to be public (e.g. shared with friends or family), it is highly advised to put it in a hidden path in the website. For example, if your domain is *example.com*, you can put it in a folder named *www.example.com/56165452012132135/*, with 56165452012132135 being a random number, and share this url only with the people you want. This is a simple additional security measure, but quite useful.
 * This service use `Basic Authentication <https://en.wikipedia.org/wiki/Basic_access_authentication>`_ (with the php variable *$_SERVER['REMOTE_USER']*), and requires at least a user "admin" to work. HTTPS is not compulsory but highly advised.
  * If this is an Apache web server, edit *.htaccess*, and setup your users in the *.htpasswd* file. 
  * If this is not an Apache web server, remove *.htaccess* and well... read the manual.
  * If you don't understand, ask your hoster for help.
 * Edit the file *inc/conf.inc.php* . You probably don't need to change anything, except that you may like to change the value of *CONST_MAIN_TITLE* (this is the title of all the pages). If you don't understand, don't worry and skip this line.
 * Upload your pictures and video organized as you wish into subdirectories of albums/ , for example with an FTP client. You can remove the directory *example_album*.
 * Go with your browser to the index page, connect as user "admin".
 * Add visibility for the other users with "Virtual albums and group titles".
 * Click on "disconnect or change user", and now connect with another login to see what visibility you set up.
 * Go in each album to load the thumbnails for each picture.