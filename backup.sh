#!/bin/bash
tar -zcvf "logging-$(date '+%y-%m-%d').tar.gz" db.sqlite > /dev/null 2>&1
