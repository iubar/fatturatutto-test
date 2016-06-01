REM USEAGE: chrome, firefox, marionette, phantomjs

SET BROWSER=chrome
SET SCREENSHOTS_PATH=%~dp0..\..\logs\screenshots\
SET SELENIUM_SERVER=localhost:4444
SET SELENIUM_PATH=%UserProfile%\Desktop\selenium
SET /P FT_USERNAME="Please enter the username: "
SET /P FT_PASSWORD="Please enter the password for %FT_USERNAME%: "