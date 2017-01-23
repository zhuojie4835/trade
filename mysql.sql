SELECT COUNT(*) AS `number`, `mother` > 24 AS `type` FROM `prince` GROUP BY `mother` > 24;

SELECT 
    ( SELECT COUNT( * ) FROM `prince` WHERE `mother` >24 ) AS `digong`, 
    ( SELECT COUNT( * ) FROM `prince` WHERE `mother` <=24 ) AS `tiangong`
	
SELECT 
    COUNT( CASE WHEN `mother` >24 THEN 1 ELSE NULL END ) AS `digong`, 
    COUNT( CASE WHEN `mother` <=24 THEN 1 ELSE NULL END ) AS `tiangong`
FROM prince