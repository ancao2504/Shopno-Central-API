<?php

$curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => 'https://api.worldota.net/api/b2b/v3/hotel/order/booking/finish/',
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => '',
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => 'POST',
  CURLOPT_POSTFIELDS =>'{

    "user": {
        "email": "asdfds@foo.com",
        "comment": "comment",
        "phone": "12312321"
    },
    "partner": {
        "partner_order_id": "asd123",
        "comment": "partner_comment",
        "amount_sell_b2b2c": "10"
    },
    "language": "en",
    "rooms": [
        {
            "guests": [
                {
                    "first_name": "Marty",
                    "last_name": "Quatro"
                },
                {
                    "first_name": "Marta",
                    "last_name": "Quatro"
                }
            ]
        }
    ],
    "upsell_data": [
        {
            "charge_price": {
                "amount": "4.6",
                "currency_code": "EUR"
            },
            "data": {
                "checkout_time": "19:00:00"
            },
            "name": "late_checkout",
            "rule_id": 473,
            "uid": "a3f405af-14ea-4cf0-923d-a2e6047c1ba7"
        },
        {
            "charge_price": {
                "amount": "3.2",
                "currency_code": "EUR"
            },
            "data": {
                "checkout_time": "18:00:00"
            },
            "name": "late_checkout",
            "rule_id": 473,
            "uid": "3350a400-7262-44c9-8c96-3e92959a7344"
        },
        {
            "charge_price": {
                "amount": "2.8",
                "currency_code": "EUR"
            },
            "data": {
                "checkin_time": "11:00:00"
            },
            "name": "early_checkin",
            "rule_id": 493,
            "uid": "5e5e5839-874b-4f12-b43e-02543a1a478b"
        },
        {
            "charge_price": {
                "amount": "3.9",
                "currency_code": "EUR"
            },
            "data": {
                "checkin_time": "09:00:00"
            },
            "name": "early_checkin",
            "rule_id": 493,
            "uid": "b29b5a49-1b81-429f-8bbf-644e9d585481"
        },
        {
            "charge_price": {
                "amount": "3.9",
                "currency_code": "EUR"
            },
            "data": {
                "checkin_time": "08:00:00"
            },
            "name": "early_checkin",
            "rule_id": 493,
            "uid": "f43dd8af-d86f-421c-b2ec-5feddf86e879"
        },
        {
            "charge_price": {
                "amount": "2.8",
                "currency_code": "EUR"
            },
            "data": {
                "checkin_time": "10:00:00"
            },
            "name": "early_checkin",
            "rule_id": 493,
            "uid": "f46a2e15-922c-4bc8-80fb-4c85705dcd8f"
        },
        {
            "charge_price": {
                "amount": "3.2",
                "currency_code": "EUR"
            },
            "data": {
                "checkout_time": "17:00:00"
            },
            "name": "late_checkout",
            "rule_id": 473,
            "uid": "a114bb6b-537a-4a6f-816c-4f5f3c43e465"
        },
        {
            "charge_price": {
                "amount": "4.6",
                "currency_code": "EUR"
            },
            "data": {
                "checkout_time": "20:00:00"
            },
            "name": "late_checkout",
            "rule_id": 473,
            "uid": "796d4761-8a69-4925-ad04-d0772a44050f"
        }
    ],
    "payment_type": {
        "type": "deposit",
        "amount": "4",
        "currency_code": "USD"
    }
}',
  CURLOPT_HTTPHEADER => array(
    'Authorization: Basic NDk0NjowNjk1ZTZjOS1hZDlmLTQxOTUtOTNjMy1mNTY4YTkzMmY1Zjc=',
    'Content-Type: application/json',
  ),
));

$response = curl_exec($curl);

curl_close($curl);
echo $response;