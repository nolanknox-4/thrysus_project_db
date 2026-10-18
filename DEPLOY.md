# Putting the Thrysus Project online

What you need: a small Linux server (a "VPS", ~$5/month: Hetzner, DigitalOcean, Linode…)
and a domain name. Everything else is in this folder.

## 1. Point the domain at the server
At your domain registrar, add an **A record** for your domain (e.g. `thrysus.example.com`)
pointing to the server's IP address. It can take a few minutes to an hour to take effect.

## 2. Install Docker on the server
Log in to the server (`ssh root@your-server-ip`), then:
```
curl -fsSL https://get.docker.com | sh
```

## 3. Get the code onto the server
```
git clone https://github.com/nolanknox-4/thrysus_project_db.git
cd thrysus_project_db
```

## 4. Create the .env file
```
cp .env.example .env
nano .env
```
Fill in:
- `SITE_DOMAIN` — your domain, e.g. `thrysus.example.com`
- `DB_USER`, `DB_PASSWORD`, `DB_ROOT_PASSWORD` — make up two long random passwords
- `ADMIN_PASSWORD_HASH` — your admin password, hashed. On your own computer (with the local
  site running) run:
  `docker compose exec web php -r "echo password_hash('your-password', PASSWORD_DEFAULT);"`
  and paste the result inside **single quotes**.

## 5. Start it
```
docker compose -f docker-compose.prod.yml up -d --build
```
Visit `https://your-domain`. The first load can take ~30 seconds while Caddy gets the certificate.

## 6. Bring your content over
A fresh server starts with only the seed data. To copy your local database and images up:

On your own computer:
```
docker compose exec -T db mariadb-dump -uroot -proot thrysus_project_db > my-content.sql
```
Copy `my-content.sql` and your `images/` folder to the server (e.g. with `scp`), then on the server:
```
docker compose -f docker-compose.prod.yml exec -T db sh -c 'mariadb -uroot -p"$MYSQL_ROOT_PASSWORD" thrysus_project_db' < my-content.sql
docker compose -f docker-compose.prod.yml cp images/. web:/var/www/html/images/
```

## Updating the site later
```
git pull
docker compose -f docker-compose.prod.yml up -d --build
```
Your database and uploaded images are kept: they live in Docker volumes, not in the code.
Images you add by hand (like `biome-map.jpg`) go in with the `cp` command above.

## Backups
```
sh ops/backup.sh
```
Saves the database and all images into `backups/<date>/`, keeping the newest 14.
The comment at the top of `ops/backup.sh` shows how to run it every night automatically.
Copy the `backups/` folder off the server now and then: a backup on the same machine
doesn't help if the machine dies.
