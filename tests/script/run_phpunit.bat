REM start selenium
start C:\Users\Matteo\Desktop\selenium\start_selenium_chrome.bat

call %~dp0env.bat
call phpunit -c %~dp0..\..\phpunit.xml

REM stop selenium
start http://localhost:4444/selenium-server/driver/?cmd=shutDownSeleniumServer