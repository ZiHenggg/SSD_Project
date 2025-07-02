FROM mcr.microsoft.com/playwright:v1.44.0-jammy

WORKDIR /tests

COPY . .

RUN npm install
RUN npx playwright install --with-deps

CMD ["npx", "playwright", "test"]