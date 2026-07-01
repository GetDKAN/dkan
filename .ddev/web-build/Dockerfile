#ddev-generated
ARG BASE_IMAGE
FROM $BASE_IMAGE

RUN apt-get update \
  && apt-get install -y php${DDEV_PHP_VERSION}-pcov \
  && apt-get clean \
  && rm -rf /var/lib/apt/lists/*
