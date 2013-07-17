<?php
/** Basic Configuration */
Configure::write('DocumentManager.baseDir', 'files'); // Root folder of your directory tree inside of webroot, use relative path ex: 'files/documents'
Configure::write('DocumentManager.excludeRootFiles', true); // Option to display files inside the root folder or only directories (ex: hide the mess in the files directory)

/** Permission Configuration */
Configure::write('DocumentManager.authentification', true); // Associate files to a user, and prevent illegal deletion/renaming by another non admin user. Turning to false makes all files manageable by all users.