FROM registry.kuznitsa.pro/docker/php

RUN mkdir -p /home/application/.mongodb && \
    wget "https://storage.yandexcloud.net/cloud-certs/CA.pem" -O /home/application/.mongodb/YandexCA.pem && \
    chown -R application. /home/application/.mongodb
