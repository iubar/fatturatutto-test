REM start selenium
start C:\Users\Matteo\Desktop\selenium\start_selenium_all.bat

call phpunit -c phpunit_chrome.xml
call phpunit -c phpunit_marionette.xml

start C:\Users\Matteo\Desktop\selenium\start_phantomjs.bat
call phpunit -c phpunit_phantomjs.xml

REM stop selenium
start http://localhost:4444/selenium-server/driver/?cmd=shutDownSeleniumServer