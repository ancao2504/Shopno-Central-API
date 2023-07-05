SELECT a.agentId, ag.company, ag.name, b.remBook, b.latestTravelDate, c.lastAmount, a.remarks, c.loan, ag.credit
FROM activitylog a
JOIN agent ag ON a.agentId = ag.agentId
JOIN (
    SELECT agentId, COUNT(*) AS remBook, MAX(travelDate) AS latestTravelDate
    FROM booking
    WHERE status = 'Hold'
    GROUP BY agentId
) b ON a.agentId = b.agentId
JOIN (
    SELECT agentId, lastAmount, loan,
           ROW_NUMBER() OVER (PARTITION BY agentId ORDER BY createdAt DESC) AS rn
    FROM agent_ledger
) c ON a.agentId = c.agentId
WHERE a.status = 'Credited' AND c.rn = 1;
