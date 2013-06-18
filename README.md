cgui
==========

A web interface for cgminer that is made to reduce setup time and increase productivity, featuring the famous 2 minute install. It adapts to mobile screens and re-arranges itself to look good on any display. Here's a [demo](http://patel.no-ip.biz).

Getting started
----------
Download the files and extract them in a folder in your web root directory. I recommend using [XAMPP](http://www.apachefriends.org/en/xampp.html).

Setting up
----------
Update your cgminer arguments to include ```--api-listen --api-network --api-port 4028```, then edit ```index.php``` with your rig info. Also, remember to edit the timezone with one from [this list](http://php.net/manual/en/timezones.php).

----------

**Name**: whatever you call your rig, optional  
**Address**: the local or remote IP address of the rig  
**Port**: the port you set in the cgminer arguments, probably 4028

----------

For the `$apis` array, use the pool's mining URL as the key and the API URL as the value.

Like: `"mining.example.com" => "https://www.example.com/api?key=yourkeyhere"`
