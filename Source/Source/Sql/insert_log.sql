INSERT INTO logs
	(
		message, 
		source, 
		create_time, 
		create_ip
	) 
VALUES
	(
		:message, 
		:source, 
		:create_time, 
		:create_ip
	)