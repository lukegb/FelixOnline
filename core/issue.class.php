<?php
/*
 * Issue Class
 *
 * Fields:
 *      id          - Issue id
 *      PubDate     - Date of publish (YYYY-MM-DD)
 *      IssueNo     - Issue number
 *      PubNo       - Publication number (references publication table)
 *      Description - Text description of publication
 *      Year        - Temporary
 */
class Issue extends BaseModel {
    protected $filters = array();

    function __construct($id = NULL) {
        global $dba;
        $this->dba = $dba;
        if($id !== NULL) {
            $sql = "SELECT
                        `id`,
                        UNIX_TIMESTAMP(`PubDate`) as pub_date,
                        `IssueNo`,
                        `PubNo`,
                        `Description`,
                        `Year`
                    FROM `Issues`
                    WHERE id=".$id;
            $this->filters = array(
                'IssueNo' => 'issue_no',
                'PubNo' => 'pub_no'
            );
            parent::__construct($this->dba->get_row($sql), 'Issue', $id);
            return $this;
        } else {
            // initialise new issue
        }
    }

    /*
     * Public: Get URL
     *
     * Returns string
     */
    public function getURL() {
        $url = STANDARD_URL.'issuearchive/issue/'.$this->getId();    
        return $url;
    }

    /*
     * Public: Get download URL
     *
     * Returns string
     */
    public function getDownloadURL() {
        $url = $this->getURL().'/download';
        return $url;
    }

    /*
     * Public: Get thumbnail
     * Gets thumbnail filename
     *
     * TODO: clean up
     *
     * Returns string
     */
    public function getThumbnail() {
        $thumb = substr($this->getFileName(),8,(strlen($this->getFileName())-11)).'png';
        return $thumb;
    }

    /*
     * Public: Get thumbnail url
     *
     * Returns string
     */
    public function getThumbnailURL() {
        $url = STANDARD_URL.'archive/thumbs/'.$this->getThumbnail();
        return $url;
    }
}

