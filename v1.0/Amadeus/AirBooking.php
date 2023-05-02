<?php

$curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => 'https://test.api.amadeus.com/v1/booking/flight-orders',
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => '',
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => 'POST',
  CURLOPT_POSTFIELDS =>'{
  "data": {
    "type": "flight-order",
    "flightOffers": [
        {
            "type": "flight-offer",
            "id": "1",
            "source": "GDS",
            "instantTicketingRequired": false,
            "nonHomogeneous": false,
            "oneWay": false,
            "lastTicketingDate": "2022-11-01",
            "numberOfBookableSeats": 9,
            "itineraries": [
                {
                    "duration": "PT1H20M",
                    "segments": [
                        {
                            "departure": {
                                "iataCode": "LHR",
                                "terminal": "4",
                                "at": "2022-11-01T06:25:00"
                            },
                            "arrival": {
                                "iataCode": "CDG",
                                "terminal": "2E",
                                "at": "2022-11-01T08:45:00"
                            },
                            "carrierCode": "AF",
                            "number": "1381",
                            "aircraft": {
                                "code": "320"
                            },
                            "operating": {
                                "carrierCode": "AF"
                            },
                            "duration": "PT1H20M",
                            "id": "1",
                            "numberOfStops": 0,
                            "blacklistedInEU": false
                        }
                    ]
                },
                {
                    "duration": "PT1H20M",
                    "segments": [
                        {
                            "departure": {
                                "iataCode": "CDG",
                                "terminal": "2E",
                                "at": "2022-11-05T18:00:00"
                            },
                            "arrival": {
                                "iataCode": "LHR",
                                "terminal": "4",
                                "at": "2022-11-05T18:20:00"
                            },
                            "carrierCode": "AF",
                            "number": "1180",
                            "aircraft": {
                                "code": "319"
                            },
                            "operating": {
                                "carrierCode": "AF"
                            },
                            "duration": "PT1H20M",
                            "id": "6",
                            "numberOfStops": 0,
                            "blacklistedInEU": false
                        }
                    ]
                }
            ],
            "price": {
                "currency": "EUR",
                "total": "255.30",
                "base": "48.00",
                "fees": [
                    {
                        "amount": "0.00",
                        "type": "SUPPLIER"
                    },
                    {
                        "amount": "0.00",
                        "type": "TICKETING"
                    }
                ],
                "grandTotal": "255.30",
                "additionalServices": [
                    {
                        "amount": "50.00",
                        "type": "CHECKED_BAGS"
                    }
                ]
            },
            "pricingOptions": {
                "fareType": [
                    "PUBLISHED"
                ],
                "includedCheckedBagsOnly": false
            },
            "validatingAirlineCodes": [
                "AF"
            ],
            "travelerPricings": [
                {
                    "travelerId": "1",
                    "fareOption": "STANDARD",
                    "travelerType": "ADULT",
                    "price": {
                        "currency": "EUR",
                        "total": "127.65",
                        "base": "24.00"
                    },
                    "fareDetailsBySegment": [
                        {
                            "segmentId": "1",
                            "cabin": "ECONOMY",
                            "fareBasis": "GS50OALG",
                            "brandedFare": "LIGHT2",
                            "class": "G",
                            "includedCheckedBags": {
                                "quantity": 0
                            }
                        },
                        {
                            "segmentId": "6",
                            "cabin": "ECONOMY",
                            "fareBasis": "GS50OALG",
                            "brandedFare": "LIGHT2",
                            "class": "G",
                            "includedCheckedBags": {
                                "quantity": 0
                            }
                        }
                    ]
                },
                {
                    "travelerId": "2",
                    "fareOption": "STANDARD",
                    "travelerType": "ADULT",
                    "price": {
                        "currency": "EUR",
                        "total": "127.65",
                        "base": "24.00"
                    },
                    "fareDetailsBySegment": [
                        {
                            "segmentId": "1",
                            "cabin": "ECONOMY",
                            "fareBasis": "GS50OALG",
                            "brandedFare": "LIGHT2",
                            "class": "G",
                            "includedCheckedBags": {
                                "quantity": 0
                            }
                        },
                        {
                            "segmentId": "6",
                            "cabin": "ECONOMY",
                            "fareBasis": "GS50OALG",
                            "brandedFare": "LIGHT2",
                            "class": "G",
                            "includedCheckedBags": {
                                "quantity": 0
                            }
                        }
                    ]
                }
            ]
        }
        ],
    "travelers": [
      {
        "id": "1",
        "dateOfBirth": "1982-01-16",
        "name": {
          "firstName": "JORGE",
          "lastName": "GONZALES"
        },
        "gender": "MALE",
        "contact": {
          "emailAddress": "jorge.gonzales833@telefonica.es",
          "phones": [
            {
              "deviceType": "MOBILE",
              "countryCallingCode": "34",
              "number": "480080076"
            }
          ]
        },
        "documents": [
          {
            "documentType": "PASSPORT",
            "birthPlace": "Madrid",
            "issuanceLocation": "Madrid",
            "issuanceDate": "2015-04-14",
            "number": "00000000",
            "expiryDate": "2025-04-14",
            "issuanceCountry": "ES",
            "validityCountry": "ES",
            "nationality": "ES",
            "holder": true
          }
        ]
      },
      {
        "id": "2",
        "dateOfBirth": "2012-10-11",
        "gender": "FEMALE",
        "contact": {
          "emailAddress": "jorge.gonzales833@telefonica.es",
          "phones": [
            {
              "deviceType": "MOBILE",
              "countryCallingCode": "34",
              "number": "480080076"
            }
          ]
        },
        "name": {
          "firstName": "ADRIANA",
          "lastName": "GONZALES"
        }
      }
    ],
    "remarks": {
      "general": [
        {
          "subType": "GENERAL_MISCELLANEOUS",
          "text": "ONLINE BOOKING FROM INCREIBLE VIAJES"
        }
      ]
    },
    "ticketingAgreement": {
      "option": "DELAY_TO_CANCEL",
      "delay": "6D"
    },
    "contacts": [
      {
        "addresseeName": {
          "firstName": "PABLO",
          "lastName": "RODRIGUEZ"
        },
        "companyName": "INCREIBLE VIAJES",
        "purpose": "STANDARD",
        "phones": [
          {
            "deviceType": "LANDLINE",
            "countryCallingCode": "34",
            "number": "480080071"
          },
          {
            "deviceType": "MOBILE",
            "countryCallingCode": "33",
            "number": "480080072"
          }
        ],
        "emailAddress": "support@increibleviajes.es",
        "address": {
          "lines": [
            "Calle Prado, 16"
          ],
          "postalCode": "28014",
          "cityName": "Madrid",
          "countryCode": "ES"
        }
      }
    ]
  }
}',
  CURLOPT_HTTPHEADER => array(
    'Content-Type: application/json',
    'Authorization: Bearer uWUq4wko8GJztoxOu0323JDxBp6f'
  ),
));

$response = curl_exec($curl);

curl_close($curl);
echo $response;
