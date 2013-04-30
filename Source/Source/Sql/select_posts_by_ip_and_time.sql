SELECT 
	* 
FROM 
	posts 
WHERE 
	create_ip 	 = :create_ip AND 
	create_time >= :min_time