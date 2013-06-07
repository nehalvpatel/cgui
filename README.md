cgui
==========

A web interface for cgminer that is made to reduce setup time and increase productivity, featuring the famous 2 minute install.

Getting started
----------
Download the files and extract them in a folder in your web root directory. I recommend using [XAMPP](http://www.apachefriends.org/en/xampp.html).

Setting up
----------
Update your cgminer arguments to include ```--api-listen --api-network --api-port 4028```, then edit ```index.php``` with your rig info.

**Name**: whatever you call your rig, optional  
**Address**: the local or remote IP address of the rig  
**Port**: the port you set in the cgminer arguments, probably 4028

Also, remember to edit the timezone with one from [this list](http://php.net/manual/en/timezones.php).
