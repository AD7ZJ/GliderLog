#!/bin/bash
tar -zcvf "/home/elijah/soaring_db_backups/logging-$(date '+%y-%m-%d').tar.gz" db.sqlite > /dev/null 2>&1
