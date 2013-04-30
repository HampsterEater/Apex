SELECT
	*
FROM
	POSTS
WHERE 
	parent_id 	 =	:parent_id AND
	upload_file != 	""