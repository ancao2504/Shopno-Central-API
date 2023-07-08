SELECT * FROM passengers p
    JOIN booking b ON p.bookingId=b.bookingId
    WHERE b.platform='GF'