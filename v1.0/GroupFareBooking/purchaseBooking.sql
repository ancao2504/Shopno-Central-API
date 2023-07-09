$sql="UPDATE booking SET
            airlinesPNR='$airlinesPNR',
            paidAmount= '$paidAmount',
            paidDate='$paidDate',
            status='Ticketed'
            WHERE bookingId ='$bookingId'";