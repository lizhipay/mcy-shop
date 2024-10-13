#!/bin/bash
# 文件路径
CONFIG_FILE="./config/server.php"

NAME=$(grep "'name'" $CONFIG_FILE | awk -F "=> '" '{print $2}' | awk -F "'" '{print $1}')
PORT=$(grep "'port'" $CONFIG_FILE | awk -F "=> " '{print $2}' | awk -F "," '{print $1}')


# 函数：启动服务
start_service() {
  while true; do
    server=$(ps aux | grep ${NAME}.${PORT}.main | grep -v grep)
    if [ ! "$server" ]; then
      ./bin index.php dev
    fi
    sleep 1
  done
}

# 函数：停止服务
stop_service() {
  for pid in $(ps aux | grep ${NAME}.${PORT} | awk '{print $2}'); do
    if kill -0 $pid >/dev/null 2>&1; then
      kill -9 $pid
    fi
  done

  for pid in $(ps aux | grep dev.sh | awk '{print $2}'); do
    if kill -0 $pid >/dev/null 2>&1; then
      kill -9 $pid
    fi
  done
}

# 函数：重启服务
restart_service() {
  for pid in $(ps aux | grep ${NAME}.${PORT} | awk '{print $2}'); do
    if kill -0 $pid >/dev/null 2>&1; then
      kill -9 $pid
    fi
  done
}

case "$1" in
"start")
  start_service
  ;;
"stop")
  stop_service
  ;;
"restart")
  restart_service
  ;;
*)
  echo "Usage: $0 [start|stop|restart]"
  exit 1
  ;;
esac