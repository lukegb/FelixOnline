<?php
/*
 * Article class
 * Deals with both article retrieval and article submission
 *
 * Fields:
 *	  id			  - id of article
 *	  title		   - title of article
 *	  short_title	 - short title of article for boxes on front page [optional]
 *	  teaser		  - article teaser
 *	  author		  - first author of article, superseded by article_author table [depreciated]
 *	  category		- id of category article is in
 *	  date			- timestamp when article was added to site
 *	  approvedby	  - user who approved the article to be published
 *	  published	   - timestamp when article was published
 *	  hidden		  - if article is hidden from engine
 *        searchable       - can article be seen by search engines?
 *	  text1		   - id of main article text
 *	  img1			- id of main article image
 *	  text2		   - id of second article text [depreciated]
 *	  img2			- id of second image text [depreciated]
 *	  img2lr		  - not quite sure [TODO]
 *	  hits			- number of views the article has had
 *	  short_desc	  - short description of article for boxes on front page [optional]
 */
class Article extends BaseModel {
	private $authors; // array of authors of article
	private $approvedby; // user object of user who approved article
	private $category_cat; // category cat (short version)
	private $category_label; // category label
	private $content; // article content
	private $image; // image class
	private $image_title; // image title
	private $num_comments; // number of comments
	private $category; // category class
	private $search = array('@<>@',
		'@<script[^>]*?>.*?</script>@siU',  // javascript
		'@<style[^>]*?>.*?</style>@siU',	// style tags
		'@<embed[^>]*?>.*?</embed>@siU',	// embed
		'@<object[^>]*?>.*?</object>@siU',	// object
		'@<iframe[^>]*?>.*?</iframe>@siU',	// iframe
		'@<![\s\S]*?--[ \t\n\r]*>@',		// multi-line comments including CDATA
		'@</?[^>]*>*@' 		  // html tags
	);
	protected $db;
	protected $safesql;

	/*
	 * Constructor for Article class
	 * If initialised with id then store relevant data in object
	 *
	 * $id - ID of article (optional)
	 *
	 * Returns article object
	 */
	function __construct($id = NULL) {
		global $db;
		global $safesql;
		$this->db = $db;
		$this->safesql = $safesql;

		//$this->db->cache_queries = true;
		if($id !== NULL) { // if creating an already existing article object
			$sql = $this->safesql->query("SELECT
										`id`,
										`title`,
										`short_title`,
										`teaser`,
										`author`,
										`approvedby`,
										`category`,
										UNIX_TIMESTAMP(`date`) as date,
										UNIX_TIMESTAMP(`published`) as published,`hidden`,
										`searchable`,
										`text1`,
										`text2`,
										`img1`,
										`img2`,
										`img2lr`,
										`hits`
									FROM `article`
									WHERE id=%i", array($id));
			parent::__construct($this->db->get_row($sql), 'Article', $id);
			//$this->db->cache_queries = false;
			return $this;
		} else {
			// initialise new article
		}
	}

	/*
	 * Public: Get array of authors of article
	 *
	 * Returns array
	 */
	public function getAuthors() {
		if(!$this->authors) {
			$sql = $this->safesql->query("SELECT
											article_author.author as author
											FROM `article_author`
											INNER JOIN `article`
											ON (article_author.article=article.id)
											WHERE article.id=%i", array($this->getId()));
			$authors = $this->db->get_results($sql);
			foreach($authors as $author) {
				$this->authors[] = new User($author->author);
			}
		}
		return $this->authors;
	}

	/*
	 * Public: Get approved by user
	 *
	 * Returns User object
	 */
	public function getApprovedBy() {
		if(!$this->approvedby) {
			$this->approvedby = new User($this->fields['approvedby']);
		}
		return $this->approvedby;
	}

	/*
	 * Public: Get list of authors in english
	 *
	 * Returns html string of article authors
	 */
	public function getAuthorsEnglish() {
		$array = $this->getAuthors();
		// sanity check
		if (!$array || !count ($array))
			return '';
		// change array into linked usernames
		foreach ($array as $key => $user) {
			$full_array[$key] = '<a href="'.$user->getURL().'">'.$user->getName().'</a>';
		}
		// get last element
		$last = array_pop($full_array);
		// if it was the only element - return it
		if (!count ($full_array))
			return $last;
		return implode (', ', $full_array).' and '.$last;
	}

	/*
	 * Public: Get category class
	 */
	public function getCategory() {
		if(!$this->category) {
			$this->category = new Category($this->getCategoryCat());
		}
		return $this->category;
	}

	/*
	 * Public: Get cat of article category
	 */
	public function getCategoryCat() {
		if(!$this->category_cat || !$this->category_label) {
			$sql = $this->safesql->query("SELECT
											`cat`,
											label
										FROM `category`
										WHERE id = %i", array($this->fields['category']));
			$cat = $this->db->get_row($sql);
			$this->category_cat = $cat->cat;
			$this->category_label = $cat->label;
		}
		return $this->category_cat;
	}

	/*
	 * Public: Get label of article category
	 */
	public function getCategoryLabel() {
		if(!$this->category_label || !$this->category_cat) {
			$sql = $this->safesql->query("SELECT cat,`label` FROM `category` WHERE id = %i", array($this->getCategory()));
			$cat = $this->db->get_row($sql);
			$this->category_label = $cat->label;
			$this->category_cat = $cat->cat;
		}
		return $this->category_label;
	}

	/*
	 * Public: Get category url
	 */
	public function getCategoryURL() {
		return STANDARD_URL.$this->getCategoryCat().'/';
	}

	/*
	 * Public: Get article content
	 */
	public function getContent() {
		if(!$this->content) {
			$sql = $this->safesql->query("SELECT `content` FROM `text_story` WHERE id = %i", array($this->getText1()));
			$this->content = $this->db->get_var($sql);
		}
		return $this->cleanText($this->content);
	}

	/*
	 * Private: Clean text
	 */
	private function cleanText($text) {
		$result = strip_tags($text, '<p><a><div><b><i><br><blockquote><object><param><embed><li><ul><ol><strong><img><h1><h2><h3><h4><h5><h6><em><iframe><strike>'); // Gets rid of html tags except <p><a><div>
		$result = preg_replace('#<div[^>]*(?:/>|>(?:\s|&nbsp;)*</div>)#im', '', $result); // Removes empty html div tags
		$result = preg_replace('#<span*(?:/>|>(?:\s|&nbsp;)[^>]*</span>)#im', '', $result); // Removes empty html div tags
		$result = preg_replace('#<p[^>]*(?:/>|>(?:\s|&nbsp;)*</p>)#im', '', $result); // Removes empty html p tags
		//$result = preg_replace("/<(\/)*div[^>]*>/", "<\\1p>", $result); // Changes div tags into <p> tags
		return $result;
	}

	/*
	 * Public: Get article teaser
	 * TODO
	 *
	 * Returns string
	 */
	public function getTeaserFull() {
		if ($this->getTeaser()) {
			return str_replace('<br/>','',strip_tags($this->getTeaser()));
			//return str_replace('<br/>','',preg_replace($this->search,'',$this->teaser));
		} else {
			$text = $this->getText(1);
			return trim(substr(strip_tags($text),0,strrpos(substr(strip_tags($text),0,TEASER_LENGTH),' '))).'...';
		}
	}

	/*
	 * Public: Get article preview with word limit
	 * Shortens article content to word limit
	 *
	 * $limit - word limit [defaults to 50]
	 */
	public function getPreview($limit = 50) {
		$string = strip_tags($this->getContent());
		$words = explode(" ",$string);
		if(count($words) > $limit) {
		  $append = ' ... <br/><a href="'.$this->getURL().'" title="Read more" id="readmorelink">Read more</a>';
		}
		return implode(" ",array_splice($words,0,$limit)) . $append;
	}

	/*
	 * Public: Get short description
	 * If a short description is specified in the database then use that.
	 * Otherwise limit article content to a certain character length
	 *
	 * $limit - character limit for description [defaults to 80]
	 */
	public function getShortDesc($limit = 80) {
		if($this->fields['short_desc']) {
			return substr($this->fields['short_desc'], 0, $limit);
		} else {
			return substr(strip_tags($this->getContent()), 0, $limit);
		}
	}

	/*
	 * Public: Get number of comments on article
	 *
	 * Returns int
	 */
	public function getNumComments() {
		if(!$this->num_comments && $this->num_comments !== 0) {
			$sql = $this->safesql->query("SELECT SUM(count) AS count
										FROM (
											SELECT article,COUNT(*) AS count
											FROM `comment`
											WHERE article=%i
											AND `active`=1
											GROUP BY article
											UNION ALL
											SELECT article,COUNT(*) AS count
											FROM `comment_ext`
											WHERE article=%i
											AND `active`=1
											AND `pending`=0
											GROUP BY article
										) AS t GROUP BY article", array($this->getId(), $this->getId()));
			$this->num_comments = $this->db->get_var($sql);
			if(!$this->num_comments) $this->num_comments = 0;
		}
		return $this->num_comments;
	}

	/*
	 * Public: Get comments
	 *
	 * Returns db object
	 */
	public function getComments() {
		$sql = $this->safesql->query("SELECT id,timestamp
									FROM (
										SELECT
											comment.id,
											UNIX_TIMESTAMP(comment.timestamp) AS timestamp
										FROM `comment`
										WHERE article=%i
										AND active=1". // select all internal comments
									" UNION SELECT
											comment_ext.id,
											UNIX_TIMESTAMP(comment_ext.timestamp) AS timestamp
										FROM `comment_ext`
										WHERE article=%i
										AND pending=0 AND spam=0". // select external comments that are not spam
									" UNION SELECT
											comment_ext.id,
											UNIX_TIMESTAMP(comment_ext.timestamp) AS timestamp
											FROM `comment_ext`
										WHERE article=%i
										AND IP = '%s'
										AND active=1
										AND pending=1
										AND spam=0". // select external comments that are pending and are from current ip
									") AS t
									ORDER BY timestamp ASC
									LIMIT 500", array($this->getId(), $this->getId(), $this->getId(), $_SERVER['REMOTE_ADDR']));
		$comments = array();
		$rsc = $this->db->get_results($sql);
		if($rsc) {
			foreach($rsc as $key => $obj) {
				$comments[] = new Comment($obj->id);
			}
		}
		return $comments;
	}

	/*
	 * Public: Get image class
	 */
	public function getImage() {
		if($this->getImg1()) {
			if($this->getImg1() == 183 || $this->getImg1() == 742) {
				return false;
			} else {
				if(!$this->image) {
					$this->image = new Image($this->getImg1());
				}
				return $this->image;
			}
		} else {
			return false;
		}
	}

	/*
	 * Public: Get full article url
	 *
	 * Returns string
	 */
	public function getURL() {
		return STANDARD_URL.$this->constructURL();
	}

	/*
	 * Private: Construct url for article from title and category label
	 *
	 * Returns string
	 */
	private function constructURL() {
		$cat = $this->getCategoryCat();
		$dashed = Utility::urliseText($this->getTitle());
		$output = $cat.'/'.$this->getId().'/'.$dashed.'/'; // output: CAT/ID/TITLE/
		return $output;
	}

	/*
	 * Public: Log visit and increment hit count on article
	 * Check if user has visited page before (based on ip or user for a set length of time)
	 */
	public function logVisit() {
		if(!$this->recentlyVisited()) {
			$this->logVisitor();
			$this->hitArticle();
		} else {
			$this->logVisitor(1);
		}
	}

	/*
	 * Private: Increment hit count on article
	 */
	private function hitArticle() {
		$sql = $this->safesql->query("UPDATE `article` SET hits=hits+1 WHERE id=%i", array($this->getId()));
		return $this->db->query($sql);
	}

	/*
	 * Private: Add log of visitor into article_vist table
	 */
	private function logVisitor($repeat = 0) {
		global $currentuser;
		$user = NULL;
		if($currentuser->isLoggedIn()) $user = $currentuser->getUser();
		$sql = $this->safesql->query("INSERT INTO
										article_visit
									(
										article,
										user,
										IP,
										browser,
										referrer,
										repeat_visit
									) VALUES (%q)", array(array($this->getId(), $user, $_SERVER['REMOTE_ADDR'], $_SERVER['HTTP_USER_AGENT'], $_SERVER['HTTP_REFERER'], $repeat)));
		return $this->db->query($sql);
	}

	/*
	 * Private: Check if user has recently visited article
	 *
	 * Returns boolean
	 */
	private function recentlyVisited() {
		global $currentuser;
		if($currentuser->isLoggedIn()) {
			$sql = $this->safesql->query("SELECT
											COUNT(id)
										FROM
											`article_visit`
										WHERE user = '%s'
										AND article = '%s'
										AND UNIX_TIMESTAMP(timestamp) < now() - interval 4 week", array($currentuser->getUser(), $this->getId()));
			return $this->db->get_var($sql);
		} else {
			$sql = $this->safesql->query("SELECT
											COUNT(id)
										FROM
											`article_visit`
										WHERE IP = '%s'
										AND browser = '%s'
										AND article = %i
										AND UNIX_TIMESTAMP(timestamp) < now() - interval 4 week", array($_SERVER['REMOTE_ADDR'], $_SERVER['HTTP_USER_AGENT'], $this->getId()));
			return $this->db->get_var($sql);
		}
	}

	public function print_this() {
		print_r($this);
	}

	public static function getMostPopular($number_to_get) {
		global $db;
		global $safesql;

		$sql = $safesql->query("
			SELECT
				DISTINCT article AS id,
				COUNT(article) AS c
			FROM (
				SELECT article FROM article_visit AS av
				INNER JOIN article AS a
				ON (av.article=a.id)
				WHERE a.published IS NOT NULL
				AND a.published > FROM_UNIXTIME(UNIX_TIMESTAMP() - 1814400)
				ORDER BY timestamp DESC LIMIT 500
			) AS t GROUP BY article ORDER BY c DESC LIMIT %i",
			array($number_to_get)
		);

		return $db->get_results($sql);
	}

	public static function getMostCommented($threshold, $number_to_get) {
		global $db;
		global $safesql;

		$sql = $safesql->query("SELECT article AS id,SUM(count) AS count
									FROM (
											(SELECT c.article,COUNT(*) AS count
											FROM `comment` AS c
											INNER JOIN `article` AS a ON (c.article=a.id)
											WHERE c.`active`=1
											AND timestamp>(DATE_SUB(NOW(),INTERVAL %i day))
											AND a.published<NOW()
											GROUP BY article
											ORDER BY timestamp DESC
											LIMIT 20)
										UNION ALL
											(SELECT ce.article,COUNT(*) AS count
											FROM `comment_ext` AS ce
											INNER JOIN `article` AS a ON (ce.article=a.id)
											WHERE ce.`active`=1
											AND pending=0
											AND timestamp>(DATE_SUB(NOW(),INTERVAL %i day))
											AND a.published<NOW()
											GROUP BY article
											ORDER BY timestamp DESC)
									) AS t
									GROUP BY article
									ORDER BY count DESC, article DESC LIMIT %i", array($threshold, $threshold, $number_to_get)); // go for most recent comments instead
		return $db->get_results($sql);
	}
	
}

