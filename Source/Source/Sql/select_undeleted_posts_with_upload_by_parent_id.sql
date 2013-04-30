SELECT
	*
FROM
	POSTS
WHERE 
	parent_id 	 =	:parent_id AND
	upload_file != 	"" AND
	is_deleted	!= 1