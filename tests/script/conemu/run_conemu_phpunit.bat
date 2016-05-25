SET CONEMU_EXE="%MY_APPS%\ConEmuPack.150813g\ConEmu64.exe"

REM start selenium
start C:\Users\Matteo\Desktop\selenium\start_selenium_chrome.bat

REM cmd /k phpunit -c %~dp0/phpunit

"%CONEMU_EXE%" /cmd %~dp0..\setenv.bat
"%CONEMU_EXE%" /cmd phpunit -c %~dp0..\..\..\phpunit.xml

REM stop selenium
start http://localhost:4444/selenium-server/driver/?cmd=shutDownSeleniumServer
