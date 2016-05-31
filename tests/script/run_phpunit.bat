@echo off
REM start selenium

call %~dp0env.bat

echo BROWSER: %BROWSER%
echo SELENIUM SERVER: %SELENIUM_SERVER%
echo SELENIUM PATH: %SELENIUM_PATH%
echo SCREENSHOTS_PATH PATH: %SCREENSHOTS_PATH%


IF %BROWSER%==chrome (
	start %SELENIUM_PATH%\start_selenium_chrome.bat
	)
IF %BROWSER%==firefox (
	start %SELENIUM_PATH%\start_selenium_firefox.bat
	)
IF %BROWSER%==marionette (
	start %SELENIUM_PATH%\start_selenium_marionette.bat
	)
IF %BROWSER%==phantomjs (
	start %SELENIUM_PATH%\start_selenium_phantomjs.bat
	)

call phpunit -c %~dp0..\..\phpunit.xml

echo "Stopping Selenium..."
start http://%SERVER%/selenium-server/driver/?cmd=shutDownSeleniumServer