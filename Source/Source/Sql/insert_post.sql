INSERT INTO posts
(
	parent_id,
	subject,
	email,
	name,
	comment,
	tripcode,
	upload_url,
	upload_thumbnail_url,
	upload_hash,
	original_upload_name,
	file_type_id,
	bump_time,
	bump_count,
	contains_exif,
	create_ip,
	create_time
)
VALUES
(
	:parent_id,
	:subject,
	:email,
	:name,
	:comment,
	:tripcode,
	:upload_url,
	:upload_thumbnail_url,
	:upload_hash,
	:original_upload_name,
	:file_type_id,
	:bump_time,
	:bump_count,
	:contains_exif,
	:create_ip,
	:create_time
)

