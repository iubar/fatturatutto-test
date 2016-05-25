REM start selenium
start C:\Users\Matteo\Desktop\selenium\start_selenium_chrome.bat

call setenv.bat
call phpunit -c ..\..\phpunit.xml

REM stop selenium
REM start http://localhost:4444/selenium-server/driver/?cmd=shutDownSeleniumServer