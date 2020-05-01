AweTracker
==========

## Demo
I'm running a demo here: *** discontinued ***
It's always on the latest version so here, you'll be able to check the building process.
Since I don't have much time to spend on this, feel free to contribute to the project.

## How to use
Since I did not want to create a reg/login system Filtr. is used to handle user authentication. Feel free to replace it with your solution.

### Part 1
- Create a Filtr. account at https://filtr.sandros.hu
- Create an application. Make sure you use real data. The URL format: http://YOURDOMAIN.com/?token={token}
- Go back to the Filtr. dashboard (main page).
- Click on your applications ID. You will see 2 hashes. Save these.

### Part 2
- Upload the AweTracker files to your server.
- Open includes/_config.php

- Change __FILTR_ID to your applications ID.
- Change __FILTR_STAT to your applications STATT token.
- Change __FILTR_APPTOKEN to your applications access token.

- Change __BASEDIR to where AweTracker is on the system.
- Change __ANNOUNCE to the URL of announce.php

- Scroll down to the "Database connection settings" part.
- You know what to do.

### Part 3
- Create a database.
- Set charset to UTF8_GENERAL_CI
- Run the commands from awe.sql

### You are done!
For more settings, see the "settings" table in the database.

## IMPORTANT!
This is/was a hobby project from some time ago when I got the idea to experiment with p2p/torrent trackers.
I'd suggest that you do not use this for any important/public purposes, it may have fatal bugs/vulnerabilities. 
