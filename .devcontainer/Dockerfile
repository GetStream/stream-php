ARG VARIANT=8-bullseye
FROM mcr.microsoft.com/vscode/devcontainers/php:0-${VARIANT}

RUN pecl install ast && \
    echo "extension=ast.so" >> "$PHP_INI_DIR/php.ini-development" && \
    mv "$PHP_INI_DIR/php.ini-development" "$PHP_INI_DIR/php.ini"

ENV PHAN_ALLOW_XDEBUG 0
ENV PHAN_DISABLE_XDEBUG_WARN 1