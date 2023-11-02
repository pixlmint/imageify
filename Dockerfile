FROM christiangroeber/php-server:8.1.2

ARG UID=99
ARG GID=100

RUN apt update && apt install -y mediainfo cron ffmpeg python3

COPY ./ /var/www/html

WORKDIR /var/www/html

RUN composer install

RUN groupadd -o -g ${GID} journal_group
RUN useradd -M -N -u ${UID} -g ${GID} journal_user

RUN cp docker/apache2.conf /etc/apache2/apache2.conf

RUN chown -R journal_user:journal_group .

# Cron
COPY docker/crontab /etc/cron.d/container_crontab
COPY docker/cron_start.sh /root/cron_start.sh
COPY docker/startup.sh /root/startup.sh

RUN chmod +x /root/startup.sh
RUN echo "{\"UID\":\"${UID}\",\"GID\":\"${GID}\"}" > /etc/cron_env.json

CMD ["/bin/bash", "-c", "/root/startup.sh"]