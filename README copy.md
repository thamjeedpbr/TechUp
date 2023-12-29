
#Test

Steps to follow to run this project

1. Create Env file

```bash
  cp .env.example .env
```

2. Generate the key

```bash
  php artisan key:generate
```

3. Install composer  

```bash
  composer install
```

4. Connect the Database

5. Run Migration

```bash
  php artisan migrate
```

6. Command to Collect Coin

```bash
  php artisan db:seed
```

7. Storage public

```bash
  php artisan storage:link
```
