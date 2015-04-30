Kaffeestrichliste
=================

This is a software written for managing the payments for coffee in a small work environment. The system is based on  trust as there is no security to actually check if a customer paid his coffee.

Requirements
------------

This software depends on a webserver of your choice running at least PHP version 5.
You will also need a MySQL server. A common Linux server should always work, however this was never tested on any Windows-based host.

Installation
------------
### 1. Install the sources

Copy the whole directory to a directory in your webroot.
Create a directory named "backups", the software will store backups in this directory.
Make sure that this directory is not accessable from the public but writeable by the user running the webserver. You would normally just set the permission to 777 like this:

	mkdir backups
	chmod 777 backups

In order to keep other users from stealing your backups add a .htaccess file in this directory to forbid access:

	echo "Order deny,allow" > backups/.htaccess
	echo "Deny from all" >> backups.htaccess

### 2. Create a MySQL Database

Set up a new MySQL-Database as well as a new user. Save the username, password as well as the name of the database as you will need those later.

**Make sure the database uses UTF-8 as encoding.**

### 3. Edit the configuration

There is a file called ```config.php.example``` containing an example configuration.
Copy this file to ```config.php``` and edit it with an editor of your choice.

You will need to set some fields in this file, there are instructions included in the file on how to do so and what the specific fields do.

You will also need to edit the ```.htaccess``` file and add IPs to the range of IP adresses from where the software should be reachable. Delete the preconfigured adresses and add your own or make everything public.

### 4. Enjoy a hot cup of coffee

Grab a cup and reward yourself with a nice hot cup of coffee as you are done with setting up the software and should be able to reach it.

Usage
-----

After the installation is done you can now reach the Kaffeestrichliste from the outside. Take a look at the admin-interface as well as the control-interface.

Both pages should be self-explanatory.


License
-------

Copyright (c) 2015 RWTH-Aachen University
Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.

Contact
-------

Please take a look at the issue-tracker and the wiki in this repository before contacting us. If there are no issues or entries in the wiki that can help you deal with the problems please open up a new issue.

Contributors
------------

Frederick Gnodtke (RWTH Aachen)
Jonas Hahnfeld (RWTH Aachen)
