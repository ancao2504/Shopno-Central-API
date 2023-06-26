<?php


header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] == 'POST'){
$_POST = json_decode(file_get_contents('php://input'), true);

$tripType = $_POST['tripType'];
$Segment = isset($_POST['segment']) ? $_POST['segment'] : '';

$RequestArray = array();


if((array_key_exists("adultCount",$_POST)) && (array_key_exists("childCount",$_POST) && array_key_exists("infantCount",$_POST))){

      $adult = $_POST['adultCount'];
      $child = $_POST['childCount'];
      $infants = $_POST['infantCount'];

      $SeatReq = $adult + $child;

    if($adult > 0 && $child> 0 && $infants> 0){
      $paxRequest = '{
                "Code": "ADT",
                "Quantity": '.$adult.'
              },
              {
                "Code": "C09",
                "Quantity": '.$child.'
              },
              {
                "Code": "INF",
                "Quantity": '.$infants.'
              }';
                     

    }else if($adult > 0 && $child > 0){

      $paxRequest = '{
                    "Code": "ADT",
                    "Quantity": '.$adult.'
                  },
                  {
                    "Code": "C09",
                    "Quantity": '.$child.'
                  }';
    }else if($adult > 0 && $infants > 0){
      $paxRequest = '{
                  "Code": "ADT",
                  "Quantity": '.$adult.'
                  },
                  {
                    "Code": "INF",
                    "Quantity": '.$infants.'
                  }';

    }else{
      $paxRequest = '{
                "Code": "ADT",
                "Quantity": '.$adult.'
              }';

    }

}

if($tripType== 1 || $tripType== 'oneway'){
    if($Segment == 1){
        $departure = $_POST['segments'][0]['departure'];
        $arrival = $_POST['segments'][0]['arrival'];
        $dpTime = $_POST['segments'][0]['dpTime'];
        $arrTime = $_POST['segments'][0]['arrTime'];
        $bCode = $_POST['segments'][0]['bCode'];
        $mCarrier = $_POST['segments'][0]['mCarrier'];
        $mCarrierFN = $_POST['segments'][0]['mCarrierFN'];
        $oCarrier = $_POST['segments'][0]['oCarrier'];
        $oCarrierFN = $_POST['segments'][0]['oCarrierFN'];

        $Request ='[{
                    "RPH": "0",
                    "DepartureDateTime": "'.$dpTime.'",
                    "OriginLocation": {
                        "LocationCode": "'.$departure.'"
                    },
                    "DestinationLocation": {
                        "LocationCode": "'.$arrival.'"
                    },
                    "TPA_Extensions": {
                        "SegmentType": {
                            "Code": "O"
                        },
                        "Flight": [
                            {
                                "Number": '.$mCarrierFN.',
                                "DepartureDateTime": "'.$dpTime.'",
                                "ArrivalDateTime": "'.$arrTime.'",
                                "Type": "A",
                                "ClassOfService": "'.$bCode.'",
                                "OriginLocation": {
                                    "LocationCode": "'.$departure.'"
                                },
                                "DestinationLocation": {
                                    "LocationCode": "'.$arrival.'"
                                },
                                "Airline": {
                                    "Operating": "'.$oCarrier.'",
                                    "Marketing": "'.$mCarrier.'"
                                }
                            }
                        ]
                    }
                }]';



    }else if($Segment == 2){

            $departure = $_POST['segments'][0]['departure'];
            $arrival = $_POST['segments'][0]['arrival'];
            $dpTime = $_POST['segments'][0]['dpTime'];
            $arrTime = $_POST['segments'][0]['arrTime'];
            $bCode = $_POST['segments'][0]['bCode'];
            $mCarrier = $_POST['segments'][0]['mCarrier'];
            $mCarrierFN = $_POST['segments'][0]['mCarrierFN'];
            $oCarrier = $_POST['segments'][0]['oCarrier'];
            $oCarrierFN = $_POST['segments'][0]['oCarrierFN'];

            $departure1 = $_POST['segments'][1]['departure'];
            $arrival1 = $_POST['segments'][1]['arrival'];
            $dpTime1 = $_POST['segments'][1]['dpTime'];
            $arrTime1 = $_POST['segments'][1]['arrTime'];
            $bCode1 = $_POST['segments'][1]['bCode'];
            $mCarrier1 = $_POST['segments'][1]['mCarrier'];
            $mCarrierFN1 = $_POST['segments'][1]['mCarrierFN'];
            $oCarrier1 = $_POST['segments'][1]['oCarrier'];
            $oCarrierFN1 = $_POST['segments'][1]['oCarrierFN'];

        $Request ='[{
                    "RPH": "0",
                    "DepartureDateTime": "'.$dpTime.'",
                    "OriginLocation": {
                        "LocationCode": "'.$departure.'"
                    },
                    "DestinationLocation": {
                        "LocationCode": "'.$arrival.'"
                    },
                    "TPA_Extensions": {
                        "SegmentType": {
                            "Code": "O"
                        },
                        "Flight": [
                            {
                                "Number": '.$mCarrierFN.',
                                "DepartureDateTime": "'.$dpTime.'",
                                "ArrivalDateTime": "'.$arrTime.'",
                                "Type": "A",
                                "ClassOfService": "'.$bCode.'",
                                "OriginLocation": {
                                    "LocationCode": "'.$departure.'"
                                },
                                "DestinationLocation": {
                                    "LocationCode": "'.$arrival.'"
                                },
                                "Airline": {
                                    "Operating": "'.$oCarrier.'",
                                    "Marketing": "'.$mCarrier.'"
                                }
                            },
                            {
                                "Number": '.$mCarrierFN1.',
                                "DepartureDateTime": "'.$dpTime1.'",
                                "ArrivalDateTime": "'.$arrTime1.'",
                                "Type": "A",
                                "ClassOfService": "'.$bCode1.'",
                                "OriginLocation": {
                                    "LocationCode": "'.$departure1.'"
                                },
                                "DestinationLocation": {
                                    "LocationCode": "'.$arrival1.'"
                                },
                                "Airline": {
                                    "Operating": "'.$oCarrier1.'",
                                    "Marketing": "'.$mCarrier1.'"
                                }
                            }
                        ]
                    }
                }]';
                
            

    }else if($Segment == 3){

        $departure = $_POST['segments'][0]['departure'];
        $arrival = $_POST['segments'][0]['arrival'];
        $dpTime = $_POST['segments'][0]['dpTime'];
        $arrTime = $_POST['segments'][0]['arrTime'];
        $bCode = $_POST['segments'][0]['bCode'];
        $mCarrier = $_POST['segments'][0]['mCarrier'];
        $mCarrierFN = $_POST['segments'][0]['mCarrierFN'];
        $oCarrier = $_POST['segments'][0]['oCarrier'];
        $oCarrierFN = $_POST['segments'][0]['oCarrierFN'];

        $departure1 = $_POST['segments'][1]['departure'];
        $arrival1 = $_POST['segments'][1]['arrival'];
        $dpTime1 = $_POST['segments'][1]['dpTime'];
        $arrTime1 = $_POST['segments'][1]['arrTime'];
        $bCode1 = $_POST['segments'][1]['bCode'];
        $mCarrier1 = $_POST['segments'][1]['mCarrier'];
        $mCarrierFN1 = $_POST['segments'][1]['mCarrierFN'];
        $oCarrier1 = $_POST['segments'][1]['oCarrier'];
        $oCarrierFN1 = $_POST['segments'][1]['oCarrierFN'];

        $departure2 = $_POST['segments'][2]['departure'];
        $arrival2 = $_POST['segments'][2]['arrival'];
        $dpTime2 = $_POST['segments'][2]['dpTime'];
        $arrTime2 = $_POST['segments'][2]['arrTime'];
        $bCode2 = $_POST['segments'][2]['bCode'];
        $mCarrier2 = $_POST['segments'][2]['mCarrier'];
        $mCarrierFN2 = $_POST['segments'][2]['mCarrierFN'];
        $oCarrier2 = $_POST['segments'][2]['oCarrier'];
        $oCarrierFN2 = $_POST['segments'][2]['oCarrierFN'];

        $Request ='[{
                    "RPH": "0",
                    "DepartureDateTime": "'.$dpTime.'",
                    "OriginLocation": {
                        "LocationCode": "'.$departure.'"
                    },
                    "DestinationLocation": {
                        "LocationCode": "'.$arrival.'"
                    },
                    "TPA_Extensions": {
                        "SegmentType": {
                            "Code": "O"
                        },
                        "Flight": [
                            {
                                "Number": '.$mCarrierFN.',
                                "DepartureDateTime": "'.$dpTime.'",
                                "ArrivalDateTime": "'.$arrTime.'",
                                "Type": "A",
                                "ClassOfService": "'.$bCode.'",
                                "OriginLocation": {
                                    "LocationCode": "'.$departure.'"
                                },
                                "DestinationLocation": {
                                    "LocationCode": "'.$arrival.'"
                                },
                                "Airline": {
                                    "Operating": "'.$oCarrier.'",
                                    "Marketing": "'.$mCarrier.'"
                                }
                            },
                            {
                                "Number": '.$mCarrierFN1.',
                                "DepartureDateTime": "'.$dpTime1.'",
                                "ArrivalDateTime": "'.$arrTime1.'",
                                "Type": "A",
                                "ClassOfService": "'.$bCode1.'",
                                "OriginLocation": {
                                    "LocationCode": "'.$departure1.'"
                                },
                                "DestinationLocation": {
                                    "LocationCode": "'.$arrival1.'"
                                },
                                "Airline": {
                                    "Operating": "'.$oCarrier1.'",
                                    "Marketing": "'.$mCarrier1.'"
                                }
                            },
                            {
                                "Number": '.$mCarrierFN2.',
                                "DepartureDateTime": "'.$dpTime2.'",
                                "ArrivalDateTime": "'.$arrTime2.'",
                                "Type": "A",
                                "ClassOfService": "'.$bCode2.'",
                                "OriginLocation": {
                                    "LocationCode": "'.$departure2.'"
                                },
                                "DestinationLocation": {
                                    "LocationCode": "'.$arrival2.'"
                                },
                                "Airline": {
                                    "Operating": "'.$oCarrier2.'",
                                    "Marketing": "'.$mCarrier2.'"
                                }
                            }
                            
                            
                        ]
                    }
                }]';


    }

}else if($tripType == 2 || $tripType== 'return'){
    if($Segment == 1){
        $godeparture = $_POST['segments']['go'][0]['departure'];
        $goarrival = $_POST['segments']['go'][0]['arrival'];
        $godpTime = $_POST['segments']['go'][0]['dpTime'];
        $goarrTime = $_POST['segments']['go'][0]['arrTime'];
        $gobCode = $_POST['segments']['go'][0]['bCode'];
        $gomCarrier = $_POST['segments']['go'][0]['mCarrier'];
        $gomCarrierFN = $_POST['segments']['go'][0]['mCarrierFN'];
        $gooCarrier = $_POST['segments']['go'][0]['oCarrier'];
        $gooCarrierFN = $_POST['segments']['go'][0]['oCarrierFN'];

        $backdeparture = $_POST['segments']['back'][0]['departure'];
        $backarrival = $_POST['segments']['back'][0]['arrival'];
        $backdpTime = $_POST['segments']['back'][0]['dpTime'];
        $backarrTime = $_POST['segments']['back'][0]['arrTime'];
        $backbCode = $_POST['segments']['back'][0]['bCode'];
        $backmCarrier = $_POST['segments']['back'][0]['mCarrier'];
        $backmCarrierFN = $_POST['segments']['back'][0]['mCarrierFN'];
        $backoCarrier = $_POST['segments']['back'][0]['oCarrier'];
        $backoCarrierFN = $_POST['segments']['back'][0]['oCarrierFN'];

    $Request ='[{
                "RPH": "0",
                "DepartureDateTime": "'.$godpTime.'",
                "OriginLocation": {
                    "LocationCode": "'.$godeparture.'"
                },
                "DestinationLocation": {
                    "LocationCode": "'.$goarrival.'"
                },
                "TPA_Extensions": {
                    "SegmentType": {
                        "Code": "O"
                    },
                    "Flight": [
                        {
                            "Number": '.$gomCarrierFN.',
                            "DepartureDateTime": "'.$godpTime.'",
                            "ArrivalDateTime": "'.$goarrTime.'",
                            "Type": "A",
                            "ClassOfService": "'.$gobCode.'",
                            "OriginLocation": {
                                "LocationCode": "'.$godeparture.'"
                            },
                            "DestinationLocation": {
                                "LocationCode": "'.$goarrival.'"
                            },
                            "Airline": {
                                "Operating": "'.$gooCarrier.'",
                                "Marketing": "'.$gomCarrier.'"
                            }
                        }
                    ]
                }
            },
            {
                "RPH": "1",
                "DepartureDateTime": "'.$backdpTime.'",
                "OriginLocation": {
                    "LocationCode": "'.$backdeparture.'"
                },
                "DestinationLocation": {
                    "LocationCode": "'.$backarrival.'"
                },
                "TPA_Extensions": {
                    "SegmentType": {
                        "Code": "O"
                    },
                    "Flight": [
                        {
                            "Number": '.$backmCarrierFN.',
                            "DepartureDateTime": "'.$backdpTime.'",
                            "ArrivalDateTime": "'.$backarrTime.'",
                            "Type": "A",
                            "ClassOfService": "'.$backbCode.'",
                            "OriginLocation": {
                                "LocationCode": "'.$backdeparture.'"
                            },
                            "DestinationLocation": {
                                "LocationCode": "'.$backarrival.'"
                            },
                            "Airline": {
                                "Operating": "'.$backoCarrier.'",
                                "Marketing": "'.$backmCarrier.'"
                            }
                        }
                    ]
                }
            }]';

    }else if($Segment == 2){
        $godeparture = $_POST['segments']['go'][0]['departure'];
        $goarrival = $_POST['segments']['go'][0]['arrival'];
        $godpTime = $_POST['segments']['go'][0]['dpTime'];
        $goarrTime = $_POST['segments']['go'][0]['arrTime'];
        $gobCode = $_POST['segments']['go'][0]['bCode'];
        $gomCarrier = $_POST['segments']['go'][0]['mCarrier'];
        $gomCarrierFN = $_POST['segments']['go'][0]['mCarrierFN'];
        $gooCarrier = $_POST['segments']['go'][0]['oCarrier'];
        $gooCarrierFN = $_POST['segments']['go'][0]['oCarrierFN'];

        $godeparture1 = $_POST['segments']['go'][1]['departure'];
        $goarrival1 = $_POST['segments']['go'][1]['arrival'];
        $godpTime1 = $_POST['segments']['go'][1]['dpTime'];
        $goarrTime1 = $_POST['segments']['go'][1]['arrTime'];
        $gobCode1 = $_POST['segments']['go'][1]['bCode'];
        $gomCarrier1 = $_POST['segments']['go'][1]['mCarrier'];
        $gomCarrierFN1 = $_POST['segments']['go'][1]['mCarrierFN'];
        $gooCarrier1 = $_POST['segments']['go'][1]['oCarrier'];
        $gooCarrierFN1 = $_POST['segments']['go'][1]['oCarrierFN'];

        $backdeparture = $_POST['segments']['back'][0]['departure'];
        $backarrival = $_POST['segments']['back'][0]['arrival'];
        $backdpTime = $_POST['segments']['back'][0]['dpTime'];
        $backarrTime = $_POST['segments']['back'][0]['arrTime'];
        $backbCode = $_POST['segments']['back'][0]['bCode'];
        $backmCarrier = $_POST['segments']['back'][0]['mCarrier'];
        $backmCarrierFN = $_POST['segments']['back'][0]['mCarrierFN'];
        $backoCarrier = $_POST['segments']['back'][0]['oCarrier'];
        $backoCarrierFN = $_POST['segments']['back'][0]['oCarrierFN'];

        $backdeparture1 = $_POST['segments']['back'][1]['departure'];
        $backarrival1 = $_POST['segments']['back'][1]['arrival'];
        $backdpTime1 = $_POST['segments']['back'][1]['dpTime'];
        $backarrTime1 = $_POST['segments']['back'][1]['arrTime'];
        $backbCode1 = $_POST['segments']['back'][1]['bCode'];
        $backmCarrier1 = $_POST['segments']['back'][1]['mCarrier'];
        $backmCarrierFN1 = $_POST['segments']['back'][1]['mCarrierFN'];
        $backoCarrier1 = $_POST['segments']['back'][1]['oCarrier'];
        $backoCarrierFN1 = $_POST['segments']['back'][1]['oCarrierFN']; 

    $Request ='[{
                "RPH": "0",
                "DepartureDateTime": "'.$godpTime.'",
                "OriginLocation": {
                    "LocationCode": "'.$godeparture.'"
                },
                "DestinationLocation": {
                    "LocationCode": "'.$goarrival.'"
                },
                "TPA_Extensions": {
                    "SegmentType": {
                        "Code": "O"
                    },
                    "Flight": [
                        {
                            "Number": '.$gomCarrierFN.',
                            "DepartureDateTime": "'.$godpTime.'",
                            "ArrivalDateTime": "'.$goarrTime.'",
                            "Type": "A",
                            "ClassOfService": "'.$gobCode.'",
                            "OriginLocation": {
                                "LocationCode": "'.$godeparture.'"
                            },
                            "DestinationLocation": {
                                "LocationCode": "'.$goarrival.'"
                            },
                            "Airline": {
                                "Operating": "'.$gooCarrier.'",
                                "Marketing": "'.$gomCarrier.'"
                            }
                        },
                        {
                            "Number": '.$gomCarrierFN1.',
                            "DepartureDateTime": "'.$godpTime1.'",
                            "ArrivalDateTime": "'.$goarrTime1.'",
                            "Type": "A",
                            "ClassOfService": "'.$gobCode1.'",
                            "OriginLocation": {
                                "LocationCode": "'.$godeparture1.'"
                            },
                            "DestinationLocation": {
                                "LocationCode": "'.$goarrival1.'"
                            },
                            "Airline": {
                                "Operating": "'.$gooCarrier1.'",
                                "Marketing": "'.$gomCarrier1.'"
                            }
                        }
                    ]
                }
            },{
                "RPH": "1",
                "DepartureDateTime": "'.$backdpTime.'",
                "OriginLocation": {
                    "LocationCode": "'.$backdeparture.'"
                },
                "DestinationLocation": {
                    "LocationCode": "'.$backarrival.'"
                },
                "TPA_Extensions": {
                    "SegmentType": {
                        "Code": "O"
                    },
                    "Flight": [
                        {
                            "Number": '.$backmCarrierFN.',
                            "DepartureDateTime": "'.$backdpTime.'",
                            "ArrivalDateTime": "'.$backarrTime.'",
                            "Type": "A",
                            "ClassOfService": "'.$backbCode.'",
                            "OriginLocation": {
                                "LocationCode": "'.$backdeparture.'"
                            },
                            "DestinationLocation": {
                                "LocationCode": "'.$backarrival.'"
                            },
                            "Airline": {
                                "Operating": "'.$backoCarrier.'",
                                "Marketing": "'.$backmCarrier.'"
                            }
                        },
                        {
                            "Number": '.$backmCarrierFN1.',
                            "DepartureDateTime": "'.$backdpTime1.'",
                            "ArrivalDateTime": "'.$backarrTime1.'",
                            "Type": "A",
                            "ClassOfService": "'.$backbCode1.'",
                            "OriginLocation": {
                                "LocationCode": "'.$backdeparture1.'"
                            },
                            "DestinationLocation": {
                                "LocationCode": "'.$backarrival1.'"
                            },
                            "Airline": {
                                "Operating": "'.$backoCarrier1.'",
                                "Marketing": "'.$backmCarrier1.'"
                            }
                        }
                    ]
                }
            }]';


    }else if($Segment == 3){

        $godeparture = $_POST['segments']['go'][0]['departure'];
        $goarrival = $_POST['segments']['go'][0]['arrival'];
        $godpTime = $_POST['segments']['go'][0]['dpTime'];
        $goarrTime = $_POST['segments']['go'][0]['arrTime'];
        $gobCode = $_POST['segments']['go'][0]['bCode'];
        $gomCarrier = $_POST['segments']['go'][0]['mCarrier'];
        $gomCarrierFN = $_POST['segments']['go'][0]['mCarrierFN'];
        $gooCarrier = $_POST['segments']['go'][0]['oCarrier'];
        $gooCarrierFN = $_POST['segments']['go'][0]['oCarrierFN'];

        $godeparture1 = $_POST['segments']['go'][1]['departure'];
        $goarrival1 = $_POST['segments']['go'][1]['arrival'];
        $godpTime1 = $_POST['segments']['go'][1]['dpTime'];
        $goarrTime1 = $_POST['segments']['go'][1]['arrTime'];
        $gobCode1 = $_POST['segments']['go'][1]['bCode'];
        $gomCarrier1 = $_POST['segments']['go'][1]['mCarrier'];
        $gomCarrierFN1 = $_POST['segments']['go'][1]['mCarrierFN'];
        $gooCarrier1 = $_POST['segments']['go'][1]['oCarrier'];
        $gooCarrierFN1 = $_POST['segments']['go'][1]['oCarrierFN'];
        

        $godeparture2 = $_POST['segments']['go'][2]['departure'];
        $goarrival2 = $_POST['segments']['go'][2]['arrival'];
        $godpTime2 = $_POST['segments']['go'][2]['dpTime'];
        $goarrTime2 = $_POST['segments']['go'][2]['arrTime'];
        $gobCode2 = $_POST['segments']['go'][2]['bCode'];
        $gomCarrier2 = $_POST['segments']['go'][2]['mCarrier'];
        $gomCarrierFN2 = $_POST['segments']['go'][2]['mCarrierFN'];
        $gooCarrier2 = $_POST['segments']['go'][2]['oCarrier'];
        $gooCarrierFN2 = $_POST['segments']['go'][2]['oCarrierFN'];
        

        $backdeparture = $_POST['segments']['back'][0]['departure'];
        $backarrival = $_POST['segments']['back'][0]['arrival'];
        $backdpTime = $_POST['segments']['back'][0]['dpTime'];
        $backarrTime = $_POST['segments']['back'][0]['arrTime'];
        $backbCode = $_POST['segments']['back'][0]['bCode'];
        $backmCarrier = $_POST['segments']['back'][0]['mCarrier'];
        $backmCarrierFN = $_POST['segments']['back'][0]['mCarrierFN'];
        $backoCarrier = $_POST['segments']['back'][0]['oCarrier'];
        $backoCarrierFN = $_POST['segments']['back'][0]['oCarrierFN'];

        $backdeparture1 = $_POST['segments']['back'][1]['departure'];
        $backarrival1 = $_POST['segments']['back'][1]['arrival'];
        $backdpTime1 = $_POST['segments']['back'][1]['dpTime'];
        $backarrTime1 = $_POST['segments']['back'][1]['arrTime'];
        $backbCode1 = $_POST['segments']['back'][1]['bCode'];
        $backmCarrier1 = $_POST['segments']['back'][1]['mCarrier'];
        $backmCarrierFN1 = $_POST['segments']['back'][1]['mCarrierFN'];
        $backoCarrier1 = $_POST['segments']['back'][1]['oCarrier'];
        $backoCarrierFN1 = $_POST['segments']['back'][1]['oCarrierFN'];

        $backdeparture2 = $_POST['segments']['back'][2]['departure'];
        $backarrival2 = $_POST['segments']['back'][2]['arrival'];
        $backdpTime2 = $_POST['segments']['back'][2]['dpTime'];
        $backarrTime2 = $_POST['segments']['back'][2]['arrTime'];
        $backbCode2 = $_POST['segments']['back'][2]['bCode'];
        $backmCarrier2 = $_POST['segments']['back'][2]['mCarrier'];
        $backmCarrierFN2 = $_POST['segments']['back'][2]['mCarrierFN'];
        $backoCarrier2 = $_POST['segments']['back'][2]['oCarrier'];
        $backoCarrierFN2 = $_POST['segments']['back'][2]['oCarrierFN'];

    $Request ='[{
                "RPH": "0",
                "DepartureDateTime": "'.$godpTime.'",
                "OriginLocation": {
                    "LocationCode": "'.$godeparture.'"
                },
                "DestinationLocation": {
                    "LocationCode": "'.$goarrival.'"
                },
                "TPA_Extensions": {
                    "SegmentType": {
                        "Code": "O"
                    },
                    "Flight": [
                        {
                            "Number": '.$gomCarrierFN.',
                            "DepartureDateTime": "'.$godpTime.'",
                            "ArrivalDateTime": "'.$goarrTime.'",
                            "Type": "A",
                            "ClassOfService": "'.$gobCode.'",
                            "OriginLocation": {
                                "LocationCode": "'.$godeparture.'"
                            },
                            "DestinationLocation": {
                                "LocationCode": "'.$goarrival.'"
                            },
                            "Airline": {
                                "Operating": "'.$gooCarrier.'",
                                "Marketing": "'.$gomCarrier.'"
                            }
                        },
                        {
                            "Number": '.$gomCarrierFN1.',
                            "DepartureDateTime": "'.$godpTime1.'",
                            "ArrivalDateTime": "'.$goarrTime1.'",
                            "Type": "A",
                            "ClassOfService": "'.$gobCode1.'",
                            "OriginLocation": {
                                "LocationCode": "'.$godeparture1.'"
                            },
                            "DestinationLocation": {
                                "LocationCode": "'.$goarrival1.'"
                            },
                            "Airline": {
                                "Operating": "'.$gooCarrier1.'",
                                "Marketing": "'.$gomCarrier1.'"
                            }
                        },
                        {
                            "Number": '.$gomCarrierFN2.',
                            "DepartureDateTime": "'.$godpTime2.'",
                            "ArrivalDateTime": "'.$goarrTime2.'",
                            "Type": "A",
                            "ClassOfService": "'.$gobCode2.'",
                            "OriginLocation": {
                                "LocationCode": "'.$godeparture2.'"
                            },
                            "DestinationLocation": {
                                "LocationCode": "'.$goarrival2.'"
                            },
                            "Airline": {
                                "Operating": "'.$gooCarrier2.'",
                                "Marketing": "'.$gomCarrier2.'"
                            }
                        }
                    ]
                }
            },
            {
                "RPH": "1",
                "DepartureDateTime": "'.$backdpTime.'",
                "OriginLocation": {
                    "LocationCode": "'.$backdeparture.'"
                },
                "DestinationLocation": {
                    "LocationCode": "'.$backarrival.'"
                },
                "TPA_Extensions": {
                    "SegmentType": {
                        "Code": "O"
                    },
                    "Flight": [
                        {
                            "Number": '.$backmCarrierFN.',
                            "DepartureDateTime": "'.$backdpTime.'",
                            "ArrivalDateTime": "'.$backarrTime.'",
                            "Type": "A",
                            "ClassOfService": "'.$backbCode.'",
                            "OriginLocation": {
                                "LocationCode": "'.$backdeparture.'"
                            },
                            "DestinationLocation": {
                                "LocationCode": "'.$backarrival.'"
                            },
                            "Airline": {
                                "Operating": "'.$backoCarrier.'",
                                "Marketing": "'.$backmCarrier.'"
                            }
                        },
                        {
                            "Number": '.$backmCarrierFN1.',
                            "DepartureDateTime": "'.$backdpTime1.'",
                            "ArrivalDateTime": "'.$backarrTime1.'",
                            "Type": "A",
                            "ClassOfService": "'.$backbCode1.'",
                            "OriginLocation": {
                                "LocationCode": "'.$backdeparture1.'"
                            },
                            "DestinationLocation": {
                                "LocationCode": "'.$backarrival1.'"
                            },
                            "Airline": {
                                "Operating": "'.$backoCarrier1.'",
                                "Marketing": "'.$backmCarrier1.'"
                            }
                        },
                        {
                            "Number": '.$backmCarrierFN2.',
                            "DepartureDateTime": "'.$backdpTime2.'",
                            "ArrivalDateTime": "'.$backarrTime2.'",
                            "Type": "A",
                            "ClassOfService": "'.$backbCode2.'",
                            "OriginLocation": {
                                "LocationCode": "'.$backdeparture2.'"
                            },
                            "DestinationLocation": {
                                "LocationCode": "'.$backarrival2.'"
                            },
                            "Airline": {
                                "Operating": "'.$backoCarrier2.'",
                                "Marketing": "'.$backmCarrier2.'"
                            }
                        }
                    ]
                }
            }]';

    }else if($Segment == 12){
        $godeparture = $_POST['segments']['go'][0]['departure'];
        $goarrival = $_POST['segments']['go'][0]['arrival'];
        $godpTime = $_POST['segments']['go'][0]['dpTime'];
        $goarrTime = $_POST['segments']['go'][0]['arrTime'];
        $gobCode = $_POST['segments']['go'][0]['bCode'];
        $gomCarrier = $_POST['segments']['go'][0]['mCarrier'];
        $gomCarrierFN = $_POST['segments']['go'][0]['mCarrierFN'];
        $gooCarrier = $_POST['segments']['go'][0]['oCarrier'];
        $gooCarrierFN = $_POST['segments']['go'][0]['oCarrierFN'];

        
        $backdeparture = $_POST['segments']['back'][0]['departure'];
        $backarrival = $_POST['segments']['back'][0]['arrival'];
        $backdpTime = $_POST['segments']['back'][0]['dpTime'];
        $backarrTime = $_POST['segments']['back'][0]['arrTime'];
        $backbCode = $_POST['segments']['back'][0]['bCode'];
        $backmCarrier = $_POST['segments']['back'][0]['mCarrier'];
        $backmCarrierFN = $_POST['segments']['back'][0]['mCarrierFN'];
        $backoCarrier = $_POST['segments']['back'][0]['oCarrier'];
        $backoCarrierFN = $_POST['segments']['back'][0]['oCarrierFN'];

        $backdeparture1 = $_POST['segments']['back'][1]['departure'];
        $backarrival1 = $_POST['segments']['back'][1]['arrival'];
        $backdpTime1 = $_POST['segments']['back'][1]['dpTime'];
        $backarrTime1 = $_POST['segments']['back'][1]['arrTime'];
        $backbCode1 = $_POST['segments']['back'][1]['bCode'];
        $backmCarrier1 = $_POST['segments']['back'][1]['mCarrier'];
        $backmCarrierFN1 = $_POST['segments']['back'][1]['mCarrierFN'];
        $backoCarrier1 = $_POST['segments']['back'][1]['oCarrier'];
        $backoCarrierFN1 = $_POST['segments']['back'][1]['oCarrierFN']; 

    $Request ='[{
                "RPH": "0",
                "DepartureDateTime": "'.$godpTime.'",
                "OriginLocation": {
                    "LocationCode": "'.$godeparture.'"
                },
                "DestinationLocation": {
                    "LocationCode": "'.$goarrival.'"
                },
                "TPA_Extensions": {
                    "SegmentType": {
                        "Code": "O"
                    },
                    "Flight": [
                        {
                            "Number": '.$gomCarrierFN.',
                            "DepartureDateTime": "'.$godpTime.'",
                            "ArrivalDateTime": "'.$goarrTime.'",
                            "Type": "A",
                            "ClassOfService": "'.$gobCode.'",
                            "OriginLocation": {
                                "LocationCode": "'.$godeparture.'"
                            },
                            "DestinationLocation": {
                                "LocationCode": "'.$goarrival.'"
                            },
                            "Airline": {
                                "Operating": "'.$gooCarrier.'",
                                "Marketing": "'.$gomCarrier.'"
                            }
                        }
                    ]
                }
            },
            {
                "RPH": "1",
                "DepartureDateTime": "'.$backdpTime.'",
                "OriginLocation": {
                    "LocationCode": "'.$backdeparture.'"
                },
                "DestinationLocation": {
                    "LocationCode": "'.$backarrival.'"
                },
                "TPA_Extensions": {
                    "SegmentType": {
                        "Code": "O"
                    },
                    "Flight": [
                        {
                            "Number": '.$backmCarrierFN.',
                            "DepartureDateTime": "'.$backdpTime.'",
                            "ArrivalDateTime": "'.$backarrTime.'",
                            "Type": "A",
                            "ClassOfService": "'.$backbCode.'",
                            "OriginLocation": {
                                "LocationCode": "'.$backdeparture.'"
                            },
                            "DestinationLocation": {
                                "LocationCode": "'.$backarrival.'"
                            },
                            "Airline": {
                                "Operating": "'.$backoCarrier.'",
                                "Marketing": "'.$backmCarrier.'"
                            }
                        },
                        {
                            "Number": '.$backmCarrierFN1.',
                            "DepartureDateTime": "'.$backdpTime1.'",
                            "ArrivalDateTime": "'.$backarrTime1.'",
                            "Type": "A",
                            "ClassOfService": "'.$backbCode1.'",
                            "OriginLocation": {
                                "LocationCode": "'.$backdeparture1.'"
                            },
                            "DestinationLocation": {
                                "LocationCode": "'.$backarrival1.'"
                            },
                            "Airline": {
                                "Operating": "'.$backoCarrier1.'",
                                "Marketing": "'.$backmCarrier1.'"
                            }
                        }
                    ]
                }
            }]';


    }else if($Segment == 21){
        $godeparture = $_POST['segments']['go'][0]['departure'];
        $goarrival = $_POST['segments']['go'][0]['arrival'];
        $godpTime = $_POST['segments']['go'][0]['dpTime'];
        $goarrTime = $_POST['segments']['go'][0]['arrTime'];
        $gobCode = $_POST['segments']['go'][0]['bCode'];
        $gomCarrier = $_POST['segments']['go'][0]['mCarrier'];
        $gomCarrierFN = $_POST['segments']['go'][0]['mCarrierFN'];
        $gooCarrier = $_POST['segments']['go'][0]['oCarrier'];
        $gooCarrierFN = $_POST['segments']['go'][0]['oCarrierFN'];

        $godeparture1 = $_POST['segments']['go'][1]['departure'];
        $goarrival1 = $_POST['segments']['go'][1]['arrival'];
        $godpTime1 = $_POST['segments']['go'][1]['dpTime'];
        $goarrTime1 = $_POST['segments']['go'][1]['arrTime'];
        $gobCode1 = $_POST['segments']['go'][1]['bCode'];
        $gomCarrier1 = $_POST['segments']['go'][1]['mCarrier'];
        $gomCarrierFN1 = $_POST['segments']['go'][1]['mCarrierFN'];
        $gooCarrier1 = $_POST['segments']['go'][1]['oCarrier'];
        $gooCarrierFN1 = $_POST['segments']['go'][1]['oCarrierFN'];

        $backdeparture = $_POST['segments']['back'][0]['departure'];
        $backarrival = $_POST['segments']['back'][0]['arrival'];
        $backdpTime = $_POST['segments']['back'][0]['dpTime'];
        $backarrTime = $_POST['segments']['back'][0]['arrTime'];
        $backbCode = $_POST['segments']['back'][0]['bCode'];
        $backmCarrier = $_POST['segments']['back'][0]['mCarrier'];
        $backmCarrierFN = $_POST['segments']['back'][0]['mCarrierFN'];
        $backoCarrier = $_POST['segments']['back'][0]['oCarrier'];
        $backoCarrierFN = $_POST['segments']['back'][0]['oCarrierFN'];


    $Request ='[{
                "RPH": "0",
                "DepartureDateTime": "'.$godpTime.'",
                "OriginLocation": {
                    "LocationCode": "'.$godeparture.'"
                },
                "DestinationLocation": {
                    "LocationCode": "'.$goarrival.'"
                },
                "TPA_Extensions": {
                    "SegmentType": {
                        "Code": "O"
                    },
                    "Flight": [
                        {
                            "Number": '.$gomCarrierFN.',
                            "DepartureDateTime": "'.$godpTime.'",
                            "ArrivalDateTime": "'.$goarrTime.'",
                            "Type": "A",
                            "ClassOfService": "'.$gobCode.'",
                            "OriginLocation": {
                                "LocationCode": "'.$godeparture.'"
                            },
                            "DestinationLocation": {
                                "LocationCode": "'.$goarrival.'"
                            },
                            "Airline": {
                                "Operating": "'.$gooCarrier.'",
                                "Marketing": "'.$gomCarrier.'"
                            }
                        },
                        {
                            "Number": '.$gomCarrierFN1.',
                            "DepartureDateTime": "'.$godpTime1.'",
                            "ArrivalDateTime": "'.$goarrTime1.'",
                            "Type": "A",
                            "ClassOfService": "'.$gobCode1.'",
                            "OriginLocation": {
                                "LocationCode": "'.$godeparture1.'"
                            },
                            "DestinationLocation": {
                                "LocationCode": "'.$goarrival1.'"
                            },
                            "Airline": {
                                "Operating": "'.$gooCarrier1.'",
                                "Marketing": "'.$gomCarrier1.'"
                            }
                        }
                        
                    ]
                }
            },{
                "RPH": "1",
                "DepartureDateTime": "'.$backdpTime.'",
                "OriginLocation": {
                    "LocationCode": "'.$backdeparture.'"
                },
                "DestinationLocation": {
                    "LocationCode": "'.$backarrival.'"
                },
                "TPA_Extensions": {
                    "SegmentType": {
                        "Code": "O"
                    },
                    "Flight": [
                        {
                            "Number": '.$backmCarrierFN.',
                            "DepartureDateTime": "'.$backdpTime.'",
                            "ArrivalDateTime": "'.$backarrTime.'",
                            "Type": "A",
                            "ClassOfService": "'.$backbCode.'",
                            "OriginLocation": {
                                "LocationCode": "'.$backdeparture.'"
                            },
                            "DestinationLocation": {
                                "LocationCode": "'.$backarrival.'"
                            },
                            "Airline": {
                                "Operating": "'.$backoCarrier.'",
                                "Marketing": "'.$backmCarrier.'"
                            }
                        }
                    ]
                }
            }]';

    }
} else if ($tripType == 3 || $tripType == 'multicity') {

    $i =0;
    foreach ($_POST['segments'] as $segment) {
        $i++;
        $departure = $segment['departure'];
        $arrival = $segment['arrival'];
        $dpTime = $segment['dpTime'];
        $arrTime = $segment['arrTime'];
        $bCode = $segment['bCode'];
        $mCarrier = $segment['mCarrier'];
        $mCarrierFN = (int)$segment['mCarrierFN'];
        $oCarrier = $segment['oCarrier'];
        $oCarrierFN = (int)$segment['oCarrierFN'];


        $MultiRequest =
            array(
                "RPH" => "$i",
                "DepartureDateTime" => $dpTime,
                "OriginLocation" => array(
                    "LocationCode" => $departure
                ),
                "DestinationLocation" => array(
                    "LocationCode" => $arrival,
                ),
                "TPA_Extensions" => array(
                    "SegmentType" => array(
                        "Code" => "O"
                    ),
                    "Flight" => array(
                        array(
                            "Number" => $mCarrierFN,
                            "DepartureDateTime" => $dpTime,
                            "ArrivalDateTime" => $arrTime,
                            "Type" => "A",
                            "ClassOfService" => $bCode,
                            "OriginLocation" => array(
                                "LocationCode" => $departure
                            ),
                            "DestinationLocation" => array(
                                "LocationCode" => $arrival
                            ),
                            "Airline" => array(
                                "Operating" => $oCarrier,
                                "Marketing" => $mCarrier
                            )
                        )
                    )
                )
            );

        array_push($RequestArray, $MultiRequest);

    }

    $Request = json_encode($RequestArray);
}


$SabreRequest ='{
    "OTA_AirLowFareSearchRQ": {
        "Version": "4",
        "TravelPreferences": {
            "TPA_Extensions": {
                "VerificationItinCallLogic": {
                    "Value": "B"
                }
            }
        },
        "TravelerInfoSummary": {
            "SeatsRequested": [
                1
            ],
            "AirTravelerAvail": [
                {
                    "PassengerTypeQuantity": ['.$paxRequest.']
                }
            ]
        },
        "POS": {
            "Source": [
                {
                    "PseudoCityCode": "FD3K",
                    "RequestorID": {
                        "Type": "1",
                        "ID": "1",
                        "CompanyName": {
                            "Code": "TN"
                        }
                    }
                }
            ]
        },
        "OriginDestinationInformation":'.$Request.',
        "TPA_Extensions": {
            "IntelliSellTransaction": {
                "RequestType": {
                    "Name": "50ITINS"
                }
            }
        }
    }
}';

//print($SabreRequest);



try{

    $client_id= base64_encode("V1:593072:14KK:AA");
	//$client_secret = base64_encode("280ff537"); //cert
	$client_secret = base64_encode("f270395");
    
    $token = base64_encode($client_id.":".$client_secret);
    
    $curl = curl_init();
    
    curl_setopt_array($curl, array(
      CURLOPT_URL => 'https://api.platform.sabre.com/v2/auth/token',
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => '',
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => 'POST',
      CURLOPT_POSTFIELDS =>'grant_type=client_credentials',
      CURLOPT_HTTPHEADER => array(
        'Content-Type: application/x-www-form-urlencoded',
        "Authorization: Basic $token"
      ),
    ));
    $Tokenres = curl_exec($curl);
    curl_close($curl);
    $resToken = json_decode($Tokenres, true);
    $access_token = $resToken['access_token'];

    //echo $access_token;

}catch (Exception $e){ 
    
}



$curl = curl_init();

curl_setopt_array($curl, array(
   // CURLOPT_URL => 'https://api-crt.cert.havail.sabre.com/v4.3.0/shop/flights/revalidate', //Testing
  CURLOPT_URL => 'https://api.platform.sabre.com/v4/shop/flights/revalidate', //Live
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => '',
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => 'POST',
  CURLOPT_POSTFIELDS => $SabreRequest,
  CURLOPT_HTTPHEADER => array(
    'Content-Type: application/json',
    'Conversation-ID: {{conv_id}}',
    "Authorization: Bearer $access_token",
  ),
));

$response = curl_exec($curl);

//echo $response;

curl_close($curl);

    $result = json_decode($response, true);
      if(isset($result['groupedItineraryResponse']['statistics']['itineraryCount'])){
         $status = $result['groupedItineraryResponse']['statistics']['itineraryCount'];
         if($status == 1){
            echo $response;           
         }else if($status == 0){
            $SabreResponse['status']= "error";
            $SabreResponse['message']= "InComplete";
            $SabreResponse['response']= "You cannot Book this flight";
            echo json_encode($SabreResponse);                      
         }  
         
      }else{
        $SabreResponse['status']= "error";
        $SabreResponse['message']= "InComplete";
        $SabreResponse['response']= "You cannot Book this flight";
        echo json_encode($SabreResponse);
      }

}


?>