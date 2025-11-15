<?php

$user_id = (int) ( $_GET['u'] ?? null );
if ( ! ( $user_id && $user_id > 0 && $user_id < 1e12 ) ) {
	return;
}

$mtime = @filemtime( __DIR__ . '/../../../../../uploads/voxel-cache/inbox-activity/' . $user_id . '.txt' );
echo ( $mtime && $mtime > time() ) ? 1 : '';
exit;
