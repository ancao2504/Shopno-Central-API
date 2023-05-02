<?php

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");



$message = <<<EOM
<univ:AirCreateReservationReq xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:univ="http://www.travelport.com/schema/universal_v51_0" TraceId="FFI-KayesFahim" TargetBranch="P7182044" RuleName="COMM" RetainReservation="Both" RestrictWaitlist="true" xmlns="http://www.travelport.com/schema/common_v51_0">
  <BillingPointOfSaleInfo xmlns="http://www.travelport.com/schema/common_v51_0" OriginApplication="UAPI" />
  <BookingTraveler Key="11" TravelerType="ADT" Gender="M" Nationality="BD" xmlns="http://www.travelport.com/schema/common_v51_0">
    <BookingTravelerName Prefix="MR " First="KAYES" Last="FAHIM" />
    <DeliveryInfo>
      <ShippingAddress>
        <AddressName>Home</AddressName>
        <Street>Block D, Basundhara, Dhaka</Street>
        <City>DHAKA</City>
        <State>DHAKA</State>
        <PostalCode>1229</PostalCode>
        <Country>BD</Country>
      </ShippingAddress>
    </DeliveryInfo>
    <Email Type="Home" EmailID="abcd@gmail.com" />
    <SSR Type="DOCS" Status="HK" FreeText="P/BD/090561W1/BD/08APR87/M/02AUG30/FAHIM/KAYES" Carrier="EK" />
    <SSR Type="PCTC" FreeText="MR KAYES FAHIM/DAC TEL 01922358358" Carrier="EK" />
    <SSR Type="CTCM" Status="HK" FreeText="01922358358" Carrier="EK" />
    <SSR Type="CTCE" Status="HK" FreeText="abcd//gmail.com" Carrier="EK" />
  </BookingTraveler>
  <BookingTraveler Key="21" TravelerType="CNN" Age="7" Gender="M" Nationality="BD" xmlns="http://www.travelport.com/schema/common_v51_0">
    <BookingTravelerName Prefix="MSTR" First="PARVEZ" Last="HASAN" />
    <SSR Type="DOCS" Status="HK" FreeText="P/BD/0905SD12361W1/BD/08APR15/M/02AUG30/HASAN/PARVEZ" Carrier="EK" />
    <SSR Type="PCTC" FreeText="MSTR  PARVEZ HASAN/DAC TEL 01912238358" Carrier="EK" />
    <SSR Type="CTCM" Status="HK" FreeText="01912238358" Carrier="EK" />
    <SSR Type="CTCE" Status="HK" FreeText="abcd//gmail.com" Carrier="EK" />
    <NameRemark Category="AIR">
      <RemarkData>P-C07 DOB08Apr15</RemarkData>
    </NameRemark>
  </BookingTraveler>
  <BookingTraveler Key="31" TravelerType="INF" Gender="M" Nationality="BD" xmlns="http://www.travelport.com/schema/common_v51_0">
    <BookingTravelerName Prefix="MSTR" First="SOHAN" Last="HASAN" />
    <SSR Type="DOCS" Status="HK" FreeText="P/BD/090561341W1/BD/08DEC21/MI/02AUG30/HASAN/SOHAN" Carrier="EK" />
    <SSR Type="PCTC" FreeText="MSTR  SOHAN HASAN/DAC TEL 01924658358" Carrier="EK" />
    <SSR Type="CTCM" Status="HK" FreeText="01924658358" Carrier="EK" />
    <SSR Type="CTCE" Status="HK" FreeText="abcd//gmail.com" Carrier="EK" />
    <NameRemark Category="AIR">
      <RemarkData>08Dec21</RemarkData>
    </NameRemark>
  </BookingTraveler>
  <OSI Carrier="EK" Text="PAX CTCM/234567890" xmlns="http://www.travelport.com/schema/common_v51_0" />
  <OSI Carrier="EK" Text=" PAX CTCE/abcd//gmail.com" xmlns="http://www.travelport.com/schema/common_v51_0" />
  <ContinuityCheckOverride Key="1T" xmlns="http://www.travelport.com/schema/common_v51_0">true
     </ContinuityCheckOverride>
  <AgencyContactInfo xmlns="http://www.travelport.com/schema/common_v51_0">
    <PhoneNumber Location="DAC" Number="234567890" Text="Flyway International" />
  </AgencyContactInfo>
  <AirPricingSolution Key="TTWiiMSqWDKA+a92DAAAAA==" TotalPrice="BDT166696" BasePrice="USD1370.00" ApproximateTotalPrice="BDT166696" ApproximateBasePrice="BDT127534" EquivalentBasePrice="BDT127534" Taxes="BDT39162" Fees="BDT0" ApproximateTaxes="BDT39162" QuoteDate="2022-09-13" xmlns="http://www.travelport.com/schema/air_v51_0">
    <AirSegment Key="TTWiiMSqWDKA2a92DAAAAA==" Group="0" Carrier="EK" FlightNumber="583" ProviderCode="1G" Origin="DAC" Destination="DXB" DepartureTime="2022-09-16T10:15:00.000+06:00" ArrivalTime="2022-09-16T13:05:00.000+04:00" FlightTime="290" TravelTime="290" Distance="2207" ClassOfService="L" Equipment="77W" ChangeOfPlane="false" OptionalServicesIndicator="false" AvailabilitySource="S" ParticipantLevel="Secure Sell" LinkAvailability="true" PolledAvailabilityOption="O and D cache or polled status used with different local status" AvailabilityDisplayType="Fare Specific Fare Quote Unbooked">
      <CodeshareInfo OperatingCarrier="EK">Emirates</CodeshareInfo>
      <AirAvailInfo ProviderCode="1G" />
      <FlightDetails Key="TTWiiMSqWDKA3a92DAAAAA==" Origin="DAC" Destination="DXB" DepartureTime="2022-09-16T10:15:00.000+06:00" ArrivalTime="2022-09-16T13:05:00.000+04:00" FlightTime="290" TravelTime="290" Distance="2207" />
      <Connection SegmentIndex="0" />
    </AirSegment>
    <AirSegment Key="TTWiiMSqWDKA4a92DAAAAA==" Group="0" Carrier="EK" FlightNumber="9" ProviderCode="1G" Origin="DXB" Destination="LGW" DepartureTime="2022-09-16T14:55:00.000+04:00" ArrivalTime="2022-09-16T19:45:00.000+01:00" FlightTime="470" TravelTime="470" Distance="3403" ClassOfService="L" Equipment="388" ChangeOfPlane="false" OptionalServicesIndicator="false" AvailabilitySource="S" ParticipantLevel="Secure Sell" LinkAvailability="true" PolledAvailabilityOption="O and D cache or polled status used with different local status" AvailabilityDisplayType="Fare Specific Fare Quote Unbooked">
      <CodeshareInfo OperatingCarrier="EK">Emirates</CodeshareInfo>
      <AirAvailInfo ProviderCode="1G" />
      <FlightDetails Key="TTWiiMSqWDKA5a92DAAAAA==" Origin="DXB" Destination="LGW" DepartureTime="2022-09-16T14:55:00.000+04:00" ArrivalTime="2022-09-16T19:45:00.000+01:00" FlightTime="470" TravelTime="470" Distance="3403" />
    </AirSegment>
    <AirSegment Key="TTWiiMSqWDKA6a92DAAAAA==" Group="1" Carrier="EK" FlightNumber="10" ProviderCode="1G" Origin="LGW" Destination="DXB" DepartureTime="2022-09-30T21:45:00.000+01:00" ArrivalTime="2022-10-01T07:35:00.000+04:00" FlightTime="410" TravelTime="410" Distance="3403" ClassOfService="T" Equipment="388" ChangeOfPlane="false" OptionalServicesIndicator="false" AvailabilitySource="S" ParticipantLevel="Secure Sell" LinkAvailability="true" PolledAvailabilityOption="O and D cache or polled status used with different local status" AvailabilityDisplayType="Fare Specific Fare Quote Unbooked">
      <CodeshareInfo OperatingCarrier="EK">Emirates</CodeshareInfo>
      <AirAvailInfo ProviderCode="1G" />
      <FlightDetails Key="TTWiiMSqWDKA7a92DAAAAA==" Origin="LGW" Destination="DXB" DepartureTime="2022-09-30T21:45:00.000+01:00" ArrivalTime="2022-10-01T07:35:00.000+04:00" FlightTime="410" TravelTime="410" Distance="3403" />
      <Connection SegmentIndex="2" />
    </AirSegment>
    <AirSegment Key="TTWiiMSqWDKA8a92DAAAAA==" Group="1" Carrier="EK" FlightNumber="584" ProviderCode="1G" Origin="DXB" Destination="DAC" DepartureTime="2022-10-01T16:45:00.000+04:00" ArrivalTime="2022-10-01T23:20:00.000+06:00" FlightTime="275" TravelTime="275" Distance="2207" ClassOfService="T" Equipment="77W" ChangeOfPlane="false" OptionalServicesIndicator="false" AvailabilitySource="S" ParticipantLevel="Secure Sell" LinkAvailability="true" PolledAvailabilityOption="O and D cache or polled status used with different local status" AvailabilityDisplayType="Fare Specific Fare Quote Unbooked">
      <CodeshareInfo OperatingCarrier="EK">Emirates</CodeshareInfo>
      <AirAvailInfo ProviderCode="1G" />
      <FlightDetails Key="TTWiiMSqWDKA9a92DAAAAA==" Origin="DXB" Destination="DAC" DepartureTime="2022-10-01T16:45:00.000+04:00" ArrivalTime="2022-10-01T23:20:00.000+06:00" FlightTime="275" TravelTime="275" Distance="2207" />
    </AirSegment>
    <AirPricingInfo Key="TTWiiMSqWDKAFb92DAAAAA==" TotalPrice="BDT91885" BasePrice="USD739.00" ApproximateTotalPrice="BDT91885" ApproximateBasePrice="BDT68794" EquivalentBasePrice="BDT68794" ApproximateTaxes="BDT23091" Taxes="BDT23091" LatestTicketingTime="2022-09-16T23:59:00.000+06:00" PricingMethod="Guaranteed" IncludesVAT="false" ETicketability="Yes" ProviderCode="1G">
      <FareInfo Key="TTWiiMSqWDKAQb92DAAAAA==" FareBasis="LEXEPBD1" PassengerTypeCode="ADT" Origin="DAC" Destination="LGW" EffectiveDate="2022-09-13T10:41:00.000+06:00" DepartureDate="2022-09-16" Amount="BDT36454" NegotiatedFare="false" NotValidBefore="2022-09-16" NotValidAfter="2022-09-16" TaxAmount="BDT10566.00">
        <FareRuleKey FareInfoRef="TTWiiMSqWDKAQb92DAAAAA==" ProviderCode="1G">6UUVoSldxwhxAJfK++DSaMbKj3F8T9EyxsqPcXxP0TLGyo9xfE/RMsuWFfXVd1OAly5qxZ3qLwOXLmrFneovA5cuasWd6i8Dly5qxZ3qLwOXLmrFneovAxsq9Xrmw+DmxWa1uaqI55k3aSkvhp2ybd8UGJnpHTqkwXlRHy9zjZzAyGqh8JIaqDaeTunq4mkeOwvqi1ink3il8b2FdO9G0ur/bRGkSYBeVvL0gTWvGVKnsahilB5/WJLKVanbw7tLZ2vqEvHvGtBS8ndYUmM13YB1xjSbRrjKTicpvYbmY4Wr6XufjmxNISf0n2fNLTc7ir5wkVQHOuKXLmrFneovA5cuasWd6i8Dly5qxZ3qLwOXLmrFneovA5cuasWd6i8Dc3mNX1GvOry3EdbqPPDZRsBAkLPMThzJuedM5Dut5bASSvT1evWeYfQMlKepD17IYq7nMtlnk50=</FareRuleKey>
        <Brand Key="TTWiiMSqWDKAQb92DAAAAA==" BrandFound="false" />
      </FareInfo>
      <FareInfo Key="TTWiiMSqWDKATb92DAAAAA==" FareBasis="TEXEPBD1" PassengerTypeCode="ADT" Origin="LGW" Destination="DAC" EffectiveDate="2022-09-13T10:41:00.000+06:00" DepartureDate="2022-09-30" Amount="BDT32340" NegotiatedFare="false" NotValidBefore="2022-09-30" NotValidAfter="2022-09-30" TaxAmount="BDT12525.00">
        <FareRuleKey FareInfoRef="TTWiiMSqWDKATb92DAAAAA==" ProviderCode="1G">6UUVoSldxwhxAJfK++DSaMbKj3F8T9EyxsqPcXxP0TLGyo9xfE/RMsuWFfXVd1OAly5qxZ3qLwOXLmrFneovA5cuasWd6i8Dly5qxZ3qLwOXLmrFneovAxsq9Xrmw+DmxWa1uaqI55k3aSkvhp2ybXwoNKEjC8vw4Qu7zOl6oOTAyGqh8JIaqDOlLL1pWVUuOwvqi1ink3jLnG/YMnHULer/bRGkSYBeVvL0gTWvGVKnsahilB5/WJLKVanbw7tLZ2vqEvHvGtBS8ndYUmM13Tyy/Q52QOiITicpvYbmY4XviQBXmhPC36vlnzGFI7eTir5wkVQHOuKXLmrFneovA5cuasWd6i8Dly5qxZ3qLwOXLmrFneovA5cuasWd6i8Dc3mNX1GvOry3EdbqPPDZRsBAkLPMThzJuedM5Dut5bASSvT1evWeYfQMlKepD17IYq7nMtlnk50=</FareRuleKey>
        <Brand Key="TTWiiMSqWDKATb92DAAAAA==" BrandFound="false" />
      </FareInfo>
      <BookingInfo BookingCode="L" CabinClass="Economy" FareInfoRef="TTWiiMSqWDKAQb92DAAAAA==" SegmentRef="TTWiiMSqWDKA2a92DAAAAA==" HostTokenRef="TTWiiMSqWDKA/a92DAAAAA==" />
      <BookingInfo BookingCode="L" CabinClass="Economy" FareInfoRef="TTWiiMSqWDKAQb92DAAAAA==" SegmentRef="TTWiiMSqWDKA4a92DAAAAA==" HostTokenRef="TTWiiMSqWDKA/a92DAAAAA==" />
      <BookingInfo BookingCode="T" CabinClass="Economy" FareInfoRef="TTWiiMSqWDKATb92DAAAAA==" SegmentRef="TTWiiMSqWDKA6a92DAAAAA==" HostTokenRef="TTWiiMSqWDKAAb92DAAAAA==" />
      <BookingInfo BookingCode="T" CabinClass="Economy" FareInfoRef="TTWiiMSqWDKATb92DAAAAA==" SegmentRef="TTWiiMSqWDKA8a92DAAAAA==" HostTokenRef="TTWiiMSqWDKAAb92DAAAAA==" />
      <TaxInfo Category="BD" Amount="BDT500" Key="TTWiiMSqWDKAGb92DAAAAA==" />
      <TaxInfo Category="OW" Amount="BDT3000" Key="TTWiiMSqWDKAHb92DAAAAA==" />
      <TaxInfo Category="P7" Amount="BDT931" Key="TTWiiMSqWDKAIb92DAAAAA==" />
      <TaxInfo Category="P8" Amount="BDT931" Key="TTWiiMSqWDKAJb92DAAAAA==" />
      <TaxInfo Category="UT" Amount="BDT4000" Key="TTWiiMSqWDKAKb92DAAAAA==" />
      <TaxInfo Category="F6" Amount="BDT1776" Key="TTWiiMSqWDKALb92DAAAAA==" />
      <TaxInfo Category="ZR" Amount="BDT254" Key="TTWiiMSqWDKAMb92DAAAAA==" />
      <TaxInfo Category="GB" Amount="BDT9253" Key="TTWiiMSqWDKANb92DAAAAA==" />
      <TaxInfo Category="UB" Amount="BDT2091" Key="TTWiiMSqWDKAOb92DAAAAA==" />
      <TaxInfo Category="E5" Amount="BDT355" Key="TTWiiMSqWDKAPb92DAAAAA==" />
      <FareCalc>DAC EK X/DXB EK LON 389.00LEXEPBD1 EK X/DXB EK DAC 347.00TEXEPBD1 Q DACDAC2.60NUC738.60END ROE1.0</FareCalc>
      <PassengerType Code="ADT" BookingTravelerRef="11" />
      <ChangePenalty>
        <Amount>BDT7447.0</Amount>
      </ChangePenalty>
      <CancelPenalty>
        <Percentage>100.00</Percentage>
      </CancelPenalty>
      <AirPricingModifiers PlatingCarrier="EK" />
      <BaggageAllowances>
        <BaggageAllowanceInfo TravelerType="ADT" Origin="DAC" Destination="LGW" Carrier="EK">
          <URLInfo>
            <URL>VIEWTRIP.TRAVELPORT.COM/BAGGAGEPOLICY/EK</URL>
          </URLInfo>
          <TextInfo>
            <Text>25K</Text>
            <Text>BAGGAGE DISCOUNTS MAY APPLY BASED ON FREQUENT FLYER STATUS/ ONLINE CHECKIN/FORM OF PAYMENT/MILITARY/ETC.</Text>
          </TextInfo>
          <BagDetails ApplicableBags="1stChecked">
            <BaggageRestriction>
              <TextInfo>
                <Text>CHGS MAY APPLY IF BAGS EXCEED TTL WT ALLOWANCE</Text>
              </TextInfo>
            </BaggageRestriction>
          </BagDetails>
          <BagDetails ApplicableBags="2ndChecked">
            <BaggageRestriction>
              <TextInfo>
                <Text>CHGS MAY APPLY IF BAGS EXCEED TTL WT ALLOWANCE</Text>
              </TextInfo>
            </BaggageRestriction>
          </BagDetails>
        </BaggageAllowanceInfo>
        <BaggageAllowanceInfo TravelerType="ADT" Origin="LGW" Destination="DAC" Carrier="EK">
          <URLInfo>
            <URL>VIEWTRIP.TRAVELPORT.COM/BAGGAGEPOLICY/EK</URL>
          </URLInfo>
          <TextInfo>
            <Text>25K</Text>
            <Text>BAGGAGE DISCOUNTS MAY APPLY BASED ON FREQUENT FLYER STATUS/ ONLINE CHECKIN/FORM OF PAYMENT/MILITARY/ETC.</Text>
          </TextInfo>
          <BagDetails ApplicableBags="1stChecked">
            <BaggageRestriction>
              <TextInfo>
                <Text>CHGS MAY APPLY IF BAGS EXCEED TTL WT ALLOWANCE</Text>
              </TextInfo>
            </BaggageRestriction>
          </BagDetails>
          <BagDetails ApplicableBags="2ndChecked">
            <BaggageRestriction>
              <TextInfo>
                <Text>CHGS MAY APPLY IF BAGS EXCEED TTL WT ALLOWANCE</Text>
              </TextInfo>
            </BaggageRestriction>
          </BagDetails>
        </BaggageAllowanceInfo>
        <CarryOnAllowanceInfo Origin="DAC" Destination="DXB" Carrier="EK">
          <TextInfo>
            <Text>1P</Text>
          </TextInfo>
          <CarryOnDetails ApplicableCarryOnBags="1" BasePrice="BDT0" ApproximateBasePrice="BDT0" TotalPrice="BDT0" ApproximateTotalPrice="BDT0">
            <BaggageRestriction>
              <TextInfo>
                <Text>UPTO15LB/7KG AND UPTO45LI/115LCM</Text>
              </TextInfo>
            </BaggageRestriction>
          </CarryOnDetails>
        </CarryOnAllowanceInfo>
        <CarryOnAllowanceInfo Origin="DXB" Destination="LGW" Carrier="EK">
          <TextInfo>
            <Text>1P</Text>
          </TextInfo>
          <CarryOnDetails ApplicableCarryOnBags="1" BasePrice="AED0" TotalPrice="AED0">
            <BaggageRestriction>
              <TextInfo>
                <Text>UPTO15LB/7KG AND UPTO45LI/115LCM</Text>
              </TextInfo>
            </BaggageRestriction>
          </CarryOnDetails>
        </CarryOnAllowanceInfo>
        <CarryOnAllowanceInfo Origin="LGW" Destination="DXB" Carrier="EK">
          <TextInfo>
            <Text>1P</Text>
          </TextInfo>
          <CarryOnDetails ApplicableCarryOnBags="1" BasePrice="GBP0.00" TotalPrice="GBP0.00">
            <BaggageRestriction>
              <TextInfo>
                <Text>UPTO15LB/7KG AND UPTO45LI/115LCM</Text>
              </TextInfo>
            </BaggageRestriction>
          </CarryOnDetails>
        </CarryOnAllowanceInfo>
        <CarryOnAllowanceInfo Origin="DXB" Destination="DAC" Carrier="EK">
          <TextInfo>
            <Text>1P</Text>
          </TextInfo>
          <CarryOnDetails ApplicableCarryOnBags="1" BasePrice="AED0" TotalPrice="AED0">
            <BaggageRestriction>
              <TextInfo>
                <Text>UPTO15LB/7KG AND UPTO45LI/115LCM</Text>
              </TextInfo>
            </BaggageRestriction>
          </CarryOnDetails>
        </CarryOnAllowanceInfo>
      </BaggageAllowances>
    </AirPricingInfo>
    <AirPricingInfo Key="TTWiiMSqWDKAab92DAAAAA==" TotalPrice="BDT63503" BasePrice="USD555.00" ApproximateTotalPrice="BDT63503" ApproximateBasePrice="BDT51665" EquivalentBasePrice="BDT51665" ApproximateTaxes="BDT11838" Taxes="BDT11838" LatestTicketingTime="2022-09-16T23:59:00.000+06:00" PricingMethod="Guaranteed" IncludesVAT="false" ETicketability="Yes" ProviderCode="1G">
      <FareInfo Key="TTWiiMSqWDKAkb92DAAAAA==" FareBasis="LEXEPBD1" PassengerTypeCode="CNN" Origin="DAC" Destination="LGW" EffectiveDate="2022-09-13T10:41:00.000+06:00" DepartureDate="2022-09-16" Amount="BDT27401" NegotiatedFare="false" NotValidBefore="2022-09-16" NotValidAfter="2022-09-16" TaxAmount="BDT8566.00">
        <FareRuleKey FareInfoRef="TTWiiMSqWDKAkb92DAAAAA==" ProviderCode="1G">6UUVoSldxwhxAJfK++DSaMbKj3F8T9EyxsqPcXxP0TLGyo9xfE/RMsuWFfXVd1OAly5qxZ3qLwOXLmrFneovA5cuasWd6i8Dly5qxZ3qLwOXLmrFneovAxsq9Xrmw+DmxWa1uaqI55k3aSkvhp2ybVTzaHYPPf4607t4woc+eVXAyGqh8JIaqDaeTunq4mkeOwvqi1ink3il8b2FdO9G0ur/bRGkSYBeVvL0gTWvGVIHyACgdSlRrJLKVanbw7tLZ2vqEvHvGtBS8ndYUmM13YB1xjSbRrjKTicpvYbmY4Wr6XufjmxNIcs7gUFvemZ4ir5wkVQHOuKXLmrFneovA5cuasWd6i8Dly5qxZ3qLwOXLmrFneovA5cuasWd6i8Dc3mNX1GvOry3EdbqPPDZRsBAkLPMThzJV2Fthr9vlXgSSvT1evWeYfQMlKepD17IYq7nMtlnk50=</FareRuleKey>
        <Brand Key="TTWiiMSqWDKAkb92DAAAAA==" BrandFound="false" />
      </FareInfo>
      <FareInfo Key="TTWiiMSqWDKAnb92DAAAAA==" FareBasis="TEXEPBD1" PassengerTypeCode="CNN" Origin="LGW" Destination="DAC" EffectiveDate="2022-09-13T10:41:00.000+06:00" DepartureDate="2022-09-30" Amount="BDT24264" NegotiatedFare="false" NotValidBefore="2022-09-30" NotValidAfter="2022-09-30" TaxAmount="BDT3272.00">
        <FareRuleKey FareInfoRef="TTWiiMSqWDKAnb92DAAAAA==" ProviderCode="1G">6UUVoSldxwhxAJfK++DSaMbKj3F8T9EyxsqPcXxP0TLGyo9xfE/RMsuWFfXVd1OAly5qxZ3qLwOXLmrFneovA5cuasWd6i8Dly5qxZ3qLwOXLmrFneovAxsq9Xrmw+DmxWa1uaqI55k3aSkvhp2ybSbwz6fyDLOY5NH/d8Gd5Y7AyGqh8JIaqDOlLL1pWVUuOwvqi1ink3jLnG/YMnHULer/bRGkSYBeVvL0gTWvGVIHyACgdSlRrJLKVanbw7tLZ2vqEvHvGtBS8ndYUmM13Tyy/Q52QOiITicpvYbmY4XviQBXmhPC3/dS5cd2RIT5ir5wkVQHOuKXLmrFneovA5cuasWd6i8Dly5qxZ3qLwOXLmrFneovA5cuasWd6i8Dc3mNX1GvOry3EdbqPPDZRsBAkLPMThzJV2Fthr9vlXgSSvT1evWeYfQMlKepD17IYq7nMtlnk50=</FareRuleKey>
        <Brand Key="TTWiiMSqWDKAnb92DAAAAA==" BrandFound="false" />
      </FareInfo>
      <BookingInfo BookingCode="L" CabinClass="Economy" FareInfoRef="TTWiiMSqWDKAkb92DAAAAA==" SegmentRef="TTWiiMSqWDKA2a92DAAAAA==" HostTokenRef="TTWiiMSqWDKABb92DAAAAA==" />
      <BookingInfo BookingCode="L" CabinClass="Economy" FareInfoRef="TTWiiMSqWDKAkb92DAAAAA==" SegmentRef="TTWiiMSqWDKA4a92DAAAAA==" HostTokenRef="TTWiiMSqWDKABb92DAAAAA==" />
      <BookingInfo BookingCode="T" CabinClass="Economy" FareInfoRef="TTWiiMSqWDKAnb92DAAAAA==" SegmentRef="TTWiiMSqWDKA6a92DAAAAA==" HostTokenRef="TTWiiMSqWDKACb92DAAAAA==" />
      <BookingInfo BookingCode="T" CabinClass="Economy" FareInfoRef="TTWiiMSqWDKAnb92DAAAAA==" SegmentRef="TTWiiMSqWDKA8a92DAAAAA==" HostTokenRef="TTWiiMSqWDKACb92DAAAAA==" />
      <TaxInfo Category="BD" Amount="BDT500" Key="TTWiiMSqWDKAbb92DAAAAA==" />
      <TaxInfo Category="OW" Amount="BDT3000" Key="TTWiiMSqWDKAcb92DAAAAA==" />
      <TaxInfo Category="P7" Amount="BDT931" Key="TTWiiMSqWDKAdb92DAAAAA==" />
      <TaxInfo Category="P8" Amount="BDT931" Key="TTWiiMSqWDKAeb92DAAAAA==" />
      <TaxInfo Category="UT" Amount="BDT2000" Key="TTWiiMSqWDKAfb92DAAAAA==" />
      <TaxInfo Category="F6" Amount="BDT1776" Key="TTWiiMSqWDKAgb92DAAAAA==" />
      <TaxInfo Category="ZR" Amount="BDT254" Key="TTWiiMSqWDKAhb92DAAAAA==" />
      <TaxInfo Category="UB" Amount="BDT2091" Key="TTWiiMSqWDKAib92DAAAAA==" />
      <TaxInfo Category="E5" Amount="BDT355" Key="TTWiiMSqWDKAjb92DAAAAA==" />
      <FareCalc>DAC EK X/DXB EK LON 291.75LEXEPBD1CH EK X/DXB EK DAC 260.25TEXEPBD1CH Q DACDAC2.60NUC554.60END ROE1.0</FareCalc>
      <PassengerType Code="CNN" Age="7" BookingTravelerRef="21" />
      <ChangePenalty>
        <Amount>BDT7447.0</Amount>
      </ChangePenalty>
      <CancelPenalty>
        <Percentage>100.00</Percentage>
      </CancelPenalty>
      <AirPricingModifiers PlatingCarrier="EK" />
      <BaggageAllowances>
        <BaggageAllowanceInfo TravelerType="CNN" Origin="DAC" Destination="LGW" Carrier="EK">
          <URLInfo>
            <URL>VIEWTRIP.TRAVELPORT.COM/BAGGAGEPOLICY/EK</URL>
          </URLInfo>
          <TextInfo>
            <Text>25K</Text>
            <Text>BAGGAGE DISCOUNTS MAY APPLY BASED ON FREQUENT FLYER STATUS/ ONLINE CHECKIN/FORM OF PAYMENT/MILITARY/ETC.</Text>
          </TextInfo>
          <BagDetails ApplicableBags="1stChecked">
            <BaggageRestriction>
              <TextInfo>
                <Text>CHGS MAY APPLY IF BAGS EXCEED TTL WT ALLOWANCE</Text>
              </TextInfo>
            </BaggageRestriction>
          </BagDetails>
          <BagDetails ApplicableBags="2ndChecked">
            <BaggageRestriction>
              <TextInfo>
                <Text>CHGS MAY APPLY IF BAGS EXCEED TTL WT ALLOWANCE</Text>
              </TextInfo>
            </BaggageRestriction>
          </BagDetails>
        </BaggageAllowanceInfo>
        <BaggageAllowanceInfo TravelerType="CNN" Origin="LGW" Destination="DAC" Carrier="EK">
          <URLInfo>
            <URL>VIEWTRIP.TRAVELPORT.COM/BAGGAGEPOLICY/EK</URL>
          </URLInfo>
          <TextInfo>
            <Text>25K</Text>
            <Text>BAGGAGE DISCOUNTS MAY APPLY BASED ON FREQUENT FLYER STATUS/ ONLINE CHECKIN/FORM OF PAYMENT/MILITARY/ETC.</Text>
          </TextInfo>
          <BagDetails ApplicableBags="1stChecked">
            <BaggageRestriction>
              <TextInfo>
                <Text>CHGS MAY APPLY IF BAGS EXCEED TTL WT ALLOWANCE</Text>
              </TextInfo>
            </BaggageRestriction>
          </BagDetails>
          <BagDetails ApplicableBags="2ndChecked">
            <BaggageRestriction>
              <TextInfo>
                <Text>CHGS MAY APPLY IF BAGS EXCEED TTL WT ALLOWANCE</Text>
              </TextInfo>
            </BaggageRestriction>
          </BagDetails>
        </BaggageAllowanceInfo>
        <CarryOnAllowanceInfo Origin="DAC" Destination="DXB" Carrier="EK">
          <TextInfo>
            <Text>1P</Text>
          </TextInfo>
          <CarryOnDetails ApplicableCarryOnBags="1" BasePrice="BDT0" ApproximateBasePrice="BDT0" TotalPrice="BDT0" ApproximateTotalPrice="BDT0">
            <BaggageRestriction>
              <TextInfo>
                <Text>UPTO15LB/7KG AND UPTO45LI/115LCM</Text>
              </TextInfo>
            </BaggageRestriction>
          </CarryOnDetails>
        </CarryOnAllowanceInfo>
        <CarryOnAllowanceInfo Origin="DXB" Destination="LGW" Carrier="EK">
          <TextInfo>
            <Text>1P</Text>
          </TextInfo>
          <CarryOnDetails ApplicableCarryOnBags="1" BasePrice="AED0" TotalPrice="AED0">
            <BaggageRestriction>
              <TextInfo>
                <Text>UPTO15LB/7KG AND UPTO45LI/115LCM</Text>
              </TextInfo>
            </BaggageRestriction>
          </CarryOnDetails>
        </CarryOnAllowanceInfo>
        <CarryOnAllowanceInfo Origin="LGW" Destination="DXB" Carrier="EK">
          <TextInfo>
            <Text>1P</Text>
          </TextInfo>
          <CarryOnDetails ApplicableCarryOnBags="1" BasePrice="GBP0.00" TotalPrice="GBP0.00">
            <BaggageRestriction>
              <TextInfo>
                <Text>UPTO15LB/7KG AND UPTO45LI/115LCM</Text>
              </TextInfo>
            </BaggageRestriction>
          </CarryOnDetails>
        </CarryOnAllowanceInfo>
        <CarryOnAllowanceInfo Origin="DXB" Destination="DAC" Carrier="EK">
          <TextInfo>
            <Text>1P</Text>
          </TextInfo>
          <CarryOnDetails ApplicableCarryOnBags="1" BasePrice="AED0" TotalPrice="AED0">
            <BaggageRestriction>
              <TextInfo>
                <Text>UPTO15LB/7KG AND UPTO45LI/115LCM</Text>
              </TextInfo>
            </BaggageRestriction>
          </CarryOnDetails>
        </CarryOnAllowanceInfo>
      </BaggageAllowances>
    </AirPricingInfo>
    <AirPricingInfo Key="TTWiiMSqWDKAub92DAAAAA==" TotalPrice="BDT11308" BasePrice="USD76.00" ApproximateTotalPrice="BDT11308" ApproximateBasePrice="BDT7075" EquivalentBasePrice="BDT7075" ApproximateTaxes="BDT4233" Taxes="BDT4233" LatestTicketingTime="2022-09-16T23:59:00.000+06:00" PricingMethod="Guaranteed" IncludesVAT="false" ETicketability="Yes" ProviderCode="1G">
      <FareInfo Key="TTWiiMSqWDKAzb92DAAAAA==" FareBasis="LEXEPBD1" PassengerTypeCode="INF" Origin="DAC" Destination="LGW" EffectiveDate="2022-09-13T10:41:00.000+06:00" DepartureDate="2022-09-16" Amount="BDT3864" NegotiatedFare="false" NotValidBefore="2022-09-16" NotValidAfter="2022-09-16" TaxAmount="BDT2015.00">
        <FareRuleKey FareInfoRef="TTWiiMSqWDKAzb92DAAAAA==" ProviderCode="1G">6UUVoSldxwhxAJfK++DSaMbKj3F8T9EyxsqPcXxP0TLGyo9xfE/RMsuWFfXVd1OAly5qxZ3qLwOXLmrFneovA5cuasWd6i8Dly5qxZ3qLwOXLmrFneovAxsq9Xrmw+DmxWa1uaqI55k3aSkvhp2ybd8UGJnpHTqk4gWzNYwCpZlN3J3NyYpz6c1U+/S+MNcvqlae8q+tUXvVuFM1fnsVZWLgFn3B9sPdSU8gecfbWEljEmMG+NRoPpmRVmgyUnTzVQh4viDNLsteWRv8cS/JBUPFc7ZmQhP+mpBH9wehGMHKU92ACEHlpKjYt39+X+MB/AFKEXb03hK/he9va7VDH7+F729rtUMfv4Xvb2u1Qx+/he9va7VDHxDGJun84l6GmjYuszn207WOnxkQ2WPPHvaVUAnec78/Nwk2d9nu1JIBHLSHPwSl7SN21AwjfkkHCdZzfSk26lQ=</FareRuleKey>
        <Brand Key="TTWiiMSqWDKAzb92DAAAAA==" BrandFound="false" />
      </FareInfo>
      <FareInfo Key="TTWiiMSqWDKA2b92DAAAAA==" FareBasis="TEXEPBD1" PassengerTypeCode="INF" Origin="LGW" Destination="DAC" EffectiveDate="2022-09-13T10:41:00.000+06:00" DepartureDate="2022-09-30" Amount="BDT3211" NegotiatedFare="false" NotValidBefore="2022-09-30" NotValidAfter="2022-09-30" TaxAmount="BDT2218.00">
        <FareRuleKey FareInfoRef="TTWiiMSqWDKA2b92DAAAAA==" ProviderCode="1G">6UUVoSldxwhxAJfK++DSaMbKj3F8T9EyxsqPcXxP0TLGyo9xfE/RMsuWFfXVd1OAly5qxZ3qLwOXLmrFneovA5cuasWd6i8Dly5qxZ3qLwOXLmrFneovAxsq9Xrmw+DmxWa1uaqI55k3aSkvhp2ybXwoNKEjC8vwwRHirO3N1z9N3J3NyYpz6ZO0MgXcEdpMqlae8q+tUXt2Uf5cVN8F+mLgFn3B9sPdSU8gecfbWEljEmMG+NRoPpmRVmgyUnTzVQh4viDNLsteWRv8cS/JBU4nKb2G5mOFmpBH9wehGMEN1+cn/Sb89WMuEfmxXTEd/AFKEXb03hK/he9va7VDH7+F729rtUMfv4Xvb2u1Qx+/he9va7VDHxDGJun84l6GmjYuszn207WOnxkQ2WPPHvaVUAnec78/Nwk2d9nu1JIBHLSHPwSl7SN21AwjfkkHCdZzfSk26lQ=</FareRuleKey>
        <Brand Key="TTWiiMSqWDKA2b92DAAAAA==" BrandFound="false" />
      </FareInfo>
      <BookingInfo BookingCode="L" CabinClass="Economy" FareInfoRef="TTWiiMSqWDKAzb92DAAAAA==" SegmentRef="TTWiiMSqWDKA2a92DAAAAA==" HostTokenRef="TTWiiMSqWDKADb92DAAAAA==" />
      <BookingInfo BookingCode="L" CabinClass="Economy" FareInfoRef="TTWiiMSqWDKAzb92DAAAAA==" SegmentRef="TTWiiMSqWDKA4a92DAAAAA==" HostTokenRef="TTWiiMSqWDKADb92DAAAAA==" />
      <BookingInfo BookingCode="T" CabinClass="Economy" FareInfoRef="TTWiiMSqWDKA2b92DAAAAA==" SegmentRef="TTWiiMSqWDKA6a92DAAAAA==" HostTokenRef="TTWiiMSqWDKAEb92DAAAAA==" />
      <BookingInfo BookingCode="T" CabinClass="Economy" FareInfoRef="TTWiiMSqWDKA2b92DAAAAA==" SegmentRef="TTWiiMSqWDKA8a92DAAAAA==" HostTokenRef="TTWiiMSqWDKAEb92DAAAAA==" />
      <TaxInfo Category="P7" Amount="BDT931" Key="TTWiiMSqWDKAvb92DAAAAA==" />
      <TaxInfo Category="P8" Amount="BDT931" Key="TTWiiMSqWDKAwb92DAAAAA==" />
      <TaxInfo Category="UB" Amount="BDT2091" Key="TTWiiMSqWDKAxb92DAAAAA==" />
      <TaxInfo Category="E5" Amount="BDT280" Key="TTWiiMSqWDKAyb92DAAAAA==" />
      <FareCalc>DAC EK X/DXB EK LON 38.90LEXEPBD1IN EK X/DXB EK DAC 34.70TEXEPBD1IN Q DACDAC2.60NUC76.20END ROE1.0</FareCalc>
      <PassengerType Code="INF" Age="1" BookingTravelerRef="31" />
      <ChangePenalty>
        <Amount>BDT7447.0</Amount>
      </ChangePenalty>
      <CancelPenalty>
        <Percentage>100.00</Percentage>
      </CancelPenalty>
      <AirPricingModifiers PlatingCarrier="EK" />
      <BaggageAllowances>
        <BaggageAllowanceInfo TravelerType="INF" Origin="DAC" Destination="LGW" Carrier="EK">
          <URLInfo>
            <URL>VIEWTRIP.TRAVELPORT.COM/BAGGAGEPOLICY/EK</URL>
          </URLInfo>
          <TextInfo>
            <Text>10K</Text>
            <Text>BAGGAGE DISCOUNTS MAY APPLY BASED ON FREQUENT FLYER STATUS/ ONLINE CHECKIN/FORM OF PAYMENT/MILITARY/ETC.</Text>
          </TextInfo>
          <BagDetails ApplicableBags="1stChecked">
            <BaggageRestriction>
              <TextInfo>
                <Text>CHGS MAY APPLY IF BAGS EXCEED TTL WT ALLOWANCE</Text>
              </TextInfo>
            </BaggageRestriction>
          </BagDetails>
          <BagDetails ApplicableBags="2ndChecked">
            <BaggageRestriction>
              <TextInfo>
                <Text>CHGS MAY APPLY IF BAGS EXCEED TTL WT ALLOWANCE</Text>
              </TextInfo>
            </BaggageRestriction>
          </BagDetails>
        </BaggageAllowanceInfo>
        <BaggageAllowanceInfo TravelerType="INF" Origin="LGW" Destination="DAC" Carrier="EK">
          <URLInfo>
            <URL>VIEWTRIP.TRAVELPORT.COM/BAGGAGEPOLICY/EK</URL>
          </URLInfo>
          <TextInfo>
            <Text>10K</Text>
            <Text>BAGGAGE DISCOUNTS MAY APPLY BASED ON FREQUENT FLYER STATUS/ ONLINE CHECKIN/FORM OF PAYMENT/MILITARY/ETC.</Text>
          </TextInfo>
          <BagDetails ApplicableBags="1stChecked">
            <BaggageRestriction>
              <TextInfo>
                <Text>CHGS MAY APPLY IF BAGS EXCEED TTL WT ALLOWANCE</Text>
              </TextInfo>
            </BaggageRestriction>
          </BagDetails>
          <BagDetails ApplicableBags="2ndChecked">
            <BaggageRestriction>
              <TextInfo>
                <Text>CHGS MAY APPLY IF BAGS EXCEED TTL WT ALLOWANCE</Text>
              </TextInfo>
            </BaggageRestriction>
          </BagDetails>
        </BaggageAllowanceInfo>
        <CarryOnAllowanceInfo Origin="DAC" Destination="DXB" Carrier="EK">
          <TextInfo>
            <Text>1P</Text>
          </TextInfo>
          <CarryOnDetails ApplicableCarryOnBags="1" BasePrice="BDT0" ApproximateBasePrice="BDT0" TotalPrice="BDT0" ApproximateTotalPrice="BDT0">
            <BaggageRestriction>
              <TextInfo>
                <Text>UPTO11LB/5KG AND UPTO45LI/115LCM</Text>
              </TextInfo>
            </BaggageRestriction>
          </CarryOnDetails>
        </CarryOnAllowanceInfo>
        <CarryOnAllowanceInfo Origin="DXB" Destination="LGW" Carrier="EK">
          <TextInfo>
            <Text>1P</Text>
          </TextInfo>
          <CarryOnDetails ApplicableCarryOnBags="1" BasePrice="AED0" TotalPrice="AED0">
            <BaggageRestriction>
              <TextInfo>
                <Text>UPTO11LB/5KG AND UPTO45LI/115LCM</Text>
              </TextInfo>
            </BaggageRestriction>
          </CarryOnDetails>
        </CarryOnAllowanceInfo>
        <CarryOnAllowanceInfo Origin="LGW" Destination="DXB" Carrier="EK">
          <TextInfo>
            <Text>1P</Text>
          </TextInfo>
          <CarryOnDetails ApplicableCarryOnBags="1" BasePrice="GBP0.00" TotalPrice="GBP0.00">
            <BaggageRestriction>
              <TextInfo>
                <Text>UPTO11LB/5KG AND UPTO45LI/115LCM</Text>
              </TextInfo>
            </BaggageRestriction>
          </CarryOnDetails>
        </CarryOnAllowanceInfo>
        <CarryOnAllowanceInfo Origin="DXB" Destination="DAC" Carrier="EK">
          <TextInfo>
            <Text>1P</Text>
          </TextInfo>
          <CarryOnDetails ApplicableCarryOnBags="1" BasePrice="AED0" TotalPrice="AED0">
            <BaggageRestriction>
              <TextInfo>
                <Text>UPTO11LB/5KG AND UPTO45LI/115LCM</Text>
              </TextInfo>
            </BaggageRestriction>
          </CarryOnDetails>
        </CarryOnAllowanceInfo>
      </BaggageAllowances>
    </AirPricingInfo>
    <FareNote Key="TTWiiMSqWDKA9b92DAAAAA==">SUM IDENTIFIED AS UB IS A PASSENGER SERVICE CHARGE</FareNote>
    <FareNote Key="TTWiiMSqWDKA+b92DAAAAA==">RATE USED IN EQU TOTAL IS BSR 1USD - 93.09BDT</FareNote>
    <FareNote Key="TTWiiMSqWDKA/b92DAAAAA==">LAST DATE TO PURCHASE TICKET: 16SEP22</FareNote>
    <FareNote Key="TTWiiMSqWDKAAc92DAAAAA==">FARE HAS A PLATING CARRIER RESTRICTION</FareNote>
    <FareNote Key="TTWiiMSqWDKABc92DAAAAA==">E-TKT REQUIRED</FareNote>
    <FareNote Key="TTWiiMSqWDKACc92DAAAAA==">TICKETING FEES MAY APPLY</FareNote>
    <FareNote Key="TTWiiMSqWDKADc92DAAAAA==">SUM IDENTIFIED AS UB IS A PASSENGER SERVICE CHARGE</FareNote>
    <FareNote Key="TTWiiMSqWDKAEc92DAAAAA==">RATE USED IN EQU TOTAL IS BSR 1USD - 93.09BDT</FareNote>
    <FareNote Key="TTWiiMSqWDKAFc92DAAAAA==">LAST DATE TO PURCHASE TICKET: 16SEP22</FareNote>
    <FareNote Key="TTWiiMSqWDKAGc92DAAAAA==">FARE HAS A PLATING CARRIER RESTRICTION</FareNote>
    <FareNote Key="TTWiiMSqWDKAHc92DAAAAA==">E-TKT REQUIRED</FareNote>
    <FareNote Key="TTWiiMSqWDKAIc92DAAAAA==">TICKETING FEES MAY APPLY</FareNote>
    <FareNote Key="TTWiiMSqWDKAJc92DAAAAA==">SUM IDENTIFIED AS UB IS A PASSENGER SERVICE CHARGE</FareNote>
    <FareNote Key="TTWiiMSqWDKAKc92DAAAAA==">RATE USED IN EQU TOTAL IS BSR 1USD - 93.09BDT</FareNote>
    <FareNote Key="TTWiiMSqWDKALc92DAAAAA==">LAST DATE TO PURCHASE TICKET: 16SEP22</FareNote>
    <FareNote Key="TTWiiMSqWDKAMc92DAAAAA==">FARE HAS A PLATING CARRIER RESTRICTION</FareNote>
    <FareNote Key="TTWiiMSqWDKANc92DAAAAA==">E-TKT REQUIRED</FareNote>
    <FareNote Key="TTWiiMSqWDKAOc92DAAAAA==">TICKETING FEES MAY APPLY</FareNote>
    <HostToken Key="TTWiiMSqWDKA/a92DAAAAA==" xmlns="http://www.travelport.com/schema/common_v51_0">GFB10101ADT00  01LEXEPBD1                              0200010002#GFB200010101NADTV3004BDT2001000109940#GFMCXES004NBDT2 EK ADTLEXEPBD1</HostToken>
    <HostToken Key="TTWiiMSqWDKAAb92DAAAAA==" xmlns="http://www.travelport.com/schema/common_v51_0">GFB10101ADT00  02TEXEPBD1                              0200030004#GFB200010102NADTV3004BDT2001000099940#GFMCXES004NBDT2 EK ADTTEXEPBD1</HostToken>
    <HostToken Key="TTWiiMSqWDKABb92DAAAAA==" xmlns="http://www.travelport.com/schema/common_v51_0">GFB10202CNN00  01LEXEPBD1                              0200010002#GFB200020201NCNNV3004BDT2001000109910000684640#GFMCXES004NBDT2DEK CNNLEXEPBD1</HostToken>
    <HostToken Key="TTWiiMSqWDKACb92DAAAAA==" xmlns="http://www.travelport.com/schema/common_v51_0">GFB10202CNN00  02TEXEPBD1                              0200030004#GFB200020202NCNNV3004BDT2001000099910000684640#GFMCXES004NBDT2DEK CNNTEXEPBD1</HostToken>
    <HostToken Key="TTWiiMSqWDKADb92DAAAAA==" xmlns="http://www.travelport.com/schema/common_v51_0">GFB10303INF00  01LEXEPBD1                              0200010002#GFB200030301NINFV3004BDT2001000109910027112740#GFMCXES004NBDT2DEK INFLEXEPBD1</HostToken>
    <HostToken Key="TTWiiMSqWDKAEb92DAAAAA==" xmlns="http://www.travelport.com/schema/common_v51_0">GFB10303INF00  02TEXEPBD1                              0200030004#GFB200030302NINFV3004BDT2001000099910027112740#GFMCXES004NBDT2DEK INFTEXEPBD1</HostToken>
  </AirPricingSolution>
  <ActionStatus xmlns="http://www.travelport.com/schema/common_v51_0" Type="TAW" TicketDate="2022-09-16T23:59:00.000+06:00" ProviderCode="1G" />
  <AirPricingTicketingModifiers xmlns="http://www.travelport.com/schema/air_v51_0" ETicketability="Required" FaresIndicator="PublicAndPrivateFares">
    <AirPricingInfoRef Key="TTWiiMSqWDKAFb92DAAAAA==" />
    <TicketingModifiers>
      <Commission xmlns="http://www.travelport.com/schema/common_v51_0" Level="Fare" Type="PercentBase" Percentage="7.00" />
    </TicketingModifiers>
  </AirPricingTicketingModifiers>
  <AirPricingTicketingModifiers xmlns="http://www.travelport.com/schema/air_v51_0" ETicketability="Required" FaresIndicator="PublicAndPrivateFares">
    <AirPricingInfoRef Key="TTWiiMSqWDKAab92DAAAAA==" />
    <TicketingModifiers>
      <Commission xmlns="http://www.travelport.com/schema/common_v51_0" Level="Fare" Type="PercentBase" Percentage="7.00" />
    </TicketingModifiers>
  </AirPricingTicketingModifiers>
  <AirPricingTicketingModifiers xmlns="http://www.travelport.com/schema/air_v51_0" ETicketability="Required" FaresIndicator="PublicAndPrivateFares">
    <AirPricingInfoRef Key="TTWiiMSqWDKAub92DAAAAA==" />
    <TicketingModifiers>
      <Commission xmlns="http://www.travelport.com/schema/common_v51_0" Level="Fare" Type="PercentBase" Percentage="7.00" />
    </TicketingModifiers>
  </AirPricingTicketingModifiers>
</univ:AirCreateReservationReq>
EOM;



		$TARGETBRANCH = 'P7182044';
		$CREDENTIALS = 'Universal API/uAPI5270664478-0c51bde6:2Td*m/F3M5'; 
		
		$auth = base64_encode("$CREDENTIALS"); 
		$soap_do = curl_init("https://apac.universal-api.pp.travelport.com/B2BGateway/connect/uAPI/AirService");
		$header = array(
		"Content-Type: text/xml;charset=UTF-8", 
		"Accept: gzip,deflate", 
		"Cache-Control: no-cache", 
		"Pragma: no-cache", 
		"SOAPAction: \"\"",
		"Authorization: Basic $auth", 
		"Content-length: ".strlen($message),
		); 


		curl_setopt($soap_do, CURLOPT_SSL_VERIFYPEER, false); 
		curl_setopt($soap_do, CURLOPT_SSL_VERIFYHOST, false); 
		curl_setopt($soap_do, CURLOPT_POST, true ); 
		curl_setopt($soap_do, CURLOPT_POSTFIELDS, $message); 
		curl_setopt($soap_do, CURLOPT_HTTPHEADER, $header); 
		curl_setopt($soap_do, CURLOPT_RETURNTRANSFER, true);
		$return = curl_exec($soap_do);
		curl_close($soap_do);

		print_r($return);

		//$return = file_get_contents("res.xml") ;
		$response = preg_replace("/(<\/?)(\w+):([^>]*>)/", "$1$2$3", $return);
		$xml = new SimpleXMLElement($response);
		//print_r($xml);
		if(isset($xml->xpath('//universalAirCreateReservationRsp')[0])){
			$body = $xml->xpath('//universalAirCreateReservationRsp')[0];
			
		$result = json_decode(json_encode((array)$body), TRUE); 

		$json_string = json_encode($result, JSON_PRETTY_PRINT);
		
		echo $json_string;
		
		}
    

?>