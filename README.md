AweTracker
==========

## How to use

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
- Change __FILTR_APPTOKEN to your applications access token (second one).

- Change __BASEDIR to where AweTracker is on the system.
- Change __ANNOUNCE to the URL of announce.php

- Scroll down to the "Database connection settings" part.
- You know what to do.

### Part 3
- Create a database.
- Set charset to UTF8_GENERAL__CI
- Run the commands from awe.sql

### You are done!
For more settings, see the "settings" table in the database.

## IMPORTANT!
This system is in aplha state without real version number.
If you find a bug PLEASE REPORT IT!
If you have time, please help me developing this tracker.

Feel free to send me an email: spam at sandros.hu (not joking!)
