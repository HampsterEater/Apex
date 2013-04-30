UPDATE
	posts
SET
	bump_time=:time,
	bump_count=bump_count+1,
WHERE
	id=:id