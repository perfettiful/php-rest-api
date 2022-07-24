# Secure your PHP Rest API with Magic!

This is a simple [PHP REST API](https://github.com/shahbaz17/php-rest-api) protected with [Magic](https://magic.link).

We will be using [Magic Admin PHP SDK](https://github.com/magiclabs/magic-admin-php) in this sample code to protect the PHP REST API.

The Magic Admin PHP SDK provides convenient ways for developers to interact with Magic API endpoints and an array of utilities to handle [DID Token](https://docs.magic.link/decentralized-id).

Please read https://dev.to/shahbaz17/secure-your-php-rest-api-with-magic-82k to learn more about securing the PHP REST API with Magic.

### Prerequisites

- [PHP 5.6 and above](https://www.php.net/downloads.php)
- [MySQL](https://www.mysql.com/downloads/)
- [Composer](http://getcomposer.org/)
- [Postman](https://www.postman.com/downloads/)

## Getting Started

Clone this project with the following commands:

```bash
git clone https://github.com/shahbaz17/magic-php-rest-api.git
cd magic-php-rest-api
```

### Configure the application

Create the database and user for the project.

```php
mysql -u root -p
CREATE DATABASE blog CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'rest_api_user'@'localhost' identified by 'rest_api_password';
GRANT ALL on blog.* to 'rest_api_user'@'localhost';
quit
```

Create the `post` table.

```php
mysql -u rest_api_user -p;
// Enter your password
use blog;

CREATE TABLE `post` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `body` text NOT NULL,
  `author` varchar(255),
  `author_picture` varchar(255),
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
);
```

Copy `.env.example` to `.env` file and enter your database details.

```bash
cp .env.example .env
```

`.env`

```php
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=blog
DB_USERNAME=rest_api_user
DB_PASSWORD=rest_api_password
```

### Get your Magic Secret Key

Try Magic for free [here](https://magic.link/invite/r/kpD9rMJFZGPqVvx1) and get your **`MAGIC_SECRET_KEY`**.

Feel free to use the Test Application automatically configured for you, or create a new one from your [Dashboard](https://dashboard.magic.link).

![Magic Dashboard](https://dev-to-uploads.s3.amazonaws.com/uploads/articles/yiehdmd2kpokpeom3rqh.png)

`.env` complete

```php
MAGIC_SECRET_KEY=sk_test_01234567890 // Paste SECRET KEY
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=blog
DB_USERNAME=rest_api_user
DB_PASSWORD=rest_api_password
```

## Development

### Server (PHP APIs)

Install the project dependencies and start the PHP server:

```bash
composer install
```

```bash
php -S localhost:8000 -t api
```

### Frontend (To get DID Token)

To get the DID Token, please follow https://github.com/shahbaz17/magic-didtoken.

Or simply deploy your own on Vercel or Netlfiy.
[![Deploy with Vercel](https://vercel.com/button)](https://vercel.com/new/git/external?repository-url=https%3A%2F%2Fgithub.com%2Fshahbaz17%2Fmagic-didtoken&env=NEXT_PUBLIC_MAGIC_PUBLISHABLE_KEY,MAGIC_SECRET_KEY) [![Deploy with Netlify](https://www.netlify.com/img/deploy/button.svg)](https://app.netlify.com/start/deploy?repository=https://github.com/shahbaz17/magic-didtoken)

Frontend would look like https://magic-didtoken.vercel.app

DID Token received will be used in Postman to access the protected API endpoints.

## Your APIs

| API               |                                Description |
| :---------------- | -----------------------------------------: |
| GET /posts        |            Get the Posts from `post` table |
| GET /post/{id}    |        Get a single Post from `post` table |
| POST /post        | Create a Post and insert into `post` table |
| PUT /post/{id}    |            Update the Post in `post` table |
| DELETE /post/{id} |            Delete a Post from `post` table |

Test the API endpoints using [Postman](https://www.postman.com/)

## License

See [License.](./LICENSE)
