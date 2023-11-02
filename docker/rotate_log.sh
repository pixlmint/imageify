#!/bin/bash

count=$(find /var/log/cron/bak | wc -l)
mv /var/log/cron/cron.log "/var/log/cron/cron.bak.${count}.log"
touch /var/log/cron/cron.log