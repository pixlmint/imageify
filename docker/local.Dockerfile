FROM christiangroeber/php-server:8.1.2

ARG UID
ARG GID

RUN apt update && apt install -y mediainfo cron ffmpeg python3

COPY docker/crontab /etc/cron.d/container_crontab
COPY docker/cron_start.sh /root/cron_start.sh

RUN chmod +x /root/cron_start.sh

RUN echo "{\"UID\":\"${UID}\",\"GID\":\"${GID}\"}" > /etc/cron_env.json

CMD ["/bin/bash", "-c", "/root/cron_start.sh && apache2-foreground"]