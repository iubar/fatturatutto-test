SET CONEMU_EXE="%MY_APPS%\ConEmuPack.150813g\ConEmu64.exe"

REM start selenium
start C:\Users\Matteo\Desktop\selenium\start_selenium_all.bat

REM cmd /k phpunit -c %~dp0/phpunit_chrome.xml
REM cmd /k phpunit -c %~dp0/phpunit_marionette.xml

"%CONEMU_EXE%" /cmd phpunit -c %~dp0/phpunit_chrome.xml
"%CONEMU_EXE%" /cmd phpunit -c %~dp0/phpunit_marionette.xml

start C:\Users\Matteo\Desktop\selenium\start_phantomjs.bat
"%CONEMU_EXE%" /cmd phpunit -c %~dp0/phpunit_phantomjs.xml

REM stop selenium
start http://localhost:4444/selenium-server/driver/?cmd=shutDownSeleniumServer
