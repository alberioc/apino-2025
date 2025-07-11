@echo off
echo Adicionando arquivos ao Git...
git add .
echo Fazendo commit...
set /p msg="Digite a mensagem do commit: "
git commit -m "%msg%"
echo Enviando para o GitHub...
git push
echo Tudo pronto!
pause
