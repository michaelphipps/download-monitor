<?php

/**
 * DLM_Download_Version class.
 */
class DLM_Download_Version {

	/**
	 * __construct function.
	 *
	 * @access public
	 * @return void
	 */
	public function __construct( $version_id, $download_id ) {
		$this->id          = absint( $version_id );
		$this->download_id = absint( $download_id );

		// Get Version Data
		$this->mirrors  = array_filter( (array) get_post_meta( $this->id, '_files', true ) );
		$this->url      = current( $this->mirrors );
		$this->filename = current( explode( '?', basename( $this->url ) ) );
		$this->filetype = strtolower( substr( strrchr( $this->filename, "." ), 1 ) );
		$this->version  = strtolower( get_post_meta( $this->id, '_version', true ) );
		$this->download_count     = get_post_meta( $this->id, '_download_count', true );
		$this->filesize = get_post_meta( $this->id, '_filesize', true );

		// If data is not set, load it
		if ( $this->filesize == "" )
			$this->filesize = $this->get_filesize( $this->url );
	}

	/**
	 * increase_download_count function.
	 *
	 * @access public
	 * @return void
	 */
	public function increase_download_count() {
		// check if we are counting unique downloads only
		$count_download = false;
		if ( get_option( 'dlm_unique_downloads' ) == 1 ){
			// if we are:

			// get users ip address
			$user_ip = sanitize_text_field( ! empty( $_SERVER['HTTP_X_FORWARD_FOR'] ) ? $_SERVER['HTTP_X_FORWARD_FOR'] : $_SERVER['REMOTE_ADDR'] );

			// get list of ips that have downloaded this file
			$download_ips = get_post_meta( $this->id, '_download_ips', true );
			
			// check if the users' IP address has already downloaded this file
			if(in_array($user_ip, $download_ips)){
				// if it has, don't count.
				
			} else {
				// if it hasn't, add the ip to the array and count
				$download_ips[] = $user_ip;
				update_post_meta( $this->id, '_download_ips', $download_ips);
				$count_download = true;
			}
		} else {
			// we're not counting unique downloads.  Count Download!
			$count_download = true;
		}

		if ($count_download){
			// File download_count
			$this->download_count = absint( get_post_meta( $this->id, '_download_count', true ) ) + 1;
			update_post_meta( $this->id, '_download_count', $this->download_count );

			// Parent download download_count
			$parent_download_count = absint( get_post_meta( $this->download_id, '_download_count', true ) ) + 1;
			update_post_meta( $this->download_id, '_download_count', $parent_download_count );
		}
	}

	/**
	 * get_filesize function.
	 *
	 * @access public
	 * @param mixed $file
	 * @return void
	 */
	public function get_filesize( $file_path ) {
		global $download_monitor;

		$filesize = $download_monitor->get_filesize( $file_path );

		update_post_meta( $this->id, '_filesize', $filesize );

		return $filesize;
	}
}