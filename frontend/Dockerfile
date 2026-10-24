# Multi-stage build for the React homepage (serve behind nginx or any CDN).
FROM node:20-alpine AS build
WORKDIR /app
COPY frontend/package*.json ./
RUN npm ci --no-audit --no-fund
COPY frontend ./
ARG VITE_WP_URL=http://localhost:8080
ENV VITE_WP_URL=$VITE_WP_URL
RUN npm run build

FROM nginx:1.27-alpine
COPY --from=build /app/dist /usr/share/nginx/html
COPY docker/nginx.conf /etc/nginx/conf.d/default.conf
EXPOSE 80
