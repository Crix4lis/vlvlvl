
> author: Michał Powała <br>
> source repository: [docker-php-cli-xdebug](https://github.com/Crix4lis/docker-php-cli-xdebug)

# How to run:
- Start docker container `docker-compose up -d`
- Run tests `docker-compose exec cli ./vendor/bin/phpunit tests`
- Generate output file: `docker-compose exec cli php run.php`
- output files will be saved within `output_files` directory
- source files are stored within `source_files` directory
