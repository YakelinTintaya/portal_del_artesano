@echo off
title Portal del Artesano - Despliegue a Produccion
cd /d "%~dp0"
python deploy.py
echo.
pause
