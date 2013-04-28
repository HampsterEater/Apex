INSERT INTO password_reset_links
	(
		member_id, 
		link_hash, 
		create_time, 
		create_ip
	) 
VALUES
	(
		:member_id, 
		:link_hash, 
		:create_time, 
		:create_ip
	)