#!/bin/sh
MYSQL="/phpstudy/mysql/bin/mysql -uroot -pDssz123456 "
sql='use tp;select goods_id,store_count from tp_goods where goods_id < 5;'
result="$($MYSQL -e "$sql")"
echo $result;