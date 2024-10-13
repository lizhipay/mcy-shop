#!/bin/bash

# 检查是否输入了文件编号
if [ $# -eq 0 ]; then
    echo "呵，连个编号都不输入，真是个杂鱼。"
    echo "用法: $0 <文件编号>"
    exit 1
fi

FILE_ID=$1
INPUT_FILE="${FILE_ID}.wav"
OUTPUT_FILE="${FILE_ID}.aac"

# 检查文件是否存在
if [ ! -f "$INPUT_FILE" ]; then
    echo "啊哟，文件${INPUT_FILE}不存在，难道你想让空气转换成AAC？"
    exit 1
fi

# 使用ffmpeg转换
ffmpeg -i "$INPUT_FILE" -c:a aac -b:a 128k -strict -2 "$OUTPUT_FILE"

if [ $? -eq 0 ]; then
    echo "转换成功，${OUTPUT_FILE}已经准备好了。"
else
    echo "转换失败了，是不是又做了什么奇怪的事？"
fi