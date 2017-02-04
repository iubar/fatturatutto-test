@echo off

REM USEAGE: chrome, firefox, marionette, phantomjs
SET BROWSER=chrome
SET SCREENSHOTS_PATH=%~dp0..\..\logs\screenshots
SET SELENIUM_SERVER=localhost
SET SELENIUM_PORT=4444
SET SELENIUM_PATH=\\apollo\doc\dev\selenium
SET APP_HOST=fatturatutto.it
SET /P APP_USERNAME="Please enter the username: "
SET /P APP_PASSWORD="Please enter the password for %APP_USERNAME%: "