CakePHP 2.X Document Manager Plugin
==============================

This plugin offers the the possibility to have an online browser that allows to manage files inside a directory tree.
You can upload, rename, delete files, create folders, get the absolute URL of a file and much more...


*** Database ***

documents.sql in the root directory contains the structure of the table this plugin uses to store uploaded files as documents.


*** How to load the plugin in your application ***

In your App/Config/bootstrap.php , load the plugin using its own bootstrap file : 
CakePlugin::load('DocumentManager', array('bootstrap' => true));

Load plugin's css files and scripts in the layout you use:

echo $this->fetch('css');
echo $this->fetch('script');

Once the plugin is loaded, its base URL is : /document_manager/documents


*** Dependencies ***

This plugin uses jQuery : http://jquery.com/

Be sure to load jQuery before including the scripts of this plugin. Otherwise while the plugin will still work, AJAX calls won't be possible.

For a better display install Bootstrap. http://twitter.github.io/bootstrap/

For users permissions this plugin is meant to be used with the Authake plugin : https://github.com/mtkocak/authake
Authake.User should declare it hasMany DocumentManager.Document.
- Add the right permissions so Users can access the plugin actions
- Users should have fields id, email, first_name and last_name.
- Users can have field picture (displayed with Documents).

If you have another users management system, change the $belongsTo in Document.php, __construct() function, according to your User class, and change the functions 
getUserId() and isAdmin() in DocumentManager/Controller/DocumentManagerAppController.php and DocumentManager/View/Helper/DocumentManagerHelper.php to retrieve the right information.

Using this plugin without Authake and without modifying these two functions will only work if you disable permission management to allow any user
to access any action and any file as explained below.


*** Turning of permission management ***

If you do not want user permission management, just turn it off by setting DocumentManager.authentification to false in DocumentManager/Config/bootstrap.php


Check out at La Pâtisserie: http://patisserie.keensoftware.com/
