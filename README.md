TMDbAPIPHP
=======

PHP library for retrieving movie or tv show from TMDb API.<br>
For this to work you need a tmdb API bearer token (not api key!) so you have to create a account to get it<br>

All info is in the wiki pages<br>
https://github.com/duck7000/TMDbAPIPHP/wiki


Quick Start
===========

* Clone this repo or download the latest [release zip]
* Find a film you want the data for e.g. 1408 https://www.themoviedb.org/movie/3021-1408
* Include `bootstrap.php`.
* Get some data

Search For Title, person, tv, keyword, company and collection
```php
$Tmdb = new \Tmdb\Search();
$results = $Tmdb->textSearch("1408");
```
For externalId search:
```php
$Tmdb = new \Tmdb\Search();
$results = $Tmdb->externalIdSearch("tt0450385"); // tt and nm are supported
```

For Movie:
```php
$title = new \Imdb\Movie("3021");
$results = $title->fetchMovieData();
```
For Person:
```php
$person = new \Imdb\Person("3036");
$results = $person->fetchPersonData();
```

For Tv series:
```php
$tv = new \Imdb\Tv("66788");
$results = $tv->fetchTvData();
```

Installation
============

Download the latest version or latest git version and extract it to your webserver. Use one of the above methods to get some results

Get the files with one of:
* Git clone. Checkout the latest release tag
* [Zip/Tar download]

### Requirements
* PHP >= works from 8.0 - 8.4
* PHP cURL extension
* PHP json extension

