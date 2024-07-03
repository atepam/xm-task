<?php

$content = '';
if (stristr($_SERVER['PATH_INFO'], 'perfect')) {
    $content = getPerfectContent();

} elseif (stristr($_SERVER['PATH_INFO'], 'rate-limit')) {
    $content = getRateLimit();

} elseif (stristr($_SERVER['PATH_INFO'], 'invalid')) {
    $content = getInvalid();
}

header('Content-Type: application/json');
echo json_encode($content);
exit;

///


function getPerfectContent(): array
{
    $symbol = empty($_GET['symbol'])
        ? 'IBM'
        : $_GET['symbol'];

    $open = getRandom();
    $price = $open + rand(-1, 12);
    return [
        "Global Quote" => [
            "01. symbol" => $symbol,
            "02. open" => $open,
            "03. high" => (string)($open + rand(3, 77)),
            "04. low" => (string)($open - rand(3, 77)),
            "05. price" => (string)$price,
            "06. volume" => (string)rand(222222, 33333333),
            "07. latest trading day" => "2024-06-24",
            "08. previous close" => (string)($open + rand(-10, 20)),
            "09. change" => (string)rand(3, 5),
            "10. change percent" => (string)(rand(22, 3) / 3 . "%")
        ]
    ];
}

function getRandom(): string
{
    return (string)rand(0, 1000) / 1000 + (0 - rand(0, 1000) / 1000) + (100 + rand(0, 200));
}

function getRateLimit(): array
{
    return [
        "Information" => "Thank you for using Alpha Vantage! Our standard API rate limit is 25 requests per day. Please subscribe to any of the premium plans at https://www.alphavantage.co/premium/ to instantly remove all daily rate limits.",
    ];
}

function getInvalid(): array
{
    return [
        "---Global Quote" => [
            "01. symbol" => '',
            "02. open" => '',
            "03. high" => '',
            "04. low" => '',
            "05. price" => '',
            "06. volume" => '',
            "07. latest trading day" => '',
            "08. previous close" => '',
            "09. change" => '',
            "10. change percent" => '',
        ]
    ];
}
