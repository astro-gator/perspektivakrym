FROM redis
COPY redis.conf /usr/local/etc/redis/redis.conf
RUN touch /data/redis-server.log && \
    chown redis:redis /data/redis-server.log;
CMD [ "redis-server", "/usr/local/etc/redis/redis.conf" ]