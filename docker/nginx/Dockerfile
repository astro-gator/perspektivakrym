FROM nginx:alpine

ADD conf.d/default.conf /etc/nginx/conf.d/default.conf
ADD nginx.conf /etc/nginx/

ARG PHP_UPSTREAM_CONTAINER=php
ARG PHP_UPSTREAM_PORT=9000
ARG PUID=1006
ARG PGID=1006

# Set user and group
RUN sed -i -e "s@^.*nginx:x:.*@nginx:x:${PGID}:nginx@" /etc/group \
    && sed -i -e "s@^.*nginx:x:.*@nginx:x:${PUID}:${PGID}:nginx:/var/cache/nginx:/sbin/nologin@" /etc/passwd

CMD ["nginx"]

EXPOSE 80