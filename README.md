# Task runners for PHP (Talk)

Walk-through the most used task runners in PHP at the moment:

+ [Phing](https://www.phing.info/)
+ [Robo](http://robo.li/)

## Slides

You can find the slides to this presentation in [Slideshare](https://www.slideshare.net/nass600/php-task-runners).
I hope you find them useful! ;)

## Usage

### Phing

If phing is installed `globally`:

````bash
cd path/to/this/project
phing setup:install
````

If phing will be installed `locally`:

````bash
cd path/to/this/project
composer install
vendor/bin/phing setup:install
````


### Robo

If robo is installed `globally`:

````bash
cd path/to/this/project
robo setup:install
````

If robo will be installed `locally`:

````bash
cd path/to/this/project
composer install
vendor/bin/robo setup:install
````

## Author

[Ignacio Velazquez](http://ignaciovelazquez.es)
