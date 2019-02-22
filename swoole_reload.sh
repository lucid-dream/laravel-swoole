pid=`pidof swoole_lumen`
kill -USR1 $pid
echo $pid reload