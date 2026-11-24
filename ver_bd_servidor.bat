@echo off
chcp 65001 > nul
title Ver Base de Datos del Servidor - Portal del Artesano
cd /d "%~dp0"
python ver_bd_servidor.py
echo.
pause
