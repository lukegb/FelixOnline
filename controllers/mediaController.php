<?php
/*
 * Media Site Controller
 */
class MediaController extends BaseController {
    protected $theme; // placeholder for theme class
    protected $db;

    function __construct() {
        global $db;
        $this->db = $db;

        $this->theme = new Theme('classic');
        $this->theme->setSite('media');
    }

    /*
     * Render correct page
     *
     * Hierarchy:
     *      media - base media page (all media)
     *      media-{type} - media type page (e.g. photo or video)
     *      media-{type}-single - media type single page
     */
    function GET($matches) {
        if(array_key_exists('type', $matches)) {
            $class = 'media'.ucfirst($matches['type']);
            if(array_key_exists('id', $matches)) {
                $media = new $class($matches['id']);
                $this->theme->setHierarchy(array(
                    $matches['type'].'-single', /* media-{type}-single.php*/
                    $matches['type'] /* media-{type}.php */
                ));
            } else {
                $media = new $class();
                $this->theme->setHierarchy(array(
                    $matches['type'] /* media-{type}.php */
                ));
            }
        } else {
            $media = new Media();
        }
        $this->theme->appendData(array(
            'media' => $media
        ));

        $this->theme->render('media');
    }
}
?>
