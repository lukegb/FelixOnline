<?php
/*
 * Media Video class
 *
 * Fields:
 *	  id		  - id of video
 *	  title	   - title of video
 *	  description - video description
 *	  author	  - author of video [string]
 *	  video_id	- id of video
 *	  date		- date of video
 *	  hidden	  -
 *	  hits		-
 *	  site		- name of site video is on
 *	  thumbnail   - url of thumbnail
 *	  
 */
class MediaVideo extends BaseModel {
	protected $type;
	private $thumbnail; // video thumbnail
	private $mostViewed; // array of most viewed videos

	function __construct($id = NULL) {
		global $db, $safesql;
		$this->db = $db;
		$this->safesql = $safesql;
		$this->type = 'video';
		if($id !== NULL) {
			$sql = $this->safesql->query(
				"SELECT 
					`id`,
					`title`,
					`description`,
					`author`,
					`video_id`,
					UNIX_TIMESTAMP(`date`) as date,
					`hidden`,
					`thumbnail`,
					`hits`,
					`site`
				FROM `media_video` 
				WHERE id= %i",
				array(
					$id,
				));
			parent::__construct($this->db->get_row($sql), get_class($this), $id);
			return $this;
		} else {
			return $this;
		}
	}

	public function getType() {
		return $this->type;
	}

	/*
	 * Public: Get photo album url
	 */
	public function getURL() {
		$url = STANDARD_URL
			.'media/video/'
			.$this->getId()
			.'/'
			.Utility::urliseText($this->getTitle())
			.'/';
		return $url;
	}

	/*
	 * Public: Get thumbail
	 *
	 * Returns mediaPhoto object of thumbnail
	 */
	public function getThumbnail() {
		if($this->getSite() == 'youtube') {
			return "http://i.ytimg.com/vi/".$this->getVideoId()."/0.jpg";
		} else {
			return $this->fields['thumbnail'];
		}
	}

	/*
	 * Public: Get most viewed videos
	 *
	 * Returns array of mediaVideo objects
	 */
	public function getMostViewed() {
		if(!$this->mostViewed) {
			$sql = $this->safesql->query(
				"SELECT
					`id`
				FROM
					`media_video`
				WHERE
					hidden = '0'
				ORDER BY hits DESC
				LIMIT 0, 3", array());
			$albums = $this->db->get_results($sql);
			foreach($albums as $object) {
				$this->mostViewed[] = new MediaVideo($object->id);
			}
		} 
		return $this->mostViewed;
	}

	/*
	 * Public: Get embed code
	 *
	 * $width - optional
	 * $height - optional
	 *
	 * Returns html embed code
	 */
	public function getEmbed($width = NULL, $height = NULL) {
		if(!$width) $width = 620;
		if(!$height) $height = 378;
		if($this->getSite() == 'youtube') {
			return '<iframe width="'.$width.'" height="'.$height.'" src="http://www.youtube.com/embed/'.$this->getVideoId().'?rel=0" frameborder="0" allowfullscreen></iframe>';
		} else if($this->getSite() == 'vimeo') {
			return '<iframe src="http://player.vimeo.com/video/'.$this->getVideoId().'" width="'.$width.'" height="'.$height.'" frameborder="0" webkitAllowFullScreen mozallowfullscreen allowFullScreen></iframe>';
		}
	}

	/*
	 * Public: Hit video
	 */
	public function hit() {
		$sql = $this->safesql->query(
			"UPDATE 
				`media_video` 
			SET hits=hits+1 
			WHERE id=%i",
			array(
				$this->getId(),
			));
		return $this->db->query($sql);
	}

}
