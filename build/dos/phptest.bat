@echo off
echo:
echo:
echo Setting enviroments...
call %~dp0config.bat
echo:
echo BROWSER: %BROWSER%
echo SELENIUM SERVER: %SELENIUM_SERVER%
echo SELENIUM PATH: %SELENIUM_PATH%
echo SCREENSHOTS_PATH PATH: %SCREENSHOTS_PATH%
echo:
IF %BROWSER%==chrome (
	echo starting %SELENIUM_PATH%\start_selenium_chrome.bat ...
	start "" "%SELENIUM_PATH%\start_selenium_chrome.bat"
	)
IF %BROWSER%==firefox (
	echo Calling start %SELENIUM_PATH%\start_selenium_firefox.bat ...
	start "" "%SELENIUM_PATH%\start_selenium_firefox.bat"
	)
IF %BROWSER%==marionette (
	echo Calling start %SELENIUM_PATH%\start_selenium_marionette.bat ...
	start "" "%SELENIUM_PATH%\start_selenium_marionette.bat"
	)
IF %BROWSER%==phantomjs (
	echo Calling start %SELENIUM_PATH%\start_selenium_phantomjs.bat ...
	start "" "%SELENIUM_PATH%\start_selenium_phantomjs.bat"
	)
echo:
echo Starting phpunit ...
call phpunit -c  "%~dp0..\..\phpunit.xml"
echo:
echo Stopping Selenium ...
start "" "http://%SELENIUM_SERVER%/selenium-server/driver/?cmd=shutDownSeleniumServer"
echo:
pause