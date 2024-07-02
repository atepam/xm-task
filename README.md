# Stock Price Aggregator App

## Install

### Create .env file

Make a copy of `.env.example` if you do not have a `.env` file!

Optionally get an API key for AlphaVantage and add it to `.env` to `ALPHA_VANTAGE_API_KEY`.
The default key is `demo`.

### Start the app

```
cd <your-project-folder>
./vendor/bin/sail up -d
./vendor/bin/sail artisan migrate:fresh --seed
```

### Migration and User Seeding
In your project folder run
```
./vendor/bin/sail artisan migrate:fresh --seed
```
The seeder will write the API token to the standard output!

If you want to run tests you may need the testing DB so run migrations for it by
```
./vendor/bin/sail artisan migrate:fresh --env=testing
```

### Check if the app is running

Go to http://localhost/ in your browser. You should see the home screen of the app.


## Run Queue Worker and Scheduler

```
./vendor/bin/sail artisan schedule:work
./vendor/bin/sail artisan queue:work
```

## Trigger Stock Price Checker Command Manually 

You can run the 
```
./vendor/bin/sail artisan app:refresh-latest-prices
```
command to trigger the process immediately.

# Service Endpoints
Run `./vendor/bin/sail artisan route:list --path=api` to list API routes.

## Get Latest Prices

```curl -vvv --location 'http://localhost:80/api/latest-prices' --header 'Content-Type: application/json' --header 'Authorization: Bearer REPLACE-THIS-WITH-API-TOKEN'```

The `REPLACE-THIS-WITH-API-TOKEN` part must be replaced with the API token that the DB seeder writes on stdout.

## Get Latest Prices Report
```curl -vvv --location 'http://localhost:80/api/latest-prices/report' --header 'Content-Type: application/json' --header 'Authorization: Bearer REPLACE-THIS-WITH-API-TOKEN'```

The `REPLACE-THIS-WITH-API-TOKEN` part must be replaced with the API token that the DB seeder writes on stdout.


# Developer notes

## Application Architecture

Conceptually latest prices and reports are served from cached data. 

The `app:refresh-latest-prices` command (scheduled for every minute) enqueues a `GetLatestPricesJob` job for each ticker that is configured in the `LATEST_PRICES_SYMBOLS` `.env` variable.

The `GetLatestPricesJob` job fetches the AlphaVantage API's [latest price endpoint](https://www.alphavantage.co/documentation/#latestprice) and returns a new instance of ticker latest price data and the job persists it.

The `LatestPriceCandle` model is observed by the `LatestPriceCandleObserver` that listens to `created`, `updated` and `deleted` model events and handles cached data accordingly.


## DB Schema

This simple schema is based on that the app has a caching mechanism so the DB is
queried only when
- inserting new prices (on 1 minute frequency)
- cache expires or gets cleared (selects)

I did not find the explicit info on currency in the doc, so I supposed
the [latest price endpoint](https://www.alphavantage.co/documentation/#latestprice) provides price data in USD and 
no need for additional data about it so the schema does not have a currency field.


# Error Handling / Monitoring / Alerting
The API has a tight rate limit so the app reaches it very soon. In this case a `RateLimitException` is thrown that is logged on `CRITICAL` level.
The monitoring/alerting system should be very sensitive for it in production.

# Fake API service
To prevent slow responses and fast rate limit issues I have added a fake alpha vantage api server (`tests/alpha_vantage_test_server.php`) to the app that has
responses for normal and ratelimited cases. This server is configured for tests (`ALPHA_VANTAGE_API_HOST`). 


# Possible DB Schema Improvements

The specification does not mention anything about data retention, number of handled symbols, symbols 
in other currencies or in other intervals (1|3|5|15|30mins etc).

If the app in the future needs to handle various aggregation levels and/or intervals and/or keep data for long time 
it is considerable to improve the schema.

**Ideas**
- partitioning the data by symbol and date
- separating/partitioning data by symbol and time interval and/or currencies

We may store symbols, currencies, intervals in separated tables and use relational schema for prices.

## TODOs
- add a scheduled background job that maintains the `candles` table by deleting records older than X hours. I would not keep current prices for long. 

## AlphaVantage API

### Currency and TimeZone

The application is configured to UTC timezone so `candles` table contains `time` in UTC.

Additional info added to field and table level comments in migration.

### apiKey - rate limit - param order - case sensitiveness

It seems the rate limit is by IP.
Until we are within the limit teh apiKey is not checked: it works with random string

It seems when apiKey is `demo` the query param order is checked (with other key it is not checked)
It seems when apiKey is `demo` the param value case is checked (symbol=IBM vs symbol=ibm)

