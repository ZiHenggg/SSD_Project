
# 🚀 ICT2216 PHP Web App – Team Setup Guide

Welcome! This guide helps you set up the PHP + MySQL project using Docker on your local machine.

---

## ✅ 1. Clone the Repository

```bash
git clone https://github.com/mogaman/ICT2216_G5.git
cd ICT2216_G5
```

---

## ✅ 2. Understand the Project Structure

```
ICT2216_G5/
├── src/              → PHP source code (index.php, users.php, etc.)
├── db/
│   └── seed.sql      → SQL file that creates and populates the database
├── mysql-data/       → MySQL database files (auto-generated)
├── Dockerfile        → Builds the PHP + Apache environment
├── docker-compose.yml → Runs all containers
└── .gitignore        → Tells Git what to ignore (like mysql-data/)
```

---

## ✅ 3. Start the Application

Run the following command in the root of the project folder:

```bash
docker-compose up --build
```

This will:
- Build the PHP container with necessary extensions
- Start the MySQL database container

---

## ✅ 4. MySQL Access and Seeding Data

To open the MySQL shell:

```bash
docker exec -it mysql-db mysql -u appuser -p
```

Password: `secret`

Then run the following SQL commands:

```sql
USE appdb;

CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100),
  email VARCHAR(100)
);

INSERT INTO users (name, email) VALUES
('Alice', 'alice@example.com'),
('Bob', 'bob@example.com');

SHOW TABLES;
SELECT * FROM users;
```

---

## ✅ 5. Reset the Database

To reset the database and reload `seed.sql`:

```bash
docker-compose down -v
docker-compose up --build
```

---

## ✅ 6. Open the Web App in Browser

Visit the following in your browser:

- [http://localhost:8080](http://localhost:8080)
- [http://localhost:8080/users.php](http://localhost:8080/users.php)

---

## ✅ Troubleshooting

| Problem                               | Solution                                                             |
|---------------------------------------|----------------------------------------------------------------------|
| ❌ `could not find driver` in users.php | Run `docker-compose up --build` to rebuild with MySQL PDO support   |
| ❌ No data in users.php                | Reset DB: `docker-compose down -v && docker-compose up --build`     |
| ❌ Port 8080 already in use            | Change port in `docker-compose.yml` (e.g., `8081:80`)               |
| ❌ Can't access MySQL                  | Make sure Docker is running and container name is `mysql-db`        |

---

## 📝 Notes

Eventually the database will be hosted and accessible via a remote IP address for centralized access.
