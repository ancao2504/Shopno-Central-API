SELECT a.agentId, a.name, b.remBook, b.latestTravelDate, c.lastAmount
FROM agent a
JOIN (
    SELECT agentId, COUNT(status) AS remBook, MAX(travelDate) AS latestTravelDate
    FROM booking
    WHERE status = 'Hold'
    GROUP BY agentId
) b ON a.agentId = b.agentId
JOIN (
    SELECT agentId, lastAmount,
           ROW_NUMBER() OVER (PARTITION BY agentId ORDER BY createdAt DESC) AS rn
    FROM agent_ledger
) c ON a.agentId = c.agentId
WHERE a.status = 'active' AND c.rn = 1;