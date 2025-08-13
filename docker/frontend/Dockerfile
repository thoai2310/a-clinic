FROM node:22-alpine

WORKDIR /app

RUN npm install -g @vue/cli @vitejs/plugin-vue

COPY fe-s-clinic/package*.json ./

RUN if [ -f "package.json" ]; then npm install; fi

EXPOSE 5173

CMD ["npm", "run", "dev", "--", "--host", "0.0.0.0", "--port", "5173"]