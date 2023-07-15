SELECT 'totalPendingDepositAmount' AS category, COALESCE(SUM(amount),0) AS count
FROM `deposit_request`
WHERE DATE(createdAt) = CURDATE() AND status = 'pending'

UNION ALL

SELECT 'totalApprovedDepositAmount' AS category, COALESCE(SUM(amount),0) AS count
FROM `deposit_request`
WHERE DATE(actionAt) = CURDATE() AND status = 'approved'

UNION ALL

SELECT 'totalRejectedDepositAmount' AS category, COALESCE(SUM(amount),0) AS count
FROM `deposit_request`
WHERE DATE(actionAt) = CURDATE() AND status = 'rejected'