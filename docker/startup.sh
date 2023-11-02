# !/bin/bash
chmod +x /root/cron_start.sh
/root/cron_start.sh

chown -R journal_user:users data backup content media

apache2-foreground