ARG ALPINE_VERSION=3.20
ARG NODE_VERSION=22.13.1
ARG NGINX_VERSION=1.27.3

#############################
## Bundle front-end assets ##
#############################
FROM node:${NODE_VERSION}-alpine${ALPINE_VERSION} AS node

WORKDIR /app

COPY ./package.json ./package-lock.json /app/
COPY ./resources /app/resources
COPY ./*.config.js /app/

RUN npm ci --verbose
RUN npm run build

############################
## Build NGINX container  ##
############################
FROM nginx:${NGINX_VERSION}-alpine${ALPINE_VERSION} AS base

ARG APP_ENV=prod

LABEL maintainer="Nizar El Berjawi <nizarberjawi12@gmail.com>"

WORKDIR /app

RUN echo "UTC" > /etc/timezone

COPY .docker/nginx/nginx.conf /etc/nginx/nginx.conf

#################################
## Build DEVELOPMENT container ##
#################################
FROM base AS development

COPY .docker/nginx/http.d/default.dev.conf /etc/nginx/http.d/default.conf

################################
## Build PRODUCTION container ##
################################
FROM base AS production

COPY .docker/nginx/http.d/default.prod.conf /etc/nginx/http.d/default.conf
COPY --from=node /app/public/build /app/public/build

COPY . /app
