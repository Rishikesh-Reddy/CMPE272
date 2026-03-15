# syntax=docker/dockerfile:1

FROM php:8.3-cli-alpine

RUN adduser -D -H -u 1001 appuser

WORKDIR /var/www/html

COPY --chown=appuser:appuser null-castle/    ./

USER appuser

ENV PORT=8080

EXPOSE 8080

CMD php -S 0.0.0.0:${PORT} -t /var/www/html
