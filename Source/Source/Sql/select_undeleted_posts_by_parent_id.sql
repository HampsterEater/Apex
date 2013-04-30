SELECT
	*
FROM
	POSTS
WHERE 
	parent_id=:parent_id AND
	is_deleted != 1