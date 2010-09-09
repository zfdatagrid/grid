@ECHO OFF
phpcs -n -s --standard="%~dp0\Bvb" %*
echo "done."
