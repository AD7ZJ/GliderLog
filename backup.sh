#!/bin/bash
tar -zcvf "logging-$(date '+%y-%m-%d').tar.gz" myDatabase.sqlite > /dev/null 2>&1
