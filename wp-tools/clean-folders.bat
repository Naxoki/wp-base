@echo off
setlocal EnableDelayedExpansion

echo WP Clean Empty
echo -----------------------------------------------------
echo Eliminando carpetas vacias... (Ignorando las con ".")
echo -----------------------------------------------------
echo.

set "found=0"

call :CleanFolder "%~dp0"

if "%found%"=="0" echo Sin carpetas por eliminar.

echo.
echo -----------------------------------------------------
echo Proceso finalizado.
echo -----------------------------------------------------
pause
exit /b


:CleanFolder
set "current=%~1"

for /D %%D in ("%current%\*") do (
    set "folder=%%~nxD"

    rem Si la carpeta comienza con ".", no entrar ni eliminar
    if not "!folder:~0,1!"=="." (
        call :CleanFolder "%%~fD"

        rem Intentar eliminar solo despues de procesar sus subcarpetas
        rd "%%~fD" 2>nul

        if not exist "%%~fD" (
            echo Eliminada: %%~fD
            set "found=1"
        )
    )
)

exit /b