#!/bin/bash
home=/home/yangrenqiang/dumpjson/
homejson=/home/yangrenqiang/dumpjson/json/
DATE=$(date +%Y年%m月%d日)
echo "开始dump 公众电台"
echo "$(date +%Y年%m月%d日%H时%M分%S秒)开始dump 公众电台" >> ${homejson}$DATE.log
#开始dump genre
echo "开始dump genre表"
echo "$(date +%Y年%m月%d日%H时%M分%S秒)开始dump genre表" >> ${homejson}$DATE.log
/usr/local/php/bin/php ${home}index.php Index/genre
echo "dump genre表完成"
echo "$(date +%Y年%m月%d日%H时%M分%S秒)dump genre表完成" >> ${homejson}$DATE.log
#开始dump mood
echo "开始dump mood表"
echo "$(date +%Y年%m月%d日%H时%M分%S秒)开始dump mood表" >> ${homejson}$DATE.log
/usr/local/php/bin/php ${home}index.php Index/mood
echo "dump mood表完成"
echo "$(date +%Y年%m月%d日%H时%M分%S秒)dump mood表完成" >> ${homejson}$DATE.log
#开始dump style
echo "开始dump style表"
echo "$(date +%Y年%m月%d日%H时%M分%S秒)开始dump style表" >> ${homejson}$DATE.log
/usr/local/php/bin/php ${home}index.php Index/style
echo "dump style表完成"
echo "$(date +%Y年%m月%d日%H时%M分%S秒)dump style表完成" >> ${homejson}$DATE.log
#开始dump theme
echo "开始dump theme表"
echo "$(date +%Y年%m月%d日%H时%M分%S秒)开始dump theme表" >> ${homejson}$DATE.log
/usr/local/php/bin/php ${home}index.php Index/theme
echo "dump theme表完成"
echo "$(date +%Y年%m月%d日%H时%M分%S秒)dump theme表完成" >> ${homejson}$DATE.log
echo "dump 公众电台结束"
echo "$(date +%Y年%m月%d日%H时%M分%S秒)dump 公众电台结束" >> ${homejson}$DATE.log
echo "开始导入公众电台数据到elasticsearch中"
echo "$(date +%Y年%m月%d日%H时%M分%S秒)开始导入公众电台数据到elasticsearch中" >> ${homejson}$DATE.log
curl -XPOST 'http://115.29.10.169:9200/moodbox/genre/_bulk?pretty' --data-binary @${homejson}genre.json
curl -XPOST 'http://115.29.10.169:9200/moodbox/mood/_bulk?pretty' --data-binary @${homejson}mood.json
curl -XPOST 'http://115.29.10.169:9200/moodbox/style/_bulk?pretty' --data-binary @${homejson}style.json
curl -XPOST 'http://115.29.10.169:9200/moodbox/theme/_bulk?pretty' --data-binary @${homejson}theme.json
echo "导入公众电台完成"
echo "$(date +%Y年%m月%d日%H时%M分%S秒)导入公众电台完成" >> ${homejson}$DATE.log
#删除/home/yangrenqiang/dumpjson/json/下面所有的json文件
rm -rf ${homejson}genre.json
rm -rf ${homejson}mood.json
rm -rf ${homejson}style.json
rm -rf ${homejson}theme.json
echo "$(date +%Y年%m月%d日%H时%M分%S秒)删除/home/yangrenqiang/dumpjson/json/下面所有的genre mood style theme json文件" >> ${homejson}$DATE.log
exit
