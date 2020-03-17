# JPCANADAVANJOB
- Twitter bot for Japanese people who lives in Vancouver
- Get info from this site and tweet for 3 topics ('Job', 'Rent' and  'Buy&Sell')
https://www.jpcanada.com/

## (What is JPCANADA?)
- JPCANADA is the website that offers bunch of information for Japanese people who lives in Canada

## Why I created this
- The website jpcanada didn't have twitter account and RSS
- To help japanese people in Vancouver, especially young folks
- To try scraping and using Twitter API

## Structure of this application
![jpcanada_structure](https://user-images.githubusercontent.com/15808541/76873672-4f355880-682b-11ea-8274-8e6ae4fadca7.png)

#### 1, Get data from https://www.jpcanada.com/
- Scraping each records from each board('Job', 'Rent' and  'Buy&Sell')
- Using the library 'phpQuery' to extract data from html file
- This is run by 'heroku scheduler' which you can use as cron. All the batch jobs can be controlled by visual monitor in heroku panel
- (3 times access in an hour) * (3 boards) = 9 access / hour
#### 2, Store them to DB
- Using heroku postgres addon
#### 3, Tweet the fetched data
- Using Twitter API
- Checking if the data is not tweeted yet when this batch process starts
- After tweet, update tweeted data as tweeted

## Technologies used:
- PHP 7.3
- Laravel 5.5
- PHP libraries
    - phpQuery
    - Twitter SDK
- heroku
- heroku add ons
    - heroku Scheduler
    - heroku Postgres

## Twitter Accounts
#### Job
https://twitter.com/jpcanadavanjob

#### Buy & Sell
https://twitter.com/jpcanadavanbs

#### Rent
https://twitter.com/jpcanadavanhs
