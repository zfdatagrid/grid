@ECHO OFF
echo "F6: generate documentation"
echo "Shift+F6: CodeSniff actual file in editor"

if "%1" == "public" (
	goto :DOC
) else (
	goto :CS
)

:CS
echo running CodeSniffer on actual file
call %2\library\CodingStyle\CodeSniffer\run.cmd %1
goto :END

:DOC
echo generating documentation for this project

REM configuration of phpDocumentor
SET SRC=%2\library\Bvb,%2\library\My
SET IGNORE=
SET OUTPUT=HTML:frames:phphtmllib
rem SET OUTPUT=HTML:frames:phpedit
rem SET OUTPUT=HTML:Smarty:HandS
SET DSC=%2\public\api

call phpdoc -q -d %SRC% -t %DSC% --ignore %IGNORE% -o %OUTPUT%
echo "Generating finished, open web page file://%DSC%/index.html"
goto :END

:END
echo done.