SELECT 
	* 
FROM 
	usergroups AS a
WHERE 
(
	SELECT
		COUNT(*)
	FROM
		members_usergroups AS b
	WHERE 
	(
		a.id 	    = b.usergroup_id and 
		b.member_id = :id
	)
) > 0