INSERT INTO posts_tags
(
	post_id,
	tag_id,
	create_ip,
	create_time
)
VALUES
(
	:post_id,
	:tag_id,
	:create_ip,
	:create_time
)