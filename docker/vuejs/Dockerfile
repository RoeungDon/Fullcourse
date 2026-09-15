FROM node:24.12.0-alpine

WORKDIR /app

COPY docker/vuejs/*.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/*.sh

COPY vuejs-app/package.json vuejs-app/package-lock.json* /app/
RUN npm install || true

COPY vuejs-app /app

EXPOSE 5173

CMD ["sh", "/usr/local/bin/entrypoint.development.sh"]
