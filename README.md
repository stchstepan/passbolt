# Passbolt
A custom containerized packaging of the passbolt password manager.

## Installation

### To install Passbolt

1. Download the contents of the repository.
2. In scripts/generate_key.sh, change `Name-Real: First Last` and `Name-Email: your_mail@your.org` to your values.
3. In scripts/setup.sh, in the `gpg --armor --export-secret-keys your_mail@your.org > /var/www/passbolt/config/gpg/serverkey_private.asc` line, change `your_mail@your.org`.
4. In ssl/certs/cert-.your-org-name.key, put the key to be used by Nginx.
5. In ssl/certs/cert-.your-org-name.crt, place the certificate to be used by Nginx.
6. In ssl/nginx.conf, in the line `server_name YOUR_SERVER;` change `YOUR_SERVER` to the name of your server.
7. If you made changes to the name of the certificate or key, in the lines:
   ```
   ssl_certificate /ssl/cert-.your-org-name.crt;
   ssl_certificate_key /ssl/cert-.your-org-name.key;
   ```
   Change `/ssl/cert-.your-org-name.crt;` and `/ssl/cert-.your-org-name.key;`.
8. In .env, change the following values to the desired values:
```
PASSBOLT_ADMIN_PASSWORD=your_password
MYSQL_TCP_PORT=your_port
MYSQL_IP=x.x.x.x.x:your_port:your_port/tcp
APP_IP=x.x.x.x.x:your_port:your_port/tcp
```
9. In passbolt.php, make changes to the following configurations according to the values from .env:
```
'App' => [
    ...
    'fullBaseUrl' => 'https://your_url.com',
    ...
],
// Database configuration.
'Datasources' => [
    'default' => [
        'host' => 'db',
        'port' => 'your_port',
        'username' => 'passbolt',
        'password' => 'your_password',
        'database' => 'passbolt',
    ],
],
// Email configuration.
'EmailTransport' => [
    'default' => [
        'host' => 'localhost',
        'port' => 25, // or another
        'username' => 'your_user',
        'password' => 'your_password',
        // Is this a secure connection? true if yes, null if no.
        'tls' => null,
        //'timeout' => 30,
        //'client' => null,
        //'url' => null,
    ],
],
'Email' => [
    'default' => [
        // Defines the default name and email of the sender of the emails.
        'from' => ['passbolt@your.org' => 'Passbolt']]
        //'charset' => 'utf-8',
        //'headerCharset' => 'utf-8',
    ],
],
```
10. Execute the `docker build` command. Make changes to the Dockerfile beforehand if necessary.
11. Make the appropriate changes to docker-compose.yml.
12. Run the `docker-compose up -d` command.

### Configuration

On first startup, you must create an administrator account, to do this:
1. Access the container with the application (`docker exec -it <container_id> bash`).
2. Execute the command `/var/www/passbolt/bin/cake passbolt register_user -u your_mail@your.org -f First -l Last -r admin`.
3. Continue with the registration as instructed.

## Support

- Check connection to the mail server: `/var/www/passbolt/bin/cake passbolt send_test_email --recipient=your_mail@your.org`.
- Connecting to the database (you can often find logs in the database that are obviously nowhere to be found): `mysql -hdb -P<your_port> -upassbolt -p<your_password>`.
- You can use any other version of Passbolt by replacing the passbolt directory.
